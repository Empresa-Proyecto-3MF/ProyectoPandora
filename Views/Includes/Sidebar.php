<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../Core/Auth.php';
require_once __DIR__ . '/../../Core/I18n.php';
require_once __DIR__ . '/../../Core/ImageHelper.php';
I18n::boot();
$authUser = Auth::user();
$locale = I18n::getLocale();
$i18nPayload = [
  'locale' => $locale,
  'messages' => I18n::messages()
];
$i18nJson = json_encode($i18nPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
if ($i18nJson === false) { $i18nJson = '{}'; }
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">

<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $adminCssPath = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\') . '/ProyectoPandora/Public/css/AdminDash.css'; ?>
    <link rel="stylesheet" href="/ProyectoPandora/Public/css/AdminDash.css?v=<?= file_exists($adminCssPath) ? filemtime($adminCssPath) : time(); ?>">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title><?= __('app.name') ?></title>
<<<<<<< HEAD
=======
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

>>>>>>> 26b1931848bcd9d2d5a4fe07c2bc3ace6b4674ed
=======
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = str_replace('\\', '/', dirname($scriptName ?: '/index.php'));
    if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') {
      $baseHref = '/';
    } else {
      $baseHref = rtrim($scriptDir, '/') . '/';
    }
    $adminCssPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . '/css/AdminDash.css';
  ?>
  <base href="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="css/AdminDash.css?v=<?= file_exists($adminCssPath) ? filemtime($adminCssPath) : time(); ?>">
  <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <title><?= I18n::t('app.name') ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    window.APP_I18N = <?= $i18nJson ?>;
  </script>
>>>>>>> 4944a813758c8e8cb1408a567514e17dab2335e7
</head>

<body>
  
  <aside class="sidebar">
    <div class=" flex">
      <span class="nav_image">
        <a href="index.php?route=Default/Index" style="cursor: pointer;">
          <img src="img/Innovasys_V2.png" alt="logo">
        </a>
      </span>
    </div>

    <div class="menu_container">
      <ul class="menu_items">
        <?php 
          $name = $authUser['name'] ?? '';
          $email = $authUser['email'] ?? '';
          $avatarStored = $authUser['img_perfil'] ?? '';
          $avatar = profile_image_url($avatarStored);
        ?>
        <?php if ($authUser): ?>
          <li class="item user-block">
            <a href="index.php?route=Default/Perfil" class="user-link flex">
              <img src="<?= htmlspecialchars($avatar) ?>" alt="Perfil" class="user-avatar"/>
              <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($name) ?></span>
                <small class="user-email"><?= htmlspecialchars($email) ?></small>
              </div>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($authUser): ?>
          <li class="item menu-item-static">
            <a href="index.php?route=Auth/Logout" class="link flex logout-link">
              <i class='bx bx-log-out'></i>
              <span><?= I18n::t('nav.logout') ?></span>
            </a>
          </li>
        <?php endif; ?>
        <div class="menu_title flex">
          <span class="title"><?= I18n::t('nav.menu') ?></span>
          <span class="line"></span>
        </div>
            
        
        <li class="item menu-item-static">
          <a href="index.php?route=Default/Index" class="link flex">
            <i class='bx bx-home'></i>
            <span><?= I18n::t('nav.home') ?></span>
          </a>
        </li>

<<<<<<< HEAD
                        <?php if ($role === 'administrador'): ?>
                            
                            <li class="item menu-item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Admin/ListarUsers" class="link flex">
                                    <i class='bx bx-user-circle'></i>
                                    <span><?= __('nav.users') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Historial/ListarHistorial" class="link flex">
                                    <i class='bx bx-time'></i>
                                    <span><?= __('nav.history') ?></span>
                                </a>
                            </li>
                                <!---
                                <li class="item menu-item menu-item-static">
                                    <a href="/ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados" class="link flex">
                                        <i class='bx bx-list-check'></i>
                                        <span>Estados</span>
                                    </a>
                                </li> --->
                            <li class="item menu-item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Device/ListarCategoria" class="link flex">
                                    <i class='bx bx-category'></i>
                                    <span><?= __('nav.device.categories') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias" class="link flex">
                                    <i class='bx bx-purchase-tag'></i>
                                    <span><?= __('nav.inventory.categories') ?></span>
                                </a>
                            </li>
                        <?php elseif ($role === 'supervisor'): ?>
                            <li class="item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Supervisor/Asignar" class="link flex">
                                    <i class='bx bx-task'></i>
                                    <span><?= __('nav.assign.tech') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario" class="link flex">
                                    <i class='bx bx-package'></i>
                                    <span><?= __('nav.inventory.manage') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item-static">
                                <a href="/ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos" class="link flex">
                                    <i class='bx bx-dollar'></i>
                                    <span><?= __('nav.budgets') ?></span>
                                </a>
                            </li>
                        <?php elseif ($role === 'tecnico'): ?>
                            
                            <li class="item menu-item-static">
                                <a href="index.php?route=Tecnico/MisReparaciones" class="link flex">
                                    <i class='bxr  bx-ticket'></i>
                                    <span><?= __('nav.tickets') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item-static">
                                <a href="index.php?route=Tecnico/MisStats" class="link flex">
