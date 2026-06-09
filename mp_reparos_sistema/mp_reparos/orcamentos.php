<?php
$pagina    = 'orcamentos';
$titulo    = 'Orçamentos';
$subtitulo = 'Gestão de orçamentos';
$topbar_acoes = '<button class="btn btn-primary btn-sm" onclick="abrirNovo()">＋ Novo orçamento</button>';

require_once 'config/db.php';
$db = getDB();

include 'includes/topo.php';
?>

<div class="busca-bar">
  <input type="text" id="busca" placeholder="🔍  Buscar por nº, cliente ou veículo..." oninput="carregar()">
  <button class="btn btn-primary" onclick="abrirNovo()">＋ Novo orçamento</button>
</div>

<div class="card">
  <div class="card-body" style="padding:0">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr>
            <th>Nº</th>
            <th>Data</th>
            <th>Cliente</th>
            <th>Veículo</th>
            <th>Total</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody id="tabela-orc"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-orc">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">📄 <span id="modal-orc-titulo">Novo orçamento</span></div>
      <button class="modal-close" onclick="fecharModal('modal-orc')">✕</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="orc-id">

      <div class="hero-orc" style="margin-bottom:16px">
        <div class="hero-left">
          <div class="hero-logo">🚗</div>
          <div>
            <div class="hero-nome">MP Reparos Automotivos</div>
            <div class="hero-info">
             📍 Avenida Luiz Pasteur, 1555 — Tamandaré, Esteio — RS<br>
        📞 (51) 994037229 &nbsp;|&nbsp; ✉ mpautomotivos@gmail.com<br>
        CNPJ: 36.454.523/0001-55
            </div>
          </div>
        </div>
        <div class="hero-badge" id="orc-badge-num">Orçamento #001</div>
      </div>

      <div class="orc-section">
        <div class="orc-section-title">📌 Dados do orçamento</div>

        <div class="g4-orc">
          <div class="field">
            <label>Nº Orçamento</label>
            <input type="text" id="orc-num" value="001" oninput="document.getElementById('orc-badge-num').textContent='Orçamento #'+this.value">
          </div>

          <div class="field">
            <label>Data emissão</label>
            <input type="date" id="orc-data-e">
          </div>

          <div class="field">
            <label>Validade</label>
            <input type="date" id="orc-data-v">
          </div>

          <div class="field">
            <label>Situação</label>
            <select id="orc-situacao">
              <option>Aguardando aprovação</option>
              <option>Aprovado</option>
              <option>Em andamento</option>
              <option>Concluído</option>
              <option>Cancelado</option>
            </select>
          </div>
        </div>
      </div>

      <div class="orc-section">
        <div class="orc-section-title">👤 Cliente e veículo</div>

        <div class="orc-info-grid">
          <div class="field">
            <label>Cliente</label>
            <input type="text" id="orc-cliente-nome" placeholder="Digite o nome do cliente">
          </div>

          <div class="field">
            <label>Veículo</label>
            <input type="text" id="orc-veiculo-desc" placeholder="Ex: Gol G6 branco">
          </div>

          <div class="field">
            <label>Placa</label>
            <input type="text" id="orc-placa" placeholder="Ex: ABC1234">
          </div>

          <div class="field">
            <label>Cor</label>
            <input type="text" id="orc-cor" placeholder="Ex: Branco">
          </div>
        </div>
      </div>

      <div class="orc-section">
        <div class="orc-section-title">🔨 Funilaria e pintura</div>

        <div class="g2" style="margin-bottom:14px">
          <div class="field">
            <label>Tipo de tinta</label>
            <select id="orc-tipo-tinta">
              <option value="">Selecione...</option>
              <option>PU</option>
              <option>Poliéster</option>
              <option>Metálica</option>
              <option>Perolizada</option>
              <option>Verniz</option>
            </select>
          </div>

          <div class="field">
            <label>Seguradora / Revenda</label>
            <input type="text" id="orc-seguradora" placeholder="Ex: Particular, revenda ou seguradora">
          </div>
        </div>

        <div class="field">
          <label>Áreas danificadas</label>
          <div class="danos-grid">
            <label><input type="checkbox" class="chk-dano" value="Para-choque dianteiro"> Para-choque dianteiro</label>
            <label><input type="checkbox" class="chk-dano" value="Capô"> Capô</label>
            <label><input type="checkbox" class="chk-dano" value="Paralama esquerdo"> Paralama esquerdo</label>
            <label><input type="checkbox" class="chk-dano" value="Paralama direito"> Paralama direito</label>
            <label><input type="checkbox" class="chk-dano" value="Porta dianteira esquerda"> Porta dianteira esquerda</label>
            <label><input type="checkbox" class="chk-dano" value="Porta dianteira direita"> Porta dianteira direita</label>
            <label><input type="checkbox" class="chk-dano" value="Porta traseira esquerda"> Porta traseira esquerda</label>
            <label><input type="checkbox" class="chk-dano" value="Porta traseira direita"> Porta traseira direita</label>
            <label><input type="checkbox" class="chk-dano" value="Teto"> Teto</label>
            <label><input type="checkbox" class="chk-dano" value="Tampa traseira"> Tampa traseira</label>
            <label><input type="checkbox" class="chk-dano" value="Para-choque traseiro"> Para-choque traseiro</label>
            <label><input type="checkbox" class="chk-dano" value="Caixa de ar"> Caixa de ar</label>
          </div>
        </div>

        <div class="field">
          <label>Diagnóstico técnico</label>
          <textarea id="orc-diagnostico" placeholder="Descreva os danos e reparos necessários..."></textarea>
        </div>
      </div>

      <div class="orc-section">
        <div class="orc-section-title">💰 Serviços e peças</div>

        <div class="table-wrap">
          <table class="itens">
            <colgroup>
              <col style="width:38%">
              <col style="width:11%">
              <col style="width:9%">
              <col style="width:17%">
              <col style="width:16%">
              <col style="width:9%">
            </colgroup>

            <thead>
              <tr>
                <th>Descrição</th>
                <th>Qtd.</th>
                <th>Un.</th>
                <th style="text-align:right">Valor unit.</th>
                <th style="text-align:right">Total</th>
                <th></th>
              </tr>
            </thead>

            <tbody id="orc-tbody"></tbody>
          </table>
        </div>

        <button class="add-item" type="button" onclick="addItem()">＋ Adicionar item</button>

        <div class="totais-box">
          <div class="tot-row">
            <span class="tot-lbl">Subtotal</span>
            <span class="tot-val" id="orc-sub">R$ 0,00</span>
          </div>

          <div class="tot-row">
            <span class="tot-lbl">Desconto</span>
            <span class="tot-val" style="display:flex;align-items:center;gap:6px">
              <input type="number" class="desc-inp" id="orc-desc" value="0" min="0" max="100" step="0.5" oninput="calcTotais()"> %
            </span>
          </div>

          <div class="tot-final">
            <span class="tot-lbl">Total</span>
            <span class="tot-val" id="orc-tot">R$ 0,00</span>
          </div>
        </div>
      </div>

      <div class="orc-section">
        <div class="orc-section-title">📄 Condições</div>

        <div class="g3">
          <div class="field">
            <label>Pagamento</label>
            <select id="orc-pag">
              <option value="">Selecione...</option>
              <option>À vista — Dinheiro</option>
              <option>À vista — Pix</option>
              <option>Cartão de débito</option>
              <option>Cartão de crédito</option>
              <option>50% entrada + 50% na entrega</option>
              <option>Convênio / Seguradora</option>
              <option>A combinar</option>
            </select>
          </div>

          <div class="field">
            <label>Prazo de entrega</label>
            <input type="text" id="orc-prazo" placeholder="Ex: 5 dias úteis">
          </div>

          <div class="field">
            <label>Garantia</label>
            <input type="text" id="orc-gar" placeholder="Ex: 90 dias">
          </div>
        </div>

        <div class="field">
          <label>Observações</label>
          <textarea id="orc-obs" placeholder="Orçamento válido conforme data acima..."></textarea>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-danger btn-sm" id="btn-del-orc" onclick="deletar()" style="margin-right:auto;display:none">🗑 Excluir</button>
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-orc')">Cancelar</button>
      <button class="btn btn-navy btn-sm" id="btn-imprimir-orc" onclick="imprimirOrc()" style="display:none">🖨️ Imprimir</button>
      <button class="btn btn-primary btn-sm" onclick="salvar()">💾 Salvar</button>
    </div>
  </div>
