<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {

  if ($action === 'orcamentos_cliente') {
    $clienteId = (int)($_GET['cliente_id'] ?? 0);

    $s = $db->prepare("
      SELECT
        o.id,
        o.numero,
        o.total,
        o.data_emissao,
        v.modelo,
        v.placa
      FROM orcamentos o
      LEFT JOIN veiculos v ON v.id = o.veiculo_id
      WHERE o.cliente_id = ?
      AND (o.status IS NULL OR o.status <> 'Cancelado')
      ORDER BY o.id DESC
    ");

    $s->bind_param('i', $clienteId);
    $s->execute();

    echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC));
    exit;
  }

  if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $s = $db->prepare("
      SELECT f.*, c.nome AS cliente_nome
      FROM faturas f
      LEFT JOIN clientes c ON c.id = f.cliente_id
      WHERE f.id = ?
    ");

    $s->bind_param('i', $id);
    $s->execute();

    $fatura = $s->get_result()->fetch_assoc();

    if (!$fatura) {
      echo json_encode(['erro' => 'Fatura não encontrada']);
      exit;
    }

    $it = $db->prepare("
      SELECT *
      FROM fatura_itens
      WHERE fatura_id = ?
      ORDER BY id
    ");

    $it->bind_param('i', $id);
    $it->execute();

    $fatura['itens'] = $it->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($fatura);
    exit;
  }

  $q = '%' . ($_GET['q'] ?? '') . '%';

  $s = $db->prepare("
    SELECT
      f.id,
      f.numero,
      f.data_emissao,
      f.status,
      f.total,
      c.nome AS cliente_nome,
      COUNT(fi.id) AS qtd_itens
    FROM faturas f
    LEFT JOIN clientes c ON c.id = f.cliente_id
    LEFT JOIN fatura_itens fi ON fi.fatura_id = f.id
    WHERE f.numero LIKE ?
       OR c.nome LIKE ?
       OR f.status LIKE ?
    GROUP BY f.id
    ORDER BY f.id DESC
    LIMIT 150
  ");

  $s->bind_param('sss', $q, $q, $q);
  $s->execute();

  echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC));
  exit;
}

if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  if (!$data) {
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
  }

  if ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);

    $s = $db->prepare("DELETE FROM faturas WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();

    echo json_encode(['ok' => true]);
    exit;
  }

  $id          = (int)($data['id'] ?? 0);
  $numero      = trim($data['numero'] ?? '');
  $clienteId   = (int)($data['cliente_id'] ?? 0);
  $dataEmissao = $data['data_emissao'] ?? date('Y-m-d');
  $status      = $data['status'] ?? 'Aberta';
  $observacoes = $data['observacoes'] ?? '';
  $itens       = $data['itens'] ?? [];

  if (!$clienteId) {
    echo json_encode(['erro' => 'Selecione um cliente']);
    exit;
  }

  if (!$itens) {
    echo json_encode(['erro' => 'Selecione pelo menos um carro/orçamento']);
    exit;
  }

  $total = 0;
  foreach ($itens as $item) {
    $total += floatval($item['valor'] ?? 0);
  }

  if ($action === 'update' && $id > 0) {
    $s = $db->prepare("
      UPDATE faturas SET
        numero = ?,
        cliente_id = ?,
        data_emissao = ?,
        status = ?,
        observacoes = ?,
        total = ?
      WHERE id = ?
    ");

    $s->bind_param(
      'sisssdi',
      $numero,
      $clienteId,
      $dataEmissao,
      $status,
      $observacoes,
      $total,
      $id
    );

    $s->execute();

    $del = $db->prepare("DELETE FROM fatura_itens WHERE fatura_id = ?");
    $del->bind_param('i', $id);
    $del->execute();

    inserirItensFatura($db, $id, $itens);

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
  }

  $s = $db->prepare("
    INSERT INTO faturas (
      numero,
      cliente_id,
      data_emissao,
      status,
      observacoes,
      total
    ) VALUES (?,?,?,?,?,?)
  ");

  $s->bind_param(
    'sisssd',
    $numero,
    $clienteId,
    $dataEmissao,
    $status,
    $observacoes,
    $total
  );

  $s->execute();

  $newId = $db->insert_id;

  if ($numero === '') {
    $numero = str_pad($newId, 4, '0', STR_PAD_LEFT);

    $up = $db->prepare("UPDATE faturas SET numero = ? WHERE id = ?");
    $up->bind_param('si', $numero, $newId);
    $up->execute();
  }

  inserirItensFatura($db, $newId, $itens);

  echo json_encode(['ok' => true, 'id' => $newId]);
  exit;
}

function inserirItensFatura($db, $faturaId, $itens) {
  $s = $db->prepare("
    INSERT INTO fatura_itens (
      fatura_id,
      orcamento_id,
      descricao,
      veiculo,
      placa,
      valor
    ) VALUES (?,?,?,?,?,?)
  ");

  foreach ($itens as $item) {
    $orcamentoId = !empty($item['orcamento_id']) ? (int)$item['orcamento_id'] : null;
    $descricao   = trim($item['descricao'] ?? '');
    $veiculo     = trim($item['veiculo'] ?? '');
    $placa       = strtoupper(trim($item['placa'] ?? ''));
    $valor       = floatval($item['valor'] ?? 0);

    if ($descricao === '') {
      $descricao = $veiculo ?: 'Serviço automotivo';
    }

    $s->bind_param(
      'iisssd',
      $faturaId,
      $orcamentoId,
      $descricao,
      $veiculo,
      $placa,
      $valor
    );

    $s->execute();
  }
}

echo json_encode(['erro' => 'Requisição inválida']);