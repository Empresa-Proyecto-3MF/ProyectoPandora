<?php
require_once __DIR__ . '/../../Core/ImageHelper.php';
$fallbackTicketImg = device_image_url('');
include_once __DIR__ . '/../Includes/Sidebar.php';
?>
<main>
  <?php include_once __DIR__ . '/../Includes/Header.php'; ?>
  
  <div class="Contenedor">

    <?php  ?>

    <form method="get" action="index.php" class="filtros" style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;align-items:center;">
      <input type="hidden" name="route" value="Cliente/MisTicketActivo" />
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
      <a class="btn btn-outline" href="index.php?route=Cliente/MisTicketActivo">Limpiar</a>
    </form>

    <section class="section-mis-tickets">
      <h2 class="titulo-carrusel"></h2>

      <div class="carousel-container">
        <button class="carousel-btn prev-btn" id="prevTicketBtn">&#10094;</button>
        <div class="carousel-track" id="carouselTicketTrack">
          <?php if (!empty($tickets)): ?>
            <?php foreach ($tickets as $ticket): ?>

              <?php
                
                $imgSrc = (string)($ticket['img_preview'] ?? '');
                if ($imgSrc === '') { $imgSrc = $fallbackTicketImg; }
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

                $isWorking = in_array($estado, [
                  'diagnóstico','diagnostico','en reparación','en reparacion','en pruebas'
                ]);
              ?>

              <article class="ticket-card">
                <div class="ticket-img">
                    <img 
                    src="<?= htmlspecialchars($imgSrc) ?>" 
                    alt="Ticket #<?= (int)$ticket['id'] ?> - <?= htmlspecialchars(($ticket['dispositivo'] ?? '') . ' ' . ($ticket['modelo'] ?? '')) ?>"
                    loading="lazy"
                    decoding="async"
                    onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallbackTicketImg, ENT_QUOTES, 'UTF-8') ?>'"
                  >
                </div>

                <div class="ticket-info u-flex-col u-flex-1">
                  <h3><?= htmlspecialchars($ticket['dispositivo']) ?> <?= htmlspecialchars($ticket['modelo']) ?></h3>
                  <p class="line-clamp-3"><strong>Descripción:</strong> <?= htmlspecialchars($ticket['descripcion_falla']) ?></p>

                  
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

                  <p><strong>Fecha:</strong> <time title="<?= htmlspecialchars($ticket['fecha_exact'] ?? '') ?>"><?= htmlspecialchars($ticket['fecha_human'] ?? '') ?></time></p>
                  <p><strong>Técnico:</strong> <?= htmlspecialchars($ticket['tecnico'] ?? 'Sin asignar') ?></p>


                  <div class="card-actions">
                    <a href="index.php?route=Ticket/Ver&id=<?= (int)$ticket['id'] ?>" class="btn btn-primary">Ver detalle</a>
                    <a href="index.php?route=Ticket/Editar&id=<?= (int)$ticket['id'] ?>" class="btn btn-edit">Editar</a>
                      <?php if (!empty($ticket['puedeEliminar'])): ?>
                        <a href="index.php?route=Ticket/Eliminar&id=<?= (int)$ticket['id'] ?>" class="btn delete-btn" data-confirm="¿Seguro que deseas eliminar este ticket? Esta acción no se puede deshacer.">Eliminar</a>
                      <?php endif; ?>
                  </div>
                </div>
              </article>

            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-device">
              <p>No tienes tickets activos.</p>
              <a href="index.php?route=Ticket/mostrarCrear" class="btn-float-add btn-center" title="Agregar ticket">+</a>
            </div>
          <?php endif; ?>
        </div>
        <button class="carousel-btn next-btn" id="nextTicketBtn">&#10095;</button>
      </div>
    </section>

    <a href="index.php?route=Ticket/mostrarCrear" class="btn-float-add" id="btnAddTicket" title="Agregar ticket">+</a>
  </div>
</main>

<script src="js/clientes-mis-ticket-activo.js" defer></script>
