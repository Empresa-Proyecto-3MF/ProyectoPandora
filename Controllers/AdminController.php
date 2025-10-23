<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Controllers/HistorialController.php';
Auth::checkRole('Administrador');
class AdminController
{
    private $historialController;
    private $userModel;

    public function __construct()
    {
        $this->historialController = new HistorialController();
        $db = new Database();
        $db->connectDatabase();
        $this->userModel = new UserModel($db->getConnection());
    }
        
        public function PanelAdmin(){
        header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
        exit;
    }

    public function listarUsers()
    {
        $users = $this->userModel->getAllUsers();
        include_once __DIR__ . '/../Views/Admin/ListaUser.php';
    }

    public function listarCli()
    {
        $clientes = $this->userModel->getAllClientes();
        include_once __DIR__ . '/../Views/Admin/ListaCliente.php';
    }

    public function listarTecs()
    {
        $tecnicos = $this->userModel->getAllTecnicos();
        // Enriquecer con rating promedio y conteo
        require_once __DIR__ . '/../Models/Rating.php';
        $dbx = new Database();
        $dbx->connectDatabase();
        $ratingModel = new RatingModel($dbx->getConnection());
        foreach ($tecnicos as &$tec) {
            $tecId = (int)($tec['id'] ?? 0);
            list($avg, $count) = $ratingModel->getAvgForTecnico($tecId);
            $tec['rating_avg'] = $avg ? (float)$avg : 0.0;
            $tec['rating_count'] = (int)$count;
        }
        unset($tec);
        include_once __DIR__ . '/../Views/Admin/ListaTecnico.php';
    }

    public function listarSupers()
    {
        $supervisor = $this->userModel->getAllSupervisores();
        include_once __DIR__ . '/../Views/Admin/ListaSupervisor.php';
    }

    public function listarAdmins()
    {
        $administradores = $this->userModel->getAllAdministradores();
        include_once __DIR__ . '/../Views/Admin/ListaAdmin.php';
    }

    public function ActualizarUser()
    {
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());

        // Determina el ID desde GET (vista) o POST (submit)
        $userId = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = isset($_POST['id']) ? (int)$_POST['id'] : null;
        } else {
            $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        }

        if (!$userId) {
            // Si no hay id, volver al listado
            header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
            exit;
        }

    $user = $userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim((string)($_POST['name'] ?? ''));
            $role = trim((string)($_POST['role'] ?? ''));
            $from = $_POST['from'] ?? 'Admin/ListarUsers';

            // Validaciones básicas del servidor
            if ($name === '') {
                header('Location: /ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id='.(int)$userId.'&error=NombreRequerido&from='.urlencode($from));
                exit;
            }
            $rolesValidos = ['Cliente','Tecnico','Supervisor','Administrador'];
            if ($role === '' || !in_array($role, $rolesValidos, true)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id='.(int)$userId.'&error=RolInvalido&from='.urlencode($from));
                exit;
            }

            // Reobtén el usuario por ID para asegurar email correcto
            $current = $userModel->findById($userId);
            $email = $current['email'] ?? ($user['email'] ?? '');

            // Guardar datos previos para un log más claro
            $before = $userModel->findById($userId);
            $oldName = $before['name'] ?? '—';
            $oldEmail = $before['email'] ?? '—';
            $oldRole = $before['role'] ?? '—';

            $userModel->updateUser($userId, $name, $email, $role);

            $admin = Auth::user();
            $accion = "Actualización de usuario";
            $cambios = [];
            if ($name !== '' && $name !== $oldName) { $cambios[] = "nombre: '{$oldName}' → '{$name}'"; }
            if ($role !== '' && $role !== $oldRole) { $cambios[] = "rol: {$oldRole} → {$role}"; }
            // El email lo resolvemos siempre del registro real por seguridad
            $detalle = "{$admin['name']} editó a {$oldName} (ID {$userId}, email {$oldEmail})";
            if (!empty($cambios)) { $detalle .= ". Cambios: " . implode(', ', $cambios) . "."; }
            $this->historialController->agregarAccion($accion, $detalle);

            // Si el admin se cambia a sí mismo el rol, forzar logout para refrescar permisos
            $currentAdmin = Auth::user();
            if ($currentAdmin && (int)$currentAdmin['id'] === (int)$userId && ($role !== ($currentAdmin['role'] ?? ''))) {
                // Limpiar sesión y redirigir a login
                session_unset();
                session_destroy();
                header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login&info=Reinicio%20de%20sesion%20por%20cambio%20de%20rol');
                exit;
            }

            header("Location: index.php?route=$from");
            exit;
        }
        include_once __DIR__ . '/../Views/Admin/ActualizarUser.php';
    }


    public function DeleteUser()
    {
        $userId = $_GET['id'];
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());
        // Capturar info antes de eliminar para tener un log legible
        $victim = $userModel->findById((int)$userId);
        $userModel->deleteUser($userId);

        $admin = Auth::user();
        $accion = "Eliminación de usuario";
        if ($victim) {
            $detalle = "{$admin['name']} eliminó al usuario {$victim['name']} (ID {$userId}, email {$victim['email']}, rol {$victim['role']}).";
        } else {
            $detalle = "{$admin['name']} eliminó al usuario con ID {$userId}.";
        }
        $this->historialController->agregarAccion($accion, $detalle);

        header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
        exit;
    }
}
