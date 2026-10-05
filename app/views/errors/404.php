<?php $titulo = 'Página no encontrada'; require ROOT_PATH . '/app/views/partials/head.php'; ?>
<body>
  <main class="auth-panel" style="min-height:100vh">
    <div class="auth-form">
      <h1 class="auth-titulo">No encontramos esa página</h1>
      <p class="suave">La dirección no existe o todavía no está disponible.</p>
      <a class="boton" href="<?= e(url('dashboard')) ?>">Ir al dashboard</a>
    </div>
  </main>
<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>
