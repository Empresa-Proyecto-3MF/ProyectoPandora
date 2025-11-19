<?php

class ItemTicketModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function crear(
        int $ticketId,
        int $inventarioId,
        int $tecnicoId,
        int $supervisorId,
        int $cantidad,
        float $valorTotal
    ): bool {
        $stmt = $this->conn->prepare(
            'INSERT INTO item_ticket (ticket_id, inventario_id, tecnico_id, supervisor_id, cantidad, valor_total)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiiiid', $ticketId, $inventarioId, $tecnicoId, $supervisorId, $cantidad, $valorTotal);
        return $stmt->execute();
    }

    public function listarPorTicket(int $ticketId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT it.id,
                    it.ticket_id,
                    it.inventario_id,
                    it.tecnico_id,
                    it.supervisor_id,
                    it.cantidad,
                    it.valor_total,
                    it.fecha_asignacion,
                    i.name_item,
                    i.valor_unitario,
                    i.foto_item,
                    c.name AS categoria
             FROM item_ticket it
             INNER JOIN inventarios i ON it.inventario_id = i.id
             INNER JOIN categorias_inventario c ON i.categoria_id = c.id
             WHERE it.ticket_id = ?
             ORDER BY it.fecha_asignacion DESC'
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
