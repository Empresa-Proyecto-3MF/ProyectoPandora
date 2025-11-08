(function(){
  const form = document.querySelector('form[action*="route=Ticket/Crear"]');
  const select = document.getElementById('dispositivoSelect');
  const descripcion = document.getElementById('descripcion');
  const clienteSel = document.getElementById('cliente_id');
  const recarga = document.querySelector('input[name="recarga_cliente"]');

  function syncDescripcion(){
    if (!select || !descripcion) return;
    const opt = select.options[select.selectedIndex];
    const val = opt ? opt.getAttribute('data-descripcion') : '';
    descripcion.value = val || '';
  }

  if (select && descripcion) {
    select.addEventListener('change', syncDescripcion);
  }

  // Auto-submit al cambiar cliente (vista admin)
  if (clienteSel && form && recarga) {
    clienteSel.addEventListener('change', function(){
      recarga.value = '1';
      form.submit();
    });
  }

  // En el submit final, limpiar recarga_cliente para no re-cargar cliente
  if (form && recarga) {
    form.addEventListener('submit', function(){
      // Si el submit proviene del click final, limpiar recarga
      recarga.value = '';
    });
  }
})();