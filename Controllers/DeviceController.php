<?php
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Models/DeviceCategory.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Historial.php';
require_once __DIR__ . '/../Controllers/HistorialController.php';

class DeviceController
{
    private $historialController;
    private $deviceModel;
    private $categoryModel;
    private $userModel;

    public function __construct()
    {
        $db = new Database();
        $db->connectDatabase();
        $conn = $db->getConnection();

        $this->historialController = new HistorialController();
        $this->deviceModel = new DeviceModel($conn);
    $this->categoryModel = new DeviceCategoryModel($conn);
        $this->userModel = new UserModel($conn);
    }

    public function listarDevice()
    {
        // Vista ListaDispositivos no existe; mantenemos compatibilidad redirigiendo.
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if (($user['role'] ?? '') === 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice');
        } else {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
        }
        exit;
    }

    public function listarCategoria()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        $categorias = $this->categoryModel->getAllCategories();
        include_once __DIR__ . '/../Views/Device/ListaCategoria.php';
    }

    public function mostrarCrearDispositivo()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($user['role'] === 'Administrador') {
            
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }
        $isAdmin = false;
        $clientes = [];
        $categorias = $this->categoryModel->getAllCategories();

        
        if (empty($categorias)) {
            $errorMsg = "Primero debes crear al menos una categoría antes de poder agregar un dispositivo.";
            include_once __DIR__ . '/../Views/Device/CrearDevice.php';
            return;
        }

        include_once __DIR__ . '/../Views/Device/CrearDevice.php';
    }

    public function CrearDispositivo()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($user['role'] === 'Administrador') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }
        $isAdmin = false;
        $clientes = [];
        $categorias = $this->categoryModel->getAllCategories();

        $userId = $user['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoriaId = $_POST['categoria_id'] ?? 0;
            $marca = $_POST['marca'] ?? '';
            $modelo = $_POST['modelo'] ?? '';
            $descripcion = $_POST['descripcion_falla'] ?? '';
            $img_dispositivo = $_FILES['img_dispositivo']['name'] ?? '';

            if (!$categoriaId || !$marca || !$modelo) {
                $error = "Todos los campos son obligatorios.";
                include_once __DIR__ . '/../Views/Device/CrearDevice.php';
                return;
            }

            // Manejo de imagen opcional: usar NoFoto.jpg si no se sube
            if (!empty($img_dispositivo)) {
                $rutaDestinoDir = __DIR__ . '/../Public/img/imgDispositivos/';
                if (!is_dir($rutaDestinoDir)) { @mkdir($rutaDestinoDir, 0777, true); }
                $rutaDestino = $rutaDestinoDir . basename($img_dispositivo);
                if (!move_uploaded_file($_FILES['img_dispositivo']['tmp_name'], $rutaDestino)) {
                    $error = "Error al subir la imagen.";
                    include_once __DIR__ . '/../Views/Device/CrearDevice.php';
                    return;
                }
            } else {
                $img_dispositivo = 'NoFoto.jpg';
            }

            if ($this->deviceModel->createDevice($userId, $categoriaId, $marca, $modelo, $descripcion, $img_dispositivo)) {
                $accion = "Registro de dispositivo";
                $detalle = "{$user['name']} registró su dispositivo {$marca} {$modelo}";
                $this->historialController->agregarAccion($accion, $detalle);

                
                header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&success=1');
                exit;
            } else {
                $error = "Error al registrar el dispositivo.";
                include_once __DIR__ . '/../Views/Device/CrearDevice.php';
                return;
            }
        }
        include_once __DIR__ . '/../Views/Device/CrearDevice.php';
    }

    public function CrearCategoria()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreCategoria = $_POST['nombre'] ?? '';

            if (empty($nombreCategoria)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Device/CrearCategoria&error=CamposRequeridos');
                exit;
            }

            if ($this->categoryModel->createCategory($nombreCategoria)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&success=1');
                exit;
            } else {
                header('Location: /ProyectoPandora/Public/index.php?route=Device/CrearCategoria&error=ErrorAlAgregarCategoria');
                exit;
            }
        }
        include_once __DIR__ . '/../Views/Device/CrearCategoria.php';
    }

    public function ActualizarCategoria()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        $id = (int) ($_GET['id'] ?? 0);

        $categoria = $this->categoryModel->findCategoryById($id);
        if (!$categoria) {
            echo "Categoría no encontrada.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreCategoria = $_POST['nombre'] ?? '';
            if (empty($nombreCategoria)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=CamposRequeridos');
                exit;
            }

            if ($this->categoryModel->updateCategory($id, $nombreCategoria)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&success=1');
                exit;
            }

            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=ErrorAlActualizarCategoria');
            exit;
        }
        require_once __DIR__ . '/../Views/Device/ActualizarCategoria.php';
    }

    public function ActualizarDevice()
    {
        
        Auth::checkRole(['Supervisor', 'Tecnico', 'Cliente']);
        $u = Auth::user();
        if ($u && $u['role'] === 'Administrador') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) exit("ID inválido.");

        $dispositivo = $this->deviceModel->findDeviceById((int)$id);
        if (!$dispositivo) exit("Dispositivo no encontrado.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria_id     = $_POST['categoria_id'] ?? null;
            $marca            = $_POST['marca'] ?? null;
            $modelo           = $_POST['modelo'] ?? null;
            $descripcion_falla = $_POST['descripcion_falla'] ?? null;

            if ($categoria_id && $marca && $modelo && $descripcion_falla) {
                $img_dispositivo = $dispositivo['img_dispositivo'];
                if (!empty($_FILES['img_dispositivo']['name'])) {
                    $dir = __DIR__ . "/../Public/img/imgDispositivos/";
                    if (!is_dir($dir)) mkdir($dir, 0777, true);
                    $fileName = basename($_FILES['img_dispositivo']['name']);
                    $img_dispositivo = $fileName;
                    move_uploaded_file($_FILES['img_dispositivo']['tmp_name'], $dir . $fileName);
                }
                $this->deviceModel->updateDevice($id, $categoria_id, $marca, $modelo, $descripcion_falla, $img_dispositivo);

                $admin = Auth::user();
                $this->historialController->agregarAccion(
                    "Actualización de dispositivo",
                    "{$admin['name']} actualizó el dispositivo #{$id} ({$marca} {$modelo})."
                );
                header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarDevice');
                exit;
            }
            $error = "Todos los campos son obligatorios.";
        }
        $categorias = $this->categoryModel->getAllCategories();
        include __DIR__ . '/../Views/Device/ActualizarDevice.php';
    }

    public function deleteDevice()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($user['role'] === 'Administrador') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }
        $deviceId = $_GET['id'] ?? 0;
        if (!$deviceId) {
            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarDevice&error=DeviceNotFound');
            exit;
        }
        if ($this->deviceModel->deleteDevice($deviceId)) {
            
            $accion = "Eliminación de dispositivo";
            $detalle = "{$user['name']} eliminó el dispositivo #{$deviceId}.";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarDevice&success=1');
            exit;
        }
        header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarDevice&error=ErrorDeletingDevice');
        exit;
    }

    public function deleteCategory()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        // Enforce POST for destructive action
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria');
            exit;
        }

        $categoryId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($categoryId <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=CategoryNotFound');
            exit;
        }

        // Validar existencia
        $categoria = $this->categoryModel->getCategoryById($categoryId);
        if (!$categoria) {
            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=CategoryNotFound');
            exit;
        }

        // Prevenir eliminación si hay dispositivos usando esta categoría
        $usos = method_exists($this->deviceModel, 'countDevicesByCategory')
            ? $this->deviceModel->countDevicesByCategory($categoryId)
            : 0;
        if ($usos > 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=CategoryInUse');
            exit;
        }

        if ($this->categoryModel->deleteCategory($categoryId)) {
            $accion = "Se Eliminó una Categoría";
            $detalle = "Usuario {$user['name']} eliminó la categoría con ID: $categoryId";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&success=1');
            exit;
        }
        header('Location: /ProyectoPandora/Public/index.php?route=Device/ListarCategoria&error=ErrorDeletingCategory');
        exit;
    }

    public function Eliminar()
    {
        Auth::checkRole('Cliente');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice');
            return;
        }

        $deviceId = isset($_POST['device_id']) ? (int)$_POST['device_id'] : 0;
        if ($deviceId <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&error=param');
            return;
        }
        $db = new Database();
        $db->connectDatabase();
        $conn = $db->getConnection();

        $deviceModel = new DeviceModel($conn);
        $ticketModel = new Ticket($conn);
        $hist        = new Historial($conn);

        $authUser = Auth::user();
        $userId = (int) $authUser['id'];

        $ownerId = $deviceModel->getOwnerId($deviceId);
        if ($ownerId !== $userId) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&error=forbidden');
            return;
        }

        if ($ticketModel->hasActiveTicketForDevice($deviceId)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&error=ticket_activo');
            return;
        }

        if ($deviceModel->deleteByIdAndUser($deviceId, $userId)) {
            $hist->agregarAccion('Eliminación de dispositivo', "Usuario ID {$userId} eliminó el dispositivo ID {$deviceId}");
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&deleted=1');
            return;
        }

        header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice&error=delete');
    }
}
