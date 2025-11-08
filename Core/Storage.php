<?php

class Storage
{
    private static ?string $basePath = null;
    private static ?string $baseUrl = null;
    private static ?array $envFile = null;

    private static function loadEnvFile(): array
    {
        if (self::$envFile !== null) {
            return self::$envFile;
        }
        $envPath = dirname(__DIR__) . '/.env';
        $data = [];
        if (is_file($envPath) && is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    if (strpos(ltrim($line), '#') === 0) {
                        continue;
                    }
                    if (strpos($line, '=') === false) {
                        continue;
                    }
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\"'\r\n");
                    if ($k !== '') {
                        $data[$k] = $v;
                    }
                }
            }
        }
        self::$envFile = $data;
        return self::$envFile;
    }

    private static function env(string $key): ?string
    {
        $env = self::loadEnvFile();
        if (isset($env[$key])) {
            $value = $env[$key];
            return $value === '' ? null : $value;
        }
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if ($value === false && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
        if ($value === false) {
            return null;
        }
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    public static function basePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }
        $custom = self::env('PANDORA_STORAGE_PATH');
        if ($custom) {
            self::$basePath = rtrim($custom, '/\\');
            return self::$basePath;
        }
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : '';
        if ($documentRoot !== '') {
            $guess = $documentRoot . '/ProyectoPandora/Public/uploads';
            if (!is_dir($guess)) {
                @mkdir($guess, 0775, true);
            }
            if (is_dir($guess)) {
                self::$basePath = $guess;
                return self::$basePath;
            }
        }
        $fallback = dirname(__DIR__) . '/Public/uploads';
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0775, true);
        }
        self::$basePath = $fallback;
        return self::$basePath;
    }

    public static function baseUrl(): string
    {
        if (self::$baseUrl !== null) {
            return self::$baseUrl;
        }
        $custom = self::env('PANDORA_STORAGE_URL');
        if ($custom) {
            self::$baseUrl = rtrim($custom, '/');
            return self::$baseUrl;
        }
        // Intentar inferir desde SCRIPT_NAME
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $marker = '/ProyectoPandora/Public';
        if ($script && ($pos = strpos($script, $marker)) !== false) {
            $base = substr($script, 0, $pos) . $marker . '/uploads';
            self::$baseUrl = rtrim($base, '/');
            return self::$baseUrl;
        }
        self::$baseUrl = '/ProyectoPandora/Public/uploads';
        return self::$baseUrl;
    }

    public static function ensure(string $subdir): string
    {
        $subdir = trim($subdir, '/\\');
        $path = self::basePath() . '/' . $subdir;
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
        return $path;
    }

    public static function storeUploadedFile(array $file, string $subdir = 'profile'): ?array
    {
        if (!isset($file['tmp_name'], $file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
    $original = $file['name'] ?? 'file';
    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', (string)$original);
    $filename = uniqid(trim($subdir, '/\\') . '_', true) . '_' . $safeName;
    $subdir = trim($subdir, '/\\');
        $targetDir = self::ensure($subdir);
        $destination = $targetDir . '/' . $filename;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }
        $relative = $subdir === '' ? $filename : ($subdir . '/' . $filename);
        return [
            'relative' => $relative,
            'path' => $destination,
            'url' => self::publicUrl($relative),
        ];
    }

    public static function publicUrl(string $relativePath): string
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $relativePath)) {
            return $relativePath;
        }
        if ($relativePath[0] === '/') {
            return $relativePath;
        }
        if (strpos($relativePath, '..') !== false) {
            $relativePath = str_replace('..', '', $relativePath);
        }
        $baseUrl = self::baseUrl();
        if (preg_match('/^https?:\/\//i', $baseUrl)) {
            return $baseUrl . '/' . ltrim($relativePath, '/');
        }
        return rtrim($baseUrl, '/') . '/' . ltrim($relativePath, '/');
    }

    public static function exists(string $relativePath): bool
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            return false;
        }
        if ($relativePath[0] === '/') {
            $abs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $relativePath;
            return $abs !== '' && is_file($abs);
        }
        $relativePath = str_replace(['..', '\\'], ['', '/'], $relativePath);
        $full = self::basePath() . '/' . ltrim($relativePath, '/');
        return is_file($full);
    }

    public static function resolveProfileUrl(?string $storedPath): string
    {
        $storedPath = $storedPath ? trim((string)$storedPath) : '';
        if ($storedPath === '') {
            return self::fallbackProfileUrl();
        }
        if (preg_match('/^https?:\/\//i', $storedPath)) {
            return $storedPath;
        }
        if ($storedPath[0] === '/') {
            if (self::exists($storedPath)) {
                return $storedPath;
            }
            return self::fallbackProfileUrl();
        }
        if (!self::exists($storedPath)) {
            $legacy = '/ProyectoPandora/Public/img/imgPerfil/' . ltrim($storedPath, '/');
            $legacyFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $legacy;
            if ($legacyFs && is_file($legacyFs)) {
                return $legacy;
            }
            return self::fallbackProfileUrl();
        }
        return self::publicUrl($storedPath);
    }

    public static function fallbackProfileUrl(): string
    {
        $default = '/ProyectoPandora/Public/img/imgPerfil/default.png';
        $defaultFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $default;
        if ($defaultFs && is_file($defaultFs)) {
            return $default;
        }
        $fallback = '/ProyectoPandora/Public/img/Innovasys_V2.png';
        return $fallback;
    }
    public static function resolveDeviceUrl(?string $storedPath): string
    {
        $storedPath = $storedPath ? trim((string)$storedPath) : '';
        if ($storedPath === '') {
            return self::fallbackDeviceUrl();
        }
        if (preg_match('/^https?:\/\//i', $storedPath)) {
            return $storedPath;
        }
        // Normalizar rutas Windows absolutas (C:\... o \\servidor\...): usar solo el nombre de archivo
        $norm = str_replace('\\', '/', $storedPath);
        if (preg_match('/^[A-Za-z]:\//', $norm) || str_starts_with($norm, '//')) {
            $base = basename($norm);
            if ($base !== '') {
                // Buscar primero en uploads/device
                $maybe = 'device/' . $base;
                if (self::exists($maybe)) { return self::publicUrl($maybe); }
                // Luego en uploads raíz
                if (self::exists($base)) { return self::publicUrl($base); }
                // Y por último en carpeta pública legacy
                $legacy = '/ProyectoPandora/Public/img/imgDispositivos/' . $base;
                $legacyFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $legacy;
                if ($legacyFs && is_file($legacyFs)) { return $legacy; }
                return self::fallbackDeviceUrl();
            }
        }
        if ($storedPath[0] === '/') {
            $fs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $storedPath;
            if ($fs && is_file($fs)) {
                return $storedPath;
            }
            return self::fallbackDeviceUrl();
        }

        // Nuevo storage (uploads/...)
        if (self::exists($storedPath)) {
            return self::publicUrl($storedPath);
        }

        // En caso de que viniera sin prefijo (ej. solo nombre de archivo)
        $maybeDevice = 'device/' . ltrim($storedPath, '/');
        if ($maybeDevice !== $storedPath && self::exists($maybeDevice)) {
            return self::publicUrl($maybeDevice);
        }

        // Carpeta pública legacy
        $legacy = '/ProyectoPandora/Public/img/imgDispositivos/' . ltrim($storedPath, '/');
        $legacyFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $legacy;
        if ($legacyFs && is_file($legacyFs)) {
            return $legacy;
        }

        return self::fallbackDeviceUrl();
    }

    public static function fallbackDeviceUrl(): string
    {
        $candidate = '/ProyectoPandora/Public/img/imgDispositivos/NoFoto.jpg';
        $candidateFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $candidate;
        if ($candidateFs && is_file($candidateFs)) {
            return $candidate;
        }
        return self::fallbackProfileUrl();
    }

    public static function resolveInventoryUrl(?string $storedPath): string
    {
        $storedPath = $storedPath ? trim((string)$storedPath) : '';
        if ($storedPath === '') {
            return self::fallbackInventoryUrl();
        }
        if (preg_match('/^https?:\/\//i', $storedPath)) {
            return $storedPath;
        }
        // Normalizar rutas Windows absolutas
        $norm = str_replace('\\', '/', $storedPath);
        if (preg_match('/^[A-Za-z]:\//', $norm) || str_starts_with($norm, '//')) {
            $base = basename($norm);
            if ($base !== '') {
                $maybe = 'inventory/' . $base;
                if (self::exists($maybe)) { return self::publicUrl($maybe); }
                if (self::exists($base)) { return self::publicUrl($base); }
                $legacy = '/ProyectoPandora/Public/img/imgInventario/' . $base;
                $legacyFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $legacy;
                if ($legacyFs && is_file($legacyFs)) { return $legacy; }
                return self::fallbackInventoryUrl();
            }
        }
        if ($storedPath[0] === '/') {
            $fs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $storedPath;
            if ($fs && is_file($fs)) {
                return $storedPath;
            }
            return self::fallbackInventoryUrl();
        }
        if (self::exists($storedPath)) {
            return self::publicUrl($storedPath);
        }
        $maybeInventory = 'inventory/' . ltrim($storedPath, '/');
        if ($maybeInventory !== $storedPath && self::exists($maybeInventory)) {
            return self::publicUrl($maybeInventory);
        }
        $legacy = '/ProyectoPandora/Public/img/imgInventario/' . ltrim($storedPath, '/');
        $legacyFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $legacy;
        if ($legacyFs && is_file($legacyFs)) {
            return $legacy;
        }
        return self::fallbackInventoryUrl();
    }

    public static function fallbackInventoryUrl(): string
    {
        $candidate = '/ProyectoPandora/Public/img/imgInventario/NoItem.jpg';
        $candidateFs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $candidate;
        if ($candidateFs && is_file($candidateFs)) {
            return $candidate;
        }
        return self::fallbackDeviceUrl();
    }
}

