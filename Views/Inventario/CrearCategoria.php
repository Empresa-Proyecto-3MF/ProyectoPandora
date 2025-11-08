<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">
        <div class="categoria-wrapper">
            <h3>Agregar Categoría de Inventario</h3>

            <?php if (isset($_GET['error'])): ?>
                <div style="color: red; font-weight: bold; margin-bottom: 15px;">
                    Ocurrió un error al agregar la categoría.
                </div>
            <?php endif; ?>

            <form action="/ProyectoPandora/Public/index.php?route=Inventario/CrearCategoria" method="POST">
                <label for="name">Nombre de la Categoría:</label>
                <input type="text" id="name" name="name" required>

                <button type="submit">Agregar Categoría</button>
            </form>
            <?php
                $fallbackUrl = '/ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias';
                $prevUrl = $_SESSION['prev_url'] ?? '';
                $prevUrlLower = strtolower($prevUrl);
                if (
                    !$prevUrl ||
                    strpos($prevUrlLower, 'inventario/mostrarcrearcategoria') !== false ||
                    strpos($prevUrlLower, 'inventario/crearcategoria') !== false ||
                    strpos($prevUrlLower, 'inventario/crearitem') !== false ||
                    strpos($prevUrlLower, 'inventario/actualizaritem') !== false
                ) {
                    $volverUrl = $fallbackUrl;
                } else {
                    $volverUrl = $prevUrl;
                }
            ?>
            <a href="<?= htmlspecialchars($volverUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-volver">Volver</a>
        </div>
    </div>
</main>
