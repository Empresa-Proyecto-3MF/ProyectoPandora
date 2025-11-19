<?php

class Historial
{
    private $connection;

    public function __construct($dbConnection)
    {
        $this->connection = $dbConnection;
    }

    public function agregarAccion(string $accion, string $detalle): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO historial (acciones, detalles, fecha) VALUES (?, ?, NOW())');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ss', $accion, $detalle);
        return $stmt->execute();
    }

    public function obtenerHistorial(): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM historial ORDER BY fecha DESC');
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarHistorial(
        string $q = '',
        string $tipo = '',
        string $desde = '',
        string $hasta = '',
        int $page = 1,
        int $perPage = 20
    ): array {
        $conds = [];
        $params = [];
        $types = '';

        if ($q !== '') {
            $conds[] = '(acciones LIKE ? OR detalles LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }
        if ($tipo !== '') {
            $conds[] = 'LOWER(acciones) LIKE ?';
            $params[] = '%' . strtolower($tipo) . '%';
            $types .= 's';
        }
        if ($desde !== '') {
            $conds[] = 'DATE(fecha) >= ?';
            $params[] = $desde;
            $types .= 's';
        }
        if ($hasta !== '') {
            $conds[] = 'DATE(fecha) <= ?';
            $params[] = $hasta;
            $types .= 's';
        }

        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

        $stmtCount = $this->connection->prepare("SELECT COUNT(*) AS c FROM historial $where");
        $total = 0;
        if ($stmtCount) {
            if ($types !== '') {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $res = $stmtCount->get_result()->fetch_assoc();
            $total = (int) ($res['c'] ?? 0);
        }

        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->connection->prepare("SELECT * FROM historial $where ORDER BY fecha DESC LIMIT ? OFFSET ?");
        if (!$stmt) {
            return ['data' => [], 'total' => $total, 'page' => $page, 'perPage' => $perPage];
        }

        if ($types !== '') {
            $typesAll = $types . 'ii';
            $paramsAll = array_merge($params, [$perPage, $offset]);
            $stmt->bind_param($typesAll, ...$paramsAll);
        } else {
            $stmt->bind_param('ii', $perPage, $offset);
        }

        $data = [];
        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return ['data' => $data, 'total' => $total, 'page' => $page, 'perPage' => $perPage];
    }
}
