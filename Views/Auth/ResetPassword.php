<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $email = $_GET['email'] ?? ''; $ok = $_GET['ok'] ?? ''; $err = $_GET['err'] ?? ''; ?>
<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="index.php?route=Auth/DoResetPassword" method="post" autocomplete="off">
        <?= Csrf::input(); ?>
  <h1><?= I18n::t('auth.reset.title'); ?></h1>
        <?php if ($ok): ?>
          <p style="color:#2ecc71"><?= I18n::t('auth.reset.verified'); ?></p>
        <?php endif; ?>
        <?php if ($err === 'invalid'): ?>
          <p style="color:#ff6b6b"><?= I18n::t('auth.reset.error.invalid'); ?></p>
        <?php elseif ($err === 'save'): ?>
          <p style="color:#ff6b6b"><?= I18n::t('auth.reset.error.save'); ?></p>
        <?php endif; ?>
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <div class="input-box">
          <input type="password" name="password" placeholder="<?= I18n::t('auth.reset.field.new'); ?>" minlength="6" required>
          <i class='bx bx-lock'></i>
        </div>
        <div class="input-box">
          <input type="password" name="password_confirm" placeholder="<?= I18n::t('auth.reset.field.confirm'); ?>" minlength="6" required>
          <i class='bx bx-lock-alt'></i>
        </div>
  <button type="submit" class="btn-login"><?= I18n::t('auth.reset.submit'); ?></button>
        <div class="register-link">
          <p><a href="index.php?route=Auth/Login"><?= I18n::t('auth.reset.back.login'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
</main>
