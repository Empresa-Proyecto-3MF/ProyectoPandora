<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/Date.php'; ?>
<main>
    <?php include_once __DIR__ . '/../Includes/Header.php'; ?>

    <form method="get" action="/ProyectoPandora/Public/index.php" class="filtros" style="display:flex;gap:10px;flex-wrap:nowrap;margin:10px 0;align-items:center;overflow-x:auto;">
            <input type="hidden" name="route" value="Historial/ListarHistorial" />
        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="asignar-input asignar-input--small" type="text" placeholder="Buscar texto..." style="min-width:220px;" />
            <input name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
            <input name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>" class="asignar-input asignar-input--small" type="date" />
            <select name="tipo" class="asignar-input asignar-input--small">
                <?php $tipoSel = strtolower($_GET['tipo'] ?? ''); ?>
                <option value="" <?= $tipoSel===''?'selected':'' ?>>Todos los tipos</option>
                <option value="ticket" <?= $tipoSel==='ticket'?'selected':'' ?>>Ticket</option>
                <option value="inventario" <?= $tipoSel==='inventario'?'selected':'' ?>>Inventario</option>
                <option value="usuario" <?= $tipoSel==='usuario'?'selected':'' ?>>Usuario</option>
                <option value="estado" <?= $tipoSel==='estado'?'selected':'' ?>>Estado</option>
            </select>
            <select name="perPage" class="asignar-input asignar-input--small">
                <?php $pp = (int)($_GET['perPage'] ?? ($perPage ?? 20)); ?>
                <?php foreach ([10,20,50,100] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $pp===$opt?'selected':'' ?>><?= $opt ?>/pág</option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a href="/ProyectoPandora/Public/index.php?route=Historial/ListarHistorial" class="btn btn-outline">Limpiar</a>
        </form>

        <table id="tablaHistorial">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Acción</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historial as $mov): ?>
                <tr data-fecha="<?= htmlspecialchars(substr($mov['fecha'],0,10)) ?>"
                    data-tipo="<?= htmlspecialchars(strtolower(preg_match('/ticket|inventario|usuario|estado/i', $mov['acciones'], $m) ? $m[0] : '')) ?>">
                    <td>
                        <time title="<?= htmlspecialchars($mov['fecha_exact'] ?? '') ?>">
                            <?= htmlspecialchars($mov['fecha_human'] ?? '') ?>
                        </time>
                    </td>
                    <td><?= htmlspecialchars($mov['acciones']) ?></td>
                    <td><?= htmlspecialchars($mov['detalles']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php 
          $page = isset($page) ? (int)$page : (int)($_GET['page'] ?? 1);
          $perPage = isset($perPage) ? (int)$perPage : (int)($_GET['perPage'] ?? 20);
          $total = isset($total) ? (int)$total : (int)count($historial);
          $totalPages = isset($totalPages) ? (int)$totalPages : (int)ceil(($total ?: 0) / ($perPage ?: 1));
          $q = $_GET['q'] ?? ''; $tipo = $_GET['tipo'] ?? ''; $desde = $_GET['desde'] ?? ''; $hasta = $_GET['hasta'] ?? '';
          $mkUrl = function($p) use($q,$tipo,$desde,$hasta,$perPage){
            $qs = http_build_query([
                'route' => 'Historial/ListarHistorial',
                'q' => $q,
                'tipo' => $tipo,
                'desde' => $desde,
                'hasta' => $hasta,
                'perPage' => $perPage,
                'page' => $p,
            ]);
            return '/ProyectoPandora/Public/index.php?'.$qs;
          };
        ?>
        <div class="paginacion" style="display:flex; gap:8px; align-items:center; justify-content:center; margin:12px 0;">
            <a class="btn btn-outline" href="<?= htmlspecialchars($mkUrl(1)) ?>" <?= $page<=1?'aria-disabled="true"':'' ?>>Primera</a>
            <a class="btn btn-outline" href="<?= htmlspecialchars($mkUrl(max(1,$page-1))) ?>" <?= $page<=1?'aria-disabled="true"':'' ?>>Anterior</a>
            <span>Página <?= (int)$page ?> de <?= (int)$totalPages ?></span>
            <a class="btn btn-outline" href="<?= htmlspecialchars($mkUrl(min($totalPages,$page+1))) ?>" <?= $page>=$totalPages?'aria-disabled="true"':'' ?>>Siguiente</a>
            <a class="btn btn-outline" href="<?= htmlspecialchars($mkUrl($totalPages)) ?>" <?= $page>=$totalPages?'aria-disabled="true"':'' ?>>Última</a>
        </div>
        <small style="opacity:.8;">Total de registros: <?= (int)$total ?></small>
</main>