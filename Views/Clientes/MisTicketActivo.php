<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>

<main>
  <?php include_once __DIR__ . '/../Includes/Header.php'; ?>
  
  <div class="Contenedor">

    <form method="get" action="/ProyectoPandora/Public/index.php" class="filtros" style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;align-items:center;">
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
      <a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo">Limpiar</a>
    </form>

    <section class="section-mis-tickets">
      <div class="cards-container">
        <?php if (!empty($tickets)): ?>
          <?php foreach ($tickets as $ticket): ?>
            <?php 
              $estadoStr = $ticket['estado'] ?? ''; 
              $estadoClass = $ticket['estadoClass'] ?? 'badge'; 
              $estadoLower = strtolower($estadoStr); 
              $imgUrl = !empty($ticket['imagen']) 
                ? htmlspecialchars($ticket['imagen']) 
                : '/ProyectoPandora/Public/assets/img/default-device.jpg'; // Imagen por defecto
            ?>

            <div class="device-card">
              <!-- <div class="device-img">
                <img src="<?= $imgUrl ?>" alt="Imagen del dispositivo">
              </div> -->

              <div class="device-info u-flex-col u-flex-1">
                <h3><?= htmlspecialchars($ticket['dispositivo']) ?> <?= htmlspecialchars($ticket['modelo']) ?></h3>
                <p class="line-clamp-3"><strong>Descripción:</strong> <?= htmlspecialchars($ticket['descripcion_falla']) ?></p>
                <p><strong>Estado:</strong> <span class="<?= $estadoClass ?>"><?= htmlspecialchars($estadoStr) ?></span></p>
                <p><strong>Fecha:</strong> <time title="<?= htmlspecialchars($ticket['fecha_exact'] ?? '') ?>"><?= htmlspecialchars($ticket['fecha_human'] ?? '') ?></time></p>
                <p><strong>Técnico:</strong> <?= htmlspecialchars($ticket['tecnico'] ?? 'Sin asignar') ?></p>

                <div class="card-actions">
                  <a href="/ProyectoPandora/Public/index.php?route=Ticket/Ver&id=<?= (int)$ticket['id'] ?>" class="btn btn-primary">Ver detalle</a>
                  <a href="/ProyectoPandora/Public/index.php?route=Ticket/Editar&id=<?= (int)$ticket['id'] ?>" class="btn btn-edit">Editar</a>
                  <?php if (!empty($ticket['puedeEliminar'])): ?>
                    <a href="/ProyectoPandora/Public/index.php?route=Ticket/Eliminar&id=<?= (int)$ticket['id'] ?>" class="btn delete-btn" onclick="return confirm('¿Seguro que deseas eliminar este ticket? Esta acción no se puede deshacer.');">Eliminar</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <p>No tienes tickets activos.</p>
        <?php endif; ?>
      </div>
    </section>

    <a href="/ProyectoPandora/Public/index.php?route=Ticket/mostrarCrear" class="btn-float-add" title="Agregar ticket">+</a>
  </div>
</main>
