<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
  <section class="login-body">
    <div class="wrapper-login">
  <form action="/ProyectoPandora/Public/index.php?route=Register/Register" method="POST" novalidate>
        <h1>Bienvenido a Innovasys</h1>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'EmailYaRegistrado'): ?>
          <div class="alerta-error">El correo electrónico ya está registrado. Por favor, usa otro.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'NombreRequerido'): ?>
          <div class="alerta-error">El nombre es obligatorio.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordCorta'): ?>
          <div class="alerta-error">La contraseña debe tener al menos 8 caracteres.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'PasswordEspacios'): ?>
          <div class="alerta-error">La contraseña no puede contener espacios ni caracteres en blanco.</div>
        <?php endif; ?>

        <div class="input-box">
          <input type="text" name="name" id="name" placeholder="Nombre" required autocomplete="off">
          <i class='bx bx-user'></i>
        </div>

        <div class="input-box">
          <input type="email" name="email" id="email" placeholder="Email" required autocomplete="off"
            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
            title="Ingresá un email válido (debe incluir un dominio con punto, ej: usuario@dominio.com)">
          <i class='bx bx-envelope'></i>
        </div>

        <div class="input-box">
          <input type="password" name="password" id="password" placeholder="Contraseña" required autocomplete="off"
            minlength="8" pattern="^\S{8,}$" title="La contraseña debe tener al menos 8 caracteres y no puede contener espacios">
          <i class='bx bx-lock'></i>
        </div>

        <button type="submit" class="btn-login">Registrarse</button>

        <div class="register-link">
          <p>¿Ya tienes una cuenta? <a href="/ProyectoPandora/Public/index.php?route=Auth/Login">Iniciar sesión</a></p>
          <p><a href="/ProyectoPandora/Public/index.php?route=Default/Index">Volver al inicio</a></p>
        </div>
      </form>
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
