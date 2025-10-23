
<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main class="asignar-page">
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
	<section class="content asignar-content">

			<?php if (isset($_GET['success'])): ?>
				<div class="alert alert-success">Asignación realizada correctamente.</div>
			<?php elseif (isset($_GET['error'])): ?>
				<div class="alert alert-error">Error: <?php echo htmlspecialchars($_GET['error']); ?></div>
			<?php endif; ?>

		<div class="asignar-panel">
			<form action="/ProyectoPandora/Public/index.php?route=Supervisor/Asignar" method="get" id="filtros" class="filters asignar-filters">
				<input type="hidden" name="route" value="Supervisor/Asignar" />
				<div class="field asignar-field">
					<label class="asignar-label">Filtro disponibilidad</label>
					<select name="estado" id="estado" class="asignar-input">
						<?php $estadoSel = $_GET['estado'] ?? 'todos'; ?>
						<option value="todos" <?php echo $estadoSel==='todos'?'selected':''; ?>>Todos</option>
						<option value="Disponible" <?php echo $estadoSel==='Disponible'?'selected':''; ?>>Disponibles</option>
						<option value="Ocupado" <?php echo $estadoSel==='Ocupado'?'selected':''; ?>>Asignados</option>
					</select>
				</div>
				<div class="field asignar-field asignar-field--grow">
					<label class="asignar-label">Buscar técnico</label>
					<input type="text" name="q" id="q" placeholder="Nombre o especialidad" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="asignar-input" />
				</div>
				<div class="field asignar-field">
					<label class="asignar-label">Ticket</label>
					<select name="ticket_id" id="ticket_id" class="asignar-input">
						<?php if (empty($ticketsSinTecnico)): ?>
							<option value="">No hay tickets sin técnico</option>
						<?php else: ?>
							<option value="">Selecciona un ticket…</option>
							<?php foreach ($ticketsSinTecnico as $t): ?>
								<?php $label = "#{$t['id']} - {$t['dispositivo']} {$t['modelo']} ({$t['cliente']})"; ?>
								<option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($_GET['ticket_id']) && $_GET['ticket_id']==$t['id'])?'selected':''; ?>><?php echo htmlspecialchars($label); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="actions asignar-actions">
					<button type="submit" class="btn btn-primary">Aplicar</button>
					<a href="/ProyectoPandora/Public/index.php?route=Supervisor/Asignar" class="btn btn-outline">Limpiar</a>
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
					<div class="asignar-empty">No se encontraron técnicos con esos criterios.</div>
				<?php else: ?>
					<?php foreach ($filtrados as $tec): ?>
						<?php
							$avatar = $tec['img_perfil'] ?? '';
							$defaultAvatar = '/ProyectoPandora/Public/img/imgPerfil/default.png';
							$fallbackAvatar = '/ProyectoPandora/Public/img/Innovasys.png';
							if ($avatar && strpos($avatar, '/ProyectoPandora/') !== 0 && !preg_match('#^https?://#i', $avatar)) {
								$avatar = '/ProyectoPandora/Public/img/imgPerfil/' . ltrim($avatar, '/');
							}
							$baseDocRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
							$avatarFs = $baseDocRoot . $avatar;
							if (!$avatar || ($baseDocRoot && !preg_match('#^https?://#i', $avatar) && !is_file($avatarFs))) {
								$avatar = $defaultAvatar;
								$defaultFs = $baseDocRoot . $defaultAvatar;
								if ($baseDocRoot && !is_file($defaultFs)) {
									$avatar = $fallbackAvatar;
								}
							}
							$estado = $tec['disponibilidad'] ?? 'Desconocido';
							$badgeColor = $estado === 'Disponible' ? '#16a34a' : ($estado==='Ocupado' ? '#ef4444' : '#64748b');
						?>
						<div class="asignar-card">
							<div class="asignar-card__head">
								<img src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar" class="asignar-avatar" />
								<div class="asignar-card__title">
									<div class="asignar-card__row">
										<h3 class="asignar-card__name"><?php echo htmlspecialchars($tec['name'] ?? ''); ?></h3>
										<span class="badge <?php echo $estado==='Disponible' ? 'badge--success' : ($estado==='Ocupado' ? 'badge--danger' : 'badge--muted'); ?>" title="Estado informativo, gestionado por el técnico en su perfil"><?php echo htmlspecialchars($estado); ?></span>
									</div>
									<div class="asignar-card__row" style="gap:6px; align-items:center;">
										<?php $r = isset($tec['rating_avg']) ? (float)$tec['rating_avg'] : 0; $rc=(int)($tec['rating_count'] ?? 0); if ($rc===0 && $r<=0){ $r=3.0; } $full = (int)floor($r); $half = ($r - $full) >= 0.5; ?>
										<span title="Promedio: <?php echo round($r,1); ?> (<?php echo $rc; ?> califs)" style="font-size:14px; color:#f5c518;">
											<?php for($i=1;$i<=5;$i++): ?>
												<?php if ($i <= $full): ?>★<?php elseif ($half && $i==$full+1): ?>☆<?php else: ?>☆<?php endif; ?>
											<?php endfor; ?>
										</span>
										<small style="opacity:.8;">(<?php echo round($r,1); ?>)</small>
									</div>
									<div class="asignar-card__subtitle">Especialidad: <?php echo htmlspecialchars($tec['especialidad'] ?? '—'); ?></div>
								</div>
							</div>
							<div class="asignar-card__chips">
								<div class="chip">Tickets asignados: <?php echo (int)($tec['tickets_asignados'] ?? 0); ?></div>
								<div class="chip">Activos: <?php echo (int)($tec['tickets_activos'] ?? 0); ?></div>
								<div class="chip">Email: <?php echo htmlspecialchars($tec['email'] ?? ''); ?></div>
							</div>
							<?php $dispOk = strcasecmp($estado, 'Disponible') === 0; ?>
							<form action="/ProyectoPandora/Public/index.php?route=Supervisor/AsignarTecnico" method="post" class="asignar-assign-form">
								<input type="hidden" name="tecnico_id" value="<?php echo (int)$tec['id']; ?>" />
								<select name="ticket_id" class="asignar-input asignar-input--small" <?php echo (!$dispOk || empty($ticketsSinTecnico)) ? 'disabled' : ''; ?>>
									<?php if (empty($ticketsSinTecnico)): ?>
										<option value="">No hay tickets sin técnico</option>
									<?php else: ?>
										<option value="">Selecciona ticket…</option>
										<?php foreach ($ticketsSinTecnico as $t): ?>
											<?php $label = "#{$t['id']} - {$t['dispositivo']} {$t['modelo']}"; ?>
											<option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($_GET['ticket_id']) && $_GET['ticket_id']==$t['id'])?'selected':''; ?>><?php echo htmlspecialchars($label); ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
								<button type="submit" class="btn btn-primary" <?php echo (!$dispOk || empty($ticketsSinTecnico)) ? 'disabled' : ''; ?>>Asignar</button>
							</form>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<script src="/ProyectoPandora/Public/js/modal.js"></script>
