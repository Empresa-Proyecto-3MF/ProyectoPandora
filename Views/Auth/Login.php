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
            <form action="index.php?route=Auth/Login" method="post">
                <?= Csrf::input(); ?>
                <h1><?= I18n::t('auth.welcome'); ?></h1>

                <div class="input-box">
                    <input type="email" name="email" id="email" placeholder="<?= I18n::t('auth.login.email'); ?>" required>
                    <i class='bx bx-user'></i> 
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="<?= I18n::t('auth.login.password'); ?>" autocomplete="off" required>
                    <i class='bx bx-lock'></i> 
                </div>

                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember" value="1"> <?= I18n::t('auth.login.remember'); ?></label>
                    <a href="index.php?route=Auth/Forgot"><?= I18n::t('auth.login.forgot'); ?></a>
                </div>

                <button type="submit" class="btn-login"><?= I18n::t('auth.login.submit'); ?></button>

                <div class="register-link">
                    <p><?= I18n::t('auth.login.no.account'); ?> <a href="index.php?route=Register/Register"><?= I18n::t('auth.login.register.link'); ?></a></p>
                </div>
            </form>
        </div>
    </section>
</main>
<script src="js/language-switcher.js"></script>
