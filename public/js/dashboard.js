
(() => {
  const contenedor = document.getElementById('grafica-dias');
  if (!contenedor) return;

  const dias = JSON.parse(contenedor.dataset.dias || '[]');
  const maximo = Math.max(...dias.map((d) => d.total), 0);

  const completo = (n) => '$' + Math.round(n).toLocaleString('es-CO');
  const compacto = (n) =>
    '$' + new Intl.NumberFormat('es-CO', { notation: 'compact', maximumFractionDigits: 1 }).format(n);

  dias.forEach((d) => {
    const col = document.createElement('div');
    col.className = 'col' + (d.hoy ? ' hoy' : '');

    const valor = document.createElement('span');
    valor.className = 'col-valor';
    valor.textContent = d.total > 0 ? compacto(d.total) : '';

    const area = document.createElement('div');
    area.className = 'col-area';

    const barra = document.createElement('div');
    barra.className = 'col-barra';
    barra.style.setProperty('--h', (maximo ? (d.total / maximo) * 100 : 0) + '%');
    barra.title = d.etiqueta + ': ' + completo(d.total);
    area.appendChild(barra);

    const etiqueta = document.createElement('span');
    etiqueta.className = 'col-dia';
    etiqueta.textContent = d.hoy ? 'hoy' : d.etiqueta;

    col.append(valor, area, etiqueta);
    contenedor.appendChild(col);
  });
})();
