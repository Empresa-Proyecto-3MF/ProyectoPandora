<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/Database.php';

class Auth
{
    private const REMEMBER_COOKIE = 'pandora_remember';
    private const REMEMBER_LIFETIME = 60 * 60 * 24 * 30; // 30 días

    /**
     * Garantiza que la sesión esté iniciada antes de acceder a $_SESSION.
     */
    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function userModel(): UserModel
    {
        $db = new Database();
        $db->connectDatabase();
        return new UserModel($db->getConnection());
    }

    private static function generateRandomHex(int $bytes): string
    {
        try {
            return bin2hex(random_bytes($bytes));
        } catch (\Throwable $e) {
            return bin2hex(openssl_random_pseudo_bytes($bytes));
        }
    }

    private static function setRememberCookie(string $value, int $expiry): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        if (PHP_VERSION_ID >= 70300) {
            setcookie(self::REMEMBER_COOKIE, $value, [
                'expires' => $expiry,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            setcookie(self::REMEMBER_COOKIE, $value, $expiry, '/', '', $secure, true);
        }

        if ($expiry < time()) {
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        } else {
            $_COOKIE[self::REMEMBER_COOKIE] = $value;
        }
    }

    private static function rememberUser(int $userId): void
    {
        $model = self::userModel();
        $selector = self::generateRandomHex(9);
        $token = self::generateRandomHex(32);
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

        if ($model->storeRememberToken($userId, $selector, $tokenHash, $expiresAt)) {
            self::ensureSession();
            if (isset($_SESSION['user']) && (int)($_SESSION['user']['id'] ?? 0) === $userId) {
                $_SESSION['user']['remember_selector'] = $selector;
                $_SESSION['user']['remember_token'] = $tokenHash;
                $_SESSION['user']['remember_expires_at'] = $expiresAt;
            }
            self::setRememberCookie($selector . ':' . $token, time() + self::REMEMBER_LIFETIME);
        }
    }

    private static function forgetRememberCredentials(?int $userId = null): void
    {
        if (!empty($_COOKIE[self::REMEMBER_COOKIE])) {
            self::setRememberCookie('', time() - 3600);
        }

        if ($userId !== null) {
            $model = self::userModel();
            $model->clearRememberToken($userId);
        }

        self::ensureSession();
        if (isset($_SESSION['user'])) {
            if ($userId === null || (int)($_SESSION['user']['id'] ?? 0) === $userId) {
                $_SESSION['user']['remember_selector'] = null;
                $_SESSION['user']['remember_token'] = null;
                $_SESSION['user']['remember_expires_at'] = null;
            }
        }
    }

    private static function attemptRememberedLogin(): ?array
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if ($cookie === '') {
            return null;
        }

        $parts = explode(':', $cookie, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            self::forgetRememberCredentials();
            return null;
        }

        [$selector, $token] = $parts;
        $model = self::userModel();
        $user = $model->findByRememberSelector($selector);

        if (!$user) {
            self::forgetRememberCredentials();
            return null;
        }

        if (empty($user['remember_token']) || empty($user['remember_expires_at'])) {
            self::forgetRememberCredentials((int)$user['id']);
            return null;
        }

        if (strtotime($user['remember_expires_at']) < time()) {
            self::forgetRememberCredentials((int)$user['id']);
            return null;
        }

        $expectedHash = $user['remember_token'];
        $providedHash = hash('sha256', $token);

        if (!hash_equals($expectedHash, $providedHash)) {
            self::forgetRememberCredentials((int)$user['id']);
            return null;
        }

        self::ensureSession();
        $_SESSION['user'] = $user;
        // Rotar token para mitigar robo de cookies
        self::rememberUser((int)$user['id']);
        return $_SESSION['user'];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        self::ensureSession();
        if (!isset($_SESSION['user'])) {
            return self::attemptRememberedLogin();
        }
        return $_SESSION['user'];
    }

    public static function checkRole($requiredRoles): void
    {
        $user = self::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        if (is_array($requiredRoles)) {
            if (!in_array($user['role'], $requiredRoles, true)) {
                echo "Acceso denegado: se requiere uno de los roles " . implode(', ', $requiredRoles) . ".";
                exit;
            }
        } else {
            if ($user['role'] !== $requiredRoles) {
                echo "Acceso denegado: se requiere el rol $requiredRoles.";
                exit;
            }
        }
    }

    public static function login(array $user, bool $remember = false): void
    {
        self::ensureSession();
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }
        $_SESSION['user'] = $user;

        if ($remember && isset($user['id'])) {
            self::rememberUser((int)$user['id']);
        } else {
            if (isset($user['id'])) {
                self::forgetRememberCredentials((int)$user['id']);
            } else {
                self::forgetRememberCredentials();
            }
        }
    }

    public static function logout(): void
    {
        self::ensureSession();
        $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        self::forgetRememberCredentials($userId);

        $_SESSION = [];
        session_unset();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'] ?? false, $params['httponly'] ?? false);
        }

        session_destroy();

        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }
    }
}