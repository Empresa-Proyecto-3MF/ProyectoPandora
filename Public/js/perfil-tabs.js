// Perfil tabs switching
(function(){
  // Tabs de perfil
  const tabs = document.querySelectorAll('.perfil-tabs .tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.perfil-tabs .tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.perfil-content').forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      const target = document.getElementById(tab.dataset.tab);
      if (target) target.classList.add('active');
    });
  });

  // Auto-submit del avatar al seleccionar archivo
  const avatarInput = document.getElementById('avatarUpload');
  if (avatarInput) {
    avatarInput.addEventListener('change', function(){
      const form = avatarInput.closest('form');
      if (form) form.submit();
    });
  }
})();
