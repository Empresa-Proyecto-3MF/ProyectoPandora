<?php    
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Inventario.php';
require_once __DIR__ . '/../Models/ItemTicket.php';
require_once __DIR__ . '/../Models/TicketLabor.php';
require_once __DIR__ . '/../Models/Rating.php';
require_once __DIR__ . '/../Models/TecnicoStats.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/HistorialController.php';
require_once __DIR__ . '/../Core/Date.php';
require_once __DIR__ . '/../Core/Storage.php';
require_once __DIR__ . '/../Core/Flash.php';

class TecnicoController {  
    private $db;
    private $historial;

    private function estadoBadgeClass(?string $estado): string {
        $s = strtolower(trim($estado ?? ''));
        if (in_array($s, ['finalizado'], true)) return 'badge badge--success';
        if (in_array($s, ['cerrado','cancelado'], true)) return 'badge badge--danger';
        if (in_array($s, ['en proceso','diagnóstico','diagnostico','reparación','reparacion','en reparación','en pruebas'], true)) return 'badge badge--info';
        if (in_array($s, ['en espera','pendiente','presupuesto'], true)) return 'badge badge--warning';
        if (in_array($s, ['abierto','nuevo','recibido'], true)) return 'badge badge--primary';
        return 'badge badge--muted';
    }

