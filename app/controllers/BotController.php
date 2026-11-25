<?php
declare(strict_types=1);

/**
 * Puente entre el chat flotante y Rasa.
 *   navegador --(sesión + CSRF)--> PHP --> Rasa --> PHP ejecuta en MySQL --> navegador
 */
class BotController extends Controller
{
    /** Recibe un mensaje del chat y devuelve las respuestas en JSON. */
    public function mensaje(): void
    {
        $idUsuario = $this->autenticarJson();

        $config = require ROOT_PATH . '/config/bot.php';
        $cuerpo = json_decode((string) file_get_contents('php://input'), true);
        $texto = trim((string) (is_array($cuerpo) ? ($cuerpo['mensaje'] ?? '') : ''));
        if ($texto === '' || mb_strlen($texto) > $config['max_texto']) {
            $this->json(['error' => 'Escribe un mensaje de hasta ' . $config['max_texto'] . ' caracteres.'], 422);
        }

        // Cada inicio de sesión (o "Reiniciar") genera un token nuevo => conversación nueva en Rasa.
        $_SESSION['bot_token'] ??= bin2hex(random_bytes(8));
        $sender = 'usuario_' . $idUsuario . '_' . $_SESSION['bot_token'];

        $respuestas = $this->preguntarARasa($config, $sender, $texto);
        if ($respuestas === null) {
            $this->json(['mensajes' => ['El asistente no está disponible en este momento. Intenta más tarde.']]);
        }

        // Texto normal se muestra; un "custom" con "accion" lo ejecuta Bolsillo.
        $acciones = new BotAcciones();
        $mensajes = [];
        foreach ($respuestas as $r) {
            if (isset($r['custom']['accion'])) {
                $mensajes[] = $acciones->ejecutar($idUsuario, $r['custom']);
            } elseif (!empty($r['text'])) {
                $mensajes[] = (string) $r['text'];
            }
        }
        $this->json(['mensajes' => $mensajes]);
    }

    /** Botón "Reiniciar": empieza una conversación nueva (Rasa olvida el formulario en curso). */
    public function reiniciar(): void
    {
        $this->autenticarJson();
        $_SESSION['bot_token'] = bin2hex(random_bytes(8));
        $this->json(['token' => $_SESSION['bot_token']]);
    }

    /** ¿Está encendido Rasa? Lo usa el punto verde/rojo del botón. */
    public function estado(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (empty($_SESSION['usuario_id'])) {
            $this->json(['error' => 'Tu sesión expiró.'], 401);
        }
        session_write_close(); // no bloquear la sesión mientras esperamos a Rasa

        $config = require ROOT_PATH . '/config/bot.php';
        $salud = substr($config['rasa_url'], 0, (int) strrpos($config['rasa_url'], '/') + 1);
        $contexto = stream_context_create(['http' => [
            'method'        => 'GET',
            'timeout'       => 5,
            'ignore_errors' => true,
        ]]);
        $respuesta = @file_get_contents($salud, false, $contexto);
        $datos = $respuesta === false ? null : json_decode($respuesta, true);

        $this->json([
            'activo' => is_array($datos) && ($datos['status'] ?? '') === 'ok',
            'debug'  => [ // quitar cuando funcione
                'url'       => $salud,
                'respuesta' => $respuesta === false ? (error_get_last()['message'] ?? 'sin respuesta') : $respuesta,
            ],
        ]);
    }

    /**
     * Valida sesión y CSRF para los endpoints JSON. El id del usuario SIEMPRE sale de la
     * sesión PHP: nunca del mensaje ni de Rasa.
     */
    private function autenticarJson(): int
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['usuario_id'])) {
            $this->json(['error' => 'Tu sesión expiró. Inicia sesión de nuevo.'], 401);
        }
        $token = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            $this->json(['error' => 'La sesión expiró. Recarga la página.'], 419);
        }
        return (int) $_SESSION['usuario_id'];
    }

    private function preguntarARasa(array $config, string $sender, string $texto): ?array
    {
        $contexto = stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n",
            'content'       => json_encode(['sender' => $sender, 'message' => $texto], JSON_UNESCAPED_UNICODE),
            'timeout'       => $config['timeout'],
            'ignore_errors' => true,
        ]]);

        $resp = @file_get_contents($config['rasa_url'], false, $contexto);
        $datos = $resp === false ? null : json_decode($resp, true);
        return is_array($datos) ? $datos : null;
    }

    private function json(array $datos, int $codigo = 200): never
    {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
