<?php

$pagina = 'gestao';
$titulo = 'Detalhes da Semana';
$subtitulo = 'Veículos e serviços';

require_once 'config/db.php';
$db = getDB();

$inicio = $_GET['inicio'] ?? date('Y-m-d');
$fim    = $_GET['fim'] ?? date('Y-m-d');
$ano    = $_GET['ano'] ?? date('Y');
$mes    = $_GET['mes'] ?? date('m');

include 'includes/topo.php';

$sql = "
SELECT
    a.id,
    a.data_agenda,
    a.status,
    a.valor_total,
    c.nome AS cliente,
    v.placa,
    v.modelo
FROM agendamentos a
LEFT JOIN clientes c ON c.id = a.cliente_id
LEFT JOIN veiculos v ON v.id = a.veiculo_id
WHERE a.data_agenda BETWEEN '$inicio' AND '$fim'
ORDER BY a.data_agenda
";

$agendamentos = $db->query($sql);

$totalSemana = 0;
?>

<div class="card">

    <div class="card-header">

        <div class="card-title">
            Semana <?= date('d/m/Y', strtotime($inicio)) ?>
            até
            <?= date('d/m/Y', strtotime($fim)) ?>
        </div>

        <a
            href="gestao_mes.php?ano=<?= $ano ?>&mes=<?= $mes ?>"
            class="btn btn-ghost btn-sm">
            ← Voltar
        </a>

    </div>

    <div class="card-body">

<?php if($agendamentos->num_rows == 0): ?>

<div style="
padding:40px;
text-align:center;
background:#f8fafc;
border-radius:12px;
">

<h2>Nenhum veículo nesta semana</h2>

<p>
Você pode cadastrar novos agendamentos pela Agenda.
</p>

<a href="agenda.php" class="btn btn-primary">
Abrir Agenda
</a>

</div>

<?php else: ?>

<table class="tabela">

<thead>
<tr>
    <th>Data</th>
    <th>Cliente</th>
    <th>Placa</th>
    <th>Veículo</th>
    <th>Status</th>
    <th>Total</th>
</tr>
</thead>

<tbody>

<?php while($ag = $agendamentos->fetch_assoc()): ?>

<?php
$totalSemana += $ag['valor_total'];
?>

<tr>

<td>
<?= date('d/m/Y', strtotime($ag['data_agenda'])) ?>
</td>

<td>
<?= htmlspecialchars($ag['cliente']) ?>
</td>

<td>
<?= htmlspecialchars($ag['placa']) ?>
</td>

<td>
<?= htmlspecialchars($ag['modelo']) ?>
</td>

<td>

<?php

switch($ag['status']){

case 'pronto':
echo '<span class="badge badge-pronto">Pronto</span>';
break;

case 'andamento':
echo '<span class="badge badge-andamento">Andamento</span>';
break;

case 'cancelado':
echo '<span class="badge badge-cancelado">Cancelado</span>';
break;

default:
echo '<span class="badge badge-aguardando">Aguardando</span>';

}

?>

</td>

<td>
R$ <?= number_format($ag['valor_total'],2,',','.') ?>
</td>

</tr>

<tr>

<td colspan="6">

<?php

$agId = $ag['id'];

$servicos = $db->query("
SELECT descricao, valor
FROM agendamento_servicos
WHERE agendamento_id = $agId
");

if($servicos->num_rows > 0){

echo '<div style="padding:10px 0;">';

while($srv = $servicos->fetch_assoc()){

echo '
<div style="
padding:6px 10px;
margin:4px 0;
background:#f8fafc;
border-radius:8px;
display:flex;
justify-content:space-between;
">
<span>'.htmlspecialchars($srv['descricao']).'</span>
<strong>R$ '.number_format($srv['valor'],2,',','.').'</strong>
</div>
';

}

echo '</div>';

}else{

echo '<em>Nenhum serviço cadastrado.</em>';

}

?>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<div style="
margin-top:20px;
padding:20px;
background:#f97316;
color:white;
border-radius:12px;
text-align:center;
">

<h2>
Faturamento da Semana
</h2>

<h1>
R$ <?= number_format($totalSemana,2,',','.') ?>
</h1>

</div>

<?php endif; ?>

    </div>

</div>

<?php include 'includes/rodape.php'; ?>