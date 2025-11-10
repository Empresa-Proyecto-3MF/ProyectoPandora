<?php 
include_once __DIR__ . '/../Includes/Sidebar.php'; 
$role = strtolower($authUser['role'] ?? 'invitado');
?>

<main>
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>

    <div class="home-container">
        
        <section class="home-hero">
            <div class="hero-left">
                <h2><i class='bx bx-grid-alt'></i> <?= __('home.hero.title') ?></h2>
                <p><?= __('home.hero.subtitle') ?></p>
                <div class="home-cta">
                    <?php if ($role === 'invitado'): ?>
                        <a class="btn-a" href="index.php?route=Auth/Login"><i class='bx bx-log-in'></i> <?= __('home.cta.login') ?></a>
                        <a class="btn-b" href="index.php?route=Register/Register"><i class='bx bx-user-plus'></i> <?= __('home.cta.register') ?></a>
                    <?php elseif ($role === 'cliente'): ?>
                        <a class="btn-a" href="index.php?route=Cliente/MisTicketActivo"><i class='bx bx-support'></i> <?= __('home.cta.client.tickets') ?></a>
                        <a class="btn-b" href="index.php?route=Cliente/MisDevice"><i class='bx bx-laptop'></i> <?= __('home.cta.client.devices') ?></a>
                        <a class="btn-c" href="index.php?route=Ticket/Crear"><i class='bx bx-plus-circle'></i> <?= __('home.cta.client.createTicket') ?></a>
                    <?php elseif ($role === 'tecnico'): ?>
                        <a class="btn-a" href="index.php?route=Tecnico/MisReparaciones"><i class='bx bx-wrench'></i> <?= __('home.cta.tech.repairs') ?></a>
                        <a class="btn-b" href="index.php?route=Tecnico/MisStats"><i class='bx bx-line-chart'></i> <?= __('home.cta.tech.stats') ?></a>
                    <?php elseif ($role === 'supervisor'): ?>
                        <a class="btn-a" href="index.php?route=Supervisor/Asignar"><i class='bx bx-user-voice'></i> <?= __('home.cta.supervisor.assign') ?></a>
                        <a class="btn-b" href="index.php?route=Supervisor/Presupuestos"><i class='bx bx-wallet'></i> <?= __('home.cta.supervisor.budgets') ?></a>
                    <?php elseif ($role === 'administrador'): ?>
                        <a class="btn-a" href="index.php?route=Admin/ListarUsers"><i class='bx bx-user'></i> <?= __('home.cta.admin.users') ?></a>
                        <a class="btn-b" href="index.php?route=Historial/ListarHistorial"><i class='bx bx-history'></i> <?= __('home.cta.admin.history') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-stats">
                    <div class="stat">
                        <i class='bx bx-time-five'></i>
                        <span class="num" id="activeTickets"><?= isset($stats['activeTickets']) ? (int)$stats['activeTickets'] : 0 ?></span>
                        <span class="label"><?= __('home.stats.activeTickets') ?></span>
                    </div>
                    <div class="stat">
                        <i class='bx bx-star'></i>
                        <span class="num" id="avgRating"><?= isset($stats['avgRating']) && $stats['avgRating'] !== null ? $stats['avgRating'] : '—' ?></span>
                        <span class="label"><?= __('home.stats.avgRating') ?></span>
                    </div>
                    <div class="stat">
                        <i class='bx bx-refresh'></i>
                        <span class="num" id="lastUpdate">
                            <?php if (!empty($stats['lastUpdateIso'])): ?>
                                <time title="<?= htmlspecialchars($stats['lastUpdateIso']) ?>">
                                    <?= htmlspecialchars($stats['lastUpdateHuman'] ?? '') ?>
                                </time>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                        <span class="label"><?= __('home.stats.lastUpdate') ?></span>
                    </div>
                </div>
            </div>
        </section>

        
        <div class="home-wrap">
            <div class="home-grid">

            
                
                <section class="home-news card-wide">
                    <h3><i class='bx bx-news'></i> <?= __('home.news.title') ?></h3>
                    <ul>
                        <li><strong><?= __('home.news.item.new') ?></strong> Seguimiento de repuestos en tiempo real disponible.</li>
                        <li><strong><?= __('home.news.item.info') ?></strong> Mantenimiento programado — 12 Oct (00:00 - 02:00)</li>
                        <li><strong><?= __('home.news.item.tip') ?></strong> Recordá calificar al técnico tras finalizar tu reparación.</li>
                    </ul>
                </section>

                
        <section class="home-charts card-wide">
    <h3><i class='bx bx-analyse'></i> <?= __('home.stats.title') ?></h3>
        <div id="chartsStatus" class="alert alert-warning" style="display:none;"></div>
        <div class="charts-grid">

                    
                    <div class="chart-box">
                    <h4><i class='bx bx-pie-chart'></i> <?= __('home.chart.tickets') ?></h4>
                    <canvas id="ticketsChart"></canvas>
                    </div>

                    
                    <div class="chart-box">
                    <h4><i class='bx bx-medal'></i> <?= __('home.chart.ranking') ?></h4>
                    <canvas id="rankingChart"></canvas>
                    </div>

                    
                    <div class="chart-box">
                    <h4><i class='bx bx-bar-chart-alt'></i> <?= __('home.chart.category') ?></h4>
                    <canvas id="categoryChart"></canvas>
                    </div>

                </div>
                </section>


                
                <section class="help card-wide">
                    <h3><i class='bx bx-question-mark'></i> <?= __('home.help.title') ?></h3>
                    <p><?= __('home.help.subtitle') ?></p>
                </section>
            </div>
        </div>

        <footer class="footer">
            <small>© <span id="year"></span> <?= __('home.footer') ?></small>
        </footer>
    </div>
</main>
<?php
$homeJsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/ProyectoPandora/Public/js/home-dashboard.js';
$homeJsVersion = file_exists($homeJsPath) ? filemtime($homeJsPath) : time();
?>
<script src="/ProyectoPandora/Public/js/home-dashboard.js?v=<?= $homeJsVersion ?>"></script>
<script src="/ProyectoPandora/Public/js/DarkMode.js?v=<?= time(); ?>" defer></script>
