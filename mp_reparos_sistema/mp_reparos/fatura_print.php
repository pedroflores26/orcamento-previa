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
<title>Orçamento Final</title>

<style>
*{
    box-sizing:border-box;
}

body{
    font-family: Arial, sans-serif;
    background:
        radial-gradient(circle at top left, rgba(255,0,0,.22), transparent 32%),
        linear-gradient(135deg,#070707,#141416 60%,#090909);
    color:#f8fafc;
    margin:0;
    padding:24px;
}

.acoes{
    max-width:950px;
    margin:0 auto 16px;
    display:flex;
    justify-content:flex-end;
    gap:8px;
}

.btn{
    border:none;
    border-radius:10px;
    padding:10px 16px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
    font-size:14px;
}

.btn-print{
    background:linear-gradient(135deg,#ff1e1e,#9b0000);
    color:white;
    box-shadow:0 8px 20px rgba(255,0,0,.25);
}

.btn-voltar{
    background:#15171b;
    color:#f8fafc;
    border:1px solid #2a2d34;
}

.fatura{
    max-width:950px;
    margin:0 auto;
    background:linear-gradient(180deg,#18191d,#0f1012);
    border:1px solid #2a2d34;
    border-radius:22px;
    overflow:hidden;
    box-shadow:
        0 24px 70px rgba(0,0,0,.55),
        0 0 45px rgba(255,0,0,.12);
}

.topo{
    background:
        linear-gradient(135deg,#220000,#111214 55%,#050505);
    border-bottom:3px solid #ff2020;
    padding:26px 30px;
}

.topo-inner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.print-logo{
    width:190px;
    height:86px;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    overflow:hidden;
}

.print-logo img{
    max-width:100%;
    max-height:100%;
    object-fit:contain;
    display:block;
    filter:drop-shadow(0 0 18px rgba(255,0,0,.28));
}

.topo-texto{
    text-align:right;
}

.topo-texto h1{
    margin:0;
    font-size:25px;
    font-weight:900;
    color:white;
    letter-spacing:.03em;
}

.topo-texto p{
    margin:7px 0 0;
    color:#d1d5db;
    line-height:1.5;
    font-size:13px;
}

.faixa-info{
    padding:20px 30px;
    background:#111214;
    border-bottom:1px solid #2a2d34;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}

.info-box{
    border:1px solid #2a2d34;
    background:linear-gradient(180deg,#17181c,#101114);
    padding:14px;
    border-radius:14px;
}

.label{
    font-size:11px;
    font-weight:900;
    color:#ff5b5b;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:5px;
}

.value{
    font-size:16px;
    font-weight:800;
    color:#ffffff;
}

.conteudo{
    padding:24px 30px 30px;
}

.sec-title{
    font-size:12px;
    color:#ff5b5b;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.09em;
    margin-bottom:12px;
}

table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    overflow:hidden;
    border:1px solid #2a2d34;
    border-radius:16px;
    font-size:14px;
}

th{
    background:#090a0c;
    color:#ff5b5b;
    text-align:left;
    padding:13px;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.07em;
    border-bottom:1px solid #2a2d34;
}

td{
    padding:13px;
    border-bottom:1px solid #24262b;
    color:#f8fafc;
    background:#121316;
}

tr:nth-child(even) td{
    background:#17181c;
}

tr:last-child td{
    border-bottom:none;
}

.valor{
    text-align:right;
    font-weight:900;
    color:#ffffff;
}

.total-box{
    margin-top:22px;
    display:flex;
    justify-content:flex-end;
}
.total{
    background:#15171b;
    border:1px solid #2a2d34;

    color:white;

    padding:20px 26px;

    border-radius:18px;

    min-width:300px;

    text-align:right;
}

.total .label-total{
    color:#fee2e2;
    font-size:12px;
    text-transform:uppercase;
    font-weight:900;
    letter-spacing:.08em;
}
.total .valor-total{
    color:#ff4d4d;

    font-size:34px;

    font-weight:950;

    margin-top:4px;
}

.obs{
    margin-top:22px;
    border:1px solid #2a2d34;
    background:#111214;
    padding:16px;
    border-radius:14px;
    line-height:1.6;
    color:#d1d5db;
}

.obs strong{
    color:#ff5b5b;
}

.assinatura-fixa{
    margin-top:44px;
    text-align:center;
    color:#d1d5db;
}
.rubrica-img{
    width:135px;
    height:auto;

    object-fit:contain;

    margin-bottom:-80px;

    position:relative;
    z-index:2;

    filter:
        brightness(1.2)
        contrast(1.1);
}
.assinatura-linha{
    width:240px;
    height:1px;

    background:#3a3d44;

    margin:0 auto 8px;
}
.assinatura-texto{
    font-size:13px;
    font-weight:800;
    color:#9ca3af;
}

.rodape{
    margin-top:24px;
    padding-top:14px;
    border-top:1px solid #2a2d34;
    color:#6b7280;
    font-size:11px;
    text-align:center;
}

@media print{
    @page{
        size:A4;
        margin:8mm;
    }

    body{
        background:white;
        padding:0;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .acoes{
        display:none;
    }

    .fatura{
        max-width:100%;
        border-radius:0;
        box-shadow:none;
    }
}

@media(max-width:700px){
    .topo-inner{
        flex-direction:column;
        align-items:flex-start;
    }

    .topo-texto{
        text-align:left;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .total{
        min-width:100%;
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
        <div class="topo-inner">
            <div class="print-logo">
                <img src="assets/img/logo.png" alt="MP Reparos">
            </div>

            <div class="topo-texto">
                <h1>ORÇAMENTO FINAL</h1>
                <p>
                    Serviços de funilaria e pintura automotiva<br>
                    MP Reparos Automotivos
                </p>
            </div>
        </div>
    </div>

    <div class="faixa-info">
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
    </div>

    <div class="conteudo">
        <div class="sec-title">Veículos e serviços incluídos</div>

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

        <div class="assinatura-fixa">

    <img src="assets/img/rubrica.png" class="rubrica-img">

    <div class="assinatura-linha"></div>

    <div class="assinatura-texto">
        Responsável
    </div>

</div>
        <div class="rodape">
            Avenida Luiz Pasteur, 1555 — Tamandaré, Esteio — RS • (51) 99403-7229 • CNPJ: 36.454.523/0001-55
        </div>
    </div>

</div>

</body>
</html>