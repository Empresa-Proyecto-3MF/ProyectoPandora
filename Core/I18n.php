<?php
class I18n {
    private static $locale = 'es';
    private static $messages = [];
    private static $supported = ['es','en','pt'];

    private static function ensureSession(){
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
    }

    public static function boot(): void {
        self::ensureSession();
        $loc = isset($_SESSION['lang']) ? strtolower((string)$_SESSION['lang']) : 'es';
        self::setLocale($loc);
        if (!function_exists('__')) {
            function __($key, $params = []) { return I18n::t($key, $params); }
        }
    }

    public static function setLocale(string $loc): void {
        $loc = strtolower(trim($loc));
        if (!in_array($loc, self::$supported, true)) { $loc = 'es'; }
        if (self::$locale === $loc && !empty(self::$messages)) { return; }
        self::$locale = $loc;
        $_SESSION['lang'] = $loc;
        self::loadMessages($loc);
    }

    public static function getLocale(): string { return self::$locale; }

    private static function loadMessages(string $loc): void {
        $base = __DIR__ . '/../Lang/';
        $file = $base . $loc . '.php';
        $fallback = $base . 'es.php';
        $msgs = [];
        if (file_exists($file)) { $msgs = include $file; }
        if (!is_array($msgs)) { $msgs = []; }
        // fallback para claves faltantes
        if ($loc !== 'es' && file_exists($fallback)) {
            $es = include $fallback;
            if (is_array($es)) { $msgs = array_replace($es, $msgs); }
        }
        self::$messages = $msgs;
    }

    public static function t(string $key, array $params = []) {
        $val = self::$messages[$key] ?? $key;
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $val = str_replace('{' . $k . '}', (string)$v, $val);
            }
        }
        return $val;
    }
}
