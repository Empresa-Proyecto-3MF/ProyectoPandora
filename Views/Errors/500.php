<?php
http_response_code(500);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Error interno</title>
<style>
 body{font-family:Arial,system-ui;background:#101012;color:#eee;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;margin:0}
 h1{font-size:3rem;margin:.5rem 0;color:#ffb347}
 p{max-width:640px;text-align:center;line-height:1.5}
 a{color:#61dafb;text-decoration:none;border:1px solid #61dafb;padding:.6rem 1rem;border-radius:6px;margin-top:1rem;display:inline-block}
 a:hover{background:#61dafb;color:#101012}
 pre{background:#1e1e22;padding:.75rem 1rem;border-radius:6px;overflow:auto;max-width:90vw}
</style>stylestyle
</head>
<body>
 <h1>Error 500</h1>
 <p>Ha ocurrido un problema interno. Nuestro equipo lo revisará. Intenta nuevamente más tarde.</p>
 <?php
 
 $debug = isset($GLOBALS['APP_DEBUG']) ? (bool)$GLOBALS['APP_DEBUG'] : false;
 if ($debug && isset($GLOBALS['__last_exception']) && $GLOBALS['__last_exception'] instanceof Throwable):
     $ex = $GLOBALS['__last_exception'];
 ?>
   <pre><?php echo htmlspecialchars($ex->getMessage() . "\n" . $ex->getTraceAsString()); ?></pre>
 <?php endif; ?>
 <a href="/">Volver al Inicio</a>
</body>
</html>