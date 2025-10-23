<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Contenedor-stats">
        <section class="tecnico-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($counters['finalizados'] ?? 0) ?></span>
                    <span class="stat-label">Tickets finalizados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($counters['activos'] ?? 0) ?></span>
                    <span class="stat-label">Tickets activos</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= isset($avg) ? htmlspecialchars(number_format((float)$avg,1,'.','')) : '—' ?></span>
                    <span class="stat-label">Honor promedio (★)</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= (int)($count ?? 0) ?></span>
                    <span class="stat-label">Calificaciones</span>
                </div>
            </div>

   
        </section>
    </div>
</main>

<script>
    let timerInterval=null, elapsed=0, running=false;
    const el=document.getElementById('timer');
    function fmt(s){const h=Math.floor(s/3600), m=Math.floor((s%3600)/60), sec=s%60; return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`}
    function tick(){elapsed++; el.textContent=fmt(elapsed)}
    document.getElementById('startBtn').onclick=()=>{ if(!running){ timerInterval=setInterval(tick,1000); running=true; }}
    document.getElementById('pauseBtn').onclick=()=>{ if(running){ clearInterval(timerInterval); running=false; }}
    document.getElementById('resetBtn').onclick=()=>{ clearInterval(timerInterval); running=false; elapsed=0; el.textContent=fmt(0); }
</script>

