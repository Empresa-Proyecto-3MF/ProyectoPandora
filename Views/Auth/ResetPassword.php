<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $email = $_GET['email'] ?? ''; $ok = $_GET['ok'] ?? ''; $err = $_GET['err'] ?? ''; ?>
<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="/ProyectoPandora/Public/index.php?route=Auth/DoResetPassword" method="post" autocomplete="off">
        <h1>Nueva contraseña</h1>
        <?php if ($ok): ?>
          <p style="color:#2ecc71">Código verificado. Podés crear una nueva contraseña.</p>
        <?php endif; ?>
        <?php if ($err === 'invalid'): ?>
          <p style="color:#ff6b6b">Verificá que ambas contraseñas coincidan (mínimo 6 caracteres).</p>
        <?php elseif ($err === 'save'): ?>
          <p style="color:#ff6b6b">No se pudo guardar la contraseña. Intentá nuevamente.</p>
        <?php endif; ?>
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <div class="input-box">
          <input type="password" name="password" placeholder="Nueva contraseña" minlength="6" required>
          <i class='bx bx-lock'></i>
        </div>
        <div class="input-box">
          <input type="password" name="password_confirm" placeholder="Confirmar contraseña" minlength="6" required>
          <i class='bx bx-lock-alt'></i>
        </div>
        <button type="submit" class="btn-login">Guardar contraseña</button>
        <div class="register-link">
          <p><a href="/ProyectoPandora/Public/index.php?route=Auth/Login">Volver al login</a></p>
        </div>
      </form>
    </div>
  </section>
</main>
