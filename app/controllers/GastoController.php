<?php
declare(strict_types=1);

class GastoController extends Controller
{
    public function index(): void
    {
        $idUsuario = $this->requireAuth();
        $gasto = new Gasto();
        $categoria = new Categoria();
        $presupuestoActivo = $gasto->presupuestoActivo($idUsuario);

        $alertaPresupuesto = $_SESSION['gasto_pendiente_confirmacion'] ?? null;

        // Evitar mostrar una alerta perteneciente a otro usuario.
        if (
            $alertaPresupuesto !== null &&
            $alertaPresupuesto['id_usuario'] !== $idUsuario
        ) {
            unset($_SESSION['gasto_pendiente_confirmacion']);
            $alertaPresupuesto = null;
        }

        $this->view('gastos/index', [
            'gastos' => $gasto->listar($idUsuario),
            'presupuestos' => $gasto->presupuestos($idUsuario),
            'categorias' => $categoria->listarActivas(),
            'presupuestoActivo' => $presupuestoActivo,
            'alertaPresupuesto' => $alertaPresupuesto,
        ]);
    }

    public function guardar(): void
    {
        $idUsuario = $this->requireAuth();

        csrf_check();
        // Cancelar el registro pendiente.
        if (isset($_POST['cancelar_exceso'])) {
            unset($_SESSION['gasto_pendiente_confirmacion']);

            flash('error', 'Registro de gasto cancelado.');
            $this->redirect('gastos');
        }
        // Comprobar si el usuario confirmó el registro.
        $confirmado = isset($_POST['confirmar_exceso']);

        if ($confirmado) {
            $pendiente = $_SESSION['gasto_pendiente_confirmacion'] ?? null;
            if (
                !$pendiente ||
                $pendiente['id_usuario'] !== $idUsuario
            ) {
                unset($_SESSION['gasto_pendiente_confirmacion']);

                flash('error', 'No hay un gasto pendiente por confirmar.');
                $this->redirect('gastos');
            }
            // Recuperar los datos guardados en sesión.
            $fecha = $pendiente['fecha'];
            $monto = $pendiente['monto'];
            $idCategoria = $pendiente['id_categoria'];
            $idPresupuesto = $pendiente['id_presupuesto'];
            $descripcion = $pendiente['descripcion'];
            unset($_SESSION['gasto_pendiente_confirmacion']);
        } else {
            $fecha = trim((string) ($_POST['fecha'] ?? ''));
            $monto = (float) ($_POST['monto'] ?? 0);
            $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

            $idPresupuesto = $_POST['id_presupuesto'] ?? '';
            $idPresupuesto = $idPresupuesto !== ''
                ? (int) $idPresupuesto
                : 0;
        }
        // Validar los datos.
        if ($fecha === '' || $monto <= 0 || $idCategoria <= 0) {
            flash('error', 'Completa correctamente la fecha, el monto y la categoría.');
            $this->redirect('gastos');
        }
        if ($idPresupuesto <= 0) {
            flash('error', 'Debes seleccionar un presupuesto.');
            $this->redirect('gastos');
        }
        $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha);
        if (
            !$fechaObjeto ||
            $fechaObjeto->format('Y-m-d') !== $fecha
        ) {
            flash('error', 'La fecha del gasto no es válida.');
            $this->redirect('gastos');
        }

        if ($fecha > date('Y-m-d')) {
            flash('error', 'No puedes registrar un gasto con una fecha futura.');
            $this->redirect('gastos');
        }

        $gasto = new Gasto();

        if (!$gasto->presupuestoPerteneceUsuario($idPresupuesto, $idUsuario)) {
            flash('error', 'El presupuesto seleccionado no es válido.');
            $this->redirect('gastos');
        }

        $categoria = new Categoria();

        if (!$categoria->estaActiva($idCategoria)) {
            flash('error', 'La categoría seleccionada no está disponible.');
            $this->redirect('gastos');
        }
        // Comprobar si el gasto alcanza o supera el límite.
        if (!$confirmado) {
            $resumen = $gasto->resumenPresupuesto(
                $idUsuario,
                $idPresupuesto
            );

            if ($resumen && (float) $resumen['limite'] > 0) {
                $totalDespues = (float) $resumen['total_gastado'] + $monto;
                $limite = (float) $resumen['limite'];

                if ($totalDespues >= $limite) {
                    $_SESSION['gasto_pendiente_confirmacion'] = [
                        'id_usuario' => $idUsuario,
                        'fecha' => $fecha,
                        'monto' => $monto,
                        'id_categoria' => $idCategoria,
                        'id_presupuesto' => $idPresupuesto,
                        'descripcion' => $descripcion !== '' ? $descripcion : null,

                        'nombre_presupuesto' => $resumen['nombre'],
                        'limite' => $limite,
                        'total_gastado' => (float) $resumen['total_gastado'],
                        'total_despues' => $totalDespues,
                        'excedente' => max(0, $totalDespues - $limite),
                    ];

                    $this->redirect('gastos');
                }
            }
        }

