<?php ?>
<aside class="lateral">
  <div class="marca">
    <span class="icono" data-icon="wallet"></span>
    <span>Bolsillo</span>
  </div>

  <nav class="nav" aria-label="Principal">
    <a href="<?= e(url('dashboard')) ?>" <?= ($activo ?? '') === 'dashboard' ? 'aria-current="page"' : '' ?>>
      <span class="icono" data-icon="dashboard"></span><span>Dashboard</span>
    </a>
    <a href="<?= e(url('gastos')) ?>" <?= ($activo ?? '') === 'gastos' ? 'aria-current="page"' : '' ?>>
      <span class="icono" data-icon="receipt"></span><span>Gastos</span>
    </a>
    <a href="<?= e(url('presupuesto')) ?>" <?= ($activo ?? '') === 'presupuesto' ? 'aria-current="page"' : '' ?>>
      <span class="icono" data-icon="target"></span><span>Gestionar presupuesto</span>
    </a>
  </nav>

  <form class="lateral-pie" method="post" action="<?= e(url('logout')) ?>">
    <?= csrf_field() ?>
    <button class="btn-salir" type="submit">
      <span class="icono" data-icon="logout"></span><span>Cerrar sesión</span>
    </button>
  </form>
</aside>
