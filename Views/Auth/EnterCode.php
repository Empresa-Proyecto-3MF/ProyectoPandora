<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $email = $_GET['email'] ?? ''; $err = $_GET['err'] ?? ''; ?>
<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="index.php?route=Auth/VerifyResetCode" method="post">
        <?= Csrf::input(); ?>
    <h1><?= I18n::t('auth.code.title'); ?></h1>
  <p><?= I18n::t('auth.code.instructions'); ?> <strong><?php echo htmlspecialchars($email); ?></strong></p>
  <p style="font-size:0.85em;color:#888"><?= I18n::t('auth.code.local.hint'); ?></p>
        <?php if ($err): ?>
          <?php
            $msg = I18n::t('auth.code.error.unknown');
            switch ($err) {
              case 'invalid': $msg = I18n::t('auth.code.error.invalid'); break;
              case 'expired': $msg = I18n::t('auth.code.error.expired'); break;
              case 'no-request': $msg = I18n::t('auth.code.error.no_request'); break;
              case 'not-found': $msg = I18n::t('auth.code.error.not_found'); break;
              case 'locked': $msg = I18n::t('auth.code.error.locked'); break;
              case 'db-error': $msg = I18n::t('auth.code.error.db_error'); break;
              case 'rate':
                $wait = isset($_GET['wait']) ? (int)$_GET['wait'] : 60;
                if ($wait < 1) $wait = 1;
                if ($wait > 60) $wait = 60;
                $msg = str_replace('{seconds}', $wait, I18n::t('auth.code.error.rate'));
                break;
            }
          ?>
          <p style="color:#ff6b6b"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <div class="input-box">
          <input type="text" name="code" pattern="\d{4}" maxlength="4" placeholder="<?= I18n::t('auth.code.field.code'); ?>" required>
          <i class='bx bx-key'></i>
        </div>
  <button type="submit" class="btn-login"><?= I18n::t('auth.code.submit'); ?></button>
        <div class="register-link">
          <p><a href="index.php?route=Auth/Forgot"><?= I18n::t('auth.code.resend.link'); ?></a></p>
        </div>
      </form>
    </div>
  </section>
</main>
