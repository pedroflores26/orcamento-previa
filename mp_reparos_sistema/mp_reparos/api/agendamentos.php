<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

/* ── GET ──────────────────────────────────────────── */
if ($method === 'GET') {
  // Buscar um agendamento por ID
  if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("
      SELECT a.*, c.nome AS cliente_nome, v.placa, v.modelo, v.cor
      FROM agendamentos a
      LEFT JOIN clientes c ON a.cliente_id = c.id
      LEFT JOIN veiculos v ON a.veiculo_id = v.id
      WHERE a.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $ag = $stmt->get_result()->fetch_assoc();
    if (!$ag) { echo json_encode(['erro'=>'Não encontrado']); exit; }
    // Serviços
    $s = $db->prepare("SELECT descricao, valor FROM agendamento_servicos WHERE agendamento_id=?");
    $s->bind_param('i', $id); $s->execute();
    $ag['servicos'] = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($ag); exit;
  }

  // Listar por semana
  $inicio = $_GET['inicio'] ?? date('Y-m-d');
  $fim    = $_GET['fim']    ?? date('Y-m-d');
  $stmt = $db->prepare("
    SELECT a.id, a.data_agenda, a.hora, a.status, a.observacao, a.valor_total,
           a.cliente_id, a.veiculo_id,
           c.nome AS cliente_nome, c.telefone AS cliente_tel,
           v.placa, v.modelo, v.cor
    FROM agendamentos a
    LEFT JOIN clientes c ON a.cliente_id = c.id
    LEFT JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.data_agenda BETWEEN ? AND ?
    ORDER BY a.data_agenda, a.hora");
  $stmt->bind_param('ss', $inicio, $fim);
  $stmt->execute();
  $ags = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

  foreach ($ags as &$ag) {
    $s = $db->prepare("SELECT descricao, valor FROM agendamento_servicos WHERE agendamento_id=?");
    $s->bind_param('i', $ag['id']); $s->execute();
    $ag['servicos'] = $s->get_result()->fetch_all(MYSQLI_ASSOC);
  }
  echo json_encode($ags); exit;
}

/* ── POST ─────────────────────────────────────────── */
if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  // DELETE
  if ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM agendamentos WHERE id=?");
    $stmt->bind_param('i', $id); $stmt->execute();
    echo json_encode(['ok'=>true]); exit;
  }

  $cliId   = ($data['cliente_id'] ?: null);
  $veiId   = ($data['veiculo_id'] ?: null);
  $dataAg  = $data['data_agenda'] ?? date('Y-m-d');
  $hora    = $data['hora']        ?? null;
  $status  = $data['status']      ?? 'aguardando';
  $obs     = $data['observacao']  ?? '';
  $total   = floatval($data['valor_total'] ?? 0);
  $servicos= $data['servicos']    ?? [];

  // UPDATE
  if ($action === 'update') {
    $id = (int)($data['id'] ?? 0);
    $stmt = $db->prepare("
      UPDATE agendamentos SET
        cliente_id=?, veiculo_id=?, data_agenda=?, hora=?,
        status=?, observacao=?, valor_total=?
      WHERE id=?");
    $stmt->bind_param('iissssdi', $cliId, $veiId, $dataAg, $hora, $status, $obs, $total, $id);
    $stmt->execute();
    // Reinserir serviços
    $db->prepare("DELETE FROM agendamento_servicos WHERE agendamento_id=?")->execute() || true;
    $d2 = $db->prepare("DELETE FROM agendamento_servicos WHERE agendamento_id=?");
    $d2->bind_param('i', $id); $d2->execute();
    inserirServicos($db, $id, $servicos);
    echo json_encode(['ok'=>true, 'id'=>$id]); exit;
  }

  // CREATE
  $stmt = $db->prepare("
    INSERT INTO agendamentos (cliente_id, veiculo_id, data_agenda, hora, status, observacao, valor_total)
    VALUES (?,?,?,?,?,?,?)");
  $stmt->bind_param('iissssd', $cliId, $veiId, $dataAg, $hora, $status, $obs, $total);
  $stmt->execute();
  $newId = $db->insert_id;
  inserirServicos($db, $newId, $servicos);
  echo json_encode(['ok'=>true, 'id'=>$newId]); exit;
}

function inserirServicos($db, $agId, $servicos) {
  if (!$servicos) return;
  $stmt = $db->prepare("INSERT INTO agendamento_servicos (agendamento_id, descricao, valor) VALUES (?,?,?)");
  foreach ($servicos as $s) {
    $desc = $s['descricao'] ?? '';
    $val  = floatval($s['valor'] ?? 0);
    $stmt->bind_param('isd', $agId, $desc, $val);
    $stmt->execute();
  }
}

echo json_encode(['erro' => 'Requisição inválida']);
