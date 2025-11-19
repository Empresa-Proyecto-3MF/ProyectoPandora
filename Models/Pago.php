<?php

class PagoModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS pagos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            method ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') NOT NULL DEFAULT 'EFECTIVO',
            reference VARCHAR(100) NULL,
            user_id INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->conn->query($sql);
    }

    public function add(int $ticketId, float $amount, string $method, ?string $reference, ?int $userId): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO pagos (ticket_id, amount, method, reference, user_id) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('idssi', $ticketId, $amount, $method, $reference, $userId);
        return $stmt->execute();
    }
}
