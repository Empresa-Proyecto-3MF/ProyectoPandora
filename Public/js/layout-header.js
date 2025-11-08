(function(){
  // Menu toggle
  const menuBtn = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.sidebar');
  if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => {
      menuBtn.classList.toggle('active');
      sidebar.classList.toggle('active');
    });
  }

  // Bell shake on visible badge
  const badge = document.getElementById('notifBadge');
  const bell = document.getElementById('notifBell');
  if (badge && bell && badge.style.display !== 'none') {
    bell.classList.add('shake');
    setTimeout(() => bell.classList.remove('shake'), 1200);
  }

  // Polling notifications count every 10s
  if (badge) {
    const refresh = () => {
      fetch('/ProyectoPandora/Public/index.php?route=Notification/Count', { cache: 'no-store' })
        .then(r => r.ok ? r.text() : '0')
        .then(txt => {
          const n = parseInt((txt||'0').trim(), 10);
          if (isNaN(n) || n <= 0) {
            badge.style.display = 'none';
            badge.textContent = '0';
          } else {
            badge.style.display = 'inline-block';
            badge.textContent = String(n);
          }
        })
        .catch(() => { /* noop */ });
    };
    refresh();
    setInterval(refresh, 10000);
  }
})();