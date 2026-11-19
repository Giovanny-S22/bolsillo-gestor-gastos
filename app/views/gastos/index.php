<?php
$titulo = 'Gastos';
$activo = 'gastos';
$css = 'gastos.css';


require ROOT_PATH . '/app/views/partials/head.php';
?>

<body>

<div class="app">

    <?php require ROOT_PATH . '/app/views/partials/sidebar.php'; ?>

    <main class="principal gastos">

        <header class="saludo">
            <div>
                <h1>Mis gastos</h1>
                <p class="suave">Registra y controla tus gastos de forma sencilla.</p>
            </div>
        </header>


        <?php if ($mensaje = flash('exito')): ?>
            <div class="mensaje mensaje-exito">✓ <?= e($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($mensaje = flash('error')): ?>
            <div class="mensaje mensaje-error">! <?= e($mensaje) ?></div>
        <?php endif; ?>

        <section class="panel gasto-form">

            <div class="gasto-titulo">
                <div class="gasto-icono">+</div>
                <div>
                    <h2>Registrar gasto</h2>
                    <p>Añade un nuevo movimiento a tu presupuesto.</p>
                </div>
            </div>

            <?php if (!empty($alertaPresupuesto)): ?>
                <div class="mensaje mensaje-error">
                    <h3>¡Atención! Límite del presupuesto alcanzado</h3>
                    <p>
                        El gasto que intentas registrar hará que el presupuesto
                        <strong><?= e($alertaPresupuesto['nombre_presupuesto']) ?></strong>
                        alcance o supere su límite.
                    </p>
                    <p>
                        <strong>Límite:</strong>
                        <?= e(money($alertaPresupuesto['limite'])) ?>
                    </p>
                    <p>
                        <strong>Total gastado actualmente:</strong>
                        <?= e(money($alertaPresupuesto['total_gastado'])) ?>
                    </p>
                    <p>
                        <strong>Nuevo gasto:</strong>
                        <?= e(money($alertaPresupuesto['monto'])) ?>
                    </p>
                    <p>
                        <strong>Total después del registro:</strong>
                        <?= e(money($alertaPresupuesto['total_despues'])) ?>
                    </p>
                    <?php if ($alertaPresupuesto['excedente'] > 0): ?>
                        <p>
                            <strong>Excedente:</strong>
                            <?= e(money($alertaPresupuesto['excedente'])) ?>
                        </p>
                    <?php else: ?>
                        <p>Este gasto hará que el presupuesto llegue exactamente a su límite.</p>
                    <?php endif; ?>
                    <p>¿Deseas registrar el gasto de todas formas?</p>
                    <div class="gasto-boton">
                        <form method="post" action="<?= e(url('gastos')) ?>">
                            <?= csrf_field() ?>
                            <button
                                class="boton"
                                type="submit"
                                name="confirmar_exceso"
                                value="1"
                            >
                                Registrar de todas formas
                            </button>
                        </form>
                        <form method="post" action="<?= e(url('gastos')) ?>">
                            <?= csrf_field() ?>
                            <button
                                class="boton"
                                type="submit"
                                name="cancelar_exceso"
                                value="1"
                            >
                                Cancelar
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif (empty($presupuestos)): ?>
                <div class="estado-vacio">
                    <div class="estado-icono">$</div>
                    <h3>No tienes presupuestos disponibles</h3>
                    <p>Primero necesitas crear un presupuesto para registrar gastos.</p>
                </div>
            <?php else: ?>
                <form method="post" action="<?= e(url('gastos')) ?>">
                    <?= csrf_field() ?>
                    <div class="gasto-grid">
                        <div class="campo gasto-campo">
                            <label for="fecha">
                                <span class="icono" data-icon="calendar"></span>
                                Fecha
                            </label>
                            <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                value="<?= e(date('Y-m-d')) ?>"
                                max="<?= e(date('Y-m-d')) ?>"
                                required
                            >
                        </div>
                        <div class="campo gasto-campo">
                            <label for="monto">
                                <span>$</span>
                                Monto
                            </label>
                            <div class="monto-input">
                                <span>$</span>
                                <input
                                    type="number"
                                    id="monto"
                                    name="monto"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="25000"
                                    required
                                >
                            </div>
                        </div>
                        <div class="campo gasto-campo">
                            <label for="id_categoria">
                                <span>◇</span>
                                Categoría
                            </label>
                            <select id="id_categoria" name="id_categoria" required>
                                <option value="">Selecciona una categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int) $categoria['id'] ?>">
                                        <?= e($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="campo gasto-campo">
                            <label for="id_presupuesto">
                                <span>◎</span>
                                Presupuesto
                            </label>
                            <select id="id_presupuesto" name="id_presupuesto" required>
                                <option value="">Selecciona un presupuesto</option>
                                <?php foreach ($presupuestos as $presupuesto): ?>
                                    <option
                                        value="<?= (int) $presupuesto['id'] ?>"
                                        <?= !empty($presupuestoActivo) &&
                                            (int) $presupuesto['id'] === (int) $presupuestoActivo['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($presupuesto['nombre']) ?>
                                        <?= (int) $presupuesto['activo'] === 1 ? '(Activo)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="campo campo-completo gasto-campo">
                            <label for="descripcion">
                                <span>▤</span>
                                Descripción
                                <em>Opcional</em>
                            </label>
                            <input
                                type="text"
                                id="descripcion"
                                name="descripcion"
                                maxlength="150"
                                placeholder="Ej. Almuerzo con compañeros"
                            >
                        </div>
                    </div>
                    <div class="gasto-boton">
                        <button class="boton" type="submit">
                            + Registrar gasto
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
        <section class="panel historial">
            <div class="historial-header">

                <div class="historial-titulo">

                    <div class="historial-icono">
                        ▤
                    </div>

                    <div>
                        <h2>Historial de gastos</h2>
                        <p>Consulta tus movimientos registrados.</p>
                    </div>

                </div>

                <span class="contador">
                    <?= count($gastos) ?>
                    <?= count($gastos) === 1 ? 'gasto' : 'gastos' ?>
                </span>

            </div>


            <?php if (empty($gastos)): ?>

                <div class="estado-vacio">
                    <div class="estado-icono">$</div>
                    <h3>Aún no tienes gastos registrados</h3>
                    <p>Registra tu primer gasto para comenzar a llevar el control.</p>
                </div>

            <?php else: ?>

                <div class="lista-gastos">

                    <?php foreach ($gastos as $gasto): ?>

                        <article class="gasto-item">

                            <div class="gasto-fecha">
                                <strong>
                                    <?= e(date('d', strtotime($gasto['fecha']))) ?>
                                </strong>

                                <small>
                                    <?= e(strtoupper(date('M', strtotime($gasto['fecha'])))) ?>
                                </small>
                            </div>


                            <div class="gasto-info">

                                <span class="gasto-tipo">
                                    <?= e($gasto['categoria_nombre']) ?>
                                </span>

                                <?php if (!empty($gasto['descripcion'])): ?>
                                    <p><?= e($gasto['descripcion']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($gasto['presupuesto_nombre'])): ?>
                                    <small>
                                        ◎ <?= e($gasto['presupuesto_nombre']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="gasto-final">
                                <strong>
                                    <?= e(money($gasto['monto'])) ?>
                                </strong>
                                <div>
                                    <a
                                        href="<?= e(url('gastos/editar?id=' . (int) $gasto['id'])) ?>"
                                    >
                                        Editar
                                    </a>
                                    <form
                                        method="post"
                                        action="<?= e(url('gastos/eliminar')) ?>"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este gasto?');"
                                    >
                                        <?= csrf_field() ?>
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $gasto['id'] ?>"
                                        >
                                        <button type="submit">
                                            Eliminar
                                        </button>
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