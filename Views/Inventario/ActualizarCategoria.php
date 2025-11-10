<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <div class="content">

        <div class="contact-wrapper animated bounceInUp">
            <div class="contact-form">
                <h3><?= __('inventory.category.update.title') ?? __('inventory.category.new.heading') ?></h3>

                <form method="POST" action="/ProyectoPandora/Public/index.php?route=Inventario/EditarCategoria&id=<?= htmlspecialchars($categoria['id']) ?>">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id']) ?>">

                    <p>
                        <label for="name"><?= __('inventory.category.field.name') ?>:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($categoria['name'] ?? '') ?>" required>
                    </p>

                    <p class="block">
                        <button type="submit"><?= __('inventory.category.button.save') ?></button>
                    </p>
                </form>

                <a href="<?= isset($backUrl) ? $backUrl : '/ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias' ?>" class="btn-volver"><?= __('inventory.category.link.back') ?></a>
            </div>
        </div>

    </div>
</main>
