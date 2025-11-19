<?php
/* Diagnóstico de envío de correo en XAMPP/Windows
// Abre: http://localhostmail_diag.php
header('Content-Type: text/plain; charset=UTF-8');

function line($k, $v) { echo str_pad($k.':', 26) . ($v === '' ? '(vacio)' : $v) . "\n"; }

// Recoger valores base de php.ini cargado
$phpIniPath = php_ini_loaded_file();
$SMTP = ini_get('SMTP');
$PORT = ini_get('smtp_port');
$FROM = ini_get('sendmail_from');
$SMPATH = ini_get('sendmail_path');
$pathEvaluado = $SMPATH;
// Si sendmail_path tiene comillas, extraer ejecutable para comprobar existencia
if (preg_match('/^"([^"]+)"/',$SMPATH,$m)){
	$pathEvaluado = $m[1];
}
$exists = $pathEvaluado && file_exists($pathEvaluado) ? 'SI' : 'NO';
$openssl = extension_loaded('openssl') ? 'SI' : 'NO';
// Hora de modificación del php.ini (si accesible)
$phpIniMtime = $phpIniPath && file_exists($phpIniPath) ? date('Y-m-d H:i:s', filemtime($phpIniPath)) : '(no accesible)';

// Variables de entorno usadas por MailHelper (opcional)
$envHost = getenv('PANDORA_SMTP_HOST') ?: '';
$envPort = getenv('PANDORA_SMTP_PORT') ?: '';
$envFrom = getenv('PANDORA_MAIL_FROM') ?: '';

// Mostrar configuración actual
echo "== php.ini (mail function) ==\n";
line('php.ini ruta', $phpIniPath ?: '(desconocida)');
line('php.ini mtime', $phpIniMtime);
line('SMTP', $SMTP);
line('smtp_port', $PORT);
line('sendmail_from', $FROM);
line('sendmail_path', $SMPATH);
line('sendmail.exe existe', $exists);
line('openssl habilitado', $openssl);

echo "\n== Variables de entorno (app) ==\n";
line('PANDORA_SMTP_HOST', $envHost);
line('PANDORA_SMTP_PORT', $envPort);
line('PANDORA_MAIL_FROM', $envFrom);

// Conectividad a smtp.gmail.com:587
$errno = 0; $errstr = '';
$socketOk = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
$socketStatus = $socketOk ? 'OK' : ("ERROR $errno: $errstr");
if ($socketOk) { fclose($socketOk); }

echo "\n== Test TCP a smtp.gmail.com:587 ==\n";
line('Conexion', $socketStatus);

// Mostrar ruta esperada por defecto y validar formato de sendmail_path si vacío
echo "\n== Sugerencia / Validaciones sendmail_path ==\n";
line('Ejemplo', '"C:\\xampp\\sendmail\\sendmail.exe" -t');
if ($SMPATH === '') {
	echo "sendmail_path ESTA VACIO -> Edita el php.ini correcto (c:\\xampp\\php\\php.ini) y reinicia Apache.\n";
} else {
	if (!str_contains($SMPATH, 'sendmail.exe')) {
		echo "Aviso: sendmail_path no contiene sendmail.exe, revisa comillas/ruta.\n";
	}
	// Detectar error comun: falta espacio antes del -t => "sendmail.exe-t"
	if (preg_match('/sendmail\.exe-+t/i', $SMPATH)) {
		echo "ERROR: sendmail_path tiene 'sendmail.exe-t' (sin espacio). Debe ser \"C:\\xampp\\sendmail\\sendmail.exe\" -t\n";
	}
}

// Intentar leer sendmail.ini para mostrar parámetros críticos
$sendmailIniPathGuess = 'C:\\xampp\\sendmail\\sendmail.ini';
echo "\n== sendmail.ini (resumen) ==\n";
if (file_exists($sendmailIniPathGuess)) {
	$raw = @file_get_contents($sendmailIniPathGuess);
	if ($raw !== false) {
		// Extraer líneas clave sin exponer password completo
		$lines = preg_split('/\r?\n/', $raw);
		$showKeys = ['smtp_server','smtp_port','smtp_ssl','auth_username','auth_password','force_sender'];
		$found = [];
		foreach ($lines as $ln) {
			if (preg_match('/^\s*([a-zA-Z_]+)\s*=\s*(.+)$/',$ln,$mm)) {
				$key = trim($mm[1]);
				$val = trim($mm[2]);
				if (in_array($key,$showKeys)) {
					if ($key === 'auth_password') {
						// Ocultar la contraseña real conservando longitud
						$valMask = str_repeat('*', strlen($val));
						$val = $valMask . ' (len=' . strlen($val) . ')';
					}
					$found[$key] = $val;
				}
			}
		}
		foreach ($showKeys as $k) {
			line($k, $found[$k] ?? '(no encontrado)');
		}
		// Validaciones adicionales sobre auth_password
		if (isset($found['auth_password'])) {
			$lenRaw = strlen(str_replace('*','',$found['auth_password'])); // imposible recuperar, solo mensaje
			if ($lenRaw !== 16) {
				echo "Aviso: El App Password de Gmail DEBE tener 16 caracteres.\n";
			}
		}
	} else {
		echo "No se pudo leer sendmail.ini.\n";
	}
} else {
	echo "sendmail.ini no encontrado en ruta esperada: $sendmailIniPathGuess\n";
}

// Probar un envío mail() rápido (no usa STARTTLS explícito aquí; depende de tu config sendmail.ini)
$to = $envFrom ?: $FROM; // para probar, mandamos al remitente
$subject = 'Prueba DIAG mail()';
$body = 'Este es un correo de diagnostico generado por mail_diag.php';

// Intentar envío mail(); si falla y falta configuración, proponer alternativa
$sent = @mail($to, $subject, $body);
line('mail() envio', $sent ? 'OK' : 'FALLO (ver Logs/mail.log si tu app lo usa)'); 

*/
http_response_code(404);
exit; // Archivo deshabilitado en producción