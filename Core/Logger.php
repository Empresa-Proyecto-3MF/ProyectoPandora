<?php
/**
 * Logger central de la aplicación.
 * - Salida a archivos en carpeta /Logs
 * - Niveles: DEBUG, INFO, WARN, ERROR
 * - Rotación automática cuando el archivo supera 2MB
 * - Formato: YYYY-mm-dd HH:ii:ss [LEVEL] message | ctx={json}
 *
 * Uso rápido:
 *   Logger::info('Inicio app');
 *   Logger::error('Fallo en pago', ['order_id' => 123]);
 *   Logger::channel('mail')->info('Intento de envío', ['to' => 'x@y.com']);
 */
class Logger
{
    private string $file;
    private int $maxBytes = 2097152; // 2 MB

    private function __construct(string $file)
    {
        $this->file = $file;
    }

    // Obtiene instancia canal por defecto
    public static function app(): Logger { return self::channel('app'); }

    // Canal arbitrario
    public static function channel(string $name): Logger
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]/', '-', $name);
        $path = __DIR__ . '/../Logs/' . $safe . '.log';
        return new Logger($path);
    }

    // Métodos de instancia (se usan tanto vía Logger::app()->info() como channel('x')->error())
    public function debug(string $msg, array $ctx = []): void { $this->write('DEBUG', $msg, $ctx); }
    public function info(string $msg, array $ctx = []): void  { $this->write('INFO',  $msg, $ctx); }
    public function warn(string $msg, array $ctx = []): void  { $this->write('WARN',  $msg, $ctx); }
    public function error(string $msg, array $ctx = []): void { $this->write('ERROR', $msg, $ctx); }

    // Método genérico
    public function log(string $level, string $msg, array $ctx = []): void { $this->write(strtoupper($level), $msg, $ctx); }

    private function write(string $level, string $msg, array $ctx): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }

        // Rotación simple si supera tamaño máximo
        if (file_exists($this->file) && filesize($this->file) > $this->maxBytes) {
            $this->rotate();
        }

        $line = sprintf(
            "%s [%s] %s",
            date('Y-m-d H:i:s'),
            $level,
            $this->interpolate($msg, $ctx)
        );
        if (!empty($ctx)) {
            $json = json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $line .= ' | ctx=' . $json;
        }
        $line .= "\n";

        $fh = @fopen($this->file, 'ab');
        if ($fh) {
            @flock($fh, LOCK_EX);
            @fwrite($fh, $line);
            @flock($fh, LOCK_UN);
            @fclose($fh);
        } else {
            // fallback
            error_log($line);
        }
    }

    private function rotate(): void
    {
        $ts = date('Ymd-His');
        $rotated = $this->file . '.' . $ts;
        @rename($this->file, $rotated);
        // Mantener solo las 5 rotaciones más recientes por canal
        $prefix = basename($this->file);
        $dir = dirname($this->file);
        $files = glob($dir . DIRECTORY_SEPARATOR . $prefix . '.*');
        if (is_array($files) && count($files) > 5) {
            usort($files, function($a, $b){ return filemtime($a) <=> filemtime($b); });
            while (count($files) > 5) {
                $old = array_shift($files);
                @unlink($old);
            }
        }
    }

    // Reemplaza placeholders {key} por valores en $ctx
    private function interpolate(string $msg, array $ctx): string
    {
        if (strpos($msg, '{') === false) { return $msg; }
        $replace = [];
        foreach ($ctx as $k => $v) {
            if (is_scalar($v)) {
                $replace['{' . $k . '}'] = (string)$v;
            } elseif ($v instanceof \Stringable) {
                $replace['{' . $k . '}'] = (string)$v;
            }
        }
        return strtr($msg, $replace);
    }
}
