<?php

class InventoryCategoryModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    
    public function createCategory(string $name)
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias_inventario (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param('s', $name);
            return $stmt->execute();
        }
        return false;
    }

    public function updateCategory(int $id, string $name)
    {
        $stmt = $this->conn->prepare("UPDATE categorias_inventario SET name = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $name, $id);
            return $stmt->execute();
        }
        return false;
    }

    public function deleteCategory(int $id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias_inventario WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        }
        return false;
    }

    public function getAllCategories(): array
    {
        $res = $this->conn->query("SELECT * FROM categorias_inventario");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias_inventario WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res->fetch_assoc() ?: null;
        }
        return null;
    }

    
    public function crearCategoria(string $name) { return $this->createCategory($name); }
    public function actualizarCategory(int $id, string $name) { return $this->updateCategory($id, $name); }
    public function eliminarCategoria(int $id) { return $this->deleteCategory($id); }
    public function obtenerCategoryPorId(int $id) { return $this->getCategoryById($id); }
}

?>