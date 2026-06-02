<?php
$pagina   = 'dashboard';
$titulo   = 'Dashboard';
$subtitulo= 'Visão geral da oficina';

require_once 'config/db.php';
$db = getDB();

$total_clientes = $db->query("
    SELECT COUNT(*) FROM clientes
")->fetch_row()[0];

$total_veiculos = $db->query("
    SELECT COUNT(*) FROM veiculos
")->fetch_row()[0];

$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fim_semana    = date('Y-m-d', strtotime('sunday this week'));

$ag_semana = $db->query("
    SELECT COUNT(*)
    FROM agendamentos
    WHERE data_agenda BETWEEN '$inicio_semana' AND '$fim_semana'
")->fetch_row()[0];

$fat_semana = $db->query("
    SELECT COALESCE(SUM(valor_total),0)
    FROM agendamentos
    WHERE data_agenda BETWEEN '$inicio_semana' AND '$fim_semana'
    AND status = 'pronto'
")->fetch_row()[0];

$mes_atual = date('m');
$ano_atual = date('Y');

$fat_mes = $db->query("
    SELECT COALESCE(SUM(valor_total),0)
    FROM agendamentos
    WHERE MONTH(data_agenda) = $mes_atual
    AND YEAR(data_agenda) = $ano_atual
    AND status = 'pronto'
")->fetch_row()[0];

$servicos_mes = $db->query("
    SELECT COUNT(*)
    FROM agendamentos
    WHERE MONTH(data_agenda) = $mes_atual
    AND YEAR(data_agenda) = $ano_atual
")->fetch_row()[0];

$total_orc = $db->query("
    SELECT COUNT(*) FROM orcamentos
")->fetch_row()[0];

$ultimos_ag = $db->query("
    SELECT
        a.data_agenda,
        a.hora,
        a.status,
        a.valor_total,
        c.nome AS cliente,
        v.placa,
        v.modelo
    FROM agendamentos a
    LEFT JOIN clientes c ON a.cliente_id = c.id
    LEFT JOIN veiculos v ON a.veiculo_id = v.id
    ORDER BY a.data_agenda DESC, a.hora DESC
    LIMIT 8
");

function fmtMoeda($v){
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function fmtData($d){
    if(!$d) return '—';

    $p = explode('-', $d);

    return $p[2].'/'.$p[1].'/'.$p[0];
}

function badgeStatus($s){

    $map = [
        'aguardando' => '⏳ Aguardando',
        'andamento'  => '🔧 Em andamento',
        'pronto'     => '✅ Pronto',
        'cancelado'  => '❌ Cancelado'
    ];

    return "<span class='badge badge-{$s}'>" . ($map[$s] ?? $s) . "</span>";
}

include 'includes/topo.php';
?>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Clientes cadastrados</div>
        <div class="stat-val"><?= $total_clientes ?></div>
        <div class="stat-sub">
            <a href="clientes.php" style="color:var(--orange)">
                Ver clientes →
            </a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Veículos cadastrados</div>
        <div class="stat-val blue"><?= $total_veiculos ?></div>
        <div class="stat-sub">
            <a href="veiculos.php" style="color:var(--orange)">
                Ver veículos →
            </a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Agendamentos esta semana</div>
        <div class="stat-val orange"><?= $ag_semana ?></div>
        <div class="stat-sub">
            <a href="agenda.php" style="color:var(--orange)">
                Ver agenda →
            </a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Faturamento do mês</div>
        <div class="stat-val green"><?= fmtMoeda($fat_mes) ?></div>
        <div class="stat-sub">
            <?= $servicos_mes ?> serviços cadastrados neste mês
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Faturamento da semana</div>
        <div class="stat-val orange"><?= fmtMoeda($fat_semana) ?></div>
        <div class="stat-sub">
            Serviços concluídos
        </div>
    </div>

</div>

<div class="card">

    <div class="card-header">

        <div class="card-header-left">
            <div class="card-icon">📅</div>
            <div class="card-title">Últimos agendamentos</div>
        </div>

        <a href="agenda.php" class="btn btn-primary btn-sm">
            Ver agenda completa
        </a>

    </div>

    <div class="card-body" style="padding:0">

        <div class="table-wrap">

            <table class="tabela">

                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($r = $ultimos_ag->fetch_assoc()): ?>

                    <tr>

                        <td><?= fmtData($r['data_agenda']) ?></td>

                        <td><?= $r['hora'] ?: '—' ?></td>

                        <td><?= htmlspecialchars($r['cliente'] ?? '—') ?></td>

                        <td><?= htmlspecialchars($r['modelo'] ?? '—') ?></td>

                        <td><?= htmlspecialchars($r['placa'] ?? '—') ?></td>

                        <td><?= badgeStatus($r['status']) ?></td>

                        <td>
                            <?= $r['valor_total'] > 0 ? fmtMoeda($r['valor_total']) : '—' ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include 'includes/rodape.php'; ?>