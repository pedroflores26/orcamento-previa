<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
  if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s = $db->prepare("SELECT * FROM clientes WHERE id=?");
    $s->bind_param('i',$id); $s->execute();
    echo json_encode($s->get_result()->fetch_assoc()); exit;
  }
  $q = '%'.($db->real_escape_string($_GET['q']??'')).'%';
  $sql = "SELECT c.*, (SELECT COUNT(*) FROM veiculos v WHERE v.cliente_id=c.id) AS total_veiculos,
          c.telefone AS tel
          FROM clientes c
          WHERE c.nome LIKE ? OR c.cpf_cnpj LIKE ? OR c.telefone LIKE ?
          ORDER BY c.nome LIMIT 100";
  $s = $db->prepare($sql);
  $s->bind_param('sss',$q,$q,$q); $s->execute();
  echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC)); exit;
}

if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if ($action === 'delete') {
    $id=(int)($data['id']??0);
    $db->prepare("DELETE FROM clientes WHERE id=?")->execute() || true;
    $s=$db->prepare("DELETE FROM clientes WHERE id=?");
    $s->bind_param('i',$id); $s->execute();
    echo json_encode(['ok'=>true]); exit;
  }
  $nome    = $data['nome']     ?? '';
  $cpf     = $data['cpf_cnpj']?? '';
  $tel     = $data['telefone'] ?? '';
  $email   = $data['email']    ?? '';
  $end     = $data['endereco'] ?? '';
  if ($action === 'update') {
    $id=(int)($data['id']??0);
    $s=$db->prepare("UPDATE clientes SET nome=?,cpf_cnpj=?,telefone=?,email=?,endereco=? WHERE id=?");
    $s->bind_param('sssssi',$nome,$cpf,$tel,$email,$end,$id); $s->execute();
    echo json_encode(['ok'=>true,'id'=>$id]); exit;
  }
  $s=$db->prepare("INSERT INTO clientes (nome,cpf_cnpj,telefone,email,endereco) VALUES (?,?,?,?,?)");
  $s->bind_param('sssss',$nome,$cpf,$tel,$email,$end); $s->execute();
  echo json_encode(['ok'=>true,'id'=>$db->insert_id]); exit;
}
echo json_encode(['erro'=>'Inválido']);
