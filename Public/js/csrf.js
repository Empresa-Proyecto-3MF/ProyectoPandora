// CSRF helper: toma el token del meta y lo adjunta a peticiones fetch POST automáticamente
(function(){
  const META_NAME = 'csrf-token';
  const TOKEN_FIELD = '_csrf';

  function getMetaToken(){
    const meta = document.querySelector('meta[name="' + META_NAME + '"]');
    return meta ? meta.getAttribute('content') : null;
  }

  let currentToken = getMetaToken();

  // Si el backend nos devuelve un nuevo token (por ejemplo en un 403 con data-new-csrf), podríamos actualizarlo aquí.
  function setToken(newTk){ if (typeof newTk === 'string' && newTk.length > 10) { currentToken = newTk; } }
  window.__setCsrfToken = setToken;
  window.__getCsrfToken = function(){ return currentToken; };

  // Wrapper para fetch que inyecta el token
  const originalFetch = window.fetch;
  window.fetch = function(input, init){
    try {
      const method = (init && init.method ? init.method : 'GET').toUpperCase();
      if (method === 'POST' || method === 'PUT' || method === 'PATCH' || method === 'DELETE') {
        const tk = currentToken;
        if (tk) {
          if (init && init.body instanceof FormData) {
            // Agregar campo sólo si no existe otro _csrf
            if (!init.body.has(TOKEN_FIELD)) {
              init.body.append(TOKEN_FIELD, tk);
            }
          } else if (init && typeof init.body === 'string' && init.body.length) {
            // Si es application/x-www-form-urlencoded
            if (!init.headers || !('Content-Type' in init.headers) || /application\/x-www-form-urlencoded/i.test(String(init.headers['Content-Type']||''))) {
              const prefix = init.body.length ? '&' : '';
              init.body += prefix + encodeURIComponent(TOKEN_FIELD) + '=' + encodeURIComponent(tk);
            }
          } else {
            // No hay body: crear uno para enviar el token (solo para POST). Usamos FormData.
            const fd = new FormData();
            fd.append(TOKEN_FIELD, tk);
            init = Object.assign({}, init, { body: fd });
          }
        }
      }
    } catch(err){ /* silencioso */ }
    return originalFetch(input, init).then(async resp => {
      try {
        // Intentar extraer nuevo token si backend lo provee en un div data-new-csrf dentro de HTML
        const ct = resp.headers.get('content-type') || '';
        if (ct.includes('text/html')) {
          const text = await resp.clone().text();
          const m = text.match(/data-new-csrf="([a-f0-9]{20,})"/i);
          if (m) { setToken(m[1]); }
        } else if (ct.includes('application/json')) {
          // Para JSON podríamos buscar propiedad new_csrf en el clon
          const data = await resp.clone().json();
          if (data && typeof data.new_csrf === 'string') { setToken(data.new_csrf); }
        }
      } catch(e){ /* ignorar */ }
      return resp;
    });
  };
})();
