<?php

if (!function_exists('public_path')) {
    function public_path(string $relative = ''): string
    {
        static $base = null;
        if ($base === null) {
            $base = realpath(__DIR__ . '/../Public');
            if ($base === false) {
                $base = __DIR__ . '/../Public';
            }
            $base = rtrim($base, '/\\');
        }
        $relative = ltrim($relative, '/\\');
        return $relative === '' ? $base : $base . '/' . $relative;
    }
}

if (!function_exists('ensure_public_dir')) {
    function ensure_public_dir(string $relative): string
    {
        $path = public_path($relative);
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
        return $path;
    }
}

if (!function_exists('sanitize_image_filename')) {
    function sanitize_image_filename(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'foto_' . date('Ymd_His');
        }
        $name = str_replace(['\\', '/'], '_', $name);
        $name = preg_replace('/[^A-Za-z0-9\._\- ]+/u', '_', $name);
        $name = trim($name);
        if ($name === '' || $name === '.' || $name === '..') {
            return 'foto_' . date('Ymd_His');
        }
        return $name;
    }
}

if (!function_exists('unique_image_filename')) {
    function unique_image_filename(string $dir, string $filename): string
    {
        $dir = rtrim($dir, '/\\');
        $info = pathinfo($filename);
        $base = $info['filename'] ?? 'foto';
        $ext = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';
        $candidate = $base . $ext;
        $counter = 1;
        while (is_file($dir . '/' . $candidate)) {
            $candidate = $base . '_' . $counter . $ext;
            $counter++;
        }
        return $candidate;
    }
}

if (!function_exists('device_image_url')) {
    function device_image_url(?string $stored): string
    {
        $default = 'img/imgDispositivos/NoFoto.jpg';
        $stored = trim((string)$stored);
        if ($stored === '') {
            return $default;
        }
        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        if (preg_match('/^https?:\/\//i', $normalized)) {
            return $normalized;
        }
        if (strpos($normalized, 'img/') === 0) {
            if (is_file(public_path($normalized))) {
                return $normalized;
            }
        } else {
            $candidate = 'img/imgDispositivos/' . $normalized;
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
            $candidate = 'img/device/' . $normalized;
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
        }
        return $default;
    }
}

if (!function_exists('inventory_image_url')) {
    function inventory_image_url(?string $stored): string
    {
        $default = 'img/imgDispositivos/NoFoto.jpg';
        $stored = trim((string)$stored);
        if ($stored === '') {
            return $default;
        }
        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        if (preg_match('/^https?:\/\//i', $normalized)) {
            return $normalized;
        }
        if (strpos($normalized, 'img/') === 0) {
            if (is_file(public_path($normalized))) {
                return $normalized;
            }
        } else {
            $candidate = 'img/inventory/' . $normalized;
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
            $legacy = 'img/imgInventario/' . $normalized;
            if (is_file(public_path($legacy))) {
                return $legacy;
            }
        }
        return $default;
    }
}

if (!function_exists('profile_image_url')) {
    function profile_image_url(?string $stored): string
    {
        $default = 'img/imgPerfil/default.png';
        $stored = trim((string)$stored);
        if ($stored === '') {
            return $default;
        }
        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        if (preg_match('/^https?:\/\//i', $normalized)) {
            return $normalized;
        }
        if (strpos($normalized, 'img/') === 0) {
            if (is_file(public_path($normalized))) {
                return $normalized;
            }
        } else {
            $candidate = 'img/imgPerfil/' . $normalized;
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
            $candidate = 'img/profile/' . $normalized;
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
        }
        return $default;
    }
}

if (!function_exists('save_device_photo')) {
    function save_device_photo(array $file, int $userId): ?string
    {
        if (empty($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $dirRelative = 'img/imgDispositivos/' . $userId;
        $targetDir = ensure_public_dir($dirRelative);
        $originalName = $file['name'] ?? ('foto_' . date('Ymd_His'));
        $safeName = sanitize_image_filename($originalName);
        $safeName = unique_image_filename($targetDir, $safeName);
        $destination = $targetDir . '/' . $safeName;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }
        return $dirRelative . '/' . $safeName;
    }
}

if (!function_exists('save_inventory_photo')) {
    function save_inventory_photo(array $file): ?string
    {
        if (empty($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $dirRelative = 'img/imgInventario';
        $targetDir = ensure_public_dir($dirRelative);
        $originalName = $file['name'] ?? ('inventario_' . date('Ymd_His'));
        $safeName = sanitize_image_filename($originalName);
        $safeName = unique_image_filename($targetDir, $safeName);
        $destination = $targetDir . '/' . $safeName;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }
        return $dirRelative . '/' . $safeName;
    }
}

if (!function_exists('save_profile_photo')) {
    function save_profile_photo(array $file, int $userId): ?string
    {
        if (empty($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $dirRelative = 'img/imgPerfil';
        $targetDir = ensure_public_dir($dirRelative);
        $originalName = $file['name'] ?? ('perfil_' . date('Ymd_His'));
        $safeName = 'user' . $userId . '_' . sanitize_image_filename($originalName);
        $safeName = unique_image_filename($targetDir, $safeName);
        $destination = $targetDir . '/' . $safeName;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }
        return $dirRelative . '/' . $safeName;
    }
}

if (!function_exists('save_ticket_photo')) {
    function save_ticket_photo(string $tmpFile, string $originalName, int $ticketId): ?string
    {
        if ($tmpFile === '' || !is_uploaded_file($tmpFile)) {
            return null;
        }
        $dirRelative = 'img/ticket/' . $ticketId;
        $targetDir = ensure_public_dir($dirRelative);
        $safeName = sanitize_image_filename($originalName);
        $safeName = unique_image_filename($targetDir, $safeName);
        $destination = $targetDir . '/' . $safeName;
        if (!@move_uploaded_file($tmpFile, $destination)) {
            return null;
        }
        return $dirRelative . '/' . $safeName;
    }
}

if (!function_exists('ticket_photo_urls')) {
    function ticket_photo_urls(int $ticketId): array
    {
        $entries = [];
        $dirRelative = 'img/ticket/' . $ticketId;
        $absDir = public_path($dirRelative);
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (is_dir($absDir)) {
            $files = @scandir($absDir) ?: [];
            foreach ($files as $fn) {
                if ($fn === '.' || $fn === '..') { continue; }
                $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) { continue; }
                $path = $dirRelative . '/' . $fn;
                $entries[$path] = @filemtime($absDir . '/' . $fn) ?: 0;
            }
        }
        if (empty($entries)) {
            $legacyRel = 'img/imgTickets/' . $ticketId;
            $legacyDir = public_path($legacyRel);
            if (is_dir($legacyDir)) {
                $files = @scandir($legacyDir) ?: [];
                foreach ($files as $fn) {
                    if ($fn === '.' || $fn === '..') { continue; }
                    $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed, true)) { continue; }
                    $path = $legacyRel . '/' . rawurlencode($fn);
                    $entries[$path] = @filemtime($legacyDir . '/' . $fn) ?: 0;
                }
            }
        }
        if ($entries) {
            uasort($entries, static function ($a, $b) {
                if ($a === $b) { return 0; }
                return ($a < $b) ? -1 : 1;
            });
            return array_keys($entries);
        }
        return [];
    }
}

if (!function_exists('remove_file_if_exists')) {
    function remove_file_if_exists(?string $relative): void
    {
        $relative = trim((string)$relative);
        if ($relative === '') {
            return;
        }
        $normalized = ltrim(str_replace('\\', '/', $relative), '/');
        $path = public_path($normalized);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
