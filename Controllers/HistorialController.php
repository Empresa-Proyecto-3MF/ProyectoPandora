<?php
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Historial.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Date.php';
class HistorialController
{
    private $historialModel;
    public function __construct()
    {
        $db = new Database();
        $db->connectDatabase();
        $this->historialModel = new Historial($db->getConnection());
    }
    public function agregarAccion($accion, $detalle)
    {
        return $this->historialModel->agregarAccion($accion, $detalle);
    }
    public function obtenerHistorial()
    {
        return $this->historialModel->obtenerHistorial();
    }
    public function listarHistorial()
    {
        Auth::checkRole('Administrador');
        
        $q = trim($_GET['q'] ?? '');
        $tipo = trim($_GET['tipo'] ?? '');
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['perPage'] ?? 20)));

        $result = $this->historialModel->buscarHistorial($q, $tipo, $desde, $hasta, $page, $perPage);
        
        $historial = array_map(function($row){
            $iso = $row['fecha'] ?? null;
            $row['fecha_exact'] = DateHelper::exact($iso);
            $row['fecha_human'] = DateHelper::smart($iso);
            return $row;
        }, $result['data'] ?? []);
        $total = (int)$result['total'];
        $perPage = (int)$result['perPage'];
        $page = (int)$result['page'];
        $totalPages = (int)ceil(($total ?: 0) / ($perPage ?: 1));

        include_once __DIR__ . '/../Views/Admin/Historial.php';
    }
}
