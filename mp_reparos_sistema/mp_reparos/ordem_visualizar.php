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
            <a href="ordens.php" class="btn btn-ghost btn-sm">
                ← Voltar
            </a>

            <button class="btn btn-navy btn-sm" onclick="window.print()">
                🖨️ Imprimir
            </button>
        </div>

    </div>

    <div class="card-body">

        <div class="os-print">

            <div class="os-topo">

                <div>
                    <div class="os-titulo">
                        ORDEM DE SERVIÇO
                    </div>

                    <div class="os-sub">
                        MP Reparos Automotivos
                    </div>
                </div>

                <div class="os-num">
                    #<?= htmlspecialchars($ordem['numero_os'] ?: $ordem['id']) ?>
                </div>

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
                    <div class="os-label">Placa</div>
                    <div class="os-value"><?= htmlspecialchars($placa) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Cor</div>
                    <div class="os-value"><?= htmlspecialchars($cor) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Status</div>
                    <div class="os-value"><?= htmlspecialchars($ordem['status']) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Prioridade</div>
                    <div class="os-value"><?= htmlspecialchars($ordem['prioridade']) ?></div>
                </div>

                <div class="os-box">
                    <div class="os-label">Entrega</div>
                    <div class="os-value">
                        <?= $ordem['data_entrega']
                            ? date('d/m/Y', strtotime($ordem['data_entrega']))
                            : '—'
                        ?>
                    </div>
                </div>

                <div class="os-box">
                    <div class="os-label">Orçamento</div>
                    <div class="os-value">
                        #<?= htmlspecialchars($ordem['numero_orcamento'] ?? '—') ?>
                    </div>
                </div>

            </div>

            <div class="os-section">

                <div class="os-section-title">
                    Áreas danificadas
                </div>

                <div class="os-checks">

                    <?php if(!empty($areas)): ?>

                        <?php foreach($areas as $area): ?>

                            <div class="os-check">
                                ☐ <?= htmlspecialchars($area) ?>
                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="os-check">
                            ☐ Não informado
                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <div class="os-section">

                <div class="os-section-title">
                    Serviços a executar
                </div>

                <div class="os-text">

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

                <div class="os-section-title">
                    Informações adicionais
                </div>

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

                <div class="os-section-title">
                    Checklist
                </div>

                <div class="os-checks">

                    <div class="os-check">☐ Desmontagem</div>
                    <div class="os-check">☐ Funilaria</div>
                    <div class="os-check">☐ Preparação</div>
                    <div class="os-check">☐ Pintura</div>
                    <div class="os-check">☐ Montagem</div>
                    <div class="os-check">☐ Polimento</div>
                    <div class="os-check">☐ Revisão final</div>
                    <div class="os-check">☐ Limpeza</div>

                </div>

            </div>

            <div class="os-section">

                <div class="os-section-title">
                    Observações
                </div>

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
                    Revisão
                </div>

            </div>

        </div>

    </div>

</div>

<style>

.os-print{
    color:#111827;
}

.os-topo{
    background:#0f1923;
    color:white;
    border-radius:10px;
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:10px;
}

.os-titulo{
    font-size:22px;
    font-weight:900;
    letter-spacing:.04em;
}

.os-sub{
    color:#cbd5e1;
    font-size:12px;
    margin-top:2px;
}

.os-num{
    background:#f97316;
    padding:8px 14px;
    border-radius:8px;
    font-size:18px;
    font-weight:900;
}

.os-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:8px;
    margin-bottom:10px;
}

.os-grid-2{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:8px;
}

.os-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:8px;
}

.os-label{
    font-size:10px;
    color:#64748b;
    text-transform:uppercase;
    font-weight:800;
    margin-bottom:2px;
}

.os-value{
    font-size:13px;
    font-weight:700;
    color:#1e293b;
}

.os-section{
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:10px;
    margin-bottom:10px;
}

.os-section-title{
    font-size:11px;
    text-transform:uppercase;
    font-weight:900;
    color:#334155;
    margin-bottom:6px;
}

.os-checks{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:6px;
}

.os-check{
    border:1px solid #cbd5e1;
    border-radius:6px;
    padding:6px;
    font-size:12px;
    background:white;
    font-weight:600;
}

.os-text{
    background:#f8fafc;
    border-radius:6px;
    padding:8px;
    line-height:1.4;
    font-size:12px;
    min-height:50px;
}

.os-ass{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    text-align:center;
    margin-top:20px;
    font-size:11px;
}

@media print{

    @page{
        size:A4;
        margin:5mm;
    }

    body{
        zoom:0.72;
        background:white !important;
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

    .os-section{
        margin-bottom:6px;
    }

    .os-topo{
        margin-bottom:6px;
    }

    .os-grid{
        gap:5px;
        margin-bottom:6px;
    }

    .os-checks{
        gap:4px;
    }

}

@media(max-width:900px){

    .os-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .os-checks{
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:600px){

    .os-grid,
    .os-grid-2,
    .os-ass{
        grid-template-columns:1fr;
    }

    .os-checks{
        grid-template-columns:1fr;
    }

}

</style>

<?php include 'includes/rodape.php'; ?>