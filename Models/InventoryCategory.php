<?php

class InventoryCategoryModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function createCategory(string $name): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO categorias_inventario (name) VALUES (?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $name);
        return $stmt->execute();
    }

    public function updateCategory(int $id, string $name): bool
    {
        $stmt = $this->conn->prepare('UPDATE categorias_inventario SET name = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $name, $id);
        return $stmt->execute();
    }

    public function deleteCategory(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM categorias_inventario WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getAllCategories(): array
    {
        $result = $this->conn->query('SELECT * FROM categorias_inventario');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM categorias_inventario WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function crearCategoria(string $name): bool
    {
        return $this->createCategory($name);
    }

    public function actualizarCategory(int $id, string $name): bool
    {
        return $this->updateCategory($id, $name);
    }

    public function eliminarCategoria(int $id): bool
    {
        return $this->deleteCategory($id);
    }

    public function obtenerCategoryPorId(int $id): ?array
    {
        return $this->getCategoryById($id);
    }
}
