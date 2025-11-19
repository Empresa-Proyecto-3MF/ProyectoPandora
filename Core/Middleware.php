<?php
/**
 * Middleware simple para auth/roles.
 * Sirve de fachada de Auth, para centralizar checks futuros.
 */
require_once __DIR__ . '/Auth.php';

class Middleware
{
    public static function requireAuth(): void
    {
        $u = Auth::user();
        if (!$u) {
            header('Location: index.php?route=Auth/Login');
            exit;
        }
    }

    public static function requireRoles(array $roles): void
    {
        $u = Auth::user();
        if (!$u || !in_array($u['role'] ?? '', $roles, true)) {
            header('Location: index.php?route=Auth/Login');
            exit;
        }
    }
}
