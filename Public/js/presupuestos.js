// Supervisor/Presupuestos: interceptar publicación y cambios
(function(){
  function isPresupuestoAction(form){
    const act = form.action || '';
    return /(Ticket\/PublicarPresupuesto|Ticket\/AprobarPresupuesto|Ticket\/RechazarPresupuesto)/.test(act);
  }

  function onSubmit(e){
    const form = e.target.closest('form');
    if (!form) return;
    if (!isPresupuestoAction(form)) return;
    e.preventDefault();

    const method = (form.method || 'POST').toUpperCase();
    const url = form.action || window.location.href;
    const fd = new FormData(form);

    fetch(url, { method, body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(async (resp) => {
        if (resp.redirected) { window.location.href = resp.url; return; }
        if (!resp.ok) throw new Error('Error HTTP');
        // Intentar actualizar solo la tarjeta del ticket afectado
        const ct = resp.headers.get('content-type') || '';
        if (ct.includes('text/html')) {
          const html = await resp.text();
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const listSel = '.cards-container, #cards-container, .lista-container, #lista-container, .presu-list';
          const newList = doc.querySelector(listSel);
          const oldList = document.querySelector(listSel);
          if (newList && oldList) { oldList.innerHTML = newList.innerHTML; return; }
        }
        // Fallback
        window.location.reload();
      })
      .catch(() => window.location.reload());
  }

  document.addEventListener('submit', onSubmit);
})();
