<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <div class="content">

        <h1 class="logo">Inventario <span>Nuevo</span></h1>

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3>Agregar Item al Inventario</h3>
                
                <?php if (isset($errorMsg)): ?>
                    <div class="alert alert-warning"><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>

        <form action="index.php?route=Inventario/CrearItem" method="POST" enctype="multipart/form-data">
            <?= Csrf::input(); ?>
                        <p>
                            <label for="categoria_id">Categoría:</label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= isset($old['categoria_id']) && (int)$old['categoria_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <label for="name_item">Tipo:</label>
                            <input type="text" id="name_item" name="name_item" required value="<?= isset($old['name_item']) ? htmlspecialchars($old['name_item']) : '' ?>">
                        </p>
                        <p>
                            <label for="valor_unitario">Valor Unitario:</label>
                            <input type="number" step="0.01" min="0" id="valor_unitario" name="valor_unitario" required value="<?= isset($old['valor_unitario']) ? htmlspecialchars($old['valor_unitario']) : '' ?>">
                        </p>
                        <p>
                            <label for="foto_item">Imagen:</label>
                            <input type="file" id="foto_item" name="foto_item" accept="image/*">
                        </p>
                        <p>
                            <label for="stock_actual">Cantidad:</label>
                            <input type="number" min="0" id="stock_actual" name="stock_actual" required value="<?= isset($old['stock_actual']) ? htmlspecialchars($old['stock_actual']) : '' ?>">
                        </p>
                        <p>
                            <label for="stock_minimo">Stock Mínimo:</label>
                            <input type="number" min="0" id="stock_minimo" name="stock_minimo" required value="<?= isset($old['stock_minimo']) ? htmlspecialchars($old['stock_minimo']) : '' ?>">
                        </p>
                        <p class="block">
                            <button type="submit">Agregar Item</button>
                        </p>
                    </form>
                <?php
                    $fallbackUrl = 'index.php?route=Inventario/ListarCategorias';
                    $prevUrl = $_SESSION['prev_url'] ?? '';
                    $prevUrlLower = strtolower($prevUrl);
                    if (
                        !$prevUrl ||
                        strpos($prevUrlLower, 'inventario/mostrarcrearitem') !== false ||
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
    </div>
</main>
