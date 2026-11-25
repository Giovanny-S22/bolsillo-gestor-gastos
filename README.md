# Bolsillo — Gestor de gastos personales (PHP + MVC + chatbot Rasa)

Aplicación web para llevar el control de gastos y presupuestos personales. Incluye:

- **Login y registro** de usuarios.
- **Dashboard** con el resumen de gastos y presupuestos.
- **Gastos**: registrar, listar, editar y eliminar.
- **Presupuestos**: crear, activar, editar y eliminar.
- **BolsiBot**: un asistente (chat flotante) construido con **Rasa** que permite registrar gastos, crear presupuestos y consultar información escribiendo en lenguaje natural.

El proyecto tiene dos partes que se ejecutan al mismo tiempo:

| Parte | Tecnología | Puerto por defecto |
|-------|------------|--------------------|
| Aplicación web | PHP 8.1+ y MySQL/MariaDB | 8000 (o el de Apache/XAMPP) |
| Chatbot BolsiBot | Python 3.10 y Rasa 3.6.21 | 5005 |

---

## Requisitos

- PHP 8.1 o superior (extensión `pdo_mysql`)
- MySQL 8.0.16+ o MariaDB 10.2+
- **Python 3.10** (Rasa 3.6.21 no funciona con Python 3.11 o superior)
- Git

### Descargar Python 3.10

- Página de descargas: <https://www.python.org/downloads/>
- Versión recomendada para Windows: **Python 3.10.11** (la última 3.10 con instalador): <https://www.python.org/downloads/release/python-31011/>

Al instalar en Windows, marca la casilla **"Add python.exe to PATH"**.

Para verificar que quedó instalado:

```bash
py -3.10 --version
```

Debe mostrar `Python 3.10.x`.

---

## Estructura del proyecto

```
bolsillo-gestor-gastos/
├── app/
│   ├── controllers/     Auth, Dashboard, Gasto, Presupuesto y Bot (puente con Rasa)
│   ├── models/          Usuario, Gasto, Presupuesto, Categoria, Resumen y BotAcciones
│   └── views/           auth/, dashboard/, gastos/, presupuesto/, partials/ (incluye asistente.php), errors/
├── config/              database.php, config.php y bot.php (dirección de Rasa)
├── core/                Database, Model, Controller, Router, helpers y BotParser
├── database/            schema.sql y datos_prueba.sql
├── public/              index.php (entrada), css/, js/ (incluye asistente.js)
├── routes/web.php       mapa de rutas
├── rasa/
│   ├── venv/            entorno virtual de Python (NO se sube a Git)
│   └── BolsiBot-V2/     el bot
│       ├── config.yml       pipeline de NLU y políticas
│       ├── domain.yml       intents, entidades, slots, respuestas y formularios
│       ├── data/
│       │   ├── nlu.yml      ejemplos de frases por intent
│       │   └── rules.yml    reglas de conversación
│       └── models/          modelo entrenado (NO se sube a Git)
└── README.md
```

La carpeta `rasa/` contiene el entorno virtual, y dentro de ella `BolsiBot-V2/` contiene únicamente el bot. Así se separa el código del asistente del resto del sistema.

---

## 1. Puesta en marcha de la aplicación web

1. Crear las tablas: `mysql -u root -p < database/schema.sql`
   (o importar `database/schema.sql` desde phpMyAdmin).
2. Ajustar host, puerto, usuario y contraseña en `config/database.php` (por defecto el puerto es `3309`; cámbialo según tu máquina).
3. Levantar el servidor desde la carpeta del proyecto:
   ```bash
   php -S localhost:8000 -t public
   ```
   Abrir <http://localhost:8000>
   (Con XAMPP/WAMP: poner la carpeta en `htdocs` y abrir `/gestor-gastos/public/`; el `.htaccess` ya está.)
4. Opcional: registrarse en la app y luego importar `database/datos_prueba.sql` para ver el dashboard con datos.

---

## 2. Instalación del chatbot (Rasa) — solo la primera vez

Todos los comandos se ejecutan desde una terminal (PowerShell o CMD en Windows).

### Paso 1. Ir a la carpeta `rasa` del proyecto

