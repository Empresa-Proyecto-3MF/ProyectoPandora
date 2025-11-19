<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <div class="content">

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3>Actualizar Dispositivo</h3>

                <form method="POST" enctype="multipart/form-data" action="index.php?route=Device/ActualizarDevice&id=<?= $dispositivo['id'] ?>">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="from" value="<?= $_GET['from'] ?? 'Cliente/MisDevice' ?>">
                    <input type="hidden" name="id" value="<?= $dispositivo['id'] ?>">

                    <p>
                        <label for="categoria_id">Categoría:</label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($dispositivo['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label for="marca">Marca:</label>
                        <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($dispositivo['marca']) ?>" required>
                    </p>

                    <p>
                        <label for="modelo">Modelo:</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($dispositivo['modelo']) ?>" required>
                    </p>

                    <p class="block">
                        <label for="descripcion_falla">Descripción de la falla:</label>
                        <textarea id="descripcion_falla" name="descripcion_falla" rows="3"><?= htmlspecialchars($dispositivo['descripcion_falla']) ?></textarea>
                    </p>

                    <p>
                        <label for="img_dispositivo">Imagen del dispositivo:</label>
                        <input type="file" id="img_dispositivo" name="img_dispositivo" accept="image/*" <?= empty($dispositivo['img_dispositivo']) ? 'required' : '' ?>>
                    </p>

                    <?php if (!empty($dispositivo['img_dispositivo'])): ?>
                        <p class="block">
                            <div class="preview-img">
                                    <?php $imgPreview = device_image_url($dispositivo['img_dispositivo']); ?>
                                    <img src="<?= htmlspecialchars($imgPreview) ?>" alt="Dispositivo">
                            </div>
                        </p>
                    <?php endif; ?>

                    <p class="block">
                        <button type="submit">Guardar</button>
                    </p>
                </form>

                <a href="<?= $_SESSION['prev_url'] ?? 'index.php?route=Default/Index' ?>" class="btn-volver">Volver</a>
            </div>
        </div>

    </div>
</main>
