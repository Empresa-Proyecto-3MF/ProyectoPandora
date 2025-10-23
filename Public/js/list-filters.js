// Interceptar formularios de filtros (GET) y reemplazar contenedores de listas
(function(){
  function isFilterForm(form){
    return form.classList.contains('filtros') || (form.method || 'GET').toUpperCase() === 'GET' && /\b(filter|buscar|estado|cierre|categoria|q)=/i.test(new URLSearchParams(new FormData(form)).toString());
  }

  function formToQuery(form){
    const params = new URLSearchParams();
    new FormData(form).forEach((v, k) => { if (v !== null && v !== undefined) params.append(k, v.toString()); });
    return params.toString();
  }

  function onSubmit(e){
    const form = e.target.closest('form');
    if (!form) return;
    if (!isFilterForm(form)) return;
    e.preventDefault();

    const base = form.action || window.location.pathname + window.location.search;
    const url = base.split('?')[0] + '?' + formToQuery(form);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
      .then(async (resp) => {
        if (resp.redirected) { window.location.href = resp.url; return; }
        if (!resp.ok) throw new Error('Error HTTP en filtros');
        const html = await resp.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        // Reemplazar contenedor principal de tarjetas/listado
        const selectors = ['.cards-container', '#cards-container', '.lista-container', '#lista-container', '.tabla-container', '#tabla-container', '.presu-list'];
        for (const sel of selectors) {
          const newEl = doc.querySelector(sel);
          const oldEl = document.querySelector(sel);
          if (newEl && oldEl) {
            oldEl.innerHTML = newEl.innerHTML;
            return;
          }
        }
        // Si no encontramos contenedor conocido, como fallback recargamos
        window.location.href = url;
      })
      .catch(() => { window.location.href = url; });
  }

  document.addEventListener('submit', onSubmit);
})();
