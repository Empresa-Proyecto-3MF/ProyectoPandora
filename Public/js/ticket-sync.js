(function(){
  // Auto-initialize only on Ticket/Ver pages where data-sync endpoint is available
  const container = document.querySelector('.bloque-tecnico');
  const ticketIdEl = document.querySelector('input[name="ticket_id"]');
  if (!container || !ticketIdEl) return;

  const ticketId = parseInt(ticketIdEl.value || '0', 10);
  if (!ticketId) return;

  const laborForm = document.querySelector('form.presu-labor');
  const editRepuestosBtn = document.querySelector('a[href*="Tecnico/MisRepuestos"]');

  // compute current rev from hidden input if present
  const getLocalRev = () => {
    const revEl = document.querySelector('input[name="rev_state"]');
    return revEl ? String(revEl.value) : null;
  };

  const disableEditing = (reason) => {
    if (laborForm) {
      const btn = laborForm.querySelector('button[type="submit"]');
      if (btn) { btn.setAttribute('disabled', 'true'); btn.textContent = 'Bloqueado'; }
      const inputs = laborForm.querySelectorAll('input,button,select,textarea');
      inputs.forEach(el => el.setAttribute('disabled','true'));
    }
    if (editRepuestosBtn) {
      editRepuestosBtn.setAttribute('aria-disabled','true');
      editRepuestosBtn.classList.add('btn--disabled');
      editRepuestosBtn.addEventListener('click', (e)=>{ e.preventDefault(); });
    }
    // Show an info banner once
    let banner = document.getElementById('sync-published-banner');
    if (!banner) {
      banner = document.createElement('div');
      banner.id = 'sync-published-banner';
      banner.className = 'alert alert-info';
      banner.style.marginTop = '10px';
      banner.textContent = reason || 'Presupuesto publicado: edición deshabilitada.';
      container.insertBefore(banner, container.firstChild);
    }
  };

  const poll = async () => {
    try {
      const rev = getLocalRev();
      const url = `/ProyectoPandora/Public/index.php?route=Ticket/SyncStatus&ticket_id=${ticketId}${rev?`&rev=${encodeURIComponent(rev)}`:''}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      // If published, force-disable editing
      if (data.published === true) {
        disableEditing('Presupuesto publicado: edición deshabilitada.');
        return;
      }
      // If rev changed server-side, update hidden rev and, if server says no edit, disable
      if (data.rev && data.rev !== rev) {
        const revEl = document.querySelector('input[name="rev_state"]');
        if (revEl) revEl.value = data.rev;
      }
      if (data.canEdit === false) {
        disableEditing('Edición bloqueada.');
      }
    } catch (e) {
      // ignore network errors
    }
  };

  setInterval(poll, 10000); // 10s
  // initial
  poll();
})();
