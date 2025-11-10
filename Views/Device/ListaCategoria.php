<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        
        <div class="botones">
            <div class="btn-table-acciones">
                <a class="btn-all btn-acciones-cate" href="/ProyectoPandora/Public/index.php?route=Device/CrearCategoria"><?= __('device.category.list.add') ?></a>
            </div>
        </div>
        <br>
        <table id="userTable">
            <thead>
                <tr>
                    <th><?= __('common.id') ?></th>
                    <th><?= __('device.category.list.col.name') ?></th>
                    <th><?= __('common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td data-label="id"><?php echo htmlspecialchars($categoria['id']); ?></td>
                        <td data-label="name"><?php echo htmlspecialchars($categoria['name']); ?></td>
                        <td data-label="acciones">
                            <div class='action-buttons'>
                                <a href="/ProyectoPandora/Public/index.php?route=Device/ActualizarCategoria&id=<?= (int)$categoria['id'] ?>" class="btn edit-btn"><?= __('common.update') ?></a>
                                |
                                <form method="post" action="/ProyectoPandora/Public/index.php?route=Device/DeleteCategoria" style="display:inline;" data-confirm="<?= __('device.category.confirm.delete') ?>">
                                    <?= Csrf::input(); ?>
                                    <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                                    <button class="btn delete-btn"><?= __('common.delete') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="3"><?= __('device.category.list.empty') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="/ProyectoPandora/Public/js/Buscador.js"></script>scriptscript
</main>