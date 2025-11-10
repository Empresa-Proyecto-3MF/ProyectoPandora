<?php
class Ticket
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function crear($cliente_id, $dispositivo_id, $descripcion_falla)
    {
        
        $estadoInicialId = null;
        
        if ($st = $this->conn->prepare("SELECT id FROM estados_tickets WHERE LOWER(name) = LOWER('Nuevo') LIMIT 1")) {
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            if ($row && isset($row['id'])) { $estadoInicialId = (int)$row['id']; }
        }
        
        if (!$estadoInicialId) {
            if ($ins = $this->conn->prepare("INSERT INTO estados_tickets (name) VALUES ('Nuevo')")) {
                if ($ins->execute()) {
                    $estadoInicialId = (int)$this->conn->insert_id;
                }
            }
        }
        if (!$estadoInicialId) {
            
            return false;
        }

        $sql = "INSERT INTO tickets (cliente_id, dispositivo_id, descripcion_falla, estado_id) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { return false; }
        $stmt->bind_param("iisi", $cliente_id, $dispositivo_id, $descripcion_falla, $estadoInicialId);
        if ($stmt->execute()) {
            return (int)$this->conn->insert_id;
        }
        return false;
    }

    public function listar()
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    u.name AS cliente,
                    t.descripcion_falla AS descripcion,
                    e.name AS estado,
                    tec.name AS tecnico
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                ORDER BY t.id DESC";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function ver($id)
    {
        $sql = "SELECT 
                    t.id,
                    t.estado_id AS estado_id,
                    d.marca,
                    d.modelo,
                    d.img_dispositivo,
                    u.name AS cliente,
                    t.descripcion_falla AS descripcion,
                    e.name AS estado,
                    tec.name AS tecnico,
                    t.fecha_creacion,
                    t.fecha_cierre
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                WHERE t.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function actualizar($id, $descripcion_falla)
    {
        $sql = "UPDATE tickets SET descripcion_falla = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $descripcion_falla, $id);
        return $stmt->execute();
    }

    public function deleteTicket($ticketId)
    {
        $stmt = $this->conn->prepare("DELETE FROM tickets WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $ticketId);
            return $stmt->execute();
        }
        return false;
    }
    public function obtenerDispositivosPorCliente($cliente_id)
    {
        
        $sql = "SELECT d.id, d.marca, d.modelo, d.descripcion_falla 
                FROM dispositivos d
                INNER JOIN clientes c ON d.user_id = c.user_id
                WHERE c.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function obtenerClientePorUser($user_id)
    {
        $sql = "SELECT id FROM clientes WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getTicketsByUserId($user_id)
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    d.img_dispositivo,
                    t.descripcion_falla,
                    e.name AS estado,
                    t.fecha_creacion,
                    tec.name AS tecnico
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                WHERE c.user_id = ?
                ORDER BY t.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getTicketsActivosByUserId($user_id)
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    d.img_dispositivo,
                    t.descripcion_falla,
                    e.name AS estado,
                                        t.fecha_creacion,
                                        t.fecha_cierre,
                    tec.name AS tecnico
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                WHERE c.user_id = ?
                                    AND t.fecha_cierre IS NULL
                ORDER BY t.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getTicketsTerminadosByUserId($user_id)
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    d.img_dispositivo,
                    t.descripcion_falla,
                    e.name AS estado,
                                        t.fecha_creacion,
                                        t.fecha_cierre,
                    tec.name AS tecnico
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                WHERE c.user_id = ?
                                    AND t.fecha_cierre IS NOT NULL
                ORDER BY t.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    public function actualizarDescripcion($id, $descripcion)
    {
        $stmt = $this->conn->prepare("UPDATE tickets SET descripcion_falla = ? WHERE id = ?");
        $stmt->bind_param("si", $descripcion, $id);
        return $stmt->execute();
    }

    public function actualizarCompleto($id, $descripcion, $estado_id, $tecnico_id)
    {
        
        if ($tecnico_id !== null && ($estado_id === null || $estado_id === '')) {
            $q = $this->conn->prepare("SELECT e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id = t.estado_id WHERE t.id = ? LIMIT 1");
            if ($q) {
                $idInt = (int)$id;
                $q->bind_param("i", $idInt);
                $q->execute();
                $row = $q->get_result()->fetch_assoc();
                $estadoActual = strtolower(trim($row['estado'] ?? ''));
                if ($estadoActual === 'nuevo') {
                    $st = $this->conn->prepare("SELECT id FROM estados_tickets WHERE LOWER(name)=LOWER('En espera') LIMIT 1");
                    if ($st) {
                        $st->execute();
                        $res = $st->get_result()->fetch_assoc();
                        if ($res && isset($res['id'])) {
                            $estado_id = (int)$res['id'];
                        }
                    }
                }
            }
        }

        $campos = [ 'descripcion_falla = ?' ];
        $types = 's';
        $params = [ $descripcion ];

        if ($estado_id !== null && $estado_id !== '') {
            $campos[] = 'estado_id = ?';
            $types .= 'i';
            $params[] = (int)$estado_id;
        }

        if ($tecnico_id !== null && $tecnico_id !== '') {
            $campos[] = 'tecnico_id = ?';
            $types .= 'i';
            $params[] = (int)$tecnico_id;
        }

        $sql = 'UPDATE tickets SET ' . implode(', ', $campos) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = (int)$id;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }
    public function getTicketsByTecnicoId($tecnico_user_id)
    {
        $sql = "SELECT 
                    t.id,
                    d.marca,
                    d.modelo,
                    d.img_dispositivo,
                    u.name AS cliente,
                    t.descripcion_falla,
                    e.name AS estado,
                    t.fecha_creacion,
                    t.fecha_cierre
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                INNER JOIN tecnicos tc ON t.tecnico_id = tc.id
                WHERE tc.user_id = ?
                ORDER BY t.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $tecnico_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    public function getAllTickets()
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    u.name AS cliente,
                    t.descripcion_falla AS descripcion,
                    e.name AS estado,
                    tec.name AS tecnico,
                    t.fecha_creacion,
                    t.fecha_cierre
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                LEFT JOIN tecnicos tc ON t.tecnico_id = tc.id
                LEFT JOIN users tec ON tc.user_id = tec.id
                ORDER BY t.id DESC";
        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }   

    public function getTicketsSinTecnico()
    {
        $sql = "SELECT 
                    t.id,
                    d.marca AS dispositivo,
                    d.modelo,
                    u.name AS cliente,
                    t.descripcion_falla AS descripcion,
                    e.name AS estado
                FROM tickets t
                INNER JOIN dispositivos d ON t.dispositivo_id = d.id
                INNER JOIN clientes c ON t.cliente_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                INNER JOIN estados_tickets e ON t.estado_id = e.id
                WHERE t.tecnico_id IS NULL
                ORDER BY t.id DESC";
        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function asignarTecnico($ticket_id, $tecnico_id, ?int $actor_user_id = null, ?string $actor_role = null)
    {
        
        $estadoNuevoId = null;
        $prev = $this->conn->prepare("SELECT t.tecnico_id, e.name AS estado FROM tickets t INNER JOIN estados_tickets e ON e.id=t.estado_id WHERE t.id=? LIMIT 1");
        if ($prev) {
            $tid = (int)$ticket_id;
            $prev->bind_param("i", $tid);
            $prev->execute();
            $row = $prev->get_result()->fetch_assoc();
            $estadoActual = strtolower(trim($row['estado'] ?? ''));
            $prevTec = isset($row['tecnico_id']) ? (int)$row['tecnico_id'] : null;

            if ($estadoActual === 'nuevo' && ($prevTec === null || $prevTec === 0) && (int)$tecnico_id > 0) {
                $q = $this->conn->prepare("SELECT id FROM estados_tickets WHERE LOWER(name)=LOWER('En espera') LIMIT 1");
                if ($q) {
                    $q->execute();
                    $r = $q->get_result()->fetch_assoc();
                    if ($r && isset($r['id'])) {
                        $estadoNuevoId = (int)$r['id'];
                    }
                }
            }
        }

        if ($estadoNuevoId) {
            $stmt = $this->conn->prepare("UPDATE tickets SET tecnico_id = ?, estado_id = ? WHERE id = ?");
            $stmt->bind_param("iii", $tecnico_id, $estadoNuevoId, $ticket_id);
            $ok = $stmt->execute();

            
            if ($ok && $actor_user_id && $actor_role) {
                require_once __DIR__ . '/TicketEstadoHistorial.php';
                $hist = new TicketEstadoHistorialModel($this->conn);
                $hist->add((int)$ticket_id, (int)$estadoNuevoId, (int)$actor_user_id, $actor_role, 'Técnico asignado. Pasa a En espera');
            }
            return $ok;
        } else {
            $stmt = $this->conn->prepare("UPDATE tickets SET tecnico_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $tecnico_id, $ticket_id);
            $ok = $stmt->execute();

            if ($ok && $actor_user_id && $actor_role) {
                require_once __DIR__ . '/TicketEstadoHistorial.php';
                $hist = new TicketEstadoHistorialModel($this->conn);
                $c = $this->conn->prepare("SELECT estado_id FROM tickets WHERE id = ? LIMIT 1");
                if ($c) {
                    $c->bind_param("i", $ticket_id);
                    $c->execute();
                    $r = $c->get_result()->fetch_assoc();
                    $hist->add((int)$ticket_id, (int)($r['estado_id'] ?? 0), (int)$actor_user_id, $actor_role, 'Técnico asignado');
                }
            }
            return $ok;
        }
    }

    public function getSupervisorId($ticket_id)
    {
        $stmt = $this->conn->prepare("SELECT supervisor_id FROM tickets WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['supervisor_id'] ?? null;
    }

    public function asignarSupervisor($ticket_id, $supervisor_id)
    {
        $stmt = $this->conn->prepare("UPDATE tickets SET supervisor_id = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $supervisor_id, $ticket_id);
        return $stmt->execute();
    }

    



    public function hasActiveTicketForDevice(int $deviceId): bool
    {
        
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) c
            FROM tickets
            WHERE dispositivo_id = ? AND fecha_cierre IS NULL
        ");
        if (!$stmt) return true;
        $stmt->bind_param('i', $deviceId);
        $stmt->execute();
        $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        return $c > 0;
    }
}
