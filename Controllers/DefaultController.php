<?php
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Core/Database.php';

class DefaultController
{

    public function index()
    {
        // Auto-refresh de la página cada 30s sin usar JSON/AJAX
        header('Refresh: 30');
        $user = Auth::user();
        $stats = $this->computeHomeStats($user);
        include_once __DIR__ . '/../Views/AllUsers/Home.php';
    }

    // Endpoint JSON removido por requerimiento: solo PHP renderizado en servidor

    private function computeHomeStats(?array $user): array
    {
        $stats = [
            'activeTickets'    => 0,
            'avgRating'        => null,
            'lastUpdateIso'    => null,
            'lastUpdateHuman'  => '—',
        ];
        try {
            $db = new \Database();
            $db->connectDatabase();
            $conn = $db->getConnection();

            $role = $user['role'] ?? 'Invitado';
            $userId = isset($user['id']) ? (int)$user['id'] : null;

            // Reutilizar modelo Ticket para obtener listados y contar activos por estado
            $ticketModel = new \Ticket($conn);
            $inactivos = ['finalizado','cerrado','cancelado'];
            $activos = 0;
            if ($role === 'Cliente' && $userId) {
                $lista = $ticketModel->getTicketsByUserId($userId) ?: [];
                foreach ($lista as $row) {
                    $est = strtolower(trim($row['estado'] ?? ''));
                    if (!in_array($est, $inactivos, true)) $activos++;
                }
            } elseif ($role === 'Tecnico' && $userId) {
                $lista = $ticketModel->getTicketsByTecnicoId($userId) ?: [];
                foreach ($lista as $row) {
                    $est = strtolower(trim($row['estado'] ?? ''));
                    if (!in_array($est, $inactivos, true)) $activos++;
                }
            } else {
                $lista = $ticketModel->getAllTickets() ?: [];
                foreach ($lista as $row) {
                    $est = strtolower(trim($row['estado'] ?? ''));
                    if (!in_array($est, $inactivos, true)) $activos++;
                }
            }
            $stats['activeTickets'] = $activos;

            // Promedio de calificaciones
            @require_once __DIR__ . '/../Models/Rating.php';
            if (class_exists('RatingModel')) {
                new \RatingModel($conn); // ensureTable
            }
            if ($q = $conn->query("SELECT ROUND(AVG(stars), 1) AS avg_s FROM ticket_ratings")) {
                $row = $q->fetch_assoc();
                $stats['avgRating'] = $row && $row['avg_s'] !== null ? (float)$row['avg_s'] : null;
            }

            // Última actualización
            $lastIso = null;
            if ($role === 'Cliente' && $userId) {
                $sqlH = "SELECT MAX(h.created_at) AS last
                         FROM ticket_estado_historial h
                         INNER JOIN tickets t ON h.ticket_id = t.id
                         INNER JOIN clientes c ON t.cliente_id = c.id
                         WHERE c.user_id = ?";
                if ($st = $conn->prepare($sqlH)) {
                    $st->bind_param('i', $userId);
                    $st->execute();
                    $lastIso = $st->get_result()->fetch_assoc()['last'] ?? null;
                }
                if (!$lastIso) {
                    $sqlF = "SELECT MAX(t.fecha_creacion) AS last
                             FROM tickets t
                             INNER JOIN clientes c ON t.cliente_id = c.id
                             WHERE c.user_id = ?";
                    if ($st2 = $conn->prepare($sqlF)) {
                        $st2->bind_param('i', $userId);
                        $st2->execute();
                        $lastIso = $st2->get_result()->fetch_assoc()['last'] ?? null;
                    }
                }
            } elseif ($role === 'Tecnico' && $userId) {
                $sqlH = "SELECT MAX(h.created_at) AS last
                         FROM ticket_estado_historial h
                         INNER JOIN tickets t ON h.ticket_id = t.id
                         INNER JOIN tecnicos tc ON t.tecnico_id = tc.id
                         WHERE tc.user_id = ?";
                if ($st = $conn->prepare($sqlH)) {
                    $st->bind_param('i', $userId);
                    $st->execute();
                    $lastIso = $st->get_result()->fetch_assoc()['last'] ?? null;
                }
                if (!$lastIso) {
                    $sqlF = "SELECT MAX(t.fecha_creacion) AS last
                             FROM tickets t
                             INNER JOIN tecnicos tc ON t.tecnico_id = tc.id
                             WHERE tc.user_id = ?";
                    if ($st2 = $conn->prepare($sqlF)) {
                        $st2->bind_param('i', $userId);
                        $st2->execute();
                        $lastIso = $st2->get_result()->fetch_assoc()['last'] ?? null;
                    }
                }
            } else {
                if ($q = $conn->query("SELECT MAX(created_at) AS last FROM ticket_estado_historial")) {
                    $lastIso = $q->fetch_assoc()['last'] ?? null;
                }
                if (!$lastIso) {
                    if ($q2 = $conn->query("SELECT MAX(fecha_creacion) AS last FROM tickets")) {
                        $lastIso = $q2->fetch_assoc()['last'] ?? null;
                    }
                }
            }

            $stats['lastUpdateIso'] = $lastIso;
            if ($lastIso) {
                $ts = strtotime($lastIso);
                if ($ts !== false) {
                    $diff = time() - $ts;
                    if ($diff < 60) {
                        $stats['lastUpdateHuman'] = 'hace ' . $diff . 's';
                    } elseif ($diff < 3600) {
                        $stats['lastUpdateHuman'] = 'hace ' . floor($diff / 60) . 'm';
                    } elseif ($diff < 86400) {
                        $stats['lastUpdateHuman'] = 'hace ' . floor($diff / 3600) . 'h';
                        } else {
                        $stats['lastUpdateHuman'] = date('d/m/Y H:i', $ts);
                    }
                }
            }
    } catch (\Throwable $e) {
            // error_log('[Home stats] ' . $e->getMessage());
        }
        return $stats;
    }
    public function index2() {
        $user = Auth::user();
        include_once __DIR__ . '/../Views/AllUsers/Guia.php';
    }  
    public function perfil()
    {
        $user = $_SESSION['user'] ?? [];
        $userName = $user['name'] ?? 'Usuario';
        $userEmail = $user['email'] ?? '';
        $userImg = $user['img_perfil'] ?? '/ProyectoPandora/Public/img/imgPerfil/default.png';
        $rol = $user['role'] ?? '';
        $userId = $user['id'] ?? null;

        $db = new \Database();
        $db->connectDatabase();
        $ticketModel = new \Ticket($db->getConnection());
        $deviceModel = new \DeviceModel($db->getConnection());

        $cantTickets = 0;
        $cantDevices = 0;
        $tecnicoDisponibilidad = null;
        $tecnicoEspecialidad = '';

        if ($rol === 'Cliente' && $userId) {
            $tickets = $ticketModel->getTicketsByUserId($userId);
            $cantTickets = is_array($tickets) ? count($tickets) : 0;
            $devices = $deviceModel->getDevicesByUserId($userId);
            $cantDevices = is_array($devices) ? count($devices) : 0;
        } elseif ($rol === 'Tecnico' && $userId) {
            $tickets = $ticketModel->getTicketsByTecnicoId($userId);
            $cantTickets = is_array($tickets) ? count($tickets) : 0;
            $stmtTec = $db->getConnection()->prepare("SELECT disponibilidad, especialidad FROM tecnicos WHERE user_id = ? LIMIT 1");
            if ($stmtTec) {
                $stmtTec->bind_param("i", $userId);
                $stmtTec->execute();
                $rowTec = $stmtTec->get_result()->fetch_assoc();
                $tecnicoDisponibilidad = $rowTec['disponibilidad'] ?? null;
                $tecnicoEspecialidad = $rowTec['especialidad'] ?? '';
            }
        } else {
            $tickets = $ticketModel->getAllTickets();
            $cantTickets = is_array($tickets) ? count($tickets) : 0;
            $devices = $deviceModel->getAllDevices();
            $cantDevices = is_array($devices) ? count($devices) : 0;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $user['id'] ?? null;
            // Parche de seguridad: asegura que el trigger de actualización de usuarios
            // NO borre relaciones si no cambia el rol (evita cascadas al cambiar contraseña)
            try {
                $connChk = (new \Database()); $connChk->connectDatabase(); $cx = $connChk->getConnection();
                // Re-crear el trigger con guarda (idempotente)
                @$cx->query("DROP TRIGGER IF EXISTS trigger_actualizar_usuario_por_rol");
                $sqlTrig = "CREATE TRIGGER trigger_actualizar_usuario_por_rol\n"
                    . "AFTER UPDATE ON users\n"
                    . "FOR EACH ROW\n"
                    . "BEGIN\n"
                    . "    IF OLD.role <> NEW.role THEN\n"
                    . "        IF OLD.role = 'Cliente' THEN\n"
                    . "            DELETE FROM clientes WHERE user_id = OLD.id;\n"
                    . "        ELSEIF OLD.role = 'Tecnico' THEN\n"
                    . "            DELETE FROM tecnicos WHERE user_id = OLD.id;\n"
                    . "        ELSEIF OLD.role = 'Supervisor' THEN\n"
                    . "            DELETE FROM supervisores WHERE user_id = OLD.id;\n"
                    . "        ELSEIF OLD.role = 'Administrador' THEN\n"
                    . "            DELETE FROM administradores WHERE user_id = OLD.id;\n"
                    . "        END IF;\n"
                    . "        IF NEW.role = 'Cliente' THEN\n"
                    . "            INSERT INTO clientes(user_id) VALUES (NEW.id);\n"
                    . "        ELSEIF NEW.role = 'Tecnico' THEN\n"
                    . "            INSERT INTO tecnicos(user_id, disponibilidad) VALUES (NEW.id, 'Disponible');\n"
                    . "        ELSEIF NEW.role = 'Supervisor' THEN\n"
                    . "            INSERT INTO supervisores(user_id) VALUES (NEW.id);\n"
                    . "        ELSEIF NEW.role = 'Administrador' THEN\n"
                    . "            INSERT INTO administradores(user_id) VALUES (NEW.id);\n"
                    . "        END IF;\n"
                    . "    END IF;\n"
                    . "END";
                @$cx->query($sqlTrig);
            } catch (\Throwable $e) { /* noop */ }
            $newName = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
            $newEmail = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
            $imgPerfil = $user['img_perfil'] ?? '/ProyectoPandora/Public/img/imgPerfil/default.png';
            $didUpload = false;

            // Aceptar tanto 'img_perfil' como 'avatar' desde el formulario
            $fileKey = null;
            if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {
                $fileKey = 'img_perfil';
            } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileKey = 'avatar';
            }
            if ($fileKey !== null) {
                $imgTmp = $_FILES[$fileKey]['tmp_name'];
                $origName = $_FILES[$fileKey]['name'] ?? 'avatar.png';
                $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $origName);
                $imgName = uniqid('perfil_') . '_' . $safeName;
                $webDir = '/ProyectoPandora/Public/img/imgPerfil';
                $fsDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $webDir;
                if (!is_dir($fsDir)) { @mkdir($fsDir, 0775, true); }
                $imgPath = $webDir . '/' . $imgName;
                $destFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $imgPath;
                if (@move_uploaded_file($imgTmp, $destFs)) { $imgPerfil = $imgPath; $didUpload = true; }
            }

