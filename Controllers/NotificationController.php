<?php
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/HistorialController.php';

class NotificationController
{
    public function Index()
    {
        Auth::check();
        $user = Auth::user();
        $db = new Database(); $db->connectDatabase();
        $model = new NotificationModel($db->getConnection());

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 20; $off = ($page-1)*$per;
        $list = $model->listForUser((int)$user['id'], (string)$user['role'], $per, $off);

        include_once __DIR__ . '/../Views/Notifications/List.php';
    }

    
    public function Count()
    {
        Auth::check();
        $user = Auth::user();
        $db = new Database(); $db->connectDatabase();
        $model = new NotificationModel($db->getConnection());
        $count = $model->countUnread((int)$user['id'], (string)$user['role']);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo (int)$count;
        exit;
    }

    public function MarkRead()
    {
        Auth::check();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=Notification/Index'); exit;
        }
        $user = Auth::user();
        $id = (int)($_POST['id'] ?? 0);
        $db = new Database(); $db->connectDatabase();
        $model = new NotificationModel($db->getConnection());
        if ($id) { $model->markRead((int)$user['id'], $id); }
        header('Location: index.php?route=Notification/Index');
        exit;
    }

    public function Create()
    {
        Auth::checkRole(['Administrador','Supervisor']);
        $user = Auth::user();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf'] ?? '';
            if (!isset($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], (string)$token)) {
                header('Location: index.php?route=Notification/Create&error=csrf');
                exit;
            }
            $title = trim((string)($_POST['title'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            $aud = $_POST['audience'] ?? 'ALL';
            $role = $_POST['audience_role'] ?? null;
            $target = isset($_POST['target_user_id']) && $_POST['target_user_id'] !== '' ? (int)$_POST['target_user_id'] : null;
            $db = new Database(); $db->connectDatabase();
            $model = new NotificationModel($db->getConnection());
            
            $senderRole = (string)($user['role'] ?? '');
            $isAdmin = ($senderRole === 'Administrador');
            $isSupervisor = ($senderRole === 'Supervisor');
            if ($isSupervisor) {
                
                if ($aud === 'ALL') {
                    header('Location: index.php?route=Notification/Create&error=aud');
                    exit;
                }
                if ($aud === 'ROLE') {
                    if (!in_array((string)$role, ['Cliente','Tecnico'], true)) {
                        header('Location: index.php?route=Notification/Create&error=role');
                        exit;
                    }
                } elseif ($aud === 'USER') {
                    if (!$target || $target <= 0) {
                        header('Location: index.php?route=Notification/Create&error=target');
                        exit;
                    }
                    $um = new UserModel($db->getConnection());
                    $tu = $um->findById($target);
                    $tRole = $tu['role'] ?? '';
                    if (!in_array($tRole, ['Cliente','Tecnico'], true)) {
                        header('Location: index.php?route=Notification/Create&error=target_role');
                        exit;
                    }
                }
            }
            if ($title === '' || $body === '') {
                header('Location: index.php?route=Notification/Create&error=required');
                exit;
            }
            $notifId = $model->create($title, $body, $aud, $role, $target, (int)$user['id']);
            
            try {
                $hist = new HistorialController();
                $audTxt = 'Todos';
                if ($aud === 'ROLE') { $audTxt = 'Rol: '.($role ?? ''); }
                if ($aud === 'USER') { $audTxt = 'Usuario ID: '.(int)$target; }
                $hist->agregarAccion('Notificación creada', ($user['name'] ?? 'Usuario').' creó una notificación ('.$audTxt.") titulada '".$title."'.");
            } catch (\Throwable $e) {  }
            header('Location: index.php?route=Notification/Index');
            exit;
        }
        
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        $senderRole = (string)($user['role'] ?? '');
        $canBroadcastAll = ($senderRole === 'Administrador');
        $allowedAudienceRoles = $canBroadcastAll
            ? ['Cliente','Tecnico','Supervisor','Administrador']
            : ['Cliente','Tecnico'];
        
        $db2 = new Database(); $db2->connectDatabase();
        $um = new UserModel($db2->getConnection());
        $allUsers = $um->getAllUsers();
        $selectableUsers = array_values(array_filter($allUsers, function($u) use ($senderRole){
            $role = $u['role'] ?? '';
            if ($senderRole === 'Supervisor') {
                return in_array($role, ['Cliente','Tecnico'], true);
            }
            return true; 
        }));
        include_once __DIR__ . '/../Views/Notifications/Create.php';
    }
}
