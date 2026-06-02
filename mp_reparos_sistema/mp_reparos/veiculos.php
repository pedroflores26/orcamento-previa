<?php
$pagina    = 'veiculos';
$titulo    = 'Veículos';
$subtitulo = 'Cadastro de veículos';
$topbar_acoes = '<button class="btn btn-primary btn-sm" onclick="abrirNovo()">＋ Novo veículo</button>';
require_once 'config/db.php';
$db = getDB();
$clientes = $db->query("SELECT id,nome FROM clientes ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$filtro_cli = (int)($_GET['cliente_id'] ?? 0);
include 'includes/topo.php';
?>

<div class="busca-bar">
  <input type="text" id="busca" placeholder="🔍  Buscar por placa, modelo ou cliente..." oninput="carregar()">
  <button class="btn btn-primary" onclick="abrirNovo()">＋ Novo veículo</button>
</div>

<div class="card">
  <div class="card-body" style="padding:0">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr><th>Placa</th><th>Modelo</th><th>Marca</th><th>Ano</th><th>Cor</th><th>KM</th><th>Cliente</th><th>Ações</th></tr>
        </thead>
        <tbody id="tabela-veiculos"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-vei">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">🚗 <span id="modal-vei-titulo">Novo veículo</span></div>
      <button class="modal-close" onclick="fecharModal('modal-vei')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="vei-id">
      <div class="g2">
        <div class="field"><label>Placa *</label><input type="text" id="vei-placa" placeholder="ABC-1D23" style="text-transform:uppercase"></div>
        <div class="field"><label>Cliente</label>
          <select id="vei-cliente">
            <option value="">Sem cliente vinculado</option>
            <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="g2">
        <div class="field"><label>Marca</label><input type="text" id="vei-marca" placeholder="Fiat, Chevrolet..."></div>
        <div class="field"><label>Modelo</label><input type="text" id="vei-modelo" placeholder="Strada, Onix..."></div>
      </div>
      <div class="g2">
        <div class="field"><label>Ano</label><input type="text" id="vei-ano" placeholder="2021/2022"></div>
        <div class="field"><label>Cor</label><input type="text" id="vei-cor" placeholder="Branco perolado"></div>
      </div>
      <div class="g2">
        <div class="field"><label>KM atual</label><input type="text" id="vei-km" placeholder="45.000 km"></div>
        <div class="field"><label>Seguradora</label><input type="text" id="vei-seg" placeholder="Porto Seguro..."></div>
      </div>
      <div class="field"><label>Chassi (opcional)</label><input type="text" id="vei-chassi" placeholder="9BWZZZ..."></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger btn-sm" id="btn-del-vei" onclick="deletar()" style="margin-right:auto;display:none">🗑 Excluir</button>
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-vei')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="salvar()">💾 Salvar</button>
    </div>
  </div>
</div>

<script>
const FILTRO_CLI = <?= $filtro_cli ?>;

async function carregar() {
  const q = document.getElementById('busca').value;
  const url = 'api/veiculos.php?q='+encodeURIComponent(q)+(FILTRO_CLI?'&cliente_id='+FILTRO_CLI:'');
  const resp = await fetch(url);
  const list = await resp.json();
  const tbody = document.getElementById('tabela-veiculos');
  if(!list.length){tbody.innerHTML='<tr><td colspan="8" style="text-align:center;color:var(--g400);padding:24px">Nenhum veículo encontrado</td></tr>';return;}
  tbody.innerHTML = list.map(v=>`
    <tr>
      <td><strong>${esc(v.placa)}</strong></td>
      <td>${esc(v.modelo||'—')}</td>
      <td>${esc(v.marca||'—')}</td>
      <td>${esc(v.ano||'—')}</td>
      <td>${esc(v.cor||'—')}</td>
      <td>${esc(v.km||'—')}</td>
      <td>${esc(v.cliente_nome||'—')}</td>
      <td><button class="btn btn-ghost btn-xs" onclick="editar(${v.id})">✏️ Editar</button></td>
    </tr>`).join('');
}
function esc(s){return(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

function abrirNovo(){
  ['vei-id','vei-placa','vei-marca','vei-modelo','vei-ano','vei-cor','vei-km','vei-seg','vei-chassi'].forEach(i=>document.getElementById(i).value='');
  document.getElementById('vei-cliente').value= FILTRO_CLI || '';
  document.getElementById('modal-vei-titulo').textContent='Novo veículo';
  document.getElementById('btn-del-vei').style.display='none';
  abrirModal('modal-vei');
}
async function editar(id){
  const resp=await fetch('api/veiculos.php?id='+id);
  const v=await resp.json();
  document.getElementById('vei-id').value=v.id;
  document.getElementById('vei-placa').value=v.placa||'';
  document.getElementById('vei-cliente').value=v.cliente_id||'';
  document.getElementById('vei-marca').value=v.marca||'';
  document.getElementById('vei-modelo').value=v.modelo||'';
  document.getElementById('vei-ano').value=v.ano||'';
  document.getElementById('vei-cor').value=v.cor||'';
  document.getElementById('vei-km').value=v.km||'';
  document.getElementById('vei-seg').value=v.seguradora||'';
  document.getElementById('vei-chassi').value=v.chassi||'';
  document.getElementById('modal-vei-titulo').textContent='Editar veículo';
  document.getElementById('btn-del-vei').style.display='inline-flex';
  abrirModal('modal-vei');
}
async function salvar(){
  const placa=document.getElementById('vei-placa').value.trim().toUpperCase();
  if(!placa){toast('Informe a placa','err');return;}
  const payload={
    id:document.getElementById('vei-id').value,
    placa, cliente_id:document.getElementById('vei-cliente').value,
    marca:document.getElementById('vei-marca').value,
    modelo:document.getElementById('vei-modelo').value,
    ano:document.getElementById('vei-ano').value,
    cor:document.getElementById('vei-cor').value,
    km:document.getElementById('vei-km').value,
    seguradora:document.getElementById('vei-seg').value,
    chassi:document.getElementById('vei-chassi').value,
  };
  const action=payload.id?'update':'create';
  const resp=await fetch('api/veiculos.php?action='+action,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const ret=await resp.json();
  if(ret.erro){toast(ret.erro,'err');return;}
  toast(payload.id?'Veículo atualizado!':'Veículo criado!','ok');
  fecharModal('modal-vei'); carregar();
}
async function deletar(){
  const id=document.getElementById('vei-id').value;
  if(!id||!confirm('Excluir este veículo?'))return;
  const resp=await fetch('api/veiculos.php?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
  const ret=await resp.json();
  if(ret.erro){toast(ret.erro,'err');return;}
  toast('Veículo excluído','ok'); fecharModal('modal-vei'); carregar();
}
carregar();
</script>
<?php include 'includes/rodape.php'; ?>
