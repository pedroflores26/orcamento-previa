<?php
$pagina = 'clientes_rentabilidade';
$titulo = 'Rentabilidade por Cliente';
$subtitulo = 'Ranking mensal e média anual';

require_once 'config/db.php';
$db = getDB();

$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');

if ($mes < 1 || $mes > 12) {
  $mes = (int)date('m');
}

$meses = [
  1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
  5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
  9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

$nomeMes = $meses[(int)$mes] ?? 'Mês inválido';

function moeda($v){
  return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

$inicioMes = sprintf('%04d-%02d-01', $ano, $mes);
$fimMes = date('Y-m-t', strtotime($inicioMes));

$sqlMes = "
SELECT
  c.id,
  COALESCE(c.nome, 'Cliente não informado') AS nome,
  COUNT(o.id) AS qtd_orcamentos,
  COUNT(DISTINCT o.veiculo_id) AS qtd_veiculos,
  COALESCE(SUM(o.total),0) AS total_mes,
  COALESCE(AVG(o.total),0) AS ticket_medio
FROM orcamentos o
LEFT JOIN clientes c ON c.id = o.cliente_id
WHERE o.data_emissao BETWEEN ? AND ?
AND (o.status IS NULL OR o.status <> 'Cancelado')
GROUP BY c.id, c.nome
ORDER BY total_mes DESC
";

$stmtMes = $db->prepare($sqlMes);
$stmtMes->bind_param('ss', $inicioMes, $fimMes);
$stmtMes->execute();
$rankingMes = $stmtMes->get_result();

$sqlResumo = "
SELECT
  COALESCE(SUM(o.total),0) AS total_ano,
  COUNT(o.id) AS qtd_ano,
  COALESCE(AVG(o.total),0) AS ticket_ano
FROM orcamentos o
WHERE YEAR(o.data_emissao) = ?
AND (o.status IS NULL OR o.status <> 'Cancelado')
";

$stmtResumo = $db->prepare($sqlResumo);
$stmtResumo->bind_param('i', $ano);
$stmtResumo->execute();
$resumo = $stmtResumo->get_result()->fetch_assoc();

$sqlAnual = "
SELECT
  COALESCE(c.nome, 'Cliente não informado') AS nome,
  COUNT(o.id) AS qtd_orcamentos,
  COUNT(DISTINCT o.veiculo_id) AS qtd_veiculos,
  COALESCE(SUM(o.total),0) AS total_ano,
  COALESCE(SUM(o.total) / 12,0) AS media_mensal,
  COALESCE(AVG(o.total),0) AS ticket_medio
FROM orcamentos o
LEFT JOIN clientes c ON c.id = o.cliente_id
WHERE YEAR(o.data_emissao) = ?
AND (o.status IS NULL OR o.status <> 'Cancelado')
GROUP BY c.id, c.nome
ORDER BY total_ano DESC
LIMIT 20
";

$stmtAnual = $db->prepare($sqlAnual);
$stmtAnual->bind_param('i', $ano);
$stmtAnual->execute();
$rankingAnual = $stmtAnual->get_result();

include 'includes/topo.php';
?>

<div class="card">
  <div class="card-body">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">

      <div class="field" style="margin:0;">
        <label>Mês</label>
        <select name="mes">
          <?php foreach($meses as $num=>$nome): ?>
            <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>>
              <?= $nome ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field" style="margin:0;">
        <label>Ano</label>
        <select name="ano">
          <?php for($a = date('Y') + 2; $a >= 2020; $a--): ?>
            <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>>
              <?= $a ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <button class="btn btn-primary" type="submit">Filtrar</button>

    </form>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label">Faturamento do Ano</div>
    <div class="stat-val green"><?= moeda($resumo['total_ano'] ?? 0) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Média Mensal do Ano</div>
    <div class="stat-val orange"><?= moeda(($resumo['total_ano'] ?? 0) / 12) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Orçamentos no Ano</div>
    <div class="stat-val blue"><?= (int)($resumo['qtd_ano'] ?? 0) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Ticket Médio Geral</div>
    <div class="stat-val"><?= moeda($resumo['ticket_ano'] ?? 0) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-header-left">
      <div class="card-icon">🏆</div>
      <div class="card-title">Quem mais rendeu em <?= htmlspecialchars($nomeMes) ?> de <?= $ano ?></div>
    </div>
  </div>

  <div class="card-body" style="padding:0;">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr>
            <th>Posição</th>
            <th>Cliente / Revenda</th>
            <th>Veículos</th>
            <th>Orçamentos</th>
            <th>Total do Mês</th>
            <th>Ticket Médio</th>
          </tr>
        </thead>

        <tbody>
          <?php if($rankingMes->num_rows == 0): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:25px;color:var(--g400);">
                Nenhum faturamento encontrado neste mês.
              </td>
            </tr>
          <?php endif; ?>

          <?php $pos=1; while($r = $rankingMes->fetch_assoc()): ?>
            <tr>
              <td><strong>#<?= $pos++ ?></strong></td>
              <td><strong><?= htmlspecialchars($r['nome'] ?: 'Cliente não informado') ?></strong></td>
              <td><?= (int)$r['qtd_veiculos'] ?></td>
              <td><?= (int)$r['qtd_orcamentos'] ?></td>
              <td><strong style="color:var(--green);"><?= moeda($r['total_mes']) ?></strong></td>
              <td><?= moeda($r['ticket_medio']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-header-left">
      <div class="card-icon">📊</div>
      <div class="card-title">Média anual por cliente — <?= $ano ?></div>
    </div>
  </div>

  <div class="card-body" style="padding:0;">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr>
            <th>Cliente / Revenda</th>
            <th>Veículos</th>
            <th>Orçamentos</th>
            <th>Total no Ano</th>
            <th>Média Mensal</th>
            <th>Ticket Médio</th>
          </tr>
        </thead>

        <tbody>
          <?php if($rankingAnual->num_rows == 0): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:25px;color:var(--g400);">
                Nenhum faturamento encontrado neste ano.
              </td>
            </tr>
          <?php endif; ?>

          <?php while($r = $rankingAnual->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['nome'] ?: 'Cliente não informado') ?></strong></td>
              <td><?= (int)$r['qtd_veiculos'] ?></td>
              <td><?= (int)$r['qtd_orcamentos'] ?></td>
              <td><strong style="color:var(--green);"><?= moeda($r['total_ano']) ?></strong></td>
              <td><?= moeda($r['media_mensal']) ?></td>
              <td><?= moeda($r['ticket_medio']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/rodape.php'; ?>