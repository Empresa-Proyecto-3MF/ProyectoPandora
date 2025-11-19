<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<?php require_once __DIR__ . '/../../Core/ImageHelper.php'; ?>
<?php
$frontController = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
if ($frontController === '' || $frontController === '/') {
  $frontController = '/index.php';
}
$frontController = str_replace('\\', '/', $frontController);
?>

<main>
  <div class="detalle-ticket-layout">
    
    <div class="detalle-izquierda">
  <h2 id="tituloDetalle"><?= I18n::t('ticket.view.detailTitle') ?></h2>

      
      <div class="bloque-principal">
  <?php if (!empty($view['ticket'])): ?>
    <?php $t = $view['ticket']; ?>
    
    <ul class="detalle-grid" id="detalleTicket" style="list-style:none;padding:0;margin:0;">

  <li class="dato-item"><strong><?= I18n::t('ticket.field.idTicket') ?>:</strong><span><?= htmlspecialchars($t['id']) ?></span></li>
  <li class="dato-item"><strong><?= I18n::t('ticket.field.device') ?>:</strong><span data-field="device-nombre"><?= htmlspecialchars($t['marca']) ?> <?= htmlspecialchars($t['modelo']) ?></span></li>
  <li class="dato-item"><strong><?= I18n::t('ticket.field.client') ?>:</strong>
          <span data-field="cliente-nombre"><?= htmlspecialchars($t['cliente'] ?? $t['cliente_nombre'] ?? $t['user_name'] ?? I18n::t('common.notAvailable')) ?></span>
        </li>

        <?php
          // Usar la variable $t definida arriba para evitar undefined variable
          $estado = strtolower(trim($t['estado'] ?? ''));
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
          $estadoStr = ucfirst($estado);
        ?>
    <li class="dato-item">
      <strong><?= I18n::t('ticket.field.state') ?>:</strong>
            <span id="estado-badge" class="<?= htmlspecialchars($estadoClass) ?>">
                <?= htmlspecialchars($estadoStr) ?>
            </span>
        </li>



  <li class="dato-item"><strong><?= I18n::t('ticket.field.technicianAssigned') ?>:</strong>
          <span data-field="tecnico-nombre">
            <?= !empty($t['tecnico']) ? htmlspecialchars($t['tecnico']) : '<span class="sin-asignar">'.I18n::t('ticket.unassigned').'</span>' ?>
          </span>
        </li>

        <?php if (isset($t['fecha_creacion'])): ?>
          <li class="dato-item"><strong><?= I18n::t('ticket.field.creationDate') ?>:</strong>
            <time data-field="fecha-creacion" data-exact="<?= htmlspecialchars(DateHelper::exact($t['fecha_creacion'])) ?>" title="<?= htmlspecialchars(DateHelper::exact($t['fecha_creacion'])) ?>">
              <?= htmlspecialchars(DateHelper::smart($t['fecha_creacion'])) ?>
            </time>
          </li>
        <?php endif; ?>

        <?php $hasClose = !empty($t['fecha_cierre']); ?>
  <li class="dato-item" data-field="fecha-cierre-row"<?= $hasClose ? '' : ' style="display:none;"' ?>><strong><?= I18n::t('ticket.field.closeDate') ?>:</strong>
          <time data-field="fecha-cierre" data-exact="<?= $hasClose ? htmlspecialchars(DateHelper::exact($t['fecha_cierre'])) : '' ?>" title="<?= $hasClose ? htmlspecialchars(DateHelper::exact($t['fecha_cierre'])) : '' ?>">
            <?= $hasClose ? htmlspecialchars(DateHelper::smart($t['fecha_cierre'])) : '' ?>
          </time>
        </li>

        
        <li class="dato-item dato-larga descripcion-falla">
          <strong><?= I18n::t('ticket.field.description') ?>:</strong>
          <span data-field="descripcion-falla"><?= htmlspecialchars($t['descripcion'] ?? $t['descripcion_falla']) ?></span>
        </li>

        
        <?php
          $rawImg = trim((string)($t['img_dispositivo'] ?? ''));
          $resolvedImg = $rawImg !== '' ? device_image_url($rawImg) : '';
          $hasImg = $rawImg !== '' && $resolvedImg !== '';
        ?>
        <li class="dato-item imagen-card" data-field="device-image-card"<?= $hasImg ? '' : ' style="display:none;"' ?> >
          <h3 style="margin-top:0;"><?= I18n::t('ticket.field.deviceImage') ?></h3>
          <div class="imagen-contenedor">
            <img
              class="imagen-dispositivo"
              data-field="device-image"
              src="<?= $hasImg ? htmlspecialchars($resolvedImg) : '' ?>"
              alt="<?= I18n::t('ticket.field.deviceImage') ?>"
            >
          </div>
          <div class="imagen-pie">
            <?= I18n::t('ticket.image.loadedOn') ?> <?= htmlspecialchars($t['fecha_creacion'] ?? '---') ?>
          </div>
        </li>

    </ul>

  <?php else: ?>
  <div class="alert alert-danger"><?= I18n::t('ticket.view.notFound') ?></div>
  <?php endif; ?>
