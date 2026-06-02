<?php
$pagina = 'gestao';
$titulo = 'Gestão da Oficina';
$subtitulo = 'Análise de faturamento por ano e mês';

require_once 'config/db.php';
$db = getDB();

$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$meses = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
    5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
    9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

$faturamentoAno = 0;
$totalVeiculosAno = 0;

$melhorAno = date('Y');
$melhorAnoValor = 0;

for($a = 2020; $a <= date('Y') + 2; $a++){

    $fatAno = $db->query("
        SELECT COALESCE(SUM(valor_total),0) total
        FROM agendamentos
        WHERE status='pronto'
        AND YEAR(data_agenda) = $a
    ")->fetch_assoc()['total'];

    if($fatAno > $melhorAnoValor){
        $melhorAnoValor = $fatAno;
        $melhorAno = $a;
    }
}

$melhorMesNome = '';
$melhorMesValor = 0;

include 'includes/topo.php';
?>

<div class="card" style="margin-bottom:20px;">
    <div class="card-body">

        <form method="GET" style="display:flex;gap:10px;align-items:center;">
            <label><strong>Ano:</strong></label>

            <select name="ano" onchange="this.form.submit()">
                <?php
                for($a = date('Y')+2; $a >= 2020; $a--):
                ?>
                    <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>>
                        <?= $a ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>

    </div>
</div>

<div class="stats-grid">

<?php

foreach($meses as $numeroMes => $nomeMes){

    $inicio = sprintf('%04d-%02d-01',$ano,$numeroMes);
    $fim    = date('Y-m-t', strtotime($inicio));

    $sqlFat = "
        SELECT COALESCE(SUM(valor_total),0) total
        FROM agendamentos
        WHERE data_agenda BETWEEN '$inicio' AND '$fim'
        AND status='pronto'
    ";

    $fatMes = $db->query($sqlFat)->fetch_assoc()['total'];

    $sqlQtd = "
        SELECT COUNT(*) qtd
        FROM agendamentos
        WHERE data_agenda BETWEEN '$inicio' AND '$fim'
    ";

    $qtdMes = $db->query($sqlQtd)->fetch_assoc()['qtd'];

    $faturamentoAno += $fatMes;
    $totalVeiculosAno += $qtdMes;

    if($fatMes > $melhorMesValor){
    $melhorMesValor = $fatMes;
    $melhorMesNome = $nomeMes;
}
?>

<a href="gestao_mes.php?ano=<?= $ano ?>&mes=<?= $numeroMes ?>"
   style="text-decoration:none;color:inherit;">

<div class="stat-card" style="
    height:220px;
    cursor:pointer;
    transition:.2s;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
">

    <div>
        <div class="stat-label" style="text-align:center;">
            <?= strtoupper($nomeMes) ?>
        </div>

        <div style="
            font-size:40px;
            text-align:center;
            margin:10px 0;
        ">
            📅
        </div>

        <div class="stat-val green" style="
            font-size:24px;
            text-align:center;
        ">
            R$ <?= number_format($fatMes,2,',','.') ?>
        </div>

        <div class="stat-sub" style="
            text-align:center;
            margin-top:10px;
        ">
            <?= $qtdMes ?> agendamentos
        </div>
    </div>

    <div style="
        padding:10px;
        background:#f8fafc;
        border-radius:8px;
        text-align:center;
        color:#f97316;
        font-weight:700;
    ">
        Abrir mês →
    </div>

</div>


</a>

<?php } ?>

</div>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Faturamento do Ano</div>
        <div class="stat-val green">
            R$ <?= number_format($faturamentoAno,2,',','.') ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Agendamentos do Ano</div>
        <div class="stat-val blue">
            <?= $totalVeiculosAno ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Ano Selecionado</div>
        <div class="stat-val orange">
            <?= $ano ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Média por Mês</div>
        <div class="stat-val">
            R$ <?= number_format($faturamentoAno/12,2,',','.') ?>
        </div>
    </div>

</div>

<?php include 'includes/rodape.php'; ?>