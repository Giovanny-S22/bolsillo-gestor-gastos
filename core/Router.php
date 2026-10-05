<?php
declare(strict_types=1);

/** Router mínimo: busca "METODO /ruta" en routes/web.php y ejecuta el controlador. */
final class Router
{
    public function __construct(private array $rutas) {}

    public function dispatch(string $metodo, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
            $path = substr($path, strlen(BASE_URL));
        }
        $path = '/' . trim($path, '/');

        $clave = strtoupper($metodo) . ' ' . $path;

        if (!isset($this->rutas[$clave])) {
            http_response_code(404);
            require ROOT_PATH . '/app/views/errors/404.php';
            return;
        }

        [$clase, $accion] = $this->rutas[$clave];
        (new $clase())->$accion();
    }
}
