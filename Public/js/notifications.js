// Notificaciones: marcar como leídas y actualizar el badge del header
(function(){
  function updateBellCount(){
    const badge = document.getElementById('notifBadge');
    if (!badge) return;
    fetch('/ProyectoPandora/Public/index.php?route=Notification/Count', { cache: 'no-store' })
      .then(r => r.ok ? r.text() : '0')
      .then(txt => {
        const n = parseInt((txt||'0').trim(), 10);
        if (isNaN(n) || n <= 0) {
          badge.style.display = 'none';
          badge.textContent = '0';
        } else {
          badge.style.display = 'inline-block';
          badge.textContent = String(n);
        }
      })
      .catch(() => {});
  }

  function onMarkReadSubmit(e){
    const form = e.target.closest('form');
    if (!form) return;
    // Solo interceptar formularios de marcar leída
    const isMarkRead = form.classList.contains('js-mark-read') || (form.action && form.action.includes('Notification/MarkRead'));
    if (!isMarkRead) return;
    e.preventDefault();

    const li = form.closest('li, .notification-item');
    const formData = new FormData(form);
    // CSRF: si el FormData no trae _csrf desde el formulario, el wrapper fetch lo agrega
    fetch(form.action || form.getAttribute('action') || window.location.href, {
      method: form.method || 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(async (resp) => {
      if (resp.redirected) {
        window.location.href = resp.url; return;
      }
      if (!resp.ok) throw new Error('Error HTTP al marcar como leída');
      // Actualización optimista de UI
      if (li) {
        li.classList.remove('unread');
        li.classList.add('read');
        // Cambiar badge de estado si existe
        const statusBadge = li.querySelector('.badge.estado, .status-badge, .badge');
        if (statusBadge) {
          statusBadge.classList.remove('bg-primary','bg-warning','badge--primary');
          statusBadge.classList.add('bg-secondary','badge--muted');
          statusBadge.textContent = 'Leída';
        }
        // Ocultar/eliminar botón/formulario
        form.remove();
      }
      updateBellCount();
    }).catch(() => {
      // Fallback: si falla, recargar para mantener consistencia
      window.location.reload();
    });
  }

  document.addEventListener('submit', onMarkReadSubmit);
})();
