<?php
require_once __DIR__ . '/../../Core/Auth.php';
require_once __DIR__ . '/../../Core/I18n.php';
I18n::boot();
$authUser = Auth::user();
$rol = $authUser['role'] ?? '';
$name = $authUser['name'] ?? '';
$email = $authUser['email'] ?? '';
$avatar = $authUser['img_perfil'] ?? '';

$defaultAvatar = '/ProyectoPandora/Public/img/imgPerfil/default.png';
$fallbackAvatar = '/ProyectoPandora/Public/img/Innovasys.png';

if ($avatar && strpos($avatar, '/ProyectoPandora/') !== 0) {
	$avatar = '/ProyectoPandora/Public/img/imgPerfil/' . ltrim($avatar, '/');
}

$avatarFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $avatar;
if (!$avatar || !is_file($avatarFs)) {
	$avatar = $defaultAvatar;
	$defaultFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $defaultAvatar;
	if (!is_file($defaultFs)) {
		$avatar = $fallbackAvatar;
	}
}


// Ruta actual (por si se requiere algún ajuste puntual)
$route = $_GET['route'] ?? '';
// ¿Es la Home?
function isHomeRoute(string $route): bool {
	if ($route === '' || strtolower($route) === 'default/index') return true;
	return false;
}
// Meta dinámica por ruta (título/subtítulo)
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
<!-- Estilos del header consolidados en AdminDash.css -->

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
			// Unread count minimal (no JSON): calcular rápido solo cuando hay usuario
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
			<a href="/ProyectoPandora/Public/index.php?route=Notification/Index" class="notif-btn" title="Notificaciones" id="notifBell">
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


<script>
	const menuBtn = document.getElementById('menuToggle');
const sidebar = document.querySelector('.sidebar');

menuBtn.addEventListener('click', () => {
  menuBtn.classList.toggle('active');
  sidebar.classList.toggle('active');
});

</script>
<script>
  const badge = document.getElementById('notifBadge');
  const bell = document.getElementById('notifBell');
  if (badge && badge.style.display !== 'none') {
    bell.classList.add('shake');
    setTimeout(() => bell.classList.remove('shake'), 1200);
  }
</script>

<?php if ($authUser): ?>
<script>
	// Polling sencillo cada 10 segundos para actualizar el contador del badge
	(function(){
		const badge = document.getElementById('notifBadge');
		if (!badge) return;
		const refresh = () => {
			fetch('/ProyectoPandora/Public/index.php?route=Notification/Count', { cache: 'no-store' })
				.then(r => r.ok ? r.text() : '0')
				.then(txt => {
					const n = parseInt((txt||'0').trim(), 10);
					if (isNaN(n) || n <= 0) {
						badge.style.display = 'none';
						badge.textContent = '0';
					} else {
						badge.style.display = 'inline-block';
						badge.textContent = String(n);
					}
				})
				.catch(() => {/* noop */});
		};
		// Primera carga inmediata y luego cada 10s
		refresh();
		setInterval(refresh, 10000);
	})();
</script>
<?php endif; ?>
<!-- Auto-refresh global deshabilitado: la UI se actualiza por AJAX en puntos específicos -->