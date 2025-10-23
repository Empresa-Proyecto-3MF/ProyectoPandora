<?php
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Date.php';

class ClienteController {
    // Helpers de presentación pedidos en el controlador
    private function estadoBadgeClass(?string $estado): string {
        $s = strtolower(trim($estado ?? ''));
        if (in_array($s, ['finalizado'], true)) return 'badge badge--success';
        if (in_array($s, ['cerrado','cancelado'], true)) return 'badge badge--danger';
        if (in_array($s, ['en proceso','diagnóstico','diagnostico','reparación','reparacion','en reparación','en pruebas'], true)) return 'badge badge--info';
        if (in_array($s, ['en espera','pendiente','presupuesto'], true)) return 'badge badge--warning';
        if (in_array($s, ['abierto','nuevo','recibido'], true)) return 'badge badge--primary';
        return 'badge badge--muted';
    }
    private function puedeEliminarTicket(?string $estado): bool {
        return strtolower(trim($estado ?? '')) === 'nuevo';
    }

    // Aplica filtros básicos por GET (estado ya viene aplicado por origen) y enriquece datos
    private function aplicarFiltrosYPresentacion(array $tickets): array {
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $desde = isset($_GET['desde']) ? trim((string)$_GET['desde']) : '';
        $hasta = isset($_GET['hasta']) ? trim((string)$_GET['hasta']) : '';

        // Filtro por texto
        if ($q !== '') {
            $qLower = mb_strtolower($q, 'UTF-8');
            $tickets = array_values(array_filter($tickets, function($t) use ($qLower){
                $fields = [
                    $t['descripcion_falla'] ?? '',
                    $t['estado'] ?? '',
                    $t['dispositivo'] ?? ($t['marca'] ?? ''),
                    $t['modelo'] ?? '',
                    $t['tecnico'] ?? '',
                ];
                $hay = false;
                foreach ($fields as $f) {
                    if ($f !== null && $f !== '' && strpos(mb_strtolower((string)$f, 'UTF-8'), $qLower) !== false) { $hay = true; break; }
                }
                return $hay;
            }));
        }

        // Filtro por rango de fechas (creación)
        if ($desde !== '' || $hasta !== '') {
            $tickets = array_values(array_filter($tickets, function($t) use ($desde, $hasta){
                $f = substr((string)($t['fecha_creacion'] ?? ''), 0, 10);
                if ($desde !== '' && $f < $desde) return false;
                if ($hasta !== '' && $f > $hasta) return false;
                return true;
            }));
        }

        // Enriquecer para la vista (badge + flags + fechas formateadas)
        foreach ($tickets as &$t) {
            $t['estadoClass'] = $this->estadoBadgeClass($t['estado'] ?? '');
            $t['puedeEliminar'] = $this->puedeEliminarTicket($t['estado'] ?? '');
            if (!empty($t['fecha_creacion'])) {
                $t['fecha_exact'] = DateHelper::exact($t['fecha_creacion']);
                $t['fecha_human'] = DateHelper::smart($t['fecha_creacion']);
            }
        }
        unset($t);

        return $tickets;
    }
    
    public function PanelCliente(){
        header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisDevice');
        exit;
    }

    public function MisDevice() {
        $user = Auth::user();
        if (!$user) {
            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        }

        $db = new Database();
        $db->connectDatabase();
        $deviceModel = new DeviceModel($db->getConnection());

        
        $dispositivos = $deviceModel->getDevicesByUserId($user['id']);

        include_once __DIR__ . '/../Views/Clientes/MisDevice.php';
    }

    // Compat: redirige a activos para ruta antigua
    public function MisTicket() {
        header('Location: /ProyectoPandora/Public/index.php?route=Cliente/MisTicketActivo');
        exit;
    }

    public function MisTicketActivo() {
        $user = Auth::user();
        if (!$user) { header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login'); exit; }
        $db = new Database(); $db->connectDatabase(); $ticketModel = new Ticket($db->getConnection());
        // Soporta filtro de estado (activos/finalizados/todos) para unificar vista vía filtros
        $estado = strtolower(trim($_GET['estado'] ?? 'activos'));
        if ($estado === 'todos') {
            $tickets = array_merge(
                $ticketModel->getTicketsActivosByUserId($user['id']),
                $ticketModel->getTicketsTerminadosByUserId($user['id'])
            );
        } elseif ($estado === 'finalizados') {
            // Redirigir a la ruta de terminados para URL coherente, preservando filtros
            $qs = $_GET; $qs['route'] = 'Cliente/MisTicketTerminados';
            $url = '/ProyectoPandora/Public/index.php?' . http_build_query($qs);
            header('Location: ' . $url);
            exit;
        } else {
            $tickets = $ticketModel->getTicketsActivosByUserId($user['id']);
        }
        $tickets = $this->aplicarFiltrosYPresentacion($tickets);
        include_once __DIR__ . '/../Views/Clientes/MisTicketActivo.php';
    }

    public function MisTicketTerminados() {
        $user = Auth::user();
        if (!$user) { header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login'); exit; }
        $db = new Database(); $db->connectDatabase(); $ticketModel = new Ticket($db->getConnection());
        // Soporta filtro de estado (activos/finalizados/todos)
        $estado = strtolower(trim($_GET['estado'] ?? 'finalizados'));
        if ($estado === 'todos') {
            $tickets = array_merge(
                $ticketModel->getTicketsActivosByUserId($user['id']),
                $ticketModel->getTicketsTerminadosByUserId($user['id'])
            );
        } elseif ($estado === 'activos') {
            $qs = $_GET; $qs['route'] = 'Cliente/MisTicketActivo';
            $url = '/ProyectoPandora/Public/index.php?' . http_build_query($qs);
            header('Location: ' . $url);
            exit;
        } else {
            $tickets = $ticketModel->getTicketsTerminadosByUserId($user['id']);
        }
        $tickets = $this->aplicarFiltrosYPresentacion($tickets);
        include_once __DIR__ . '/../Views/Clientes/MisTicketTerminados.php';
    }
}

?>