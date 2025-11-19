<?php
/**
 * Idempotency helper (session-based) para evitar duplicados en envíos rápidos.
 * Uso en formularios:
 *   echo Idempotency::input();
 * En controladores POST (tras CSRF):
 *   if (!Idempotency::checkAndLock($_POST['_rid'] ?? null)) {
 *       // Duplicado: redirigir o devolver 409 sin crear el recurso de nuevo
 *   }
 */
class Idempotency
{
    private const SESSION_KEY = '_idem_keys';
    private const FIELD_NAME = '_rid';
    private const TTL_SECONDS = 120; // ventana de 2 minutos
    private const MAX_KEYS = 100;

    private static function init(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    public static function generate(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function input(): string
    {
        self::init();
        $key = self::generate();
        // Pre-registro tentativo para permitir verificación rápida
        $_SESSION[self::SESSION_KEY][$key] = time();
        self::garbageCollect();
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function checkAndLock(?string $provided): bool
    {
        self::init();
        if ($provided === null) return false;
        $now = time();
        // Si no existe, permitir primer uso y registrar
        if (!isset($_SESSION[self::SESSION_KEY][$provided])) {
            $_SESSION[self::SESSION_KEY][$provided] = $now;
            self::garbageCollect();
            return true;
        }
        // Existe: verificar si está dentro de la ventana TTL (duplicado reciente)
        $age = $now - (int)$_SESSION[self::SESSION_KEY][$provided];
        if ($age <= self::TTL_SECONDS) {
            return false; // duplicado
        }
        // Expiró: reusar clave marcándola como reciente
        $_SESSION[self::SESSION_KEY][$provided] = $now;
        self::garbageCollect();
        return true;
    }

    private static function garbageCollect(): void
    {
        // Limpiar expirados y limitar cantidad
        $now = time();
        foreach ($_SESSION[self::SESSION_KEY] as $k => $ts) {
            if ($now - (int)$ts > self::TTL_SECONDS) unset($_SESSION[self::SESSION_KEY][$k]);
        }
        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_KEYS) {
            asort($_SESSION[self::SESSION_KEY]);
            $remove = count($_SESSION[self::SESSION_KEY]) - self::MAX_KEYS;
            foreach (array_keys($_SESSION[self::SESSION_KEY]) as $k) {
                if ($remove-- <= 0) break;
                unset($_SESSION[self::SESSION_KEY][$k]);
            }
        }
    }
}