</div>

<style>
  :root{
  --bg:#0b0b0c;
  --card:#111214;
  --card2:#17181c;

  --red:#ff1e1e;
  --red-dark:#c11212;
  --red-light:#ff4d4d;

  --text:#f8fafc;
  --muted:#9ca3af;

  --border:#2a2c31;

  --shadow:0 10px 30px rgba(255,0,0,.12);
}
.orc-section{
  background:white;
  border:1px solid var(--g200);
  border-radius:12px;
  padding:16px;
  margin-bottom:16px;
  box-shadow:var(--sh);
}

.orc-section-title{
  font-size:12px;
  font-weight:800;
  color:var(--g700);
  text-transform:uppercase;
  letter-spacing:.06em;
  margin-bottom:14px;
  display:flex;
  align-items:center;
  gap:8px;
}

.orc-info-grid{
  display:grid;
  grid-template-columns:1fr 1fr 160px 1fr;
  gap:14px;
}

.g4-orc{
  display:grid;
  grid-template-columns:1fr 1fr 1fr 1fr;
  gap:12px;
}

.danos-grid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:10px;
  font-size:13px;
  color:var(--g700);
}

.danos-grid label{
  background:var(--g50);
  border:1.5px solid var(--g200);
  border-radius:8px;
  padding:10px;
  cursor:pointer;
}

