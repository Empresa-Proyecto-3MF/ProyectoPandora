<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<?php I18n::boot(); ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
<div class="Contenedor">
    <section class="section-presupuestos">
        <form method="get" action="/ProyectoPandora/Public/index.php" class="presu-filtros">
            <input type="hidden" name="route" value="Supervisor/Presupuestos">
            <label for="ticket_id"><?= __('supervisor.budgets.filter.ticketId'); ?></label>
            <input type="number" name="ticket_id" id="ticket_id" min="1" 
                   value="<?= htmlspecialchars($_GET['ticket_id'] ?? '') ?>" 
                   class="asignar-input asignar-input--small"/>
            <?php $cierreSel = strtolower($_GET['cierre'] ?? 'todos'); ?>
            <label for="cierre"><?= __('supervisor.budgets.filter.closure'); ?></label>
            <select name="cierre" id="cierre" class="asignar-input asignar-input--small">
                <option value="todos" <?= $cierreSel==='todos'?'selected':'' ?>><?= __('supervisor.budgets.filter.closure.all'); ?></option>
                <option value="activos" <?= $cierreSel==='activos'?'selected':'' ?>><?= __('supervisor.budgets.filter.closure.active'); ?></option>
                <option value="finalizados" <?= $cierreSel==='finalizados'?'selected':'' ?>><?= __('supervisor.budgets.filter.closure.closed'); ?></option>
            </select>
            <button class="btn btn-outline" type="submit"><?= __('supervisor.budgets.actions.filter'); ?></button>
            <a href="/ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos" class="btn btn-outline"><?= __('supervisor.budgets.actions.clear'); ?></a>
        </form>

        <?php if (empty($presupuestos)): ?>
            <p><?= __('supervisor.budgets.empty'); ?></p>
        <?php else: ?>
        <div class="presu-list">
            <?php foreach ($presupuestos as $p): $t = $p['ticket']; ?>
            <div class="presu-card">
                <div class="presu-head">
                    <h3>#<?= (int)$t['id'] ?> - <?= htmlspecialchars($t['dispositivo'] ?? ($t['marca'] ?? '')) ?> <?= htmlspecialchars($t['modelo'] ?? '') ?></h3>
                    <div class="presu-meta">
                        <span><?= __('supervisor.budgets.label.client'); ?>: <?= htmlspecialchars($t['cliente'] ?? '') ?></span>
                        <?php 
                            $estado = strtolower(trim($t['estado'] ?? ''));
                            $estadoClass = '';
                            switch ($estado) {
                                case 'diagnóstico':
                                case 'diagnostico':
                                    $estadoClass = 'badge--warning';
                                    break;
                                case 'en reparación':
                                case 'en reparacion':
                                    $estadoClass = 'badge--info';
                                    break;
                                case 'en pruebas':
                                    $estadoClass = 'badge--primary';
                                    break;
                                case 'listo para retirar':
                                    $estadoClass = 'badge--success';
                                    break;
                                case 'finalizado':
                                    $estadoClass = 'badge--green';
                                    break;
                                case 'cancelado':
                                    $estadoClass = 'badge--danger';
                                    break;
                                default:
                                    $estadoClass = 'badge--muted';
                            }
                        ?>
                        <span><?= __('supervisor.budgets.label.state'); ?>: <span class="badge <?= $estadoClass ?>"><?= htmlspecialchars($t['estado'] ?? '') ?></span></span>
                    </div>
                </div>

                <div class="presu-body">
                    <table class="presu-table">
                        <thead>
                            <tr>
                                <th><?= __('ticket.budget.table.item'); ?></th><th><?= __('ticket.budget.table.category'); ?></th><th><?= __('supervisor.budgets.table.qty'); ?></th><th><?= __('supervisor.budgets.table.unit'); ?></th><th><?= __('ticket.budget.table.subtotal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($p['items'])): ?>
                                <tr><td colspan="5"><?= __('supervisor.budgets.table.noPartsUsed'); ?></td></tr>
                            <?php else: ?>
                            <?php foreach ($p['items'] as $it): ?>
                                <tr>
                                    <td><?= htmlspecialchars($it['name_item']) ?></td>
                                    <td><?= htmlspecialchars($it['categoria']) ?></td>
                                    <td><?= (int)$it['cantidad'] ?></td>
                                    <td><?= LogFormatter::monto((float)$it['valor_unitario']) ?></td>
                                    <td><?= LogFormatter::monto((float)$it['valor_total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="presu-totales">
                        <div><?= __('supervisor.budgets.totals.subtotalParts'); ?> <strong><?= LogFormatter::monto((float)$p['subtotal_items']) ?></strong></div>

                        <?php 
                            $sEstado = strtolower(trim($t['estado'] ?? ''));
                            $laborDef = (float)($p['mano_obra'] ?? 0) > 0;
                            $editable = (in_array($sEstado, ['diagnóstico','diagnostico']) && !$laborDef);
                        ?>

                        <div class="presu-labor" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <label><?= __('ticket.budget.labor'); ?>:</label>
                            <?php if ($editable): ?>
                                <input type="number" name="labor_amount" step="0.01" min="0"
                                       value="<?= number_format((float)$p['mano_obra'], 2, '.', '') ?>"
                                       class="asignar-input asignar-input--small"/>
                                <span class="badge badge--warning"><?= __('supervisor.budgets.labor.onlyDiagnosis'); ?></span>
                            <?php else: ?>
                                <span><strong><?= LogFormatter::monto((float)$p['mano_obra']) ?></strong></span>
                                <span class="badge <?= $laborDef ? 'badge--success' : 'badge--warning' ?>">
                                    <?= $laborDef ? __('supervisor.budgets.labor.defined') : __('supervisor.budgets.labor.onlyDiagnosis') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div><?= __('ticket.budget.total'); ?>: <strong><?= LogFormatter::monto((float)$p['total']) ?></strong></div>

                        <?php 
                            $ready = ($p['subtotal_items'] > 0 && $p['mano_obra'] > 0);
                            $yaPublicado = ($sEstado === 'presupuesto');
                            $postAprobacion = in_array($sEstado, ['en reparación','en reparacion','en pruebas','listo para retirar','finalizado','cancelado'], true);
                        ?>
                        <?php if (!$postAprobacion): ?>
                                                        <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/PublicarPresupuesto" 
                                  style="margin-top:8px;display:flex;gap:8px;align-items:center;">
                                                                <?= Csrf::input(); ?>
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>"/>
                                <button class="btn btn-outline" type="submit" <?= ($ready && !$yaPublicado) ? '' : 'disabled' ?>><?= __('supervisor.budgets.publish'); ?></button>
                                <?php if ($yaPublicado): ?>
                                    <span class="badge badge--primary"><?= __('supervisor.budgets.badge.sentToClient'); ?></span>
                                <?php elseif (!$ready): ?>
                                    <span class="badge badge--danger"><?= __('supervisor.budgets.badge.missingData'); ?> <?= $p['mano_obra'] <= 0 ? __('ticket.budget.labor') : '' ?></span>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>

                        <?php 
                            $s = strtolower(trim($t['estado'] ?? ''));
                            $puedeListo = in_array($s, ['en reparación','en reparacion','en pruebas']);
                            $puedePagar = ($s === 'listo para retirar');
                        ?>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                            <?php if ($puedeListo): ?>
                            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/MarcarListoParaRetirar">
                                <?= Csrf::input(); ?>
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>" />
                                <button class="btn btn-primary" type="submit"><?= __('ticket.supervisor.markReadyForPickup'); ?></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($puedePagar): ?>
                            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/MarcarPagadoYFinalizar">
                                <?= Csrf::input(); ?>
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>" />
                                <button class="btn btn-success" type="submit"><?= __('ticket.supervisor.registerPaymentAndFinish'); ?></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>
</main>
