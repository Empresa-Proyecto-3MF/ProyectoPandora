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

        // Filtro por estado (activos: sin fecha_cierre; finalizados: con fecha_cierre)
        if ($estado === 'activos' || $estado === 'finalizados') {
            $tickets = array_values(array_filter($tickets, function($t) use ($estado){
                $cerrado = !empty($t['fecha_cierre']);
                return $estado === 'activos' ? !$cerrado : $cerrado;
            }));
        }

        // Filtro por texto
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

        // Filtro por fechas (creación)
        if ($desde !== '' || $hasta !== '') {
            $tickets = array_values(array_filter($tickets, function($t) use ($desde, $hasta){
                $f = substr((string)($t['fecha_creacion'] ?? ''), 0, 10);
                if ($desde !== '' && $f < $desde) return false;
                if ($hasta !== '' && $f > $hasta) return false;
                return true;
            }));
        }

        // Enriquecer (badge + fechas preformateadas)
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
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=1');
            exit;
        }
    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $clientRev = isset($_POST['rev_state']) ? (string)$_POST['rev_state'] : null;
        $inventario_id = (int)($_POST['inventario_id'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 0);
    $valor_unitario = 0.0; 
        if ($ticket_id <= 0 || $inventario_id <= 0 || $cantidad <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=1');
            exit;
        }

        $inventarioModel = new InventarioModel($this->db->getConnection());
        $itemTicketModel = new ItemTicketModel($this->db->getConnection());
        $ticketModel = new Ticket($this->db->getConnection());

        
        
        $tickets = (new Ticket($this->db->getConnection()))->getTicketsByTecnicoId($user['id']);
        $ticketIds = array_map(function($t){ return (int)$t['id']; }, $tickets);
        if (!in_array($ticket_id, $ticketIds, true)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=ticket');
            exit;
        }

        $inv = $inventarioModel->obtenerPorId($inventario_id);
        if (!$inv) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=inventario');
            exit;
        }
        $valor_unitario = (float)$inv['valor_unitario'];
        $supervisor_id = (int)($ticketModel->getSupervisorId($ticket_id) ?? 0);
        if ($supervisor_id <= 0) { $supervisor_id = 0; }

        // Identificar técnico antes de alterar stock
        $tecnico_id = $this->obtenerTecnicoIdPorUserId($user['id']);
        if (!$tecnico_id) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=tecnico');
            exit;
        }

        // Reglas de estado y concurrencia (previas a descontar stock)
        // Estado actual del ticket
        $conn = $this->db->getConnection();
        if ($st = $conn->prepare("SELECT e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id=t.estado_id WHERE t.id=? LIMIT 1")) {
            $st->bind_param('i', $ticket_id); $st->execute(); $row = $st->get_result()->fetch_assoc();
            $estadoActualNombre = strtolower(trim($row['estado'] ?? ''));
        } else { $estadoActualNombre = ''; }

        // Labor e items actuales
        $laborModel2 = new TicketLaborModel($conn);
        $labor = $laborModel2->getByTicket($ticket_id);
        $itemsTicket = $itemTicketModel->listarPorTicket($ticket_id);
        $laborAmountNow = (float)($labor['labor_amount'] ?? 0);
        $itemsCountNow = is_array($itemsTicket) ? count($itemsTicket) : 0;
        $serverRev = md5($estadoActualNombre.'|'.(string)$laborAmountNow.'|'.(string)$itemsCountNow);

        if ($clientRev && $clientRev !== $serverRev && $estadoActualNombre === 'presupuesto') {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=stale&ticket_id='.(int)$ticket_id);
            exit;
        }

        // Permitir agregar repuestos en Diagnóstico; en En espera solo si ya hay mano de obra (diagnóstico finalizado) y no publicado
        if ($estadoActualNombre === 'presupuesto') {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=locked&ticket_id='.(int)$ticket_id);
            exit;
        }
        if ($estadoActualNombre === 'en espera' && $laborAmountNow <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=labor_required&ticket_id='.(int)$ticket_id);
            exit;
        }

        // Solo ahora, descontar stock
        if (!$inventarioModel->reducirStock($inventario_id, $cantidad)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=stock');
            exit;
        }

        // Agregar el ítem al ticket cliente :3
        $valor_total = $cantidad * $valor_unitario;
        $ok = $itemTicketModel->crear($ticket_id, $inventario_id, $tecnico_id, $supervisor_id, $cantidad, $valor_total);
        if ($ok) {
            // Registra en el historial
            $this->historial->agregarAccion(
                'Solicitud de repuesto',
                "Técnico {$user['name']} solicitó {$cantidad} und(s) del inventario ID {$inventario_id} para ticket {$ticket_id} (total $valor_total)."
            );
            
            // Recalcular tras agregar
            $labor = $laborModel2->getByTicket($ticket_id);
            if ($labor && (float)($labor['labor_amount'] ?? 0) > 0) {
                // No cambiamos el estado automáticamente para evitar retrocesos a "En espera".
                // El técnico verá el botón "Diagnóstico finalizado" en la vista del ticket si está en Diagnóstico.
                require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
                $hist2 = new TicketEstadoHistorialModel($this->db->getConnection());
                $hist2->add($ticket_id, (int)($ticketModel->ver($ticket_id)['estado_id'] ?? 0), (int)$user['id'], 'Tecnico', 'Repuestos listos + mano de obra definida: presupuesto listo para publicar');
            }
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&success=1&ticket_id=' . $ticket_id);
            exit;
        }
        header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisRepuestos&error=1');
        exit;
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
                
                // Permitir editar mano de obra durante Diagnóstico o En espera (mientras no esté publicado el presupuesto)
                if (!in_array($estadoActualNombre, ['diagnóstico','diagnostico','en espera'], true)) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id.'&error=labor_estado');
                    exit;
                }
                
                // Se elimina validación por rangos predefinidos (labor_min/labor_max)
                $saveTecnicoId = (int)$ticketTecnicoId;
            } elseif ($isSupervisor) {
                
                if ((int)($ticketTecnicoId ?? 0) <= 0) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=sin_tecnico');
                    exit;
                }
                
                if (!in_array($estadoActualNombre, ['diagnóstico','diagnostico'], true)) {
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=labor_estado');
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
                // Si ya existe mano de obra, permitir editarla SOLO si está en 'En espera' (antes de que el supervisor publique presupuesto)
                if ($estadoActualNombre !== 'en espera') {
                    if ($isSupervisor) {
                        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=labor_locked');
                    } else {
                        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id.'&error=labor_locked');
                    }
                    exit;
                }
            }
            // Reglas de edición según estado
            // 1) En Diagnóstico: permitir definir mano de obra solo si aún no existe (como ya se valida arriba)
            // 2) En En espera: permitir editar mano de obra solo si YA existía mano de obra (>0) y hay ítems (diagnóstico finalizado previamente)
            require_once __DIR__ . '/../Models/ItemTicket.php';
            $itemModelX_pre = new ItemTicketModel($conn);
            $itemsX_pre = $itemModelX_pre->listarPorTicket($ticket_id);
            if ($estadoActualNombre === 'en espera') {
                $hadLabor = $existing && (float)($existing['labor_amount'] ?? 0) > 0;
                if (!$hadLabor || empty($itemsX_pre)) {
                    // No permitir crear mano de obra por primera vez en "En espera",
                    // ni editar si aún no hubo diagnóstico completo (sin ítems)
                    header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id.'&error=labor_estado');
                    exit;
                }
            }

            // Concurrency guard: if client sent a rev_state, compare with server-side current rev
            $clientRev = isset($_POST['rev_state']) ? (string)$_POST['rev_state'] : null;
            if ($clientRev) {
                // Build current rev like in verTicket
                $currLabor = (float)($existing['labor_amount'] ?? 0);
                $currItems = $itemsX_pre; // set below but we compute early after we fetch
            }
            $laborModel->upsert($ticket_id, (int)$saveTecnicoId, $labor_amount);
            
            // Reutilizamos lista de ítems
            $itemModelX = $itemModelX_pre; $itemsX = $itemsX_pre;

            // Rebuild server rev and compare if provided
            if ($clientRev) {
                $serverEstadoLower = strtolower($estadoActualNombre);
                // After upsert, reload labor
                $reloaded = $laborModel->getByTicket($ticket_id);
                $laborNow = (float)($reloaded['labor_amount'] ?? 0);
                $revNow = md5($serverEstadoLower.'|'.(string)$laborNow.'|'.(string)count($itemsX));
                if ($revNow !== $clientRev) {
                    // If already published (estado pasó a 'Presupuesto'), block and redirect
                    if ($serverEstadoLower === 'presupuesto') {
                        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.(int)$ticket_id.'&error=stale');
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
                    // Si aún no fue publicado (no está en 'Presupuesto'), dejar/forzar 'En espera'
                    $conn->query("UPDATE tickets SET estado_id = ".$enEsperaId." WHERE id = ".$ticket_id);
                    // Registrar en historial solo si provenía de Diagnóstico
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

        // Se elimina la ruta de configuración de labor_min/labor_max; no se aceptan más estos parámetros

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