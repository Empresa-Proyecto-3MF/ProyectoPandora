<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="index.php?route=Auth/SendResetCode" method="post">
        <?= Csrf::input(); ?>
  <h1><?= I18n::t('auth.forgot.title'); ?></h1>
  <p><?= I18n::t('auth.forgot.instructions'); ?></p>

        <div class="input-box">
          <input type="email" name="email" placeholder="<?= I18n::t('common.email'); ?>" required>
          <i class='bx bx-mail-send'></i>
        </div>

  <button type="submit" class="btn-login"><?= I18n::t('auth.forgot.submit'); ?></button>

        <div class="register-link">
          <p><a href="index.php?route=Auth/Login"><?= I18n::t('auth.forgot.back.login'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
</main>
