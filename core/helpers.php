<?php
declare(strict_types=1);

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Pon <?= csrf_field() ?> dentro de TODO formulario POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Llámalo al inicio de cada acción que procese un POST. */
function csrf_check(): void
{
    $enviado = (string) ($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['_csrf'] ?? '', $enviado)) {
        http_response_code(419);
        exit('La sesión expiró. Recarga la página e inténtalo de nuevo.');
    }
}

/* ---------- URLs ---------- */

function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

function asset(string $archivo): string
{
    return BASE_URL . '/' . ltrim($archivo, '/');
}

/* ---------- Mensajes entre peticiones ---------- */

/** flash('error', 'texto') guarda; flash('error') lee y borra. */
function flash(string $clave, mixed $valor = null): mixed
{
    if ($valor !== null) {
        $_SESSION['_flash'][$clave] = $valor;
        return null;
    }
    $v = $_SESSION['_flash'][$clave] ?? null;
    unset($_SESSION['_flash'][$clave]);
    return $v;
}

/** Valor anterior de un campo de formulario, ya escapado. */
function old(string $campo): string
{
    return e($_SESSION['_old'][$campo] ?? '');
}

/* ---------- Formato ---------- */

function money(float|int|string $monto): string
{
    return '$' . number_format((float) $monto, 0, ',', '.');
}

function fecha_larga(?int $ts = null): string
{
    $ts ??= time();
    $dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    return $dias[(int) date('w', $ts)] . ', ' . (int) date('j', $ts) . ' de ' . $meses[(int) date('n', $ts) - 1];
}

/** '2026-10-01' => '1 oct' */
function fecha_corta(string $fecha): string
{
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $ts = strtotime($fecha);

    return (int) date('j', $ts) . ' ' . $meses[(int) date('n', $ts) - 1];
}

/** '2026-10-01' => 'jue' */
function nombre_dia_corto(string $fecha): string
{
    $dias = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
    return $dias[(int) date('w', strtotime($fecha))];
}
