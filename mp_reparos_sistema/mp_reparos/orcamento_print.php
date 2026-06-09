<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Orçamento — MP Reparos Automotivos</title>

<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:Arial, sans-serif;
    background:
        radial-gradient(circle at top left, rgba(255,0,0,.22), transparent 32%),
        linear-gradient(135deg,#070707,#141416 60%,#090909);
    color:#f8fafc;
    padding:24px;
}

/* LOGO */
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

/* CONTAINER */
.print-wrap{
    max-width:900px;
    margin:20px auto;
    background:linear-gradient(180deg,#18191d,#0f1012);
    border:1px solid #2a2d34;
    border-radius:22px;
    overflow:hidden;
    box-shadow:
        0 24px 70px rgba(0,0,0,.55),
        0 0 45px rgba(255,0,0,.12);
}

/* HEADER */
.print-header{
    background:linear-gradient(135deg,#220000,#111214 55%,#050505);
    border-bottom:3px solid #ff2020;
    padding:26px 30px;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
}

.ph-left{
    flex:1;
}

.ph-info{
    font-size:11px;
    color:#d1d5db;
    margin-top:8px;
    line-height:1.8;
}

.ph-right{
    text-align:right;
}

.ph-orc{
    font-size:22px;
    font-weight:900;
    color:white;
    letter-spacing:.04em;
}

.ph-datas{
    font-size:12px;
    color:#d1d5db;
    margin-top:8px;
    line-height:1.8;
}

.orange-bar{
    height:0;
}

/* DADOS */
.boxes{
    padding:10px 24px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    background:#111214;
    border-bottom:1px solid #2a2d34;
}

.boxes > div{
    border:1px solid #2a2d34;
    background:linear-gradient(180deg,#17181c,#101114);
    padding:14px;
    border-radius:14px;
}

.box-title{
    font-size:11px;
    font-weight:900;
    color:#ff5b5b;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:8px;
}

.box-row{
    font-size:13px;
    color:#d1d5db;
    margin-bottom:4px;
}

.box-row strong{
    color:#ffffff;
}

/* SEÇÕES */
.services{
    padding:18px 24px;
    border-bottom:1px solid #2a2d34;
}

.sec-title{
    font-size:12px;
    font-weight:900;
    color:#ff5b5b;
    text-transform:uppercase;
    letter-spacing:.09em;
    margin-bottom:12px;
}

/* TABELA */
table.itens-print{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    overflow:hidden;
    border:1px solid #2a2d34;
    border-radius:16px;
    font-size:14px;
}

table.itens-print th{
    background:#090a0c;
    color:#ff5b5b;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.07em;
    padding:13px;
    text-align:left;
    border-bottom:1px solid #2a2d34;
}

table.itens-print th:last-child{
    text-align:right;
}

table.itens-print td{
    padding:13px;
    border-bottom:1px solid #24262b;
    color:#f8fafc;
    background:#121316;
}

table.itens-print td:last-child{
    text-align:right;
    font-weight:900;
    color:#ffffff;
}

table.itens-print tr:nth-child(even) td{
    background:#17181c;
}

table.itens-print tr:last-child td{
    border-bottom:none;
}

/* TOTAIS */
.totals{
    padding:20px 30px;
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:8px;
    border-bottom:1px solid #2a2d34;
}

.tot-r{
    display:flex;
    gap:20px;
    font-size:13px;
}

.tot-r span:first-child{
    color:#9ca3af;
    min-width:130px;
    text-align:right;
}

.tot-r span:last-child{
    font-weight:800;
    min-width:120px;
    text-align:right;
    color:white;
}

.tot-final-r{
    background:#15171b;
    border:1px solid #2a2d34;
    border-radius:16px;
    padding:16px 22px;
    display:flex;
    gap:20px;
    margin-top:6px;
}

.tot-final-r span:first-child{
    color:#fee2e2;
    font-size:12px;
    font-weight:900;
    text-transform:uppercase;
    min-width:110px;
    text-align:right;
    letter-spacing:.08em;
}

.tot-final-r span:last-child{
    color:#ff4d4d;
    font-size:28px;
    font-weight:950;
    min-width:150px;
    text-align:right;
}

/* CONDIÇÕES */
.conds{
    padding:20px 30px;
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:14px;
    border-bottom:1px solid #2a2d34;
    background:#111214;
}

.conds > div{
    border:1px solid #2a2d34;
    background:linear-gradient(180deg,#17181c,#101114);
    padding:14px;
    border-radius:14px;
}

.cond-label{
    font-size:11px;
    font-weight:900;
    color:#ff5b5b;
    text-transform:uppercase;
    letter-spacing:.08em;
    margin-bottom:5px;
}

.cond-val{
    font-size:13px;
    color:#ffffff;
    font-weight:700;
}

/* OBS */
.obs-box{
    padding:20px 30px;
    border-bottom:1px solid #2a2d34;
    background:#111214;
}

.obs-text{
    font-size:13px;
    color:#d1d5db;
    line-height:1.6;
}

/* ASSINATURA */
.assinatura-fixa{
        padding:18px 28px 10px;
    text-align:center;
    border-bottom:1px solid #2a2d34;
}

.rubrica-img{
    width:135px;
    height:auto;
    object-fit:contain;
    margin-bottom:-80px;
    position:relative;
    z-index:2;
    filter:brightness(1.2) contrast(1.1);
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

/* FOOTER */
.print-footer{
       background:#0f1012;
    padding:8px 20px;
    border-top:1px solid #2a2d34;
}

.pf-text{
    font-size:11px;
    color:#6b7280;
    text-align:center;
}

/* BOTÕES */
.no-print{

    display:flex;
    justify-content:center;
    gap:10px;
    padding:10px;
    background:#111214;
    border-top:1px solid #2a2d34;
}

.btn-print{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:10px 18px;
    background:linear-gradient(135deg,#ff1e1e,#9b0000);
    color:white;
    border:none;
    border-radius:10px;
    font-size:13.5px;
    font-weight:800;
    cursor:pointer;
    font-family:inherit;
    box-shadow:0 8px 20px rgba(255,0,0,.25);
}

.btn-print:hover{
    filter:brightness(1.08);
}

.btn-close{
    padding:10px 18px;
    background:#15171b;
    color:#f8fafc;
    border:1.5px solid #2a2d34;
    border-radius:10px;
    font-size:13.5px;
    font-weight:800;
    cursor:pointer;
    font-family:inherit;
}

@media print{
    @page{
        size:A4;
        margin:8mm;
    }

    .no-print{
        display:none!important;
    }

    body{
        background:white;
        padding:0;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .print-wrap{
        box-shadow:none;
        margin:0;
        border-radius:0;
        max-width:100%;
    }
}

@media(max-width:700px){
    .print-header{
        flex-direction:column;
    }

    .ph-right{
        text-align:left;
    }

    .boxes,
    .conds{
        grid-template-columns:1fr;
    }

    .tot-final-r{
        width:100%;
        justify-content:space-between;
    }
}
</style>
</head>

<body>
<?php
require_once 'config/db.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    die('<p style="padding:40px;text-align:center;color:#dc2626">ID do orçamento não informado.</p>');
}

$stmt = $db->prepare("
    SELECT 
        o.*, 
        c.nome AS cli_nome, 
        c.cpf_cnpj, 
        c.telefone AS cli_tel, 
        c.email AS cli_email, 
        c.endereco AS cli_end,
        v.placa, 
        v.modelo, 
        v.marca, 
        v.ano, 
        v.cor, 
        v.km, 
        v.seguradora
    FROM orcamentos o
    LEFT JOIN clientes c ON o.cliente_id = c.id
    LEFT JOIN veiculos v ON o.veiculo_id = v.id
    WHERE o.id = ?
");

$stmt->bind_param('i', $id);
$stmt->execute();

$o = $stmt->get_result()->fetch_assoc();

if (!$o) {
    die('<p style="padding:40px;text-align:center;color:#dc2626">Orçamento não encontrado.</p>');
}

$si = $db->prepare("
    SELECT *
    FROM orcamento_itens
    WHERE orcamento_id=?
    ORDER BY id
");

$si->bind_param('i', $id);
$si->execute();

$itens = $si->get_result()->fetch_all(MYSQLI_ASSOC);

function h($s){
    return htmlspecialchars($s ?? '');
}

function fmtD($d){
    if(!$d) return '—';

    $p = explode('-', $d);

    if(count($p) !== 3) return $d;

    return $p[2].'/'.$p[1].'/'.$p[0];
}

function fmtR($v){
    return 'R$ '.number_format((float)$v, 2, ',', '.');
}
?>

<div class="print-wrap">

    <div class="print-header">
        <div class="ph-left">
            <div class="print-logo">
                <img src="assets/img/logo.png" alt="MP Reparos">
            </div>

            <div class="ph-info">
                📍 Avenida Luiz Pasteur, 1555 — Tamandaré, Esteio — RS<br>
                📞 (51) 994037229 &nbsp;|&nbsp; ✉ mpautomotivos@gmail.com<br>
                CNPJ: 36.454.523/0001-55
            </div>
        </div>

        <div class="ph-right">
            <div class="ph-orc">ORÇAMENTO</div>

            <div class="ph-datas">
                Emissão: <?= fmtD($o['data_emissao']) ?><br>
                Validade: <?= fmtD($o['data_validade']) ?><br>
                Status: <?= h($o['status']) ?>
            </div>
        </div>
    </div>

    <div class="orange-bar"></div>

    <div class="boxes">
        <div>
            <div class="box-title">Dados do Cliente</div>

            <div class="box-row">
                <strong><?= h($o['cli_nome'] ?? '—') ?></strong>
            </div>

            <?php if($o['cpf_cnpj']): ?>
                <div class="box-row">CPF/CNPJ: <?= h($o['cpf_cnpj']) ?></div>
            <?php endif; ?>

            <?php if($o['cli_tel']): ?>
                <div class="box-row">Tel: <?= h($o['cli_tel']) ?></div>
            <?php endif; ?>

            <?php if($o['cli_email']): ?>
                <div class="box-row">E-mail: <?= h($o['cli_email']) ?></div>
            <?php endif; ?>

            <?php if($o['cli_end']): ?>
                <div class="box-row"><?= h($o['cli_end']) ?></div>
            <?php endif; ?>
        </div>

        <div>
            <div class="box-title">Dados do Veículo</div>

            <div class="box-row">
                <strong><?= h(trim(($o['marca'] ?? '').' '.($o['modelo'] ?? ''))) ?></strong>
            </div>

            <?php if($o['ano']): ?>
                <div class="box-row">Ano: <?= h($o['ano']) ?></div>
            <?php endif; ?>

            <?php if($o['cor']): ?>
                <div class="box-row">Cor: <?= h($o['cor']) ?></div>
            <?php endif; ?>

            <?php if($o['placa']): ?>
                <div class="box-row">Placa: <strong><?= h($o['placa']) ?></strong></div>
            <?php endif; ?>

            <?php if($o['km']): ?>
                <div class="box-row">KM: <?= h($o['km']) ?></div>
            <?php endif; ?>

            <?php if($o['seguradora']): ?>
                <div class="box-row">Seguradora: <?= h($o['seguradora']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="services">
        <div class="sec-title">Serviços e Peças</div>

        <table class="itens-print">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Qtd.</th>
                    <th>Un.</th>
                    <th>Valor Unit.</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($itens as $it): ?>
                    <tr>
                        <td><?= h($it['descricao']) ?></td>
                        <td><?= number_format((float)$it['quantidade'], 2, ',', '.') ?></td>
                        <td><?= h($it['unidade']) ?></td>
                        <td style="text-align:right"><?= fmtR($it['valor_unit']) ?></td>
                        <td><?= fmtR($it['valor_total']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if(!$itens): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:#9ca3af;padding:16px">
                            Nenhum item cadastrado
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="totals">
        <div class="tot-r">
            <span>Subtotal</span>
            <span><?= fmtR($o['subtotal']) ?></span>
        </div>

        <?php if($o['desconto'] > 0): ?>
            <div class="tot-r">
                <span>Desconto (<?= number_format((float)$o['desconto'], 1, ',', '.') ?>%)</span>
                <span style="color:#ff5b5b">
                    − <?= fmtR($o['subtotal'] * $o['desconto'] / 100) ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="tot-final-r">
            <span>Total</span>
            <span><?= fmtR($o['total']) ?></span>
        </div>
    </div>

    <?php if($o['pagamento'] || $o['prazo'] || $o['garantia']): ?>
        <div class="conds">
            <?php if($o['pagamento']): ?>
                <div>
                    <div class="cond-label">Pagamento</div>
                    <div class="cond-val"><?= h($o['pagamento']) ?></div>
                </div>
            <?php endif; ?>

            <?php if($o['prazo']): ?>
                <div>
                    <div class="cond-label">Prazo de entrega</div>
                    <div class="cond-val"><?= h($o['prazo']) ?></div>
                </div>
            <?php endif; ?>

            <?php if($o['garantia']): ?>
                <div>
                    <div class="cond-label">Garantia</div>
                    <div class="cond-val"><?= h($o['garantia']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($o['observacoes']): ?>
        <div class="obs-box">
            <div class="sec-title">Observações</div>
            <div class="obs-text"><?= nl2br(h($o['observacoes'])) ?></div>
        </div>
    <?php endif; ?>

    <div class="assinatura-fixa">
        <img src="assets/img/rubrica.png" class="rubrica-img">
        <div class="assinatura-linha"></div>
        <div class="assinatura-texto">Responsável</div>
    </div>

    <div class="print-footer">
        <div class="pf-text">
            MP Reparos Automotivos — Orçamento #<?= h($o['numero'] ?? $o['id']) ?> — Emitido em <?= fmtD($o['data_emissao']) ?>
        </div>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>

        <button class="btn-close" onclick="window.close()">
            ✕ Fechar
        </button>
    </div>

</div>

</body>
</html>