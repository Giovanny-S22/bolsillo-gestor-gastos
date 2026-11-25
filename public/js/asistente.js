// Chat flotante de BolsiBot. Todo se pinta con textContent (nunca innerHTML) para evitar XSS.
// El historial se guarda en localStorage con una clave que incluye el token de la sesión:
// al iniciar sesión de nuevo el token cambia y el chat empieza limpio.
(function () {
  const raiz = document.getElementById('bot');
  if (!raiz) return;

  const panel = document.getElementById('bot-panel');
  const lista = document.getElementById('bot-mensajes');
  const form = document.getElementById('bot-form');
  const campo = document.getElementById('bot-texto');
  const enviarBtn = form.querySelector('button');
  const abrirBtn = document.getElementById('bot-boton');
  const cerrarBtn = document.getElementById('bot-cerrar');
  const reiniciarBtn = document.getElementById('bot-reiniciar');
  const saludoEl = document.getElementById('bot-saludo');
  const cerrarSaludoBtn = document.getElementById('bot-saludo-cerrar');
  const estadoEl = document.getElementById('bot-estado');
  const saludoTextoEl = document.getElementById('bot-saludo-texto');

  const MAX_MENSAJES = 100;
  const SALUDO = '¡Hola! Soy BolsiBot. Escribe "ayuda" para ver lo que puedo hacer.';
  const SALUDO_MS = 20000;         // el globo de bienvenida dura 20 segundos
  const DESPUES_HOVER_MS = 4000;   // al quitar el mouse, el globo sigue 5 segundos más
  const REVISAR_ESTADO_MS = 10000; // cada cuánto se revisa si Rasa sigue encendido
  const TEXTO_SALUDO = saludoTextoEl ? saludoTextoEl.textContent : '';  // se edita en asistente.php
  const TEXTO_HOVER = '👋 Hola, soy BolsiBot. ¿En qué puedo ayudarte?';
  const TEXTO_INACTIVO = '😴 BolsiBot está desconectado en este momento';
  let temporizadorSaludo = null;
  let modoGlobo = 'saludo';   // 'saludo' (al cargar) o 'hover' (al pasar el mouse)
  let activo = null;          // null = todavía no se sabe si Rasa responde
  let token = raiz.dataset.token;
  let estado = { abierto: false, mensajes: [] };

  const clave = () => 'bolsibot_' + token;

  // Bolita pequeña: gris = verificando, verde = Rasa responde, roja = no responde
  function setEstadoActivo(valor) {
    activo = valor;
    if (!estadoEl) return;
    estadoEl.classList.remove('verificando');
    estadoEl.classList.toggle('inactivo', !valor);
    estadoEl.title = valor ? 'BolsiBot activo' : 'BolsiBot sin conexión';
    if (saludoEl && !saludoEl.hidden) saludoTextoEl.textContent = textoGlobo(); // actualiza el globo visible
  }

  function textoGlobo() {
    if (activo === false) return TEXTO_INACTIVO;
    return modoGlobo === 'hover' ? TEXTO_HOVER : TEXTO_SALUDO;
  }

  // Le pregunta a PHP si Rasa está encendido (PHP hace un ping a Rasa)
  async function verificarEstado() {
    try {
      const resp = await fetch(raiz.dataset.estado, { cache: 'no-store' });
      const datos = await resp.json();
      setEstadoActivo(resp.ok && datos.activo === true);
    } catch (e) {
      setEstadoActivo(false); // ni siquiera responde el servidor de Bolsillo
    }
  }

  // Muestra el globo (solo si el chat está cerrado). ms = 0 => se queda hasta que algo lo oculte.
  function mostrarGlobo(modo, ms) {
    if (!saludoEl || !panel.hidden) return;
    modoGlobo = modo;
    saludoTextoEl.textContent = textoGlobo();
    saludoEl.hidden = false;
    programarOcultar(ms);
  }

  function programarOcultar(ms) {
    clearTimeout(temporizadorSaludo);
    if (ms > 0) temporizadorSaludo = setTimeout(ocultarSaludo, ms);
  }

  function ocultarSaludo() {
    if (!saludoEl) return;
    clearTimeout(temporizadorSaludo);
    saludoEl.hidden = true;
  }

  function guardar() {
    try { localStorage.setItem(clave(), JSON.stringify(estado)); } catch (e) { /* sin almacenamiento */ }
  }

  function cargar() {
    try {
      for (let i = localStorage.length - 1; i >= 0; i--) {
        const k = localStorage.key(i);
        if (k && k.startsWith('bolsibot_') && k !== clave()) localStorage.removeItem(k);
      }
      const guardado = JSON.parse(localStorage.getItem(clave()));
      if (guardado && Array.isArray(guardado.mensajes)) estado = guardado;
    } catch (e) { /* se usa el estado vacío */ }
  }

  function pintar(texto, clase) {
    const div = document.createElement('div');
    div.className = 'burbuja ' + clase;
    div.textContent = texto;
    lista.appendChild(div);
    lista.scrollTop = lista.scrollHeight;
  }

  function agregar(texto, clase) {
    pintar(texto, clase);
    estado.mensajes.push({ t: texto, c: clase });
    if (estado.mensajes.length > MAX_MENSAJES) estado.mensajes.shift();
    guardar();
  }

  function mostrar(abierto) {
    estado.abierto = abierto;
    panel.hidden = !abierto;
    abrirBtn.setAttribute('aria-expanded', String(abierto));
    if (abierto) {
      ocultarSaludo();
      lista.scrollTop = lista.scrollHeight;
      campo.focus();
    }
    guardar();
  }

  async function llamar(url, cuerpo) {
    const resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': raiz.dataset.csrf },
      body: JSON.stringify(cuerpo || {}),
    });
    return { ok: resp.ok, datos: await resp.json() };
  }

  async function enviar(texto) {
    enviarBtn.disabled = true;
    try {
      const { ok, datos } = await llamar(raiz.dataset.url, { mensaje: texto });
      if (!ok) {
        agregar(datos.error || 'Ocurrió un error.', 'error');
      } else {
        (datos.mensajes || []).forEach((m) => agregar(m, 'bot'));
      }
      verificarEstado();
    } catch (e) {
      setEstadoActivo(false);
      agregar('No se pudo contactar al servidor.', 'error');
    } finally {
      enviarBtn.disabled = false;
      campo.focus();
    }
  }

  async function reiniciar() {
    try {
      const { ok, datos } = await llamar(raiz.dataset.reiniciar);
      if (!ok) {
        agregar(datos.error || 'No se pudo reiniciar.', 'error');
        return;
      }
      try { localStorage.removeItem(clave()); } catch (e) { /* nada */ }
      token = datos.token;
      estado = { abierto: true, mensajes: [] };
      lista.textContent = '';
      agregar(SALUDO, 'bot');
    } catch (e) {
      setEstadoActivo(false);
      agregar('No se pudo contactar al servidor.', 'error');
    }
  }

  // ----- Inicio -----
  cargar();
  estado.mensajes.forEach((m) => pintar(m.t, m.c));
  if (estado.mensajes.length === 0) agregar(SALUDO, 'bot');

  mostrar(Boolean(estado.abierto));

  // El globo de bienvenida sale en CADA carga, pero solo cuando ya sabemos si Rasa responde
  // (así no aparece primero un texto y luego cambia).
  verificarEstado().then(() => { if (!estado.abierto) mostrarGlobo('saludo', SALUDO_MS); });

  // Globo al pasar el mouse (o enfocar con teclado) por el botón
  abrirBtn.addEventListener('mouseenter', () => mostrarGlobo('hover', 0));
  abrirBtn.addEventListener('focus', () => mostrarGlobo('hover', 0));
  abrirBtn.addEventListener('mouseleave', () => { if (!saludoEl.hidden) programarOcultar(DESPUES_HOVER_MS); });
  abrirBtn.addEventListener('blur', () => { if (!saludoEl.hidden) programarOcultar(DESPUES_HOVER_MS); });
  // Mientras el mouse esté sobre el globo, no se oculta
  saludoEl?.addEventListener('mouseenter', () => clearTimeout(temporizadorSaludo));
  saludoEl?.addEventListener('mouseleave', () => programarOcultar(DESPUES_HOVER_MS));
  setInterval(() => { if (!document.hidden) verificarEstado(); }, REVISAR_ESTADO_MS);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) verificarEstado(); });

  cerrarSaludoBtn?.addEventListener('click', ocultarSaludo);
  abrirBtn.addEventListener('click', () => mostrar(panel.hidden));
  cerrarBtn.addEventListener('click', () => mostrar(false));
  reiniciarBtn.addEventListener('click', reiniciar);
  document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape' && !panel.hidden) mostrar(false); });

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    const texto = campo.value.trim();
    if (!texto) return;
    agregar(texto, 'yo');
    campo.value = '';
    enviar(texto);
  });
})();
