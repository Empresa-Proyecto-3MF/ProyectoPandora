<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>

<main class="inv-page">
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <section class="content asignar-content">


        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Solicitud registrada correctamente.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'stock'): ?>
            <div class="alert alert-error">Stock insuficiente para la cantidad solicitada.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'locked'): ?>
            <div class="alert alert-warning">Edición bloqueada: el presupuesto ya fue publicado.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'stale'): ?>
            <div class="alert alert-warning">Tu vista quedó desactualizada. Actualiza la página del ticket.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'labor_required'): ?>
            <div class="alert alert-info">Primero debes definir la mano de obra antes de agregar repuestos en En espera.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'inventario'): ?>
            <div class="alert alert-error">El item de inventario no existe.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'ticket'): ?>
            <div class="alert alert-error">No tienes permisos sobre este ticket.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'tecnico'): ?>
            <div class="alert alert-error">No se pudo identificar tu perfil de técnico.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-error">No se pudo procesar la solicitud.</div>
        <?php endif; ?>

        <div class="asignar-panel">
            <div class="Tabla-Contenedor">
                <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap; justify-content:space-between; margin-bottom:12px;">
                    <div>
                        <h2 style="margin:0;">Solicitar repuesto</h2>
                        <?php $prefijado = isset($ticket_id) && (int)$ticket_id > 0; ?>
                        <small>
                            <?= $prefijado ? 'Agregando repuestos para el ticket #' . (int)$ticket_id : 'Selecciona el ticket y filtra el inventario.' ?>
                        </small>
                        <?php if ($prefijado): ?>
                            <div style="margin-top:8px;">
                                <a class="btn btn-secondary" href="/ProyectoPandora/Public/index.php?route=Ticket/Ver&id=<?php echo (int)$ticket_id; ?>">← Volver al ticket</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="get" action="/ProyectoPandora/Public/index.php" style="display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
                        <input type="hidden" name="route" value="Tecnico/MisRepuestos" />
                        <?php if (!empty($_GET['rev'])): ?>
                            <input type="hidden" name="rev" value="<?php echo htmlspecialchars((string)$_GET['rev']); ?>" />
                        <?php endif; ?>
                        <?php if (!$prefijado): ?>
                            <div>
                                <label class="asignar-label" for="ticket_id">Ticket:</label>
                                <select class="asignar-input" name="ticket_id" id="ticket_id">
                                    <?php foreach (($tickets ?? []) as $t): ?>
                                        <option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($ticket_id) && (int)$ticket_id === (int)$t['id']) ? 'selected' : ''; ?>>
                                            #<?php echo (int)$t['id']; ?> · <?php echo htmlspecialchars($t['marca'] . ' ' . $t['modelo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="ticket_id" id="ticket_id" value="<?php echo (int)$ticket_id; ?>" />
                        <?php endif; ?>
                        <div>
                            <label class="asignar-label" for="categoria_id">Categoría:</label>
                            <select class="asignar-input" name="categoria_id" id="categoria_id">
                                <option value="">Todas</option>
                                <?php foreach (($categorias ?? []) as $c): ?>
                                    <option value="<?php echo (int)$c['id']; ?>" <?php echo (isset($categoria_id) && (int)$categoria_id === (int)$c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="asignar-label" for="q">Buscar:</label>
                            <input class="asignar-input" type="text" id="q" name="q" placeholder="Nombre del item" value="<?php echo htmlspecialchars($buscar ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="asignar-label" for="sort">Ordenar por:</label>
                            <select class="asignar-input" name="sort" id="sort">
                                <?php $sortSel = $sort ?? 'i.id'; ?>
                                <option value="i.id" <?php echo ($sortSel === 'i.id' ? 'selected' : ''); ?>>ID</option>
                                <option value="i.name_item" <?php echo ($sortSel === 'i.name_item' ? 'selected' : ''); ?>>Nombre</option>
                                <option value="c.name" <?php echo ($sortSel === 'c.name' ? 'selected' : ''); ?>>Categoría</option>
                                <option value="i.valor_unitario" <?php echo ($sortSel === 'i.valor_unitario' ? 'selected' : ''); ?>>Precio</option>
                                <option value="i.stock_actual" <?php echo ($sortSel === 'i.stock_actual' ? 'selected' : ''); ?>>Stock</option>
                                <option value="i.stock_minimo" <?php echo ($sortSel === 'i.stock_minimo' ? 'selected' : ''); ?>>Stock Mín.</option>
                            </select>
                        </div>
                        <div>
                            <label class="asignar-label" for="dir">Dirección:</label>
                            <?php $dirSel = strtoupper($dir ?? 'DESC'); ?>
                            <select class="asignar-input" name="dir" id="dir">
                                <option value="ASC" <?php echo ($dirSel === 'ASC' ? 'selected' : ''); ?>>Asc</option>
                                <option value="DESC" <?php echo ($dirSel === 'DESC' ? 'selected' : ''); ?>>Desc</option>
                            </select>
                        </div>
                        <div>
                            <label class="asignar-label" for="perPage">Por página:</label>
                            <?php $ppSel = (int)($perPage ?? 10); ?>
                            <select class="asignar-input" name="perPage" id="perPage">
                                <?php foreach ([5, 10, 20, 30, 50] as $pp): ?>
                                    <option value="<?php echo $pp; ?>" <?php echo ($ppSel === $pp ? 'selected' : ''); ?>><?php echo $pp; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-outline" type="submit">Aplicar</button>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Img</th>
                            <th>Categoria</th>
                            <th>Item</th>
                            <th>Precio Unit.</th>
                            <th>Stock</th>
                            <th>Solicitar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($items ?? []) as $row): ?>
                            <tr>
                                <td>
                                    <?php
                                    $foto = $row['foto_item'] ?? '';
                                    $imgSrc = $foto
                                        ? '/ProyectoPandora/Public/img/imgInventario/' . $foto
                                        : '/ProyectoPandora/Public/img/imgInventario/images.jpg';
                                    ?>
                                    <img class="inv-thumb" src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($row['name_item']); ?>" />
                                </td>
                                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($row['name_item']); ?></td>
                                <td><span class="precio" data-precio="<?php echo (float)$row['valor_unitario']; ?>"><?php echo htmlspecialchars(LogFormatter::monto((float)$row['valor_unitario'])); ?></span></td>
                                <td><?php echo (int)$row['stock_actual']; ?><?php if ((int)$row['stock_actual'] <= 0) echo ' <span class="badge badge--muted">Sin stock</span>'; ?></td>
                                <td>
                                    <form class="req-form" action="/ProyectoPandora/Public/index.php?route=Tecnico/SolicitarRepuesto" method="post">
                                        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket_id; ?>" />
                                        <input type="hidden" name="inventario_id" value="<?php echo (int)$row['id']; ?>" />
                                        <?php if (!empty($_GET['rev'])): ?>
                                            <input type="hidden" name="rev_state" value="<?php echo htmlspecialchars((string)$_GET['rev']); ?>" />
                                        <?php endif; ?>
                                        <input type="number" name="cantidad" min="1" step="1" max="<?php echo (int)$row['stock_actual']; ?>" class="asignar-input asignar-input--small js-cantidad" placeholder="cant" inputmode="numeric" pattern="[0-9]*" <?php echo ((int)$row['stock_actual'] <= 0) ? 'disabled' : ''; ?> required />
                                        <span class="eq">=</span>
                                        <span class="currency">$</span>
                                        <strong class="js-total">0.00</strong>
                                        <button class="btn btn-primary" type="submit" <?php echo ((int)$row['stock_actual'] <= 0) ? 'disabled' : ''; ?>>Solicitar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="asignar-panel">
            <div class="Tabla-Contenedor">
                <h2 style="margin:0 0 12px 0;">Solicitudes del ticket #<?php echo (int)$ticket_id; ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Img</th>
                            <th>Categoría</th>
                            <th>Item</th>
                            <th>Cant.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($items_ticket ?? []) as $it): ?>
                            <tr>
                                <td>
                                    <time title="<?php echo htmlspecialchars(DateHelper::exact($it['fecha_asignacion'] ?? '')); ?>">
                                        <?php echo htmlspecialchars(DateHelper::smart($it['fecha_asignacion'] ?? '')); ?>
                                    </time>
                                </td>
                                <td>
                                    <?php
                                    $foto = $it['foto_item'] ?? '';
                                    $imgSrc = $foto
                                        ? '/ProyectoPandora/Public/img/imgInventario/' . $foto
                                        : '/ProyectoPandora/Public/img/imgInventario/images.jpg';
                                    ?>
                                    <img class="inv-thumb" src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($it['name_item']); ?>" />
                                </td>
                                <td><?php echo htmlspecialchars($it['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($it['name_item']); ?></td>
                                <td><?php echo (int)$it['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars(LogFormatter::monto((float)$it['valor_total'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items_ticket)): ?>
                            <tr>
                                <td colspan="6">Aún no hay solicitudes para este ticket.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <nav style="display:flex; gap:6px; justify-content:flex-end; margin-top:12px;">
                <?php
                $curr = (int)($page ?? 1);
                $buildUrl = function ($p) use ($ticket_id, $categoria_id, $buscar, $sort, $dir, $perPage) {
                    $q = http_build_query([
                        'route' => 'Tecnico/MisRepuestos',
                        'ticket_id' => $ticket_id,
                        'categoria_id' => $categoria_id,
                        'q' => $buscar,
                        'sort' => $sort,
                        'dir' => $dir,
                        'perPage' => $perPage,
                        'page' => $p,
                        'rev' => isset($_GET['rev']) ? (string)$_GET['rev'] : null,
                    ]);
                    return '/ProyectoPandora/Public/index.php?' . $q;
                };
                ?>
                <a class="btn btn-outline" href="<?php echo htmlspecialchars($buildUrl(max(1, $curr - 1))); ?>">« Prev</a>
                <span style="align-self:center;">Página <?php echo $curr; ?> de <?php echo (int)$totalPages; ?></span>
                <a class="btn btn-outline" href="<?php echo htmlspecialchars($buildUrl(min($totalPages, $curr + 1))); ?>">Next »</a>
            </nav>
        <?php endif; ?>
    </section>
</main>

<script>
    // Calcula el total dinámico por fila
    document.querySelectorAll('tr').forEach(function(row) {
        const precioEl = row.querySelector('.precio');
        const inputCant = row.querySelector('.js-cantidad');
        const totalEl = row.querySelector('.js-total');
        if (!precioEl || !inputCant || !totalEl) return;
        const precio = parseFloat(precioEl.dataset.precio || '0');

        function update() {
            let cant = parseInt(inputCant.value || '0');
            const max = parseInt(inputCant.getAttribute('max') || '0');
            if (isNaN(cant) || cant < 0) {
                // No tocar el valor del input si el usuario está escribiendo
                cant = 0;
            } else if (max && cant > max) {
                cant = max;
                inputCant.value = String(max);
            }
            const total = (cant > 0 && !isNaN(precio)) ? (cant * precio) : 0;
            totalEl.textContent = total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        inputCant.addEventListener('input', update);
        update();
    });
</script>