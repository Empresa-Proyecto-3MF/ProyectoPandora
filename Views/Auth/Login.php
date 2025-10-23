<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <section class="fondo-login">
        <div class="login">
            <h2 class="bienvenida"><?= __('auth.login.welcome', ['app' => __('app.name')]) ?></h2>
            <h3><?= __('auth.login.title') ?></h3>

            <form class="form" id="loginForm" method="post" action="/ProyectoPandora/Public/index.php?route=Auth/Login">
                <div class="textbox">
                    <input type="email" name="email" id="email" required>
                    <label for="email"><?= __('auth.login.email') ?></label>
                </div>

                <div class="textbox">
                    <input type="password" name="password" id="password" autocomplete="off" required>
                    <label for="password"><?= __('auth.login.password') ?></label>
                </div>

                <button type="submit">
                    <span class="material-symbols-outlined"><?= __('auth.login.submit') ?></span>
                </button>
            </form>

            <!-- ZONA DE RESPUESTA -->
            <div id="respuesta" style="margin-top: 10px; color: red;"></div>

            <a class="footer-login" href="#">¿Olvidaste tu contraseña?</a>
            <p>
                <a class="footer-login" href="/ProyectoPandora/Public/index.php?route=Register/Register"><?= __('auth.register.title') ?></a>
            </p>
        </div>
    </section>
</main>


