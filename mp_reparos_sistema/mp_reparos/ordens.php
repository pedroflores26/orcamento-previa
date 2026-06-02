<?php
$pagina = 'ordens';
$titulo = 'Ordens de Serviço';
$subtitulo = 'Painel dos serviços da oficina';
$topbar_acoes = '<button class="btn btn-primary btn-sm" onclick="carregar()">↻ Atualizar</button>';

require_once 'config/db.php';
$db = getDB();

include 'includes/topo.php';
?>

<div class="busca-bar">
  <input type="text" id="busca" placeholder="🔍 Buscar por cliente, placa, veículo, OS ou status..." oninput="carregar()">
  <button class="btn btn-primary" onclick="carregar()">↻ Atualizar</button>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label">Total de OS</div>
    <div class="stat-val" id="st-total">0</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Aguardando</div>
    <div class="stat-val orange" id="st-aguardando">0</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Em andamento</div>
    <div class="stat-val blue" id="st-andamento">0</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Finalizadas</div>
    <div class="stat-val green" id="st-finalizado">0</div>
  </div>
</div>

<div class="os-board" id="os-board"></div>

<div class="modal-overlay" id="modal-os">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">🔧 <span id="modal-os-titulo">Ordem de Serviço</span></div>
      <button class="modal-close" onclick="fecharModal('modal-os')">✕</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="os-id">

      <div class="os-header-box">
        <div>
          <div class="os-num" id="os-numero">OS #</div>
          <div class="os-sub" id="os-cliente-veiculo">Cliente / Veículo</div>
        </div>
        <div class="os-badge" id="os-badge-status">Aguardando</div>
      </div>

      <div class="g3" style="margin-top:16px">
        <div class="field">
          <label>Status</label>
          <select id="os-status">
            <option>Aguardando</option>
            <option>Desmontagem</option>
            <option>Funilaria</option>
            <option>Preparação</option>
            <option>Pintura</option>
            <option>Montagem</option>
            <option>Polimento</option>
            <option>Finalizado</option>
            <option>Entregue</option>
            <option>Cancelado</option>
          </select>
        </div>

        <div class="field">
          <label>Prioridade</label>
          <select id="os-prioridade">
            <option>Normal</option>
            <option>Alta</option>
            <option>Urgente</option>
          </select>
        </div>

        <div class="field">
          <label>Previsão de entrega</label>
          <input type="date" id="os-data-entrega">
        </div>
      </div>

      <div class="field">
        <label>Tarefas para os funcionários</label>
        <textarea id="os-tarefas" placeholder="Ex: Desmontar para-choque, recuperar paralama, preparar para pintura..."></textarea>
      </div>

      <div class="field">
        <label>Observações internas</label>
        <textarea id="os-observacoes" placeholder="Observações para equipe interna..."></textarea>
      </div>

      <div class="os-print-box">
        <div class="os-print-title">Checklist da oficina</div>

        <div class="os-check-grid">
          <label><input type="checkbox"> Conferir peças</label>
          <label><input type="checkbox"> Desmontagem</label>
          <label><input type="checkbox"> Funilaria</label>
          <label><input type="checkbox"> Preparação</label>
          <label><input type="checkbox"> Pintura</label>
          <label><input type="checkbox"> Montagem</label>
          <label><input type="checkbox"> Polimento</label>
          <label><input type="checkbox"> Revisão final</label>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-os')">Cancelar</button>
      <button class="btn btn-navy btn-sm" onclick="abrirFolhaOS()">🖨️ Imprimir folha</button>
      <button class="btn btn-primary btn-sm" onclick="salvarOS()">💾 Salvar OS</button>
    </div>
  </div>
</div>

<style>
.os-board{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:16px;
}

.os-card{
  background:white;
  border:1px solid var(--g200);
  border-radius:14px;
  padding:16px;
  box-shadow:var(--sh);
  cursor:pointer;
  transition:.2s;
}

.os-card:hover{
  transform:translateY(-3px);
  box-shadow:var(--sh2);
  border-color:var(--orange);
}

.os-card-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
  margin-bottom:12px;
}

.os-title{
  font-size:17px;
  font-weight:800;
  color:var(--g800);
}

.os-sub{
  font-size:13px;
  color:var(--g500);
  margin-top:3px;
}

.os-placa{
  font-size:13px;
  font-weight:800;
  color:var(--orange);
  margin-top:8px;
}

.os-status{
  display:inline-flex;
  padding:5px 10px;
  border-radius:20px;
  font-size:11px;
  font-weight:800;
  white-space:nowrap;
  background:var(--orange-pale);
  color:var(--orange);
}

.os-status.Finalizado,
.os-status.Entregue{
  background:var(--green-pale);
  color:var(--green);
}

.os-status.Pintura,
.os-status.Funilaria,
.os-status.Preparação,
.os-status.Montagem,
.os-status.Desmontagem,
.os-status.Polimento{
  background:var(--blue-pale);
  color:var(--blue);
}

.os-status.Cancelado{
  background:var(--red-pale);
  color:var(--red);
}

.os-mini{
  margin-top:12px;
  padding:10px;
  background:var(--g50);
  border-radius:10px;
  font-size:13px;
  color:var(--g600);
  line-height:1.5;
  min-height:60px;
}

