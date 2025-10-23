<?php

class LogFormatter
{
    // Formatea montos como $1234.56 (consistente con el resto del proyecto)
    public static function monto(float $v): string
    {
        return '$' . number_format($v, 2, '.', '');
    }

    // Devuelve nombre del cliente asociado al ticket
    public static function nombreClientePorTicket($conn, int $ticketId): ?string
    {
        $sql = "SELECT uc.name AS cliente\n                FROM tickets t\n                INNER JOIN clientes c ON t.cliente_id=c.id\n                INNER JOIN users uc ON uc.id=c.user_id\n                WHERE t.id=? LIMIT 1";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $ticketId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            return $row['cliente'] ?? null;
        }
        return null;
    }

    // Devuelve nombre del técnico asignado al ticket
    public static function nombreTecnicoPorTicket($conn, int $ticketId): ?string
    {
        $sql = "SELECT ut.name AS tec\n                FROM tickets t\n                LEFT JOIN tecnicos tc ON t.tecnico_id=tc.id\n                LEFT JOIN users ut ON ut.id=tc.user_id\n                WHERE t.id=? LIMIT 1";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $ticketId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            return $row['tec'] ?? null;
        }
        return null;
    }

    // Resumen rápido de presupuesto (items, subtotal, mano y total)
    public static function resumenPresupuesto($conn, int $ticketId): array
    {
        require_once __DIR__ . '/../Models/ItemTicket.php';
        require_once __DIR__ . '/../Models/TicketLabor.php';
        $itemM = new ItemTicketModel($conn);
        $laborM = new TicketLaborModel($conn);
        $items = $itemM->listarPorTicket($ticketId);
        $subtotal = 0.0; foreach ($items as $it) { $subtotal += (float)($it['valor_total'] ?? 0); }
        $labor = $laborM->getByTicket($ticketId); $mano = (float)($labor['labor_amount'] ?? 0);
        $total = $subtotal + $mano;
        return [
            'itemsCount' => is_array($items) ? count($items) : 0,
            'subtotal'   => $subtotal,
            'mano'       => $mano,
            'total'      => $total,
        ];
    }

    // Descripción breve del dispositivo: "Marca Modelo (Categoría)"
    public static function dispositivoResumenPorId($conn, int $deviceId): string
    {
        $sql = "SELECT d.marca, d.modelo, c.name AS categoria\n                FROM dispositivos d\n                LEFT JOIN categorias c ON c.id = d.categoria_id\n                WHERE d.id = ? LIMIT 1";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $deviceId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc() ?: [];
            $marca = trim((string)($row['marca'] ?? ''));
            $modelo = trim((string)($row['modelo'] ?? ''));
            $cat = trim((string)($row['categoria'] ?? ''));
            $base = trim(($marca . ' ' . $modelo)) ?: 'dispositivo';
            return $base . ($cat ? (' (categoría ' . $cat . ')') : '');
        }
        return 'dispositivo';
    }
}

?>