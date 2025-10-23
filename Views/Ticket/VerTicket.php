<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<main>
  <div class="detalle-ticket-layout">
    <!-- ================== DETALLE IZQUIERDA ================== -->
    <div class="detalle-izquierda">
      <h2 id="tituloDetalle">Detalle del Ticket</h2>

      <!-- BLOQUE PRINCIPAL: INFO HORIZONTAL -->
      <div class="bloque-principal">
        <?php if (!empty($view['ticket'])): ?>
          <?php $t = $view['ticket']; ?>
          <ul class="detalle-grid" id="detalleTicket" style="list-style:none;padding:0;margin:0;">
              <li class="dato-item"><strong>ID Ticket:</strong><span><?= htmlspecialchars($t['id']) ?></span></li>
              <li class="dato-item"><strong>Dispositivo:</strong><span><?= htmlspecialchars($t['marca']) ?> <?= htmlspecialchars($t['modelo']) ?></span></li>
              <li class="dato-item"><strong>Cliente:</strong><span><?= htmlspecialchars($t['cliente'] ?? $t['cliente_nombre'] ?? $t['user_name'] ?? 'No disponible') ?></span></li>
              <li class="dato-item"><strong>Estado:</strong><span id="estado-badge" class="<?= htmlspecialchars($view['estadoClass']) ?>"><?= htmlspecialchars($view['estadoStr']) ?></span></li>
              <li class="dato-item dato-larga"><strong>Descripción de la falla:</strong><span><?= htmlspecialchars($t['descripcion'] ?? $t['descripcion_falla']) ?></span></li>
              <li class="dato-item"><strong>Técnico asignado:</strong><span><?= !empty($t['tecnico']) ? htmlspecialchars($t['tecnico']) : '<span class="sin-asignar">Sin asignar</span>' ?></span></li>
              <?php if (isset($t['fecha_creacion'])): ?>
                <li class="dato-item"><strong>Fecha de creación:</strong>
                  <time title="<?= htmlspecialchars(DateHelper::exact($t['fecha_creacion'])) ?>">
                    <?= htmlspecialchars(DateHelper::smart($t['fecha_creacion'])) ?>
                  </time>
                </li>
              <?php endif; ?>
              <?php if (!empty($t['fecha_cierre'])): ?>
                <li class="dato-item"><strong>Fecha de cierre:</strong>
                  <time title="<?= htmlspecialchars(DateHelper::exact($t['fecha_cierre'])) ?>">
                    <?= htmlspecialchars(DateHelper::smart($t['fecha_cierre'])) ?>
                  </time>
                </li>
              <?php endif; ?>

              <!-- Zona imagen: destacada -->
              <?php if (!empty($t['img_dispositivo'])): ?>
                  <li class="dato-item imagen-wrap">
                    <strong>Imagen del dispositivo:</strong>
                    <div class="imagen-contenedor">
                      <img class="imagen-dispositivo" src="/ProyectoPandora/Public/img/imgDispositivos/<?= htmlspecialchars($t['img_dispositivo']) ?>" alt="Imagen dispositivo">
                    </div>
                  </li>
              <?php endif; ?>
          </ul>
        <?php else: ?>
          <div class="alert alert-danger">No se encontró información para este ticket.</div>
        <?php endif; ?>
      </div> <!-- .bloque-principal -->

      <!-- BLOQUE: MENSAJES / ALERTAS YA EXISTENTES (NO TOCAR su CSS original) -->
      <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='estado'): ?>
          <div class="alert alert-warning">Solo puedes calificar cuando el ticket esté finalizado.</div>
      <?php endif; ?>
      <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='aprobacion'): ?>
          <div class="alert alert-warning">Aún falta que el cliente apruebe el presupuesto.</div>
      <?php endif; ?>
      <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='aprobado'): ?>
          <div class="alert alert-success">Presupuesto aprobado. El técnico podrá continuar con la reparación.</div>
      <?php elseif (!empty($view['flash']['ok']) && $view['flash']['ok']==='rechazado'): ?>
          <div class="alert alert-warning">Presupuesto rechazado. El ticket ha sido marcado como cancelado.</div>
      <?php endif; ?>
      <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='pagado'): ?>
          <div class="alert alert-success">Pago registrado. Ticket finalizado.</div>
      <?php endif; ?>

      <?php
        $rol = $view['rol'] ?? '';
        $finalizado = !empty($view['finalizado']);
        $estadoLower = strtolower(trim($view['estadoStr'] ?? ''));
      ?>

      <?php if (!empty($view['ticket']) && $rol === 'Cliente' && !empty($view['ticket']['tecnico']) && !$finalizado): ?>
          <div class="alert alert-info">Podrás calificar al técnico cuando el ticket esté finalizado.</div>
      <?php endif; ?>

      <!-- === BLOQUE CLIENTE (Presupuesto, botones aprobar/rechazar, calificación) === -->
      <div class="bloque-cliente">
        <?php if (!empty($view['ticket']) && $rol === 'Cliente'): ?>
          <?php if (!empty($view['enPresu'])): ?>
            <?php $p = $view['presupuesto']; ?>
            <?php $msgPrefix = ($estadoLower === 'presupuesto') ? 'Presupuesto publicado por' : 'Presupuesto preparado por'; ?>
            <div class="alert alert-info">
              <?= $msgPrefix ?> <strong><?= $p['total_fmt'] ?? LogFormatter::monto((float)$p['total']) ?></strong>.
              <?php if ($estadoLower === 'en espera'): ?> <span>(pendiente de publicación del supervisor)</span><?php endif; ?>
            </div>

            <div class="Tabla-Contenedor presu-tabla">
                <table>
                    <thead><tr><th>Ítem</th><th>Categoría</th><th>Cant.</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php if (empty($p['items'])): ?>
                        <tr><td colspan="4">Sin repuestos</td></tr>
          <?php else: foreach ($p['items'] as $it): ?>
                        <tr>
                            <td><?= htmlspecialchars($it['name_item']) ?></td>
                            <td><?= htmlspecialchars($it['categoria']) ?></td>
                            <td><?= (int)$it['cantidad'] ?></td>
              <td><?= htmlspecialchars($it['valor_total_fmt'] ?? LogFormatter::monto((float)$it['valor_total'])) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
                <div class="tabla-totales">
                  <div>Subtotal repuestos: <strong><?= htmlspecialchars($p['subtotal_fmt'] ?? LogFormatter::monto((float)$p['subtotal'])) ?></strong></div>
                  <div>Mano de obra: <strong><?= htmlspecialchars($p['mano_obra_fmt'] ?? LogFormatter::monto((float)$p['mano_obra'])) ?></strong>
                        <?php if ((float)$p['mano_obra'] <= 0): ?>
                            <span class="badge badge--muted">Falta definir mano de obra</span>
                        <?php endif; ?>
                    </div>
                  <div>Total: <strong><?= htmlspecialchars($p['total_fmt'] ?? LogFormatter::monto((float)$p['total'])) ?></strong></div>
                </div>
            </div>

            <?php if ($estadoLower === 'presupuesto'): ?>
            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/AprobarPresupuesto" class="inline-form">
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                <input type="hidden" name="comentario" value="Aprobado por el cliente" />
                <button class="btn btn-success" type="submit">Aprobar</button>
            </form>
            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/RechazarPresupuesto" class="inline-form">
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                <input type="hidden" name="comentario" value="Rechazado por el cliente" />
                <button class="btn btn-danger" type="submit">Rechazar</button>
            </form>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div> <!-- .bloque-cliente -->

      <!-- === CALIFICACIÓN CLIENTE (si corresponde) === -->
      <?php if (!empty($view['ticket']) && $rol === 'Cliente' && !empty($view['ticket']['tecnico']) && $finalizado): ?>
          <div class="bloque-cliente calificacion">
            <hr>
            <h3>Calificar atención del técnico</h3>
            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/Calificar">
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
                <label>Estrellas:</label>
                <div class="rating">
                    <input type="radio" id="star5" name="stars" value="5"/><label for="star5" class="star">&#9733;</label>
                    <input type="radio" id="star4" name="stars" value="4"/><label for="star4" class="star">&#9733;</label>
                    <input type="radio" id="star3" name="stars" value="3"/><label for="star3" class="star">&#9733;</label>
                    <input type="radio" id="star2" name="stars" value="2"/><label for="star2" class="star">&#9733;</label>
                    <input type="radio" id="star1" name="stars" value="1"/><label for="star1" class="star">&#9733;</label>
                </div>
                <label>Comentario (opcional):</label>
                <input type="text" name="comment" class="asignar-input-rating asignar-input--small-ratign" placeholder="Tu experiencia"/>
                <button class="btn btn-primary-submit" type="submit">Enviar</button>
            </form>
          </div>
      <?php endif; ?>

      <!-- === BLOQUE TÉCNICO === -->
      <div class="bloque-tecnico">
        <?php if (!empty($view['ticket']) && $rol === 'Tecnico'): ?>
            <hr>
      <h3>Cambiar estado</h3>
      <?php if (!empty($view['tecnico']['acciones'])): ?>
        <?php $readyDiag = !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor']); ?>
        <?php foreach ($view['tecnico']['acciones'] as $accion): ?>
          <?php $label = (string)$accion['label']; $isFinDiag = (stripos($label,'diagnóstico finalizado') !== false || stripos($label,'diagnostico finalizado') !== false); ?>
          <?php if ($isFinDiag && !$readyDiag) continue; ?>
          <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/ActualizarEstado" class="inline-form">
            <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
            <input type="hidden" name="estado_id" value="<?= (int)$accion['estado_id'] ?>" />
            <input type="hidden" name="comentario" value="<?= htmlspecialchars($accion['comentario'], ENT_QUOTES, 'UTF-8') ?>" />
            <button class="btn btn-primary" type="submit"><?= htmlspecialchars($accion['label']) ?></button>
          </form>
        <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($view['tecnico']['mensaje'])): ?>
                <div class="alert <?= empty($view['tecnico']['acciones']) ? 'alert-warning' : 'alert-info' ?>"><?= htmlspecialchars($view['tecnico']['mensaje']) ?></div>
            <?php endif; ?>

      <div class="mano-obra">
                <h4>Mano de obra</h4>

                <?php $ready = !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor']); ?>
                <div class="alert <?= $ready ? 'alert-success':'alert-warning' ?>">
                    <?= $ready ? 'Presupuesto listo para publicar.' : 'Para preparar el presupuesto: agrega repuestos y define mano de obra.' ?>
                </div>

    <?php if (!empty($view['tecnico']['labor_editable'])): ?>
                    <form method="post" action="/ProyectoPandora/Public/index.php?route=Tecnico/ActualizarStats" class="presu-labor">
                        <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
            <input type="hidden" name="rev_state" value="<?= htmlspecialchars((string)($view['rev_state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"/>
            <label>Importe:</label>
            <input type="number" name="labor_amount" step="0.01" min="0" class="asignar-input asignar-input--small" required />
                        <button class="btn btn-primary" type="submit">Guardar mano de obra</button>
                    </form>
        <?php elseif (!empty($view['tecnico']['labor_editable_en_espera'])): ?>
          <form method="post" action="/ProyectoPandora/Public/index.php?route=Tecnico/ActualizarStats" class="presu-labor">
            <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
            <input type="hidden" name="rev_state" value="<?= htmlspecialchars((string)($view['rev_state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"/>
            <label>Importe:</label>
            <input type="number" name="labor_amount" step="0.01" min="0" class="asignar-input asignar-input--small" value="<?= htmlspecialchars((string)($view['presupuesto']['mano_obra'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required />
            <button class="btn btn-primary" type="submit">Editar mano de obra</button>
          </form>
        <?php else: ?>
      <?php $lb = (float)($view['presupuesto']['mano_obra'] ?? 0); ?>
                    <div class="alert alert-info">
            Mano de obra <?= $lb > 0 ? 'definida' : 'no disponible para edición' ?><?= $lb > 0 ? ': <strong>'.LogFormatter::monto((float)$lb).'</strong>' : '' ?>.
            <?= ($estadoLower!=='diagnóstico' && $estadoLower!=='diagnostico') ? 'Solo editable durante Diagnóstico.' : '' ?>
                    </div>
                <?php endif; ?>
            </div>

      <?php
        // Botón para gestionar repuestos: durante Diagnóstico (añadir) o en Espera con diagnóstico listo (editar)
        if (in_array($estadoLower, ['diagnóstico','diagnostico'])): ?>
                <div style="margin-top:12px;">
                  <a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id=<?= (int)$view['ticket']['id'] ?>&rev=<?= urlencode((string)($view['rev_state'] ?? '')) ?>">Añadir repuestos a este ticket</a>
                </div>
      <?php elseif ($estadoLower === 'en espera' && !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor'])): ?>
        <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                  <a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id=<?= (int)$view['ticket']['id'] ?>&rev=<?= urlencode((string)($view['rev_state'] ?? '')) ?>">Editar repuestos</a>
        </div>
      <?php endif; ?>
        <?php endif; ?>
      </div> <!-- .bloque-tecnico -->

      <!-- === BLOQUE SUPERVISOR === -->
      <div class="bloque-supervisor">
        <?php if (!empty($view['ticket']) && $rol === 'Supervisor'): ?>
            <hr>
            <h3>Acciones del supervisor</h3>
            <?php if (!empty($view['supervisor']['puede_listo'])): ?>
                <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/MarcarListoParaRetirar" class="inline-form">
                    <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                    <button class="btn btn-primary" type="submit">Marcar listo para retirar</button>
                </form>
            <?php endif; ?>
            <?php if (!empty($view['supervisor']['puede_finalizar'])): ?>
                <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/MarcarPagadoYFinalizar" class="inline-form">
                    <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                    <button class="btn btn-success" type="submit">Registrar pago y finalizar</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
      </div> <!-- .bloque-supervisor -->

      <a href="<?= htmlspecialchars($view['backHref'] ?? '/ProyectoPandora/Public/index.php?route=Default/Index') ?>" class="boton-volver">Volver</a>

      <?php
        // Galería de fotos del ticket (si el controlador las provee)
        $fotos = $view['fotos_ticket'] ?? [];
        if (!empty($fotos)):
      ?>
        <hr>
        <h3>Fotos del ticket</h3>
        <div class="galeria-slider" style="display:flex; gap:8px; overflow-x:auto; padding:6px 0;">
          <?php foreach ($fotos as $src): ?>
            <img src="<?= htmlspecialchars($src) ?>" alt="Foto ticket" style="height:140px; border-radius:8px; object-fit:cover;"/>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- === OVERLAY PAGADO === -->
      <?php
          $mostrarPagadoOverlay = false;
          if ((!empty($view['flash']['ok']) && $view['flash']['ok']==='pagado') || $finalizado) {
              require_once __DIR__ . '/../../Core/Database.php';
              require_once __DIR__ . '/../../Models/Rating.php';
              $dbx = new Database(); $dbx->connectDatabase();
              $rtM = new RatingModel($dbx->getConnection());
              $rt = $rtM->getByTicket((int)$view['ticket']['id']);
              $mostrarPagadoOverlay = !empty($rt) && (int)($rt['stars'] ?? 0) > 0;
              if (!$mostrarPagadoOverlay && $finalizado && $rol === 'Cliente') {
                  echo '<div class="alert alert-warning">Tu ticket está finalizado. Por favor, califica la atención para completar el cierre.</div>';
              }
          }
      ?>
      <?php if ($mostrarPagadoOverlay): ?>
      <div class="overlay-pagado">
          <div class="overlay-box">PAGADO</div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ================== LÍNEA DE TIEMPO DERECHA ================== -->
    <div class="timeline-box">
      <div class="Tabla-Contenedor">
        <h3 class="titulo-timeline">Línea de tiempo</h3>
        <div class="timeline-2col">
          <div>
            <strong>Técnico</strong>
            <ul>
              <?php foreach (($view['timeline']['Tecnico'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div>Estado: <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
                  <?php if (!empty($ev['comentario'])): ?><div>"<?= htmlspecialchars($ev['comentario']) ?>"</div><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong>Cliente</strong>
            <ul>
              <?php foreach (($view['timeline']['Cliente'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div>Estado: <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
                  <?php if (!empty($ev['comentario'])): ?><div>"<?= htmlspecialchars($ev['comentario']) ?>"</div><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong>Supervisor</strong>
            <ul>
              <?php foreach (($view['timeline']['Supervisor'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div>Estado: <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
                  <?php if (!empty($ev['comentario'])): ?><div>"<?= htmlspecialchars($ev['comentario']) ?>"</div><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
(function(){
  const badge = document.getElementById('estado-badge');
  const id = <?= (int)($view['ticket']['id'] ?? 0) ?>;
  if (!badge || !id) return;
  const classFor = (txt)=>{
      const t = (txt||'').toLowerCase();
      if (["finalizado","cerrado","cancelado"].includes(t)) return 'badge badge--muted';
      if (["presupuesto","en espera","pendiente"].includes(t)) return 'badge badge--warning';
      if (["en reparación","diagnóstico","diagnostico","reparando"].includes(t)) return 'badge badge--info';
      return 'badge badge--success';
  };
  badge.className = classFor(badge.textContent);
})();
</script>
