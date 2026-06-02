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
      SELECT o.*, c.nome AS cliente_nome, v.placa, v.modelo, v.cor
      FROM orcamentos o
      LEFT JOIN clientes c ON o.cliente_id = c.id
      LEFT JOIN veiculos v ON o.veiculo_id = v.id
      WHERE o.id = ?
    ");
    $s->bind_param('i', $id);
    $s->execute();

    $o = $s->get_result()->fetch_assoc();

    if (!$o) {
      echo json_encode(['erro' => 'Não encontrado']);
      exit;
    }

    $si = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
    $si->bind_param('i', $id);
    $si->execute();

    $o['itens'] = $si->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($o);
    exit;
  }

  $q = '%' . ($_GET['q'] ?? '') . '%';

  $s = $db->prepare("
    SELECT
      o.id,
      o.numero,
      o.data_emissao,
      o.status,
      o.total,
      c.nome AS cliente_nome,
      v.placa,
      v.modelo
    FROM orcamentos o
    LEFT JOIN clientes c ON o.cliente_id = c.id
    LEFT JOIN veiculos v ON o.veiculo_id = v.id
    WHERE o.numero LIKE ?
       OR c.nome LIKE ?
       OR v.placa LIKE ?
       OR v.modelo LIKE ?
    ORDER BY o.id DESC
    LIMIT 100
  ");
  $s->bind_param('ssss', $q, $q, $q, $q);
  $s->execute();

  echo json_encode($s->get_result()->fetch_all(MYSQLI_ASSOC));
  exit;
}

