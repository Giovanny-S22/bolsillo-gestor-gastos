<?php
$titulo = 'Presupuestos';
$activo = 'presupuestos';
$css = 'presupuesto.css'; 

require ROOT_PATH . '/app/views/partials/head.php';
?>

<body>

<div class="app">

    <?php require ROOT_PATH . '/app/views/partials/sidebar.php'; ?>

    <main class="principal gastos">

        <header class="saludo">
            <div>
                <h1>Gestión de Presupuestos</h1>
                <p class="suave">Crea y administra tus límites de gastos por fechas.</p>
            </div>
        </header>

        <?php if ($mensaje = flash('exito')): ?>
            <div class="mensaje mensaje-exito">✓ <?= e($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($mensaje = flash('error')): ?>
            <div class="mensaje mensaje-error">! <?= e($mensaje) ?></div>
        <?php endif; ?>

        <!-- Formulario para crear presupuesto -->
        <section class="panel gasto-form">
            <div class="gasto-titulo">
                <div class="gasto-icono">+</div>
                <div>
                    <h2>Nuevo Presupuesto</h2>
                    <p>Establece un nuevo límite en un periodo de tiempo.</p>
                </div>
            </div>

            <form method="post" action="<?= e(url('presupuesto')) ?>">
                <?= csrf_field() ?>

                <div class="gasto-grid">
                    <div class="campo gasto-campo">
                        <label for="nombre">Nombre del presupuesto</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre del presupuesto" required maxlength="100">
                    </div>

                    <div class="campo gasto-campo">
                        <label for="limite">Límite</label>
                        <div class="monto-input">
                            <span>$</span>
                            <input type="number" id="limite" name="limite" min="0.01" step="0.01" placeholder="150000" required>
                        </div>
                    </div>

                    <div class="campo gasto-campo">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e(date('Y-m-d')) ?>" required>
                    </div>

                    <div class="campo gasto-campo">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>

                    <div class="campo campo-completo gasto-campo">
                        <label>
                            <input type="checkbox" name="activo" value="1"> Activar este presupuesto
                        </label>
                    </div>
                </div>

                <div class="gasto-boton">
                    <button class="boton" type="submit">+ Crear Presupuesto</button>
                </div>
            </form>
        </section>

        <!-- historial de Presupuestos -->
        <section class="panel historial">
            <div class="historial-header">
                <div class="historial-titulo">
                    <div class="historial-icono">▤</div>
                    <div>
                        <h2>Historial de presupuestos</h2>
                        <p>Consulta y cambia tu presupuesto activo.</p>
                    </div>
                </div>
                <span class="contador">
                    <?= count($presupuestos) ?> <?= count($presupuestos) === 1 ? 'presupuesto' : 'presupuestos' ?>
                </span>
            </div>

            <?php if (empty($presupuestos)): ?>
                <div class="estado-vacio">
                    <div class="estado-icono">$</div>
                    <h3>No tienes presupuestos creados</h3>
                    <p>Crea tu primer presupuesto arriba para empezar.</p>
                </div>
            <?php else: ?>
                <div class="lista-gastos">
                    <?php foreach ($presupuestos as $presupuesto): ?>
                        <article class="gasto-item" style="border-left: 4px solid <?= $presupuesto['activo'] ? '#2ecc71' : '#ccc' ?>;">
                            <div class="gasto-fecha">
                                <strong><?= e(date('d', strtotime($presupuesto['fecha_inicio']))) ?></strong>
                                <small><?= e(strtoupper(date('M', strtotime($presupuesto['fecha_inicio'])))) ?></small>
                            </div>

                            <div class="gasto-info">
                                <span class="gasto-tipo">
                                    <?= e($presupuesto['nombre']) ?> 
                                    <?= $presupuesto['activo'] ? '<span style="color: #2ecc71; font-weight: bold;">(ACTIVO)</span>' : '' ?>
                                </span>
                                <p><small>Del <?= e($presupuesto['fecha_inicio']) ?> al <?= e($presupuesto['fecha_fin']) ?></small></p>
                            </div>

                            <div class="gasto-final">
                                <strong><?= e(money($presupuesto['limite'])) ?></strong>
                                <div>
                                    <?php if (!$presupuesto['activo']): ?>
                                       <form method="post" action="<?= e(url('presupuesto/activar')) ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $presupuesto['id'] ?>">
                                            <button type="submit" class="boton-secundario" style="background:none; border:none; cursor:pointer; color:var(--primario);">Activar</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= e(url('presupuesto/editar?id=' . (int) $presupuesto['id'])) ?>">Editar</a>
                                    <form method="post" action="<?= e(url('presupuesto/eliminar')) ?>" onsubmit="return confirm('¿Seguro que deseas eliminar este presupuesto?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $presupuesto['id'] ?>">
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>
</body>