    private function aplicarFiltrosYPresentacion(array $tickets): array {
        $estado = strtolower(trim($_GET['estado'] ?? 'activos'));
        $q = trim((string)($_GET['q'] ?? ''));
        $desde = trim((string)($_GET['desde'] ?? ''));
        $hasta = trim((string)($_GET['hasta'] ?? ''));

                    Flash::error('Técnico inválido.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisStats');
        if ($estado === 'activos' || $estado === 'finalizados') {
            $tickets = array_values(array_filter($tickets, function($t) use ($estado){
                $cerrado = !empty($t['fecha_cierre']);
                    Flash::error('Ticket no asociado.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisStats');
            }));
        }

        
        if ($q !== '') {
            $qLower = mb_strtolower($q, 'UTF-8');
            $tickets = array_values(array_filter($tickets, function($t) use ($qLower){
                $fields = [
                    $t['cliente'] ?? '',
                    $t['marca'] ?? '',
                    $t['modelo'] ?? '',
                    $t['descripcion_falla'] ?? '',
                    $t['estado'] ?? '',
                ];
                foreach ($fields as $f) {
                    if ($f !== null && $f !== '' && strpos(mb_strtolower((string)$f, 'UTF-8'), $qLower) !== false) return true;
                }
                return false;
            }));
        }

        
        if ($desde !== '' || $hasta !== '') {
            $tickets = array_values(array_filter($tickets, function($t) use ($desde, $hasta){
                $f = substr((string)($t['fecha_creacion'] ?? ''), 0, 10);
                if ($desde !== '' && $f < $desde) return false;
                if ($hasta !== '' && $f > $hasta) return false;
                return true;
            }));
        }

        
        foreach ($tickets as &$t) {
            $t['estadoClass'] = $this->estadoBadgeClass($t['estado'] ?? '');
            if (!empty($t['fecha_creacion'])) {
                $t['fecha_exact'] = DateHelper::exact($t['fecha_creacion']);
                $t['fecha_human'] = DateHelper::smart($t['fecha_creacion']);
            }
        }
        unset($t);
        return $tickets;
    }

    
    private function ticketPreviewUrl(array $ticket): string {
        $imgDevice = \Storage::resolveDeviceUrl($ticket['img_dispositivo'] ?? '');
        $imgSrc = $imgDevice;
        try {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $relDir = 'ticket/' . (int)($ticket['id'] ?? 0);
            $absDir = \Storage::basePath() . '/' . $relDir . '/';
            if (is_dir($absDir)) {
                $files = @scandir($absDir) ?: [];
                $latestFile = '';
                $latestTime = 0;
                foreach ($files as $fn) {
                    if ($fn === '.' || $fn === '..') continue;
                    $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed, true)) continue;
                    $t = @filemtime($absDir . $fn) ?: 0;
                    if ($t >= $latestTime) { $latestTime = $t; $latestFile = $fn; }
                }
                if ($latestFile) {
                    $imgSrc = \Storage::publicUrl($relDir . '/' . $latestFile);
                }
            }
            if ($imgSrc === $imgDevice) {
                $legacyDir = __DIR__ . '/../Public/img/imgTickets/' . (int)($ticket['id'] ?? 0) . '/';
                $legacyUrlBase = '/ProyectoPandora/Public/img/imgTickets/' . (int)($ticket['id'] ?? 0) . '/';
                if (is_dir($legacyDir)) {
                    $files = @scandir($legacyDir) ?: [];
                    $latestFile = '';
                    $latestTime = 0;
                    foreach ($files as $fn) {
                        if ($fn === '.' || $fn === '..') continue;
                        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed, true)) continue;
                        $t = @filemtime($legacyDir . $fn) ?: 0;
                        if ($t >= $latestTime) { $latestTime = $t; $latestFile = $fn; }
                    }
                    if ($latestFile) {
                        $imgSrc = $legacyUrlBase . rawurlencode($latestFile);
                    }
                }
            }
        } catch (\Throwable $e) {  }
        return $imgSrc;
    }

    public function __construct() {
        $this->db = new Database();
        $this->db->connectDatabase();
        $this->historial = new HistorialController();
    }

    public function PanelTecnico() {
        header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones');
        exit;
    }

    public function MisReparaciones() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Tecnico') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

    $ticketModel = new Ticket($this->db->getConnection());

        
    $tickets = $ticketModel->getTicketsByTecnicoId($user['id']);
    $tickets = $this->aplicarFiltrosYPresentacion($tickets);
    foreach ($tickets as &$t) { $t['img_preview'] = $this->ticketPreviewUrl($t); }
    unset($t);

    include_once __DIR__ . '/../Views/Tecnicos/MisReparaciones.php';
    }

    public function MisRepuestos() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Tecnico') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        $ticketModel = new Ticket($this->db->getConnection());
    $inventarioModel = new InventarioModel($this->db->getConnection());
    $categorias = $inventarioModel->listarCategorias();

        
        $tickets = $ticketModel->getTicketsByTecnicoId($user['id']);

        
        $categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null;
        $buscar = isset($_GET['q']) ? trim($_GET['q']) : '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['perPage'] ?? 10)));
        $sort = $_GET['sort'] ?? 'i.id';
        $dir = $_GET['dir'] ?? 'DESC';
        $offset = ($page - 1) * $perPage;

        if (method_exists($inventarioModel, 'contarFiltrado')) {
            $total = $inventarioModel->contarFiltrado($categoria_id, $buscar);
        } else {
            $total = 0;
        }
        if (method_exists($inventarioModel, 'listarFiltrado')) {
            $items = $inventarioModel->listarFiltrado($categoria_id, $buscar, $perPage, $offset, $sort, $dir);
        } else {
            $items = $inventarioModel->listar();
        }
        $totalPages = $perPage > 0 ? (int)ceil(($total ?: count($items)) / $perPage) : 1;

        
        $itemTicketModel = new ItemTicketModel($this->db->getConnection());
        $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : (count($tickets) ? (int)$tickets[0]['id'] : 0);
        $items_ticket = $ticket_id ? $itemTicketModel->listarPorTicket($ticket_id) : [];

    include_once __DIR__ . '/../Views/Tecnicos/MisRepuestos.php';
    }

    public function SolicitarRepuesto() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Tecnico') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::error('Método inválido.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }
    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $clientRev = isset($_POST['rev_state']) ? (string)$_POST['rev_state'] : null;
        $inventario_id = (int)($_POST['inventario_id'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 0);
    $valor_unitario = 0.0; 
        if ($ticket_id <= 0 || $inventario_id <= 0 || $cantidad <= 0) {
            Flash::error('Parámetros inválidos.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }

        $inventarioModel = new InventarioModel($this->db->getConnection());
        $itemTicketModel = new ItemTicketModel($this->db->getConnection());
        $ticketModel = new Ticket($this->db->getConnection());

        
        
        $tickets = (new Ticket($this->db->getConnection()))->getTicketsByTecnicoId($user['id']);
        $ticketIds = array_map(function($t){ return (int)$t['id']; }, $tickets);
        if (!in_array($ticket_id, $ticketIds, true)) {
            Flash::error('Ticket no asociado al técnico.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }

        $inv = $inventarioModel->obtenerPorId($inventario_id);
        if (!$inv) {
            Flash::error('Ítem de inventario inexistente.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }
        $valor_unitario = (float)$inv['valor_unitario'];
        $supervisor_id = (int)($ticketModel->getSupervisorId($ticket_id) ?? 0);
        if ($supervisor_id <= 0) { $supervisor_id = 0; }

        
        $tecnico_id = $this->obtenerTecnicoIdPorUserId($user['id']);
        if (!$tecnico_id) {
            Flash::error('Identidad de técnico inválida.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }

        
        
        $conn = $this->db->getConnection();
        if ($st = $conn->prepare("SELECT e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id=t.estado_id WHERE t.id=? LIMIT 1")) {
            $st->bind_param('i', $ticket_id); $st->execute(); $row = $st->get_result()->fetch_assoc();
            $estadoActualNombre = strtolower(trim($row['estado'] ?? ''));
        } else { $estadoActualNombre = ''; }

        
        $laborModel2 = new TicketLaborModel($conn);
        $labor = $laborModel2->getByTicket($ticket_id);
        $itemsTicket = $itemTicketModel->listarPorTicket($ticket_id);
        $laborAmountNow = (float)($labor['labor_amount'] ?? 0);
        $itemsCountNow = is_array($itemsTicket) ? count($itemsTicket) : 0;
        $serverRev = md5($estadoActualNombre.'|'.(string)$laborAmountNow.'|'.(string)$itemsCountNow);

        if ($clientRev && $clientRev !== $serverRev && $estadoActualNombre === 'presupuesto') {
            Flash::error('Estado desactualizado (presupuesto publicado).');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id='.(int)$ticket_id);
            exit;
        }

        
        if ($estadoActualNombre === 'presupuesto') {
            Flash::error('Ticket bloqueado: presupuesto ya publicado.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id='.(int)$ticket_id);
            exit;
        }
        if ($estadoActualNombre === 'en espera' && $laborAmountNow <= 0) {
            Flash::error('Debe definir mano de obra antes de agregar repuestos.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id='.(int)$ticket_id);
            exit;
        }

        
        $conn->begin_transaction();
        $valor_total = $cantidad * $valor_unitario;
        $stockOk = $inventarioModel->reducirStock($inventario_id, $cantidad);
        $itemOk = false;
        if ($stockOk) {
            $itemOk = $itemTicketModel->crear($ticket_id, $inventario_id, $tecnico_id, $supervisor_id, $cantidad, $valor_total);
        }
        if ($stockOk && $itemOk) {
            $conn->commit();
            
            $this->historial->agregarAccion(
                'Solicitud de repuesto',
                "Técnico {$user['name']} solicitó {$cantidad} und(s) del inventario ID {$inventario_id} para ticket {$ticket_id} (total $valor_total)."
            );
            
            
            $labor = $laborModel2->getByTicket($ticket_id);
            if ($labor && (float)($labor['labor_amount'] ?? 0) > 0) {
                
                
                require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
                $hist2 = new TicketEstadoHistorialModel($this->db->getConnection());
                $hist2->add($ticket_id, (int)($ticketModel->ver($ticket_id)['estado_id'] ?? 0), (int)$user['id'], 'Tecnico', 'Repuestos listos + mano de obra definida: presupuesto listo para publicar');
            }
            require_once __DIR__ . '/../Core/Flash.php';
            Flash::successQuiet('Repuesto asignado.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&ticket_id=' . $ticket_id);
            exit;
        } else {
            $conn->rollback();
            Flash::error('Stock insuficiente o error al asignar.');
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos');
            exit;
        }
    }

    public function MisStats() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Tecnico') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        $conn = $this->db->getConnection();
        
        $tecnico_id = $this->obtenerTecnicoIdPorUserId($user['id']);
        $statsModel = new TecnicoStatsModel($conn);
        $ratingModel = new RatingModel($conn);
        $ticketModel = new Ticket($conn);
        $stats = $statsModel->getByTecnico($tecnico_id) ?: ['labor_min'=>0,'labor_max'=>0];
        list($avg, $count) = $ratingModel->getAvgForTecnico($tecnico_id);
        $reviews = $tecnico_id ? $ratingModel->listForTecnico($tecnico_id) : [];

        
        $res = $conn->query("SELECT 
            SUM(CASE WHEN fecha_cierre IS NOT NULL THEN 1 ELSE 0 END) AS finalizados,
            SUM(CASE WHEN fecha_cierre IS NULL THEN 1 ELSE 0 END) AS activos
            FROM tickets t INNER JOIN tecnicos tc ON t.tecnico_id = tc.id WHERE tc.user_id = " . (int)$user['id']);
        $counters = $res ? $res->fetch_assoc() : ['finalizados'=>0,'activos'=>0];

        include_once __DIR__ . '/../Views/Tecnicos/MisStats.php';
    }

    public function ActualizarStats() {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        $conn = $this->db->getConnection();
        $tecnico_id = $this->obtenerTecnicoIdPorUserId($user['id']);

    if (isset($_POST['ticket_id']) && isset($_POST['labor_amount'])) {
            
            $ticket_id = (int)$_POST['ticket_id'];
            $labor_amount = max(0, (float)$_POST['labor_amount']);
            $role = $user['role'] ?? '';
            $isTecnico = ($role === 'Tecnico');
            $isSupervisor = ($role === 'Supervisor');
            $ticketTecnicoId = null;
            
            $stmtT = $conn->prepare("SELECT t.tecnico_id, e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id = t.estado_id WHERE t.id = ? LIMIT 1");
            if ($stmtT) { $stmtT->bind_param("i", $ticket_id); $stmtT->execute(); $rT = $stmtT->get_result()->fetch_assoc(); $ticketTecnicoId = $rT['tecnico_id'] ?? null; }
            $estadoActualNombre = strtolower(trim($rT["estado"] ?? ''));

            if ($isTecnico) {
                
                if (!$tecnico_id) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisStats&error=tecnico');
                    exit;
                }
                if ((int)($ticketTecnicoId ?? 0) <= 0) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisStats&error=ticket');
                    exit;
                }
                $stmtChk = $conn->prepare("SELECT COUNT(*) c FROM tecnicos tc WHERE tc.id = ? AND tc.user_id = ?");
                if ($stmtChk) {
                    $stmtChk->bind_param("ii", $ticketTecnicoId, $user['id']);
                    $stmtChk->execute();
                    $row = $stmtChk->get_result()->fetch_assoc();
                    if (((int)($row['c'] ?? 0)) === 0) {
                        header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisStats&error=ticket');
                        exit;
                    }
                }
                
                
                if (!in_array($estadoActualNombre, ['diagnóstico','diagnostico','en espera'], true)) {
                    Flash::error('Estado no permite definir mano de obra.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id);
                    exit;
                }
                
                
                $saveTecnicoId = (int)$ticketTecnicoId;
            } elseif ($isSupervisor) {
                
                if ((int)($ticketTecnicoId ?? 0) <= 0) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=sin_tecnico');
                    exit;
                }
                
                if (!in_array($estadoActualNombre, ['diagnóstico','diagnostico'], true)) {
                    Flash::error('Estado no permite definir mano de obra.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos');
                    exit;
                }
                $saveTecnicoId = (int)$ticketTecnicoId;
            } else {
                header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
                exit;
            }
            $laborModel = new TicketLaborModel($conn);
            $existing = $laborModel->getByTicket($ticket_id);
            if ($existing && (float)($existing['labor_amount'] ?? 0) > 0) {
                
                if ($estadoActualNombre !== 'en espera') {
                    if ($isSupervisor) {
                        Flash::error('Mano de obra bloqueada.');
                        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos');
                    } else {
                        Flash::error('Mano de obra bloqueada.');
                        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id);
                    }
                    exit;
                }
            }
            
            
            
            require_once __DIR__ . '/../Models/ItemTicket.php';
            $itemModelX_pre = new ItemTicketModel($conn);
            $itemsX_pre = $itemModelX_pre->listarPorTicket($ticket_id);
            if ($estadoActualNombre === 'en espera') {
                $hadLabor = $existing && (float)($existing['labor_amount'] ?? 0) > 0;
                if (!$hadLabor || empty($itemsX_pre)) {
                    
                    
                    Flash::error('No puede definir mano de obra en este estado.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id);
                    exit;
                }
            }

            
            $clientRev = isset($_POST['rev_state']) ? (string)$_POST['rev_state'] : null;
            if ($clientRev) {
                
                $currLabor = (float)($existing['labor_amount'] ?? 0);
                $currItems = $itemsX_pre; 
            }
            $laborModel->upsert($ticket_id, (int)$saveTecnicoId, $labor_amount);
            
            
            $itemModelX = $itemModelX_pre; $itemsX = $itemsX_pre;

            
            if ($clientRev) {
                $serverEstadoLower = strtolower($estadoActualNombre);
                
                $reloaded = $laborModel->getByTicket($ticket_id);
                $laborNow = (float)($reloaded['labor_amount'] ?? 0);
                $revNow = md5($serverEstadoLower.'|'.(string)$laborNow.'|'.(string)count($itemsX));
                if ($revNow !== $clientRev) {
                    
                    if ($serverEstadoLower === 'presupuesto') {
                        Flash::error('Cambios desactualizados.');
                        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id);
                        exit;
                    }
                }
            }
            if (!empty($itemsX) && $labor_amount > 0) {
                
                require_once __DIR__ . '/../Models/EstadoTicket.php';
                $em = new EstadoTicketModel($conn);
                $stmtEstados = $conn->query("SELECT id, name FROM estados_tickets");
                $enEsperaId = 0;
                if ($stmtEstados) { while($r=$stmtEstados->fetch_assoc()){ if (strcasecmp($r['name'],'En espera')===0) { $enEsperaId=(int)$r['id']; break; } } }
                if ($enEsperaId && $estadoActualNombre !== 'presupuesto') {
                    
                    $conn->query("UPDATE tickets SET estado_id = ".$enEsperaId." WHERE id = ".$ticket_id);
                    
                    if (in_array($estadoActualNombre, ['diagnóstico','diagnostico'], true)) {
                        require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
                        $histM = new TicketEstadoHistorialModel($conn);
                        $histM->add($ticket_id, $enEsperaId, (int)($user['id']), $user['role'], 'Diagnóstico finalizado: items y mano de obra listos (En espera)');
                    }
                }
            }
            
            if ($isSupervisor) {
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&ok=labor');
            } else {
                header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id.'&ok=labor');
            }
            exit;
        }

        

        header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
    }

    private function obtenerTecnicoIdPorUserId($user_id) {
        $stmt = $this->db->getConnection()->prepare("SELECT id FROM tecnicos WHERE user_id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['id'] ?? null;
    }
}