<?php
require_once __DIR__ . '/../../Core/Flash.php';

$authUser = Auth::user();
$rol = $authUser['role'] ?? '';
$name = $authUser['name'] ?? '';
$email = $authUser['email'] ?? '';
$avatarStored = $authUser['img_perfil'] ?? '';
$avatar = \Storage::resolveProfileUrl($avatarStored);



$route = $_GET['route'] ?? '';

function isHomeRoute(string $route): bool {
	if ($route === '' || strtolower($route) === 'default/index') return true;
	return false;
}

function headerMeta(string $route, string $rol): array {
	$titleKey = 'header.default.title';
	$subtitleKey = 'header.default.subtitle';
	switch (true) {
		case stripos($route, 'Default/Index') === 0:
			$titleKey = 'header.home.title'; $subtitleKey = 'header.home.subtitle'; break;
		case stripos($route, 'EstadoTicket/') === 0:
			$titleKey = 'header.estados.title'; $subtitleKey = 'header.estados.subtitle'; break;
		case stripos($route, 'Historial/') === 0:
			$titleKey = 'header.historial.title'; $subtitleKey = 'header.historial.subtitle'; break;
		case stripos($route, 'Admin/') === 0:
			$titleKey = 'header.admin.title'; $subtitleKey = 'header.admin.subtitle'; break;
		case stripos($route, 'Supervisor/Asignar') === 0:
			$titleKey = 'header.asignar.title'; $subtitleKey = 'header.asignar.subtitle'; break;
		case stripos($route, 'Supervisor/Presupuestos') === 0:
			$titleKey = 'header.presupuestos.title'; $subtitleKey = 'header.presupuestos.subtitle'; break;
		case stripos($route, 'Supervisor/GestionInventario') === 0:
			$titleKey = 'header.inventario.title'; $subtitleKey = 'header.inventario.subtitle'; break;
		case stripos($route, 'Tecnico/MisRepuestos') === 0:
			$titleKey = 'header.tecnico.repuestos.title'; $subtitleKey = 'header.tecnico.repuestos.subtitle'; break;
		case stripos($route, 'Tecnico/MisReparaciones') === 0:
			$titleKey = 'header.tecnico.reparaciones.title'; $subtitleKey = 'header.tecnico.reparaciones.subtitle'; break;
		case stripos($route, 'Tecnico/MisStats') === 0:
			$titleKey = 'header.tecnico.stats.title'; $subtitleKey = 'header.tecnico.stats.subtitle'; break;
		case stripos($route, 'Cliente/MisDevice') === 0:
			$titleKey = 'header.cliente.devices.title'; $subtitleKey = 'header.cliente.devices.subtitle'; break;
		case stripos($route, 'Cliente/MisTicket') === 0:
			$titleKey = 'header.cliente.tickets.title'; $subtitleKey = 'header.cliente.tickets.subtitle'; break;
		case stripos($route, 'Ticket/') === 0:
			$titleKey = 'header.tickets.title'; $subtitleKey = 'header.tickets.subtitle'; break;
		case stripos($route, 'Inventario/') === 0:
			$titleKey = 'header.inventario.title'; $subtitleKey = 'header.inventario.subtitle'; break;
		case stripos($route, 'Device/') === 0:
			$titleKey = 'header.device.title'; $subtitleKey = 'header.device.subtitle'; break;
		case stripos($route, 'Default/Guia') === 0:
			$titleKey = 'header.guia.title'; $subtitleKey = 'header.guia.subtitle'; break;
	}
	return [$titleKey, $subtitleKey];
}

list($titleKey, $subtitleKey) = headerMeta($route, $rol);
$title = __($titleKey);
$subtitle = __($subtitleKey);
?>


<header class="header hero-header">
	<div class="hero-row">
		<div class="hero-left">
			<?php if (isHomeRoute($route)): ?>
				<p class="hero-greet">
					<?= $authUser 
						? __('header.greet.hello', ['name' => htmlspecialchars($name)]) 
						: __('header.greet.welcome', ['app' => __('app.name')]) 
					?>
				</p>
			<?php endif; ?>
			<p class="hero-sub">
				<?= htmlspecialchars($title) ?> · <?= htmlspecialchars($subtitle) ?>
			</p>
		</div>
		<div class="hero-actions">
			<?php 
			
			$unread = 0; 
			if ($authUser) {
				require_once __DIR__ . '/../../Core/Database.php';
				require_once __DIR__ . '/../../Models/Notification.php';
				$dbh = new Database(); $dbh->connectDatabase();
				$nm = new NotificationModel($dbh->getConnection());
				$unread = $nm->countUnread((int)$authUser['id'], (string)$authUser['role']);
			}
			?>
			<?php if ($authUser): ?>
			<a href="/ProyectoPandora/Public/index.php?route=Notification/Index" class="notif-btn-home" title="Notificaciones" id="notifBell">
				<i class='bx bx-bell'></i>
				<span class="notif-badge" id="notifBadge" style="display: <?= ($unread>0?'inline-block':'none') ?>;">
					<?= (int)$unread ?>
				</span>
			</a>

			<?php endif; ?>
			
		</div>
  </div>
  <div class="hamburger" id="menuToggle">
        <span></span><span></span><span></span>
    </div>
</header>

<?php
	$only = ['error','warning'];
	include __DIR__ . '/FlashMessages.php';
?>

<!-- CSRF para AJAX: meta con token de un solo uso y helper JS global -->
<meta name="csrf-token" content="<?= htmlspecialchars(Csrf::generate(), ENT_QUOTES, 'UTF-8') ?>">
<script src="/ProyectoPandora/Public/js/csrf.js?v=<?= time(); ?>" defer></script>

<script src="/ProyectoPandora/Public/js/DarkMode.js?v=<?= time(); ?>" defer></script>
<script src="/ProyectoPandora/Public/js/Sidebar.js?v=<?= time(); ?>" defer></script>
<script src="/ProyectoPandora/Public/js/layout-header.js" defer></script>