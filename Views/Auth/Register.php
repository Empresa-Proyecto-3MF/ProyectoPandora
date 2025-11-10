<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
  <section class="login-body">
    <div class="wrapper-login">
  <form action="/ProyectoPandora/Public/index.php?route=Register/Register" method="POST" novalidate
    data-msg-name-required="<?= __('auth.register.error.name_required'); ?>"
    data-msg-email-invalid="<?= __('auth.register.error.email_invalid'); ?>"
    data-msg-password-short="<?= __('auth.register.error.password_short'); ?>">
    <?= Csrf::input(); ?>
  <h1><?= __('auth.register.heading'); ?></h1>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'EmailYaRegistrado'): ?>
          <div class="alerta-error"><?= __('auth.register.error.email_taken'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
          <div class="alerta-error"><?= __('auth.register.error.name_required'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordCorta'): ?>
          <div class="alerta-error"><?= __('auth.register.error.password_short'); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordEspacios'): ?>
          <div class="alerta-error"><?= __('auth.register.error.password_spaces'); ?></div>
        <?php endif; ?>

        <div class="input-box">
          <input type="text" name="name" id="name" placeholder="<?= __('auth.register.name'); ?>" required autocomplete="off">
          <i class='bx bx-user'></i>
        </div>

        <div class="input-box">
          <input type="email" name="email" id="email" placeholder="<?= __('auth.register.email'); ?>" required autocomplete="off"
            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
            title="Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)">
          <i class='bx bx-envelope'></i>
        </div>

        <div class="input-box">
          <input type="password" name="password" id="password" placeholder="<?= __('auth.register.password'); ?>" required autocomplete="off"
            minlength="8" pattern="^\S{8,}$" title="La contraseña debe tener al menos 8 caracteres y no puede contener espacios">
          <i class='bx bx-lock'></i>
        </div>

  <button type="submit" class="btn-login"><?= __('auth.register.submit'); ?></button>

        <div class="register-link">
          <p><?= __('auth.register.have.account'); ?> <a href="/ProyectoPandora/Public/index.php?route=Auth/Login"><?= __('auth.register.login.link'); ?></a></p>
          <p><a href="/ProyectoPandora/Public/index.php?route=Default/Index"><?= __('auth.register.back.home'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
</main>

<div id="appValidationModal" class="app-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" aria-describedby="appModalMsg">
  <div class="app-modal" role="document">
  <div class="app-modal__header"><span id="appModalTitle"><?= __('auth.register.modal.title'); ?></span></div>
  <div class="app-modal__body" id="appModalMsg"><?= __('auth.register.modal.message'); ?></div>
    <div class="app-modal__footer">
  <button type="button" class="btn-primary" id="appModalOkBtn"><?= __('auth.register.modal.accept'); ?></button>
    </div>
  </div>
  <span class="sr-only" aria-live="assertive"></span>
</div>

<script src="/ProyectoPandora/Public/js/validation-register.js"></script>scriptscript
