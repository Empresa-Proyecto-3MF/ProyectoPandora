<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Página no encontrada</title>
<style>
 body{font-family:Arial,system-ui;background:#0f0f10;color:#eee;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;margin:0}
 h1{font-size:3rem;margin:.5rem 0;color:#ff6b6b}
 p{max-width:600px;text-align:center;line-height:1.5}
 a{color:#61dafb;text-decoration:none;border:1px solid #61dafb;padding:.6rem 1rem;border-radius:6px;margin-top:1rem;display:inline-block}
 a:hover{background:#61dafb;color:#0f0f10}
 .code{font-family:monospace;background:#1e1e22;padding:.3rem .5rem;border-radius:4px}
</style>stylestyle
</head>
<body>
 <h1>404</h1>
 <p>La página que buscas no existe o fue movida. Verifica la URL o regresa al inicio.</p>
 <p class="code">Ruta: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?></p>
 <a href="/">Ir al Inicio</a>
</body>
</html>