            $userModel = new \UserModel($db->getConnection());
            // Actualizar perfil solo si se enviaron datos de perfil (evita pisar con vacío)
            $shouldUpdateProfile = ($newName !== '' || $newEmail !== '' || $didUpload);
            if ($shouldUpdateProfile) {
                // Completar con valores actuales si alguno no viene
                $current = $userModel->findById((int)$userId);
                $finalName = ($newName !== '') ? $newName : ($current['name'] ?? $userName);
                $finalEmail = ($newEmail !== '') ? $newEmail : ($current['email'] ?? $userEmail);
                $userModel->actualizarPerfil($userId, $finalName, $finalEmail, $imgPerfil);
                if ($newName !== '') $_SESSION['user']['name'] = $finalName;
                if ($newEmail !== '') $_SESSION['user']['email'] = $finalEmail;
                $_SESSION['user']['img_perfil'] = $imgPerfil;
            }

            // Actualizar contraseña si corresponde (requiere contraseña actual válida)
            $curPass = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
            $newPass = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
            $confPass = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';
            if ($curPass !== '' || $newPass !== '' || $confPass !== '') {
                // Debe proveer los tres campos
                if ($curPass === '' || $newPass === '' || $confPass === '') {
                    header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil&error=pass');
                    exit;
                }
                if ($newPass !== $confPass || strlen($newPass) < 8) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil&error=pass');
                    exit;
                }
                // Traer hash actual y verificar current_password
                $stGet = $db->getConnection()->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                if ($stGet) {
                    $stGet->bind_param('i', $userId);
                    $stGet->execute();
                    $rowPw = $stGet->get_result()->fetch_assoc();
                    $hashOld = $rowPw['password'] ?? '';
                    if (!$hashOld || !password_verify($curPass, $hashOld)) {
                        header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil&error=wrongpass');
                        exit;
                    }
                    // Evitar cambiar a la misma contraseña
                    if (password_verify($newPass, $hashOld)) {
                        header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil&error=passsame');
                        exit;
                    }
                }
                // Actualizar a nuevo hash
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stPw = $db->getConnection()->prepare('UPDATE users SET password = ? WHERE id = ?');
                if ($stPw) { 
                    $stPw->bind_param('si', $hash, $userId); 
                    if ($stPw->execute()) {
                        // Registrar en historial para trazabilidad
                        @require_once __DIR__ . '/HistorialController.php';
                        if (class_exists('HistorialController')) {
                            $hist = new \HistorialController();
                            $accion = 'Cambio de contraseña';
                            $detalle = 'El usuario ' . ($user['name'] ?? ('ID '.(int)$userId)) . ' actualizó su contraseña desde la sección Perfil.';
                            $hist->agregarAccion($accion, $detalle);
                        }
                    }
                }
                // Redirigir con ok=pass y cortar aquí para no confundir feedback
                header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil&ok=pass');
                exit;
            }

            if ($rol === 'Tecnico') {
                // Ajustes: disponibilidad
                if (isset($_POST['disponibilidad'])) {
                    $nuevaDisp = $_POST['disponibilidad'];
                    if ($nuevaDisp === 'Disponible' || $nuevaDisp === 'Ocupado') {
                        $stmtUpd = $db->getConnection()->prepare("UPDATE tecnicos SET disponibilidad = ? WHERE user_id = ?");
                        if ($stmtUpd) { $stmtUpd->bind_param("si", $nuevaDisp, $userId); $stmtUpd->execute(); $tecnicoDisponibilidad = $nuevaDisp; }
                    }
                }
                // Perfil: especialidad
                if (isset($_POST['especialidad'])) {
                    $nuevaEsp = trim((string)$_POST['especialidad']);
                    $stmtEsp = $db->getConnection()->prepare("UPDATE tecnicos SET especialidad = ? WHERE user_id = ?");
                    if ($stmtEsp) { $stmtEsp->bind_param("si", $nuevaEsp, $userId); $stmtEsp->execute(); $tecnicoEspecialidad = $nuevaEsp; }
                }
            }

            // Redirigir con estado (solo perfil aquí; contraseña ya redirigió arriba)
            $suffix = ($shouldUpdateProfile ? '&ok=perfil' : '');
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Perfil' . $suffix);
            exit;
        }

        include_once __DIR__ . '/../Views/AllUsers/Perfil.php';
    }
}
