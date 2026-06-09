<?php
$pagina = 'custos';
$titulo = 'Custos e Lucro';
$subtitulo = 'Lucro semanal e mensal pela Gestão da Oficina';

require_once 'config/db.php';
$db = getDB();

$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');

if($mes < 1 || $mes > 12){
    $mes = (int)date('m');
}

$meses = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
    5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
    9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

function moeda($v){
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function dataBR($d){
    return $d ? date('d/m/Y', strtotime($d)) : '—';
}

function semanaDoMes($data){
    $dia = (int)date('d', strtotime($data));
    return (int)ceil($dia / 7);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $acao = $_POST['acao'] ?? '';

    if($acao === 'delete'){
        $id = (int)($_POST['id'] ?? 0);

        $s = $db->prepare("DELETE FROM custos_servico WHERE id=?");
        $s->bind_param('i', $id);
        $s->execute();

        header("Location: custos.php?ano=$ano&mes=$mes");
        exit;
    }

    $tipo        = $_POST['tipo'] ?? '';
    $descricao   = trim($_POST['descricao'] ?? '');
    $valor       = (float)($_POST['valor'] ?? 0);
    $data        = $_POST['data_custo'] ?? date('Y-m-d');
    $orcamentoId = !empty($_POST['orcamento_id']) ? (int)$_POST['orcamento_id'] : null;
    $ordemId     = !empty($_POST['ordem_id']) ? (int)$_POST['ordem_id'] : null;

    if($tipo && $descricao && $valor > 0){
        $s = $db->prepare("
            INSERT INTO custos_servico
            (orcamento_id, ordem_id, tipo, descricao, valor, data_custo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $s->bind_param(
            'iissds',
            $orcamentoId,
            $ordemId,
            $tipo,
            $descricao,
            $valor,
            $data
        );

        $s->execute();
    }

    header("Location: custos.php?ano=$ano&mes=$mes");
    exit;
}

$inicio = sprintf('%04d-%02d-01', $ano, $mes);
$fim = date('Y-m-t', strtotime($inicio));

/* FATURAMENTO VINDO DA GESTÃO DA OFICINA */
$faturamentoMes = $db->query("
    SELECT COALESCE(SUM(valor_total),0) AS total
    FROM agendamentos
    WHERE data_agenda BETWEEN '$inicio' AND '$fim'
    AND (status IS NULL OR status <> 'cancelado')
")->fetch_assoc()['total'];

/* CUSTOS DO MÊS */
$totalCustosMes = $db->query("
    SELECT COALESCE(SUM(valor),0) AS total
    FROM custos_servico
    WHERE data_custo BETWEEN '$inicio' AND '$fim'
")->fetch_assoc()['total'];

$lucroMes = $faturamentoMes - $totalCustosMes;
$margemMes = $faturamentoMes > 0 ? ($lucroMes / $faturamentoMes) * 100 : 0;

/* RESUMO POR TIPO */
$resumoTipos = [
    'Peça'=>0,
    'Material'=>0,
    'Mão de obra'=>0,
    'Terceiro'=>0,
    'Outros'=>0
];

$custosResumo = $db->query("
    SELECT tipo, COALESCE(SUM(valor),0) total
    FROM custos_servico
    WHERE data_custo BETWEEN '$inicio' AND '$fim'
    GROUP BY tipo
");

while($r = $custosResumo->fetch_assoc()){
    $resumoTipos[$r['tipo']] = $r['total'];
}

/* SEMANAS DO MÊS */
$semanas = [];

for($i = 1; $i <= 5; $i++){
    $semInicioDia = (($i - 1) * 7) + 1;
    $semFimDia = min($i * 7, (int)date('t', strtotime($inicio)));

    if($semInicioDia > (int)date('t', strtotime($inicio))){
        continue;
    }

    $iniSemana = sprintf('%04d-%02d-%02d', $ano, $mes, $semInicioDia);
    $fimSemana = sprintf('%04d-%02d-%02d', $ano, $mes, $semFimDia);

    $fatSemana = $db->query("
        SELECT COALESCE(SUM(valor_total),0) AS total
        FROM agendamentos
        WHERE data_agenda BETWEEN '$iniSemana' AND '$fimSemana'
        AND (status IS NULL OR status <> 'cancelado')
    ")->fetch_assoc()['total'];

    $custosSemana = $db->query("
        SELECT COALESCE(SUM(valor),0) AS total
        FROM custos_servico
        WHERE data_custo BETWEEN '$iniSemana' AND '$fimSemana'
    ")->fetch_assoc()['total'];

    $custosGeraisSemana = $db->query("
        SELECT COALESCE(SUM(valor),0) AS total
        FROM custos_servico
        WHERE data_custo BETWEEN '$iniSemana' AND '$fimSemana'
        AND orcamento_id IS NULL
        AND ordem_id IS NULL
    ")->fetch_assoc()['total'];

    $custosVeiculosSemana = $custosSemana - $custosGeraisSemana;
    $lucroSemana = $fatSemana - $custosSemana;

    $semanas[] = [
        'num' => $i,
        'inicio' => $iniSemana,
        'fim' => $fimSemana,
        'faturamento' => $fatSemana,
        'custos' => $custosSemana,
        'custos_gerais' => $custosGeraisSemana,
        'custos_veiculos' => $custosVeiculosSemana,
        'lucro' => $lucroSemana
    ];
}

/* LISTAGEM DE CUSTOS */
$custos = $db->query("
    SELECT 
        cs.*,
        o.numero AS numero_orcamento,
        c.nome AS cliente,
        v.modelo,
        v.placa,
        os.numero_os
    FROM custos_servico cs
    LEFT JOIN orcamentos o ON o.id = cs.orcamento_id
    LEFT JOIN clientes c ON c.id = o.cliente_id
    LEFT JOIN veiculos v ON v.id = o.veiculo_id
    LEFT JOIN ordens_servico os ON os.id = cs.ordem_id
    WHERE cs.data_custo BETWEEN '$inicio' AND '$fim'
    ORDER BY cs.data_custo DESC, cs.id DESC
");

/* LUCRO POR VEÍCULO */
$lucroPorOrcamento = $db->query("
    SELECT
        o.id,
        o.numero,
        o.total AS faturado,
        c.nome AS cliente,
        v.modelo,
        v.placa,
        COALESCE(custos.total_custos,0) AS custos,
        (o.total - COALESCE(custos.total_custos,0)) AS lucro,
        CASE
            WHEN o.total > 0 THEN ((o.total - COALESCE(custos.total_custos,0)) / o.total) * 100
            ELSE 0
        END AS margem
    FROM orcamentos o
    LEFT JOIN clientes c ON c.id = o.cliente_id
    LEFT JOIN veiculos v ON v.id = o.veiculo_id
    LEFT JOIN (
        SELECT
            COALESCE(cs.orcamento_id, os.orcamento_id) AS orcamento_ref,
            SUM(cs.valor) AS total_custos
        FROM custos_servico cs
        LEFT JOIN ordens_servico os ON os.id = cs.ordem_id
        WHERE cs.data_custo BETWEEN '$inicio' AND '$fim'
        GROUP BY COALESCE(cs.orcamento_id, os.orcamento_id)
    ) custos ON custos.orcamento_ref = o.id
    WHERE o.data_emissao BETWEEN '$inicio' AND '$fim'
    AND (o.status IS NULL OR o.status <> 'Cancelado')
    ORDER BY lucro DESC
");

/* SELECTS DO MODAL */
$orcamentos = $db->query("
    SELECT 
        o.id,
        o.numero,
        o.total,
        c.nome AS cliente,
        v.modelo,
        v.placa
    FROM orcamentos o
    LEFT JOIN clientes c ON c.id = o.cliente_id
    LEFT JOIN veiculos v ON v.id = o.veiculo_id
    ORDER BY o.id DESC
    LIMIT 300
");

$ordens = $db->query("
    SELECT 
        os.id,
        os.numero_os,
        o.numero AS numero_orcamento,
        c.nome AS cliente,
        v.modelo,
        v.placa
    FROM ordens_servico os
    LEFT JOIN orcamentos o ON o.id = os.orcamento_id
    LEFT JOIN clientes c ON c.id = os.cliente_id
    LEFT JOIN veiculos v ON v.id = os.veiculo_id
    ORDER BY os.id DESC
    LIMIT 300
");

include 'includes/topo.php';
?>

<div class="card">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="margin:0;">
                <label>Mês</label>
                <select name="mes">
                    <?php foreach($meses as $n=>$nome): ?>
                        <option value="<?= $n ?>" <?= $n == $mes ? 'selected' : '' ?>>
                            <?= $nome ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field" style="margin:0;">
                <label>Ano</label>
                <select name="ano">
                    <?php for($a = date('Y')+2; $a >= 2020; $a--): ?>
                        <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>>
                            <?= $a ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button class="btn btn-primary" type="submit">Filtrar</button>

            <button class="btn btn-success" type="button" onclick="abrirModal('modal-custo')">
                ＋ Novo custo
            </button>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Faturamento do mês</div>
        <div class="stat-val green"><?= moeda($faturamentoMes) ?></div>
        <div class="stat-sub">Puxado da Gestão da Oficina</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Custos do mês</div>
        <div class="stat-val orange"><?= moeda($totalCustosMes) ?></div>
        <div class="stat-sub">Custos por veículo + gerais</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Lucro do mês</div>
        <div class="stat-val <?= $lucroMes >= 0 ? 'green' : 'orange' ?>">
            <?= moeda($lucroMes) ?>
        </div>
        <div class="stat-sub">Margem: <?= number_format($margemMes,1,',','.') ?>%</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mês analisado</div>
        <div class="stat-val blue"><?= $meses[$mes] ?></div>
        <div class="stat-sub"><?= $ano ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <div class="card-icon">📆</div>
            <div class="card-title">Resultado semanal — <?= $meses[$mes] ?> de <?= $ano ?></div>
        </div>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Semana</th>
                        <th>Período</th>
                        <th>Faturamento</th>
                        <th>Custos veículos</th>
                        <th>Custos gerais</th>
                        <th>Total custos</th>
                        <th>Lucro</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($semanas as $s): ?>
                        <tr>
                            <td><strong>Semana <?= $s['num'] ?></strong></td>
                            <td><?= dataBR($s['inicio']) ?> até <?= dataBR($s['fim']) ?></td>
                            <td><strong style="color:var(--green);"><?= moeda($s['faturamento']) ?></strong></td>
                            <td><?= moeda($s['custos_veiculos']) ?></td>
                            <td><?= moeda($s['custos_gerais']) ?></td>
                            <td><strong style="color:var(--orange);"><?= moeda($s['custos']) ?></strong></td>
                            <td>
                                <strong style="color:<?= $s['lucro'] >= 0 ? 'var(--green)' : 'var(--red)' ?>;">
                                    <?= moeda($s['lucro']) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<div class="stats-grid">
    <?php foreach($resumoTipos as $tipo=>$valor): ?>
        <div class="stat-card">
            <div class="stat-label"><?= htmlspecialchars($tipo) ?></div>
            <div class="stat-val" style="font-size:22px;"><?= moeda($valor) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <div class="card-icon">💸</div>
            <div class="card-title">Custos lançados — <?= $meses[$mes] ?> de <?= $ano ?></div>
        </div>

        <button class="btn btn-primary btn-sm" onclick="abrirModal('modal-custo')">＋ Novo custo</button>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Semana</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Vínculo</th>
                        <th>Cliente / Veículo</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if($custos->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:25px;color:var(--g400);">
                                Nenhum custo lançado neste mês.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php while($c = $custos->fetch_assoc()): ?>
                        <?php
                            $vinculo = 'Custo geral';
                            if($c['numero_orcamento']){
                                $vinculo = 'Orçamento #'.$c['numero_orcamento'];
                            }
                            if($c['numero_os']){
                                $vinculo = 'OS '.$c['numero_os'];
                            }
                        ?>

                        <tr>
                            <td><?= dataBR($c['data_custo']) ?></td>
                            <td>Semana <?= semanaDoMes($c['data_custo']) ?></td>
                            <td><span class="badge badge-orange"><?= htmlspecialchars($c['tipo']) ?></span></td>
                            <td><?= htmlspecialchars($c['descricao']) ?></td>
                            <td><?= htmlspecialchars($vinculo) ?></td>
                            <td>
                                <?= htmlspecialchars($c['cliente'] ?: '—') ?>
                                <br>
                                <small>
                                    <?= htmlspecialchars($c['modelo'] ?: '') ?>
                                    <?= $c['placa'] ? ' — '.htmlspecialchars($c['placa']) : '' ?>
                                </small>
                            </td>
                            <td><strong><?= moeda($c['valor']) ?></strong></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Excluir este custo?')" style="display:inline;">
                                    <input type="hidden" name="acao" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button class="btn btn-danger btn-xs" type="submit">🗑 Excluir</button>
                                </form>
                            </td>
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
            <div class="card-icon">🚗</div>
            <div class="card-title">Lucro por veículo / orçamento</div>
        </div>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Orçamento</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Placa</th>
                        <th>Faturado</th>
                        <th>Custos</th>
                        <th>Lucro</th>
                        <th>Margem</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if($lucroPorOrcamento->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:25px;color:var(--g400);">
                                Nenhum orçamento encontrado neste mês.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php while($l = $lucroPorOrcamento->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($l['numero'] ?: $l['id']) ?></strong></td>
                            <td><?= htmlspecialchars($l['cliente'] ?: 'Cliente') ?></td>
                            <td><?= htmlspecialchars($l['modelo'] ?: 'Veículo') ?></td>
                            <td><?= htmlspecialchars($l['placa'] ?: '—') ?></td>
                            <td><strong style="color:var(--green);"><?= moeda($l['faturado']) ?></strong></td>
                            <td><strong style="color:var(--orange);"><?= moeda($l['custos']) ?></strong></td>
                            <td>
                                <strong style="color:<?= $l['lucro'] >= 0 ? 'var(--green)' : 'var(--red)' ?>;">
                                    <?= moeda($l['lucro']) ?>
                                </strong>
                            </td>
                            <td><?= number_format($l['margem'],1,',','.') ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-custo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-title">💸 Novo custo</div>
            <button class="modal-close" onclick="fecharModal('modal-custo')">✕</button>
        </div>

        <form method="POST">
            <div class="modal-body">

                <div class="field">
                    <label>Tipo</label>
                    <select name="tipo" required>
                        <option value="">Selecione...</option>
                        <option>Peça</option>
                        <option>Material</option>
                        <option>Mão de obra</option>
                        <option>Terceiro</option>
                        <option>Outros</option>
                    </select>
                </div>

                <div class="field">
                    <label>Descrição</label>
                    <input type="text" name="descricao" placeholder="Ex: Material semanal, lixa, tinta, verniz, peça..." required>
                </div>

                <div class="field">
                    <label>Valor</label>
                    <input type="number" name="valor" min="0" step="0.01" placeholder="0,00" required>
                </div>

                <div class="field">
                    <label>Data do custo</label>
                    <input type="date" name="data_custo" value="<?= date('Y-m-d') ?>" required>
                    <small>Essa data define em qual semana o custo entra.</small>
                </div>

                <div class="field">
                    <label>Vincular ao orçamento / veículo</label>
                    <select name="orcamento_id">
                        <option value="">Custo geral da oficina / semana</option>

                        <?php while($o = $orcamentos->fetch_assoc()): ?>
                            <option value="<?= $o['id'] ?>">
                                #<?= htmlspecialchars($o['numero'] ?: $o['id']) ?>
                                — <?= htmlspecialchars($o['cliente'] ?: 'Cliente') ?>
                                — <?= htmlspecialchars($o['modelo'] ?: 'Veículo') ?>
                                <?= $o['placa'] ? ' — '.htmlspecialchars($o['placa']) : '' ?>
                                — <?= moeda($o['total']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Vincular à OS</label>
                    <select name="ordem_id">
                        <option value="">Não vincular à OS</option>

                        <?php while($os = $ordens->fetch_assoc()): ?>
                            <option value="<?= $os['id'] ?>">
                                <?= htmlspecialchars($os['numero_os'] ?: 'OS #'.$os['id']) ?>
                                <?= $os['numero_orcamento'] ? ' — Orçamento #'.htmlspecialchars($os['numero_orcamento']) : '' ?>
                                — <?= htmlspecialchars($os['cliente'] ?: 'Cliente') ?>
                                — <?= htmlspecialchars($os['modelo'] ?: 'Veículo') ?>
                                <?= $os['placa'] ? ' — '.htmlspecialchars($os['placa']) : '' ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;font-size:13px;color:#475569;">
                    <strong>Dica:</strong><br>
                    Para lançar gasto semanal de material, deixe sem vínculo com orçamento e OS.
                    Exemplo: Tipo <strong>Material</strong>, descrição <strong>Material semana 1</strong>, valor <strong>1000</strong>.
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="fecharModal('modal-custo')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 Salvar custo</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/rodape.php'; ?>