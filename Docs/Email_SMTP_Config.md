# Configurar envío de correo (SMTP) en XAMPP/Windows

Este proyecto usa `mail()` de PHP para enviar el código de recuperación. En XAMPP/Windows, `mail()` depende de la configuración SMTP de PHP. Tenés dos opciones:

- Opción A (recomendada): definir variables de entorno y dejar que la app configure `SMTP`, `smtp_port` y `sendmail_from` automáticamente.
- Opción B: configurar directamente `php.ini`.

Además, si `mail()` no está disponible, el sistema escribe el contenido en `Logs/mail.log` (se crea automáticamente).

---

## Opción A: Variables de entorno

La app lee estas variables:

- PANDORA_SMTP_HOST (ej.: `smtp.gmail.com` o `smtp.tu_proveedor.com`)
- PANDORA_SMTP_PORT (ej.: `587`)
- PANDORA_MAIL_FROM (ej.: `no-reply@tu-dominio.com`)

La app hace `ini_set('SMTP', host)`, `ini_set('smtp_port', port)` y `ini_set('sendmail_from', from)` antes de llamar a `mail()`.

### Windows PowerShell (persistente para tu usuario)

```powershell
setx PANDORA_SMTP_HOST "smtp.tu_proveedor.com"
setx PANDORA_SMTP_PORT "587"
setx PANDORA_MAIL_FROM "no-reply@tu-dominio.com"
```

Cerrá y volvé a abrir XAMPP/Apache para que los cambios tomen efecto.

### Apache (httpd.conf o VirtualHost)

Dentro del VirtualHost o global:

```
SetEnv PANDORA_SMTP_HOST smtp.tu_proveedor.com
SetEnv PANDORA_SMTP_PORT 587
SetEnv PANDORA_MAIL_FROM no-reply@tu-dominio.com
```

Reiniciá Apache.

---

## Opción B: Editar php.ini

Abrí tu `php.ini` (en XAMPP suele estar en `xampp/php/php.ini`) y configurá:

```
SMTP = smtp.tu_proveedor.com
smtp_port = 587
sendmail_from = no-reply@tu-dominio.com
```

Guardá cambios y reiniciá Apache.

---

## Verificación rápida

1) Usá el flujo "¿Olvidaste tu contraseña?" e ingresá un email registrado.
2) Si no recibís el mail:
   - revisá `Logs/mail.log` (se guarda el envío fallido con el código).
   - revisá que las variables o php.ini estén correctamente seteados.
   - reiniciá Apache.

---

## Notas

- Para entornos de pruebas, podés usar Mailtrap o Papercut.
- El sistema bloquea por 10 minutos tras 5 intentos de código fallidos.
- Los códigos se guardan hasheados en la base (`reset_code`).

---

## Configuración SMTP (Gmail con contraseña de aplicación)

Si tenés una contraseña de aplicación de Gmail (16 dígitos), podés usar `mail()` de PHP con archivo `sendmail.ini` (en XAMPP) o variables de entorno + edición de `php.ini`.

### Paso 1: Activar App Password en Gmail
1. Tener 2FA habilitado en la cuenta.
2. Generar una contraseña de aplicación (16 caracteres) para "Mail" y dispositivo "Other".

### Paso 2: Editar php.ini
Ubicado en `C:\xampp\php\php.ini`:

```
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = tu_correo@gmail.com
```

Guardar y reiniciar Apache.

### Paso 3: Configurar sendmail.ini (opcional si usás sendmail en XAMPP)
En `C:\xampp\sendmail\sendmail.ini` (si existe):

```
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=auto
auth_username=tu_correo@gmail.com
auth_password=CONTRASEÑA_DE_APLICACION
force_sender=tu_correo@gmail.com
```

### Paso 4: Variables de entorno (alternativa sin sendmail.ini)
```
setx PANDORA_SMTP_HOST "smtp.gmail.com"
setx PANDORA_SMTP_PORT "587"
setx PANDORA_MAIL_FROM "tu_correo@gmail.com"
```
Reiniciá Apache.

### Prueba
Usá “¿Olvidaste tu contraseña?”; si no llega el mail:
- Revisar `Logs/mail.log` para ver el código.
- Verificar puertos salientes (algunas redes bloquean 587).
- Confirmar que la App Password es la correcta.

### Seguridad
- Los códigos de recuperación se guardan hasheados.
- Bloqueo tras 5 intentos fallidos (10 min).
- No se expone si el email existe (respuesta genérica en el envío).
