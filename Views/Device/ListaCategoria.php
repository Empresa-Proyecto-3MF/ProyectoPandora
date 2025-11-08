<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        <?php if (!empty($flash) && is_array($flash)): ?>
            <div class='alert <?= $flash['type'] === 'success' ? 'success' : 'error' ?>'>
                <?= htmlspecialchars($flash['message'] ?? '') ?>
            </div>
        <?php endif; ?>
        <div class="botones">
            <div class="btn-table-acciones">
                <a class="btn-all btn-acciones-cate" href="/ProyectoPandora/Public/index.php?route=Device/CrearCategoria">Añadir Categoria</a>
            </div>
        </div>
        <table id="categoryTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de la Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['name']); ?></td>
                        <td>
                            <div class='action-buttons'>
                                <a href="/ProyectoPandora/Public/index.php?route=Device/ActualizarCategoria&id=<?= (int)$categoria['id'] ?>" class="btn edit-btn">Actualizar</a>
                                |
                                <form method="post" action="/ProyectoPandora/Public/index.php?route=Device/DeleteCategoria" style="display:inline;" data-confirm="¿Seguro que deseas eliminar esta categoría de dispositivos?">
                                    <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                                    <button class="btn delete-btn">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="3">No hay categorías disponibles.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="/ProyectoPandora/Public/js/Buscador.js"></script>
</main>