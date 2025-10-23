<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
<?php include_once __DIR__ . '/../Includes/Header.php'; ?>
    <div class="Tabla-Contenedor">
        <?php
        $msg = '';
        if (isset($_GET['success'])) {
            $msg = 'Categoría eliminada correctamente.';
            echo "<div class='alert success'>" . htmlspecialchars($msg) . "</div>";
        } elseif (isset($_GET['error'])) {
            $err = $_GET['error'];
            switch ($err) {
                case 'CategoryInUse':
                    $msg = 'No se puede eliminar: la categoría está siendo usada por uno o más dispositivos.';
                    break;
                case 'CategoryNotFound':
                    $msg = 'Categoría no encontrada.';
                    break;
                case 'ErrorDeletingCategory':
                default:
                    $msg = 'No se pudo eliminar la categoría.';
                    break;
            }
            echo "<div class='alert error'>" . htmlspecialchars($msg) . "</div>";
        }
        ?>
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
                                <form method="post" action="/ProyectoPandora/Public/index.php?route=Device/DeleteCategoria" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría de dispositivos?');">
                                    <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                                    <button type="submit" class="btn delete-btn">Eliminar</button>
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