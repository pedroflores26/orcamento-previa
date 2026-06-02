  </div><!-- /content -->
</div><!-- /main -->

<div class="toast" id="toast"></div>

<script>
function toast(msg, tipo='ok') {
  const t = document.getElementById('toast');
  t.textContent = (tipo==='ok'?'✅ ':tipo==='err'?'❌ ':'ℹ️ ') + msg;
  t.className = 'toast show ' + tipo;
  setTimeout(() => t.className = 'toast', 3000);
}
function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
function fecharModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if(e.target===el) el.classList.remove('open'); });
});
</script>
</body>
</html>
