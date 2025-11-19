(function(){
    function buildUrl(lang, el){
        var prev = el ? el.getAttribute('data-prev-url') : '';
        if (!prev) {
            prev = window.location.pathname + window.location.search;
        }
        return 'index.php?route=Language/Set&lang=' + encodeURIComponent(lang) + '&prev=' + encodeURIComponent(prev);
    }

    function onChange(event){
        var lang = event.target.value;
        if (!lang) { return; }
        window.location.href = buildUrl(lang, event.target);
    }

    function init(){
        var selects = document.querySelectorAll('[data-language-selector]');
        selects.forEach(function(select){
            select.removeEventListener('change', onChange);
            select.addEventListener('change', onChange);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
