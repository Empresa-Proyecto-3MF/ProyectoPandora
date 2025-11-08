// Modal y validaciones para formularios de registro (público y admin)
(function(){
  var overlay = document.getElementById('appValidationModal');
  var msgEl = overlay ? document.getElementById('appModalMsg') : null;
  var okBtn = overlay ? document.getElementById('appModalOkBtn') : null;
  var focusAfterClose = null;

  function fallbackAlert(message, focusEl){
    alert(message || 'Completá los campos requeridos.');
    if (focusEl && typeof focusEl.focus === 'function') focusEl.focus();
  }

  if (overlay && msgEl && okBtn) {
    window.showValidationDialog = function(message, focusEl){
      msgEl.textContent = message || 'Completá los campos requeridos.';
      focusAfterClose = focusEl || null;
      overlay.style.display = 'flex';
      okBtn.focus();
    };
    function closeDialog(){
      overlay.style.display = 'none';
      if (focusAfterClose && typeof focusAfterClose.focus === 'function'){
        focusAfterClose.focus();
      }
    }
    okBtn.addEventListener('click', closeDialog);
    overlay.addEventListener('click', function(e){ if(e.target === overlay) closeDialog(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && overlay.style.display === 'flex') closeDialog(); });
  } else {
    // Si no existe el modal en el DOM, usar alert como fallback
    window.showValidationDialog = fallbackAlert;
  }

  // Validación compartida de email
  function isValidEmail(val){
    var re = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    return re.test(String(val || '').trim().toLowerCase());
  }

  window.validarEmailRegistro = function(form){
    var nameInput = form.querySelector('input[name="name"]');
    var emailInput = form.querySelector('input[name="email"]');
    var passInput = form.querySelector('input[name="password"]');
    if (!nameInput || (nameInput.value || '').trim() === '') {
      showValidationDialog('El nombre es obligatorio.', nameInput);
      return false;
    }
    var val = emailInput ? (emailInput.value || '').trim().toLowerCase() : '';
    if (!isValidEmail(val)) {
      showValidationDialog('Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)', emailInput);
      return false;
    }
    if (!passInput || (passInput.value || '').length < 8) {
      showValidationDialog('La contraseña debe tener al menos 8 caracteres.', passInput);
      return false;
    }
    return true;
  };

  window.validarEmailRegistroAdmin = function(form){
    var nameInput = form.querySelector('input[name="name"]');
    var emailInput = form.querySelector('input[name="email"]');
    var passInput = form.querySelector('input[name="password"]');
    if (!nameInput || (nameInput.value || '').trim() === '') {
      showValidationDialog('El nombre es obligatorio.', nameInput);
      return false;
    }
    var val = emailInput ? (emailInput.value || '').trim().toLowerCase() : '';
    if (!isValidEmail(val)) {
      showValidationDialog('Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)', emailInput);
      return false;
    }
    if (!passInput || (passInput.value || '').length < 8) {
      showValidationDialog('La contraseña debe tener al menos 8 caracteres.', passInput);
      return false;
    }
    return true;
  };
  
  // Auto-bind submit handlers so views no longer need inline onsubmit
  function attachValidation(form, fn){
    form.addEventListener('submit', function(e){
      try {
        if (!fn(form)) {
          e.preventDefault();
          e.stopPropagation();
        }
      } catch(err){
        // En caso de error inesperado, previene submit para no enviar datos inconsistentes
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  // Registrar listeners para formularios de registro público y admin
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('form[action*="route=Register/Register"]').forEach(function(f){ attachValidation(f, window.validarEmailRegistro); });
    document.querySelectorAll('form[action*="route=Register/RegisterAdmin"]').forEach(function(f){ attachValidation(f, window.validarEmailRegistroAdmin); });
  });
})();
