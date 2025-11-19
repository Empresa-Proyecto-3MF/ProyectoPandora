<?php

class DeviceCategoryModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function createCategory(string $name): bool
    {
        $sql = 'INSERT INTO categorias (name) VALUES (?)';
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $name);
        return $stmt->execute();
    }

    public function updateCategory(int $id, string $name): bool
    {
        $sql = 'UPDATE categorias SET name = ? WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $name, $id);
        return $stmt->execute();
    }

    public function deleteCategory(int $id): bool
    {
        $sql = 'DELETE FROM categorias WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getAllCategories(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM categorias');
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $res = $stmt->get_result();

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM categorias WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();

        return $res->fetch_assoc() ?: null;
    }

    public function findCategoryById(int $id): ?array
    {
        return $this->getCategoryById($id);
    }
}