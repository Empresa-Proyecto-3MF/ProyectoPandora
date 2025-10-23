<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
<div class="Contenedor">
    <section class="section-presupuestos">
        <form method="get" action="/ProyectoPandora/Public/index.php" class="presu-filtros">
            <input type="hidden" name="route" value="Supervisor/Presupuestos">
            <label for="ticket_id">Ticket ID</label>
            <input type="number" name="ticket_id" id="ticket_id" min="1" 
                   value="<?= htmlspecialchars($_GET['ticket_id'] ?? '') ?>" 
                   class="asignar-input asignar-input--small"/>
            <?php $cierreSel = strtolower($_GET['cierre'] ?? 'todos'); ?>
            <label for="cierre">Cierre</label>
            <select name="cierre" id="cierre" class="asignar-input asignar-input--small">
                <option value="todos" <?= $cierreSel==='todos'?'selected':'' ?>>Todos</option>
                <option value="activos" <?= $cierreSel==='activos'?'selected':'' ?>>No finalizados</option>
                <option value="finalizados" <?= $cierreSel==='finalizados'?'selected':'' ?>>Finalizados</option>
            </select>
            <button class="btn btn-outline" type="submit">Filtrar</button>
            <a href="/ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos" class="btn btn-outline">Limpiar</a>
        </form>

        <?php if (empty($presupuestos)): ?>
            <p>No hay datos de tickets para mostrar.</p>
        <?php else: ?>
        <div class="presu-list">
            <?php foreach ($presupuestos as $p): $t = $p['ticket']; ?>
            <div class="presu-card">
                <div class="presu-head">
                    <h3>#<?= (int)$t['id'] ?> - <?= htmlspecialchars($t['dispositivo'] ?? ($t['marca'] ?? '')) ?> <?= htmlspecialchars($t['modelo'] ?? '') ?></h3>
                    <div class="presu-meta">
                        <span>Cliente: <?= htmlspecialchars($t['cliente'] ?? '') ?></span>
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
                        <span>Estado: <span class="badge <?= $estadoClass ?>"><?= htmlspecialchars($t['estado'] ?? '') ?></span></span>
                    </div>
                </div>

                <div class="presu-body">
                    <table class="presu-table">
                        <thead>
                            <tr>
                                <th>Ítem</th><th>Categoría</th><th>Cant.</th><th>Unitario</th><th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($p['items'])): ?>
                                <tr><td colspan="5">Sin repuestos utilizados</td></tr>
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
                        <div>Subtotal repuestos: <strong><?= LogFormatter::monto((float)$p['subtotal_items']) ?></strong></div>

                        <?php 
                            $sEstado = strtolower(trim($t['estado'] ?? ''));
                            $laborDef = (float)($p['mano_obra'] ?? 0) > 0;
                            $editable = (in_array($sEstado, ['diagnóstico','diagnostico']) && !$laborDef);
                        ?>

                        <div class="presu-labor" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <label>Mano de obra:</label>
                            <?php if ($editable): ?>
                                <input type="number" name="labor_amount" step="0.01" min="0"
                                       value="<?= number_format((float)$p['mano_obra'], 2, '.', '') ?>"
                                       class="asignar-input asignar-input--small"/>
                                <span class="badge badge--warning">Solo En Diagnóstico</span>
                            <?php else: ?>
                                <span><strong><?= LogFormatter::monto((float)$p['mano_obra']) ?></strong></span>
                                <span class="badge <?= $laborDef ? 'badge--success' : 'badge--warning' ?>">
                                    <?= $laborDef ? 'Ya Definida' : 'Solo En Diagnóstico' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div>Total: <strong><?= LogFormatter::monto((float)$p['total']) ?></strong></div>

                        <?php 
                            $ready = ($p['subtotal_items'] > 0 && $p['mano_obra'] > 0);
                            $yaPublicado = ($sEstado === 'presupuesto');
                            $postAprobacion = in_array($sEstado, ['en reparación','en reparacion','en pruebas','listo para retirar','finalizado','cancelado'], true);
                        ?>
                        <?php if (!$postAprobacion): ?>
                            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/PublicarPresupuesto" 
                                  style="margin-top:8px;display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>"/>
                                <button class="btn btn-outline" type="submit" <?= ($ready && !$yaPublicado) ? '' : 'disabled' ?>>Publicar presupuesto</button>
                                <?php if ($yaPublicado): ?>
                                    <span class="badge badge--primary">Enviado al cliente</span>
                                <?php elseif (!$ready): ?>
                                    <span class="badge badge--danger">Faltan datos: <?= $p['mano_obra'] <= 0 ? 'Mano de obra' : '' ?></span>
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
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>" />
                                <button class="btn btn-primary" type="submit">Marcar listo para retirar</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($puedePagar): ?>
                            <form method="post" action="/ProyectoPandora/Public/index.php?route=Ticket/MarcarPagadoYFinalizar">
                                <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>" />
                                <button class="btn btn-success" type="submit">Registrar pago y finalizar</button>
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
