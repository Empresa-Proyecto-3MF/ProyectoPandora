<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>

<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="/ProyectoPandora/Public/index.php?route=Auth/SendResetCode" method="post">
        <h1>Recuperar contraseña</h1>
        <p>Ingresá tu email registrado y te enviaremos un código de 4 dígitos.</p>

        <div class="input-box">
          <input type="email" name="email" placeholder="Email" required>
          <i class='bx bx-mail-send'></i>
        </div>

        <button type="submit" class="btn-login">Enviar código</button>

        <div class="register-link">
          <p><a href="/ProyectoPandora/Public/index.php?route=Auth/Login">Volver al login</a></p>
        </div>
      </form>
    </div>
  </section>
</main>
