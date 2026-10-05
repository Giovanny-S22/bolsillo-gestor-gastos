<?php

return [
    // ---- Inicio / autenticación / dashboard (ya hechos) ----
    'GET /'          => [DashboardController::class, 'index'],
    'GET /dashboard' => [DashboardController::class, 'index'],
    'GET /login'     => [AuthController::class, 'mostrarLogin'],
    'POST /login'    => [AuthController::class, 'login'],
    'GET /registro'  => [AuthController::class, 'mostrarRegistro'],
    'POST /registro' => [AuthController::class, 'registro'],
    'POST /logout'   => [AuthController::class, 'logout'],

    // ---- Sección GASTOS ----
    'GET /gastos'             => [GastoController::class, 'index'],
    'POST /gastos'            => [GastoController::class, 'guardar'],
    'GET /gastos/editar'      => [GastoController::class, 'editar'],
    'POST /gastos/actualizar' => [GastoController::class, 'actualizar'],
    'POST /gastos/eliminar'   => [GastoController::class, 'eliminar'],

    // ---- Sección GESTIONAR PRESUPUESTO ----
    // 'GET /presupuesto'     => [PresupuestoController::class, 'index'],
    // 'POST /presupuesto'    => [PresupuestoController::class, 'guardar'],
];
