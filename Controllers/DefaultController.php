<?php
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Storage.php';

class DefaultController
{

    public function index()
    {
        $user = Auth::user();
        $stats = $this->computeHomeStats($user);
        include_once __DIR__ . '/../Views/AllUsers/Home.php';
    }

    public function HomeMetrics()
    {
        $user = Auth::user();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new \Database();
            $db->connectDatabase();
            $conn = $db->getConnection();

            $scope = $this->buildTicketScope($user);

            @require_once __DIR__ . '/../Models/Rating.php';
            if (class_exists('RatingModel')) {
                new \RatingModel($conn); // asegura ticket_ratings
            }

            $counts = ['activos' => 0, 'finalizados' => 0, 'cancelados' => 0];
            $sqlTickets = <<<SQL
                SELECT
                    SUM(CASE WHEN t.fecha_cierre IS NULL THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN t.fecha_cierre IS NOT NULL THEN 1 ELSE 0 END) AS finalizados,
                    SUM(CASE WHEN est.name = 'Cancelado' THEN 1 ELSE 0 END) AS cancelados
                FROM tickets t
                INNER JOIN estados_tickets est ON est.id = t.estado_id
            SQL;
            $sqlTickets .= $scope['joins'];
            $sqlTickets .= ' WHERE 1=1' . $scope['where'];

            if ($scope['param'] !== null && ($stmt = $conn->prepare($sqlTickets))) {
                $param = (int)$scope['param'];
                $stmt->bind_param('i', $param);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    if ($res) {
                        $row = $res->fetch_assoc();
                        if ($row) {
                            $counts = array_merge($counts, array_filter($row, function ($v) { return $v !== null; }));
                        }
                    }
                }
                $stmt->close();
            } elseif ($scope['param'] === null && ($res = $conn->query($sqlTickets))) {
                $row = $res->fetch_assoc();
                if ($row) {
                    $counts = array_merge($counts, array_filter($row, function ($v) { return $v !== null; }));
                }
            }

            $categories = ['labels' => [], 'data' => []];
            $sqlCat = <<<SQL
                SELECT cat.name AS categoria, COUNT(*) AS total
                FROM tickets t
                INNER JOIN dispositivos d ON d.id = t.dispositivo_id
                INNER JOIN categorias cat ON cat.id = d.categoria_id
            SQL;
            $sqlCat .= $scope['joins'];
            $sqlCat .= ' WHERE 1=1' . $scope['where'];
            $sqlCat .= ' GROUP BY cat.name ORDER BY total DESC LIMIT 6';

