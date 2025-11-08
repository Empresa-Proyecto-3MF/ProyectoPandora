(function(){
  const badge = document.getElementById('estado-badge');
  if (!badge) return;
  const classFor = (txt)=>{
    const t = (txt||'').toLowerCase();
    if (["finalizado","cerrado","cancelado"].includes(t)) return 'badge badge--muted';
    if (["presupuesto","en espera","pendiente"].includes(t)) return 'badge badge--warning';
    if (["en reparación","diagnóstico","diagnostico","reparando"].includes(t)) return 'badge badge--info';
    return 'badge badge--success';
  };
  badge.className = classFor(badge.textContent);
})();

(function(){
  function attachLightbox(img){
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `<img src="${img.src}">`;
    document.body.appendChild(lightbox);
    img.addEventListener('click', () => lightbox.classList.add('active'));
    lightbox.addEventListener('click', () => lightbox.classList.remove('active'));
  }
  const img = document.querySelector('.imagen-dispositivo');
  if (img) attachLightbox(img);
})();