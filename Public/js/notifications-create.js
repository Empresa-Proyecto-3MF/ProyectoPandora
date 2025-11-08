// Notifications Create view logic
(function(){
  const sel = document.getElementById('audienceSelect');
  const roleF = document.getElementById('roleField');
  const userF = document.getElementById('userField');

  function upd(){
    if(!sel || !roleF || !userF) return;
    const v = sel.value;
    roleF.style.display = (v==='ROLE') ? 'block' : 'none';
    userF.style.display = (v==='USER') ? 'block' : 'none';
  }

  if (sel) {
    sel.addEventListener('change', upd);
    // Initial state
    upd();
  }

  // Expose for inline oninput if present
  window.filterUsers = function(){
    const searchEl = document.getElementById('userSearch');
    const select = document.getElementById('userSelect');
    if(!searchEl || !select) return;
    const q = (searchEl.value || '').toLowerCase();
    for (const opt of select.options) {
      const txt = opt.textContent.toLowerCase();
      opt.hidden = q && !txt.includes(q);
    }
  };

  // Also attach event listener for robustness when attribute isn't present
  const searchEl = document.getElementById('userSearch');
  if (searchEl) {
    searchEl.addEventListener('input', window.filterUsers);
  }
})();
