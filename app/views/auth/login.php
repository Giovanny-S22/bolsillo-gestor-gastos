<?php $titulo = 'Iniciar sesión'; require ROOT_PATH . '/app/views/partials/head.php'; ?>
<body>
<main class="auth">
  <section class="auth-lado">
    <div class="marca"><span class="icono" data-icon="wallet"></span><span>Bolsillo</span></div>
    <div>
      <h2>Sabe a dónde se va tu dinero.</h2>
      <p>Anota lo que gastas cada día y mira cómo vas frente a tu presupuesto.</p>
    </div>
  </section>

  <section class="auth-panel">
    <form class="auth-form" method="post" action="<?= e(url('login')) ?>">
      <?= csrf_field() ?>
      <div>
        <h1 class="auth-titulo">Iniciar sesión</h1>
        <p class="suave">Entra para ver tu resumen.</p>
      </div>

      <?php if ($error = flash('error')): ?>
        <p class="aviso" role="alert"><?= e($error) ?></p>
      <?php endif; ?>

      <div class="campo">
        <label for="email">Correo</label>
        <div class="entrada">
          <span class="icono" data-icon="mail"></span>
          <input id="email" name="email" type="email" autocomplete="email" required value="<?= old('email') ?>">
        </div>
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <div class="entrada">
          <span class="icono" data-icon="lock"></span>
          <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
      </div>

      <button class="boton" type="submit">Iniciar sesión</button>
      <p class="suave centro">¿Primera vez aquí? <a href="<?= e(url('registro')) ?>">Crea tu cuenta</a></p>
    </form>
  </section>
</main>
<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>
