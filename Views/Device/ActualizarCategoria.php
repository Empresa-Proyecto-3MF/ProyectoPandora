<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3><?= __('device.category.button.update') ?> <?= __('device.category.title') ?></h3>

                <form method="POST" action="/ProyectoPandora/Public/index.php?route=Device/ActualizarCategoria&id=<?= htmlspecialchars($categoria['id'] ?? '') ?>">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id'] ?? '') ?>">

                    <p>
                        <label for="nombre"><?= __('device.category.field.name') ?>:</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['name'] ?? '') ?>" required>
                    </p>    

                    <p class="block">
                        <button type="submit"><?= __('device.category.button.update') ?></button>
                    </p>
                </form>

                <a href="/ProyectoPandora/Public/index.php?route=Device/ListarCategoria" class="btn-volver"><?= __('device.category.link.backList') ?></a>
            </div>
        </div>

    </div>
</main>
