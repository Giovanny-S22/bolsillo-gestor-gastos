<?php
$titulo = 'Editar gasto';
$activo = 'gastos';
$css = 'gastos.css';

$categorias = [
    'Comida',
    'Transporte',
    'Mercado',
    'Servicios',
    'Ocio',
    'Salud',
    'Educación',
    'Otros'
];

require ROOT_PATH . '/app/views/partials/head.php';
?>

<body>

<div class="app">

    <?php require ROOT_PATH . '/app/views/partials/sidebar.php'; ?>

    <main class="principal gastos">

        <header class="saludo">
            <div>
                <h1>Editar gasto</h1>
                <p class="suave">Modifica la información del gasto seleccionado.</p>
            </div>
        </header>


        <section class="panel gasto-form">

            <div class="gasto-titulo">

                <div class="gasto-icono">
                    ✎
                </div>

                <div>
                    <h2>Información del gasto</h2>
                    <p>Actualiza los datos que necesites.</p>
                </div>

            </div>


            <form
                method="post"
                action="<?= e(url('gastos/actualizar')) ?>"
            >

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $gasto['id'] ?>"
                >


                <div class="gasto-grid">

                    <div class="campo">
                        <label for="fecha">
                            <span class="icono" data-icon="calendar"></span>
                            Fecha
                        </label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="<?= e($gasto['fecha']) ?>"
                            max="<?= e(date('Y-m-d')) ?>"
                            required
                        >
                    </div>


                    <div class="campo">

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
                                value="<?= e($gasto['monto']) ?>"
                                required
                            >
                        </div>

                    </div>


                    <div class="campo">

                        <label for="tipo_gasto">
                            <span>◇</span>
                            Categoría
                        </label>

                        <select
                            id="tipo_gasto"
                            name="tipo_gasto"
                            required
                        >

                            <option value="">
                                Selecciona una categoría
                            </option>

                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?= e($categoria) ?>"
                                    <?= $gasto['tipo_gasto'] === $categoria ? 'selected' : '' ?>
                                >
                                    <?= e($categoria) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="id_presupuesto">
                            <span>◎</span>
                            Presupuesto
                        </label>

                        <select
                            id="id_presupuesto"
                            name="id_presupuesto"
                            required
                        >

                            <option value="">
                                Selecciona un presupuesto
                            </option>

                            <?php foreach ($presupuestos as $presupuesto): ?>

                                <option
                                    value="<?= (int) $presupuesto['id'] ?>"
                                    <?= (int) $presupuesto['id'] === (int) $gasto['id_presupuesto'] ? 'selected' : '' ?>
                                >
                                    <?= e($presupuesto['nombre']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="campo campo-completo">

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
                            value="<?= e($gasto['descripcion'] ?? '') ?>"
                            placeholder="Ej. Almuerzo con compañeros"
                        >

                    </div>

                </div>


                <div class="gasto-boton editar-botones">

                    <a
                        class="boton boton-secundario"
                        href="<?= e(url('gastos')) ?>"
                    >
                        Cancelar
                    </a>

                    <button class="boton" type="submit">
                        ✓ Guardar cambios
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

<?php require ROOT_PATH . '/app/views/partials/foot.php'; ?>

</body>