</div>

      
      <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='estado'): ?>
          <div class="alert alert-warning"><?= I18n::t('ticket.alert.rate.onlyFinal') ?></div>
      <?php endif; ?>
      <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='aprobacion'): ?>
          <div class="alert alert-warning"><?= I18n::t('ticket.alert.budget.pendingApproval') ?></div>
      <?php endif; ?>
    <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='presupuesto'): ?>
      <div class="alert alert-warning"><?= I18n::t('ticket.labor.hint') ?></div>
    <?php endif; ?>
    <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='transicion'): ?>
      <div class="alert alert-warning"><?= I18n::t('ticket.alert.transition.invalid') ?></div>
    <?php endif; ?>
    <?php if (!empty($view['flash']['error']) && $view['flash']['error']==='estado_actual'): ?>
      <div class="alert alert-warning"><?= I18n::t('ticket.alert.state.currentInvalid') ?></div>
    <?php endif; ?>
      <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='aprobado'): ?>
          <div class="alert alert-success"><?= I18n::t('ticket.alert.budget.approved') ?></div>
      <?php elseif (!empty($view['flash']['ok']) && $view['flash']['ok']==='rechazado'): ?>
          <div class="alert alert-warning"><?= I18n::t('ticket.alert.budget.rejected') ?></div>
      <?php endif; ?>
      <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='pagado'): ?>
          <div class="alert alert-success"><?= I18n::t('ticket.alert.payment.registered') ?></div>
      <?php endif; ?>
    <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='estado'): ?>
      <div class="alert alert-success"><?= I18n::t('ticket.success.stateUpdated') ?></div>
    <?php endif; ?>
    <?php if (!empty($view['flash']['ok']) && $view['flash']['ok']==='listo'): ?>
      <div class="alert alert-success"><?= I18n::t('ticket.success.markedReady') ?></div>
    <?php endif; ?>

      <?php
        $rol = $view['rol'] ?? '';
        $finalizado = !empty($view['finalizado']);
        $estadoLower = strtolower(trim($view['estadoStr'] ?? ''));
      ?>

      <?php if (!empty($view['ticket']) && $rol === 'Cliente' && !empty($view['ticket']['tecnico']) && !$finalizado): ?>
          <div class="alert alert-info"><?= I18n::t('ticket.alert.client.canRateWhenFinished') ?></div>
      <?php endif; ?>

      
      <div class="bloque-cliente">
        <?php if (!empty($view['ticket']) && $rol === 'Cliente'): ?>
          <?php if (!empty($view['enPresu'])): ?>
            <?php $p = $view['presupuesto']; ?>
            <?php $msgPrefix = ($estadoLower === 'presupuesto') ? I18n::t('ticket.budget.publishedBy') : I18n::t('ticket.budget.preparedBy'); ?>
            <div class="alert alert-info">
              <?= $msgPrefix ?> <strong><?= $p['total_fmt'] ?? LogFormatter::monto((float)$p['total']) ?></strong>.
              <?php if ($estadoLower === 'en espera'): ?> <span><?= I18n::t('ticket.budget.pendingSupervisor') ?></span><?php endif; ?>
            </div>

            <div class="Tabla-Contenedor presu-tabla">
                <table>
                    <thead><tr><th><?= I18n::t('ticket.budget.table.item') ?></th><th><?= I18n::t('ticket.budget.table.category') ?></th><th><?= I18n::t('ticket.budget.table.qty') ?></th><th><?= I18n::t('ticket.budget.table.subtotal') ?></th></tr></thead>
                    <tbody>
                    <?php if (empty($p['items'])): ?>
                        <tr><td colspan="4"><?= I18n::t('ticket.budget.noParts') ?></td></tr>
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
                  <div><?= I18n::t('ticket.budget.subtotalParts') ?>: <strong><?= htmlspecialchars($p['subtotal_fmt'] ?? LogFormatter::monto((float)$p['subtotal'])) ?></strong></div>
                  <div><?= I18n::t('ticket.budget.labor') ?>: <strong><?= htmlspecialchars($p['mano_obra_fmt'] ?? LogFormatter::monto((float)$p['mano_obra'])) ?></strong>
                        <?php if ((float)$p['mano_obra'] <= 0): ?>
                            <span class="badge badge--muted"><?= I18n::t('ticket.budget.laborMissing') ?></span>
                        <?php endif; ?>
                    </div>
                  <div><?= I18n::t('ticket.budget.total') ?>: <strong><?= htmlspecialchars($p['total_fmt'] ?? LogFormatter::monto((float)$p['total'])) ?></strong></div>
                </div>
            </div>

            <?php if ($estadoLower === 'presupuesto'): ?>
      <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/AprobarPresupuesto" class="inline-form">
        <?= Csrf::input(); ?>
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                <input type="hidden" name="comentario" value="<?= I18n::t('ticket.budget.comment.approvedByClient') ?>" />
                <button class="btn btn-success" type="submit"><?= I18n::t('ticket.budget.approve') ?></button>
            </form>
      <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/RechazarPresupuesto" class="inline-form">
        <?= Csrf::input(); ?>
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                <input type="hidden" name="comentario" value="<?= I18n::t('ticket.budget.comment.rejectedByClient') ?>" />
                <button class="btn btn-danger" type="submit"><?= I18n::t('ticket.budget.reject') ?></button>
            </form>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div> 

      
      <?php if (!empty($view['ticket']) && $rol === 'Cliente' && !empty($view['ticket']['tecnico']) && $finalizado): ?>
          <div class="bloque-cliente calificacion">
            <hr>
            <h3><?= I18n::t('ticket.rating.title') ?></h3>
      <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/Calificar">
        <?= Csrf::input(); ?>
                <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
                <label><?= I18n::t('ticket.rating.stars') ?></label>
                <div class="rating">
                    <input type="radio" id="star5" name="stars" value="5"/><label for="star5" class="star">&#9733;</label>
                    <input type="radio" id="star4" name="stars" value="4"/><label for="star4" class="star">&#9733;</label>
                    <input type="radio" id="star3" name="stars" value="3"/><label for="star3" class="star">&#9733;</label>
                    <input type="radio" id="star2" name="stars" value="2"/><label for="star2" class="star">&#9733;</label>
                    <input type="radio" id="star1" name="stars" value="1"/><label for="star1" class="star">&#9733;</label>
                </div>
                <label><?= I18n::t('ticket.rating.comment.optional') ?></label>
                <input type="text" name="comment" class="asignar-input-rating asignar-input--small-ratign" placeholder="<?= I18n::t('ticket.rating.placeholder') ?>"/>
                <button class="btn btn-primary-submit" type="submit"><?= I18n::t('common.send') ?></button>
            </form>
          </div>
      <?php endif; ?>

      
      <div class="bloque-tecnico">
        <?php if (!empty($view['ticket']) && $rol === 'Tecnico'): ?>
            <hr>
  <h3><?= I18n::t('ticket.tech.changeState') ?></h3>
      <?php if (!empty($view['tecnico']['acciones'])): ?>
        <?php $readyDiag = !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor']); ?>
        <?php foreach ($view['tecnico']['acciones'] as $accion): ?>
          <?php $label = (string)$accion['label']; $isFinDiag = (stripos($label,'diagnóstico finalizado') !== false || stripos($label,'diagnostico finalizado') !== false); ?>
          <?php if ($isFinDiag && !$readyDiag) continue; ?>
          <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/ActualizarEstado" class="inline-form">
            <?= Csrf::input(); ?>
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
                <h4><?= I18n::t('ticket.labor.title') ?></h4>

                <?php $ready = !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor']); ?>
        <div class="alert <?= $ready ? 'alert-success':'alert-warning' ?>">
          <?= $ready ? I18n::t('ticket.labor.ready') : I18n::t('ticket.labor.hint') ?>
                </div>

    <?php if (!empty($view['tecnico']['labor_editable'])): ?>
          <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Tecnico/ActualizarStats" class="presu-labor">
            <?= Csrf::input(); ?>
                        <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
            <input type="hidden" name="rev_state" value="<?= htmlspecialchars((string)($view['rev_state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"/>
            <label><?= I18n::t('ticket.labor.amount') ?></label>
            <input type="number" name="labor_amount" step="0.01" min="0" class="asignar-input asignar-input--small" required />
                        <button class="btn btn-primary" type="submit"><?= I18n::t('ticket.labor.save') ?></button>
                    </form>
        <?php elseif (!empty($view['tecnico']['labor_editable_en_espera'])): ?>
          <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Tecnico/ActualizarStats" class="presu-labor">
            <?= Csrf::input(); ?>
            <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>"/>
            <input type="hidden" name="rev_state" value="<?= htmlspecialchars((string)($view['rev_state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"/>
            <label><?= I18n::t('ticket.labor.amount') ?></label>
            <input type="number" name="labor_amount" step="0.01" min="0" class="asignar-input asignar-input--small" value="<?= htmlspecialchars((string)($view['presupuesto']['mano_obra'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required />
            <button class="btn btn-primary" type="submit"><?= I18n::t('ticket.labor.edit') ?></button>
          </form>
        <?php else: ?>
      <?php $lb = (float)($view['presupuesto']['mano_obra'] ?? 0); ?>
                    <div class="alert alert-info">
            <?= I18n::t('ticket.labor.title') ?> <?= $lb > 0 ? I18n::t('ticket.labor.defined') : I18n::t('ticket.labor.notEditable') ?><?= $lb > 0 ? ': <strong>'.LogFormatter::monto((float)$lb).'</strong>' : '' ?>.
            <?= ($estadoLower!=='diagnóstico' && $estadoLower!=='diagnostico') ? I18n::t('ticket.labor.onlyDuringDiagnosis') : '' ?>
                    </div>
                <?php endif; ?>
            </div>

      <?php
        
        if (in_array($estadoLower, ['diagnóstico','diagnostico'])): ?>
                <div style="margin-top:12px;">
                  <a class="btn btn-outline" href="<?= htmlspecialchars($frontController) ?>?route=Tecnico/MisRepuestos&ticket_id=<?= (int)$view['ticket']['id'] ?>&rev=<?= urlencode((string)($view['rev_state'] ?? '')) ?>"><?= I18n::t('ticket.parts.addToThisTicket') ?></a>
                </div>
      <?php elseif ($estadoLower === 'en espera' && !empty($view['tecnico']['has_items']) && !empty($view['tecnico']['has_labor'])): ?>
        <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                  <a class="btn btn-outline" href="<?= htmlspecialchars($frontController) ?>?route=Tecnico/MisRepuestos&ticket_id=<?= (int)$view['ticket']['id'] ?>&rev=<?= urlencode((string)($view['rev_state'] ?? '')) ?>"><?= I18n::t('ticket.parts.edit') ?></a>
        </div>
      <?php endif; ?>
        <?php endif; ?>
      </div> 

      
      <div class="bloque-supervisor">
        <?php if (!empty($view['ticket']) && $rol === 'Supervisor'): ?>
            <hr>
            <h3><?= I18n::t('ticket.supervisor.actions') ?></h3>
            <?php if (!empty($view['supervisor']['puede_listo'])): ?>
        <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/MarcarListoParaRetirar" class="inline-form">
          <?= Csrf::input(); ?>
                    <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                    <button class="btn btn-primary" type="submit"><?= I18n::t('ticket.supervisor.markReadyForPickup') ?></button>
                </form>
            <?php endif; ?>
            <?php if (!empty($view['supervisor']['puede_finalizar'])): ?>
        <form method="post" action="<?= htmlspecialchars($frontController) ?>?route=Ticket/MarcarPagadoYFinalizar" class="inline-form">
          <?= Csrf::input(); ?>
                    <input type="hidden" name="ticket_id" value="<?= (int)$view['ticket']['id'] ?>" />
                    <button class="btn btn-success" type="submit"><?= I18n::t('ticket.supervisor.registerPaymentAndFinish') ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
      </div> 

  <a href="<?= htmlspecialchars($view['backHref'] ?? ($frontController . '?route=Default/Index')) ?>" class="boton-volver"><?= I18n::t('common.back') ?></a>

      <?php
        
        $fotos = $view['fotos_ticket'] ?? [];
        if (!empty($fotos)):
      ?>
        <hr>
  <h3><?= I18n::t('ticket.photos.title') ?></h3>
        <div class="galeria-slider" style="display:flex; gap:8px; overflow-x:auto; padding:6px 0;">
          <?php foreach ($fotos as $src): ?>
            <img src="<?= htmlspecialchars($src) ?>" alt="<?= I18n::t('ticket.photos.alt') ?>" style="height:140px; border-radius:8px; object-fit:cover;"/>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    
    <?php if (!empty($view['mostrarPagadoOverlay'])): ?>
      <div class="overlay-pagado">
          <div class="overlay-box"><?= I18n::t('ticket.overlay.paid') ?></div>
      </div>
      <?php endif; ?>
    <?php if (!empty($view['debeCalificar'])): ?>
  <div class="alert alert-warning"><?= I18n::t('ticket.alert.mustRateToClose') ?></div>
    <?php endif; ?>
  <?php if (!empty($view['flash']['rated'])): ?>
  <div class="alert alert-success"><?= I18n::t('ticket.rating.thanks') ?></div>
  <?php endif; ?>
    </div>

    
    <div class="timeline-box">
      <div class="Tabla-Contenedor">
  <h3 class="titulo-timeline"><?= I18n::t('ticket.timeline.title') ?></h3>
        <div class="timeline-2col">
          <div>
            <strong><?= I18n::t('roles.technician') ?></strong>
            <ul data-timeline="Tecnico">
              <?php foreach (($view['timeline']['Tecnico'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div><?= I18n::t('ticket.timeline.state') ?> <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
                  <?php if (!empty($ev['comentario'])): ?><div>"<?= htmlspecialchars($ev['comentario']) ?>"</div><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong><?= I18n::t('roles.client') ?></strong>
            <ul data-timeline="Cliente">
              <?php foreach (($view['timeline']['Cliente'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div><?= I18n::t('ticket.timeline.state') ?> <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
                  <?php if (!empty($ev['comentario'])): ?><div>"<?= htmlspecialchars($ev['comentario']) ?>"</div><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong><?= I18n::t('roles.supervisor') ?></strong>
            <ul data-timeline="Supervisor">
              <?php foreach (($view['timeline']['Supervisor'] ?? []) as $ev): ?>
                <li>
                  <div class="timeline-fecha">
                    <time title="<?= htmlspecialchars($ev['fecha_exact'] ?? '') ?>">
                      <?= htmlspecialchars($ev['fecha_human'] ?? '') ?>
                    </time>
                  </div>
                  <div><?= I18n::t('ticket.timeline.state') ?> <span class="<?= htmlspecialchars($ev['badge_class'] ?? 'badge') ?>"><?= htmlspecialchars($ev['estado']) ?></span></div>
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

<script src="js/ticket-ver.js" defer></script>
