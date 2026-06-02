<?php

$pagina = 'gestao';
$titulo = 'Gestão Mensal';
$subtitulo = 'Semanas do mês';

require_once 'config/db.php';
$db = getDB();

$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');

$meses = [
1=>'Janeiro',
2=>'Fevereiro',
3=>'Março',
4=>'Abril',
5=>'Maio',
6=>'Junho',
7=>'Julho',
8=>'Agosto',
9=>'Setembro',
10=>'Outubro',
11=>'Novembro',
12=>'Dezembro'
];

include 'includes/topo.php';

$primeiroDia = strtotime("$ano-$mes-01");
$ultimoDia   = strtotime(date('Y-m-t', $primeiroDia));

$faturamentoMes = $db->query("
    SELECT COALESCE(SUM(valor_total),0) total
    FROM agendamentos
    WHERE status='pronto'
    AND YEAR(data_agenda) = $ano
    AND MONTH(data_agenda) = $mes
")->fetch_assoc()['total'];

$totalVeiculosMes = $db->query("
    SELECT COUNT(*) total
    FROM agendamentos
    WHERE YEAR(data_agenda) = $ano
    AND MONTH(data_agenda) = $mes
")->fetch_assoc()['total'];
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?= $meses[$mes] ?> de <?= $ano ?>
        </div>

        <a href="gestao.php?ano=<?= $ano ?>" class="btn btn-ghost btn-sm">
            ← Voltar
        </a>
    </div>

    <div class="card-body">

    <div class="stats-grid" style="margin-bottom:20px;">

    <div class="stat-card">
        <div class="stat-label">Faturamento do Mês</div>
        <div class="stat-val green">
            R$ <?= number_format($faturamentoMes,2,',','.') ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Agendamentos</div>
        <div class="stat-val blue">
            <?= $totalVeiculosMes ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mês</div>
        <div class="stat-val orange">
            <?= $meses[$mes] ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Ano</div>
        <div class="stat-val">
            <?= $ano ?>
        </div>
    </div>

</div>

<?php

$semana = 1;

for($inicioSemana = $primeiroDia;
    $inicioSemana <= $ultimoDia;
    $inicioSemana = strtotime('+7 days',$inicioSemana)) {

    $fimSemana = strtotime('+6 days',$inicioSemana);

    if($fimSemana > $ultimoDia){
        $fimSemana = $ultimoDia;
    }

    $inicio = date('Y-m-d',$inicioSemana);
    $fim    = date('Y-m-d',$fimSemana);

    $fat = $db->query("
        SELECT COALESCE(SUM(valor_total),0) total
        FROM agendamentos
        WHERE data_agenda BETWEEN '$inicio' AND '$fim'
        AND status='pronto'
    ")->fetch_assoc()['total'];

    $qtd = $db->query("
        SELECT COUNT(*) qtd
        FROM agendamentos
        WHERE data_agenda BETWEEN '$inicio' AND '$fim'
    ")->fetch_assoc()['qtd'];

?>

<div style="
margin-bottom:15px;
padding:20px;
border:1px solid #e2e8f0;
border-radius:12px;
background:white;
">

<h3>
Semana <?= $semana ?>
</h3>

<p>
<?= date('d/m',$inicioSemana) ?>
até
<?= date('d/m',$fimSemana) ?>
</p>

<p>
<strong>Veículos:</strong>
<?= $qtd ?>
</p>

<p>
<strong>Faturamento:</strong>
R$ <?= number_format($fat,2,',','.') ?>
</p>

<a
href="gestao_semana.php?ano=<?= $ano ?>&mes=<?= $mes ?>&semana=<?= $semana ?>&inicio=<?= $inicio ?>&fim=<?= $fim ?>"
class="btn btn-primary btn-sm"
>
Abrir Semana
</a>

</div>

<?php

$semana++;

}

?>

    </div>
</div>

<?php include 'includes/rodape.php'; ?>