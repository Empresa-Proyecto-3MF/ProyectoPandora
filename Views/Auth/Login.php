<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <section class="login-body">
        <div class="wrapper-login">
            <form action="/ProyectoPandora/Public/index.php?route=Auth/Login" method="post">
                <?= Csrf::input(); ?>
                <h1><?= __('auth.welcome'); ?></h1>

                <div class="input-box">
                    <input type="email" name="email" id="email" placeholder="<?= __('auth.login.email'); ?>" required>
                    <i class='bx bx-user'></i> 
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="<?= __('auth.login.password'); ?>" autocomplete="off" required>
                    <i class='bx bx-lock'></i> 
                </div>

                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember" value="1"> <?= __('auth.login.remember'); ?></label>
                    <a href="/ProyectoPandora/Public/index.php?route=Auth/Forgot"><?= __('auth.login.forgot'); ?></a>
                </div>

                <button type="submit" class="btn-login"><?= __('auth.login.submit'); ?></button>

                <div class="register-link">
                    <p><?= __('auth.login.no.account'); ?> <a href="/ProyectoPandora/Public/index.php?route=Register/Register"><?= __('auth.login.register.link'); ?></a></p>
                </div>
            </form>
        </div>
    </section>
</main>