            if ($scope['param'] !== null && ($stmt = $conn->prepare($sqlCat))) {
                $param = (int)$scope['param'];
                $stmt->bind_param('i', $param);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    while ($res && ($row = $res->fetch_assoc())) {
                        $categories['labels'][] = $row['categoria'];
                        $categories['data'][] = (int)$row['total'];
                    }
                }
                $stmt->close();
            } elseif ($scope['param'] === null && ($res = $conn->query($sqlCat))) {
                while ($row = $res->fetch_assoc()) {
                    $categories['labels'][] = $row['categoria'];
                    $categories['data'][] = (int)$row['total'];
                }
            }

            $ranking = ['labels' => [1, 2, 3, 4, 5], 'data' => [0, 0, 0, 0, 0]];
            $sqlRanking = <<<SQL
                SELECT bucket, COUNT(*) AS total
                FROM (
                    SELECT 
                        CASE
                            WHEN ROUND(AVG(r.stars)) < 1 THEN 1
                            WHEN ROUND(AVG(r.stars)) > 5 THEN 5
                            ELSE ROUND(AVG(r.stars))
                        END AS bucket
                    FROM ticket_ratings r
                    INNER JOIN tickets t ON t.id = r.ticket_id
                    INNER JOIN tecnicos tc ON tc.id = r.tecnico_id
            SQL;
            $sqlRanking .= $scope['joins'];
            $sqlRanking .= ' WHERE 1=1' . $scope['where'];
            $sqlRanking .= ' GROUP BY tc.id
                ) buckets
                WHERE bucket IS NOT NULL
                GROUP BY bucket
                ORDER BY bucket ASC';

            if ($scope['param'] !== null && ($stmt = $conn->prepare($sqlRanking))) {
                $param = (int)$scope['param'];
                $stmt->bind_param('i', $param);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    while ($res && ($row = $res->fetch_assoc())) {
                        $bucket = (int)($row['bucket'] ?? 0);
                        if ($bucket >= 1 && $bucket <= 5) {
                            $ranking['data'][$bucket - 1] = (int)$row['total'];
                        }
                    }
                }
                $stmt->close();
            } elseif ($scope['param'] === null && ($res = $conn->query($sqlRanking))) {
                while ($row = $res->fetch_assoc()) {
                    $bucket = (int)($row['bucket'] ?? 0);
                    if ($bucket >= 1 && $bucket <= 5) {
                        $ranking['data'][$bucket - 1] = (int)$row['total'];
                    }
                }
            }

            $stats = $this->computeHomeStats($user, $conn);

            echo json_encode([
                'generated_at' => gmdate('c'),
                'role' => $user['role'] ?? 'Invitado',
                'stats' => $stats,
                'charts' => [
                    'tickets' => [
                        'labels' => ['Activos', 'Finalizados', 'Cancelados'],
                        'data' => [
                            (int)($counts['activos'] ?? 0),
                            (int)($counts['finalizados'] ?? 0),
                            (int)($counts['cancelados'] ?? 0),
                        ],
                    ],
                    'ranking' => $ranking,
                    'categories' => $categories,
                ],
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'No se pudieron obtener las métricas',
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function buildTicketScope(?array $user): array
    {
        $role = $user['role'] ?? '';
        $userId = isset($user['id']) ? (int)$user['id'] : null;

        if (!$userId) {
            return ['joins' => '', 'where' => '', 'param' => null];
        }

        switch ($role) {
            case 'Cliente':
                return [
                    'joins' => ' INNER JOIN clientes scope_cli ON t.cliente_id = scope_cli.id',
                    'where' => ' AND scope_cli.user_id = ?',
                    'param' => $userId,
                ];
            case 'Tecnico':
                return [
                    'joins' => ' INNER JOIN tecnicos scope_tc ON t.tecnico_id = scope_tc.id',
                    'where' => ' AND scope_tc.user_id = ?',
                    'param' => $userId,
                ];
            case 'Supervisor':
                return [
                    'joins' => ' INNER JOIN supervisores scope_sup ON t.supervisor_id = scope_sup.id',
                    'where' => ' AND scope_sup.user_id = ?',
                    'param' => $userId,
                ];
            default:
                return ['joins' => '', 'where' => '', 'param' => null];
        }
    }

    private function computeHomeStats(?array $user, ?\mysqli $connOverride = null): array
    {
        $stats = [
            'activeTickets'    => 0,
            'avgRating'        => null,
            'lastUpdateIso'    => null,
            'lastUpdateHuman'  => '—',
        ];
        try {
            $conn = $connOverride;
            if (!$connOverride) {
                $db = new \Database();
                $db->connectDatabase();
                $conn = $db->getConnection();
            }
            if (!$conn) {
                return $stats;
            }

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
        $userImg = \Storage::resolveProfileUrl($user['img_perfil'] ?? '');
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
            $imgPerfil = $user['img_perfil'] ?? '';
            $didUpload = false;

            // Aceptar tanto 'img_perfil' como 'avatar' desde el formulario
            $fileKey = null;
            if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {
                $fileKey = 'img_perfil';
            } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileKey = 'avatar';
            }
            if ($fileKey !== null) {
                $stored = \Storage::storeUploadedFile($_FILES[$fileKey], 'profile');
                if ($stored) {
                    $imgPerfil = $stored['relative'];
                    $didUpload = true;
                }
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
