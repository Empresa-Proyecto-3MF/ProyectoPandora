<?php
/**
 * Pequeño helper de envío de mail. Usa mail() nativo.
 * En entornos donde mail() no esté configurado (XAMPP por defecto),
 * se puede reemplazar por logging a archivo.
 */
require_once __DIR__ . '/Logger.php';

class MailHelper
{
    // Se eliminó outbox: ya no guardamos copias .eml; logging central con Logger

    public static function send(string $to, string $subject, string $body): bool
    {
        // Config SMTP básica para mail() en Windows (sin PHPMailer)
        $smtpHost = getenv('PANDORA_SMTP_HOST') ?: '';
        $smtpPort = getenv('PANDORA_SMTP_PORT') ?: '';
        $mailFrom = getenv('PANDORA_MAIL_FROM') ?: '';
        if ($smtpHost) { @ini_set('SMTP', $smtpHost); }
        if ($smtpPort) { @ini_set('smtp_port', (string)$smtpPort); }
        if ($mailFrom) { @ini_set('sendmail_from', $mailFrom); }

        // Encabezados recomendados para mejor entregabilidad en Gmail
        $fromName = 'Innovasys';
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        if ($mailFrom) {
            $headers[] = 'From: ' . self::formatAddress($mailFrom, $fromName);
            $headers[] = 'Reply-To: ' . self::formatAddress($mailFrom, $fromName);
        }
        $headers[] = 'X-Mailer: PHP/' . PHP_VERSION;
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . self::generateMessageId($mailFrom) . '>';

        // BCC opcional para trazabilidad (p.ej., a remitente)
        $bccSelf = getenv('PANDORA_MAIL_BCC_SELF');
        if ($bccSelf && $mailFrom) {
            $headers[] = 'Bcc: ' . $mailFrom;
        }

        $headersStr = implode("\r\n", $headers);

        // Si el cuerpo no parece HTML, envolver en plantilla mínima
        $looksLikeHtml = (stripos($body, '<html') !== false) || (stripos($body, '<body') !== false) || (stripos($body, '<p') !== false) || (stripos($body, '<div') !== false);
        if (!$looksLikeHtml) {
            $safe = htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $body = "<html><body><pre style=\"font-family:Segoe UI,Roboto,Arial,sans-serif;font-size:14px;white-space:pre-wrap;\">$safe</pre></body></html>";
        }

        // Codificar subjet de forma segura para UTF-8
        $subjectEnc = self::encodeSubject($subject);

        $start = microtime(true);
    $ok = @mail($to, $subjectEnc, $body, $headersStr);
        $elapsedMs = (int) round((microtime(true) - $start) * 1000);
        $err = $ok ? null : (function_exists('error_get_last') ? error_get_last() : null);

        // Log SIEMPRE el intento (éxito o fallo) para trazabilidad
        $logger = Logger::channel('mail');
        $logger->info('mail() intento envío', [
            'to' => $to,
            'subject' => $subject,
            'from' => $mailFrom,
            'status' => $ok ? 'OK' : 'FAIL',
            'elapsed_ms' => $elapsedMs,
            'smtp' => [ 'host' => $smtpHost, 'port' => $smtpPort ],
            'error' => $err ? ($err['message'] ?? 'unknown') : null,
        ]);

        return $ok;
    }

    // logAttempt removido: ahora se usa Logger central

    private static function encodeSubject(string $subject): string
    {
        // RFC 2047: codificación base64 para UTF-8
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private static function formatAddress(string $email, string $name = ''): string
    {
        $name = trim($name);
        if ($name === '') return $email;
        // Escapar comillas en el nombre
        $safe = '"' . str_replace('"', '\\"', $name) . '"';
        return $safe . ' <' . $email . '>';
    }

    private static function generateMessageId(?string $from): string
    {
        $domain = 'localhost';
        if ($from && strpos($from, '@') !== false) {
            $domain = substr($from, strpos($from, '@') + 1);
        }
        return uniqid('', true) . '@' . $domain;
    }

}
