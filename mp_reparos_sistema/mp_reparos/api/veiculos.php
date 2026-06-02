<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
  if (isset($_GET['id'])) {
    $id=(int)$_GET['id'];
    $s=$db->prepare("SELECT * FROM veiculos WHERE id=?");
    $s->bind_param('i',$id); $s->execute();
    echo json_encode($s->get_result()->fetch_assoc()); exit;
  }
  $q  = '%'.($db->real_escape_string($_GET['q']??'')).'%';
  $cliId = (int)($_GET['cliente_id']??0);
  $sql = "SELECT v.*, c.nome AS cliente_nome FROM veiculos v
          LEFT JOIN clientes c ON v.cliente_id=c.id
          WHERE (v.placa LIKE ? OR v.modelo LIKE ? OR v.marca LIKE ? OR c.nome LIKE ?)";
  if ($cliId) $sql .= " AND v.cliente_id=$cliId";
  $sql .= " ORDER BY v.placa LIMIT 100";
  $s=$db->prepare($sql);
  $s->bind_param('ssss',$q,$q,$q,$q); $s->execute();
  echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC)); exit;
}

if ($method === 'POST') {
  $data=json_decode(file_get_contents('php://input'),true);
  if ($action==='delete') {
    $id=(int)($data['id']??0);
    $s=$db->prepare("DELETE FROM veiculos WHERE id=?");
    $s->bind_param('i',$id); $s->execute();
    echo json_encode(['ok'=>true]); exit;
  }
  $placa=$data['placa']??'';
  $cliId=($data['cliente_id']?:(null));
  $marca=$data['marca']??'';
  $modelo=$data['modelo']??'';
  $ano=$data['ano']??'';
  $cor=$data['cor']??'';
  $km=$data['km']??'';
  $seg=$data['seguradora']??'';
  $chassi=$data['chassi']??'';
  if ($action==='update') {
    $id=(int)($data['id']??0);
    $s=$db->prepare("UPDATE veiculos SET cliente_id=?,placa=?,marca=?,modelo=?,ano=?,cor=?,km=?,seguradora=?,chassi=? WHERE id=?");
    $s->bind_param('issssssssi',$cliId,$placa,$marca,$modelo,$ano,$cor,$km,$seg,$chassi,$id);
    $s->execute(); echo json_encode(['ok'=>true,'id'=>$id]); exit;
  }
  $s=$db->prepare("INSERT INTO veiculos (cliente_id,placa,marca,modelo,ano,cor,km,seguradora,chassi) VALUES (?,?,?,?,?,?,?,?,?)");
  $s->bind_param('issssssss',$cliId,$placa,$marca,$modelo,$ano,$cor,$km,$seg,$chassi);
  $s->execute(); echo json_encode(['ok'=>true,'id'=>$db->insert_id]); exit;
}
echo json_encode(['erro'=>'Inválido']);
