<?php
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/HistorialController.php';
require_once __DIR__ . '/../Models/EstadoTicket.php';
require_once __DIR__ . '/../Models/Rating.php';
require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Core/LogFormatter.php';
require_once __DIR__ . '/../Core/Date.php';
require_once __DIR__ . '/../Core/Storage.php';

class TicketController
{
    private $ticketModel;
    private $deviceModel;
    private $userModel;
    private $estadoModel;
    private $historialController;
    private $histEstadoModel;

    public function __construct()
    {
        $db = new Database();
        $db->connectDatabase();
        $this->ticketModel = new Ticket($db->getConnection());
        $this->deviceModel = new DeviceModel($db->getConnection());
        $this->userModel = new UserModel($db->getConnection());
        $this->estadoModel = new EstadoTicketModel($db->getConnection());
        $this->historialController = new HistorialController();
    $this->histEstadoModel = new TicketEstadoHistorialModel($db->getConnection());
    }

    
    public function SyncStatus() {
        $user = Auth::user();
        if (!$user) { http_response_code(401); echo json_encode(['error'=>'auth']); return; }
        $ticket_id = (int)($_GET['ticket_id'] ?? 0);
        if ($ticket_id <= 0) { http_response_code(400); echo json_encode(['error'=>'ticket']); return; }
        $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
        header('Content-Type: application/json');
        try {
            
            $st = $conn->prepare("SELECT e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id=t.estado_id WHERE t.id=? LIMIT 1");
            $st->bind_param('i', $ticket_id); $st->execute(); $row = $st->get_result()->fetch_assoc();
            $estadoLower = strtolower(trim($row['estado'] ?? ''));

            
            require_once __DIR__ . '/../Models/TicketLabor.php';
            $lm = new TicketLaborModel($conn); $labor = $lm->getByTicket($ticket_id); $laborAmount = (float)($labor['labor_amount'] ?? 0);
            
            require_once __DIR__ . '/../Models/ItemTicket.php';
            $im = new ItemTicketModel($conn); $items = $im->listarPorTicket($ticket_id); $itemsCount = is_array($items) ? count($items) : 0;
            $rev = md5($estadoLower.'|'.(string)$laborAmount.'|'.(string)$itemsCount);
            $published = ($estadoLower === 'presupuesto');
            $canEdit = (!$published) && (($estadoLower === 'diagnóstico' || $estadoLower === 'diagnostico') || ($estadoLower === 'en espera' && $itemsCount>0 && $laborAmount>0));
            $clientRev = isset($_GET['rev']) ? trim((string)$_GET['rev']) : '';

            $response = [
                'published' => $published,
                'rev' => $rev,
                'canEdit' => $canEdit,
            ];

            if ($clientRev !== $rev) {
                $ticket = $this->ticketModel->ver($ticket_id);
                if ($ticket) {
                    $estadoStr = $ticket['estado'] ?? $ticket['estado_actual'] ?? '';
                    $estadoBadge = $this->badgeClassFor(strtolower(trim($estadoStr)));
                    $clienteNombre = $ticket['cliente'] ?? $ticket['cliente_nombre'] ?? $ticket['user_name'] ?? '';
                    $deviceNombre = trim(($ticket['marca'] ?? '') . ' ' . ($ticket['modelo'] ?? ''));
                    $descripcion = $ticket['descripcion'] ?? $ticket['descripcion_falla'] ?? '';
                    $fechaCreacion = $ticket['fecha_creacion'] ?? null;
                    $fechaCierre = $ticket['fecha_cierre'] ?? null;
                    $imgUrl = \Storage::resolveDeviceUrl($ticket['img_dispositivo'] ?? '');

                    $response['detail'] = [
                        'cliente' => $clienteNombre,
                        'device' => $deviceNombre,
                        'estado' => [
                            'label' => $estadoStr,
                            'badge_class' => $estadoBadge,
                        ],
                        'tecnico' => [
                            'name' => $ticket['tecnico'] ?? null,
                        ],
                        'fechas' => [
                            'creacion' => [
                                'human' => $fechaCreacion ? DateHelper::smart($fechaCreacion) : '',
                                'exact' => $fechaCreacion ? DateHelper::exact($fechaCreacion) : '',
                            ],
                            'cierre' => [
                                'human' => $fechaCierre ? DateHelper::smart($fechaCierre) : '',
                                'exact' => $fechaCierre ? DateHelper::exact($fechaCierre) : '',
                            ],
                        ],
                        'descripcion' => $descripcion,
                        'image' => [
                            'url' => $imgUrl,
                        ],
                        'rev' => $rev,
                    ];

                    require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
                    $th = new TicketEstadoHistorialModel($conn);
                    $events = $th->listByTicket($ticket_id);
                    $timeline = ['Tecnico'=>[], 'Cliente'=>[], 'Supervisor'=>[]];
                    foreach ($events as $ev) {
                        $role = $ev['user_role'] ?? '';
                        if ($role === 'Administrador') { $role = 'Supervisor'; }
                        if (!isset($timeline[$role])) { continue; }
                        $evEstado = $ev['estado'] ?? '';
                        $timeline[$role][] = [
                            'human' => DateHelper::smart($ev['created_at'] ?? ''),
                            'exact' => DateHelper::exact($ev['created_at'] ?? ''),
                            'estado' => $evEstado,
                            'badge_class' => $this->badgeClassFor(strtolower(trim($evEstado))),
                            'comentario' => $ev['comentario'] ?? '',
                        ];
                    }
                    $response['timeline'] = $timeline;
                }
            }

            echo json_encode($response);
        } catch (\Throwable $e) {
            echo json_encode(['error'=>'server']);
        }
    }
    
