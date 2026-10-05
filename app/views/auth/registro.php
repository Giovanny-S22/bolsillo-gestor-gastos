<?php $titulo = 'Crear cuenta'; require ROOT_PATH . '/app/views/partials/head.php'; ?>
<body>
<main class="auth">
  <section class="auth-lado">
    <div class="marca"><span class="icono" data-icon="wallet"></span><span>Bolsillo</span></div>
    <div>
      <h2>Empieza con tu primer gasto.</h2>
      <p>Crea tu cuenta, fija cuánto quieres gastar y deja que el dashboard haga las cuentas.</p>
    </div>
  </section>

  <section class="auth-panel">
    <form class="auth-form" method="post" action="<?= e(url('registro')) ?>">
      <?= csrf_field() ?>
      <div>
        <h1 class="auth-titulo">Crear cuenta</h1>
        <p class="suave">Solo toma un minuto.</p>
      </div>

      <?php if ($errores = flash('errores')): ?>
        <div class="aviso" role="alert">
          <ul>
            <?php foreach ($errores as $mensaje): ?>
              <li><?= e($mensaje) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="campo">
        <label for="nombre">Nombre</label>
        <div class="entrada">
          <span class="icono" data-icon="user"></span>
          <input id="nombre" name="nombre" type="text" autocomplete="name" required maxlength="80" value="<?= old('nombre') ?>">
        </div>
      </div>

      <div class="campo">
        <label for="email">Correo</label>
        <div class="entrada">
          <span class="icono" data-icon="mail"></span>
          <input id="email" name="email" type="email" autocomplete="email" required maxlength="120" value="<?= old('email') ?>">
        </div>
      </div>

      <div class="campo">
        <label for="password">Contraseña <span class="suave">(mínimo 8 caracteres)</span></label>
        <div class="entrada">
          <span class="icono" data-icon="lock"></span>
          <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
        </div>
      </div>

      <div class="campo">
        <label for="confirmar">Repite la contraseña</label>
        <div class="entrada">
          <span class="icono" data-icon="lock"></span>
          <input id="confirmar" name="confirmar" type="password" autocomplete="new-password" required minlength="8">
        </div>
      </div>

      <button class="boton" type="submit">Crear cuenta</button>
      <p class="suave centro">¿Ya tienes cuenta? <a href="<?= e(url('login')) ?>">Inicia sesión</a></p>
    </form>
  </section>
</main>
<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>
