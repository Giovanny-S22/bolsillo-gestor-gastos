<?php
declare(strict_types=1);

/** Clase base de los controladores. */
abstract class Controller
{
    /** Carga una vista de app/views/ (ej: 'dashboard/index') pasándole variables. */
    protected function view(string $vista, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);
        require ROOT_PATH . '/app/views/' . $vista . '.php';
        unset($_SESSION['_old']); // los valores "old" solo viven una carga
    }

    protected function redirect(string $ruta): never
    {
        header('Location: ' . url($ruta));
        exit;
    }

    /** Protege una página: si no hay sesión manda al login. Devuelve el id del usuario. */
    protected function requireAuth(): int
    {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirect('login');
        }
        return (int) $_SESSION['usuario_id'];
    }

    /** Para login/registro: si ya hay sesión manda al dashboard. */
    protected function requireGuest(): void
    {
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirect('dashboard');
        }
    }
}