```bash
cd bolsillo-gestor-gastos/rasa
```

Si la carpeta no existe, créala.

### Paso 2. Crear el entorno virtual con Python 3.10

```bash
py -3.10 -m venv venv
```

> En macOS/Linux: `python3.10 -m venv venv`

Verifica que se creó la carpeta `venv`:

```bash
dir        # Windows
ls         # macOS/Linux
```

### Paso 3. Activar el entorno virtual

```bash
venv\Scripts\activate          # Windows (CMD o PowerShell)
source venv/bin/activate       # macOS/Linux
```

Cuando está activo, el nombre `(venv)` aparece al inicio de la línea de la terminal.

> Si PowerShell bloquea la activación, ejecuta primero:
> `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`

### Paso 4. Actualizar pip

```bash
python -m pip install --upgrade pip
```

Esto asegura que se use la versión más reciente de `pip`, el instalador de paquetes de Python, y evita errores al instalar Rasa.

### Paso 5. Instalar Rasa 3.6.21

```bash
python -m pip install rasa==3.6.21
```

> Escríbelo sin espacios alrededor del `==`. La instalación puede tardar varios minutos.

Verifica la instalación:

```bash
rasa --version
```

Debe mostrar `Rasa Version: 3.6.21`.

### Paso 6. Entrar a la carpeta del bot

```bash
cd BolsiBot-V2
```

Debe tener el entorno virtual activo (`(venv)` visible).

### Paso 7. Validar los datos del bot

```bash
rasa data validate
```

Revisa que `config.yml`, `domain.yml` y los archivos de `data/` no tengan errores.

### Paso 8. Entrenar el modelo

```bash
rasa train
```

Se genera la carpeta `models/` con el modelo entrenado. Hay que repetir este paso cada vez que cambien `nlu.yml`, `rules.yml`, `domain.yml` o `config.yml`.

### Paso 9. Probar el bot en la terminal (opcional)

```bash
rasa shell
```

Escribe mensajes como "hola" o "quiero registrar un gasto" para ver las respuestas. Sale con `/stop`.
Aquí solo se prueba la comprensión del bot: los datos no se guardan en la base de datos, porque eso lo hace la aplicación PHP.


---

## 3. Uso diario

Cada vez que quieras trabajar con el asistente necesitas **dos terminales**.

**Terminal 1 — Aplicación web**

```bash
cd bolsillo-gestor-gastos
php -S localhost:8000 -t public
```

**Terminal 2 — Rasa**

```bash
cd bolsillo-gestor-gastos/rasa
venv\Scripts\activate
cd BolsiBot-V2
rasa run --port 5005
```

Abre la aplicación, inicia sesión y usa el botón flotante de BolsiBot (abajo a la derecha).

### Indicador de estado

El botón redondo del chat tiene un punto de color:

| Color | Significado |
|-------|-------------|
| Gris | Verificando la conexión |
| Verde | Rasa está activo y responde |
| Rojo | Rasa no responde (apagado o puerto distinto) |

La aplicación revisa el estado cada 10 segundos.

---

## 4. Cómo funciona el chatbot

```
Navegador (asistente.js)
   │  mensaje + sesión + token CSRF
   ▼
PHP: BotController ───────────────►  Rasa (puerto 5005)
   ▲                  respuesta        entiende el mensaje (intent y entidades)
   │
   ▼
PHP: BotAcciones  ──►  MySQL (gastos, presupuestos, categorías)
```

1. El usuario escribe en el chat. `public/js/asistente.js` envía el mensaje a `POST /asistente/mensaje`.
2. `BotController` valida la sesión y el token CSRF, y reenvía el texto a Rasa por su canal REST.
3. Rasa **solo interpreta** el mensaje y conduce la conversación (por ejemplo, pide el monto, la categoría y la fecha de un gasto). Cuando ya tiene todos los datos, responde con una acción `custom`.
4. `BotAcciones` ejecuta esa acción en MySQL reutilizando los modelos `Gasto`, `Presupuesto` y `Categoria`, con las mismas reglas que la web.
5. El resultado se devuelve al chat.

