<?php
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/LogFormatter.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Inventario.php';
require_once __DIR__ . '/../Models/ItemTicket.php';
require_once __DIR__ . '/../Models/TicketLabor.php';
require_once __DIR__ . '/../Models/Rating.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Core/Flash.php';
require_once __DIR__ . '/../Core/I18n.php';

class SupervisorController {
    public function PanelSupervisor() {
        Auth::checkRole(['Supervisor']);
        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
        exit;
    }

    public function Asignar() {
        Auth::checkRole(['Supervisor']);
        I18n::boot();

        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());
        $ticketModel = new Ticket($db->getConnection());
        $ratingModel = new RatingModel($db->getConnection());

        $tecnicos = $userModel->getAllTecnicos();
        $ticketsSinTecnico = $ticketModel->getTicketsSinTecnico();


        foreach ($tecnicos as &$tec) {
            $tecId = (int)($tec['id'] ?? 0);
            list($avg, $count) = $ratingModel->getAvgForTecnico($tecId);
            $tec['rating_avg'] = $avg ? (float)$avg : 0.0;
            $tec['rating_count'] = (int)$count;
        }
        unset($tec);
        usort($tecnicos, function($a,$b){
            $ra = $a['rating_avg'] ?? 0; $rb = $b['rating_avg'] ?? 0;
            if ($rb <=> $ra) return ($rb <=> $ra); 
            
            return (int)($a['tickets_activos'] ?? 0) <=> (int)($b['tickets_activos'] ?? 0);
        });

