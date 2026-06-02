<?php
$pagina = 'faturas';
$titulo = 'Faturas';
$subtitulo = 'Orçamento final para pagamento';

require_once 'config/db.php';
$db = getDB();

$clientes = $db->query("SELECT id, nome FROM clientes ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

include 'includes/topo.php';
?>

<div class="busca-bar">
  <input type="text" id="busca" placeholder="🔍 Buscar por cliente, nº ou status..." oninput="carregarFaturas()">
  <button class="btn btn-primary" onclick="abrirNovaFatura()">＋ Nova fatura</button>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr>
            <th>Nº</th>
            <th>Data</th>
            <th>Cliente</th>
            <th>Carros</th>
            <th>Total</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody id="tbody-faturas"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-fatura">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">📄 <span id="modal-fatura-titulo">Nova fatura</span></div>
      <button class="modal-close" onclick="fecharModal('modal-fatura')">✕</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="fat-id">

      <div class="g3">
        <div class="field">
          <label>Nº Fatura</label>
          <input type="text" id="fat-numero" placeholder="Automático">
        </div>

        <div class="field">
          <label>Data emissão</label>
          <input type="date" id="fat-data">
        </div>

        <div class="field">
          <label>Status</label>
          <select id="fat-status">
            <option>Aberta</option>
            <option>Enviada</option>
            <option>Paga</option>
            <option>Cancelada</option>
          </select>
        </div>
      </div>

      <div class="field">
        <label>Cliente</label>
        <select id="fat-cliente" onchange="carregarOrcamentosCliente()">
          <option value="">Selecione o cliente...</option>
          <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="card" style="margin-top:14px;">
        <div class="card-header">
          <div class="card-title">Carros / pré-orçamentos do cliente</div>
        </div>

        <div class="card-body" id="orcamentos-cliente-box">
          <div style="text-align:center;color:var(--g400);padding:20px;">
            Selecione um cliente para carregar os carros.
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px;">
        <div class="card-header">
          <div class="card-title">Itens selecionados</div>
        </div>

        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table class="tabela">
              <thead>
                <tr>
                  <th>Descrição</th>
                  <th>Veículo</th>
                  <th>Placa</th>
                  <th>Valor</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="fat-itens"></tbody>
            </table>
          </div>
        </div>
      </div>

      <button class="add-item" type="button" onclick="addItemManual()">＋ Adicionar item manual</button>

      <div class="totais-box">
        <div class="tot-final">
          <span class="tot-lbl">Total da fatura</span>
          <span class="tot-val" id="fat-total">R$ 0,00</span>
        </div>
      </div>

      <div class="field" style="margin-top:14px;">
        <label>Observações</label>
        <textarea id="fat-obs" placeholder="Ex: Pagamento referente aos veículos listados acima..."></textarea>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-danger btn-sm" id="btn-del-fat" onclick="deletarFatura()" style="margin-right:auto;display:none;">🗑 Excluir</button>
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-fatura')">Cancelar</button>
      <button class="btn btn-navy btn-sm" id="btn-print-fat" onclick="imprimirFatura()" style="display:none;">🖨️ Imprimir</button>
      <button class="btn btn-primary btn-sm" onclick="salvarFatura()">💾 Salvar</button>
    </div>
  </div>
</div>

<style>
.fatura-orc-list{
  display:grid;
  grid-template-columns:1fr;
  gap:8px;
}

.fatura-orc-item{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  border:1px solid var(--g200);
  background:var(--g50);
  border-radius:10px;
  padding:12px;
}

.fatura-orc-title{
  font-weight:800;
  color:var(--g800);
}

.fatura-orc-sub{
  font-size:12px;
  color:var(--g500);
  margin-top:2px;
}

.fatura-orc-valor{
  font-size:16px;
  font-weight:900;
  color:var(--green);
  white-space:nowrap;
}

.fat-row-input{
  width:100%;
  border:1px solid var(--g200);
  border-radius:6px;
  padding:7px;
}
</style>

<script>
let FAT_ITENS = [];

async function carregarFaturas(){
  const q = document.getElementById('busca').value;
  const resp = await fetch('api/faturas.php?q=' + encodeURIComponent(q));
  const list = await resp.json();
  const tbody = document.getElementById('tbody-faturas');

  if(!Array.isArray(list) || !list.length){
    tbody.innerHTML = `
      <tr>
        <td colspan="7" style="text-align:center;padding:25px;color:var(--g400);">
          Nenhuma fatura encontrada.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = list.map(f => `
    <tr>
      <td><strong>#${esc(f.numero || f.id)}</strong></td>
      <td>${fmtData(f.data_emissao)}</td>
      <td>${esc(f.cliente_nome || '—')}</td>
      <td>${Number(f.qtd_itens || 0)}</td>
      <td><strong>${fmtMoeda(f.total)}</strong></td>
      <td><span class="badge badge-orange">${esc(f.status || 'Aberta')}</span></td>
      <td>
        <div class="td-acoes">
          <button class="btn btn-ghost btn-xs" onclick="editarFatura(${Number(f.id)})">✏️ Editar</button>
          <a href="fatura_print.php?id=${Number(f.id)}" target="_blank" class="btn btn-navy btn-xs">🖨️ Imprimir</a>
        </div>
      </td>
    </tr>
  `).join('');
}

function abrirNovaFatura(){
  document.getElementById('fat-id').value = '';
  document.getElementById('fat-numero').value = '';
  document.getElementById('fat-data').value = new Date().toISOString().split('T')[0];
  document.getElementById('fat-status').value = 'Aberta';
  document.getElementById('fat-cliente').value = '';
  document.getElementById('fat-obs').value = '';
  document.getElementById('modal-fatura-titulo').textContent = 'Nova fatura';
  document.getElementById('btn-del-fat').style.display = 'none';
  document.getElementById('btn-print-fat').style.display = 'none';

  FAT_ITENS = [];
  renderItens();
  document.getElementById('orcamentos-cliente-box').innerHTML = `
    <div style="text-align:center;color:var(--g400);padding:20px;">
      Selecione um cliente para carregar os carros.
    </div>
  `;

  abrirModal('modal-fatura');
}

async function carregarOrcamentosCliente(){
  const clienteId = document.getElementById('fat-cliente').value;
  const box = document.getElementById('orcamentos-cliente-box');

  if(!clienteId){
    box.innerHTML = `<div style="text-align:center;color:var(--g400);padding:20px;">Selecione um cliente.</div>`;
    return;
  }

  const resp = await fetch('api/faturas.php?action=orcamentos_cliente&cliente_id=' + encodeURIComponent(clienteId));
  const list = await resp.json();

  if(!Array.isArray(list) || !list.length){
    box.innerHTML = `<div style="text-align:center;color:var(--g400);padding:20px;">Nenhum pré-orçamento encontrado para este cliente.</div>`;
    return;
  }

  box.innerHTML = `
    <div class="fatura-orc-list">
      ${list.map(o => `
        <div class="fatura-orc-item">
          <div>
            <div class="fatura-orc-title">#${esc(o.numero || o.id)} — ${esc(o.modelo || 'Veículo')} ${o.placa ? '— '+esc(o.placa) : ''}</div>
            <div class="fatura-orc-sub">Emitido em ${fmtData(o.data_emissao)}</div>
          </div>

          <div style="display:flex;align-items:center;gap:10px;">
            <div class="fatura-orc-valor">${fmtMoeda(o.total)}</div>
            <button class="btn btn-primary btn-xs" onclick='adicionarOrcamento(${JSON.stringify(o)})'>＋ Adicionar</button>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

function adicionarOrcamento(o){
  const existe = FAT_ITENS.some(i => Number(i.orcamento_id) === Number(o.id));

  if(existe){
    toast('Este carro já foi adicionado','err');
    return;
  }

  FAT_ITENS.push({
    orcamento_id: o.id,
    descricao: 'Serviço de funilaria e pintura',
    veiculo: o.modelo || '',
    placa: o.placa || '',
    valor: Number(o.total || 0)
  });

  renderItens();
}

function addItemManual(){
  FAT_ITENS.push({
    orcamento_id: null,
    descricao: '',
    veiculo: '',
    placa: '',
    valor: 0
  });

  renderItens();
}

function removerItem(i){
  FAT_ITENS.splice(i,1);
  renderItens();
}

function renderItens(){
  const tbody = document.getElementById('fat-itens');

  if(!FAT_ITENS.length){
    tbody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align:center;padding:18px;color:var(--g400);">
          Nenhum item selecionado.
        </td>
      </tr>
    `;
    calcTotal();
    return;
  }

  tbody.innerHTML = FAT_ITENS.map((it,i) => `
    <tr>
      <td><input class="fat-row-input" value="${esc(it.descricao)}" oninput="FAT_ITENS[${i}].descricao=this.value"></td>
      <td><input class="fat-row-input" value="${esc(it.veiculo)}" oninput="FAT_ITENS[${i}].veiculo=this.value"></td>
      <td><input class="fat-row-input" value="${esc(it.placa)}" oninput="FAT_ITENS[${i}].placa=this.value"></td>
      <td><input class="fat-row-input" type="number" min="0" step="0.01" value="${Number(it.valor || 0)}" oninput="FAT_ITENS[${i}].valor=this.value;calcTotal()"></td>
      <td><button class="btn btn-danger btn-xs" onclick="removerItem(${i})">✕</button></td>
    </tr>
  `).join('');

  calcTotal();
}

function calcTotal(){
  const total = FAT_ITENS.reduce((s,it) => s + Number(it.valor || 0), 0);
  document.getElementById('fat-total').textContent = fmtMoeda(total);
}

async function salvarFatura(){
  const id = document.getElementById('fat-id').value;

  const payload = {
    id,
    numero: document.getElementById('fat-numero').value,
    cliente_id: document.getElementById('fat-cliente').value,
    data_emissao: document.getElementById('fat-data').value,
    status: document.getElementById('fat-status').value,
    observacoes: document.getElementById('fat-obs').value,
    itens: FAT_ITENS.map(i => ({
      orcamento_id: i.orcamento_id,
      descricao: i.descricao,
      veiculo: i.veiculo,
      placa: i.placa,
      valor: Number(i.valor || 0)
    }))
  };

  const action = id ? 'update' : 'create';

  const resp = await fetch('api/faturas.php?action=' + action, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  });

  const ret = await resp.json();

  if(ret.erro){
    toast(ret.erro,'err');
    return;
  }

  toast(id ? 'Fatura atualizada!' : 'Fatura criada!','ok');
  fecharModal('modal-fatura');
  carregarFaturas();
}

async function editarFatura(id){
  const resp = await fetch('api/faturas.php?id=' + encodeURIComponent(id));
  const f = await resp.json();

  if(f.erro){
    toast(f.erro,'err');
    return;
  }

  document.getElementById('fat-id').value = f.id;
  document.getElementById('fat-numero').value = f.numero || f.id;
  document.getElementById('fat-data').value = f.data_emissao || '';
  document.getElementById('fat-status').value = f.status || 'Aberta';
  document.getElementById('fat-cliente').value = f.cliente_id || '';
  document.getElementById('fat-obs').value = f.observacoes || '';
  document.getElementById('modal-fatura-titulo').textContent = 'Editar fatura';
  document.getElementById('btn-del-fat').style.display = 'inline-flex';
  document.getElementById('btn-print-fat').style.display = 'inline-flex';

  FAT_ITENS = (f.itens || []).map(it => ({
    orcamento_id: it.orcamento_id,
    descricao: it.descricao,
    veiculo: it.veiculo,
    placa: it.placa,
    valor: Number(it.valor || 0)
  }));

  renderItens();
  carregarOrcamentosCliente();

  abrirModal('modal-fatura');
}

async function deletarFatura(){
  const id = document.getElementById('fat-id').value;

  if(!id || !confirm('Excluir esta fatura?')) return;

  const resp = await fetch('api/faturas.php?action=delete', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  });

  const ret = await resp.json();

  if(ret.erro){
    toast(ret.erro,'err');
    return;
  }

  toast('Fatura excluída','ok');
  fecharModal('modal-fatura');
  carregarFaturas();
}

function imprimirFatura(){
  const id = document.getElementById('fat-id').value;

  if(id){
    window.open('fatura_print.php?id=' + encodeURIComponent(id), '_blank');
  }
}

function esc(s){
  if(s === null || s === undefined) return '';
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

function fmtData(d){
  if(!d) return '—';
  const p = String(d).split('-');
  if(p.length !== 3) return d;
  return p[2] + '/' + p[1] + '/' + p[0];
}

function fmtMoeda(v){
  return 'R$ ' + Number(v || 0).toLocaleString('pt-BR',{minimumFractionDigits:2});
}

carregarFaturas();
</script>

<?php include 'includes/rodape.php'; ?>