<?php
  $title = $title ?? 'Gestor de Portátiles';
  if (!isset($__view)) { $__view = 'handovers/index'; } // fallback seguro
?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8">
<title><?= htmlspecialchars($title) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
  <div class="right">
  <?php if (!empty($_SESSION['user'])): ?>
    <span class="me-2">👤 <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
    <a href="<?= url('auth/logout') ?>">Salir</a>
  <?php endif; ?>
</div>
<header class="topbar">
  <div class="brand"><a href="<?= url('handovers/index') ?>">💻 Gestor de Portátiles</a></div>
  <nav class="menu">
      <a href="<?= url('handovers/index') ?>">Inicio</a>
      <a href="<?= url('people/index') ?>">Personas</a>
      <a href="<?= url('laptops/index') ?>">Portátiles</a>
      <a href="<?= url('courses/index') ?>">Cursos</a>
      <a href="<?= url('locations/index') ?>">Ubicaciones</a>

      <a href="<?= url('handovers/entrega') ?>">Entrega</a>
      <a href="<?= url('handovers/devolucion') ?>">Devolución</a>
  </nav>
</header>
<main class="container">
  <?php include BASE_PATH . "/app/Views/" . $__view . ".php"; ?>
</main>
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body></html>
