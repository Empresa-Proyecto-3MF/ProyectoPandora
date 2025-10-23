// Acciones en Ticket/Ver: interceptar formularios y manejar redirecciones
(function(){
  function shouldIntercept(form){
    // Interceptamos acciones inline y de cambio de estado/labor/items, típicamente con clase 'inline-form'
    if (form.classList.contains('inline-form')) return true;
    // También podemos detectar rutas conocidas
    const action = form.action || '';
    return /(Ticket\/CambiarEstado|Ticket\/PublicarPresupuesto|Ticket\/AprobarPresupuesto|Ticket\/RechazarPresupuesto|Ticket\/DiagnosticoFinalizado)/.test(action);
  }

  function onFormSubmit(e){
    const form = e.target.closest('form');
    if (!form) return;
    if (!shouldIntercept(form)) return;
    e.preventDefault();

    const formData = new FormData(form);
    const method = (form.method || 'POST').toUpperCase();
    const url = form.action || window.location.href;

    fetch(url, {
      method,
      body: method === 'GET' ? null : formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(async (resp) => {
      if (resp.redirected) { window.location.href = resp.url; return; }
      if (!resp.ok) throw new Error('Error en acción del ticket');
      // Si devuelve HTML parcial o mensaje, intentamos actualizar zonas conocidas
      const ct = resp.headers.get('content-type') || '';
      if (ct.includes('text/html')) {
        const html = await resp.text();
        // Intentar reemplazar contenedores comunes
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const ids = ['ticket-acciones', 'ticket-timeline', 'ticket-alertas'];
        let updated = false;
        ids.forEach(id => {
          const newEl = doc.getElementById(id);
          const oldEl = document.getElementById(id);
          if (newEl && oldEl) { oldEl.innerHTML = newEl.innerHTML; updated = true; }
        });
        if (!updated) {
          // Fallback: recargar para consistencia
          window.location.reload();
        }
      } else {
        // Respuesta no HTML: recargar
        window.location.reload();
      }
    }).catch(() => window.location.reload());
  }

  document.addEventListener('submit', onFormSubmit);
})();
