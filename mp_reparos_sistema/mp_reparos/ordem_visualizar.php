<?php

$pagina = 'ordens';
$titulo = 'Visualizar Ordem de Serviço';
$subtitulo = 'Folha interna para funcionários';

require_once 'config/db.php';
$db = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Ordem inválida.');
}

$sql = "
SELECT
    os.*,
    c.nome AS cliente_nome,
    v.placa,
    v.modelo,
    v.cor,
    o.numero AS numero_orcamento,
    o.danos,
    o.observacoes AS observacoes_orcamento,
    o.data_emissao
FROM ordens_servico os
LEFT JOIN clientes c ON os.cliente_id = c.id
LEFT JOIN veiculos v ON os.veiculo_id = v.id
LEFT JOIN orcamentos o ON os.orcamento_id = o.id
WHERE os.id = $id
";

$ordem = $db->query($sql)->fetch_assoc();

if (!$ordem) {
    die('Ordem de serviço não encontrada.');
}

$dados = [];

if (!empty($ordem['danos'])) {
    $tmp = json_decode($ordem['danos'], true);
    if (is_array($tmp)) {
        $dados = $tmp;
    }
}

$cliente = $dados['cliente_nome'] ?? $ordem['cliente_nome'] ?? '—';
$veiculo = $dados['veiculo_desc'] ?? $ordem['modelo'] ?? '—';
$placa   = $dados['placa'] ?? $ordem['placa'] ?? '—';
$cor     = $dados['cor'] ?? $ordem['cor'] ?? '—';

$tipoTinta = $dados['tipo_tinta'] ?? '—';
$seguradora = $dados['seguradora'] ?? '—';
$diagnostico = $dados['diagnostico'] ?? '';
$areas = $dados['areas'] ?? [];

include 'includes/topo.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <div class="card-icon">🔧</div>
            <div class="card-title">
                OS #<?= htmlspecialchars($ordem['numero_os'] ?: $ordem['id']) ?>
            </div>
        </div>

        <div class="btn-group">
            <a href="ordens.php" class="btn btn-ghost btn-sm">← Voltar</a>
            <button class="btn btn-navy btn-sm" onclick="window.print()">🖨️ Imprimir</button>
        </div>
    </div>

    <div class="card-body">
        <div class="os-print">

            <div class="os-topo">
                <div>
                    <div class="os-titulo">ORDEM DE SERVIÇO</div>
                    <div class="os-sub">Folha interna para execução do serviço</div>
                </div>

                <div class="os-num">
                    OS #<?= htmlspecialchars($ordem['numero_os'] ?: $ordem['id']) ?>
                </div>
            </div>

            <div class="os-placa-area">
                <div class="os-placa-label">PLACA</div>
                <div class="os-placa"><?= htmlspecialchars($placa) ?></div>
            </div>

            <div class="os-grid">

                <div class="os-box">
                    <div class="os-label">Cliente</div>
                    <div class="os-value"><?= htmlspecialchars($cliente) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Veículo</div>
                    <div class="os-value"><?= htmlspecialchars($veiculo) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Cor</div>
                    <div class="os-value"><?= htmlspecialchars($cor) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Entrega</div>
                    <div class="os-value">
                        <?= $ordem['data_entrega'] ? date('d/m/Y', strtotime($ordem['data_entrega'])) : '—' ?>
                    </div>
                </div>

            </div>

            <div class="os-section destaque">
                <div class="os-section-title">O QUE FAZER NO CARRO</div>

                <div class="os-text grande">
                    <?php
                    if(!empty($ordem['tarefas'])){
                        echo nl2br(htmlspecialchars($ordem['tarefas']));
                    }
                    elseif(!empty($diagnostico)){
                        echo nl2br(htmlspecialchars($diagnostico));
                    }
                    else{
                        echo 'Nenhuma tarefa cadastrada.';
                    }
                    ?>
                </div>
            </div>

            <div class="os-section">
                <div class="os-section-title">Peças / áreas para reparo</div>

                <div class="os-checks">
                    <?php if(!empty($areas)): ?>
                        <?php foreach($areas as $area): ?>
                            <div class="os-check">☐ <?= htmlspecialchars($area) ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="os-check">☐ Não informado</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="os-section">
                <div class="os-section-title">Informações rápidas</div>

                <div class="os-grid-2">
                    <div class="os-box">
                        <div class="os-label">Tipo de tinta</div>
                        <div class="os-value"><?= htmlspecialchars($tipoTinta) ?></div>
                    </div>

                    <div class="os-box">
                        <div class="os-label">Seguradora / Revenda</div>
                        <div class="os-value"><?= htmlspecialchars($seguradora) ?></div>
                    </div>
                </div>
            </div>

         

            <div class="os-section">
                <div class="os-section-title">Observações</div>

                <div class="os-text">
                    <?= nl2br(htmlspecialchars(
                        $ordem['observacoes']
                        ?: $ordem['observacoes_orcamento']
                        ?: ''
                    )) ?>
                </div>
            </div>

            <div class="os-ass">
                <div>
                    _______________________________<br>
                    Responsável
                </div>

                <div>
                    _______________________________<br>
                    Revisão final
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.os-print{
    color:#000;
    background:#fff;
    font-family:Arial, sans-serif;
}

