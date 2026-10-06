<?php
$titulo  = 'Dashboard';
$activo  = 'dashboard';
$scripts = ['dashboard.js'];
require ROOT_PATH . '/app/views/partials/head.php';

?>
<body>
<div class="app">
  <?php require ROOT_PATH . '/app/views/partials/sidebar.php'; ?>

  <main class="principal">
    <header class="saludo">
      <h1>Hola, <?= e($nombre) ?></h1>
      <p class="suave"><?= e(fecha_larga()) ?></p>
    </header>

    <?php if ($presupuesto): $p = $presupuesto; ?>

      <section class="presupuesto" aria-labelledby="t-presupuesto">
        <div class="presupuesto-top">
          <div>
            <h2 id="t-presupuesto"><?= e($p['nombre']) ?></h2>
            <p class="restante"><?= e(money(abs($p['restante']))) ?></p>
            <p class="restante-nota">
              <?php if ($p['restante'] >= 0): ?>
                disponibles de <?= e(money($p['limite_num'])) ?>
              <?php else: ?>
                por encima del límite de <?= e(money($p['limite_num'])) ?>
              <?php endif; ?>
            </p>
          </div>
          <p class="plazo">
            <span class="icono" data-icon="calendar"></span>
            <?= e(fecha_corta($p['fecha_inicio'])) ?> a <?= e(fecha_corta($p['fecha_fin'])) ?>
          </p>
        </div>

        <div class="barra" role="progressbar" aria-label="Presupuesto usado"
             aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) min(100, $p['porcentaje']) ?>">
          <div class="barra-relleno <?= e($p['estado']) ?>" style="--pct: <?= (int) min(100, $p['porcentaje']) ?>%"></div>
        </div>

        <ul class="presupuesto-datos">
          <li>Llevas gastado <strong><?= e(money($p['gastado_num'])) ?></strong> (<?= (int) $p['porcentaje'] ?>%)</li>
          <?php if ($p['plazo'] === 'vencido'): ?>
            <li>Este presupuesto terminó el <strong><?= e(fecha_corta($p['fecha_fin'])) ?></strong></li>
          <?php elseif ($p['plazo'] === 'proximo'): ?>
            <li>Empieza el <strong><?= e(fecha_corta($p['fecha_inicio'])) ?></strong></li>
          <?php else: ?>
            <li>Te quedan <strong><?= (int) $p['dias_restantes'] ?></strong> <?= $p['dias_restantes'] === 1 ? 'día' : 'días' ?></li>
          <?php endif; ?>
          <?php if ($p['por_dia'] > 0): ?>
            <li>Puedes gastar <strong><?= e(money($p['por_dia'])) ?></strong> al día</li>
          <?php endif; ?>
        </ul>
      </section>

      <section class="resumen" aria-label="Resumen del presupuesto">
        <article class="dato">
          <p class="dato-nombre"><span class="icono" data-icon="receipt"></span>Gastos registrados</p>
          <p class="valor"><?= (int) $p['num_gastos'] ?></p>
        </article>
        <article class="dato">
          <p class="dato-nombre"><span class="icono" data-icon="sun"></span>Gastado hoy</p>
          <p class="valor"><?= e(money($totalHoy)) ?></p>
        </article>
        <article class="dato">
          <p class="dato-nombre"><span class="icono" data-icon="trending"></span>Promedio por día</p>
          <p class="valor"><?= e(money($p['promedio'])) ?></p>
        </article>
      </section>

      <div class="dos">
        <section class="panel" aria-labelledby="t-dias">
          <h2 id="t-dias">Últimos 7 días</h2>
          <?php if ($hayDias): ?>
            <div class="grafica" id="grafica-dias" data-dias="<?= e(json_encode($dias)) ?>"></div>
          <?php else: ?>
            <p class="vacio-texto">No hay gastos de este presupuesto en los últimos 7 días.</p>
          <?php endif; ?>
        </section>

        <section class="panel" aria-labelledby="t-tipos">
          <h2 id="t-tipos">En qué gastas</h2>
          <?php if ($porTipo): ?>
            <ul class="tipos">
              <?php foreach ($porTipo as $tipo => $total):
                $parte = $p['gastado_num'] > 0 ? (int) round($total / $p['gastado_num'] * 100) : 0; ?>
                <li class="tipo-fila">
                  <div class="tipo-cab">
                    <span><?= e($tipo) ?></span>
                    <span><?= e(money($total)) ?> <span class="suave">· <?= $parte ?>%</span></span>
                  </div>
                  <div class="tipo-barra"><span style="--pct: <?= $parte ?>%"></span></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="vacio-texto">Cuando registres gastos verás aquí en qué se va tu dinero.</p>
          <?php endif; ?>
        </section>
      </div>

      <section class="panel" aria-labelledby="t-ultimos">
        <h2 id="t-ultimos">Últimos gastos</h2>
        <?php if ($ultimos): ?>
          <ul class="lista-gastos">
            <?php foreach ($ultimos as $g): ?>
              <li class="gasto">
                <span class="gasto-fecha"><?= e(fecha_corta($g['fecha'])) ?></span>
                <div>
                  <p class="gasto-tipo"><?= e($g['tipo_gasto']) ?></p>
                  <?php if (!empty($g['descripcion'])): ?>
                    <p class="suave gasto-desc"><?= e($g['descripcion']) ?></p>
                  <?php endif; ?>
                </div>
                <p class="gasto-monto"><?= e(money($g['monto'])) ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="vacio-texto">Todavía no hay gastos en este presupuesto. <a href="<?= e(url('gastos')) ?>">Registra el primero</a>.</p>
        <?php endif; ?>
      </section>

    <?php else: ?>

      <section class="presupuesto vacio">
        <span class="icono" data-icon="target"></span>
        <div>
          <h2>No tienes un presupuesto activo</h2>
          <p class="suave">Crea un presupuesto o activa uno que ya tengas para ver aquí tu resumen.</p>
        </div>
        <a class="boton boton-claro" href="<?= e(url('presupuesto')) ?>">Gestionar presupuesto</a>
      </section>

    <?php endif; ?>
  </main>
</div>
<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>