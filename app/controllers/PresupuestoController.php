<?php

class PresupuestoController extends Controller {

    public function index(): void {
        $idUsuario = $this->requireAuth();
        $presupuestoModel = new Presupuesto();

        $this->view('presupuesto/index', [
            'presupuestos' => $presupuestoModel->listar($idUsuario)
        ]);
    }

    public function guardar(): void {
    $idUsuario = $this->requireAuth();
    csrf_check();

    $nombre = trim($_POST['nombre'] ?? '');
    $fechaInicio = $_POST['fecha_inicio'] ?? '';
    $fechaFin = $_POST['fecha_fin'] ?? '';
    $limite = (float) ($_POST['limite'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($fechaInicio) || empty($fechaFin) || $limite <= 0) {
        flash('error', 'Por favor completa todos los campos correctamente.');
        header('Location: ' . url('presupuesto'));
        exit;
    }
    //validación de fecha
    if ($fechaFin < $fechaInicio) {
        flash('error', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
        header('Location: ' . url('presupuesto'));
        exit;
    }

    $presupuestoModel = new Presupuesto();

    // Validación de nombre duplicado
    if ($presupuestoModel->existeNombre($nombre, $idUsuario)) {
        flash('error', 'Ya tienes un presupuesto registrado con ese nombre.');
        header('Location: ' . url('presupuesto'));
        exit;
    }

    $presupuestoModel->crear($idUsuario, $nombre, $fechaInicio, $fechaFin, $limite, $activo);

    flash('exito', 'Presupuesto creado correctamente.');
    header('Location: ' . url('presupuesto'));
    exit;
}

    public function activar(): void {
        $idUsuario = $this->requireAuth();
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $presupuestoModel = new Presupuesto();
            $presupuestoModel->activar($id, $idUsuario);
            flash('exito', 'Presupuesto activado correctamente.');
        }

        header('Location: ' . url('presupuesto'));
        exit;
    }

    public function editar(): void {
        $idUsuario = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $presupuestoModel = new Presupuesto();
        $presupuesto = $presupuestoModel->buscar($id, $idUsuario);

        if (!$presupuesto) {
            flash('error', 'Presupuesto no encontrado.');
            header('Location: ' . url('presupuesto'));
            exit;
        }

        $this->view('presupuesto/editar', [
            'presupuesto' => $presupuesto
        ]);
    }

   public function actualizar(): void {
    $idUsuario = $this->requireAuth();
    csrf_check();

    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $fechaInicio = $_POST['fecha_inicio'] ?? '';
    $fechaFin = $_POST['fecha_fin'] ?? '';
    $limite = (float) ($_POST['limite'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($id <= 0 || empty($nombre) || empty($fechaInicio) || empty($fechaFin) || $limite <= 0) {
        flash('error', 'Todos los campos son obligatorios.');
        header('Location: ' . url('presupuesto/editar?id=' . $id));
        exit;
    }

    if ($fechaFin < $fechaInicio) {
        flash('error', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
        header('Location: ' . url('presupuesto/editar?id=' . $id));
        exit;
    }

    $presupuestoModel = new Presupuesto();

    // Validación de nombre duplicado (excluyendo el presupuesto actual que se está editando)
    if ($presupuestoModel->existeNombre($nombre, $idUsuario, $id)) {
        flash('error', 'Ya tienes otro presupuesto con ese mismo nombre.');
        header('Location: ' . url('presupuesto/editar?id=' . $id));
        exit;
    }

    $presupuestoModel->actualizar($id, $idUsuario, $nombre, $fechaInicio, $fechaFin, $limite, $activo);

    flash('exito', 'Presupuesto actualizado correctamente.');
    header('Location: ' . url('presupuesto'));
    exit;
}

    public function eliminar(): void {
        $idUsuario = $this->requireAuth();
        csrf_check();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $presupuestoModel = new Presupuesto();
            $presupuestoModel->eliminar($id, $idUsuario);
            flash('exito', 'Presupuesto eliminado correctamente.');
        }

        header('Location: ' . url('presupuesto'));
        exit;
    }
}