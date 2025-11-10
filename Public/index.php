<?php
// Sesión con cookies seguras
if (session_status() === PHP_SESSION_NONE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if (PHP_VERSION_ID >= 70300) {
        // Desde PHP 7.3 se aceptan opciones en array con estas claves
        $params = [
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        session_set_cookie_params($params);
    } else {
        // Fallback para versiones anteriores: usar INI y firma antigua
        ini_set('session.cookie_httponly', '1');
        if ($secure) { ini_set('session.cookie_secure', '1'); }
        // samesite vía INI (si está disponible en esta versión)
        @ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params(0, '/');
    }
    session_start();
}

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Core/Flash.php';
Auth::user();
Csrf::init();
// Unificar mensajes antiguos por query (?success, ?error, etc.) con Flash
Flash::adoptFromQuery();

// Zona horaria: Uruguay
date_default_timezone_set('America/Montevideo');

$currentUrl = $_SERVER['REQUEST_URI'];

// Lista de rutas donde NO se debe guardar prev_url
$noGuardar = [
    'Ticket/Ver',
    'Ticket/Editar',
    'Ticket/Actualizar',
    'Device/ActualizarDevice',
    'Device/CrearDevice',
    'Inventario/CrearItem',
    'Inventario/ActualizarItem',
    'Inventario/CrearCategoria',
    'Inventario/MostrarCrearCategoria',
    'Inventario/MostrarCrearItem',
    'EstadoTicket/Actualizar',
    'EstadoTicket/CrearEstado',
    'Register/RegisterAdmin',
    'Admin/Register',
    // Evitar que el polling y acciones AJAX sobreescriban prev_url
    'Notification/Count',
    'Notification/MarkRead'
];

$guardarPrevUrl = true;
if (isset($_GET['route'])) {
    foreach ($noGuardar as $rutaDetalle) {
        if (strpos($_GET['route'], $rutaDetalle) !== false) {
            $guardarPrevUrl = false;
            break;
        }
    }
}

// No guardar prev_url si la petición es JSON/AJAX
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$isJsonAccept = stripos($accept, 'application/json') !== false;
if ($guardarPrevUrl && !$isJsonAccept) {
    $_SESSION['prev_url'] = $currentUrl;
}

// Captura global de errores para 500 y manejo 404
set_exception_handler(function($e){
    http_response_code(500);
    $GLOBALS['__last_exception'] = $e;
    include __DIR__ . '/../Views/Errors/500.php';
    Logger::channel('error')->error('Excepción no capturada', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
});
set_error_handler(function($severity, $message, $file, $line){
    http_response_code(500);
    include __DIR__ . '/../Views/Errors/500.php';
    Logger::channel('php')->error('Error PHP', [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ]);
    return true; // manejado
});

$routes = require __DIR__ . '/../Routes/web.php';
$route = $_GET['route'] ?? 'Default/Index';
if (isset($routes[$route])) {
    $controllerName = $routes[$route]['controller'];
    $action = $routes[$route]['action'];

    $controllerFile = __DIR__ . "../../Controllers/{$controllerName}Controller.php";

    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        $className = $controllerName . 'Controller';

        if (class_exists($className)) {
            $controller = new $className();

            // Validación CSRF global antes de ejecutar cualquier método POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                Csrf::validateOrThrow();
            }

            if (method_exists($controller, $action)) {
                // Si la acción espera un parámetro 'id' en la URL
                // Ejecutar acción
                if (isset($_GET['id'])) {
                    $controller->$action($_GET['id']);
                } else {
                    $controller->$action();
                }
                // Los mensajes flash se mostrarán desde Includes/FlashMessages.php cuando corresponda
            } else {
                http_response_code(404);
                include __DIR__ . '/../Views/Errors/404.php';
            }
        } else {
            http_response_code(404);
            include __DIR__ . '/../Views/Errors/404.php';
        }
    } else {
        http_response_code(404);
        include __DIR__ . '/../Views/Errors/404.php';
    }
} else {
    http_response_code(404);
    include __DIR__ . '/../Views/Errors/404.php';
}