    private function transicionesValidas(): array {
        return [
            'Nuevo' => ['En espera'],
            'En espera' => ['Diagnóstico'],
            'Diagnóstico' => ['Presupuesto'],
            'Presupuesto' => ['En reparación', 'Cancelado'],
            'En reparación' => ['En pruebas'],
            'En pruebas' => ['Listo para retirar'],
            'Listo para retirar' => ['Finalizado'],
            'Finalizado' => [],
            'Cancelado' => []
        ];
    }

    
    private function normalizarEstadoNombre(string $name): string {
        $n = strtolower(trim($name));
        $map = [
            'diagnostico' => 'diagnóstico',
            'en reparacion' => 'en reparación',
        ];
        return $map[$n] ?? $n;
    }

    
    private function puedeTransicionar(string $desde, string $hacia): bool {
        $from = $this->normalizarEstadoNombre($desde);
        $to = $this->normalizarEstadoNombre($hacia);
        $rules = [];
        foreach ($this->transicionesValidas() as $k => $arr) {
            $rules[strtolower($k)] = array_map(fn($x)=>$this->normalizarEstadoNombre($x), $arr);
        }
        return in_array($to, $rules[$from] ?? [], true);
    }

    
    private function estadoIdPorNombre(string $nombre): ?int {
        $db = new Database(); $db->connectDatabase(); $cn = $db->getConnection();
        $sql = "SELECT id FROM estados_tickets WHERE LOWER(name) = LOWER(?) LIMIT 1";
        $st = $cn->prepare($sql);
        if (!$st) return null;
        $st->bind_param('s', $nombre);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        return $r ? (int)$r['id'] : null;
    }

    
    private function estadoNombrePorId(int $id): ?string {
        $db = new Database(); $db->connectDatabase(); $cn = $db->getConnection();
        $st = $cn->prepare("SELECT name FROM estados_tickets WHERE id = ? LIMIT 1");
        if (!$st) return null;
        $st->bind_param('i', $id);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        return $r['name'] ?? null;
    }

    private function badgeClassFor(string $estadoLower): string {
        
        if (in_array($estadoLower, ['finalizado'])) return 'badge badge--success';
        if (in_array($estadoLower, ['cerrado','cancelado'])) return 'badge badge--danger';
        if (in_array($estadoLower, ['en proceso','diagnóstico','diagnostico','reparación','reparacion','en reparación','en pruebas'])) return 'badge badge--info';
        if (in_array($estadoLower, ['en espera','pendiente','presupuesto'])) return 'badge badge--warning';
        if (in_array($estadoLower, ['abierto','nuevo','recibido'])) return 'badge badge--primary';
        return 'badge badge--muted';
    }

    private function buildTecnicoAcciones(string $estadoActual, array $estados): array {
        
        $mapId = [];
        foreach ($estados as $e) {
            $mapId[strtolower(trim($e['name']))] = (int)$e['id'];
        }
        $s = strtolower(trim($estadoActual));
        $acciones = [];
        $mensajeKey = '';

        
        if ($s === 'nuevo') {
            $mensajeKey = 'ticket.tech.msg.new';
        } elseif ($s === 'en espera') {
            if (isset($mapId['diagnóstico'])) $acciones[] = ['label'=>__('ticket.tech.action.startDiagnosis'),'estado_id'=>$mapId['diagnóstico'],'comentario'=>__('ticket.tech.action.startDiagnosis')];
            elseif (isset($mapId['diagnostico'])) $acciones[] = ['label'=>__('ticket.tech.action.startDiagnosis'),'estado_id'=>$mapId['diagnostico'],'comentario'=>__('ticket.tech.action.startDiagnosis')];
            $mensajeKey = 'ticket.tech.msg.wait';
        } elseif ($s === 'diagnóstico' || $s === 'diagnostico') {
            if (isset($mapId['presupuesto'])) $acciones[] = ['label'=>__('ticket.tech.action.finishDiagnosis'),'estado_id'=>$mapId['presupuesto'],'comentario'=>__('ticket.tech.action.finishDiagnosis')];
            $mensajeKey = 'ticket.tech.msg.diagnosis';
        } elseif ($s === 'presupuesto') {
            $mensajeKey = 'ticket.tech.msg.budget';
        } elseif ($s === 'en reparación' || $s === 'en reparacion') {
            if (isset($mapId['en pruebas'])) $acciones[] = ['label'=>__('ticket.tech.action.repairFinished'),'estado_id'=>$mapId['en pruebas'],'comentario'=>__('ticket.tech.action.repairFinished')];
            $mensajeKey = 'ticket.tech.msg.repair';
        } elseif ($s === 'en pruebas') {
            if (isset($mapId['listo para retirar'])) $acciones[] = ['label'=>__('ticket.tech.action.testsFinished'),'estado_id'=>$mapId['listo para retirar'],'comentario'=>__('ticket.tech.action.testsFinished')];
            $mensajeKey = 'ticket.tech.msg.tests';
        } elseif ($s === 'listo para retirar') {
            $mensajeKey = 'ticket.tech.msg.ready';
        } elseif ($s === 'finalizado' || $s === 'cancelado') {
            $mensajeKey = 'ticket.tech.msg.closed';
        }

        $mensaje = $mensajeKey ? __($mensajeKey) : '';
        return [$acciones, $mensaje];
    }

