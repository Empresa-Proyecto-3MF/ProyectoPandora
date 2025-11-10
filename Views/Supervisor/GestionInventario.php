<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<?php I18n::boot(); ?>

<main class="inv-page">
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
  <section class="content asignar-content">
    <section class="content asignar-content">
			<section class="content">

        
			</section>


    <?php $flashes = Flash::peek(); foreach ($flashes as $f): if ($f['type']==='success_quiet') continue; ?>
      <div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars(__($f['message'])) ?></div>
    <?php endforeach; ?>

    <div class="asignar-panel">
      <div class="Tabla-Contenedor">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h2 style="margin:0;"><?= __('inventory.stock.current'); ?></h2>
          <a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Inventario/MostrarCrearItem"><?= __('inventory.item.addNew'); ?></a>
        </div>
        <table id="userTable">
          <thead>
            <tr>
              <th><?= __('common.id'); ?></th>
              <th><?= __('inventory.table.image'); ?></th>
              <th><?= __('inventory.table.category'); ?></th>
              <th><?= __('inventory.table.item'); ?></th>
              <th><?= __('inventory.table.unitPrice'); ?></th>
              <th><?= __('inventory.table.stock'); ?></th>
			  <th><?= __('inventory.table.minStock'); ?></th>
              <th><?= __('common.actions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($items ?? []) as $row): ?>
              <?php $low = (int)$row['stock_actual'] <= (int)$row['stock_minimo']; ?>
              <tr class="<?php echo $low ? 'row-low-stock' : ''; ?>">
                <td data-label="<?= __('common.id'); ?>"><?php echo (int)$row['id']; ?></td>
                <td data-label="<?= __('inventory.table.image'); ?>">
                  <?php 
                    $foto = $row['foto_item'] ?? '';
                    $imgSrc = \Storage::resolveInventoryUrl($foto);
                  ?>
                  <img class="inv-thumb" src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($row['name_item']); ?>"/>
                </td>
        <td data-label="<?= __('inventory.table.category'); ?>"><?php echo htmlspecialchars($row['categoria']); ?></td>
        <td data-label="<?= __('inventory.table.item'); ?>"><?php echo htmlspecialchars($row['name_item']); ?></td>
        <td data-label="<?= __('inventory.table.unitPrice'); ?>"><?php echo htmlspecialchars(LogFormatter::monto((float)$row['valor_unitario'])); ?></td>
        <td data-label="<?= __('inventory.table.stock'); ?>"><?php echo (int)$row['stock_actual']; ?></td>
				<td data-label="<?= __('inventory.table.minStock'); ?>"><?php echo (int)$row['stock_minimo']; ?></td>
        <td data-label="<?= __('common.actions'); ?>">
                  <form action="/ProyectoPandora/Public/index.php?route=Inventario/SumarStock" method="post" style="display:flex; gap:6px; align-items:center;">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                    <input type="number" name="cantidad" min="1" class="asignar-input asignar-input--small" placeholder="<?= __('inventory.stock.addQtyPlaceholder'); ?>" required />
                    <button class="btn btn-primary" type="submit"><?= __('inventory.stock.add'); ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</main>
<script src="/ProyectoPandora/Public/js/modal.js"></script>scriptscript

