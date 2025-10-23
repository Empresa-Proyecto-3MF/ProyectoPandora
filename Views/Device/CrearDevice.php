<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <div class="content">

        <h1 class="logo">Dispositivo <span>Nuevo</span></h1>

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3>Agregar Dispositivo</h3>

                <?php if (isset($errorMsg)): ?>
                    <div class="alert alert-warning"><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>

                <?php if (!isset($errorMsg)): ?>
                    <form action="/ProyectoPandora/Public/index.php?route=Device/CrearDispositivo" method="POST" enctype="multipart/form-data">

                        <?php if (isset($isAdmin) && $isAdmin && isset($clientes)): ?>
                            <p>
                                <label for="user_id">Cliente:</label>
                                <select id="user_id" name="user_id" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id'] ?>">
                                            <?= htmlspecialchars($cliente['name']) ?> (<?= htmlspecialchars($cliente['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                        <?php endif; ?>

                        <p>
                            <label for="categoria_id">Categoría del Dispositivo:</label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">-- Seleccionar --</option>
                                <?php if (isset($categorias) && is_array($categorias)): ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>">
                                            <?= htmlspecialchars($categoria['nombre'] ?? $categoria['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </p>

                        <p>
                            <label for="marca">Marca:</label>
                            <input type="text" id="marca" name="marca" required>
                        </p>

                        <p>
                            <label for="modelo">Modelo:</label>
                            <input type="text" id="modelo" name="modelo" required>
                        </p>

                        <p>
                        <p class="block">
                            <label for="descripcion_falla">Descripción de Falla:</label>
                            <textarea id="descripcion_falla" name="descripcion_falla" rows="4" required></textarea>
                        </p>

                        </p>

                        <p>
                            <label for="img_dispositivo">Imagen del Dispositivo (opcional):</label>
                            <input type="file" id="img_dispositivo" name="img_dispositivo" accept="image/*">
                            <small>Si no adjuntas una imagen, usaremos una por defecto (NoFoto.jpg).</small>
                        </p>

                        <p class="block">
                            <button type="submit">Agregar Dispositivo</button>
                        </p>
                    </form>
                <?php endif; ?>

                <?php
                $user = $_SESSION['user'] ?? null;
                $rol = $user['role'] ?? '';
                if ($rol === 'Administrador') {
                    $volverUrl = "/ProyectoPandora/Public/index.php?route=Default/Index";
                } elseif ($rol === 'Cliente') {
                    $volverUrl = "/ProyectoPandora/Public/index.php?route=Cliente/MisDevice";
                } else {
                    $volverUrl = "/ProyectoPandora/Public/index.php?route=Default/Index";
                }
                ?>
                <a href="<?= $volverUrl ?>" class="btn-volver">Volver</a>
            </div>
        </div>

    </div>
</main>