    public function verTicket($id)
    {
        
        $rolesPermitidos = ['Supervisor', 'Tecnico', 'Cliente', 'Administrador'];
        $user = Auth::user();
        if (!$user || !in_array($user['role'] ?? '', $rolesPermitidos, true)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        $ticket = $this->ticketModel->ver((int)$id);
        if (!$ticket) {
            
            $view = [
                'ticket' => null,
                'rol' => $user['role'] ?? '',
                'flash' => $_GET ?? [],
                'volverUrl' => '/ProyectoPandora/Public/index.php?route=Default/Index',
                'timeline' => ['Tecnico'=>[], 'Cliente'=>[], 'Supervisor'=>[]],
            ];
            require __DIR__ . '/../Views/Ticket/VerTicket.php';
            return;
        }

        
        $estadoStr = $ticket['estado'] ?? $ticket['estado_actual'] ?? 'No disponible';
        $estadoLower = strtolower(trim($estadoStr));
        $estadoClass = $this->badgeClassFor($estadoLower);
        $finalizado = in_array($estadoLower, ['finalizado','cerrado'], true);

        
        $rol = $user['role'] ?? '';
    if ($rol === 'Cliente') $volverUrl = "/ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo";
        elseif ($rol === 'Tecnico') $volverUrl = "/ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones";
        elseif ($rol === 'Supervisor') $volverUrl = "/ProyectoPandora/Public/index.php?route=Supervisor/Asignar";
        elseif ($rol === 'Administrador') $volverUrl = "/ProyectoPandora/Public/index.php?route=Admin/ListarUsers";
        else $volverUrl = "/ProyectoPandora/Public/index.php?route=Default/Index";
    $prev = $_SESSION['prev_url'] ?? '';
    
    if ($prev) {
        $lower = strtolower($prev);
        if (strpos($lower, 'route=notification/count') !== false ||
            strpos($lower, 'route=notification/markread') !== false ||
            strpos($lower, 'route=ticket/ver') !== false) {
            $prev = '';
        }
    }
    $backHref = $prev ?: $volverUrl;

        
        $enPresu = ($estadoLower === 'presupuesto' || $estadoLower === 'en espera');

        
        require_once __DIR__ . '/../Models/ItemTicket.php';
        require_once __DIR__ . '/../Models/TicketLabor.php';
        $dbx = new Database();
        $dbx->connectDatabase();
        $conn = $dbx->getConnection();
        $itemM = new ItemTicketModel($conn);
        $laborM = new TicketLaborModel($conn);
        $items = $itemM->listarPorTicket((int)$ticket['id']);
        $subtotalItems = 0.0;
        foreach ($items as $it) { $subtotalItems += (float)($it['valor_total'] ?? 0); }
        $laborRow = (array)$laborM->getByTicket((int)$ticket['id']);
        $laborAmount = (float)($laborRow['labor_amount'] ?? 0);
        $presuTotal = $subtotalItems + $laborAmount;

        
        $itemsFmt = [];
        foreach ($items as $it) {
            $it['valor_total_fmt'] = LogFormatter::monto((float)($it['valor_total'] ?? 0));
            $itemsFmt[] = $it;
        }

        
        $estadosAll = $this->estadoModel->getAllEstados();
        [$tecAcciones, $tecMensaje] = $this->buildTecnicoAcciones($estadoStr, $estadosAll);
        
        
        if ($estadoLower === 'en espera') {
            $hasItemsTech = !empty($items);
            $hasLaborTech = $laborAmount > 0;
            if ($hasItemsTech && $hasLaborTech) {
                $tecAcciones = [];
                $tecMensaje = __('ticket.tech.msg.diagnosisFinishedEditable');
            }
        }

        
        $stmtT2 = $conn->prepare("SELECT tc.id AS tecnico_id, ts.labor_min, ts.labor_max 
                                  FROM tickets t 
                                  LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id 
                                  LEFT JOIN tecnico_stats ts ON ts.tecnico_id = tc.id 
                                  WHERE t.id = ? LIMIT 1");
        $laborMin = 0.0; $laborMax = 0.0;
        if ($stmtT2) {
            $tid = (int)$ticket['id'];
            $stmtT2->bind_param('i', $tid);
            $stmtT2->execute();
            $rowS = $stmtT2->get_result()->fetch_assoc();
            if ($rowS) {
                $laborMin = (float)($rowS['labor_min'] ?? 0);
                $laborMax = (float)($rowS['labor_max'] ?? 0);
            }
        }
    $hasItemsTech = !empty($items);
    $hasLaborTech = $laborAmount > 0;
        $readyPresupuesto = $hasItemsTech && $hasLaborTech;
        $laborEditable = (($estadoLower === 'diagnóstico' || $estadoLower === 'diagnostico') && !$hasLaborTech);
    
    $laborEditableEnEspera = ($estadoLower === 'en espera' && $readyPresupuesto);

        
        $supervisorPuedeMarcarListo = in_array($estadoLower, ['en reparación','en reparacion','en pruebas'], true);
        $supervisorPuedeFinalizar = ($estadoLower === 'listo para retirar');

        
        require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
        $th = new TicketEstadoHistorialModel($conn);
        $events = $th->listByTicket((int)$ticket['id']);
        $timeline = ['Tecnico'=>[], 'Cliente'=>[], 'Supervisor'=>[]];
        foreach ($events as $ev) {
            
            $ev['fecha_exact'] = DateHelper::exact($ev['created_at'] ?? '');
            $ev['fecha_human'] = DateHelper::smart($ev['created_at'] ?? '');
            $evEstadoLower = strtolower(trim($ev['estado'] ?? ''));
            $ev['badge_class'] = $this->badgeClassFor($evEstadoLower);
            $r = $ev['user_role'] ?? '';
            
            if ($r === 'Administrador') { $r = 'Supervisor'; }
            if (isset($timeline[$r])) { $timeline[$r][] = $ev; }
        }

        
        $flash = $_GET ?? [];

        
        $fotos = [];
        $allowed = ['jpg','jpeg','png','gif','webp'];
        try {
            
            $relDir = 'ticket/' . (int)$ticket['id'];
            $absDir = \Storage::basePath() . '/' . $relDir . '/';
            if (is_dir($absDir)) {
                $files = @scandir($absDir) ?: [];
                foreach ($files as $fn) {
                    if ($fn === '.' || $fn === '..') continue;
                    $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed, true)) continue;
                    $rel = $relDir . '/' . $fn;
                    $fotos[] = \Storage::publicUrl($rel);
                }
            }
            
            if (empty($fotos)) {
                $legacyDir = __DIR__ . '/../Public/img/imgTickets/' . (int)$ticket['id'] . '/';
                $legacyUrlBase = '/ProyectoPandora/Public/img/imgTickets/' . (int)$ticket['id'] . '/';
                if (is_dir($legacyDir)) {
                    $files = @scandir($legacyDir) ?: [];
                    foreach ($files as $fn) {
                        if ($fn === '.' || $fn === '..') continue;
                        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed, true)) continue;
                        $fotos[] = $legacyUrlBase . rawurlencode($fn);
                    }
                }
            }
        } catch (\Throwable $e) {  }

        
        $revState = md5($estadoLower.'|'.(string)$laborAmount.'|'.(string)count($items));

        $view = [
            'ticket' => $ticket,
            'estadoStr' => $estadoStr,
            'estadoClass' => $estadoClass,
            'rol' => $rol,
            'finalizado' => $finalizado,
            'backHref' => $backHref,
            'flash' => $flash,
            'fotos_ticket' => $fotos,
            'rev_state' => $revState,

            'enPresu' => $enPresu,
            'presupuesto' => [
                'items' => $itemsFmt,
                'subtotal' => $subtotalItems,
                'mano_obra' => $laborAmount,
                'total' => $presuTotal,
                
                'subtotal_fmt' => LogFormatter::monto((float)$subtotalItems),
                'mano_obra_fmt' => LogFormatter::monto((float)$laborAmount),
                'total_fmt' => LogFormatter::monto((float)$presuTotal),
            ],

            'tecnico' => [
                'acciones' => $tecAcciones,
                'mensaje' => $tecMensaje,
                'labor_min' => $laborMin,
                'labor_max' => $laborMax,
                'labor_min_fmt' => LogFormatter::monto((float)$laborMin),
                'labor_max_fmt' => LogFormatter::monto((float)$laborMax),
                'has_items' => $hasItemsTech,
                'has_labor' => $hasLaborTech,
                'labor_editable' => $laborEditable,
                'labor_editable_en_espera' => $laborEditableEnEspera,
                'estado_lower' => $estadoLower,
            ],

            'supervisor' => [
                'puede_listo' => $supervisorPuedeMarcarListo,
                'puede_finalizar' => $supervisorPuedeFinalizar,
            ],

            'timeline' => $timeline,
        ];

        
        $mostrarPagadoOverlay = false;
        $debeCalificar = false;
        if (((string)($flash['ok'] ?? '') === 'pagado') || $finalizado) {
            require_once __DIR__ . '/../Models/Rating.php';
            $rt = (new RatingModel($conn))->getByTicket((int)$ticket['id']);
            $mostrarPagadoOverlay = !empty($rt) && (int)($rt['stars'] ?? 0) > 0;
            if (!$mostrarPagadoOverlay && $finalizado && $rol === 'Cliente') {
                $debeCalificar = true;
            }
        }

        $view['mostrarPagadoOverlay'] = $mostrarPagadoOverlay;
        $view['debeCalificar'] = $debeCalificar;

        require __DIR__ . '/../Views/Ticket/VerTicket.php';
    }

    public function ActualizarEstado() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Tecnico') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $estado_id = (int)($_POST['estado_id'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');

        $tk = $this->ticketModel->ver($ticket_id);
        if (!$tk) { header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones&error=tk'); exit; }

        
        $estadoActual = $tk['estado'] ?? '';
        $nuevo = $this->estadoModel->getById($estado_id);
        if (!$nuevo) { header('Location: /ProyectoPandora/Public/index.php?route=Tecnico/MisReparaciones&error=estado'); exit; }
        $estadoNuevo = $nuevo['name'];

        
        if (!$this->puedeTransicionar($estadoActual, $estadoNuevo) || strcasecmp($estadoNuevo, 'Finalizado')===0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&error=transicion');
            exit;
        }

        
        if (strcasecmp($estadoNuevo, 'En reparación') === 0) {
            
            
            require_once __DIR__ . '/../Models/TicketLabor.php';
            require_once __DIR__ . '/../Models/ItemTicket.php';
            $db = new Database(); $db->connectDatabase();
            $laborModel = new TicketLaborModel($db->getConnection());
            $itemModel = new ItemTicketModel($db->getConnection());
            $items = $itemModel->listarPorTicket($ticket_id);
            $hasItems = !empty($items);
            $labor = $laborModel->getByTicket($ticket_id);
            $mano = (float)($labor['labor_amount'] ?? 0);
            if (!$hasItems || $mano <= 0) {
                header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&error=presupuesto');
                exit;
            }
            
            $aprobado = false;
            $hist = $this->histEstadoModel->listByTicket($ticket_id);
            foreach ($hist as $ev) {
                if ($ev['user_role']==='Cliente' && stripos($ev['comentario'] ?? '', 'aprob') !== false) { $aprobado = true; break; }
            }
            if (!$aprobado) {
                header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&error=aprobacion');
                exit;
            }
        }

        
        
        $conn = (new Database());
        $conn->connectDatabase();
        $dbConn = $conn->getConnection();
        $stmt = $dbConn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $estado_id, $ticket_id);
        $stmt->execute();

        
        $this->histEstadoModel->add($ticket_id, $estado_id, (int)$user['id'], $user['role'], $comentario);

        
        try {
            $actor = $user['name'] ?? ('Usuario ID '.(int)$user['id']);
            $desde = trim((string)$estadoActual);
            $hacia = trim((string)$estadoNuevo);
            $accion = 'Cambio de estado de ticket';
            $detalle = $actor . " movió el ticket #{$ticket_id} de '" . $desde . "' a '" . $hacia . "'.";
            if ($comentario) { $detalle .= " Comentario: " . trim($comentario); }
            $this->historialController->agregarAccion($accion, $detalle);
        } catch (\Throwable $e) {  }

        
        try {
            $dbn = new Database(); $dbn->connectDatabase(); $cnn = $dbn->getConnection();
            $stmtC = $cnn->prepare("SELECT u.id AS user_id FROM tickets t INNER JOIN clientes c ON t.cliente_id=c.id INNER JOIN users u ON u.id=c.user_id WHERE t.id=? LIMIT 1");
            if ($stmtC) {
                $stmtC->bind_param('i', $ticket_id);
                $stmtC->execute();
                $uidRow = $stmtC->get_result()->fetch_assoc();
                if ($uidRow && isset($uidRow['user_id'])) {
                    I18n::boot();
                    $nm = new NotificationModel($cnn);
                    $title = __('notification.ticket.stateUpdated.title');
                    $body  = __('notification.ticket.stateUpdated.body', ['id'=>$ticket_id, 'state'=>($estadoNuevo ?? '#')]);
                    $nm->create($title, $body, 'USER', null, (int)$uidRow['user_id'], (int)$user['id']);
                }
            }
        } catch (\Throwable $e) {  }

        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&ok=estado');
        exit;
    }

    
    public function AprobarPresupuesto() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? 'Aprobado presupuesto');

        $tk = $this->ticketModel->ver($ticket_id);
    if (!$tk) { header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=ticket'); exit; }

        
        $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
        $stmtC = $conn->prepare("SELECT id FROM clientes WHERE user_id = ? LIMIT 1");
        $stmtC->bind_param("i", $user['id']);
        $stmtC->execute();
        $cliente = $stmtC->get_result()->fetch_assoc();
        $stmtT = $conn->prepare("SELECT cliente_id FROM tickets WHERE id = ? LIMIT 1");
        $stmtT->bind_param("i", $ticket_id);
        $stmtT->execute();
        $rowT = $stmtT->get_result()->fetch_assoc();
        if (!$cliente || (int)($rowT['cliente_id'] ?? 0) !== (int)$cliente['id']) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=forbidden');
            exit;
        }

        
        $estadoActual = strtolower(trim($tk['estado'] ?? ''));
        if ($estadoActual !== 'presupuesto') {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&error=estado_actual');
            exit;
        }

    
    $estadoId = $this->estadoIdPorNombre('En reparación');
        if ($estadoId) {
            $stmtU = $conn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
            $stmtU->bind_param("ii", $estadoId, $ticket_id);
            $stmtU->execute();
        } else {
            
            $estadoId = $this->estadoIdPorNombre('Presupuesto');
        }

        $this->histEstadoModel->add($ticket_id, (int)$estadoId, (int)$user['id'], 'Cliente', $comentario);

        
        try {
            require_once __DIR__ . '/../Models/ItemTicket.php';
            require_once __DIR__ . '/../Models/TicketLabor.php';
            $itemM = new ItemTicketModel($conn);
            $laborM = new TicketLaborModel($conn);
            $items = $itemM->listarPorTicket($ticket_id);
            $subtotal = 0.0; foreach ($items as $it) { $subtotal += (float)($it['valor_total'] ?? 0); }
            $labor = $laborM->getByTicket($ticket_id); $mano = (float)($labor['labor_amount'] ?? 0);
            $total = $subtotal + $mano;
            $montoTxt = $total>0 ? LogFormatter::monto((float)$total) : null;
            $accion = 'Rechazo de presupuesto';
            $detalle = $user['name'] . " rechazó el presupuesto del ticket #{$ticket_id}" . ($montoTxt?" por {$montoTxt}":'') . ".";
            if ($comentario) { $detalle .= " Motivo/comentario: " . trim($comentario); }
            $this->historialController->agregarAccion($accion, $detalle);
        } catch (\Throwable $e) {  }

        
        try {
            require_once __DIR__ . '/../Models/ItemTicket.php';
            require_once __DIR__ . '/../Models/TicketLabor.php';
            $itemM = new ItemTicketModel($conn);
            $laborM = new TicketLaborModel($conn);
            $items = $itemM->listarPorTicket($ticket_id);
            $subtotal = 0.0; foreach ($items as $it) { $subtotal += (float)($it['valor_total'] ?? 0); }
            $labor = $laborM->getByTicket($ticket_id); $mano = (float)($labor['labor_amount'] ?? 0);
            $total = $subtotal + $mano;
            $tecNombre = null;
            if ($stN = $conn->prepare("SELECT ut.name AS tec FROM tickets t LEFT JOIN tecnicos tc ON t.tecnico_id=tc.id LEFT JOIN users ut ON ut.id=tc.user_id WHERE t.id=? LIMIT 1")) {
                $stN->bind_param('i', $ticket_id); $stN->execute();
                $tecNombre = $stN->get_result()->fetch_assoc()['tec'] ?? null;
            }
            $accion = 'Aprobación de presupuesto';
            $montoTxt = LogFormatter::monto((float)$total);
            $detalle = $user['name'] . " aprobó el presupuesto del ticket #{$ticket_id} por {$montoTxt}.";
            if ($tecNombre) { $detalle .= " Técnico asignado: {$tecNombre}."; }
            if ($comentario) { $detalle .= " Comentario: " . trim($comentario); }
            $this->historialController->agregarAccion($accion, $detalle);
        } catch (\Throwable $e) {  }
        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&ok=aprobado');
        exit;
    }

    
    public function MarcarListoParaRetirar() {
        $user = Auth::user();
        if (!$user || ($user['role'] ?? '') !== 'Supervisor') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        if ($ticket_id <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar&error=ticket');
            exit;
        }
        $estadoId = $this->estadoIdPorNombre('Listo para retirar');
        if ($estadoId) {
            $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
            $stmtU = $conn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
            $stmtU->bind_param("ii", $estadoId, $ticket_id);
            $stmtU->execute();

            
            $this->histEstadoModel->add($ticket_id, (int)$estadoId, (int)$user['id'], 'Supervisor', 'Marcado como listo para retirar');

            
            try {
                $cliNombre = null; $tecNombre = null;
                if ($stC = $conn->prepare("SELECT uc.name AS cliente FROM tickets t INNER JOIN clientes c ON t.cliente_id=c.id INNER JOIN users uc ON uc.id=c.user_id WHERE t.id=? LIMIT 1")) {
                    $stC->bind_param('i', $ticket_id); $stC->execute();
                    $cliNombre = $stC->get_result()->fetch_assoc()['cliente'] ?? null;
                }
                if ($stT = $conn->prepare("SELECT ut.name AS tec FROM tickets t LEFT JOIN tecnicos tc ON t.tecnico_id=tc.id LEFT JOIN users ut ON ut.id=tc.user_id WHERE t.id=? LIMIT 1")) {
                    $stT->bind_param('i', $ticket_id); $stT->execute();
                    $tecNombre = $stT->get_result()->fetch_assoc()['tec'] ?? null;
                }
                $accion = 'Listo para retirar';
                $detalle = $user['name'] . " marcó el ticket #{$ticket_id} como 'Listo para retirar'";
                if ($cliNombre) { $detalle .= ", cliente: {$cliNombre}"; }
                if ($tecNombre) { $detalle .= ", técnico asignado: {$tecNombre}"; }
                $detalle .= '.';
                $this->historialController->agregarAccion($accion, $detalle);
            } catch (\Throwable $e) {  }

        }
        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id=' . $ticket_id . '&ok=listo');
        exit;
    }

    
    public function MarcarPagadoYFinalizar() {
        $user = Auth::user();
        if (!$user || ($user['role'] ?? '') !== 'Supervisor') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        if ($ticket_id <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Asignar&error=ticket');
            exit;
        }
        
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
        $method = strtoupper(trim($_POST['method'] ?? 'EFECTIVO'));
        $reference = trim($_POST['reference'] ?? '');
        
        require_once __DIR__ . '/../Models/TicketEstadoHistorial.php';
        $dbA = new Database(); $dbA->connectDatabase(); $histM = new TicketEstadoHistorialModel($dbA->getConnection());
        $aprobado = false; foreach ($histM->listByTicket($ticket_id) as $ev) { if ($ev['user_role']==='Cliente' && stripos($ev['comentario'] ?? '', 'aprob') !== false) { $aprobado = true; break; } }
        if (!$aprobado) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id=' . $ticket_id . '&error=aprobacion');
            exit;
        }
        
        $estadoListo = $this->estadoIdPorNombre('Listo para retirar');
        $estadoFinal = $this->estadoIdPorNombre('Finalizado');
        $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
        if ($estadoListo && $estadoFinal) {
            
            $stmtC = $conn->prepare("SELECT e.name as estado FROM tickets t INNER JOIN estados_tickets e ON e.id=t.estado_id WHERE t.id=? LIMIT 1");
            $stmtC->bind_param('i', $ticket_id);
            $stmtC->execute();
            $row = $stmtC->get_result()->fetch_assoc();
            $estadoActual = strtolower(trim($row['estado'] ?? ''));
            if ($estadoActual !== 'listo para retirar') {
                $stmtU1 = $conn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
                $stmtU1->bind_param('ii', $estadoListo, $ticket_id);
                $stmtU1->execute();

                
                $this->histEstadoModel->add($ticket_id, (int)$estadoListo, (int)$user['id'], 'Supervisor', 'Marcado como listo para retirar previo a pago');
            }
            
            $stmtU2 = $conn->prepare("UPDATE tickets SET estado_id = ?, fecha_cierre = NOW() WHERE id = ?");
            $stmtU2->bind_param('ii', $estadoFinal, $ticket_id);
            $stmtU2->execute();

            
            $this->histEstadoModel->add($ticket_id, (int)$estadoFinal, (int)$user['id'], 'Supervisor', 'Pago registrado y ticket finalizado');

            
            require_once __DIR__ . '/../Models/ItemTicket.php';
            require_once __DIR__ . '/../Models/TicketLabor.php';
            require_once __DIR__ . '/../Models/Pago.php';
            $itemModel = new ItemTicketModel($conn);
            $laborModel = new TicketLaborModel($conn);
            $items = $itemModel->listarPorTicket($ticket_id); $subtotal = 0.0; foreach($items as $it){ $subtotal += (float)($it['valor_total'] ?? 0); }
            $labor = $laborModel->getByTicket($ticket_id); $mano = (float)($labor['labor_amount'] ?? 0);
            $totalCalc = $subtotal + $mano;
            if ($amount === null || $amount <= 0) { $amount = $totalCalc; }
            $metodosValidos = ['EFECTIVO','TARJETA','TRANSFERENCIA','OTRO'];
            if (!in_array($method, $metodosValidos, true)) { $method = 'EFECTIVO'; }
            $pago = new PagoModel($conn);
            $pago->add($ticket_id, (float)$amount, $method, $reference, (int)$user['id']);

            
            try {
                $cliNombre = null; $tecNombre = null;
                if ($stC = $conn->prepare("SELECT uc.name AS cliente FROM tickets t INNER JOIN clientes c ON t.cliente_id=c.id INNER JOIN users uc ON uc.id=c.user_id WHERE t.id=? LIMIT 1")) {
                    $stC->bind_param('i', $ticket_id); $stC->execute();
                    $cliNombre = $stC->get_result()->fetch_assoc()['cliente'] ?? null;
                }
                if ($stT = $conn->prepare("SELECT ut.name AS tec FROM tickets t LEFT JOIN tecnicos tc ON t.tecnico_id=tc.id LEFT JOIN users ut ON ut.id=tc.user_id WHERE t.id=? LIMIT 1")) {
                    $stT->bind_param('i', $ticket_id); $stT->execute();
                    $tecNombre = $stT->get_result()->fetch_assoc()['tec'] ?? null;
                }
                $accion = 'Pago y cierre de ticket';
                $montoTxt = LogFormatter::monto((float)$amount);
                $detalle = $user['name'] . " registró un pago de {$montoTxt} (método: {$method}" . ($reference?", ref: {$reference}":'') . ") y finalizó el ticket #{$ticket_id}.";
                if ($cliNombre) { $detalle .= " Cliente: {$cliNombre}."; }
                if ($tecNombre) { $detalle .= " Técnico: {$tecNombre}."; }
                $this->historialController->agregarAccion($accion, $detalle);
            } catch (\Throwable $e) {  }
        }
        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id=' . $ticket_id . '&ok=pagado');
        exit;
    }

    
    public function RechazarPresupuesto() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? 'Rechazado presupuesto');

        $tk = $this->ticketModel->ver($ticket_id);
    if (!$tk) { header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=ticket'); exit; }

        
        $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
        $stmtC = $conn->prepare("SELECT id FROM clientes WHERE user_id = ? LIMIT 1");
        $stmtC->bind_param("i", $user['id']);
        $stmtC->execute();
        $cliente = $stmtC->get_result()->fetch_assoc();
    $stmtT = $conn->prepare("SELECT cliente_id FROM tickets WHERE id = ? LIMIT 1");
    $stmtT->bind_param("i", $ticket_id);
    $stmtT->execute();
    $rowT = $stmtT->get_result()->fetch_assoc();
        if (!$cliente || (int)($rowT['cliente_id'] ?? 0) !== (int)$cliente['id']) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=forbidden');
            exit;
        }

        
        $estadoActual = strtolower(trim($tk['estado'] ?? ''));
        if ($estadoActual !== 'presupuesto') {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&error=estado_actual');
            exit;
        }

        
        $estadoId = $this->estadoIdPorNombre('Cancelado') ?? $this->estadoIdPorNombre('Finalizado');
        if ($estadoId) {
            $stmtU = $conn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
            $stmtU->bind_param("ii", $estadoId, $ticket_id);
            $stmtU->execute();
        }
        $this->histEstadoModel->add($ticket_id, (int)$estadoId, (int)$user['id'], 'Cliente', $comentario);
        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id='.$ticket_id.'&ok=rechazado');
        exit;
    }

    public function mostrarLista()
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
        $tickets = $this->ticketModel->listar();
        $data = [];
        while ($row = $tickets->fetch_assoc()) {
            $data[] = $row;
        }
        include __DIR__ . '/../Views/Ticket/ListarTickets.php';
    }

    public function Calificar() {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $stars = max(1, min(5, (int)($_POST['stars'] ?? 0)));
        $comment = trim($_POST['comment'] ?? '');

        $db = new Database();
        $db->connectDatabase();
        $ticketModel = new Ticket($db->getConnection());
        $ratingModel = new RatingModel($db->getConnection());

        $tk = $ticketModel->ver($ticket_id);
    if (!$tk) { header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=ticket'); exit; }

        
        $estadoTxt = strtolower(trim($tk['estado'] ?? ''));
        if (!in_array($estadoTxt, ['finalizado', 'cerrado'], true)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id=' . $ticket_id . '&error=estado');
            exit;
        }

        
        $conn = $db->getConnection();
        $stmtC = $conn->prepare("SELECT id FROM clientes WHERE user_id = ? LIMIT 1");
        $stmtC->bind_param("i", $user['id']);
        $stmtC->execute();
        $cliente = $stmtC->get_result()->fetch_assoc();
        
        $stmtT = $conn->prepare("SELECT t.tecnico_id, t.cliente_id FROM tickets t WHERE t.id = ? LIMIT 1");
        $stmtT->bind_param("i", $ticket_id);
        $stmtT->execute();
        $rowT = $stmtT->get_result()->fetch_assoc();
        $tecnico_id = $rowT['tecnico_id'] ?? null;
        $owner_cliente_id = $rowT['cliente_id'] ?? null;

        
        if (!$cliente || !$tecnico_id || (int)$owner_cliente_id !== (int)$cliente['id']) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=forbidden');
            exit;
        }

        $ratingModel->save($ticket_id, (int)$tecnico_id, (int)$cliente['id'], $stars, $comment);
        header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Ver&id=' . $ticket_id . '&rated=1');
        exit;
    }

    
    public function PublicarPresupuesto() {
        $user = Auth::user();
        if (!$user || ($user['role'] ?? '') !== 'Supervisor') {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos');
            exit;
        }
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        if ($ticket_id <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=ticket');
            exit;
        }

        
        require_once __DIR__ . '/../Models/ItemTicket.php';
        require_once __DIR__ . '/../Models/TicketLabor.php';
        $db = new Database(); $db->connectDatabase(); $conn = $db->getConnection();
        $itemModel = new ItemTicketModel($conn);
        $laborModel = new TicketLaborModel($conn);
        $items = $itemModel->listarPorTicket($ticket_id);
        $subtotal = 0.0;
        foreach ($items as $it) { $subtotal += (float)($it['valor_total'] ?? 0); }
        $labor = $laborModel->getByTicket($ticket_id);
        $mano = (float)($labor['labor_amount'] ?? 0);
        
        if (empty($items) || $mano <= 0) {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&error=presupuesto_incompleto');
            exit;
        }
        $total = $subtotal + $mano;

        
        $estadoId = $this->estadoIdPorNombre('Presupuesto');
        if ($estadoId) {
            $stmtU = $conn->prepare("UPDATE tickets SET estado_id = ? WHERE id = ?");
            $stmtU->bind_param("ii", $estadoId, $ticket_id);
            $stmtU->execute();
        } else {
            $estadoId = $this->estadoIdPorNombre('En espera') ?? 0; 
        }

        
    $comentario = 'Presupuesto publicado. Total ' . LogFormatter::monto((float)$total);
        $this->histEstadoModel->add($ticket_id, (int)$estadoId, (int)$user['id'], 'Supervisor', $comentario);

        
        try {
            $actor = $user['name'] ?? ('Usuario ID '.(int)$user['id']);
            $accion = 'Publicación de presupuesto';
            $detalle = $actor . " publicó el presupuesto del ticket #{$ticket_id}: "
                . count($items) . " ítem(s) por " . LogFormatter::monto((float)$subtotal)
                . ", mano de obra " . LogFormatter::monto((float)$mano)
                . ", total " . LogFormatter::monto((float)$total) . ".";
            $this->historialController->agregarAccion($accion, $detalle);
        } catch (\Throwable $e) {  }

        
        try {
            $stmtC = $conn->prepare("SELECT u.id AS user_id FROM tickets t INNER JOIN clientes c ON t.cliente_id=c.id INNER JOIN users u ON u.id=c.user_id WHERE t.id=? LIMIT 1");
            if ($stmtC) {
                $stmtC->bind_param('i', $ticket_id);
                $stmtC->execute();
                $uidRow = $stmtC->get_result()->fetch_assoc();
                if ($uidRow && isset($uidRow['user_id'])) {
                    I18n::boot();
                    $nm = new NotificationModel($conn);
                    $title = __('notification.ticket.budgetPublished.title');
                    $body  = __('notification.ticket.budgetPublished.body', ['id'=>$ticket_id, 'total'=>LogFormatter::monto((float)$total)]);
                    $nm->create($title, $body, 'USER', null, (int)$uidRow['user_id'], (int)$user['id']);
                }
            }
        } catch (\Throwable $e) {  }

        

        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/Presupuestos&ok=publicado');
        exit;
    }

    public function edit($id)
    {
        $user = Auth::user();
        $rol = $user['role'];
        if ($rol === 'Administrador') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }
        $ticket = $this->ticketModel->ver($id);
        $estados = $this->estadoModel->getAllEstados();
        $tecnicos = $this->userModel->getAllTecnicos();

        
        $fotos = [];
        $allowed = ['jpg','jpeg','png','gif','webp'];
        try {
            $relDir = 'ticket/' . (int)$ticket['id'];
            $absDir = \Storage::basePath() . '/' . $relDir . '/';
            if (is_dir($absDir)) {
                $files = @scandir($absDir) ?: [];
                foreach ($files as $fn) {
                    if ($fn === '.' || $fn === '..') continue;
                    $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed, true)) continue;
                    $rel = $relDir . '/' . $fn;
                    $fotos[] = \Storage::publicUrl($rel);
                }
            }
            if (empty($fotos)) {
                $legacyDir = __DIR__ . '/../Public/img/imgTickets/' . (int)$ticket['id'] . '/';
                $legacyUrlBase = '/ProyectoPandora/Public/img/imgTickets/' . (int)$ticket['id'] . '/';
                if (is_dir($legacyDir)) {
                    $files = @scandir($legacyDir) ?: [];
                    foreach ($files as $fn) {
                        if ($fn === '.' || $fn === '..') continue;
                        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed, true)) continue;
                        $fotos[] = $legacyUrlBase . rawurlencode($fn);
                    }
                }
            }
        } catch (\Throwable $e) {  }

        include_once __DIR__ . '/../Views/Ticket/ActualizarTicket.php';
    }

    public function actualizar()
    {
        $user = Auth::user();
        $rol = $user['role'];
        $id = $_POST['id'];
        $descripcion = $_POST['descripcion_falla'];

        $estado_id = $_POST['estado_id'] ?? null;
        $tecnico_id = $_POST['tecnico_id'] ?? null;
        
        if ($estado_id === '' || $estado_id === null) {
            $estado_id = null;
        } else {
            $estado_id = (int)$estado_id;
        }
        if ($tecnico_id === '' || $tecnico_id === null) {
            $tecnico_id = null;
        } else {
            $tecnico_id = (int)$tecnico_id;
        }

        
        $ticketActual = $this->ticketModel->ver($id);
        $old_tecnico_id = $ticketActual['tecnico_id'] ?? null;

        
        if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $destBase = \Storage::ensure('ticket/' . (int)$id);
            $count = count($_FILES['fotos']['name']);
            for ($i=0; $i < $count; $i++) {
                $name = $_FILES['fotos']['name'][$i] ?? '';
                $tmp  = $_FILES['fotos']['tmp_name'][$i] ?? '';
                $err  = $_FILES['fotos']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($err !== UPLOAD_ERR_OK || !$tmp) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) continue;
                $safe = preg_replace('/[^a-zA-Z0-9_\.-]/','_', basename($name));
                $target = rtrim($destBase, '/\\') . '/' . (time()) . '_' . $safe;
                @move_uploaded_file($tmp, $target);
            }
        }

        $this->ticketModel->actualizarCompleto($id, $descripcion, $estado_id, $tecnico_id);

        

        
        if ($rol === 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo');
        } else {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Listar');
        }
        exit;
    }

    public function mostrarCrear()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if (($user['role'] ?? '') !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }

        
        $cliente = $this->ticketModel->obtenerClientePorUser($user['id']);
        if (!$cliente || !isset($cliente['id'])) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=cliente_no_asociado');
            exit;
        }
    $cliente_id = (int)($cliente['id']);

        $data = [];
        $dispositivos = $this->ticketModel->obtenerDispositivosPorCliente($cliente_id);
        while ($row = $dispositivos->fetch_assoc()) {
            
            $row['hasActive'] = $this->ticketModel->hasActiveTicketForDevice((int)$row['id']);
            $data[] = $row;
        }

        
        

        
        if (empty($data)) {
            
            $errorCode = 'noDevices';
            include __DIR__ . '/../Views/Ticket/CrearTicket.php';
            return;
        }

        include __DIR__ . '/../Views/Ticket/CrearTicket.php';
    }

    public function crear()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        if (($user['role'] ?? '') !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }

        
        $cliente = $this->ticketModel->obtenerClientePorUser($user['id']);
        if (!$cliente || !isset($cliente['id'])) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=cliente_no_asociado');
            exit;
        }
        $cliente_id = (int)($cliente['id']);

    $dispositivo_id = $_POST['dispositivo_id'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (empty($dispositivo_id)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/mostrarCrear&error=deviceRequired');
            exit;
        }

        
        $pertenece = false;
        $dispRes = $this->ticketModel->obtenerDispositivosPorCliente($cliente_id);
        while ($r = $dispRes->fetch_assoc()) {
            if ((int)($r['id'] ?? 0) === (int)$dispositivo_id) { $pertenece = true; break; }
        }
        if (!$pertenece) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/mostrarCrear&error=deviceOwnership');
            exit;
        }

        
        
        if ($this->ticketModel->hasActiveTicketForDevice((int)$dispositivo_id)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/mostrarCrear&error=deviceActive');
            exit;
        }

    $nuevoId = $this->ticketModel->crear($cliente_id, $dispositivo_id, $descripcion);
        if ($nuevoId) {
            
            $eid = $this->estadoIdPorNombre('Nuevo');
            if ($eid) {
                $this->histEstadoModel->add((int)$nuevoId, (int)$eid, (int)$user['id'], $user['role'], 'Ticket creado');
            }
        }

        
        
        $accion = "Creación de ticket";
        try {
            $dbd = new Database(); $dbd->connectDatabase(); $cnd = $dbd->getConnection();
            $stmtD = $cnd->prepare("SELECT d.marca, d.modelo, c.name AS categoria FROM dispositivos d LEFT JOIN categorias c ON c.id = d.categoria_id WHERE d.id = ? LIMIT 1");
            $stmtD && $stmtD->bind_param('i', $dispositivo_id) && $stmtD->execute();
            $rowD = $stmtD ? $stmtD->get_result()->fetch_assoc() : null;
            $dispDesc = $rowD ? (trim(($rowD['marca']??'')." ".($rowD['modelo']??'')) ?: 'dispositivo') : 'dispositivo';
            $cat = $rowD['categoria'] ?? '';
            $detalle = "{$user['name']} creó un ticket para {$dispDesc}" . ($cat?" (categoría {$cat})":"") . ". Descripción del problema: " . trim($descripcion);
        } catch (\Throwable $e) {
            $detalle = "{$user['name']} creó un ticket para el dispositivo ID {$dispositivo_id}. Descripción: " . trim($descripcion);
        }
        $this->historialController->agregarAccion($accion, $detalle);

        
        if ($user['role'] === 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicket');
        } else {
            header('Location: /ProyectoPandora/Public/index.php?route=Ticket/Listar');
        }
        exit;
    }

    public function eliminar()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header("Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=id");
            exit;
        }
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }
        
        
        if (($user['role'] ?? '') !== 'Cliente') {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
            exit;
        }

        
        $tk = $this->ticketModel->ver($id);
        if (!$tk) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=ticket');
            exit;
        }

        
        $db = new Database();
        $db->connectDatabase();
        $conn = $db->getConnection();
        $stmtC = $conn->prepare("SELECT id FROM clientes WHERE user_id = ? LIMIT 1");
        if ($stmtC) {
            $stmtC->bind_param('i', $user['id']);
            $stmtC->execute();
            $cliente = $stmtC->get_result()->fetch_assoc();
            
            $stmtT = $conn->prepare("SELECT cliente_id FROM tickets WHERE id = ? LIMIT 1");
            $stmtT->bind_param('i', $id);
            $stmtT->execute();
            $rowT = $stmtT->get_result()->fetch_assoc();
            if (!$cliente || (int)($rowT['cliente_id'] ?? 0) !== (int)$cliente['id']) {
                header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=forbidden');
                exit;
            }
        }

        $estadoTxt = strtolower(trim($tk['estado'] ?? $tk['estado_actual'] ?? ''));
        $allowed = ['nuevo','finalizado','cerrado'];
        if (!in_array($estadoTxt, $allowed, true)) {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=estado');
            exit;
        }

        if ($this->ticketModel->deleteTicket($id)) {
            $accion = "Eliminación de ticket";
            $detalle = "Usuario {$user['name']} eliminó el ticket ID {$id}";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&deleted=1');
        } else {
            header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo&error=delete');
        }
        exit;
    }

    

    
    
}
