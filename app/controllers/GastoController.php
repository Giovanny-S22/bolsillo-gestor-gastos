<?php
declare(strict_types=1);

class GastoController extends Controller
{
    public function index(): void
    {
        $idUsuario = $this->requireAuth();

        $gasto = new Gasto();

        $categoria = new Categoria();

        $this->view('gastos/index', [
            'gastos' => $gasto->listar($idUsuario),
            'presupuestos' => $gasto->presupuestos($idUsuario),
            'categorias' => $categoria->listarActivas(),
        ]);
    }

    public function guardar(): void{
        $idUsuario = $this->requireAuth();

        csrf_check();

        $fecha = trim((string) ($_POST['fecha'] ?? ''));
        $monto = (float) ($_POST['monto'] ?? 0);
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        $idPresupuesto = $_POST['id_presupuesto'] ?? '';
        $idPresupuesto = $idPresupuesto !== ''
            ? (int) $idPresupuesto
            : 0;

        // validacion por si falta fecha, monto o tipo de gasto
        if ($fecha === '' || $monto <= 0 || $idCategoria <= 0) {
            flash('error', 'Completa correctamente la fecha, el monto y la categoría.');
            $this->redirect('gastos');
        }

        // validacion si no hay presupuesto
        if ($idPresupuesto <= 0) {
            flash('error', 'Debes seleccionar un presupuesto para registrar el gasto.');
            $this->redirect('gastos');
        }

        // validar si tiene el formato correcto
        $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha);

        if (
            !$fechaObjeto ||
            $fechaObjeto->format('Y-m-d') !== $fecha
        ) {
            flash('error', 'La fecha del gasto no es válida.');
            $this->redirect('gastos');
        }

        
        // no permite fechas futuras
        $hoy = date('Y-m-d');

        if ($fecha > $hoy) {
            flash('error', 'No puedes registrar un gasto con una fecha futura.');
            $this->redirect('gastos');
        }

        
        // se valida con el modelo que el presupuesto pertenezca al usuario
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