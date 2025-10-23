<?php

class DeviceModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function createDevice($userId, $categoriaId, $marca, $modelo, $descripcion, $img_dispositivo): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO dispositivos (user_id, categoria_id, marca, modelo, descripcion_falla, img_dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iissss", $userId, $categoriaId, $marca, $modelo, $descripcion, $img_dispositivo);
            return $stmt->execute();
        }
        return false;
    }

    public function findDeviceById($id): ?array
    {
        $sql = "SELECT d.*, u.name AS user_name, c.name AS categoria_name
                FROM dispositivos d
                INNER JOIN users u ON d.user_id = u.id
                INNER JOIN categorias c ON d.categoria_id = c.id
                WHERE d.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getDevicesByUserId($userId): array
    {
        $sql = "SELECT d.*, c.name AS categoria
                FROM dispositivos d
                LEFT JOIN categorias c ON d.categoria_id = c.id
                WHERE d.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getAllDevices(): array
    {
        $stmt = $this->conn->prepare("SELECT d.*, u.name as users, c.name as categoria FROM dispositivos d 
            JOIN users u ON d.user_id = u.id 
            JOIN categorias c ON d.categoria_id = c.id");
        if ($stmt) {
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function updateDevice($deviceId, $categoriaId, $marca, $modelo, $descripcion, $img_dispositivo): bool
    {
        $stmt = $this->conn->prepare("UPDATE dispositivos SET categoria_id = ?, marca = ?, modelo = ?, descripcion_falla = ?, img_dispositivo = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("issssi", $categoriaId, $marca, $modelo, $descripcion, $img_dispositivo, $deviceId);
            return $stmt->execute();
        }
        return false;
    }

    public function deleteDevice($deviceId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM dispositivos WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $deviceId);
            return $stmt->execute();
        }
        return false;
    }

    public function actualizarDatosPorTicket($ticket_id, $marca, $modelo): bool
    {
        $sql = "UPDATE dispositivos 
                SET marca = ?, modelo = ?
                WHERE id = (SELECT dispositivo_id FROM tickets WHERE id = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $marca, $modelo, $ticket_id);
        return $stmt->execute();
    }

    public function getOwnerId(int $deviceId): ?int
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM dispositivos WHERE id = ?");
        if (!$stmt) return null;
        $stmt->bind_param('i', $deviceId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? (int)$res['user_id'] : null;
    }

    public function deleteByIdAndUser(int $deviceId, int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM dispositivos WHERE id = ? AND user_id = ? LIMIT 1");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $deviceId, $userId);
        return $stmt->execute();
    }

    public function countDevicesByCategory(int $categoryId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM dispositivos WHERE categoria_id = ?");
        if (!$stmt) return 0;
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? (int)$res['total'] : 0;
    }
}
