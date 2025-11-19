<?php

class EstadoTicketModel
{
    private $connection;

    public function __construct($dbConnection)
    {
        $this->connection = $dbConnection;
    }

    public function obtenerTodos(): array
    {
        return $this->getAllEstados();
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->getById($id);
    }

    public function crear(string $name): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO estados_tickets (name) VALUES (?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $name);
        return $stmt->execute();
    }

    public function updateEstado(int $id, string $name): bool
    {
        $stmt = $this->connection->prepare('UPDATE estados_tickets SET name = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $name, $id);
        return $stmt->execute();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM estados_tickets WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM estados_tickets WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getAllEstados(): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM estados_tickets ORDER BY id ASC');
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