.danos-grid label:hover{
  background:var(--orange-pale);
  border-color:var(--orange);
}

@media(max-width:1000px){
  .orc-info-grid,
  .g4-orc{
    grid-template-columns:1fr 1fr;
  }

  .danos-grid{
    grid-template-columns:repeat(2,1fr);
  }
}

@media(max-width:600px){
  .orc-info-grid,
  .g4-orc,
  .danos-grid{
    grid-template-columns:1fr;
  }
}
</style>
<script>
let orcItens = [];
let orcIdx = 0;
const UNITS = ['un','h','m','m²','kg','g','l','ml','jg','pc','cx','vb'];

async function carregar(){
  const q = document.getElementById('busca').value;
  const resp = await fetch('api/orcamentos.php?q=' + encodeURIComponent(q));
  const list = await resp.json();
  const tbody = document.getElementById('tabela-orc');

  if(!list.length){
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--g400);padding:24px">Nenhum orçamento encontrado</td></tr>';
    return;
  }

  tbody.innerHTML = list.map(o => `
    <tr>
      <td><strong>#${esc(o.numero || o.id)}</strong></td>
      <td>${fmtData(o.data_emissao)}</td>
      <td>${esc(o.cliente_nome || '—')}</td>
      <td>${esc(o.placa || '—')} ${esc(o.modelo || '')}</td>
      <td><strong>${fmtMoeda(o.total)}</strong></td>
      <td><span class="badge badge-orange">${esc(o.status)}</span></td>
      <td>
        <div class="td-acoes">
          <button class="btn btn-ghost btn-xs" onclick="editar(${Number(o.id)})">✏️ Editar</button>
          <a href="orcamento_print.php?id=${Number(o.id)}" target="_blank" class="btn btn-ghost btn-xs">🖨️ Imprimir</a>
        </div>
      </td>
    </tr>
  `).join('');
}

function esc(s){
  if(s === null || s === undefined){
    return '';
  }

  s = String(s);

  return s
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

function fmtData(d){
  if(!d) return '—';
  d = String(d);
  const p = d.split('-');
  if(p.length !== 3) return d;
  return p[2] + '/' + p[1] + '/' + p[0];
}

function fmtMoeda(v){
  return 'R$ ' + Number(v || 0).toLocaleString('pt-BR',{minimumFractionDigits:2});
}

function pn(v){
  return parseFloat((v + '').replace(',','.')) || 0;
}

function limparFunilaria(){
  [
    'orc-cliente-nome',
    'orc-veiculo-desc',
    'orc-placa',
    'orc-cor',
    'orc-seguradora',
    'orc-diagnostico'
  ].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.value = '';
  });

  const tipo = document.getElementById('orc-tipo-tinta');
  if(tipo) tipo.value = '';

  document.querySelectorAll('.chk-dano').forEach(c => c.checked = false);
}

function coletarFunilaria(){
  return {
    cliente_nome: document.getElementById('orc-cliente-nome')?.value || '',
    veiculo_desc: document.getElementById('orc-veiculo-desc')?.value || '',
    placa: document.getElementById('orc-placa')?.value || '',
    cor: document.getElementById('orc-cor')?.value || '',
    tipo_tinta: document.getElementById('orc-tipo-tinta')?.value || '',
    seguradora: document.getElementById('orc-seguradora')?.value || '',
    areas: [...document.querySelectorAll('.chk-dano:checked')].map(c => c.value),
    diagnostico: document.getElementById('orc-diagnostico')?.value || ''
  };
}

