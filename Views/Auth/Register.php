<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<main>
    <section class="fondo-login">
        <div class="login">
            <h2 class="bienvenida">Bienvenido a <strong>Innovasys</strong></h2>
            <h3>Registrarse</h3>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'EmailYaRegistrado'): ?>
                <div style="color: red; margin-bottom: 10px;">
                    El correo electrónico ya está registrado. Por favor, usa otro.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
                <div style="color: red; margin-bottom: 10px;">
                    El nombre es obligatorio.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordCorta'): ?>
                <div style="color: red; margin-bottom: 10px;">
                    La contraseña debe tener al menos 8 caracteres.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordEspacios'): ?>
                <div style="color: red; margin-bottom: 10px;">
                    La contraseña no puede contener espacios ni caracteres en blanco.
                </div>
            <?php endif; ?>

            
            <form class="form" action="/ProyectoPandora/Public/index.php?route=Register/Register" method="POST" novalidate onsubmit="return validarEmailRegistro(this)">
                <div class="textbox">
                    <input type="text" name="name" autocomplete="off" required>
                    <label for="name">Nombre</label>
                </div>

                <div class="textbox">
                    <input type="email" name="email" autocomplete="off" required pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$" title="Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)">
                    <label for="email">Email</label>
                </div>

                <div class="textbox">
                    <input type="password" name="password" autocomplete="off" required minlength="8" pattern="^\S{8,}$" title="La contraseña debe tener al menos 8 caracteres y no puede contener espacios">
                    <label for="password">Contraseña</label>
                </div>

                <button type="submit">
                    <p>Sign up</p>
                    <span class="material-symbols-outlined"></span>
                </button>
            </form>

            <p>
                <a class="footer-login" href="/ProyectoPandora/Public/index.php?route=Auth/Login">¿Ya tenés cuenta? Iniciar sesión</a>
            </p>
            <p>
                <a class="footer-login" href="/ProyectoPandora/Public/index.php?route=Default/Index">Volver al inicio</a>
            </p>
        </div>
    </section>
</main>
<div id="appValidationModal" class="app-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" aria-describedby="appModalMsg">
    <div class="app-modal" role="document">
        <div class="app-modal__header"><span id="appModalTitle">Revisá los datos</span></div>
        <div class="app-modal__body" id="appModalMsg">Mensaje</div>
        <div class="app-modal__footer">
            <button type="button" class="btn-primary" id="appModalOkBtn">Aceptar</button>
        </div>
    </div>
    <span class="sr-only" aria-live="assertive"></span>
</div>
<script src="/ProyectoPandora/Public/js/validation-register.js"></script>
