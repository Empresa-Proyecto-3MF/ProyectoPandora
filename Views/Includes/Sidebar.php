<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../Core/Auth.php';
require_once __DIR__ . '/../../Core/I18n.php';
I18n::boot();
$authUser = Auth::user();
$locale = I18n::getLocale();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $adminCssPath = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\') . '/ProyectoPandora/Public/css/AdminDash.css'; ?>
    <link rel="stylesheet" href="/ProyectoPandora/Public/css/AdminDash.css?v=<?= file_exists($adminCssPath) ? filemtime($adminCssPath) : time(); ?>">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title><?= __('app.name') ?></title>
</head>

<body>
    <aside class="sidebar">
        <nav class="sidebar">
            <div>
                <div class="nav_image flex">
                    <div class="brand">
                        <img class="brand-dark" src="/ProyectoPandora/Public/img/Innovasys_V2.png" alt="logo">
                    </div>
                </div>
            </div>
            <div class="menu-conteiner">
                <ul class="menu-items">
                    <?php 
                        $name = $authUser['name'] ?? '';
                        $email = $authUser['email'] ?? '';
                        $avatar = $authUser['img_perfil'] ?? '';
                        $defaultAvatar = '/ProyectoPandora/Public/img/imgPerfil/default.png';
                        if ($avatar && strpos($avatar, '/ProyectoPandora/') !== 0) {
                            $avatar = '/ProyectoPandora/Public/img/imgPerfil/' . ltrim($avatar, '/');
                        }
                        if (!$avatar) { $avatar = $defaultAvatar; }
                    ?>
                    <?php if ($authUser): ?>
                    <li class="item menu-item user-block">
                        <a href="/ProyectoPandora/Public/index.php?route=Default/Perfil" class="user-link">
                            <img src="<?= htmlspecialchars($avatar) ?>" alt="Perfil" class="user-avatar"/>
                            <div class="user-info">
                                <span class="user-name"><?= htmlspecialchars($name) ?></span>
                                <small class="user-email"><?= htmlspecialchars($email) ?></small>
                            </div>
                        </a>
                    </li>


                    <?php endif; ?>
                    <?php if ($authUser): ?>
                    <li class="item menu-item menu-item-static">
                        <a href="/ProyectoPandora/Public/index.php?route=Auth/Logout" class="link flex logout-link">
                            <i class='bx bx-log-out'></i>
                            <span><?= __('nav.logout') ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <div class="menu_title flex">
                        <span class="title"><?= __('nav.menu') ?></span>
                        <span class="line"></span>
                    </div>
                    <li class="item menu-item menu-item-static">
                        <a href="/ProyectoPandora/Public/index.php?route=Default/Index" class="link flex">
                            <i class='bx bx-home'></i>
                            <span><?= __('nav.home') ?></span>
                        </a>
                    </li>
                    <?php if ($authUser): ?>
                        <?php $role = strtolower($authUser['role'] ?? ''); ?>

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
                                    <i class='bxr  bx-ticket'></i>
                                    <span><?= __('nav.my.stats') ?></span>
                                </a>

                            </li>
                        <?php elseif ($role === 'cliente'): ?>
                            <li class="item menu-item-static">
                                <a href="index.php?route=Cliente/MisDevice" class="link flex">
                                    <i class='bx  bx-devices'></i>
                                    <span><?= __('nav.my.devices') ?></span>
                                </a>
                            </li>
                            <li class="item menu-item-static">
                                <a href="index.php?route=Cliente/MisTicketActivo" class="link flex">
                                    <i class='bxr  bx-ticket'></i>
                                    <span><?= __('nav.my.tickets') ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        
                        
                        <li class="item ">
                            <a href="/ProyectoPandora/Public/index.php?route=Auth/Login" class="link flex">
                                <i class='bx bx-arrow-out-right-square-half'></i>
                                <span><?= __('nav.login') ?></span>
                            </a>
                        </li>
                        <li class="item ">
                            <a href="/ProyectoPandora/Public/index.php?route=Register/Register" class="link flex">
                                <i class='bxr  bx-form'></i>
                                <span><?= __('nav.register') ?></span>
                            </a>
                        </li>
                        <li class="item ">
                            <a href="/ProyectoPandora/Public/index.php?route=Default/Guia" class="link flex">
                                <i class='bx  bx-help-circle'></i>
                                <span><?= __('nav.guide') ?></span>
                                </i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </aside>


    <!-- === SCRIPT GLOBAL DE MODO OSCURO (Mover luego a la zona js) === -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const body = document.body;
        const modo = localStorage.getItem("modo") || "claro";

        // Aplicar el modo al cargar
        if (modo === "oscuro") {
          body.classList.add("dark-mode");
        } else {
          body.classList.remove("dark-mode");
        }

        // Si existe el toggle en la página actual, sincronizarlo
        const toggle = document.getElementById("toggle-darkmode");
        if (toggle) {
          toggle.checked = (modo === "oscuro");
          toggle.addEventListener("change", () => {
            if (toggle.checked) {
              body.classList.add("dark-mode");
              localStorage.setItem("modo", "oscuro");
            } else {
              body.classList.remove("dark-mode");
              localStorage.setItem("modo", "claro");
            }
          });
        }
      });
    </script>

</body>
<?php 
    // Cargar JS de login (se auto-inicializa sólo si existe #loginForm)
    $authJsPath = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\') . '/ProyectoPandora/Public/js/auth-login.js';
?>
<script src="/ProyectoPandora/Public/js/auth-login.js?v=<?= file_exists($authJsPath) ? filemtime($authJsPath) : time(); ?>" defer></script>
        <script src="/ProyectoPandora/Public/js/notifications.js?v=<?php echo time(); ?>" defer></script>
        <script src="/ProyectoPandora/Public/js/ticket-actions.js?v=<?php echo time(); ?>" defer></script>
        <script src="/ProyectoPandora/Public/js/list-filters.js?v=<?php echo time(); ?>" defer></script>
        <script src="/ProyectoPandora/Public/js/presupuestos.js?v=<?php echo time(); ?>" defer></script>
    <script src="/ProyectoPandora/Public/js/ticket-sync.js?v=<?php echo time(); ?>" defer></script>
</html>