function preencherFunilaria(dados){
  limparFunilaria();

  if(!dados) return;

  let d = {};

  try{
    d = JSON.parse(dados);
  }catch(e){
    d = {diagnostico:String(dados)};
  }

  document.getElementById('orc-cliente-nome').value = d.cliente_nome || '';
  document.getElementById('orc-veiculo-desc').value = d.veiculo_desc || '';
  document.getElementById('orc-placa').value = d.placa || '';
  document.getElementById('orc-cor').value = d.cor || '';
  document.getElementById('orc-tipo-tinta').value = d.tipo_tinta || '';
  document.getElementById('orc-seguradora').value = d.seguradora || '';
  document.getElementById('orc-diagnostico').value = d.diagnostico || '';

  const areas = Array.isArray(d.areas) ? d.areas : [];

  document.querySelectorAll('.chk-dano').forEach(c => {
    c.checked = areas.includes(c.value);
  });
}

function abrirNovo(){
  document.getElementById('orc-id').value = '';
  document.getElementById('orc-num').value = '001';
  document.getElementById('orc-badge-num').textContent = 'Orçamento #001';

  document.getElementById('orc-desc').value = '0';
  document.getElementById('orc-pag').value = '';
  document.getElementById('orc-prazo').value = '';
  document.getElementById('orc-gar').value = '';
  document.getElementById('orc-obs').value = '';
  document.getElementById('orc-situacao').selectedIndex = 0;

  limparFunilaria();

  document.getElementById('modal-orc-titulo').textContent = 'Novo orçamento';
  document.getElementById('btn-del-orc').style.display = 'none';
  document.getElementById('btn-imprimir-orc').style.display = 'none';

  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById('orc-data-e').value = hoje;

  const val = new Date();
  val.setDate(val.getDate() + 30);
  document.getElementById('orc-data-v').value = val.toISOString().split('T')[0];

  orcItens = [];
  orcIdx = 0;
  renderItens();
  calcTotais();

  addItem();
  addItem();

  abrirModal('modal-orc');
}

async function editar(id){
  const resp = await fetch('api/orcamentos.php?id=' + encodeURIComponent(id));
  const o = await resp.json();

  if(o.erro){
    toast(o.erro, 'err');
    return;
  }

  document.getElementById('orc-id').value = o.id;
  document.getElementById('orc-num').value = o.numero || o.id;
  document.getElementById('orc-badge-num').textContent = 'Orçamento #' + (o.numero || o.id);
  document.getElementById('orc-data-e').value = o.data_emissao || '';
  document.getElementById('orc-data-v').value = o.data_validade || '';
  document.getElementById('orc-situacao').value = o.status || 'Aguardando aprovação';

  document.getElementById('orc-desc').value = o.desconto || 0;
  document.getElementById('orc-pag').value = o.pagamento || '';
  document.getElementById('orc-prazo').value = o.prazo || '';
  document.getElementById('orc-gar').value = o.garantia || '';
  document.getElementById('orc-obs').value = o.observacoes || '';

  preencherFunilaria(o.danos || '');

  document.getElementById('modal-orc-titulo').textContent = 'Editar orçamento';
  document.getElementById('btn-del-orc').style.display = 'inline-flex';
  document.getElementById('btn-imprimir-orc').style.display = 'inline-flex';

  orcItens = (o.itens || []).map((item, index) => ({
    idx: index,
    desc: item.descricao || '',
    qtd: item.quantidade || 1,
    un: item.unidade || 'un',
    unit: item.valor_unit || ''
  }));

  orcIdx = orcItens.length;

  renderItens();
  calcTotais();

  abrirModal('modal-orc');
}

function addItem(desc='', qtd=1, un='un', unit=''){
  orcItens.push({idx:orcIdx++, desc, qtd, un, unit});
  renderItens();
  calcTotais();
}

function removeItem(idx){
  orcItens = orcItens.filter(i => i.idx !== idx);
  renderItens();
  calcTotais();
}

