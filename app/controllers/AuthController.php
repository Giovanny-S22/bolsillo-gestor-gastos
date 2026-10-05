<?php
declare(strict_types=1);

class AuthController extends Controller
{
    public function mostrarLogin(): void
    {
        $this->requireGuest();
        $this->view('auth/login');
    }

    public function login(): void
    {
        $this->requireGuest();
        csrf_check();

        $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $usuario = (new Usuario())->buscarPorEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            flash('error', 'El correo o la contraseña no coinciden.');
            $_SESSION['_old'] = ['email' => $email];
            $this->redirect('login');
        }

        $this->iniciarSesion((int) $usuario['id'], $usuario['nombre']);
        $this->redirect('dashboard');
    }

    public function mostrarRegistro(): void
    {
        $this->requireGuest();
        $this->view('auth/registro');
    }

    public function registro(): void
    {
        $this->requireGuest();
        csrf_check();

        $nombre    = trim((string) ($_POST['nombre'] ?? ''));
        $email     = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password  = (string) ($_POST['password'] ?? '');
        $confirmar = (string) ($_POST['confirmar'] ?? '');

        $errores = [];
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 80) {
            $errores[] = 'Escribe tu nombre (entre 2 y 80 caracteres).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $errores[] = 'Escribe un correo válido.';
        }
        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirmar) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        $usuarios = new Usuario();

        if (!$errores && $usuarios->buscarPorEmail($email)) {
            $errores[] = 'Ya existe una cuenta con ese correo. Inicia sesión.';
        }

        if ($errores) {
            flash('errores', $errores);
            $_SESSION['_old'] = ['nombre' => $nombre, 'email' => $email];
            $this->redirect('registro');
        }

        try {
            $id = $usuarios->crear($nombre, $email, $password);
        } catch (PDOException $e) {
            // 23000 = correo duplicado (por si dos personas se registran a la vez)
            if ($e->getCode() === '23000') {
                flash('errores', ['Ya existe una cuenta con ese correo. Inicia sesión.']);
                $this->redirect('registro');
            }
            throw $e;
        }

        $this->iniciarSesion($id, $nombre);
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        csrf_check();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . url('login'));
        exit;
    }

    private function iniciarSesion(int $id, string $nombre): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $id;
        $_SESSION['usuario_nombre'] = $nombre;
    }
}
