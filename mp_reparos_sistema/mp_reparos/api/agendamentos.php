<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';

$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

/* ── GET ──────────────────────────────────────────── */
if ($method === 'GET') {

  if (isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    $stmt = $db->prepare("
      SELECT
        a.*,
        c.nome AS cliente_nome,
        v.placa,
        v.modelo,
        v.cor
      FROM agendamentos a
      LEFT JOIN clientes c ON a.cliente_id = c.id
      LEFT JOIN veiculos v ON a.veiculo_id = v.id
      WHERE a.id = ?
    ");

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $ag = $stmt->get_result()->fetch_assoc();

    if (!$ag) {
      echo json_encode(['erro' => 'Não encontrado']);
      exit;
    }

    $s = $db->prepare("
      SELECT descricao, valor
      FROM agendamento_servicos
      WHERE agendamento_id = ?
    ");

    $s->bind_param('i', $id);
    $s->execute();

    $ag['servicos'] = $s->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($ag);
    exit;
  }

  $inicio = $_GET['inicio'] ?? date('Y-m-d');
  $fim    = $_GET['fim']    ?? date('Y-m-d');

  $stmt = $db->prepare("
    SELECT
      a.id,
      a.data_agenda,
      a.hora,
      a.status,
      a.observacao,
      a.valor_total,
      a.cliente_id,
      a.veiculo_id,
      c.nome AS cliente_nome,
      c.telefone AS cliente_tel,
      v.placa,
      v.modelo,
      v.cor
    FROM agendamentos a
    LEFT JOIN clientes c ON a.cliente_id = c.id
    LEFT JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.data_agenda BETWEEN ? AND ?
    ORDER BY a.data_agenda, a.hora
  ");

  $stmt->bind_param('ss', $inicio, $fim);
  $stmt->execute();

  $ags = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

  foreach ($ags as &$ag) {
    $s = $db->prepare("
      SELECT descricao, valor
      FROM agendamento_servicos
      WHERE agendamento_id = ?
    ");

    $s->bind_param('i', $ag['id']);
    $s->execute();

    $ag['servicos'] = $s->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  echo json_encode($ags);
  exit;
}

/* ── POST ─────────────────────────────────────────── */
if ($method === 'POST') {

  $data = json_decode(file_get_contents('php://input'), true);

  if (!$data) {
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
  }

  if ($action === 'delete') {

    $id = (int)($data['id'] ?? 0);

    $stmt = $db->prepare("
      DELETE FROM agendamentos
      WHERE id = ?
    ");

    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode(['ok' => true]);
    exit;
  }

  $clienteNome = trim($data['cliente_nome'] ?? '');
  $veiculoDesc = trim($data['veiculo_desc'] ?? '');
  $placa       = strtoupper(trim($data['placa'] ?? ''));
  $cor         = trim($data['cor'] ?? '');

  $cliId = $data['cliente_id'] ?? null;
  $veiId = $data['veiculo_id'] ?? null;

  if (!$cliId && $clienteNome !== '') {
    $cliId = buscarOuCriarCliente($db, $clienteNome);
  }

  if (!$veiId && $cliId && ($veiculoDesc !== '' || $placa !== '')) {
    $veiId = buscarOuCriarVeiculo($db, $cliId, $veiculoDesc, $placa, $cor);
  }

  $cliId = $cliId ?: null;
  $veiId = $veiId ?: null;

  $dataAg   = $data['data_agenda'] ?? date('Y-m-d');
  $hora     = ($data['hora'] ?? '') ?: null;
  $status   = $data['status'] ?? 'aguardando';
  $obs      = $data['observacao'] ?? '';
  $total    = floatval($data['valor_total'] ?? 0);
  $servicos = $data['servicos'] ?? [];

  if ($action === 'update') {

    $id = (int)($data['id'] ?? 0);

    $stmt = $db->prepare("
      UPDATE agendamentos SET
        cliente_id = ?,
        veiculo_id = ?,
        data_agenda = ?,
        hora = ?,
        status = ?,
        observacao = ?,
        valor_total = ?
      WHERE id = ?
    ");

    $stmt->bind_param(
      'iissssdi',
      $cliId,
      $veiId,
      $dataAg,
      $hora,
      $status,
      $obs,
      $total,
      $id
    );

    $stmt->execute();

    $d2 = $db->prepare("
      DELETE FROM agendamento_servicos
      WHERE agendamento_id = ?
    ");

    $d2->bind_param('i', $id);
    $d2->execute();

    inserirServicos($db, $id, $servicos);

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
  }

  $stmt = $db->prepare("
    INSERT INTO agendamentos (
      cliente_id,
      veiculo_id,
      data_agenda,
      hora,
      status,
      observacao,
      valor_total
    ) VALUES (?,?,?,?,?,?,?)
  ");

  $stmt->bind_param(
    'iissssd',
    $cliId,
    $veiId,
    $dataAg,
    $hora,
    $status,
    $obs,
    $total
  );

  $stmt->execute();

  $newId = $db->insert_id;

  inserirServicos($db, $newId, $servicos);

  echo json_encode(['ok' => true, 'id' => $newId]);
  exit;
}

function buscarOuCriarCliente($db, $nome) {

  $nome = trim($nome);

  if ($nome === '') {
    return null;
  }

  $stmt = $db->prepare("
    SELECT id
    FROM clientes
    WHERE LOWER(nome) = LOWER(?)
    LIMIT 1
  ");

  $stmt->bind_param('s', $nome);
  $stmt->execute();

  $cliente = $stmt->get_result()->fetch_assoc();

  if ($cliente) {
    return (int)$cliente['id'];
  }

  $stmt = $db->prepare("
    INSERT INTO clientes (nome)
    VALUES (?)
  ");

  $stmt->bind_param('s', $nome);
  $stmt->execute();

  return (int)$db->insert_id;
}

function buscarOuCriarVeiculo($db, $clienteId, $modelo, $placa = '', $cor = '') {

  $modelo = trim($modelo);
  $placa  = strtoupper(trim($placa));
  $cor    = trim($cor);

  if (!$clienteId || ($modelo === '' && $placa === '')) {
    return null;
  }

  if ($placa !== '') {

    $stmt = $db->prepare("
      SELECT id
      FROM veiculos
      WHERE cliente_id = ?
      AND UPPER(placa) = UPPER(?)
      LIMIT 1
    ");

    $stmt->bind_param('is', $clienteId, $placa);
    $stmt->execute();

    $veiculo = $stmt->get_result()->fetch_assoc();

    if ($veiculo) {
      return (int)$veiculo['id'];
    }

  } else {

    $stmt = $db->prepare("
      SELECT id
      FROM veiculos
      WHERE cliente_id = ?
      AND LOWER(modelo) = LOWER(?)
      LIMIT 1
    ");

    $stmt->bind_param('is', $clienteId, $modelo);
    $stmt->execute();

    $veiculo = $stmt->get_result()->fetch_assoc();

    if ($veiculo) {
      return (int)$veiculo['id'];
    }
  }

  $stmt = $db->prepare("
    INSERT INTO veiculos (
      cliente_id,
      placa,
      modelo,
      cor
    ) VALUES (?,?,?,?)
  ");

  $stmt->bind_param(
    'isss',
    $clienteId,
    $placa,
    $modelo,
    $cor
  );

  $stmt->execute();

  return (int)$db->insert_id;
}

function inserirServicos($db, $agId, $servicos) {

  if (!$servicos) return;

  $stmt = $db->prepare("
    INSERT INTO agendamento_servicos (
      agendamento_id,
      descricao,
      valor
    ) VALUES (?,?,?)
  ");

  foreach ($servicos as $s) {

    $desc = $s['descricao'] ?? '';
    $val  = floatval($s['valor'] ?? 0);

    if (trim($desc) === '' && $val <= 0) {
      continue;
    }

    $stmt->bind_param(
      'isd',
      $agId,
      $desc,
      $val
    );

    $stmt->execute();
  }
}

echo json_encode(['erro' => 'Requisição inválida']);