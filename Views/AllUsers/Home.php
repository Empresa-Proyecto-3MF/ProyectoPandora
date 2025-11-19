<?php 
include_once __DIR__ . '/../Includes/Sidebar.php'; 
$role = strtolower($authUser['role'] ?? 'invitado');
?>

<main>
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>

    <div class="home-container">
        <section class="home-hero">
            <div class="hero-left">
                <h2><i class='bx bx-grid-alt'></i> <?= I18n::t('home.hero.title') ?></h2>
                <p><?= I18n::t('home.hero.subtitle') ?></p>
                <div class="home-cta">
                    <?php if ($role === 'invitado'): ?>
                        <a class="btn-a" href="index.php?route=Auth/Login"><i class='bx bx-log-in'></i> <?= I18n::t('home.cta.login') ?></a>
                        <a class="btn-b" href="index.php?route=Register/Register"><i class='bx bx-user-plus'></i> <?= I18n::t('home.cta.register') ?></a>
                    <?php elseif ($role === 'cliente'): ?>
                        <a class="btn-a" href="index.php?route=Cliente/MisTicketActivo"><i class='bx bx-support'></i> <?= I18n::t('home.cta.client.tickets') ?></a>
                        <a class="btn-b" href="index.php?route=Cliente/MisDevice"><i class='bx bx-laptop'></i> <?= I18n::t('home.cta.client.devices') ?></a>
                        <a class="btn-c" href="index.php?route=Ticket/Crear"><i class='bx bx-plus-circle'></i> <?= I18n::t('home.cta.client.createTicket') ?></a>
                    <?php elseif ($role === 'tecnico'): ?>
                        <a class="btn-a" href="index.php?route=Tecnico/MisReparaciones"><i class='bx bx-wrench'></i> <?= I18n::t('home.cta.tech.repairs') ?></a>
                        <a class="btn-b" href="index.php?route=Tecnico/MisStats"><i class='bx bx-line-chart'></i> <?= I18n::t('home.cta.tech.stats') ?></a>
                    <?php elseif ($role === 'supervisor'): ?>
                        <a class="btn-a" href="index.php?route=Supervisor/Asignar"><i class='bx bx-user-voice'></i> <?= I18n::t('home.cta.supervisor.assign') ?></a>
                        <a class="btn-b" href="index.php?route=Supervisor/Presupuestos"><i class='bx bx-wallet'></i> <?= I18n::t('home.cta.supervisor.budgets') ?></a>
                    <?php elseif ($role === 'administrador'): ?>
                        <a class="btn-a" href="index.php?route=Admin/ListarUsers"><i class='bx bx-user'></i> <?= I18n::t('home.cta.admin.users') ?></a>
                        <a class="btn-b" href="index.php?route=Historial/ListarHistorial"><i class='bx bx-history'></i> <?= I18n::t('home.cta.admin.history') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-stats">
                    <div class="stat">
                        <i class='bx bx-time-five'></i>
                        <span class="num" id="activeTickets"><?= isset($stats['activeTickets']) ? (int)$stats['activeTickets'] : 0 ?></span>
                        <span class="label"><?= I18n::t('home.stats.activeTickets') ?></span>
                    </div>
                    <div class="stat">
                        <i class='bx bx-star'></i>
                        <span class="num" id="avgRating"><?= isset($stats['avgRating']) && $stats['avgRating'] !== null ? $stats['avgRating'] : '—' ?></span>
                        <span class="label"><?= I18n::t('home.stats.avgRating') ?></span>
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
                        <span class="label"><?= I18n::t('home.stats.lastUpdate') ?></span>
                    </div>
                </div>
            </div>
        </section>
        <div class="home-wrap">
            <div class="home-grid">
                <section class="home-charts card-wide">
                    <h3><i class='bx bx-analyse'></i> <?= I18n::t('home.stats.title') ?></h3>
                    <div id="chartsStatus" class="alert alert-warning" style="display:none;"></div>
                    <div class="charts-grid">
                        <div class="chart-box">
                        <h4><i class='bx bx-pie-chart'></i> <?= I18n::t('home.chart.tickets') ?></h4>
                        <canvas id="ticketsChart"></canvas>
                        </div>
                        <div class="chart-box">
                        <h4><i class='bx bx-medal'></i> <?= I18n::t('home.chart.ranking') ?></h4>
                        <canvas id="rankingChart"></canvas>
                        </div>
                        <div class="chart-box">
                        <h4><i class='bx bx-bar-chart-alt'></i> <?= I18n::t('home.chart.category') ?></h4>
                        <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </section>
                <section class="help card-wide">
                    <h3><i class='bx bx-question-mark'></i> <?= I18n::t('home.help.title') ?></h3>
                    <p><?= I18n::t('home.help.subtitle') ?></p>
                </section>
            </div>
        </div>
        <footer class="footer">
            <small>© <span id="year"></span> <?= I18n::t('home.footer') ?></small>
        </footer>
    </div>
</main>
<?php
$homeJsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . 'js/home-dashboard.js';
$homeJsVersion = file_exists($homeJsPath) ? filemtime($homeJsPath) : time();
?>
<script src="js/home-dashboard.js?v=<?= $homeJsVersion ?>"></script>
<script src="js/DarkMode.js?v=<?= time(); ?>" defer></script>
