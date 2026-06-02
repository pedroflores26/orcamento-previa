<?php
$pagina    = 'clientes';
$titulo    = 'Clientes';
$subtitulo = 'Cadastro de clientes';
$topbar_acoes = '<button class="btn btn-primary btn-sm" onclick="abrirNovo()">＋ Novo cliente</button>';
include 'includes/topo.php';
?>

<div class="busca-bar">
  <input type="text" id="busca" placeholder="🔍  Buscar por nome, telefone ou CPF..." oninput="buscar()">
  <button class="btn btn-primary" onclick="abrirNovo()">＋ Novo cliente</button>
</div>

<div class="card">
  <div class="card-body" style="padding:0">
    <div class="table-wrap">
      <table class="tabela">
        <thead>
          <tr><th>#</th><th>Nome</th><th>CPF/CNPJ</th><th>Telefone</th><th>E-mail</th><th>Veículos</th><th>Ações</th></tr>
        </thead>
        <tbody id="tabela-clientes"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-cli">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">👤 <span id="modal-cli-titulo">Novo cliente</span></div>
      <button class="modal-close" onclick="fecharModal('modal-cli')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cli-id">
      <div class="g2">
        <div class="field"><label>Nome completo *</label><input type="text" id="cli-nome" placeholder="Nome do cliente"></div>
        <div class="field"><label>CPF / CNPJ</label><input type="text" id="cli-cpf" placeholder="000.000.000-00"></div>
      </div>
      <div class="g2">
        <div class="field"><label>Telefone / WhatsApp</label><input type="text" id="cli-tel" placeholder="(51) 99999-0000"></div>
        <div class="field"><label>E-mail</label><input type="text" id="cli-email" placeholder="cliente@email.com"></div>
      </div>
      <div class="field"><label>Endereço completo</label><input type="text" id="cli-end" placeholder="Rua, nº — Bairro, Cidade — UF"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger btn-sm" id="btn-del-cli" onclick="deletar()" style="margin-right:auto;display:none">🗑 Excluir</button>
      <button class="btn btn-ghost btn-sm" onclick="fecharModal('modal-cli')">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="salvar()">💾 Salvar</button>
    </div>
  </div>
</div>

<script>
async function carregar(q='') {
  const resp = await fetch('api/clientes.php?q='+encodeURIComponent(q));
  const list = await resp.json();
  const tbody = document.getElementById('tabela-clientes');
  if (!list.length) { tbody.innerHTML='<tr><td colspan="7" style="text-align:center;color:var(--g400);padding:24px">Nenhum cliente encontrado</td></tr>'; return; }
  tbody.innerHTML = list.map(c=>`
    <tr>
      <td><strong>${c.id}</strong></td>
      <td><strong>${esc(c.nome)}</strong></td>
      <td>${esc(c.cpf_cnpj||'—')}</td>
      <td>${esc(c.tel||'—')}</td>
      <td>${esc(c.email||'—')}</td>
      <td><a href="veiculos.php?cliente_id=${c.id}" style="color:var(--orange)">${c.total_veiculos} veículo(s)</a></td>
      <td><div class="td-acoes">
        <button class="btn btn-ghost btn-xs" onclick="editar(${c.id})">✏️ Editar</button>
        <a href="veiculos.php?cliente_id=${c.id}" class="btn btn-ghost btn-xs">🚗 Ver veículos</a>
      </div></td>
    </tr>`).join('');
}
function esc(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function buscar() { carregar(document.getElementById('busca').value); }

function abrirNovo() {
  ['cli-id','cli-nome','cli-cpf','cli-tel','cli-email','cli-end'].forEach(i=>document.getElementById(i).value='');
  document.getElementById('modal-cli-titulo').textContent='Novo cliente';
  document.getElementById('btn-del-cli').style.display='none';
  abrirModal('modal-cli');
}
async function editar(id) {
  const resp=await fetch('api/clientes.php?id='+id);
  const c=await resp.json();
  document.getElementById('cli-id').value=c.id;
  document.getElementById('cli-nome').value=c.nome||'';
  document.getElementById('cli-cpf').value=c.cpf_cnpj||'';
  document.getElementById('cli-tel').value=c.telefone||'';
  document.getElementById('cli-email').value=c.email||'';
  document.getElementById('cli-end').value=c.endereco||'';
  document.getElementById('modal-cli-titulo').textContent='Editar cliente';
  document.getElementById('btn-del-cli').style.display='inline-flex';
  abrirModal('modal-cli');
}
async function salvar() {
  const nome=document.getElementById('cli-nome').value.trim();
  if(!nome){toast('Informe o nome do cliente','err');return;}
  const payload={
    id:document.getElementById('cli-id').value,
    nome, cpf_cnpj:document.getElementById('cli-cpf').value,
    telefone:document.getElementById('cli-tel').value,
    email:document.getElementById('cli-email').value,
    endereco:document.getElementById('cli-end').value,
  };
  const action=payload.id?'update':'create';
  const resp=await fetch('api/clientes.php?action='+action,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const ret=await resp.json();
  if(ret.erro){toast(ret.erro,'err');return;}
  toast(payload.id?'Cliente atualizado!':'Cliente criado!','ok');
  fecharModal('modal-cli'); carregar();
}
async function deletar() {
  const id=document.getElementById('cli-id').value;
  if(!id||!confirm('Excluir este cliente?'))return;
  const resp=await fetch('api/clientes.php?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
  const ret=await resp.json();
  if(ret.erro){toast(ret.erro,'err');return;}
  toast('Cliente excluído','ok'); fecharModal('modal-cli'); carregar();
}
carregar();
</script>
<?php include 'includes/rodape.php'; ?>
