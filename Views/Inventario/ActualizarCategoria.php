<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3><?= I18n::t('inventory.category.update.title') ?? I18n::t('inventory.category.new.heading') ?></h3>

                <form method="POST" action="index.php?route=Inventario/EditarCategoria&id=<?= htmlspecialchars($categoria['id']) ?>">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id']) ?>">

                    <p>
                        <label for="name"><?= I18n::t('inventory.category.field.name') ?>:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($categoria['name'] ?? '') ?>" required>
                    </p>

                    <p class="block">
                        <button type="submit"><?= I18n::t('inventory.category.button.save') ?></button>
                    </p>
                </form>

                <a href="<?= isset($backUrl) ? $backUrl : 'index.php?route=Inventario/ListarCategorias' ?>" class="btn-volver"><?= I18n::t('inventory.category.link.back') ?></a>
            </div>
        </div>

    </div>
</main>
