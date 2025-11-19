<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'EmailYaRegistrado'): ?>
        <div style="color: red; margin-bottom: 10px; text-align:center;">
            <?= I18n::t('auth.register.error.email_taken'); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
        <div style="color: red; margin-bottom: 10px; text-align:center;">
            <?= I18n::t('auth.register.error.name_required'); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordCorta'): ?>
        <div style="color: red; margin-bottom: 10px; text-align:center;">
            <?= I18n::t('auth.register.error.password_short'); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordEspacios'): ?>
        <div style="color: red; margin-bottom: 10px; text-align:center;">
            <?= I18n::t('auth.register.error.password_spaces'); ?>
        </div>
    <?php endif; ?>

    <div class="form-vertical-wrapper">
        <div class="form-vertical">
            <h3><?= I18n::t('common.add'); ?> <?= I18n::t('nav.users'); ?></h3>

        <form action="index.php?route=Register/RegisterAdmin" method="POST" novalidate
            data-msg-name-required="<?= I18n::t('auth.register.error.name_required'); ?>"
            data-msg-email-invalid="<?= I18n::t('auth.register.error.email_invalid'); ?>"
            data-msg-password-short="<?= I18n::t('auth.register.error.password_short'); ?>">
                <?= Csrf::input(); ?>
                
                <p>
                    <label for="name"><?= I18n::t('auth.register.name'); ?>:</label>
                    <input type="text" name="name" id="name" autocomplete="off" required>
                </p>

                <p>
                    <label for="email"><?= I18n::t('auth.register.email'); ?>:</label>
                    <input type="email" name="email" id="email" autocomplete="off" required pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$" title="Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)">
                </p>

                <p>
                    <label for="password"><?= I18n::t('auth.register.password'); ?>:</label>
                    <input type="password" name="password" id="password" autocomplete="off" required minlength="8" pattern="^\S{8,}$" title="La contraseña debe tener al menos 8 caracteres y no puede contener espacios">
                </p>

                <p>
                    <label for="role">Rol:</label>
                    <select name="role" id="role" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Tecnico">Técnico</option>
                        <option value="Cliente">Cliente</option>
                    </select>
                </p>

                <?php
                    $defaultBack = 'index.php?route=Admin/ListarUsers';
                    $prevUrl = $_SESSION['prev_url'] ?? '';
                    $prevUrlLower = strtolower($prevUrl);
                    if (
                        !$prevUrl ||
                        strpos($prevUrlLower, 'register/registeradmin') !== false ||
                        strpos($prevUrlLower, 'admin/register') !== false
                    ) {
                        $volverAdminUrl = $defaultBack;
                    } else {
                        $volverAdminUrl = $prevUrl;
                    }
                ?>
                <button type="submit"><?= I18n::t('auth.register.submit'); ?></button>
                <a href="<?= htmlspecialchars($volverAdminUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-volver"><?= I18n::t('common.back'); ?></a>
            </form>
        </div>
    </div>
</main>
<div id="appValidationModal" class="app-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" aria-describedby="appModalMsg">
    <div class="app-modal" role="document">
    <div class="app-modal__header"><span id="appModalTitle"><?= I18n::t('auth.register.modal.title'); ?></span></div>
    <div class="app-modal__body" id="appModalMsg"><?= I18n::t('auth.register.modal.message'); ?></div>
        <div class="app-modal__footer">
            <button type="button" class="btn-primary" id="appModalOkBtn"><?= I18n::t('auth.register.modal.accept'); ?></button>
        </div>
    </div>
    <span class="sr-only" aria-live="assertive"></span>
</div>
<script src="js/validation-register.js"></script>
