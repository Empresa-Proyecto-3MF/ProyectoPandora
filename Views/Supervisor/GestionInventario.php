<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php require_once __DIR__ . '/../../Core/LogFormatter.php'; ?>
<?php require_once __DIR__ . '/../../Core/ImageHelper.php'; ?>
<?php $fallbackInventoryImg = inventory_image_url(''); ?>
<?php I18n::boot(); ?>

<main class="inv-page">
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
  <section class="content asignar-content">
    <section class="content asignar-content">
			<section class="content">

        
			</section>


    <?php $flashes = Flash::peek(); foreach ($flashes as $f): if ($f['type']==='success_quiet') continue; ?>
      <div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars(I18n::t($f['message'])) ?></div>
    <?php endforeach; ?>

    <div class="asignar-panel">
      <div class="Tabla-Contenedor">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h2 style="margin:0;"><?= I18n::t('inventory.stock.current'); ?></h2>
          <a class="btn btn-outline" href="index.php?route=Inventario/MostrarCrearItem"><?= I18n::t('inventory.item.addNew'); ?></a>
        </div>
        <table id="userTable">
          <thead>
            <tr>
              <th><?= I18n::t('common.id'); ?></th>
              <th><?= I18n::t('inventory.table.image'); ?></th>
              <th><?= I18n::t('inventory.table.category'); ?></th>
              <th><?= I18n::t('inventory.table.item'); ?></th>
              <th><?= I18n::t('inventory.table.unitPrice'); ?></th>
              <th><?= I18n::t('inventory.table.stock'); ?></th>
			  <th><?= I18n::t('inventory.table.minStock'); ?></th>
              <th><?= I18n::t('common.actions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($items ?? []) as $row): ?>
              <?php $low = (int)$row['stock_actual'] <= (int)$row['stock_minimo']; ?>
              <tr class="<?php echo $low ? 'row-low-stock' : ''; ?>">
                <td data-label="<?= I18n::t('common.id'); ?>"><?php echo (int)$row['id']; ?></td>
                <td data-label="<?= I18n::t('inventory.table.image'); ?>">
                  <?php 
                    $foto = $row['foto_item'] ?? '';
                    $imgSrc = inventory_image_url($foto);
                  ?>
                  <img class="inv-thumb" src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($row['name_item']); ?>" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($fallbackInventoryImg, ENT_QUOTES, 'UTF-8'); ?>'" />
                </td>
        <td data-label="<?= I18n::t('inventory.table.category'); ?>"><?php echo htmlspecialchars($row['categoria']); ?></td>
        <td data-label="<?= I18n::t('inventory.table.item'); ?>"><?php echo htmlspecialchars($row['name_item']); ?></td>
        <td data-label="<?= I18n::t('inventory.table.unitPrice'); ?>"><?php echo htmlspecialchars(LogFormatter::monto((float)$row['valor_unitario'])); ?></td>
        <td data-label="<?= I18n::t('inventory.table.stock'); ?>"><?php echo (int)$row['stock_actual']; ?></td>
				<td data-label="<?= I18n::t('inventory.table.minStock'); ?>"><?php echo (int)$row['stock_minimo']; ?></td>
        <td data-label="<?= I18n::t('common.actions'); ?>">
                  <div style="display:flex; flex-direction:column; gap:8px;">
                    <form action="index.php?route=Inventario/SumarStock" method="post" style="display:flex; gap:6px; align-items:center;">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                      <input type="number" name="cantidad" min="1" class="asignar-input asignar-input--small" placeholder="<?= I18n::t('inventory.stock.addQtyPlaceholder'); ?>" required />
                      <button class="btn btn-primary" type="submit"><?= I18n::t('inventory.stock.add'); ?></button>
                    </form>
                    <form action="index.php?route=Inventario/ReducirStock" method="post" style="display:flex; gap:6px; align-items:center;">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                      <input type="number" name="cantidad" min="1" class="asignar-input asignar-input--small" placeholder="<?= I18n::t('inventory.stock.removeQtyPlaceholder'); ?>" required />
                      <button class="btn btn-danger" type="submit"><?= I18n::t('inventory.stock.remove'); ?></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</main>
<script src="js/modal.js"></script>

