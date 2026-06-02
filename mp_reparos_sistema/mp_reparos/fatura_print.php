<?php
require_once 'config/db.php';
$db = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Fatura inválida.');
}

$f = $db->prepare("
    SELECT f.*, c.nome AS cliente_nome, c.telefone
    FROM faturas f
    LEFT JOIN clientes c ON c.id = f.cliente_id
    WHERE f.id = ?
");
$f->bind_param('i', $id);
$f->execute();
$fatura = $f->get_result()->fetch_assoc();

if (!$fatura) {
    die('Fatura não encontrada.');
}

$it = $db->prepare("
    SELECT *
    FROM fatura_itens
    WHERE fatura_id = ?
    ORDER BY id
");
$it->bind_param('i', $id);
$it->execute();
$itens = $it->get_result();

function moeda($v){
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function dataBR($d){
    return $d ? date('d/m/Y', strtotime($d)) : '—';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Fatura #<?= htmlspecialchars($fatura['numero'] ?: $fatura['id']) ?></title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#f1f5f9;
    color:#111827;
    margin:0;
    padding:24px;
}

.fatura{
    max-width:900px;
    margin:0 auto;
    background:white;
    padding:28px;
    border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,.12);
}

.topo{
    background:#0f1923;
    color:white;
    padding:22px;
    border-radius:12px;
    display:flex;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}

.empresa h1{
    margin:0;
    font-size:26px;
}

.empresa p{
    margin:6px 0 0;
    color:#cbd5e1;
    line-height:1.5;
    font-size:13px;
}

.numero{
    background:#f97316;
    padding:14px 18px;
    border-radius:10px;
    height:max-content;
    font-size:20px;
    font-weight:900;
    white-space:nowrap;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
    margin-bottom:20px;
}

.info-box{
    border:1px solid #e2e8f0;
    background:#f8fafc;
    padding:12px;
    border-radius:10px;
}

.label{
    font-size:11px;
    font-weight:800;
    color:#64748b;
    text-transform:uppercase;
    margin-bottom:4px;
}

.value{
    font-size:15px;
    font-weight:700;
    color:#1e293b;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    font-size:14px;
}

th{
    background:#0f1923;
    color:white;
    text-align:left;
    padding:11px;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.05em;
}

td{
    padding:11px;
    border-bottom:1px solid #e2e8f0;
}

tr:nth-child(even){
    background:#f8fafc;
}

.valor{
    text-align:right;
    font-weight:800;
}

.total-box{
    margin-top:20px;
    display:flex;
    justify-content:flex-end;
}

.total{
    background:#0f1923;
    color:white;
    padding:18px 24px;
    border-radius:12px;
    min-width:260px;
    text-align:right;
}

.total .label-total{
    color:#cbd5e1;
    font-size:12px;
    text-transform:uppercase;
    font-weight:800;
}

.total .valor-total{
    color:#f97316;
    font-size:30px;
    font-weight:900;
    margin-top:4px;
}

.obs{
    margin-top:20px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    padding:14px;
    border-radius:10px;
    line-height:1.5;
}

.assinaturas{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
    margin-top:44px;
    text-align:center;
    font-size:13px;
    color:#334155;
}

.acoes{
    max-width:900px;
    margin:0 auto 16px;
    display:flex;
    justify-content:flex-end;
    gap:8px;
}

.btn{
    border:none;
    border-radius:8px;
    padding:10px 16px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    font-size:14px;
}

.btn-print{
    background:#f97316;
    color:white;
}

.btn-voltar{
    background:white;
    color:#334155;
    border:1px solid #e2e8f0;
}

@media print{
    @page{
        size:A4;
        margin:8mm;
    }

    body{
        background:white;
        padding:0;
    }

    .acoes{
        display:none;
    }

    .fatura{
        box-shadow:none;
        border-radius:0;
        padding:0;
        max-width:100%;
    }

    .topo{
        border-radius:0;
    }
}
</style>
</head>

<body>

<div class="acoes">
    <a href="faturas.php" class="btn btn-voltar">← Voltar</a>
    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimir / PDF</button>
</div>

<div class="fatura">

    <div class="topo">
        <div class="empresa">
            <h1>MP Reparos Automotivos</h1>
            <p>
                Orçamento final / fatura para pagamento<br>
                Serviços de funilaria e pintura automotiva
            </p>
        </div>

        <div class="numero">
            FATURA #<?= htmlspecialchars($fatura['numero'] ?: $fatura['id']) ?>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Cliente</div>
            <div class="value"><?= htmlspecialchars($fatura['cliente_nome'] ?: '—') ?></div>
        </div>

        <div class="info-box">
            <div class="label">Data emissão</div>
            <div class="value"><?= dataBR($fatura['data_emissao']) ?></div>
        </div>

        <div class="info-box">
            <div class="label">Status</div>
            <div class="value"><?= htmlspecialchars($fatura['status']) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Veículo</th>
                <th>Placa</th>
                <th style="text-align:right;">Valor</th>
            </tr>
        </thead>

        <tbody>
            <?php while($i = $itens->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($i['descricao']) ?></td>
                    <td><?= htmlspecialchars($i['veiculo'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($i['placa'] ?: '—') ?></td>
                    <td class="valor"><?= moeda($i['valor']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total">
            <div class="label-total">Total a pagar</div>
            <div class="valor-total"><?= moeda($fatura['total']) ?></div>
        </div>
    </div>

    <?php if(!empty($fatura['observacoes'])): ?>
        <div class="obs">
            <strong>Observações:</strong><br>
            <?= nl2br(htmlspecialchars($fatura['observacoes'])) ?>
        </div>
    <?php endif; ?>

    <div class="assinaturas">
        <div>
            ___________________________________<br>
            MP Reparos Automotivos
        </div>

        <div>
            ___________________________________<br>
            Cliente / Responsável
        </div>
    </div>

</div>

</body>
</html>