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
        $sql = 'SELECT i.id, c.name AS categoria, i.name_item, i.valor_unitario, i.descripcion, i.foto_item,
                       i.stock_actual, i.stock_minimo, i.fecha_creacion
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id
                ORDER BY i.id DESC';

        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function listarFiltrado(
        ?int $categoriaId = null,
        string $buscar = '',
        ?int $limit = null,
        ?int $offset = null,
        string $sort = 'i.id',
        string $dir = 'DESC'
    ): array {
        $sql = 'SELECT i.id, c.name AS categoria, i.name_item, i.valor_unitario, i.foto_item, i.stock_actual, i.stock_minimo
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id';

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
            'i.stock_minimo' => 'i.stock_minimo',
        ];

        $sortKey = strtolower(trim($sort));
        $sortColumn = $allowedSort[$sortKey] ?? 'i.id';
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        if ($categoriaId) {
            $conds[] = 'i.categoria_id = ?';
            $types .= 'i';
            $params[] = $categoriaId;
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
            $params[] = $limit;
            $params[] = $offset;
        }

        $result = null;
        if ($types !== '') {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return [];
            }

            $this->bindParams($stmt, $types, $params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function contarFiltrado(?int $categoriaId = null, string $buscar = ''): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM inventarios i
                INNER JOIN categorias_inventario c ON i.categoria_id = c.id';

        $conds = [];
        $params = [];
        $types = '';

        if ($categoriaId) {
            $conds[] = 'i.categoria_id = ?';
            $types .= 'i';
            $params[] = $categoriaId;
        }
        if ($buscar !== '') {
            $conds[] = 'i.name_item LIKE ?';
            $types .= 's';
            $params[] = '%' . $buscar . '%';
        }

        if ($conds) {
            $sql .= ' WHERE ' . implode(' AND ', $conds);
        }

        $result = null;
        if ($types !== '') {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return 0;
            }
            $this->bindParams($stmt, $types, $params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        if ($result && ($row = $result->fetch_assoc())) {
            return (int) $row['total'];
        }

        return 0;
    }

    public function crear(
        int $categoriaId,
        string $nameItem,
        float $valorUnitario,
        string $descripcion,
        string $fotoItem,
        int $stockActual,
        int $stockMinimo
    ): bool {
        if ($valorUnitario < 0 || $stockActual < 0 || $stockMinimo < 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO inventarios (categoria_id, name_item, valor_unitario, descripcion, foto_item, stock_actual, stock_minimo)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isdssii', $categoriaId, $nameItem, $valorUnitario, $descripcion, $fotoItem, $stockActual, $stockMinimo);
        return $stmt->execute();
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM inventarios WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM inventarios WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function actualizar(
        int $id,
        int $categoriaId,
        string $nameItem,
        float $valorUnitario,
        string $descripcion,
        string $fotoItem,
        int $stockActual,
        int $stockMinimo
    ): bool {
        if ($valorUnitario < 0 || $stockActual < 0 || $stockMinimo < 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'UPDATE inventarios
             SET categoria_id = ?, name_item = ?, valor_unitario = ?, descripcion = ?, foto_item = ?, stock_actual = ?, stock_minimo = ?
             WHERE id = ?'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isdssiii', $categoriaId, $nameItem, $valorUnitario, $descripcion, $fotoItem, $stockActual, $stockMinimo, $id);
        return $stmt->execute();
    }

    public function findByCategoryAndName(int $categoriaId, string $nameItem): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM inventarios WHERE categoria_id = ? AND name_item = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('is', $categoriaId, $nameItem);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function sumarStock(int $id, int $cantidad): bool
    {
        if ($cantidad <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare('UPDATE inventarios SET stock_actual = stock_actual + ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $cantidad, $id);
        return $stmt->execute();
    }

    public function reducirStock(int $id, int $cantidad): bool
    {
        if ($cantidad <= 0) {
            return false;
        }
        $stmtSelect = $this->conn->prepare('SELECT stock_actual FROM inventarios WHERE id = ?');
        if (!$stmtSelect) {
            return false;
        }

        $stmtSelect->bind_param('i', $id);
        $stmtSelect->execute();
        $row = $stmtSelect->get_result()->fetch_assoc();

        if (!$row || (int) $row['stock_actual'] < $cantidad) {
            return false;
        }

        $stmtUpdate = $this->conn->prepare('UPDATE inventarios SET stock_actual = stock_actual - ? WHERE id = ?');
        if (!$stmtUpdate) {
            return false;
        }

        $stmtUpdate->bind_param('ii', $cantidad, $id);
        return $stmtUpdate->execute();
    }

    public function listarCategorias(): array
    {
        $result = $this->conn->query('SELECT id, name FROM categorias_inventario ORDER BY name');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function bindParams($stmt, string &$types, array &$params): void
    {
        $bindParams = [&$types];
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
}
