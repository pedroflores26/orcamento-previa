<?php
$pagina    = 'dashboard';
$titulo    = 'Dashboard';
$subtitulo = 'Painel operacional da oficina';

require_once 'config/db.php';
$db = getDB();

$hoje = date('Y-m-d');
$mes  = (int)date('m');
$ano  = (int)date('Y');

$inicioMes = date('Y-m-01');
$fimMes    = date('Y-m-t');

function moeda($v){
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function dataBR($d){
    if(!$d) return '—';
    return date('d/m/Y', strtotime($d));
}

/* CLIENTES / VEÍCULOS */
$total_clientes = $db->query("SELECT COUNT(*) FROM clientes")->fetch_row()[0];
$total_veiculos = $db->query("SELECT COUNT(*) FROM veiculos")->fetch_row()[0];

/* STATUS DA OFICINA */
$total_os = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE status NOT IN ('Entregue','Cancelado')
")->fetch_row()[0];

$aguardando = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE status = 'Aguardando'
")->fetch_row()[0];

$producao = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE status IN ('Desmontagem','Funilaria','Preparação','Pintura','Montagem')
")->fetch_row()[0];

$finalizados = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE status = 'Finalizado'
")->fetch_row()[0];

$entregues_mes = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE status = 'Entregue'
    AND data_entrega BETWEEN '$inicioMes' AND '$fimMes'
")->fetch_row()[0];

$atrasados = $db->query("
    SELECT COUNT(*) FROM ordens_servico
    WHERE data_entrega IS NOT NULL
    AND data_entrega < '$hoje'
    AND status NOT IN ('Finalizado','Entregue','Cancelado')
")->fetch_row()[0];

/* FATURAMENTO */
$faturamento_mes = $db->query("
    SELECT COALESCE(SUM(total),0)
    FROM orcamentos
    WHERE data_emissao BETWEEN '$inicioMes' AND '$fimMes'
    AND (status IS NULL OR status <> 'Cancelado')
")->fetch_row()[0];

$orc_aguardando = $db->query("
    SELECT COUNT(*)
    FROM orcamentos
    WHERE status = 'Aguardando aprovação'
")->fetch_row()[0];

$orc_aprovados_mes = $db->query("
    SELECT COUNT(*)
    FROM orcamentos
    WHERE data_emissao BETWEEN '$inicioMes' AND '$fimMes'
    AND status IN ('Aprovado','Em andamento','Concluído')
")->fetch_row()[0];

/* CUSTOS / LUCRO */
$custos_mes = $db->query("
    SELECT COALESCE(SUM(valor),0)
    FROM custos_servico
    WHERE data_custo BETWEEN '$inicioMes' AND '$fimMes'
")->fetch_row()[0];

$lucro_mes = $faturamento_mes - $custos_mes;
$margem = $faturamento_mes > 0 ? ($lucro_mes / $faturamento_mes) * 100 : 0;

/* TOP CLIENTE DO MÊS */
$topCliente = $db->query("
    SELECT
        c.nome,
        COALESCE(SUM(o.total),0) AS total
    FROM orcamentos o
    LEFT JOIN clientes c ON c.id = o.cliente_id
    WHERE o.data_emissao BETWEEN '$inicioMes' AND '$fimMes'
    AND (o.status IS NULL OR o.status <> 'Cancelado')
    GROUP BY c.id, c.nome
    ORDER BY total DESC
    LIMIT 1
")->fetch_assoc();

/* FILA DA OFICINA */
$fila = $db->query("
    SELECT
        os.id,
        os.numero_os,
        os.status,
        os.prioridade,
        os.data_entrega,
        os.tarefas,
        c.nome AS cliente,
        v.placa,
        v.modelo,
        v.cor
    FROM ordens_servico os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    LEFT JOIN veiculos v ON v.id = os.veiculo_id
    WHERE os.status NOT IN ('Finalizado','Entregue','Cancelado')
    ORDER BY
        CASE os.prioridade
            WHEN 'Urgente' THEN 1
            WHEN 'Alta' THEN 2
            ELSE 3
        END,
        os.data_entrega IS NULL,
        os.data_entrega ASC,
        os.id DESC
    LIMIT 8
");

/* VEÍCULOS ATRASADOS */
$listaAtrasados = $db->query("
    SELECT
        os.id,
        os.numero_os,
        os.status,
        os.data_entrega,
        c.nome AS cliente,
        v.placa,
        v.modelo
    FROM ordens_servico os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    LEFT JOIN veiculos v ON v.id = os.veiculo_id
    WHERE os.data_entrega IS NOT NULL
    AND os.data_entrega < '$hoje'
    AND os.status NOT IN ('Finalizado','Entregue','Cancelado')
    ORDER BY os.data_entrega ASC
    LIMIT 8
");

/* ORÇAMENTOS AGUARDANDO */
$ultimosOrc = $db->query("
    SELECT
        o.id,
        o.numero,
        o.data_emissao,
        o.status,
        o.total,
        c.nome AS cliente,
        v.placa,
        v.modelo
    FROM orcamentos o
    LEFT JOIN clientes c ON c.id = o.cliente_id
    LEFT JOIN veiculos v ON v.id = o.veiculo_id
    WHERE o.status = 'Aguardando aprovação'
    ORDER BY o.id DESC
    LIMIT 6
");

/* SERVIÇOS MAIS FEITOS - baseado nas áreas marcadas no campo danos */
$servicosMais = [
    'Funilaria' => 0,
    'Pintura' => 0,
    'Para-choque' => 0,
    'Porta' => 0,
    'Preparação' => 0
];

$resDanos = $db->query("
    SELECT danos
    FROM orcamentos
    WHERE data_emissao BETWEEN '$inicioMes' AND '$fimMes'
    AND danos IS NOT NULL
    AND danos <> ''
");

while($row = $resDanos->fetch_assoc()){
    $json = json_decode($row['danos'], true);

    if(!is_array($json)) continue;

    $texto = strtolower(json_encode($json, JSON_UNESCAPED_UNICODE));

    if(str_contains($texto, 'funilaria') || str_contains($texto, 'paralama') || str_contains($texto, 'capô')){
        $servicosMais['Funilaria']++;
    }

    if(str_contains($texto, 'pintura') || str_contains($texto, 'tinta')){
        $servicosMais['Pintura']++;
    }

    if(str_contains($texto, 'para-choque')){
        $servicosMais['Para-choque']++;
    }

    if(str_contains($texto, 'porta')){
        $servicosMais['Porta']++;
    }

    if(str_contains($texto, 'preparação') || str_contains($texto, 'preparacao')){
        $servicosMais['Preparação']++;
    }
}

arsort($servicosMais);

function badgeOS($status){
    $classe = 'badge-orange';

    if(in_array($status, ['Finalizado','Entregue'])){
        $classe = 'badge-pronto';
    } elseif(in_array($status, ['Desmontagem','Funilaria','Preparação','Pintura','Montagem'])){
        $classe = 'badge-andamento';
    } elseif($status === 'Cancelado'){
        $classe = 'badge-cancelado';
    }

    return "<span class='badge $classe'>" . htmlspecialchars($status ?: 'Aguardando') . "</span>";
}

include 'includes/topo.php';
?>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Veículos na oficina</div>
        <div class="stat-val"><?= $total_os ?></div>
        <div class="stat-sub">OS abertas no momento</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Aguardando início</div>
        <div class="stat-val orange"><?= $aguardando ?></div>
        <div class="stat-sub">Carros ainda não iniciados</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Em produção</div>
        <div class="stat-val blue"><?= $producao ?></div>
        <div class="stat-sub">Funilaria, preparação, pintura ou montagem</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Finalizados</div>
        <div class="stat-val green"><?= $finalizados ?></div>
        <div class="stat-sub">Prontos para entrega</div>
    </div>

</div>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Faturamento do mês</div>
        <div class="stat-val green"><?= moeda($faturamento_mes) ?></div>
        <div class="stat-sub"><?= $orc_aprovados_mes ?> orçamentos aprovados/em produção</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Custos do mês</div>
        <div class="stat-val orange"><?= moeda($custos_mes) ?></div>
        <div class="stat-sub">Peças, materiais, mão de obra e terceiros</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Lucro estimado</div>
        <div class="stat-val <?= $lucro_mes >= 0 ? 'green' : 'orange' ?>"><?= moeda($lucro_mes) ?></div>
        <div class="stat-sub">Margem: <?= number_format($margem,1,',','.') ?>%</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Veículos atrasados</div>
        <div class="stat-val <?= $atrasados > 0 ? 'orange' : 'green' ?>"><?= $atrasados ?></div>
        <div class="stat-sub">Com prazo vencido</div>
    </div>

</div>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">Orçamentos aguardando</div>
        <div class="stat-val orange"><?= $orc_aguardando ?></div>
        <div class="stat-sub">Aguardando aprovação do cliente</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Entregues no mês</div>
        <div class="stat-val green"><?= $entregues_mes ?></div>
        <div class="stat-sub">Veículos marcados como entregues</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Clientes cadastrados</div>
        <div class="stat-val"><?= $total_clientes ?></div>
        <div class="stat-sub">
            <a href="clientes.php" style="color:var(--orange)">Ver clientes →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Veículos cadastrados</div>
        <div class="stat-val blue"><?= $total_veiculos ?></div>
        <div class="stat-sub">
            <a href="veiculos.php" style="color:var(--orange)">Ver veículos →</a>
        </div>
    </div>

</div>

<div class="g2">

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon">🏁</div>
                <div class="card-title">Fila da oficina</div>
            </div>

            <a href="ordens.php" class="btn btn-primary btn-sm">Ver OS</a>
        </div>

        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Veículo</th>
                            <th>Placa</th>
                            <th>Status</th>
                            <th>Entrega</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if($fila->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;padding:25px;color:var(--g400);">
                                    Nenhum veículo em produção.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php while($f = $fila->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($f['modelo'] ?: 'Veículo') ?></strong><br>
                                    <small><?= htmlspecialchars($f['cliente'] ?: 'Cliente') ?></small>
                                </td>

                                <td><strong><?= htmlspecialchars($f['placa'] ?: '—') ?></strong></td>

                                <td><?= badgeOS($f['status']) ?></td>

                                <td><?= dataBR($f['data_entrega']) ?></td>
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
                <div class="card-icon">⚠️</div>
                <div class="card-title">Veículos atrasados</div>
            </div>
        </div>

        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Veículo</th>
                            <th>Placa</th>
                            <th>Status</th>
                            <th>Entrega</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if($listaAtrasados->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;padding:25px;color:var(--g400);">
                                    Nenhum veículo atrasado.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php while($a = $listaAtrasados->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($a['modelo'] ?: 'Veículo') ?></strong><br>
                                    <small><?= htmlspecialchars($a['cliente'] ?: 'Cliente') ?></small>
                                </td>

                                <td><strong><?= htmlspecialchars($a['placa'] ?: '—') ?></strong></td>

                                <td><?= badgeOS($a['status']) ?></td>

                                <td><strong style="color:var(--red);"><?= dataBR($a['data_entrega']) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="g2">

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon">🥇</div>
                <div class="card-title">Cliente que mais rendeu no mês</div>
            </div>
        </div>

        <div class="card-body">
            <?php if($topCliente && $topCliente['total'] > 0): ?>
                <div style="font-size:26px;font-weight:900;color:var(--g800);">
                    <?= htmlspecialchars($topCliente['nome'] ?: 'Cliente não informado') ?>
                </div>

                <div style="font-size:34px;font-weight:900;color:var(--green);margin-top:8px;">
                    <?= moeda($topCliente['total']) ?>
                </div>

                <div style="color:var(--g500);margin-top:6px;">
                    Maior faturamento registrado neste mês.
                </div>
            <?php else: ?>
                <div style="color:var(--g400);">
                    Nenhum faturamento registrado neste mês.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-icon">🛠️</div>
                <div class="card-title">Serviços mais recorrentes</div>
            </div>
        </div>

        <div class="card-body">
            <?php foreach($servicosMais as $nome=>$qtd): ?>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--g100);padding:10px 0;">
                    <strong><?= htmlspecialchars($nome) ?></strong>
                    <span class="badge badge-orange"><?= (int)$qtd ?>x</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <div class="card-icon">📄</div>
            <div class="card-title">Pré-orçamentos aguardando aprovação</div>
        </div>

        <a href="orcamentos.php" class="btn btn-primary btn-sm">Ver orçamentos</a>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if($ultimosOrc->num_rows == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:25px;color:var(--g400);">
                                Nenhum pré-orçamento aguardando aprovação.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php while($o = $ultimosOrc->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($o['numero'] ?: $o['id']) ?></strong></td>
                            <td><?= dataBR($o['data_emissao']) ?></td>
                            <td><?= htmlspecialchars($o['cliente'] ?: 'Cliente') ?></td>
                            <td><?= htmlspecialchars($o['modelo'] ?: 'Veículo') ?></td>
                            <td><?= htmlspecialchars($o['placa'] ?: '—') ?></td>
                            <td><strong><?= moeda($o['total']) ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/rodape.php'; ?>