        include_once __DIR__ . '/../Views/Supervisor/Asignar.php';
    }

    public function AsignarTecnico() {
        Auth::checkRole(['Supervisor']);
        I18n::boot();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit;
        }

        
        $user = Auth::user();
        if (!$user) { $user = $_SESSION['user'] ?? null; }
        if (!$user || empty($user['id'])) {
            Flash::error('supervisor.assign.error.session');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit; 
        }

        
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $tecnico_id = isset($_POST['tecnico_id']) ? (int)$_POST['tecnico_id'] : 0;
        if (!$ticket_id || !$tecnico_id) {
            Flash::error('supervisor.assign.error.incomplete');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit;
        }

        $db = new Database();
        $db->connectDatabase();
        $ticketModel = new Ticket($db->getConnection());
        $userModel = new UserModel($db->getConnection());
        $conn = $db->getConnection();

        
        $stmtChk = $conn->prepare("SELECT tecnico_id FROM tickets WHERE id = ? LIMIT 1");
        if ($stmtChk) {
            $stmtChk->bind_param("i", $ticket_id);
            $stmtChk->execute();
            $rowChk = $stmtChk->get_result()->fetch_assoc();
            if (!empty($rowChk['tecnico_id'])) {
                Flash::error('supervisor.assign.error.ticketHasTech');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
                exit;
            }
        }

        
        $stmtTec = $conn->prepare("SELECT disponibilidad FROM tecnicos WHERE id = ? LIMIT 1");
        if ($stmtTec) {
            $stmtTec->bind_param("i", $tecnico_id);
            $stmtTec->execute();
            $rowTec = $stmtTec->get_result()->fetch_assoc();
            $disp = strtolower(trim($rowTec['disponibilidad'] ?? ''));
            if ($disp !== 'disponible') {
                Flash::error('supervisor.assign.error.techUnavailable');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
                exit;
            }
        }

        
        $ratingAvg = 0.0; $ratingCount = 0; $starsRounded = 0;
        $stmtRating = $conn->prepare("SELECT AVG(stars) AS avg_stars, COUNT(*) AS cnt FROM ticket_ratings WHERE tecnico_id = ?");
        if ($stmtRating) {
            $stmtRating->bind_param("i", $tecnico_id);
            $stmtRating->execute();
            $r = $stmtRating->get_result()->fetch_assoc();
            $ratingAvg = (float)($r['avg_stars'] ?? 0);
            $ratingCount = (int)($r['cnt'] ?? 0);
        }
        $starsRounded = $ratingCount > 0 ? (int)round($ratingAvg) : 3; 
        $limits = [1=>3, 2=>5, 3=>10, 4=>15, 5=>PHP_INT_MAX];
        $limit = $limits[$starsRounded] ?? 5;

        
        $stmtAct = $conn->prepare("SELECT COUNT(*) AS c FROM tickets WHERE tecnico_id = ? AND fecha_cierre IS NULL");
        $activos = 0;
        if ($stmtAct) {
            $stmtAct->bind_param("i", $tecnico_id);
            $stmtAct->execute();
            $activos = (int)($stmtAct->get_result()->fetch_assoc()['c'] ?? 0);
        }
        if ($activos >= $limit) {
            Flash::error('supervisor.assign.error.limitReached');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit;
        }

    $ok = $ticketModel->asignarTecnico((int)$ticket_id, (int)$tecnico_id, (int)$user['id'], 'Supervisor');
        if ($ok) {
            
            $supervisorUserId = $_SESSION['user']['id'] ?? null;
            if ($supervisorUserId) {
                $stmtSup = $conn->prepare("SELECT id FROM supervisores WHERE user_id = ? LIMIT 1");
                if ($stmtSup) {
                    $stmtSup->bind_param("i", $supervisorUserId);
                    $stmtSup->execute();
                    $sup = $stmtSup->get_result()->fetch_assoc();
                    if ($sup && isset($sup['id'])) {
                        $ticketModel->asignarSupervisor($ticket_id, (int)$sup['id']);
                    }
                }
            }

            
            if ($limit !== PHP_INT_MAX) {
                $stmtAct2 = $conn->prepare("SELECT COUNT(*) AS c FROM tickets WHERE tecnico_id = ? AND fecha_cierre IS NULL");
                if ($stmtAct2) {
                    $stmtAct2->bind_param("i", $tecnico_id);
                    $stmtAct2->execute();
                    $activos2 = (int)($stmtAct2->get_result()->fetch_assoc()['c'] ?? 0);
                    if ($activos2 >= $limit) {
                        $userModel->setTecnicoEstado($tecnico_id, 'Ocupado');
                    }
                }
            }
            
            
            try {
                $nm = new NotificationModel($conn);
                
                
                $tecUserId = null; $tecNombre = 'técnico';
                $stmtTU = $conn->prepare("SELECT u.id AS user_id, u.name AS nombre FROM tecnicos t INNER JOIN users u ON u.id=t.user_id WHERE t.id=? LIMIT 1");
                if ($stmtTU) {
                    $stmtTU->bind_param('i', $tecnico_id);
                    $stmtTU->execute();
                    $rowTU = $stmtTU->get_result()->fetch_assoc();
                    if ($rowTU) { $tecUserId = (int)$rowTU['user_id']; $tecNombre = $rowTU['nombre'] ?? $tecNombre; }
                }
                
                
                $cliUserId = null;
                $stmtCU = $conn->prepare("SELECT u.id AS user_id FROM tickets tk INNER JOIN clientes c ON tk.cliente_id=c.id INNER JOIN users u ON u.id=c.user_id WHERE tk.id=? LIMIT 1");
                if ($stmtCU) {
                    $stmtCU->bind_param('i', $ticket_id);
                    $stmtCU->execute();
                    $rowCU = $stmtCU->get_result()->fetch_assoc();
                    if ($rowCU) { $cliUserId = (int)$rowCU['user_id']; }
                }
                
                
                if (!empty($tecUserId)) {
                    $titleT = 'Nuevo ticket asignado';
                    $bodyT  = 'Se te asignó el ticket #'.$ticket_id.'. Revisa Mis Reparaciones.';
                    $nm->create($titleT, $bodyT, 'USER', null, (int)$tecUserId, (int)$user['id']);
                }
                
                
                if (!empty($cliUserId)) {
                    $titleC = 'Técnico asignado a tu ticket';
                    $bodyC  = 'Se asignó el técnico '.$tecNombre.' a tu ticket #'.$ticket_id.'.';
                    $nm->create($titleC, $bodyC, 'USER', null, (int)$cliUserId, (int)$user['id']);
                }
            } catch (\Throwable $e) {  }
            require_once __DIR__ . '/../Core/Flash.php';
            Flash::successQuiet('supervisor.assign.success.assigned');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
        } else {
            Flash::error('supervisor.assign.error.assignFailed');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
        }
        exit;
    }

    public function GestionInventario() {
        Auth::checkRole(['Supervisor']);
        I18n::boot();

        $db = new Database();
        $db->connectDatabase();
        $inventarioModel = new InventarioModel($db->getConnection());
        $items = $inventarioModel->listar();
        $categorias = $inventarioModel->listarCategorias();

        include_once __DIR__ . '/../Views/Supervisor/GestionInventario.php';
    }

    public function Presupuestos() {
        Auth::checkRole(['Supervisor']);
        I18n::boot();

        $db = new Database();
        $db->connectDatabase();
        $ticketModel = new Ticket($db->getConnection());
        $itemTicketModel = new ItemTicketModel($db->getConnection());
        $laborModel = new TicketLaborModel($db->getConnection());

        
        $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
        $filtroCierre = strtolower(trim($_GET['cierre'] ?? 'todos')); 
        $tickets = $ticket_id ? [$ticketModel->ver($ticket_id)] : $ticketModel->getAllTickets();
        if (!$ticket_id) {
            
            $tickets = is_array($tickets) && isset($tickets[0]) ? $tickets : $tickets;
        }

        
        if ($ticket_id && $tickets && isset($tickets['id'])) {
            $tickets = [$tickets];
        }

        
        if ($filtroCierre === 'finalizados') {
            $tickets = array_values(array_filter($tickets, function($t){ return !empty($t['fecha_cierre']); }));
        } elseif ($filtroCierre === 'activos') {
            $tickets = array_values(array_filter($tickets, function($t){ return empty($t['fecha_cierre']); }));
        }

        $presupuestos = [];
        foreach ($tickets as $t) {
            if (!$t || !isset($t['id'])) continue;
            $tid = (int)$t['id'];
            $items = $itemTicketModel->listarPorTicket($tid);
            
            $res = LogFormatter::resumenPresupuesto($db->getConnection(), $tid);
            $subtotal_items = (float)$res['subtotal'];
            $mano_obra = (float)$res['mano'];
            $total = (float)$res['total'];
            $presupuestos[] = [
                'ticket' => $t,
                'items' => $items,
                'subtotal_items' => $subtotal_items,
                'mano_obra' => $mano_obra,
                'total' => $total,
            ];
        }

        include_once __DIR__ . '/../Views/Supervisor/Presupuestos.php';
    }
}