function renderItens(){
  const tb = document.getElementById('orc-tbody');
  tb.innerHTML = '';

  orcItens.forEach((it,i) => {
    const tot = pn(it.qtd) * pn(it.unit);
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td><input type="text" value="${esc(it.desc || '')}" placeholder="Descrição..." oninput="orcItens[${i}].desc=this.value"></td>
      <td><input type="number" value="${esc(it.qtd || 1)}" min="0.01" step="0.01" style="text-align:right" oninput="orcItens[${i}].qtd=this.value;updItem(${i})"></td>
      <td><select oninput="orcItens[${i}].un=this.value">${UNITS.map(u => `<option${it.un === u ? ' selected' : ''}>${esc(u)}</option>`).join('')}</select></td>
      <td><input type="number" value="${esc(it.unit || '')}" min="0" step="0.01" placeholder="0,00" style="text-align:right" oninput="orcItens[${i}].unit=this.value;updItem(${i})"></td>
      <td class="rt" id="it-${i}">${fmtMoeda(tot)}</td>
      <td><button class="del-row" onclick="removeItem(${Number(it.idx)})">✕</button></td>
    `;

    tb.appendChild(tr);
  });
}

function updItem(i){
  const t = pn(orcItens[i].qtd) * pn(orcItens[i].unit);
  const el = document.getElementById('it-' + i);

  if(el) el.textContent = fmtMoeda(t);

  calcTotais();
}

function calcTotais(){
  const sub = orcItens.reduce((s,it) => s + pn(it.qtd) * pn(it.unit), 0);
  const dp = Math.min(Math.max(pn(document.getElementById('orc-desc').value), 0), 100);
  const tot = sub * (1 - dp / 100);

  document.getElementById('orc-sub').textContent = fmtMoeda(sub);
  document.getElementById('orc-tot').textContent = fmtMoeda(tot);
}

async function salvar(){
  const id = document.getElementById('orc-id').value;

  const itens = orcItens.map((_,i) => ({
    descricao: document.getElementById('orc-tbody').rows[i]?.cells[0]?.querySelector('input')?.value || orcItens[i].desc,
    quantidade: pn(orcItens[i].qtd),
    unidade: orcItens[i].un || 'un',
    valor_unit: pn(orcItens[i].unit),
    valor_total: pn(orcItens[i].qtd) * pn(orcItens[i].unit)
  })).filter(it => it.descricao);

  const sub = itens.reduce((s,it) => s + it.valor_total, 0);
  const dp = pn(document.getElementById('orc-desc').value);
  const tot = sub * (1 - dp / 100);

  const funilaria = coletarFunilaria();

  const payload = {
    id,
    numero: document.getElementById('orc-num').value,
    cliente_nome: funilaria.cliente_nome,
    veiculo_desc: funilaria.veiculo_desc,
    placa: funilaria.placa,
    cor: funilaria.cor,
    cliente_id: null,
    veiculo_id: null,
    data_emissao: document.getElementById('orc-data-e').value,
    data_validade: document.getElementById('orc-data-v').value,
    status: document.getElementById('orc-situacao').value,
    pagamento: document.getElementById('orc-pag').value,
    prazo: document.getElementById('orc-prazo').value,
    garantia: document.getElementById('orc-gar').value,
    desconto: dp,
    observacoes: document.getElementById('orc-obs').value,
    danos: JSON.stringify(funilaria),
    subtotal: sub,
    total: tot,
    itens
  };

  const action = id ? 'update' : 'create';

  const resp = await fetch('api/orcamentos.php?action=' + action, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  });

  const ret = await resp.json();

  if(ret.erro){
    toast(ret.erro,'err');
    return;
  }

  toast(id ? 'Orçamento atualizado!' : 'Orçamento criado!','ok');
  fecharModal('modal-orc');
  carregar();
}

async function deletar(){
  const id = document.getElementById('orc-id').value;

  if(!id || !confirm('Excluir este orçamento?')) return;

  const resp = await fetch('api/orcamentos.php?action=delete', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  });

  const ret = await resp.json();

  if(ret.erro){
    toast(ret.erro,'err');
    return;
  }

  toast('Orçamento excluído','ok');
  fecharModal('modal-orc');
  carregar();
}

function imprimirOrc(){
  const id = document.getElementById('orc-id').value;

  if(id){
    window.open('orcamento_print.php?id=' + encodeURIComponent(id), '_blank');
  }
}

carregar();
</script>

<?php include 'includes/rodape.php'; ?>