<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Core/Auth.php';
Auth::user();

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

$routes = require_once __DIR__ . '../../routes/web.php';
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

            if (method_exists($controller, $action)) {
                // Si la acción espera un parámetro 'id' en la URL
                if (isset($_GET['id'])) {
                    $controller->$action($_GET['id']);
                } else {
                    $controller->$action();
                }
            } else {
                echo " Acción '$action' no encontrada.";
            }
        } else {
            echo " Clase '$className' no existe.";
        }
    } else {
        echo " Controlador '$controllerFile' no encontrado.";
    }
} else {
    echo " Ruta '$route' no registrada.";
}
