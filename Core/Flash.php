<?php
/**
 * Flash messages simples basados en sesión.
 * - set($type, $message)
 * - getAll(): retorna y limpia
 * Tipos sugeridos: success, error, warning, info
 */
class Flash
{
    private const KEY = '_flash';
    private const MAX_MESSAGES = 50; // evita crecimiento ilimitado de la sesión

    private static function ensure()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION[self::KEY]) || !is_array($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    public static function set(string $type, string $message): void
    {
        self::ensure();
        $_SESSION[self::KEY][] = [
            'type' => $type,
            'message' => $message,
            'time' => time()
        ];
        // Limitar tamaño para no inflar la sesión indefinidamente
        $count = count($_SESSION[self::KEY]);
        if ($count > self::MAX_MESSAGES) {
            // conservar los últimos MAX_MESSAGES
            $_SESSION[self::KEY] = array_slice($_SESSION[self::KEY], -self::MAX_MESSAGES);
        }
    }

    public static function getAll(): array
    {
        self::ensure();
        $all = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = []; // limpiar al leer
        return $all;
    }

    public static function peek(): array
    {
        self::ensure();
        return $_SESSION[self::KEY];
    }

    // Helpers de conveniencia
    public static function success(string $message): void { self::set('success', $message); }
    public static function error(string $message): void { self::set('error', $message); }
    public static function warning(string $message): void { self::set('warning', $message); }
    public static function info(string $message): void { self::set('info', $message); }
    // Éxito silencioso: se guarda pero por defecto no se muestra (solo si se incluye explícitamente)
    public static function successQuiet(string $message): void { self::set('success_quiet', $message); }

    /**
     * Adopta mensajes de query (?success=..&error=..) y los convierte en flash
     * para unificar UX. Llama una vez temprano (e.g., en index.php) y limpia.
     */
    public static function adoptFromQuery(): void
    {
        $map = ['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'];
        foreach ($map as $param => $type) {
            if (isset($_GET[$param]) && $_GET[$param] !== '') {
                // Decodificar por si vienen urlencoded
                $raw = (string)$_GET[$param];
                $msg = trim(urldecode($raw));
                if ($msg === '') { continue; }
                // Suprimir éxitos triviales tipo ?success=1|ok|true|yes
                if ($type === 'success') {
                    $lower = strtolower($msg);
                    if (in_array($lower, ['1','ok','true','yes','si','sí'], true)) {
                        // no mostrar nada para estos
                        continue;
                    }
                }
                self::set($type, $msg);
            }
        }
    }
}
