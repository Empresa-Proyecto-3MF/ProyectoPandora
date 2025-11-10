<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="/ProyectoPandora/Public/index.php?route=Auth/SendResetCode" method="post">
        <?= Csrf::input(); ?>
  <h1><?= __('auth.forgot.title'); ?></h1>
  <p><?= __('auth.forgot.instructions'); ?></p>

        <div class="input-box">
          <input type="email" name="email" placeholder="<?= __('common.email'); ?>" required>
          <i class='bx bx-mail-send'></i>
        </div>

  <button type="submit" class="btn-login"><?= __('auth.forgot.submit'); ?></button>

        <div class="register-link">
          <p><a href="/ProyectoPandora/Public/index.php?route=Auth/Login"><?= __('auth.forgot.back.login'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
</main>
