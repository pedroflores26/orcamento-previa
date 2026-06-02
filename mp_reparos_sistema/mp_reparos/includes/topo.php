<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulo ?? 'MP Reparos Automotivos' ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ══ SIDEBAR ══════════════════════════════════════════ -->
<nav class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🔧</div>
    <div>
      <div class="brand-name">MP Reparos</div>
      <div class="brand-sub">Automotivos</div>
    </div>
  </div>

  <div class="nav-section">Menu</div>

  <a href="index.php" class="nav-item <?= ($pagina==='dashboard')?'active':'' ?>">
    <span class="nav-icon">📊</span><span>Dashboard</span>
  </a>
<a href="gestao.php" class="nav-item <?= ($pagina==='gestao')?'active':'' ?>">
    <span class="nav-icon">📅</span><span>Gestão da Oficina</span>
</a>
  <a href="clientes.php" class="nav-item <?= ($pagina==='clientes')?'active':'' ?>">
    <span class="nav-icon">👤</span><span>Clientes</span>
  </a>
  <a href="veiculos.php" class="nav-item <?= ($pagina==='veiculos')?'active':'' ?>">
    <span class="nav-icon">🚗</span><span>Veículos</span>
  </a>
  <a href="orcamentos.php" class="nav-item <?= ($pagina==='orcamentos')?'active':'' ?>">
    <span class="nav-icon">📄</span><span>Orçamentos</span>
  </a>
  
  <a href="ordens.php" class="nav-item <?= ($pagina==='ordens')?'active':'' ?>">
  <span class="nav-icon">🔧</span><span>Ordens de Serviço</span>
</a>

  <div class="sidebar-footer">MP Reparos Automotivos</div>
</nav>

<!-- ══ MAIN ══════════════════════════════════════════ -->
<div class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title"><?= $titulo ?? 'Sistema' ?></div>
      <div class="topbar-sub"><?= $subtitulo ?? 'MP Reparos Automotivos' ?></div>
    </div>
    <div class="topbar-right">
      <?= $topbar_acoes ?? '' ?>
    </div>
  </div>
  <div class="content">
