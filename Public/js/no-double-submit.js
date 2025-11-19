document.addEventListener('DOMContentLoaded', () => {
  const guardAttr = 'data-submitted-once';
  document.body.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute(guardAttr)) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
    form.setAttribute(guardAttr, '1');
    // Deshabilitar botones submit del formulario
    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach(btn => {
      btn.disabled = true;
      const t = btn.getAttribute('data-busy-text');
      if (t) btn.setAttribute('data-original-text', btn.textContent || '');
      if (t) btn.textContent = t;
    });
    // Rehabilitar al abandonar/redirect (no podemos saberlo aquí). Si hace back, se re-crea DOM y se limpia.
  }, true);
});
