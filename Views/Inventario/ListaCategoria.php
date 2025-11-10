<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        
        <div class="botones">
            <div class="dropdown">
                <div class="btn-table-acciones">
                    <a class="btn-all btn-acciones-inventario-cate" href="/ProyectoPandora/Public/index.php?route=Inventario/MostrarCrearCategoria"><?= __('inventory.category.list.add') ?></a>
                </div>
            </div>
        </div>
        <br>
            <table id="userTable">
                <thead>
                    <tr>
                        <th><?= __('common.id') ?></th>
                        <th><?= __('inventory.category.list.col.name') ?></th>
                        <th><?= __('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td data-label="id"><?= $cat['id'] ?></td>
                                <td data-label="name"><?= htmlspecialchars($cat['name']) ?></td>
                                <td data-label="acciones">
                                    <div class='action-buttons'>
                                        <a href="/ProyectoPandora/Public/index.php?route=Inventario/ActualizarCategoria&id=<?= $cat['id'] ?>" class="btn edit-btn"><?= __('common.update') ?></a>
                                        |
                                        <a href="/ProyectoPandora/Public/index.php?route=Inventario/EliminarCategoriaInventario&id=<?= $cat['id'] ?>" class="btn delete-btn" data-confirm="<?= __('inventory.category.confirm.delete') ?>"><?= __('common.delete') ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3"><?= __('inventory.category.list.empty') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</main>