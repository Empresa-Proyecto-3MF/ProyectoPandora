<?php 

class InventarioModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    
    public function listar(): array
    {
        $sql = "SELECT i.id, c.name AS categoria, i.name_item, i.valor_unitario, i.descripcion, i.foto_item, i.stock_actual, i.stock_minimo, i.fecha_creacion
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id
                ORDER BY i.id DESC";
        $result = $this->conn->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    
    public function listarFiltrado($categoria_id = null, $buscar = '', $limit = null, $offset = null, $sort = 'i.id', $dir = 'DESC'): array
    {
        $sql = "SELECT i.id, c.name AS categoria, i.name_item, i.valor_unitario, i.foto_item, i.stock_actual, i.stock_minimo
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id";
        $conds = [];
        $params = [];
        $types = '';

        $allowedSort = [
            'i.id' => 'i.id',
            'id' => 'i.id',
            'categoria' => 'c.name',
            'c.name' => 'c.name',
            'i.name_item' => 'i.name_item',
            'name_item' => 'i.name_item',
            'valor_unitario' => 'i.valor_unitario',
            'i.valor_unitario' => 'i.valor_unitario',
            'stock_actual' => 'i.stock_actual',
            'i.stock_actual' => 'i.stock_actual',
            'stock_minimo' => 'i.stock_minimo',
            'i.stock_minimo' => 'i.stock_minimo'
        ];
        $sortKey = strtolower(trim((string)$sort));
        $sortColumn = $allowedSort[$sortKey] ?? 'i.id';
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        if ($categoria_id) {
            $conds[] = 'i.categoria_id = ?';
            $types .= 'i';
            $params[] = (int)$categoria_id;
        }
        if ($buscar !== '') {
            $conds[] = 'i.name_item LIKE ?';
            $types .= 's';
            $params[] = '%' . $buscar . '%';
        }
        if ($conds) {
            $sql .= ' WHERE ' . implode(' AND ', $conds);
        }
        $sql .= " ORDER BY $sortColumn $dir";
        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
            $types .= 'ii';
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        if ($types) {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];

            $bindParams = [];
            $bindParams[] = &$types;
            foreach ($params as $k => $v) {
                $bindParams[] = &$params[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->conn->query($sql);
        }
        $data = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function contarFiltrado($categoria_id = null, $buscar = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id";
        $conds = [];
        $params = [];
        $types = '';
        if ($categoria_id) {
            $conds[] = 'i.categoria_id = ?';
            $types .= 'i';
            $params[] = (int)$categoria_id;
        }
        if ($buscar !== '') {
            $conds[] = 'i.name_item LIKE ?';
            $types .= 's';
            $params[] = '%' . $buscar . '%';
        }
        if ($conds) {
            $sql .= ' WHERE ' . implode(' AND ', $conds);
        }
        if ($types) {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return 0;
            $bindParams = [];
            $bindParams[] = & $types;
            foreach ($params as $k => $v) {
                $bindParams[] = & $params[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->conn->query($sql);
        }
        if ($res && ($row = $res->fetch_assoc())) {
            return (int)$row['total'];
        }
        return 0;
    }

    
    public function crear($categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo): bool
    {
        
        if ((float)$valor_unitario < 0 || (int)$stock_actual < 0 || (int)$stock_minimo < 0) {
            return false;
        }
        $sql = "INSERT INTO inventarios (categoria_id, name_item, valor_unitario, descripcion, foto_item, stock_actual, stock_minimo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isdssii", $categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo);
            return $stmt->execute();
        }
        return false;
    }

    
    public function eliminar($id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM inventarios WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
        return false;
    }

    
    public function obtenerPorId($id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM inventarios WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    
    public function actualizar($id, $categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo): bool
    {
        if ((float)$valor_unitario < 0 || (int)$stock_actual < 0 || (int)$stock_minimo < 0) {
            return false;
        }
        $sql = "UPDATE inventarios SET categoria_id=?, name_item=?, valor_unitario=?, descripcion=?, foto_item=?, stock_actual=?, stock_minimo=?
                WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isdssiii", $categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo, $id);
            return $stmt->execute();
        }
        return false;
    }

    
    public function findByCategoryAndName($categoria_id, $name_item): ?array
    {
        $sql = "SELECT * FROM inventarios WHERE categoria_id = ? AND name_item = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $categoria_id, $name_item);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res->fetch_assoc();
        }
        return null;
    }

    
    public function sumarStock($id, $cantidad): bool
    {
        if ((int)$cantidad <= 0) { return false; }
        $sql = "UPDATE inventarios SET stock_actual = stock_actual + ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $cantidad, $id);
            return $stmt->execute();
        }
        return false;
    }

    
    public function reducirStock($id, $cantidad): bool
    {
        
        $stmtSel = $this->conn->prepare("SELECT stock_actual FROM inventarios WHERE id = ?");
        if (!$stmtSel) return false;
        $stmtSel->bind_param("i", $id);
        $stmtSel->execute();
        $res = $stmtSel->get_result();
        $row = $res->fetch_assoc();
        if (!$row || (int)$row['stock_actual'] < (int)$cantidad) {
            return false;
        }
        $stmtUpd = $this->conn->prepare("UPDATE inventarios SET stock_actual = stock_actual - ? WHERE id = ?");
        if ($stmtUpd) {
            $stmtUpd->bind_param("ii", $cantidad, $id);
            return $stmtUpd->execute();
        }
        return false;
    }

    
    public function listarCategorias(): array
    {
        $sql = "SELECT id, name FROM categorias_inventario ORDER BY name";
        $result = $this->conn->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
}

?>