<<<<<<< HEAD
                                    <i class='bxr  bx-ticket'></i>
=======
                                    <i class='bx bx-medal'></i>
>>>>>>> 26b1931848bcd9d2d5a4fe07c2bc3ace6b4674ed
                                    <span><?= __('nav.my.stats') ?></span>
                                </a>
=======
        <?php if ($authUser): ?>
          <?php $role = strtolower($authUser['role'] ?? ''); ?>
>>>>>>> 4944a813758c8e8cb1408a567514e17dab2335e7

          <?php if ($role === 'administrador'): ?>
            <li class="item menu-item-static">
              <a href="index.php?route=Admin/ListarUsers" class="link flex">
                <i class='bx bx-user-circle'></i>
                <span><?= I18n::t('nav.users') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Historial/ListarHistorial" class="link flex">
                <i class='bx bx-time'></i>
                <span><?= I18n::t('nav.history') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Device/ListarCategoria" class="link flex">
                <i class='bx bx-category'></i>
                <span><?= I18n::t('nav.device.categories') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Inventario/ListarCategorias" class="link flex">
                <i class='bx bx-purchase-tag'></i>
                <span><?= I18n::t('nav.inventory.categories') ?></span>
              </a>
            </li>

          <?php elseif ($role === 'supervisor'): ?>
            <li class="item menu-item-static">
              <a href="index.php?route=Supervisor/Asignar" class="link flex">
                <i class='bx bx-task'></i>
                <span><?= I18n::t('nav.assign.tech') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Supervisor/GestionInventario" class="link flex">
                <i class='bx bx-package'></i>
                <span><?= I18n::t('nav.inventory.manage') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Supervisor/Presupuestos" class="link flex">
                <i class='bx bx-dollar'></i>
                <span><?= I18n::t('nav.budgets') ?></span>
              </a>
            </li>

          <?php elseif ($role === 'tecnico'): ?>
            <li class="item menu-item-static">
              <a href="index.php?route=Tecnico/MisReparaciones" class="link flex">
                <i class='bx bx-ticket'></i>
                <span><?= I18n::t('nav.tickets') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Tecnico/MisStats" class="link flex">
                <i class='bx bx-medal'></i>
                <span><?= I18n::t('nav.my.stats') ?></span>
              </a>
            </li>

          <?php elseif ($role === 'cliente'): ?>
            <li class="item menu-item-static">
              <a href="index.php?route=Cliente/MisDevice" class="link flex">
                <i class='bx bx-devices'></i>
                <span><?= I18n::t('nav.my.devices') ?></span>
              </a>
            </li>
            <li class="item menu-item-static">
              <a href="index.php?route=Cliente/MisTicketActivo" class="link flex">
                <i class='bx bx-ticket'></i>
                <span><?= I18n::t('nav.my.tickets') ?></span>
              </a>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li class="item">
            <a href="index.php?route=Auth/Login" class="link flex">
              <i class='bx bx-log-in'></i>
              <span><?= I18n::t('nav.login') ?></span>
            </a>
          </li>
          <li class="item">
            <a href="index.php?route=Register/Register" class="link flex">
              <i class='bx bx-user-plus'></i>
              <span><?= I18n::t('nav.register') ?></span>
            </a>
          </li>
          <li class="item">
            <a href="index.php?route=Default/Guia" class="link flex">
              <i class='bx bx-help-circle'></i>
              <span><?= I18n::t('nav.guide') ?></span>
            </a>
          </li>
        <?php endif; ?>

        
      </ul>
    </div>
  </aside>

</body>


<?php 
  $authJsPath = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\') . 'js/auth-login.js';
  $i18nRuntimePath = __DIR__ . '/../../Public/js/i18n-runtime.js';
?>
<script src="js/i18n-runtime.js?v=<?= file_exists($i18nRuntimePath) ? filemtime($i18nRuntimePath) : time(); ?>" defer></script>
<script src="js/auth-login.js?v=<?= file_exists($authJsPath) ? filemtime($authJsPath) : time(); ?>" defer></script>
<script src="js/notifications.js?v=<?= time(); ?>" defer></script>
<script src="js/confirm-actions.js?v=<?= time(); ?>" defer></script>
<script src="js/ticket-actions.js?v=<?= time(); ?>" defer></script>
<script src="js/list-filters.js?v=<?= time(); ?>" defer></script>
<script src="js/presupuestos.js?v=<?= time(); ?>" defer></script>
<script src="js/ticket-sync.js?v=<?= time(); ?>" defer></script>
<script src="js/DarkMode.js?v=<?= time(); ?>" defer></script>
<script src="js/Sidebar.js?v=<?= time(); ?>" defer></script>

</html>
