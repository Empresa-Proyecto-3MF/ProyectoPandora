<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
<section class="content">

  <div class="Contenedor">
    <form method="get" action="/ProyectoPandora/Public/index.php" class="filtros" style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;align-items:center;">
      <input type="hidden" name="route" value="Tecnico/MisReparaciones" />
      <?php $estadoSel = strtolower($_GET['estado'] ?? 'activos'); ?>
      <select name="estado" class="asignar-input asignar-input--small">
        <option value="activos" <?= $estadoSel==='activos'?'selected':'' ?>>Activos</option>
        <option value="finalizados" <?= $estadoSel==='finalizados'?'selected':'' ?>>Finalizados</option>
        <option value="todos" <?= $estadoSel==='todos'?'selected':'' ?>>Todos</option>
      </select>
      <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="asignar-input asignar-input--small" type="text" placeholder="Buscar..." />
      <input name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
      <input name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
      <button class="btn btn-primary" type="submit">Filtrar</button>
      <a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones">Limpiar</a>
    </form>

    <section class="section-mis-tickets">
      <h2 class="titulo-carrusel">Mis Tickets</h2>

      <div class="carousel-container">
        <button class="carousel-btn prev-btn" id="prevTicketBtnTech">&#10094;</button>

        <div class="carousel-track" id="carouselTicketTrackTech">
          <?php if (!empty($tickets)): ?>
            <?php foreach ($tickets as $ticket): ?>
              <?php 
                // Lógica movida al controlador: usar $ticket['img_preview']
                $imgSrc = (string)($ticket['img_preview'] ?? '');

                $estado = strtolower(trim($ticket['estado'] ?? ''));
                $estadoMap = [
                    'nuevo' => 'estado-nuevo',
                    'diagnóstico' => 'estado-diagnostico',
                    'diagnostico' => 'estado-diagnostico',
                    'presupuesto' => 'estado-presupuesto',
                    'en espera' => 'estado-espera',
                    'en reparación' => 'estado-reparacion',
                    'en reparacion' => 'estado-reparacion',
                    'en pruebas' => 'estado-pruebas',
                    'listo para retirar' => 'estado-retiro',
                    'finalizado' => 'estado-finalizado',
                    'cancelado' => 'estado-cancelado'
                ];
                $estadoClass = $estadoMap[$estado] ?? 'estado-default';
                $isWorking = in_array($estado, ['diagnóstico','diagnostico','en reparación','en reparacion','en pruebas']);
              ?>
              <article class="ticket-card">
                <div class="ticket-img">
                  <img 
                    src="<?= htmlspecialchars($imgSrc) ?>" 
                    alt="Ticket #<?= (int)$ticket['id'] ?> - <?= htmlspecialchars($ticket['marca'] . ' ' . $ticket['modelo']) ?>"
                    loading="lazy"
                    decoding="async"
                    onerror="this.onerror=null;this.src='<?= htmlspecialchars(\Storage::fallbackDeviceUrl()) ?>'"
                  >
                </div>

                <div class="ticket-info">
                  <h3><?= htmlspecialchars($ticket['marca']) ?> <?= htmlspecialchars($ticket['modelo']) ?></h3>
                  <p><strong>Cliente:</strong> <?= htmlspecialchars($ticket['cliente']) ?></p>
                   <div class="ticket-estado-wrapper">
                    <strong>Estado:</strong>
                    <span class="estado-tag <?= $estadoClass ?> <?= $isWorking ? 'estado-anim' : '' ?>">
                      <?= htmlspecialchars(ucfirst($estado)) ?>
                    </span>
                    <?php if ($isWorking): ?>
                      <div class="progress-bar">
                        <div class="progress-bar-fill"></div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <p class="line-clamp-3"><strong>Descripción:</strong> <?= htmlspecialchars($ticket['descripcion_falla']) ?></p>
                  
                  <p ><strong>Fecha:</strong> <time title="<?= htmlspecialchars($ticket['fecha_exact']) ?>"><?= htmlspecialchars($ticket['fecha_human']) ?></time></p>
                </div>
                <br>
                <div class="ticket-actions">
                  <a href="/ProyectoPandora/Public/index.php?route=Ticket/Ver&id=<?= $ticket['id'] ?>" class="btn btn-primary">Ver detalle</a>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No tienes reparaciones asignadas.</p>
          <?php endif; ?>
        </div>

        <button class="carousel-btn next-btn" id="nextTicketBtnTech">&#10095;</button>
      </div>
    </section>
  </div>
</section>

<script src="/ProyectoPandora/Public/js/tecnicos-mis-reparaciones.js" defer></script>
</main>
