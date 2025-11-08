// Global confirmations for delete/critical actions
(function(){
  function handleAnchor(e){
    const a = e.target.closest('a[data-confirm]');
    if (!a) return;
    const msg = a.getAttribute('data-confirm') || '¿Estás seguro?';
    if (!confirm(msg)) {
      e.preventDefault();
      e.stopPropagation();
    }
  }

  function attachFormConfirm(form){
    form.addEventListener('submit', function(e){
      const msg = form.getAttribute('data-confirm');
      if (!msg) return; // no confirm needed
      if (!confirm(msg)) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  document.addEventListener('click', handleAnchor);

  document.querySelectorAll('form[data-confirm]').forEach(attachFormConfirm);

  // Mutation observer to handle dynamically injected forms/links
  const obs = new MutationObserver(mutations => {
    for (const m of mutations) {
      m.addedNodes.forEach(node => {
        if (!(node instanceof Element)) return;
        if (node.matches('form[data-confirm]')) attachFormConfirm(node);
        node.querySelectorAll && node.querySelectorAll('form[data-confirm]').forEach(attachFormConfirm);
      });
    }
  });
  obs.observe(document.documentElement, { childList: true, subtree: true });
})();