**Seguridad:** el id del usuario siempre sale de la sesión PHP, nunca del texto escrito ni de Rasa. Rasa no accede a la base de datos.

### Rutas del chatbot

| Ruta | Función |
|------|---------|
| `POST /asistente/mensaje` | Envía un mensaje a Rasa y devuelve la respuesta |
| `POST /asistente/reiniciar` | Empieza una conversación nueva |
| `GET /asistente/estado` | Indica si Rasa está activo (punto verde/rojo) |

### Qué puede hacer BolsiBot

- Registrar un gasto (monto, categoría, fecha y descripción).
- Crear un presupuesto (nombre, límite, fecha de inicio y de fin).
- Consultar gastos por periodo (hoy, esta semana, este mes, mes pasado) y por categoría.
- Listar presupuestos y ver el estado de uno.
- Dar consejos de ahorro.
- Editar, eliminar o activar un gasto/presupuesto: por ahora solo identifica lo que se pide y no modifica datos.

Categorías disponibles: comida, transporte, mercado, servicios, ocio, salud, educación y otros.

Montos que entiende: `20000`, `30.000`, `45 mil`, `2 millones`.
Fechas que entiende: `hoy`, `ayer` o `AAAA-MM-DD`.

### Configuración

La dirección de Rasa está en `config/bot.php`:

```php
'rasa_url'  => 'http://127.0.0.1:5005/webhooks/rest/webhook',
'timeout'   => 15,   // segundos de espera a Rasa
'max_texto' => 500,  // máximo de caracteres por mensaje
```

Usa `127.0.0.1` y no `localhost`: en Windows `localhost` puede intentar IPv6 primero y hacer que la verificación de estado falle.
Si algún integrante usa otro puerto para Rasa, solo debe cambiarlo en este archivo (y no subir ese cambio a Git).

---

## 5. Cómo modificar el bot

| Archivo | Para qué sirve |
|---------|----------------|
| `data/nlu.yml` | Frases de ejemplo de cada intent (más ejemplos = mejor comprensión) |
| `data/rules.yml` | Reglas de conversación fijas |
| `domain.yml` | Intents, entidades, slots, respuestas y formularios |
| `config.yml` | Pipeline de NLU y políticas |

Después de cualquier cambio: `rasa data validate`, luego `rasa train`, y reiniciar `rasa run`.

Si el bot necesita hacer una acción nueva en Bolsillo:
1. Agregar una respuesta `custom` con `accion: "nombre"` en `domain.yml`.
2. Agregar ese caso en el `match` de `BotAcciones::ejecutar()` y programar el método.

---

## 6. Problemas frecuentes

| Problema | Solución |
|----------|----------|
| `rasa` no se reconoce como comando | El entorno virtual no está activo. Ejecuta `venv\Scripts\activate` |
| Error al instalar Rasa | Verifica con `python --version` que sea 3.10.x dentro del entorno virtual |
| El punto del chat siempre está rojo | Rasa no está corriendo, o el puerto de `config/bot.php` no coincide con el de `rasa run` |
| "El asistente no está disponible" | Rasa no está corriendo o todavía está cargando el modelo (espera unos segundos) |
| `rasa run` dice que no hay modelo | Falta entrenar: ejecuta `rasa train` dentro de `rasa/BolsiBot-V2` |
| El puerto 5005 está ocupado | Cierra el otro proceso, o usa `rasa run --port 5006` y cambia `config/bot.php` |

---

## Reglas del equipo

- Siempre filtrar por `id_usuario` (`$this->requireAuth()` lo devuelve) para que nadie vea gastos ajenos.
- Mostrar datos con `e($valor)` y poner `<?= csrf_field() ?>` + `csrf_check()` en todo formulario POST.
- Un gasto puede ligarse a un presupuesto con `gastos.id_presupuesto` (es opcional); el dashboard calcula "lo gastado" del presupuesto con esos gastos ligados.
- Iconos: agregar el `<svg>` en `public/js/icons.js` y usarlo con `<span class="icono" data-icon="nombre"></span>`.
- No subir a Git el entorno virtual (`venv/`), los modelos (`models/`) ni la carpeta `.rasa/`: ya están en `.gitignore`.
