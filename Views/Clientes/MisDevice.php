<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>

  <div class="Contenedor">
    <section class="section-mis-devices">
      <h2 class="titulo-carrusel">Mis Dispositivos</h2>

      <div class="carousel-container">
        <button class="carousel-btn prev-btn" id="prevBtn">&#10094;</button>
        <div class="carousel-track" id="carouselTrack">
          <?php if (!empty($dispositivos)): ?>
            <?php foreach ($dispositivos as $d): ?>
              <article class="device-card">
                <div class="device-img">
                  <img 
                    src="<?= htmlspecialchars($d['img_url'] ?? \Storage::fallbackDeviceUrl()) ?>" 
                    alt="Dispositivo <?= htmlspecialchars(($d['marca'] ?? '') . ' ' . ($d['modelo'] ?? '')) ?>"
                    loading="lazy"
                    decoding="async"
                    onerror="this.onerror=null;this.src='<?= htmlspecialchars(\Storage::fallbackDeviceUrl()) ?>'"
                  >
                </div>
                <div class="device-info u-flex-col u-flex-1">
                  <h3><?= htmlspecialchars($d['marca']) ?> <?= htmlspecialchars($d['modelo']) ?></h3>
                  <p class="line-clamp-3"><strong>Descripción:</strong> <?= htmlspecialchars($d['descripcion_falla']) ?></p>
                  <p><strong>Categoría:</strong> <?= htmlspecialchars($d['categoria']) ?></p>
                  <p><strong>Fecha agregado:</strong> 
                    <time title="<?= htmlspecialchars($d['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($d['fecha_human'] ?? '') ?>
                    </time>
                  </p>
                </div>
                <div class="device-card__actions">
                  <?php if (empty($d['has_active_ticket'])): ?>
        <form method="post" action="/ProyectoPandora/Public/index.php?route=Device/Eliminar"
          data-confirm="¿Eliminar este dispositivo? Esta acción no se puede deshacer."
          style="display:inline-block">
                      <input type="hidden" name="device_id" value="<?= (int)$d['id'] ?>">
                      <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                  <?php else: ?>
                    <span class="badge badge-warn">No disponible: ticket activo</span>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-device">
              <p>No tienes dispositivos registrados aún.</p>
              <a href="/ProyectoPandora/Public/index.php?route=Device/MostrarCrearDispositivo" class="btn-float-add btn-center" title="Agregar dispositivo">+</a>
            </div>
          <?php endif; ?>
        </div>
        <button class="carousel-btn next-btn" id="nextBtn">&#10095;</button>
      </div>
    </section>
  </div>
</main>

<a href="/ProyectoPandora/Public/index.php?route=Device/MostrarCrearDispositivo" class="btn-float-add" id="btnAdd" title="Agregar dispositivo">+</a>

<script src="/ProyectoPandora/Public/js/clientes-mis-device.js" defer></script>
