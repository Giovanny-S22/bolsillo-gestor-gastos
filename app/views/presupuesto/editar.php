<?php
$titulo = 'Editar Presupuesto';
$activo = 'presupuesto';
$css = 'presupuesto.css';

require ROOT_PATH . '/app/views/partials/head.php';
?>

<body>
<div class="app">
    <?php require ROOT_PATH . '/app/views/partials/sidebar.php'; ?>

    <main class="principal gastos">
        <header class="saludo">
            <div>
                <h1>Editar presupuesto</h1>
                <p class="suave">Modifica los datos del presupuesto seleccionado.</p>
            </div>
        </header>

        <?php if ($mensaje = flash('error')): ?>
            <div class="mensaje mensaje-error">! <?= e($mensaje) ?></div>
        <?php endif; ?>

        <section class="panel gasto-form">
            <div class="gasto-titulo">
                <div class="gasto-icono">✎</div>
                <div>
                    <h2>Información del presupuesto</h2>
                    <p>Actualiza los límites o las fechas necesarias.</p>
                </div>
            </div>

            <form method="post" action="<?= e(url('presupuesto/actualizar')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $presupuesto['id'] ?>">

                <div class="gasto-grid">
                    <div class="campo gasto-campo">
                        <label for="nombre">Nombre del presupuesto</label>
                        <input type="text" id="nombre" name="nombre" value="<?= e($presupuesto['nombre']) ?>" required maxlength="100">
                    </div>

                    <div class="campo gasto-campo">
                        <label for="limite">Límite</label>
                        <div class="monto-input">
                            <span>$</span>
                            <input type="number" id="limite" name="limite" min="0.01" step="0.01" value="<?= e($presupuesto['limite']) ?>" required>
                        </div>
                    </div>

                    <div class="campo gasto-campo">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e($presupuesto['fecha_inicio']) ?>" required>
                    </div>

                    <div class="campo gasto-campo">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= e($presupuesto['fecha_fin']) ?>" required>
                    </div>

                  <div class="campo campo-completo gasto-campo">
                        <label>
                            <input type="checkbox" name="activo" value="1"> Activar este presupuesto
                        </label>
                    </div>
                </div>

                <div class="gasto-boton editar-botones">
                    <a class="boton boton-secundario" href="<?= e(url('presupuesto')) ?>">Cancelar</a>
                    <button class="boton" type="submit">✓ Guardar cambios</button>
                </div>
            </form>
        </section>
    </main>
</div>
<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>
</body>
</html>