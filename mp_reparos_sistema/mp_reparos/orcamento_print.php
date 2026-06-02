<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Orçamento — MP Reparos Automotivos</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f0f2f5;color:#1e293b}
.print-wrap{max-width:800px;margin:20px auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.print-header{background:#0f1923;padding:24px 28px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
.ph-left{flex:1}
.ph-nome{font-size:22px;font-weight:800;color:#f97316;letter-spacing:-.02em}
.ph-sub{font-size:10px;color:#94a3b8;margin-top:2px;text-transform:uppercase;letter-spacing:.08em}
.ph-info{font-size:11px;color:#94a3b8;margin-top:8px;line-height:1.8}
.ph-right{text-align:right}
.ph-orc{font-size:16px;font-weight:800;color:white}
.ph-datas{font-size:11px;color:#94a3b8;margin-top:4px;line-height:1.8}
.orange-bar{height:3px;background:#f97316}
.boxes{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:20px 28px;border-bottom:1px solid #e2e8f0}
.box-title{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
.box-row{font-size:12.5px;color:#334155;margin-bottom:3px}
.box-row strong{color:#0f172a}
.services{padding:20px 28px;border-bottom:1px solid #e2e8f0}
.sec-title{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px}
table.itens-print{width:100%;border-collapse:collapse;font-size:12.5px}
table.itens-print th{background:#0f1923;color:white;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:9px 12px;text-align:left}
table.itens-print th:last-child{text-align:right}
table.itens-print td{padding:9px 12px;border-bottom:1px solid #f1f5f9;color:#334155}
table.itens-print td:last-child{text-align:right;font-weight:700}
table.itens-print tr:nth-child(even) td{background:#f8fafc}
.totals{padding:16px 28px;display:flex;flex-direction:column;align-items:flex-end;gap:6px;border-bottom:1px solid #e2e8f0}
.tot-r{display:flex;gap:20px;font-size:12.5px}
.tot-r span:first-child{color:#94a3b8;min-width:120px;text-align:right}
.tot-r span:last-child{font-weight:600;min-width:100px;text-align:right}
.tot-final-r{background:#0f1923;border-radius:8px;padding:10px 16px;display:flex;gap:20px;margin-top:4px}
.tot-final-r span:first-child{color:#94a3b8;font-size:11px;font-weight:700;text-transform:uppercase;min-width:100px;text-align:right}
.tot-final-r span:last-child{color:#f97316;font-size:18px;font-weight:800;min-width:110px;text-align:right}
.conds{padding:16px 28px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;border-bottom:1px solid #e2e8f0}
.cond-label{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
.cond-val{font-size:12.5px;color:#334155}
.obs-box{padding:16px 28px;border-bottom:1px solid #e2e8f0}
.obs-text{font-size:12px;color:#475569;line-height:1.6}
.print-footer{background:#0f1923;padding:12px 28px;display:flex;justify-content:space-between;align-items:center}
.pf-text{font-size:10.5px;color:#64748b}
.btn-print{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#f97316;color:white;border:none;border-radius:7px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit}
.btn-print:hover{background:#ea6c0a}
.no-print{display:flex;justify-content:center;gap:10px;padding:16px;background:#f8fafc;border-top:1px solid #e2e8f0}
@media print{.no-print{display:none!important}body{background:white}.print-wrap{box-shadow:none;margin:0;border-radius:0;max-width:100%}}
</style>
</head>
<body>
<?php
require_once 'config/db.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) die('<p style="padding:40px;text-align:center;color:#dc2626">ID do orçamento não informado.</p>');

$stmt = $db->prepare("
  SELECT o.*, c.nome AS cli_nome, c.cpf_cnpj, c.telefone AS cli_tel, c.email AS cli_email, c.endereco AS cli_end,
         v.placa, v.modelo, v.marca, v.ano, v.cor, v.km, v.seguradora
  FROM orcamentos o
  LEFT JOIN clientes c ON o.cliente_id = c.id
  LEFT JOIN veiculos v ON o.veiculo_id = v.id
  WHERE o.id = ?");
$stmt->bind_param('i', $id); $stmt->execute();
$o = $stmt->get_result()->fetch_assoc();
if (!$o) die('<p style="padding:40px;text-align:center;color:#dc2626">Orçamento não encontrado.</p>');

$si = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id=? ORDER BY id");
$si->bind_param('i', $id); $si->execute();
$itens = $si->get_result()->fetch_all(MYSQLI_ASSOC);

function h($s){ return htmlspecialchars($s??''); }
function fmtD($d){ if(!$d)return'—'; $p=explode('-',$d); return $p[2].'/'.$p[1].'/'.$p[0]; }
function fmtR($v){ return 'R$ '.number_format($v,2,',','.'); }
?>

<div class="print-wrap">
  <!-- Header -->
  <div class="print-header">
    <div class="ph-left">
      <div class="ph-nome">MP Reparos Automotivos</div>
      <div class="ph-sub">Chapeação e Reparos Automotivos</div>
      <div class="ph-info">
        📍 Rua das Oficinas, 100 — Centro, Esteio — RS<br>
        📞 (51) 99999-0000 &nbsp;|&nbsp; ✉ mpreparos@email.com<br>
        CNPJ: 00.000.000/0001-00
      </div>
    </div>
    <div class="ph-right">
      <div class="ph-orc">ORÇAMENTO #<?= h($o['numero']??$o['id']) ?></div>
      <div class="ph-datas">
        Emissão: <?= fmtD($o['data_emissao']) ?><br>
        Validade: <?= fmtD($o['data_validade']) ?><br>
        Status: <?= h($o['status']) ?>
      </div>
    </div>
  </div>
  <div class="orange-bar"></div>

  <!-- Cliente + Veículo -->
  <div class="boxes">
    <div>
      <div class="box-title">Dados do Cliente</div>
      <div class="box-row"><strong><?= h($o['cli_nome']??'—') ?></strong></div>
      <?php if($o['cpf_cnpj']): ?><div class="box-row">CPF/CNPJ: <?= h($o['cpf_cnpj']) ?></div><?php endif; ?>
      <?php if($o['cli_tel']):  ?><div class="box-row">Tel: <?= h($o['cli_tel']) ?></div><?php endif; ?>
      <?php if($o['cli_email']): ?><div class="box-row">E-mail: <?= h($o['cli_email']) ?></div><?php endif; ?>
      <?php if($o['cli_end']):  ?><div class="box-row"><?= h($o['cli_end']) ?></div><?php endif; ?>
    </div>
    <div>
      <div class="box-title">Dados do Veículo</div>
      <div class="box-row"><strong><?= h(($o['marca']??'').' '.($o['modelo']??'')) ?></strong></div>
      <?php if($o['ano']): ?><div class="box-row">Ano: <?= h($o['ano']) ?></div><?php endif; ?>
      <?php if($o['cor']): ?><div class="box-row">Cor: <?= h($o['cor']) ?></div><?php endif; ?>
      <?php if($o['placa']): ?><div class="box-row">Placa: <strong><?= h($o['placa']) ?></strong></div><?php endif; ?>
      <?php if($o['km']): ?><div class="box-row">KM: <?= h($o['km']) ?></div><?php endif; ?>
      <?php if($o['seguradora']): ?><div class="box-row">Seguradora: <?= h($o['seguradora']) ?></div><?php endif; ?>
    </div>
  </div>

  <!-- Itens -->
  <div class="services">
    <div class="sec-title">Serviços e Peças</div>
    <table class="itens-print">
      <thead><tr><th>Descrição</th><th>Qtd.</th><th>Un.</th><th>Valor Unit.</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach($itens as $it): ?>
        <tr>
          <td><?= h($it['descricao']) ?></td>
          <td><?= number_format($it['quantidade'],2,',','.') ?></td>
          <td><?= h($it['unidade']) ?></td>
          <td style="text-align:right"><?= fmtR($it['valor_unit']) ?></td>
          <td><?= fmtR($it['valor_total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$itens): ?>
        <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:16px">Nenhum item cadastrado</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Totais -->
  <div class="totals">
    <div class="tot-r"><span>Subtotal</span><span><?= fmtR($o['subtotal']) ?></span></div>
    <?php if($o['desconto']>0): ?>
    <div class="tot-r"><span>Desconto (<?= number_format($o['desconto'],1,',','.') ?>%)</span>
      <span style="color:#dc2626">− <?= fmtR($o['subtotal']*$o['desconto']/100) ?></span></div>
    <?php endif; ?>
    <div class="tot-final-r"><span>TOTAL</span><span><?= fmtR($o['total']) ?></span></div>
  </div>

  <!-- Condições -->
  <?php if($o['pagamento']||$o['prazo']||$o['garantia']): ?>
  <div class="conds">
    <?php if($o['pagamento']): ?>
    <div><div class="cond-label">Pagamento</div><div class="cond-val"><?= h($o['pagamento']) ?></div></div>
    <?php endif; ?>
    <?php if($o['prazo']): ?>
    <div><div class="cond-label">Prazo de entrega</div><div class="cond-val"><?= h($o['prazo']) ?></div></div>
    <?php endif; ?>
    <?php if($o['garantia']): ?>
    <div><div class="cond-label">Garantia</div><div class="cond-val"><?= h($o['garantia']) ?></div></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Observações -->
  <?php if($o['observacoes']): ?>
  <div class="obs-box">
    <div class="sec-title">Observações</div>
    <div class="obs-text"><?= nl2br(h($o['observacoes'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="print-footer">
    <div class="pf-text">MP Reparos Automotivos — Orçamento #<?= h($o['numero']??$o['id']) ?> — Emitido em <?= fmtD($o['data_emissao']) ?></div>
  </div>

  <!-- Botões (não imprimem) -->
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
    <button onclick="window.close()" style="padding:9px 18px;background:none;border:1.5px solid #e2e8f0;border-radius:7px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">✕ Fechar</button>
  </div>
</div>
</body>
</html>