        // Registrar el gasto.
        $gasto->crear(
            $idUsuario,
            $idPresupuesto,
            $fecha,
            $monto,
            $idCategoria,
            $descripcion !== '' ? $descripcion : null
        );

        flash('exito', 'Gasto registrado correctamente.');

        $this->redirect('gastos');
    }

    public function editar(): void{
        $idUsuario = $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'El gasto seleccionado no es válido.');
            $this->redirect('gastos');
        }

        $gasto = new Gasto();

        $gastoEncontrado = $gasto->buscarPorId($id, $idUsuario);

        if (!$gastoEncontrado) {
            flash('error', 'El gasto no existe o no tienes permiso para editarlo.');
            $this->redirect('gastos');
        }

        $this->view('gastos/editar', [
            'gasto' => $gastoEncontrado,
            'presupuestos' => $gasto->presupuestos($idUsuario),
            'categorias' => (new Categoria())->listarActivas(),
        ]);
    }

    public function actualizar(): void{
        $idUsuario = $this->requireAuth();

        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);

        $fecha = trim((string) ($_POST['fecha'] ?? ''));
        $monto = (float) ($_POST['monto'] ?? 0);
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        $idPresupuesto = (int) ($_POST['id_presupuesto'] ?? 0);

        if ($id <= 0) {
            flash('error', 'El gasto seleccionado no es válido.');
            $this->redirect('gastos');
        }

        if ($fecha === '' || $monto <= 0 || $idCategoria <= 0) {
            flash('error', 'Completa correctamente la fecha, el monto y la categoría.');
            $this->redirect('gastos');
        }

        if ($idPresupuesto <= 0) {
            flash('error', 'Debes seleccionar un presupuesto.');
            $this->redirect('gastos');
        }

        $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha);

        if (
            !$fechaObjeto ||
            $fechaObjeto->format('Y-m-d') !== $fecha
        ) {
            flash('error', 'La fecha del gasto no es válida.');
            $this->redirect('gastos');
        }

        if ($fecha > date('Y-m-d')) {
            flash('error', 'No puedes registrar un gasto con una fecha futura.');
            $this->redirect('gastos');
        }

        $gasto = new Gasto();

        $gastoEncontrado = $gasto->buscarPorId($id, $idUsuario);

        if (!$gastoEncontrado) {
            flash('error', 'El gasto no existe o no tienes permiso para editarlo.');
            $this->redirect('gastos');
        }

        if (!$gasto->presupuestoPerteneceUsuario($idPresupuesto, $idUsuario)) {
            flash('error', 'El presupuesto seleccionado no es válido.');
            $this->redirect('gastos');
        }

        $categoria = new Categoria();

        $categoriaValida = $categoria->estaActiva($idCategoria);

        $categoriaActual = (int) $gastoEncontrado['id_categoria'];

        if (!$categoriaValida && $idCategoria !== $categoriaActual) {
            flash('error', 'La categoría seleccionada no está disponible.');
            $this->redirect('gastos');
        }

        $gasto->actualizar(
            $id,
            $idUsuario,
            $idPresupuesto,
            $fecha,
            $monto,
            $idCategoria,
            $descripcion !== '' ? $descripcion : null
        );

        flash('exito', 'Gasto actualizado correctamente.');

        $this->redirect('gastos');
    }

    public function eliminar(): void{
        $idUsuario = $this->requireAuth();

        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'El gasto seleccionado no es válido.');
            $this->redirect('gastos');
        }

        $gasto = new Gasto();

        if (!$gasto->buscarPorId($id, $idUsuario)) {
            flash('error', 'El gasto no existe o no tienes permiso para eliminarlo.');
            $this->redirect('gastos');
        }

        $gasto->eliminar($id, $idUsuario);

        flash('exito', 'Gasto eliminado correctamente.');

        $this->redirect('gastos');
    }


}