<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $currentLang = I18n::getLocale(); ?>

<main>

  <div class="language-switcher-login">
    <select id="languageSelector" data-language-selector data-prev-url="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <option value="es" <?= $currentLang === 'es' ? 'selected' : ''; ?>>🇪🇸 ES</option>
      <option value="en" <?= $currentLang === 'en' ? 'selected' : ''; ?>>🇺🇸 EN</option>
      <option value="pt" <?= $currentLang === 'pt' ? 'selected' : ''; ?>>🇧🇷 PT</option>
    </select>
  </div>
  <section class="login-body">
    <div class="wrapper-login">
  <form action="index.php?route=Register/Register" method="POST" novalidate
    data-msg-name-required="<?= I18n::t('auth.register.error.name_required'); ?>"
    data-msg-email-invalid="<?= I18n::t('auth.register.error.email_invalid'); ?>"
    data-msg-password-short="<?= I18n::t('auth.register.error.password_short'); ?>">
    <?= Csrf::input(); ?>
  <h1><?= I18n::t('auth.register.heading'); ?></h1>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'EmailYaRegistrado'): ?>
          <div class="alerta-error"><?= I18n::t('auth.register.error.email_taken'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
          <div class="alerta-error"><?= I18n::t('auth.register.error.name_required'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordCorta'): ?>
          <div class="alerta-error"><?= I18n::t('auth.register.error.password_short'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordEspacios'): ?>
          <div class="alerta-error"><?= I18n::t('auth.register.error.password_spaces'); ?></div>
        <?php endif; ?>

        <div class="input-box">
          <input type="text" name="name" id="name" placeholder="<?= I18n::t('auth.register.name'); ?>" required autocomplete="off">
          <i class='bx bx-user'></i>
        </div>

        <div class="input-box">
          <input type="email" name="email" id="email" placeholder="<?= I18n::t('auth.register.email'); ?>" required autocomplete="off"
            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
            title="Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)">
          <i class='bx bx-envelope'></i>
        </div>

        <div class="input-box">
          <input type="password" name="password" id="password" placeholder="<?= I18n::t('auth.register.password'); ?>" required autocomplete="off"
            minlength="8" pattern="^\S{8,}$" title="La contraseña debe tener al menos 8 caracteres y no puede contener espacios">
          <i class='bx bx-lock'></i>
        </div>

  <button type="submit" class="btn-login"><?= I18n::t('auth.register.submit'); ?></button>

        <div class="register-link">
          <p><?= I18n::t('auth.register.have.account'); ?> <a href="index.php?route=Auth/Login"><?= I18n::t('auth.register.login.link'); ?></a></p>
          <p><a href="index.php?route=Default/Index"><?= I18n::t('auth.register.back.home'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
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
<script src="js/language-switcher.js"></script>