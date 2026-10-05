# Bolsillo — Gestor de gastos personales (PHP + MVC)

Ya incluye: **login, registro, dashboard, conexión a la BD y estilos base**.
Falta (para el equipo): sección **Gestionar presupuesto**.

## Requisitos
- PHP 8.1 o superior (extensión `pdo_mysql`)
- MySQL 8.0.16+ o MariaDB 10.2+
- Cambiar el puerto de acuerdo a su maquina en config/database

## Puesta en marcha
1. Crear las tablas: `mysql -u root -p < database/schema.sql`
   (o importar `database/schema.sql` desde phpMyAdmin).
2. Ajustar usuario/contraseña en `config/database.php`.
3. Levantar el servidor desde la carpeta del proyecto:
   `php -S localhost:8000 -t public`
   Abrir http://localhost:8000
   (Con XAMPP/WAMP: poner la carpeta en `htdocs` y abrir `/gestor-gastos/public/`; el `.htaccess` ya está.)
4. Cambiar el puerto de acuerdo a su maquina en `config/database.php`
5. Opcional: registrarse en la app y luego importar `database/datos_prueba.sql` para ver el dashboard con datos.

## Estructura
```
config/      database.php (credenciales), config.php (rutas base, zona horaria)
core/        Database, Model, Controller, Router, helpers
app/
  controllers/  AuthController, DashboardController, GastoController
  models/       Usuario, Resumen (consultas del dashboard), Gasto
  views/        auth/, dashboard/, partials/ (head, sidebar, foot), errors/, gastos/ (index, editar)
routes/web.php  mapa de rutas
public/         index.php (entrada), css/, js/ (icons.js, dashboard.js)
database/       schema.sql, datos_prueba.sql
```

## Cómo agregar una sección (Gastos / Presupuesto)
1. **Modelo** `app/models/Gasto.php`:
   ```php
   class Gasto extends Model {
       public function listar(int $idUsuario): array {
           $st = $this->db->prepare('SELECT * FROM gastos WHERE id_usuario = ? ORDER BY fecha DESC');
           $st->execute([$idUsuario]);
           return $st->fetchAll();
       }
   }
   ```
   `$this->db` ya es la conexión PDO (también disponible como `Database::connect()`).
2. **Controlador** `app/controllers/GastoController.php`:
   ```php
   class GastoController extends Controller {
       public function index(): void {
           $idUsuario = $this->requireAuth();          // protege la página
           $this->view('gastos/index', ['gastos' => (new Gasto())->listar($idUsuario)]);
       }
   }
   ```
3. **Vista** `app/views/gastos/index.php`: copiar la cabecera del dashboard
   (`$titulo`, `$activo = 'gastos'`, `partials/head.php`, `partials/sidebar.php`, `partials/foot.php`).
4. **Ruta**: descomentar/agregar la línea en `routes/web.php`.

## Reglas del equipo
- Siempre filtrar por `id_usuario` (`$this->requireAuth()` lo devuelve) para que nadie vea gastos ajenos.
- Mostrar datos con `e($valor)` y poner `<?= csrf_field() ?>` + `csrf_check()` en todo formulario POST.
- Un gasto puede ligarse a un presupuesto con `gastos.id_presupuesto` (es opcional);
  el dashboard calcula "lo gastado" del presupuesto con esos gastos ligados.
- Iconos: agregar el `<svg>` en `public/js/icons.js` y usarlo con `<span class="icono" data-icon="nombre"></span>`.
