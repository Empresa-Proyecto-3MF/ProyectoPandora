<?php
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Device.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Date.php';
require_once __DIR__ . '/../Core/ImageHelper.php';

class ClienteController {
    
    private function ticketPreviewUrl(array $ticket): string {
        $imgSrc = device_image_url($ticket['img_dispositivo'] ?? '');
        $photos = ticket_photo_urls((int)($ticket['id'] ?? 0));
        if (!empty($photos)) {
            $latest = end($photos);
            if (is_string($latest) && $latest !== '') {
                $imgSrc = $latest;
            }
        }
        return $imgSrc;
    }
    
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

    
    private function aplicarFiltrosYPresentacion(array $tickets): array {
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $desde = isset($_GET['desde']) ? trim((string)$_GET['desde']) : '';
        $hasta = isset($_GET['hasta']) ? trim((string)$_GET['hasta']) : '';

        
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
        header('Location: index.php?route=Cliente/MisDevice');
        exit;
    }

    public function MisDevice() {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?route=Auth/Login');
            exit;
        }

        $db = new Database();
        $db->connectDatabase();
        $deviceModel = new DeviceModel($db->getConnection());
        $ticketModel = new Ticket($db->getConnection());

        
        $dispositivos = $deviceModel->getDevicesByUserId($user['id']);
        
        foreach ($dispositivos as &$d) {
            $d['img_url'] = device_image_url($d['img_dispositivo'] ?? '');
            if (!array_key_exists('has_active_ticket', $d)) {
                $d['has_active_ticket'] = $ticketModel->hasActiveTicketForDevice((int)($d['id'] ?? 0));
            }
            
            if (!empty($d['fecha_registro'])) {
                $d['fecha_exact'] = DateHelper::exact($d['fecha_registro']);
                $d['fecha_human'] = DateHelper::smart($d['fecha_registro']);
            }
        }
        unset($d);

        include_once __DIR__ . '/../Views/Clientes/MisDevice.php';
    }

    
    public function MisTicket() {
        header('Location: index.php?route=Cliente/MisTicketActivo');
        exit;
    }

    public function MisTicketActivo() {
        $user = Auth::user();
        if (!$user) { header('Location: index.php?route=Auth/Login'); exit; }
        $db = new Database(); $db->connectDatabase(); $ticketModel = new Ticket($db->getConnection());
        
        $estado = strtolower(trim($_GET['estado'] ?? 'activos'));
        if ($estado === 'todos') {
            $tickets = array_merge(
                $ticketModel->getTicketsActivosByUserId($user['id']),
                $ticketModel->getTicketsTerminadosByUserId($user['id'])
            );
        } elseif ($estado === 'finalizados') {
            
            $qs = $_GET; $qs['route'] = 'Cliente/MisTicketTerminados';
            $url = 'index.php?' . http_build_query($qs);
            header('Location: ' . $url);
            exit;
        } else {
            $tickets = $ticketModel->getTicketsActivosByUserId($user['id']);
        }
        $tickets = $this->aplicarFiltrosYPresentacion($tickets);
        
        foreach ($tickets as &$t) {
            $t['img_preview'] = $this->ticketPreviewUrl($t);
        }
        unset($t);
        include_once __DIR__ . '/../Views/Clientes/MisTicketActivo.php';
    }

    public function MisTicketTerminados() {
        $user = Auth::user();
        if (!$user) { header('Location: index.php?route=Auth/Login'); exit; }
        $db = new Database(); $db->connectDatabase(); $ticketModel = new Ticket($db->getConnection());
        
        $estado = strtolower(trim($_GET['estado'] ?? 'finalizados'));
        if ($estado === 'todos') {
            $tickets = array_merge(
                $ticketModel->getTicketsActivosByUserId($user['id']),
                $ticketModel->getTicketsTerminadosByUserId($user['id'])
            );
        } elseif ($estado === 'activos') {
            $qs = $_GET; $qs['route'] = 'Cliente/MisTicketActivo';
            $url = 'index.php?' . http_build_query($qs);
            header('Location: ' . $url);
            exit;
        } else {
            $tickets = $ticketModel->getTicketsTerminadosByUserId($user['id']);
        }
        $tickets = $this->aplicarFiltrosYPresentacion($tickets);
        foreach ($tickets as &$t) {
            $t['img_preview'] = $this->ticketPreviewUrl($t);
        }
        unset($t);
        include_once __DIR__ . '/../Views/Clientes/MisTicketTerminados.php';
    }
}

?>