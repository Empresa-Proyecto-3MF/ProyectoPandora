<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
        <div class="botones">
            <div class="btn-table-acciones">
                <a class="btn-all btn-acciones-inventario-cate" href="/ProyectoPandora/Public/index.php?route=Inventario/MostrarCrearCategoria">Añadir Categoría</a>
            </div>
        </div>
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td>
                                    <div class='action-buttons'>
                                        <a href="/ProyectoPandora/Public/index.php?route=Inventario/ActualizarCategoria&id=<?= $cat['id'] ?>" class="btn edit-btn">Actualizar</a>
                                        |
                                        <a href="/ProyectoPandora/Public/index.php?route=Inventario/EliminarCategoriaInventario&id=<?= $cat['id'] ?>" class="btn delete-btn" data-confirm="¿Seguro que deseas eliminar esta categoría?">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No hay categorías registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</main>