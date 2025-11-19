<?php

class TicketLaborModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS ticket_labor (
            ticket_id INT PRIMARY KEY,
            tecnico_id INT NOT NULL,
            labor_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->conn->query($sql);
    }

    public function getByTicket(int $ticketId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM ticket_labor WHERE ticket_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $ticketId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function upsert(int $ticketId, int $tecnicoId, float $laborAmount): bool
    {
        if ($this->getByTicket($ticketId)) {
            $stmt = $this->conn->prepare('UPDATE ticket_labor SET labor_amount = ?, tecnico_id = ? WHERE ticket_id = ?');
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param('dii', $laborAmount, $tecnicoId, $ticketId);
            return $stmt->execute();
        }

        $stmt = $this->conn->prepare('INSERT INTO ticket_labor (ticket_id, tecnico_id, labor_amount) VALUES (?, ?, ?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iid', $ticketId, $tecnicoId, $laborAmount);
        return $stmt->execute();
    }
}