if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  if ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);

    $s = $db->prepare("DELETE FROM orcamentos WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();

    echo json_encode(['ok' => true]);
    exit;
  }

  $num = $data['numero'] ?? '001';

  $danos = $data['danos'] ?? '';
  $dadosFunilaria = [];

  if ($danos) {
    $tmp = json_decode($danos, true);
    if (is_array($tmp)) {
      $dadosFunilaria = $tmp;
    }
  }

  $clienteNome = trim($data['cliente_nome'] ?? ($dadosFunilaria['cliente_nome'] ?? ''));
  $veiculoDesc = trim($data['veiculo_desc'] ?? ($dadosFunilaria['veiculo_desc'] ?? ''));
  $placa       = strtoupper(trim($data['placa'] ?? ($dadosFunilaria['placa'] ?? '')));
  $cor         = trim($data['cor'] ?? ($dadosFunilaria['cor'] ?? ''));

  $cliId = buscarOuCriarCliente($db, $clienteNome, null);
  $veiId = buscarOuCriarVeiculo($db, $cliId, $veiculoDesc, $placa, $cor, null);

  $de = $data['data_emissao'] ?? null;
  $dv = $data['data_validade'] ?? null;

  $st    = $data['status'] ?? 'Aguardando aprovação';
  $pag   = $data['pagamento'] ?? '';
  $prazo = $data['prazo'] ?? '';
  $gar   = $data['garantia'] ?? '';
  $desc  = floatval($data['desconto'] ?? 0);
  $obs   = $data['observacoes'] ?? '';
  $sub   = floatval($data['subtotal'] ?? 0);
  $tot   = floatval($data['total'] ?? 0);
  $itens = $data['itens'] ?? [];

  if ($action === 'update') {
    $id = (int)($data['id'] ?? 0);

    $stmt = $db->prepare("
      UPDATE orcamentos SET
        numero = ?,
        cliente_id = ?,
        veiculo_id = ?,
        data_emissao = ?,
        data_validade = ?,
        status = ?,
        pagamento = ?,
        prazo = ?,
        garantia = ?,
        desconto = ?,
        observacoes = ?,
        danos = ?,
        subtotal = ?,
        total = ?
      WHERE id = ?
    ");

    $stmt->bind_param(
      'siissssssdssddi',
      $num,
      $cliId,
      $veiId,
      $de,
      $dv,
      $st,
      $pag,
      $prazo,
      $gar,
      $desc,
      $obs,
      $danos,
      $sub,
      $tot,
      $id
    );

    $stmt->execute();

    $dd = $db->prepare("DELETE FROM orcamento_itens WHERE orcamento_id = ?");
    $dd->bind_param('i', $id);
    $dd->execute();

    inserirItens($db, $id, $itens);

    atualizarOrdemServico($db, $id, $cliId, $veiId, $danos, $obs);

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
  }

  $stmt = $db->prepare("
    INSERT INTO orcamentos (
      numero,
      cliente_id,
      veiculo_id,
      data_emissao,
      data_validade,
      status,
      pagamento,
      prazo,
      garantia,
      desconto,
      observacoes,
      danos,
      subtotal,
      total
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  ");

  $stmt->bind_param(
    'siissssssdssdd',
    $num,
    $cliId,
    $veiId,
    $de,
    $dv,
    $st,
    $pag,
    $prazo,
    $gar,
    $desc,
    $obs,
    $danos,
    $sub,
    $tot
  );

  $stmt->execute();

  $newId = $db->insert_id;

  inserirItens($db, $newId, $itens);

  criarOrdemServico($db, $newId, $cliId, $veiId, $danos, $obs);

  echo json_encode(['ok' => true, 'id' => $newId]);
  exit;
}

function buscarOuCriarCliente($db, $nome, $clienteId = null) {
  if ($clienteId) {
    return (int)$clienteId;
  }

  $nome = trim($nome);

  if ($nome === '') {
    return null;
  }

  $s = $db->prepare("SELECT id FROM clientes WHERE LOWER(nome) = LOWER(?) LIMIT 1");
  $s->bind_param('s', $nome);
  $s->execute();

  $r = $s->get_result()->fetch_assoc();

  if ($r) {
    return (int)$r['id'];
  }

  $ins = $db->prepare("INSERT INTO clientes (nome) VALUES (?)");
  $ins->bind_param('s', $nome);
  $ins->execute();

  return (int)$db->insert_id;
}

function buscarOuCriarVeiculo($db, $clienteId, $modelo, $placa = '', $cor = '', $veiculoId = null) {
  if ($veiculoId) {
    return (int)$veiculoId;
  }

  if (!$clienteId) {
    return null;
  }

  $modelo = trim($modelo);
  $placa  = strtoupper(trim($placa));
  $cor    = trim($cor);

  if ($modelo === '' && $placa === '') {
    return null;
  }

  if ($placa !== '') {
    $s = $db->prepare("
      SELECT id
      FROM veiculos
      WHERE cliente_id = ?
      AND UPPER(placa) = UPPER(?)
      LIMIT 1
    ");
    $s->bind_param('is', $clienteId, $placa);
    $s->execute();

    $r = $s->get_result()->fetch_assoc();

    if ($r) {
      return (int)$r['id'];
    }
  } else {
    $s = $db->prepare("
      SELECT id
      FROM veiculos
      WHERE cliente_id = ?
      AND LOWER(modelo) = LOWER(?)
      LIMIT 1
    ");
    $s->bind_param('is', $clienteId, $modelo);
    $s->execute();

    $r = $s->get_result()->fetch_assoc();

    if ($r) {
      return (int)$r['id'];
    }
  }

  $ins = $db->prepare("
    INSERT INTO veiculos (cliente_id, placa, modelo, cor)
    VALUES (?, ?, ?, ?)
  ");

  $ins->bind_param('isss', $clienteId, $placa, $modelo, $cor);
  $ins->execute();

  return (int)$db->insert_id;
}

function inserirItens($db, $orcId, $itens) {
  if (!$itens) return;

  $s = $db->prepare("
    INSERT INTO orcamento_itens (
      orcamento_id,
      descricao,
      quantidade,
      unidade,
      valor_unit,
      valor_total
    ) VALUES (?,?,?,?,?,?)
  ");

  foreach ($itens as $it) {
    $desc = $it['descricao'] ?? '';
    $qtd  = floatval($it['quantidade'] ?? 1);
    $un   = $it['unidade'] ?? 'un';
    $vu   = floatval($it['valor_unit'] ?? 0);
    $vt   = floatval($it['valor_total'] ?? 0);

    $s->bind_param('issddd', $orcId, $desc, $qtd, $un, $vu, $vt);
    $s->execute();
  }
}

function montarTarefasOS($danosJson) {
  $dados = json_decode($danosJson, true);

  if (!is_array($dados)) {
    $dados = [];
  }

  $areas = $dados['areas'] ?? [];
  $diagnostico = trim($dados['diagnostico'] ?? '');
  $tipoTinta = trim($dados['tipo_tinta'] ?? '');
  $seguradora = trim($dados['seguradora'] ?? '');

  $tarefas = '';

  if (!empty($areas)) {
    $tarefas .= "Áreas danificadas:\n";
    foreach ($areas as $area) {
      $tarefas .= "- " . $area . "\n";
    }
    $tarefas .= "\n";
  }

  if ($diagnostico !== '') {
    $tarefas .= "Diagnóstico técnico:\n" . $diagnostico . "\n\n";
  }

  if ($tipoTinta !== '') {
    $tarefas .= "Tipo de tinta: " . $tipoTinta . "\n";
  }

  if ($seguradora !== '') {
    $tarefas .= "Seguradora / Revenda: " . $seguradora . "\n";
  }

  if (trim($tarefas) === '') {
    $tarefas = "Verificar serviços descritos no orçamento.";
  }

  return trim($tarefas);
}

function criarOrdemServico($db, $orcamentoId, $clienteId, $veiculoId, $danosJson, $observacoes) {
  $jaExiste = $db->prepare("
    SELECT id
    FROM ordens_servico
    WHERE orcamento_id = ?
    LIMIT 1
  ");
  $jaExiste->bind_param('i', $orcamentoId);
  $jaExiste->execute();

  if ($jaExiste->get_result()->fetch_assoc()) {
    return;
  }

  $numeroOS = 'OS-' . str_pad($orcamentoId, 4, '0', STR_PAD_LEFT);
  $status = 'Aguardando';
  $prioridade = 'Normal';
  $tarefas = montarTarefasOS($danosJson);
  $observacoes = $observacoes ?? '';

  $s = $db->prepare("
    INSERT INTO ordens_servico (
      orcamento_id,
      cliente_id,
      veiculo_id,
      numero_os,
      status,
      prioridade,
      tarefas,
      observacoes
    ) VALUES (?,?,?,?,?,?,?,?)
  ");

  $s->bind_param(
    'iiisssss',
    $orcamentoId,
    $clienteId,
    $veiculoId,
    $numeroOS,
    $status,
    $prioridade,
    $tarefas,
    $observacoes
  );

  $s->execute();
}

function atualizarOrdemServico($db, $orcamentoId, $clienteId, $veiculoId, $danosJson, $observacoes) {
  $s = $db->prepare("
    SELECT id
    FROM ordens_servico
    WHERE orcamento_id = ?
    LIMIT 1
  ");
  $s->bind_param('i', $orcamentoId);
  $s->execute();

  $ordem = $s->get_result()->fetch_assoc();

  if (!$ordem) {
    criarOrdemServico($db, $orcamentoId, $clienteId, $veiculoId, $danosJson, $observacoes);
    return;
  }

  $id = (int)$ordem['id'];
  $tarefas = montarTarefasOS($danosJson);
  $observacoes = $observacoes ?? '';

  $up = $db->prepare("
    UPDATE ordens_servico SET
      cliente_id = ?,
      veiculo_id = ?,
      tarefas = ?,
      observacoes = ?
    WHERE id = ?
  ");

  $up->bind_param('iissi', $clienteId, $veiculoId, $tarefas, $observacoes, $id);
  $up->execute();
}

echo json_encode(['erro' => 'Inválido']);