.os-topo{
    border:3px solid #000;
    padding:14px 18px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:12px;
}

.os-titulo{
    font-size:30px;
    font-weight:900;
    letter-spacing:.04em;
}

.os-sub{
    font-size:13px;
    margin-top:3px;
    font-weight:700;
}

.os-num{
    border:2px solid #000;
    padding:10px 16px;
    font-size:22px;
    font-weight:900;
}

.os-placa-area{
    border:4px solid #000;
    text-align:center;
    padding:12px;
    margin-bottom:12px;
}

.os-placa-label{
    font-size:14px;
    font-weight:900;
    letter-spacing:.12em;
}

.os-placa{
    font-size:52px;
    font-weight:900;
    letter-spacing:.16em;
    line-height:1.1;
}

.os-grid{
    display:grid;
    grid-template-columns:2fr 2fr 1fr 1fr;
    gap:8px;
    margin-bottom:10px;
}

.os-grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
}

.os-box{
    border:2px solid #000;
    padding:8px;
    min-height:52px;
}

.os-label{
    font-size:11px;
    text-transform:uppercase;
    font-weight:900;
    margin-bottom:3px;
}

.os-value{
    font-size:16px;
    font-weight:900;
}

.os-section{
    border:2px solid #000;
    padding:10px;
    margin-bottom:10px;
}

.os-section.destaque{
    border-width:3px;
}

.os-section-title{
    font-size:14px;
    text-transform:uppercase;
    font-weight:900;
    margin-bottom:8px;
    border-bottom:2px solid #000;
    padding-bottom:5px;
}

.os-checks{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:6px;
}

.os-checks.checklist{
    grid-template-columns:repeat(4,1fr);
}

.os-check{
    border:2px solid #000;
    padding:8px;
    font-size:14px;
    font-weight:800;
    min-height:42px;
    display:flex;
    align-items:center;
}

.os-text{
    border:1px solid #000;
    padding:10px;
    line-height:1.5;
    font-size:14px;
    min-height:60px;
    font-weight:700;
}

.os-text.grande{
    font-size:28px;
    min-height:190px;
    line-height:1.45;
    font-weight:900;
}

.os-ass{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
    text-align:center;
    margin-top:22px;
    font-size:12px;
    font-weight:800;
}

@media print{
    @page{
        size:A4;
        margin:6mm;
    }

    body{
        background:white !important;
        zoom:0.80;
    }

    .sidebar,
    .topbar,
    .card-header,
    .btn{
        display:none !important;
    }

    .main{
        margin-left:0 !important;
    }

    .content{
        padding:0 !important;
    }

    .card{
        border:none !important;
        box-shadow:none !important;
        margin:0 !important;
    }

    .card-body{
        padding:0 !important;
    }

    .os-topo,
    .os-placa-area,
    .os-section{
        break-inside:avoid;
    }
}

@media(max-width:900px){
    .os-grid{
        grid-template-columns:1fr 1fr;
    }

    .os-checks,
    .os-checks.checklist{
        grid-template-columns:1fr 1fr;
    }
}

@media(max-width:600px){
    .os-grid,
    .os-grid-2,
    .os-ass,
    .os-checks,
    .os-checks.checklist{
        grid-template-columns:1fr;
    }

    .os-placa{
        font-size:38px;
    }
}
</style>

<?php include 'includes/rodape.php'; ?>