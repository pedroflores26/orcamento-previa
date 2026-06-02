<?php
$pagina = 'custos';
$titulo = 'Custos e Lucro';
$subtitulo = 'Controle de peças, materiais, mão de obra e lucro por veículo';

require_once 'config/db.php';
$db = getDB();

$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');

function moeda($v){
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

$meses = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
    5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
    9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

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

    if($tipo && $descricao){
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

$faturamento = $db->query("
    SELECT COALESCE(SUM(total),0) total
    FROM orcamentos
    WHERE data_emissao BETWEEN '$inicio' AND '$fim'
    AND (status IS NULL OR status <> 'Cancelado')
")->fetch_assoc()['total'];

$custosResumo = $db->query("
    SELECT tipo, COALESCE(SUM(valor),0) total
    FROM custos_servico
    WHERE data_custo BETWEEN '$inicio' AND '$fim'
    GROUP BY tipo
");

$totalCustos = 0;
$resumoTipos = [
    'Peça'=>0,
    'Material'=>0,
    'Mão de obra'=>0,
    'Terceiro'=>0,
    'Outros'=>0
];

while($r = $custosResumo->fetch_assoc()){
    $resumoTipos[$r['tipo']] = $r['total'];
    $totalCustos += $r['total'];
}

$lucro = $faturamento - $totalCustos;
$margem = $faturamento > 0 ? ($lucro / $faturamento) * 100 : 0;

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
        <div class="stat-label">Faturamento</div>
        <div class="stat-val green"><?= moeda($faturamento) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Custos</div>
        <div class="stat-val orange"><?= moeda($totalCustos) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Lucro</div>
        <div class="stat-val <?= $lucro >= 0 ? 'green' : 'orange' ?>">
            <?= moeda($lucro) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Margem</div>
        <div class="stat-val blue"><?= number_format($margem,1,',','.') ?>%</div>
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
            <div class="card-title">Custos de <?= $meses[$mes] ?> de <?= $ano ?></div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="abrirModal('modal-custo')">＋ Novo custo</button>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Orçamento</th>
                        <th>Cliente / Veículo</th>
                        <th>OS</th>
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
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['data_custo'])) ?></td>
                            <td><span class="badge badge-orange"><?= htmlspecialchars($c['tipo']) ?></span></td>
                            <td><?= htmlspecialchars($c['descricao']) ?></td>
                            <td><?= $c['numero_orcamento'] ? '#'.htmlspecialchars($c['numero_orcamento']) : '—' ?></td>
                            <td>
                                <?= htmlspecialchars($c['cliente'] ?: '—') ?>
                                <br>
                                <small>
                                    <?= htmlspecialchars($c['modelo'] ?: 'Veículo') ?>
                                    <?= $c['placa'] ? ' — '.htmlspecialchars($c['placa']) : '' ?>
                                </small>
                            </td>
                            <td><?= $c['numero_os'] ? htmlspecialchars($c['numero_os']) : '—' ?></td>
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
                    <input type="text" name="descricao" placeholder="Ex: Tinta, verniz, para-choque, diária..." required>
                </div>

                <div class="field">
                    <label>Valor</label>
                    <input type="number" name="valor" min="0" step="0.01" placeholder="0,00" required>
                </div>

                <div class="field">
                    <label>Data</label>
                    <input type="date" name="data_custo" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="field">
                    <label>Vincular ao orçamento / veículo</label>
                    <select name="orcamento_id">
                        <option value="">Não vincular</option>

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
                        <option value="">Não vincular</option>

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

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="fecharModal('modal-custo')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 Salvar custo</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/rodape.php'; ?>