.os-header-box{
  background:linear-gradient(135deg,var(--navy),#0a1628);
  color:white;
  border-radius:14px;
  padding:18px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:15px;
}

.os-num{
  font-size:22px;
  font-weight:900;
}

.os-badge{
  background:var(--orange);
  color:white;
  padding:8px 14px;
  border-radius:20px;
  font-size:12px;
  font-weight:800;
}

.os-print-box{
  margin-top:16px;
  padding:16px;
  border:1px solid var(--g200);
  border-radius:12px;
  background:var(--g50);
}

.os-print-title{
  font-size:12px;
  font-weight:900;
  color:var(--g700);
  text-transform:uppercase;
  letter-spacing:.06em;
  margin-bottom:10px;
}

.os-check-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:10px;
}

.os-check-grid label{
  background:white;
  border:1px solid var(--g200);
  border-radius:8px;
  padding:10px;
  font-size:13px;
}

@media(max-width:900px){
  .os-check-grid{
    grid-template-columns:repeat(2,1fr);
  }
}

@media(max-width:600px){
  .os-check-grid{
    grid-template-columns:1fr;
  }
}
</style>

<script>
let ORDENS = [];

async function carregar(){
  const q = document.getElementById('busca').value;
  const resp = await fetch('api/ordens.php?q=' + encodeURIComponent(q));
  const list = await resp.json();

  ORDENS = Array.isArray(list) ? list : [];

  renderStats(ORDENS);
  renderOrdens(ORDENS);
}

function renderStats(list){
  document.getElementById('st-total').textContent = list.length;
  document.getElementById('st-aguardando').textContent = list.filter(o => o.status === 'Aguardando').length;
  document.getElementById('st-andamento').textContent = list.filter(o => !['Aguardando','Finalizado','Entregue','Cancelado'].includes(o.status)).length;
  document.getElementById('st-finalizado').textContent = list.filter(o => ['Finalizado','Entregue'].includes(o.status)).length;
}

function renderOrdens(list){
  const board = document.getElementById('os-board');

  if(!list.length){
    board.innerHTML = `
      <div class="card" style="grid-column:1/-1">
        <div class="card-body" style="text-align:center;color:var(--g400);padding:40px">
          Nenhuma ordem de serviço encontrada.
        </div>
      </div>
    `;
    return;
  }

  board.innerHTML = list.map(o => `
    <div class="os-card" onclick="abrirOS(${Number(o.id)})">
      <div class="os-card-top">
        <div>
          <div class="os-title">${esc(o.modelo || 'Veículo')}</div>
          <div class="os-sub">${esc(o.cliente_nome || 'Cliente não informado')}</div>
          <div class="os-placa">${esc(o.placa || 'Sem placa')}</div>
        </div>

        <span class="os-status ${esc(o.status || 'Aguardando')}">
          ${esc(o.status || 'Aguardando')}
        </span>
      </div>

      <div class="os-mini">
        ${esc((o.tarefas || 'Sem tarefas cadastradas').substring(0,120))}
      </div>
    </div>
  `).join('');
}

async function abrirOS(id){
  const resp = await fetch('api/ordens.php?id=' + encodeURIComponent(id));
  const o = await resp.json();

  if(o.erro){
    toast(o.erro,'err');
    return;
  }

  document.getElementById('os-id').value = o.id;
  document.getElementById('os-numero').textContent = 'OS #' + (o.numero_os || o.id);
  document.getElementById('os-cliente-veiculo').textContent =
    (o.cliente_nome || 'Cliente') + ' — ' + (o.modelo || 'Veículo') + ' — ' + (o.placa || 'Sem placa');

  document.getElementById('os-status').value = o.status || 'Aguardando';
  document.getElementById('os-prioridade').value = o.prioridade || 'Normal';
  document.getElementById('os-data-entrega').value = o.data_entrega || '';
  document.getElementById('os-tarefas').value = o.tarefas || '';
  document.getElementById('os-observacoes').value = o.observacoes || '';
  document.getElementById('os-badge-status').textContent = o.status || 'Aguardando';

  document.getElementById('modal-os-titulo').textContent = 'OS #' + (o.numero_os || o.id);

  abrirModal('modal-os');
}

async function salvarOS(){
  const id = document.getElementById('os-id').value;

  const payload = {
    id,
    status: document.getElementById('os-status').value,
    prioridade: document.getElementById('os-prioridade').value,
    data_entrega: document.getElementById('os-data-entrega').value,
    tarefas: document.getElementById('os-tarefas').value,
    observacoes: document.getElementById('os-observacoes').value
  };

  const resp = await fetch('api/ordens.php?action=update', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  });

  const ret = await resp.json();

  if(ret.erro){
    toast(ret.erro,'err');
    return;
  }

  toast('Ordem atualizada!','ok');
  fecharModal('modal-os');
  carregar();
}

function abrirFolhaOS(){
  const id = document.getElementById('os-id').value;

  if(!id){
    toast('Abra uma ordem primeiro','err');
    return;
  }

  window.open('ordem_visualizar.php?id=' + encodeURIComponent(id), '_blank');
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

carregar();
</script>

<?php include 'includes/rodape.php'; ?>