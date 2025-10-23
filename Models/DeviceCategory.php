<?php

class DeviceCategoryModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    // CRUD sobre tabla 'categorias' (dispositivos)
    public function createCategory(string $name)
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param('s', $name);
            return $stmt->execute();
        }
        return false;
    }

    public function updateCategory(int $id, string $name)
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET name = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $name, $id);
            return $stmt->execute();
        }
        return false;
    }

    public function deleteCategory(int $id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        }
        return false;
    }

    public function getAllCategories(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias");
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            return $res->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res->fetch_assoc() ?: null;
        }
        return null;
    }

    // Compatibilidad con usos existentes
    public function findCategoryById(int $id): ?array
    {
        return $this->getCategoryById($id);
    }
}

?>