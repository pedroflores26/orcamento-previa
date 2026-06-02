<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {

  if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $s = $db->prepare("
      SELECT 
        os.*,
        c.nome AS cliente_nome,
        v.placa,
        v.modelo,
        v.cor,
        o.numero AS numero_orcamento
      FROM ordens_servico os
      LEFT JOIN clientes c ON os.cliente_id = c.id
      LEFT JOIN veiculos v ON os.veiculo_id = v.id
      LEFT JOIN orcamentos o ON os.orcamento_id = o.id
      WHERE os.id = ?
    ");

    $s->bind_param('i', $id);
    $s->execute();

    $ordem = $s->get_result()->fetch_assoc();

    if (!$ordem) {
      echo json_encode(['erro' => 'Ordem não encontrada']);
      exit;
    }

    echo json_encode($ordem);
    exit;
  }

  $q = '%' . ($_GET['q'] ?? '') . '%';

  $s = $db->prepare("
    SELECT 
      os.*,
      c.nome AS cliente_nome,
      v.placa,
      v.modelo,
      v.cor,
      o.numero AS numero_orcamento
    FROM ordens_servico os
    LEFT JOIN clientes c ON os.cliente_id = c.id
    LEFT JOIN veiculos v ON os.veiculo_id = v.id
    LEFT JOIN orcamentos o ON os.orcamento_id = o.id
    WHERE c.nome LIKE ?
       OR v.placa LIKE ?
       OR v.modelo LIKE ?
       OR os.numero_os LIKE ?
       OR os.status LIKE ?
    ORDER BY os.id DESC
    LIMIT 200
  ");

  $s->bind_param('sssss', $q, $q, $q, $q, $q);
  $s->execute();

  echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC));
  exit;
}

if ($method === 'POST') {

  $data = json_decode(file_get_contents('php://input'), true);

  if ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);

    $s = $db->prepare("DELETE FROM ordens_servico WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();

    echo json_encode(['ok' => true]);
    exit;
  }

  $id          = (int)($data['id'] ?? 0);
  $status      = $data['status'] ?? 'Aguardando';
  $prioridade  = $data['prioridade'] ?? 'Normal';
  $tarefas     = $data['tarefas'] ?? '';
  $observacoes = $data['observacoes'] ?? '';
  $dataEntrega = $data['data_entrega'] ?: null;

  if ($action === 'update') {
    $s = $db->prepare("
      UPDATE ordens_servico SET
        status = ?,
        prioridade = ?,
        tarefas = ?,
        observacoes = ?,
        data_entrega = ?
      WHERE id = ?
    ");

    $s->bind_param(
      'sssssi',
      $status,
      $prioridade,
      $tarefas,
      $observacoes,
      $dataEntrega,
      $id
    );

    $s->execute();

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
  }
}

echo json_encode(['erro' => 'Requisição inválida']);