<?php
require_once __DIR__ . '/../../Core/Flash.php';



$flashMessages = Flash::getAll();

$filtered = [];
$onlyTypes = $only ?? null; 
foreach ($flashMessages as $fm) {
    $type = $fm['type'] ?? 'info';
    if ($type === 'success_quiet') { continue; } 
    if (is_array($onlyTypes) && !in_array($type, $onlyTypes, true)) { continue; }
    $filtered[] = $fm;
}
if (!empty($filtered)): ?>
    <div class="flash-container" aria-live="polite" style="margin:10px 15px;display:flex;flex-direction:column;gap:6px;">
    <?php foreach ($filtered as $fm):
        $type = $fm['type'] ?? 'info';
        $msg = $fm['message'] ?? '';
        $class = 'flash-msg';
        switch ($type) {
            case 'success': $class .= ' flash-success'; break;
            case 'error': $class .= ' flash-error'; break;
            case 'warning': $class .= ' flash-warning'; break;
            case 'info': default: $class .= ' flash-info'; break;
        }
    ?>
        <div class="<?= htmlspecialchars($class) ?>" role="alert" style="padding:8px 12px;border-radius:6px;font-size:14px;line-height:1.4;" data-flash-ts="<?= (int)($fm['time'] ?? time()) ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endforeach; ?>
    </div>
    <style>
        .flash-success{background:#1e5631;color:#eaffea;border:1px solid #2e7d43}
        .flash-error{background:#641e1e;color:#ffecec;border:1px solid #8a2f2f}
        .flash-warning{background:#5a4b12;color:#fff5d6;border:1px solid #8f7a26}
        .flash-info{background:#1e3d5a;color:#e6f4ff;border:1px solid #2d5f8f}
        .flash-container .flash-msg{animation:flashFade .4s ease-in}
        @keyframes flashFade{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
        </style>
        <script>
        // Auto-hide de mensajes flash (10-15s con dispersión y fade)
        (function(){
            const nodes = document.querySelectorAll('.flash-container .flash-msg');
            const now = Date.now();
            nodes.forEach((n,i)=>{
                const created = parseInt(n.getAttribute('data-flash-ts')|| (now/1000),10)*1000;
                // ventana base 11000ms a 15000ms con leve variación por índice
            const base = 10000 + (i*700);
            const max = 15000;
                const ttl = Math.min(base, max);
                setTimeout(()=>{
                    n.style.transition='opacity .6s ease, transform .6s ease';
                    n.style.opacity='0';
                    n.style.transform='translateY(-4px)';
                    setTimeout(()=>{ if(n.parentNode){ n.parentNode.removeChild(n);} },650);
                }, ttl);
            });
        })();
        </script>
<?php endif; ?>