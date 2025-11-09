<?php include_once __DIR__ . '/../Includes/Sidebar.php'; ?>
<?php $email = $_GET['email'] ?? ''; $err = $_GET['err'] ?? ''; ?>
<main>
  <section class="login-body">
    <div class="wrapper-login">
      <form action="/ProyectoPandora/Public/index.php?route=Auth/VerifyResetCode" method="post">
        <h1>Ingresar código</h1>
  <p>Hemos enviado (o intentado enviar) un código de 4 dígitos al email: <strong><?php echo htmlspecialchars($email); ?></strong></p>
  <p style="font-size:0.85em;color:#888">Si no te llega en entorno local, revisá el archivo <code>Logs/mail.log</code> donde se registra el código. Tras 5 intentos fallidos se bloquea por 10 minutos.</p>
        <?php if ($err): ?>
          <?php
            $msg = 'Error desconocido.';
            switch ($err) {
              case 'invalid': $msg = 'Código incorrecto. Revisá y volvé a intentar.'; break;
              case 'expired': $msg = 'El código expiró. Solicitá uno nuevo.'; break;
              case 'no-request': $msg = 'No hay una solicitud de recuperación activa para este email.'; break;
              case 'not-found': $msg = 'Email no registrado (se oculta detalle por seguridad).'; break;
              case 'locked': $msg = 'Demasiados intentos fallidos. Bloqueado temporalmente (10 min).'; break;
              case 'db-error': $msg = 'Error interno de base de datos.'; break;
              case 'rate':
                $wait = isset($_GET['wait']) ? (int)$_GET['wait'] : 60;
                if ($wait < 1) $wait = 1;
                if ($wait > 60) $wait = 60;
                $msg = 'Ya enviamos un código hace poco. Esperá ' . $wait . 's para solicitar otro.';
                break;
            }
          ?>
          <p style="color:#ff6b6b"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <div class="input-box">
          <input type="text" name="code" pattern="\d{4}" maxlength="4" placeholder="Código (4 dígitos)" required>
          <i class='bx bx-key'></i>
        </div>
        <button type="submit" class="btn-login">Validar</button>
        <div class="register-link">
          <p><a href="/ProyectoPandora/Public/index.php?route=Auth/Forgot">Reenviar / Cambiar email</a></p>
        </div>
      </form>
    </div>
  </section>
</main>
