<?php
declare(strict_types=1);

class Usuario extends Model
{
    public function buscarPorEmail(string $email): ?array
    {
        $st = $this->db->prepare('SELECT id, nombre, email, password_hash FROM usuarios WHERE email = ? LIMIT 1');
        $st->execute([$email]);

        return $st->fetch() ?: null;
    }

    /** Crea el usuario y devuelve su id. La contraseña se guarda con hash. */
    public function crear(string $nombre, string $email, string $password): int
    {
        $st = $this->db->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)');
        $st->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]);

        return (int) $this->db->lastInsertId();
    }
}
