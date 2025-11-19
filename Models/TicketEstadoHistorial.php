<?php

class TicketEstadoHistorialModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function add(int $ticketId, int $estadoId, int $userId, string $userRole, ?string $comentario = null): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO ticket_estado_historial (ticket_id, estado_id, user_id, user_role, comentario) VALUES (?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiiss', $ticketId, $estadoId, $userId, $userRole, $comentario);
        return $stmt->execute();
    }

    public function listByTicket(int $ticketId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT h.*, e.name AS estado, u.name AS autor
             FROM ticket_estado_historial h
             LEFT JOIN estados_tickets e ON e.id = h.estado_id
             LEFT JOIN users u ON u.id = h.user_id
             WHERE h.ticket_id = ?
             ORDER BY h.created_at ASC'
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

