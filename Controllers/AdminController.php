<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Date.php';
require_once __DIR__ . '/../Controllers/HistorialController.php';
require_once __DIR__ . '/../Core/Flash.php';
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
        
        foreach ($users as &$u) {
            if (!empty($u['created_at'])) {
                $u['created_exact'] = DateHelper::exact($u['created_at']);
                $u['created_human'] = DateHelper::smart($u['created_at']);
            }
        }
        unset($u);
        
        $flash = null;
        if (isset($_GET['success'])) {
            $flash = ['type' => 'success', 'message' => 'Operación realizada correctamente.'];
        } elseif (isset($_GET['error'])) {
            $flash = ['type' => 'error', 'message' => 'No se pudo realizar la operación.'];
        }
        include_once __DIR__ . '/../Views/Admin/ListaUser.php';
    }

    public function listarCli()
    {
        $clientes = $this->userModel->getAllClientes();
        foreach ($clientes as &$c) {
            $created = $c['created_at'] ?? '';
            if ($created) {
                $c['created_exact'] = DateHelper::exact($created);
                $c['created_human'] = DateHelper::smart($created);
            }
        }
        unset($c);
        $flash = null;
        if (isset($_GET['success'])) {
            $flash = ['type' => 'success', 'message' => 'Operación realizada correctamente.'];
        } elseif (isset($_GET['error'])) {
            $flash = ['type' => 'error', 'message' => 'No se pudo realizar la operación.'];
        }
        include_once __DIR__ . '/../Views/Admin/ListaCliente.php';
    }

    public function listarTecs()
    {
        $tecnicos = $this->userModel->getAllTecnicos();
        
        require_once __DIR__ . '/../Models/Rating.php';
        $dbx = new Database();
        $dbx->connectDatabase();
        $ratingModel = new RatingModel($dbx->getConnection());
        foreach ($tecnicos as &$tec) {
            $tecId = (int)($tec['id'] ?? 0);
            list($avg, $count) = $ratingModel->getAvgForTecnico($tecId);
            $tec['rating_avg'] = $avg ? (float)$avg : 0.0;
            $tec['rating_count'] = (int)$count;
            
            $created = $tec['created_at'] ?? '';
            if ($created) {
                $tec['created_exact'] = DateHelper::exact($created);
                $tec['created_human'] = DateHelper::smart($created);
            }
        }
        unset($tec);
        $flash = null;
        if (isset($_GET['success'])) {
            $flash = ['type' => 'success', 'message' => 'Operación realizada correctamente.'];
        } elseif (isset($_GET['error'])) {
            $flash = ['type' => 'error', 'message' => 'No se pudo realizar la operación.'];
        }
        include_once __DIR__ . '/../Views/Admin/ListaTecnico.php';
    }

    public function listarSupers()
    {
        $supervisor = $this->userModel->getAllSupervisores();
        foreach ($supervisor as &$s) {
            $created = $s['created_at'] ?? '';
            if ($created) {
                $s['created_exact'] = DateHelper::exact($created);
                $s['created_human'] = DateHelper::smart($created);
            }
        }
        unset($s);
        $flash = null;
        if (isset($_GET['success'])) {
            $flash = ['type' => 'success', 'message' => 'Operación realizada correctamente.'];
        } elseif (isset($_GET['error'])) {
            $flash = ['type' => 'error', 'message' => 'No se pudo realizar la operación.'];
        }
        include_once __DIR__ . '/../Views/Admin/ListaSupervisor.php';
    }

    public function listarAdmins()
    {
        $administradores = $this->userModel->getAllAdministradores();
        foreach ($administradores as &$a) {
            $created = $a['created_at'] ?? '';
            if ($created) {
                $a['created_exact'] = DateHelper::exact($created);
                $a['created_human'] = DateHelper::smart($created);
            }
        }
        unset($a);
        $flash = null;
        if (isset($_GET['success'])) {
            $flash = ['type' => 'success', 'message' => 'Operación realizada correctamente.'];
        } elseif (isset($_GET['error'])) {
            $flash = ['type' => 'error', 'message' => 'No se pudo realizar la operación.'];
        }
        include_once __DIR__ . '/../Views/Admin/ListaAdmin.php';
    }

    public function ActualizarUser()
    {
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());

        
        $userId = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = isset($_POST['id']) ? (int)$_POST['id'] : null;
        } else {
            $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        }

        if (!$userId) {
            
            header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
            exit;
        }

    $user = $userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim((string)($_POST['name'] ?? ''));
            $role = trim((string)($_POST['role'] ?? ''));
            $from = $_POST['from'] ?? 'Admin/ListarUsers';

            
            if ($name === '') {
                Flash::error('El nombre es obligatorio.');
                header('Location: /ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id='.(int)$userId.'&from='.urlencode($from));
                exit;
            }
            $rolesValidos = ['Cliente','Tecnico','Supervisor','Administrador'];
            if ($role === '' || !in_array($role, $rolesValidos, true)) {
                Flash::error('Seleccioná un rol válido.');
                header('Location: /ProyectoPandora/Public/index.php?route=Admin/ActualizarUser&id='.(int)$userId.'&from='.urlencode($from));
                exit;
            }

            
            $current = $userModel->findById($userId);
            $email = $current['email'] ?? ($user['email'] ?? '');

            
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
            
            $detalle = "{$admin['name']} editó a {$oldName} (ID {$userId}, email {$oldEmail})";
            if (!empty($cambios)) { $detalle .= ". Cambios: " . implode(', ', $cambios) . "."; }
            $this->historialController->agregarAccion($accion, $detalle);

            
            $currentAdmin = Auth::user();
            if ($currentAdmin && (int)$currentAdmin['id'] === (int)$userId && ($role !== ($currentAdmin['role'] ?? ''))) {
                
                session_unset();
                session_destroy();
                header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login&info=Reinicio%20de%20sesion%20por%20cambio%20de%20rol');
                exit;
            }

            require_once __DIR__ . '/../Core/Flash.php';
            Flash::successQuiet('Usuario actualizado.');
            header('Location: /ProyectoPandora/Public/index.php?route=' . $from);
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

    require_once __DIR__ . '/../Core/Flash.php';
    Flash::successQuiet('Usuario creado.');
    header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
        exit;
    }

    






    public function MigrarTicketImages()
    {
        require_once __DIR__ . '/../Core/Storage.php';
        $isMove = (isset($_GET['mode']) && strtolower((string)$_GET['mode']) === 'move');
        $onlyId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        $legacyBase = __DIR__ . '/../Public/img/imgTickets/';
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $result = [
            'processed' => 0,
            'copied' => 0,
            'skipped' => 0,
            'deleted' => 0,
            'errors' => 0,
            'tickets' => []
        ];

        if (!is_dir($legacyBase)) {
            echo '<main><div class="Contenedor"><h2>Migración de fotos de tickets</h2><p>No existe carpeta legacy: '.htmlspecialchars($legacyBase).'</p></div></main>';
            return;
        }

        $dirs = @scandir($legacyBase) ?: [];
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            if (!ctype_digit($dir)) continue; 
            $ticketId = (int)$dir;
            if ($onlyId && $ticketId !== (int)$onlyId) continue;
            $srcDir = rtrim($legacyBase, '/\\') . '/' . $dir . '/';
            if (!is_dir($srcDir)) continue;

            $destDir = \Storage::ensure('ticket/' . $ticketId);
            $files = @scandir($srcDir) ?: [];
            $movedForTicket = 0; $skippedForTicket = 0; $deletedForTicket = 0; $errorsForTicket = 0;
            foreach ($files as $fn) {
                if ($fn === '.' || $fn === '..') continue;
                $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) continue;
                $src = $srcDir . $fn;
                $dst = rtrim($destDir, '/\\') . '/' . $fn;
                
                if (is_file($dst)) { $skippedForTicket++; $result['skipped']++; continue; }
                if (@copy($src, $dst)) {
                    $movedForTicket++; $result['copied']++;
                    if ($isMove) {
                        if (@unlink($src)) { $deletedForTicket++; $result['deleted']++; }
                    }
                } else {
                    $errorsForTicket++; $result['errors']++;
                }
                $result['processed']++;
            }
            $result['tickets'][] = [
                'id' => $ticketId,
                'copied' => $movedForTicket,
                'skipped' => $skippedForTicket,
                'deleted' => $deletedForTicket,
                'errors' => $errorsForTicket,
                'dest' => \Storage::publicUrl('ticket/' . $ticketId)
            ];
        }

        
        echo '<main><div class="Contenedor">';
        echo '<h2>Migración de fotos de tickets</h2>';
        echo '<p>Modo: '.($isMove ? 'mover' : 'copiar').'</p>';
        if ($onlyId) { echo '<p>Solo ticket ID: '.(int)$onlyId.'</p>'; }
        echo '<ul>';
        echo '<li>Archivos procesados: '.(int)$result['processed'].'</li>';
        echo '<li>Copiados: '.(int)$result['copied'].'</li>';
        echo '<li>Omitidos (ya existían): '.(int)$result['skipped'].'</li>';
        echo '<li>Eliminados del origen: '.(int)$result['deleted'].'</li>';
        echo '<li>Errores: '.(int)$result['errors'].'</li>';
        echo '</ul>';
        echo '<h3>Detalle por ticket</h3>';
        echo '<table><thead><tr><th>ID</th><th>Copiados</th><th>Omitidos</th><th>Eliminados</th><th>Errores</th></tr></thead><tbody>';
        foreach ($result['tickets'] as $t) {
            echo '<tr>';
            echo '<td>'.(int)$t['id'].'</td>';
            echo '<td>'.(int)$t['copied'].'</td>';
            echo '<td>'.(int)$t['skipped'].'</td>';
            echo '<td>'.(int)$t['deleted'].'</td>';
            echo '<td>'.(int)$t['errors'].'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p><a class="btn btn-outline" href="/ProyectoPandora/Public/index.php?route=Admin/PanelAdmin">Volver al panel</a></p>';
        echo '</div></main>';
    }
}
