<?php
/**
 * CSRF token helper minimalista.
 * Uso:
 *   - En inicio de request: Csrf::init();
 *   - En formularios: echo Csrf::input();
 *   - En controladores POST: Csrf::validateOrThrow();
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_tokens';
    private const TOKEN_NAME = '_csrf';
    private const MAX_TOKENS = 25; // limitar cantidad para no crecer indefinidamente
    private const TTL_SECONDS = 1800; // 30 minutos

    // Posibles razones de fallo para debug/log
    private const REASON_MISSING = 'missing';
    private const REASON_NOT_FOUND = 'not_found';
    private const REASON_EXPIRED = 'expired';

    public static function init(): void
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
        self::init();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][$token] = time();
        // Recortar tokens viejos
        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_TOKENS) {
            // borrar los más antiguos
            asort($_SESSION[self::SESSION_KEY]);
            $remove = count($_SESSION[self::SESSION_KEY]) - self::MAX_TOKENS;
            foreach (array_keys($_SESSION[self::SESSION_KEY]) as $tk) {
                if ($remove-- <= 0) break;
                unset($_SESSION[self::SESSION_KEY][$tk]);
            }
        }
        return $token;
    }

    public static function input(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valida devolviendo detalle para diagnóstico.
     * @return array{success:bool,reason:string|null,age?:int}
     */
    public static function validateDetailed(?string $provided): array
    {
        self::init();
        if ($provided === null) {
            return ['success' => false, 'reason' => self::REASON_MISSING];
        }
        if (!isset($_SESSION[self::SESSION_KEY][$provided])) {
            return ['success' => false, 'reason' => self::REASON_NOT_FOUND];
        }
        $ts = (int)$_SESSION[self::SESSION_KEY][$provided];
        $age = time() - $ts;
        if ($age > self::TTL_SECONDS) {
            // Consumir token igualmente para evitar replay
            unset($_SESSION[self::SESSION_KEY][$provided]);
            return ['success' => false, 'reason' => self::REASON_EXPIRED, 'age' => $age];
        }
        // Solo uso: consumir si válido
        unset($_SESSION[self::SESSION_KEY][$provided]);
        return ['success' => true, 'reason' => null, 'age' => $age];
    }

    public static function validate(?string $provided): bool
    {
        $res = self::validateDetailed($provided);
        return $res['success'];
    }

    public static function validateOrThrow(): void
    {
        // Permitir header X-CSRF-Token como alternativa para AJAX
        $provided = $_POST[self::TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        $res = self::validateDetailed($provided);
        if (!$res['success']) {
            http_response_code(403);
            // Mensaje más claro según motivo
            $reasonMsg = 'CSRF token inválido.';
            switch ($res['reason']) {
                case self::REASON_MISSING: $reasonMsg = 'CSRF faltante en el formulario.'; break;
                case self::REASON_NOT_FOUND: $reasonMsg = 'CSRF no reconocido (probable refresh duplicado o sesión nueva).'; break;
                case self::REASON_EXPIRED: $reasonMsg = 'CSRF expirado (refresca la página).'; break;
            }
            echo $reasonMsg;
            // Opcional: regenerar automáticamente un nuevo token para que JS pueda leerlo (si se usa AJAX)
            // echo '<div data-new-csrf="' . htmlspecialchars(self::generate(), ENT_QUOTES, 'UTF-8') . '"></div>';
            exit;
        }
    }

    // Variante opcional: valida sin consumir el token en caso de éxito (útil si combinás con idempotencia)
    public static function validateDetailedReusable(?string $provided): array
    {
        self::init();
        if ($provided === null) {
            return ['success' => false, 'reason' => self::REASON_MISSING];
        }
        if (!isset($_SESSION[self::SESSION_KEY][$provided])) {
            return ['success' => false, 'reason' => self::REASON_NOT_FOUND];
        }
        $ts = (int)$_SESSION[self::SESSION_KEY][$provided];
        $age = time() - $ts;
        if ($age > self::TTL_SECONDS) {
            // Consumir token igualmente para evitar replay tras TTL
            unset($_SESSION[self::SESSION_KEY][$provided]);
            return ['success' => false, 'reason' => self::REASON_EXPIRED, 'age' => $age];
        }
        // Reutilizable: no se elimina el token aquí
        return ['success' => true, 'reason' => null, 'age' => $age];
    }

    public static function validateOrThrowReusable(): void
    {
        $provided = $_POST[self::TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        $res = self::validateDetailedReusable($provided);
        if (!$res['success']) {
            http_response_code(403);
            $reasonMsg = 'CSRF token inválido.';
            switch ($res['reason']) {
                case self::REASON_MISSING: $reasonMsg = 'CSRF faltante en el formulario.'; break;
                case self::REASON_NOT_FOUND: $reasonMsg = 'CSRF no reconocido (probable refresh duplicado o sesión nueva).'; break;
                case self::REASON_EXPIRED: $reasonMsg = 'CSRF expirado (refresca la página).'; break;
            }
            echo $reasonMsg;
            exit;
        }
    }
}
?>