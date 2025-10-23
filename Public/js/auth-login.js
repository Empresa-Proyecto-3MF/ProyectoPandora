(function(){
  // Attach only on login page where the form exists
  const form = document.getElementById('loginForm');
  if (!form) return;

  const respuesta = document.getElementById('respuesta');
  function setMsg(html){ if (respuesta) respuesta.innerHTML = html; }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    const url = form.getAttribute('action') || '/ProyectoPandora/Public/index.php?route=Auth/Login';
    const fd = new FormData(form);

    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(resp => {
        // Si el backend redirige (302/303/307), fetch sigue la redirección pero no navega.
        // Usamos resp.redirected para forzar la navegación.
        if (resp.redirected && resp.url) {
          window.location.href = resp.url; return null;
        }
        const ct = resp.headers.get('content-type') || '';
        if (ct.includes('application/json')) return resp.json();
        return resp.text();
      })
      .then(data => {
        if (data == null) return; // ya redirigimos
        if (typeof data === 'object') {
          if (data.ok && data.redirect) { window.location.href = data.redirect; return; }
          if (data.ok) { window.location.reload(); return; }
          setMsg(data.message ? String(data.message) : 'Credenciales inválidas.');
        } else {
          // Backend devolvió HTML plano; lo mostramos como feedback
          setMsg(String(data));
        }
      })
      .catch(err => setMsg('Error: ' + err));
  });
})();
