<?php

class RatingModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS ticket_ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL UNIQUE,
            tecnico_id INT NOT NULL,
            cliente_id INT NOT NULL,
            stars TINYINT NOT NULL,
            comment TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->conn->query($sql);
    }

    public function getByTicket(int $ticketId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM ticket_ratings WHERE ticket_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $ticketId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function save(int $ticketId, int $tecnicoId, int $clienteId, int $stars, ?string $comment = null): bool
    {
        $existing = $this->getByTicket($ticketId);
        if ($existing) {
            $stmt = $this->conn->prepare('UPDATE ticket_ratings SET stars = ?, comment = ? WHERE ticket_id = ?');
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param('isi', $stars, $comment, $ticketId);
            return $stmt->execute();
        }

        $stmt = $this->conn->prepare('INSERT INTO ticket_ratings (ticket_id, tecnico_id, cliente_id, stars, comment) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiiis', $ticketId, $tecnicoId, $clienteId, $stars, $comment);
        return $stmt->execute();
    }

    public function getAvgForTecnico(int $tecnicoId): array
    {
        $stmt = $this->conn->prepare('SELECT AVG(stars) AS avg_stars, COUNT(*) AS total FROM ticket_ratings WHERE tecnico_id = ?');
        if (!$stmt) {
            return [null, 0];
        }

        $stmt->bind_param('i', $tecnicoId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return [$row['avg_stars'] ?? null, (int) ($row['total'] ?? 0)];
    }

    public function listForTecnico(int $tecnicoId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT r.ticket_id, r.stars, r.comment, r.created_at,
                    u.name AS cliente_nombre, u.email AS cliente_email
             FROM ticket_ratings r
             INNER JOIN clientes c ON r.cliente_id = c.id
             INNER JOIN users u ON c.user_id = u.id
             WHERE r.tecnico_id = ?
             ORDER BY r.created_at DESC'
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $tecnicoId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}

