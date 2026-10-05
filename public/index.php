<?php
declare(strict_types=1);

// Punto de entrada único: todas las peticiones pasan por aquí.
require __DIR__ . '/../config/config.php';
require ROOT_PATH . '/core/helpers.php';

// Autocarga de clases (core, controladores y modelos)
spl_autoload_register(function (string $clase): void {
    foreach (['core', 'app/controllers', 'app/models'] as $carpeta) {
        $archivo = ROOT_PATH . "/$carpeta/$clase.php";
        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

$rutas = require ROOT_PATH . '/routes/web.php';
(new Router($rutas))->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
