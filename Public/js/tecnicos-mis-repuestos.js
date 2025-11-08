(function(){
  // Calcula el total dinámico por fila en tablas de repuestos
  document.querySelectorAll('tr').forEach(function(row) {
    const precioEl = row.querySelector('.precio');
    const inputCant = row.querySelector('.js-cantidad');
    const totalEl = row.querySelector('.js-total');
    if (!precioEl || !inputCant || !totalEl) return;

    const precio = parseFloat(precioEl.dataset.precio || '0');

    function update() {
      let cant = parseInt(inputCant.value || '0');
      const max = parseInt(inputCant.getAttribute('max') || '0');
      if (isNaN(cant) || cant < 0) {
        cant = 0;
      } else if (max && cant > max) {
        cant = max;
        inputCant.value = String(max);
      }
      const total = (cant > 0 && !isNaN(precio)) ? (cant * precio) : 0;
      totalEl.textContent = total.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    inputCant.addEventListener('input', update);
    update();
  });
})();