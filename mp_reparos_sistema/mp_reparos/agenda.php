<?php
$pagina    = 'agenda';
$titulo    = 'Agenda da Semana';
$subtitulo = 'Agendamentos e serviços';
$topbar_acoes = '<button class="btn btn-primary btn-sm" onclick="abrirNovoAgendamento()">＋ Novo agendamento</button>';
require_once 'config/db.php';
$db = getDB();

// Carregar clientes e veículos para os selects
$clientes = $db->query("SELECT id, nome, telefone FROM clientes ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$veiculos = $db->query("SELECT id, cliente_id, placa, modelo FROM veiculos ORDER BY placa")->fetch_all(MYSQLI_ASSOC);
include 'includes/topo.php';
?>

<!-- Stats semana -->
<div class="stats-grid" id="stats-semana">
  <div class="stat-card"><div class="stat-label">Total na semana</div><div class="stat-val" id="s-total">—</div></div>
  <div class="stat-card"><div class="stat-label">Concluídos</div><div class="stat-val green" id="s-prontos">—</div></div>
  <div class="stat-card"><div class="stat-label">Em andamento</div><div class="stat-val blue" id="s-andamento">—</div></div>
  <div class="stat-card"><div class="stat-label">Faturamento semana</div><div class="stat-val orange" id="s-fat">—</div></div>
</div>

<!-- Navegação semana -->
<div class="semana-nav">
  <div class="semana-ctrl">
    <button class="nav-week-btn" onclick="mudarSemana(-1)">◀</button>
    <div class="semana-label" id="semana-label">—</div>
    <button class="nav-week-btn" onclick="mudarSemana(1)">▶</button>
    <button class="btn btn-ghost btn-sm" onclick="irHoje()">Hoje</button>
  </div>
  <button class="btn btn-primary btn-sm" onclick="abrirNovoAgendamento()">＋ Novo agendamento</button>
</div>

<div class="semana-grid" id="semana-grid"></div>

<!-- ══ MODAL AGENDAMENTO ══ -->
<div class="modal-overlay" id="modal-ag">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">🗓 <span id="modal-ag-titulo">Novo agendamento</span></div>
      <button class="modal-close" onclick="fecharModal('modal-ag')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ag-id">

      <div class="g2">
        <div class="field">
          <label>Cliente</label>
          <select id="ag-cliente" onchange="carregarVeiculosCliente()">
            <option value="">Selecione o cliente...</option>
            <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Veículo</label>
          <select id="ag-veiculo">
            <option value="">Selecione o veículo...</option>
            <?php foreach($veiculos as $v): ?>
            <option value="<?= $v['id'] ?>" data-cliente="<?= $v['cliente_id'] ?>">
              <?= htmlspecialchars($v['placa'].' — '.$v['modelo']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="g2">
        <div class="field">
          <label>Data</label>
          <input type="date" id="ag-data">
        </div>
        <div class="field">
          <label>Hora (opcional)</label>
          <input type="time" id="ag-hora">
        </div>
      </div>

      <div class="field">
        <label>Serviços a realizar</label>
        <div class="svc-list" id="svc-list"></div>
        <button class="add-svc" type="button" onclick="addSvc()">＋ Adicionar serviço</button>
      </div>

      <div class="field" style="margin-top:12px">
        <label>Status</label>
        <div class="status-pills" id="status-pills">
          <div class="s-pill aguardando sel" onclick="selStatus('aguardando')">⏳ Aguardando</div>
          <div class="s-pill andamento"      onclick="selStatus('andamento')">🔧 Em andamento</div>
          <div class="s-pill pronto"         onclick="selStatus('pronto')">✅ Pronto</div>
          <div class="s-pill cancelado"      onclick="selStatus('cancelado')">❌ Cancelado</div>
        </div>
      </div>

      <div class="field" style="margin-top:12px">
        <label>Observação</label>
        <textarea id="ag-obs" placeholder="Observações sobre o serviço..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger btn-sm" id="btn-deletar-ag" onclick="deletarAgendamento()" style="margin-right:auto;display:none">🗑 Excluir</button>
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-ag')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="salvarAgendamento()">💾 Salvar</button>
    </div>
  </div>
</div>

<script>
const TODOS_VEICULOS = <?= json_encode($veiculos) ?>;
let offsetSemana = 0;
let statusAtual  = 'aguardando';
let modalSvcs    = [];
let svcIdx       = 0;

/* ── Semana ── */
function getSegunda(offset) {
  const d = new Date();
  const dow = d.getDay();
  const diff = (dow === 0 ? -6 : 1 - dow);
  d.setDate(d.getDate() + diff + offset * 7);
  d.setHours(0,0,0,0);
  return d;
}
function addDias(d, n) { const r = new Date(d); r.setDate(r.getDate()+n); return r; }
function toYMD(d) { return d.toISOString().split('T')[0]; }
function fmtDMY(s) { if(!s) return '—'; const p=s.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
function fmtMoeda(v) { return 'R$ '+Number(v).toLocaleString('pt-BR',{minimumFractionDigits:2}); }

function mudarSemana(d) { offsetSemana += d; carregarSemana(); }
function irHoje()       { offsetSemana = 0; carregarSemana(); }

const DIAS_PT  = ['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'];
const DIAS_FULL= ['Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'];

async function carregarSemana() {
  const seg = getSegunda(offsetSemana);
  const dom = addDias(seg, 6);
  const ini = toYMD(seg), fim = toYMD(dom);

  document.getElementById('semana-label').textContent =
    seg.getDate()+'/'+(seg.getMonth()+1)+' – '+dom.getDate()+'/'+(dom.getMonth()+1)+'/'+dom.getFullYear();

  const resp = await fetch(`api/agendamentos.php?inicio=${ini}&fim=${fim}`);
  const ags  = await resp.json();

  // Stats
  const total    = ags.length;
  const prontos  = ags.filter(a=>a.status==='pronto').length;
  const andamento= ags.filter(a=>a.status==='andamento').length;
  const fat      = ags.filter(a=>a.status==='pronto').reduce((s,a)=>s+Number(a.valor_total),0);
  document.getElementById('s-total').textContent    = total;
  document.getElementById('s-prontos').textContent  = prontos;
  document.getElementById('s-andamento').textContent= andamento;
  document.getElementById('s-fat').textContent      = fmtMoeda(fat);

  // Grid
  const grid = document.getElementById('semana-grid');
  grid.innerHTML = '';
  const hoje = toYMD(new Date());

  for (let i = 0; i < 7; i++) {
    const dia  = addDias(seg, i);
    const key  = toYMD(dia);
    const eHoje= key === hoje;
    const diaAgs = ags.filter(a => a.data_agenda === key);

    const col = document.createElement('div');
    col.className = 'dia-col' + (eHoje ? ' hoje' : '');
    col.innerHTML = `
      <div class="dia-head">
        <div class="dia-nome">${DIAS_PT[i]}</div>
        <div class="dia-num">${dia.getDate()}</div>
      </div>
      <div class="dia-body" id="dia-${key}">
        ${diaAgs.map(a => carroCardHTML(a)).join('')}
        <button class="add-carro-btn" onclick="abrirNovoAgendamento('${key}')">＋ Adicionar</button>
      </div>`;
    grid.appendChild(col);
  }
}

function carroCardHTML(a) {
  const total = Number(a.valor_total)||0;
  const svcsResume = (a.servicos||[]).map(s=>s.descricao).filter(Boolean).slice(0,2).join(', ');
  return `<div class="carro-card status-${a.status}" onclick="abrirEdicao(${a.id})">
    <div class="carro-status-dot dot-${a.status}"></div>
    <div class="carro-placa">${a.placa||'—'}</div>
    <div class="carro-modelo">${(a.modelo||'')+' '+(a.cor||'')}</div>
    ${a.hora?`<div class="carro-hora">🕐 ${a.hora}</div>`:''}
    ${svcsResume?`<div class="carro-modelo" style="color:var(--g600)">${svcsResume}</div>`:''}
    ${total>0?`<div class="carro-preco">${fmtMoeda(total)}</div>`:''}
  </div>`;
}

/* ── Modal agendamento ── */
function abrirNovoAgendamento(data='') {
  document.getElementById('ag-id').value = '';
  document.getElementById('ag-cliente').value = '';
  document.getElementById('ag-veiculo').innerHTML = '<option value="">Selecione o veículo...</option>';
  TODOS_VEICULOS.forEach(v => {
    const opt = document.createElement('option');
    opt.value = v.id; opt.dataset.cliente = v.cliente_id;
    opt.textContent = v.placa + ' — ' + v.modelo;
    document.getElementById('ag-veiculo').appendChild(opt);
  });
  document.getElementById('ag-data').value = data || toYMD(new Date());
  document.getElementById('ag-hora').value = '';
  document.getElementById('ag-obs').value  = '';
  document.getElementById('modal-ag-titulo').textContent = 'Novo agendamento';
  document.getElementById('btn-deletar-ag').style.display = 'none';
  modalSvcs = []; renderSvcs();
  selStatus('aguardando');
  abrirModal('modal-ag');
}

async function abrirEdicao(id) {
  const resp = await fetch(`api/agendamentos.php?id=${id}`);
  const ag   = await resp.json();
  if (ag.erro) { toast(ag.erro, 'err'); return; }

  document.getElementById('ag-id').value      = ag.id;
  document.getElementById('ag-cliente').value = ag.cliente_id || '';
  document.getElementById('ag-data').value    = ag.data_agenda;
  document.getElementById('ag-hora').value    = ag.hora || '';
  document.getElementById('ag-obs').value     = ag.observacao || '';
  document.getElementById('modal-ag-titulo').textContent = 'Editar agendamento';
  document.getElementById('btn-deletar-ag').style.display = 'inline-flex';

  // Veículos do cliente
  carregarVeiculosCliente(ag.veiculo_id);
  selStatus(ag.status || 'aguardando');

  modalSvcs = (ag.servicos || []).map(s=>({desc:s.descricao,valor:s.valor}));
  renderSvcs();
  abrirModal('modal-ag');
}

function carregarVeiculosCliente(selecionarId=null) {
  const cliId = parseInt(document.getElementById('ag-cliente').value);
  const sel   = document.getElementById('ag-veiculo');
  sel.innerHTML = '<option value="">Selecione o veículo...</option>';
  const filtrados = cliId ? TODOS_VEICULOS.filter(v=>parseInt(v.cliente_id)===cliId) : TODOS_VEICULOS;
  filtrados.forEach(v => {
    const opt = document.createElement('option');
    opt.value = v.id; opt.textContent = v.placa + ' — ' + v.modelo;
    if (selecionarId && parseInt(v.id) === parseInt(selecionarId)) opt.selected = true;
    sel.appendChild(opt);
  });
}

/* ── Serviços ── */
function renderSvcs() {
  const list = document.getElementById('svc-list');
  list.innerHTML = '';
  modalSvcs.forEach((s, i) => {
    const row = document.createElement('div');
    row.className = 'svc-row';
    row.innerHTML = `
      <input type="text"   id="svc-d-${i}" placeholder="Ex: Chapear porta dianteira" value="${esc(s.desc||'')}">
      <input type="number" id="svc-v-${i}" placeholder="0,00" value="${s.valor||''}" min="0" step="0.01" class="svc-price">
      <button class="svc-del" type="button" onclick="remSvc(${i})">✕</button>`;
    list.appendChild(row);
  });
}
function addSvc()   { modalSvcs.push({desc:'',valor:''}); renderSvcs(); }
function remSvc(i)  { modalSvcs.splice(i,1); renderSvcs(); }
function esc(s)     { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ── Status ── */
function selStatus(s) {
  statusAtual = s;
  document.querySelectorAll('.s-pill').forEach(p => {
    p.classList.toggle('sel', p.classList.contains(s));
  });
}

/* ── Salvar ── */
async function salvarAgendamento() {
  const id      = document.getElementById('ag-id').value;
  const cliente = document.getElementById('ag-cliente').value;
  const veiculo = document.getElementById('ag-veiculo').value;
  const data    = document.getElementById('ag-data').value;
  const hora    = document.getElementById('ag-hora').value;
  const obs     = document.getElementById('ag-obs').value;

  if (!data) { toast('Informe a data', 'err'); return; }

  // Coleta serviços
  const svcs = modalSvcs.map((_,i) => ({
    descricao: document.getElementById(`svc-d-${i}`)?.value || '',
    valor:     parseFloat(document.getElementById(`svc-v-${i}`)?.value) || 0,
  })).filter(s => s.descricao);

  const total = svcs.reduce((s,v)=>s+v.valor, 0);

  const payload = { id, cliente_id:cliente, veiculo_id:veiculo,
                    data_agenda:data, hora, status:statusAtual,
                    observacao:obs, servicos:svcs, valor_total:total };

  const action = id ? 'update' : 'create';
  const resp   = await fetch(`api/agendamentos.php?action=${action}`, {
    method: 'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const ret = await resp.json();
  if (ret.erro) { toast(ret.erro,'err'); return; }
  toast(id ? 'Agendamento atualizado!' : 'Agendamento criado!', 'ok');
  fecharModal('modal-ag');
  carregarSemana();
}

async function deletarAgendamento() {
  const id = document.getElementById('ag-id').value;
  if (!id || !confirm('Excluir este agendamento?')) return;
  const resp = await fetch('api/agendamentos.php?action=delete', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id})
  });
  const ret = await resp.json();
  if (ret.erro) { toast(ret.erro,'err'); return; }
  toast('Agendamento excluído','ok');
  fecharModal('modal-ag');
  carregarSemana();
}

// Init
carregarSemana();
</script>

<?php include 'includes/rodape.php'; ?>
