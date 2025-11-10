<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <div class="content">

    <h1 class="logo"><?= __('device.category.new.title') ?></h1>

        <div class="categoriadevice-wrapper animated bounceInUp">
            <div class="form-container">
                <h3><?= __('device.category.new.heading') ?></h3>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'CamposRequeridos'): ?>
                    <div class="alert alert-warning">
                        Todos los campos son obligatorios.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'ErrorAlAgregarCategoria'): ?>
                    <div class="alert alert-warning">
                        Error al agregar la categoría.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
                    <div class="alert alert-success">
                        Categoría agregada exitosamente.
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <?= Csrf::input(); ?>
                    <label for="nombre"><?= __('device.category.field.name') ?>:</label>
                    <input type="text" id="nombre" name="nombre" autocomplete="off" required>

                    <button type="submit"><?= __('device.category.button.add') ?></button>
                </form>

                <a href="/ProyectoPandora/Public/index.php?route=Device/ListarCategoria" class="btn-volver">
                    <?= __('device.category.link.backList') ?>
                </a>
            </div>
        </div>

    </div>
</main>
