<?php
// Chat flotante de BolsiBot: aparece en todas las páginas, pero solo con la sesión iniciada.
if (empty($_SESSION['usuario_id'])) {
    return;
}
$_SESSION['bot_token'] ??= bin2hex(random_bytes(8));
?>
  <link rel="stylesheet" href="<?= e(asset('css/asistente.css')) ?>">

  <!-- Cambiamos class="bot" por class="bolsibot" para evitar que choque con .burbuja.bot -->
  <div class="bolsibot" id="bot"
       data-url="<?= e(url('asistente/mensaje')) ?>"
       data-reiniciar="<?= e(url('asistente/reiniciar')) ?>"
       data-estado="<?= e(url('asistente/estado')) ?>"
       data-csrf="<?= e(csrf_token()) ?>"
       data-token="<?= e($_SESSION['bot_token']) ?>">

    <section class="bot-panel" id="bot-panel" aria-label="Asistente BolsiBot" hidden>
      <header class="bot-cabecera">
        <span class="icono" data-icon="bot"></span>
        <strong>BolsiBot</strong>
        <button type="button" class="bot-reiniciar" id="bot-reiniciar" title="Empezar una conversación nueva">↻ Reiniciar</button>
        <button type="button" class="bot-cerrar" id="bot-cerrar" aria-label="Cerrar chat">&times;</button>
      </header>

      <div class="bot-mensajes" id="bot-mensajes" aria-live="polite"></div>

      <form class="bot-form" id="bot-form" autocomplete="off">
        <input type="text" id="bot-texto" maxlength="500" placeholder="Escribe aquí..." required>
        <button type="submit">Enviar</button>
      </form>
    </section>

    <!-- Globo flotante para llamar la atención sin ocupar espacio -->
    <div class="bot-saludo" id="bot-saludo" hidden>
      <span id="bot-saludo-texto">👋 ¿Registramos un gasto o revisamos tu presupuesto?</span>
      <button type="button" class="bot-saludo-cerrar" id="bot-saludo-cerrar" aria-label="Cerrar saludo">&times;</button>
    </div>

    <!-- Botón redondo compacto ("bolita") con indicador de estado verde/rojo -->
    <button type="button" class="bot-boton" id="bot-boton" aria-label="Abrir asistente BolsiBot" aria-expanded="false">
      <span class="icono" data-icon="bot"></span>
      <span class="bot-estado verificando" id="bot-estado" title="Verificando..." aria-hidden="true"></span>
    </button>

  </div>
  <script src="<?= e(asset('js/asistente.js')) ?>"></script>
