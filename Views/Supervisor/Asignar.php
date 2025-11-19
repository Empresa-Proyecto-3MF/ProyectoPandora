<?php
require_once __DIR__ . '/../../Core/ImageHelper.php';
$fallbackAvatar = profile_image_url('');
include_once __DIR__ . '/../Includes/Sidebar.php';
?>

<main class="asignar-page">
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
	<section class="content asignar-content">

			<?php I18n::boot(); $flashes = Flash::peek(); ?>
						<?php foreach ($flashes as $f): ?>
							<?php if ($f['type'] === 'success_quiet') continue; ?>
							<div class="alert alert-<?php echo htmlspecialchars($f['type']); ?>">
								<?php echo htmlspecialchars(I18n::t($f['message'])); ?>
							</div>
						<?php endforeach; ?>

		<div class="asignar-panel">
			<form action="index.php?route=Supervisor/Asignar" method="get" id="filtros" class="filters asignar-filters">
				<input type="hidden" name="route" value="Supervisor/Asignar" />
				<div class="field asignar-field">
					<label class="asignar-label"><?= I18n::t('supervisor.assign.filter.availability'); ?></label>
					<select name="estado" id="estado" class="asignar-input">
						<?php $estadoSel = $_GET['estado'] ?? 'todos'; ?>
						<option value="todos" <?php echo $estadoSel==='todos'?'selected':''; ?>><?= I18n::t('supervisor.assign.filter.option.all'); ?></option>
						<option value="Disponible" <?php echo $estadoSel==='Disponible'?'selected':''; ?>><?= I18n::t('supervisor.assign.filter.option.available'); ?></option>
						<option value="Ocupado" <?php echo $estadoSel==='Ocupado'?'selected':''; ?>><?= I18n::t('supervisor.assign.filter.option.busy'); ?></option>
					</select>
				</div>
				<div class="field asignar-field asignar-field--grow">
					<label class="asignar-label"><?= I18n::t('supervisor.assign.search.tech'); ?></label>
					<input type="text" name="q" id="q" placeholder="<?= I18n::t('supervisor.assign.search.placeholder'); ?>" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="asignar-input" />
				</div>
				<div class="field asignar-field">
					<label class="asignar-label"><?= I18n::t('supervisor.assign.ticket.label'); ?></label>
					<select name="ticket_id" id="ticket_id" class="asignar-input">
						<?php if (empty($ticketsSinTecnico)): ?>
							<option value=""><?= I18n::t('supervisor.assign.ticket.none'); ?></option>
						<?php else: ?>
							<option value=""><?= I18n::t('supervisor.assign.ticket.select'); ?></option>
							<?php foreach ($ticketsSinTecnico as $t): ?>
								<?php $label = "#{$t['id']} - {$t['dispositivo']} {$t['modelo']} ({$t['cliente']})"; ?>
								<option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($_GET['ticket_id']) && $_GET['ticket_id']==$t['id'])?'selected':''; ?>><?php echo htmlspecialchars($label); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="actions asignar-actions">
					<button type="submit" class="btn btn-primary"><?= I18n::t('supervisor.assign.actions.apply'); ?></button>
					<a href="index.php?route=Supervisor/Asignar" class="btn btn-outline"><?= I18n::t('supervisor.assign.actions.clear'); ?></a>
				</div>
			</form>

			<?php
				$estadoFiltro = $_GET['estado'] ?? 'Disponible';
				$q = strtolower(trim($_GET['q'] ?? ''));
				$filtrados = array_filter($tecnicos ?? [], function($tec) use ($estadoFiltro, $q) {
					$okEstado = ($estadoFiltro === 'todos') || strcasecmp($tec['disponibilidad'] ?? '', $estadoFiltro) === 0;
					if (!$okEstado) return false;
					if ($q === '') return true;
					$hay = strpos(strtolower($tec['name'] ?? ''), $q) !== false
							|| strpos(strtolower($tec['especialidad'] ?? ''), $q) !== false;
					return $hay;
				});
			?>

			<div class="asignar-grid">
				<?php if (empty($filtrados)): ?>
					<div class="asignar-empty"><?= I18n::t('supervisor.assign.empty'); ?></div>
				<?php else: ?>
					<?php foreach ($filtrados as $tec): ?>
						<?php
							$avatar = profile_image_url($tec['img_perfil'] ?? '');
							$estado = $tec['disponibilidad'] ?? 'Desconocido';
						?>
						<div class="asignar-card">
							<div class="asignar-card__head">
								<img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?= I18n::t('profile.avatar.alt'); ?>" class="asignar-avatar" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($fallbackAvatar, ENT_QUOTES, 'UTF-8'); ?>'" />
								<div class="asignar-card__title">
									<div class="asignar-card__row">
										<h3 class="asignar-card__name"><?php echo htmlspecialchars($tec['name'] ?? ''); ?></h3>
										<span class="badge <?php echo $estado==='Disponible' ? 'badge--success' : ($estado==='Ocupado' ? 'badge--danger' : 'badge--muted'); ?>" title="<?= I18n::t('supervisor.assign.badge.state.hint'); ?>"><?php echo htmlspecialchars(I18n::t('supervisor.assign.status.' . (strcasecmp($estado,'Disponible')===0?'available':(strcasecmp($estado,'Ocupado')===0?'busy':'unknown')))); ?></span>
									</div>
									<div class="asignar-card__row" style="gap:6px; align-items:center;">
										<?php $r = isset($tec['rating_avg']) ? (float)$tec['rating_avg'] : 0; $rc=(int)($tec['rating_count'] ?? 0); if ($rc===0 && $r<=0){ $r=3.0; } $full = (int)floor($r); $half = ($r - $full) >= 0.5; ?>
										<span title="<?= I18n::t('supervisor.assign.rating.title', ['avg'=>round($r,1), 'count'=>$rc]); ?>" style="font-size:14px; color:#f5c518;">
											<?php for($i=1;$i<=5;$i++): ?>
												<?php if ($i <= $full): ?>★<?php elseif ($half && $i==$full+1): ?>☆<?php else: ?>☆<?php endif; ?>
											<?php endfor; ?>
										</span>
										<small style="opacity:.8;">(<?php echo round($r,1); ?>)</small>
									</div>
									<div class="asignar-card__subtitle"><?= I18n::t('supervisor.assign.field.specialty'); ?>: <?php echo htmlspecialchars($tec['especialidad'] ?? '—'); ?></div>
								</div>
							</div>
							<div class="asignar-card__chips">
								<div class="chip"><?= I18n::t('supervisor.assign.chip.assigned'); ?>: <?php echo (int)($tec['tickets_asignados'] ?? 0); ?></div>
								<div class="chip"><?= I18n::t('supervisor.assign.chip.active'); ?>: <?php echo (int)($tec['tickets_activos'] ?? 0); ?></div>
								<div class="chip"><?= I18n::t('supervisor.assign.chip.email'); ?>: <?php echo htmlspecialchars($tec['email'] ?? ''); ?></div>
							</div>
							<?php $dispOk = strcasecmp($estado, 'Disponible') === 0; ?>
							<form action="index.php?route=Supervisor/AsignarTecnico" method="post" class="asignar-assign-form">
								<?= Csrf::input(); ?>
								<input type="hidden" name="tecnico_id" value="<?php echo (int)$tec['id']; ?>" />
								<select name="ticket_id" class="asignar-input asignar-input--small" <?php echo (!$dispOk || empty($ticketsSinTecnico)) ? 'disabled' : ''; ?>>
									<?php if (empty($ticketsSinTecnico)): ?>
										<option value=""><?= I18n::t('supervisor.assign.ticket.none'); ?></option>
									<?php else: ?>
										<option value=""><?= I18n::t('supervisor.assign.ticket.selectShort'); ?></option>
										<?php foreach ($ticketsSinTecnico as $t): ?>
											<?php $label = "#{$t['id']} - {$t['dispositivo']} {$t['modelo']}"; ?>
											<option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($_GET['ticket_id']) && $_GET['ticket_id']==$t['id'])?'selected':''; ?>><?php echo htmlspecialchars($label); ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
								<button type="submit" class="btn btn-primary" <?php echo (!$dispOk || empty($ticketsSinTecnico)) ? 'disabled' : ''; ?>><?= I18n::t('supervisor.assign.actions.assign'); ?></button>
							</form>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<script src="js/modal.js"></script>
