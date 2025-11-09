<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
    <section class="login-body">
        <div class="wrapper-login">
            <form action="/ProyectoPandora/Public/index.php?route=Auth/Login" method="post">
                <h1>bienvenido a Innovasys</h1>

                <div class="input-box">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <i class='bx bx-user'></i> 
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="Contraseña" autocomplete="off" required>
                    <i class='bx bx-lock'></i> 
                </div>

                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember" value="1"> Acordarme</label>
                    <a href="/ProyectoPandora/Public/index.php?route=Auth/Forgot">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>

                <div class="register-link">
                    <p>¿No tienes una cuenta? <a href="/ProyectoPandora/Public/index.php?route=Register/Register">Regístrate</a></p>
                </div>
            </form>
        </div>
    </section>
</main>
