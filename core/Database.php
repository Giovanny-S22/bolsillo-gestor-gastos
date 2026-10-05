<?php
declare(strict_types=1);

/**
 * Conexión única a MySQL con PDO.
 *
 * Uso en cualquier modelo:
 *   $db = Database::connect();
 *   $st = $db->prepare('SELECT * FROM gastos WHERE id_usuario = ?');
 *   $st->execute([$idUsuario]);
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $c = require ROOT_PATH . '/config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $c['host'], $c['port'], $c['dbname'], $c['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Error de conexión: ' . $e->getMessage());
                http_response_code(500);
                exit('No se pudo conectar a la base de datos. Revisa config/database.php');
            }
        }

        return self::$pdo;
    }
}
