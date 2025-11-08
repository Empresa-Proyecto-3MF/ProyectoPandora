(function(){
  const container = document.querySelector('.bloque-tecnico');
  const ticketIdEl = document.querySelector('input[name="ticket_id"]');
  if (!container || !ticketIdEl) return;

  const ticketId = parseInt(ticketIdEl.value || '0', 10);
  if (!ticketId) return;

  const laborForm = document.querySelector('form.presu-labor');
  const editRepuestosLinks = Array.from(document.querySelectorAll('a[href*="Tecnico/MisRepuestos"]'));

  const fields = {
    estado: document.querySelector('[data-field="estado-label"]'),
    cliente: document.querySelector('[data-field="cliente-nombre"]'),
    device: document.querySelector('[data-field="device-nombre"]'),
    tecnico: document.querySelector('[data-field="tecnico-nombre"]'),
    descripcion: document.querySelector('[data-field="descripcion-falla"]'),
    fechaCreacion: document.querySelector('[data-field="fecha-creacion"]'),
    fechaCierreRow: document.querySelector('[data-field="fecha-cierre-row"]'),
    fechaCierre: document.querySelector('[data-field="fecha-cierre"]'),
    imageCard: document.querySelector('[data-field="device-image-card"]'),
    image: document.querySelector('[data-field="device-image"]'),
  };

  const timelineLists = {
    Tecnico: document.querySelector('[data-timeline="Tecnico"]'),
    Cliente: document.querySelector('[data-timeline="Cliente"]'),
    Supervisor: document.querySelector('[data-timeline="Supervisor"]'),
  };

  const getLocalRev = () => {
    const revEl = document.querySelector('input[name="rev_state"]');
    return revEl ? String(revEl.value) : '';
  };

  const setRevInputs = (rev) => {
    if (!rev && rev !== '') return;
    document.querySelectorAll('input[name="rev_state"]').forEach(el => { el.value = rev; });
  };

  const updateRepuestosLinks = (rev) => {
    if (!rev && rev !== '') return;
    editRepuestosLinks.forEach(link => {
      try {
        const url = new URL(link.getAttribute('href'), window.location.origin);
        url.searchParams.set('rev', rev);
        link.setAttribute('href', url.pathname + url.search + url.hash);
      } catch (err) {
        // ignore malformed URLs
      }
    });
  };

  const disableEditing = (reason) => {
    if (laborForm) {
      const btn = laborForm.querySelector('button[type="submit"]');
      if (btn) {
        btn.setAttribute('disabled', 'true');
        btn.textContent = 'Bloqueado';
      }
      laborForm.querySelectorAll('input,button,select,textarea').forEach(el => {
        el.setAttribute('disabled', 'true');
      });
    }
    editRepuestosLinks.forEach(link => {
      link.setAttribute('aria-disabled', 'true');
      link.classList.add('btn--disabled');
      if (!link.dataset.syncLocked) {
        link.dataset.syncLocked = '1';
        link.addEventListener('click', (e) => e.preventDefault());
      }
    });

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

  const renderTimeline = (role, events) => {
    const list = timelineLists[role];
    if (!list) return;
    list.innerHTML = '';
    const items = Array.isArray(events) ? events : [];
    if (items.length === 0) {
      const empty = document.createElement('li');
      empty.className = 'timeline-empty';
      empty.textContent = 'Sin registros';
      list.appendChild(empty);
      return;
    }
    items.forEach(ev => {
      const li = document.createElement('li');

      const fechaDiv = document.createElement('div');
      fechaDiv.className = 'timeline-fecha';
      const time = document.createElement('time');
      if (ev && ev.exact) time.title = ev.exact;
      time.textContent = ev && ev.human ? ev.human : '';
      fechaDiv.appendChild(time);
      li.appendChild(fechaDiv);

      const estadoDiv = document.createElement('div');
      estadoDiv.textContent = 'Estado: ';
      const badge = document.createElement('span');
      badge.className = ev && ev.badge_class ? ev.badge_class : 'badge';
      badge.textContent = ev && ev.estado ? ev.estado : '';
      estadoDiv.appendChild(badge);
      li.appendChild(estadoDiv);

      if (ev && ev.comentario) {
        const comentarioDiv = document.createElement('div');
        comentarioDiv.textContent = `"${ev.comentario}"`;
        li.appendChild(comentarioDiv);
      }

      list.appendChild(li);
    });
  };

  const updateImage = (imagePayload) => {
    const card = fields.imageCard;
    const img = fields.image;
    if (!card || !img) return;

    const url = imagePayload && imagePayload.url ? imagePayload.url : '';
    if (url) {
      card.style.display = '';
      if (img.src !== url) {
        img.src = url;
      }
      const lightbox = document.querySelector('.lightbox');
      if (lightbox) {
        lightbox.style.display = '';
        const lbImg = lightbox.querySelector('img');
        if (lbImg) lbImg.src = url;
      }
    } else {
      card.style.display = 'none';
      img.src = '';
      const lightbox = document.querySelector('.lightbox');
      if (lightbox) {
        lightbox.classList.remove('active');
        lightbox.style.display = 'none';
      }
    }
  };

  const updateDetail = (detail) => {
    if (!detail) return;

    if (detail.estado && fields.estado) {
      fields.estado.textContent = detail.estado.label || '';
      if (detail.estado.badge_class) {
        fields.estado.className = detail.estado.badge_class;
      }
    }

    if (fields.cliente) {
      fields.cliente.textContent = detail.cliente || 'No disponible';
    }

    if (fields.device) {
      fields.device.textContent = detail.device || '';
    }

    if (fields.tecnico) {
      fields.tecnico.innerHTML = '';
      const nombre = detail.tecnico && detail.tecnico.name ? detail.tecnico.name : '';
      if (nombre) {
        fields.tecnico.textContent = nombre;
      } else {
        const span = document.createElement('span');
        span.className = 'sin-asignar';
        span.textContent = 'Sin asignar';
        fields.tecnico.appendChild(span);
      }
    }

    if (fields.descripcion) {
      fields.descripcion.textContent = detail.descripcion || '';
    }

    if (detail.fechas) {
      if (fields.fechaCreacion && detail.fechas.creacion) {
        const { human, exact } = detail.fechas.creacion;
        fields.fechaCreacion.textContent = human || '';
        if (typeof exact === 'string') {
          fields.fechaCreacion.dataset.exact = exact;
          fields.fechaCreacion.title = exact;
        }
      }
      if (fields.fechaCierreRow && fields.fechaCierre) {
        const cierre = detail.fechas.cierre || {};
        if (cierre.human) {
          fields.fechaCierreRow.style.display = '';
          fields.fechaCierre.textContent = cierre.human;
          if (typeof cierre.exact === 'string') {
            fields.fechaCierre.dataset.exact = cierre.exact;
            fields.fechaCierre.title = cierre.exact;
          }
        } else {
          fields.fechaCierreRow.style.display = 'none';
          fields.fechaCierre.textContent = '';
          fields.fechaCierre.removeAttribute('data-exact');
          fields.fechaCierre.removeAttribute('title');
        }
      }
    }

    updateImage(detail.image);
  };

  const applyResponse = (data) => {
    if (!data || typeof data !== 'object') return;

    if (data.detail) {
      updateDetail(data.detail);
      if (data.detail.rev) {
        setRevInputs(data.detail.rev);
        updateRepuestosLinks(data.detail.rev);
      }
    } else if (data.rev) {
      setRevInputs(data.rev);
      updateRepuestosLinks(data.rev);
    }

    if (data.timeline) {
      renderTimeline('Tecnico', data.timeline.Tecnico);
      renderTimeline('Cliente', data.timeline.Cliente);
      renderTimeline('Supervisor', data.timeline.Supervisor);
    }

    if (data.published === true) {
      disableEditing('Presupuesto publicado: edición deshabilitada.');
    } else if (data.canEdit === false) {
      disableEditing('Edición bloqueada.');
    }
  };

  const poll = async () => {
    try {
      const rev = getLocalRev();
      const url = `/ProyectoPandora/Public/index.php?route=Ticket/SyncStatus&ticket_id=${ticketId}${rev ? `&rev=${encodeURIComponent(rev)}` : ''}`;
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
        credentials: 'same-origin',
      });
      if (!res.ok) return;
  const data = await res.json();
  applyResponse(data);
    } catch (e) {
      // Silently ignore network errors
    }
  };

  setInterval(poll, 10000);
  poll();
})();
