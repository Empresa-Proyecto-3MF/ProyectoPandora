<?php
require_once __DIR__ . '/../Models/EstadoTicket.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/HistorialController.php';
class EstadoTicketController
{
    private $estadoModel;
    private $historialController;

    public function __construct()
    {
        $db = new Database();
        $db->connectDatabase();
        $this->estadoModel = new EstadoTicketModel($db->getConnection());
        $this->historialController = new HistorialController();
    }

public function listar()
{
    $estados = $this->estadoModel->obtenerTodos() ?? [];
    include __DIR__ . '/../Views/EstadoTicket/ListarEstado.php';
}


    public function crear()
    {
        Auth::checkRole('Administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            if ($name) {
                $this->estadoModel->crear($name);

                $user = Auth::user();
                $accion = "Creación de estado de ticket";
                $detalle = "{$user['name']} creó el estado de ticket '{$name}'.";
                $this->historialController->agregarAccion($accion, $detalle);

                header('Location: /ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados');
                exit;
            }
        }
        include_once __DIR__ . '/../Views/EstadoTicket/CrearEstado.php';
    }
    public function editar($id = null)
    {
        Auth::checkRole('Administrador');
        if ($id === null && isset($_GET['id'])) {
            $id = $_GET['id'];
        }

        if ($id === null) {
            die("Error: no se recibió ID para editar.");
        }

        $estado = $this->estadoModel->getById($id);
        include_once __DIR__ . '/../Views/EstadoTicket/Actualizar.php';
    }


    public function actualizar()
    {
        Auth::checkRole('Administrador');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST['id'];
            $name = $_POST['name'];

            if ($this->estadoModel->updateEstado($id, $name)) {
                header("Location: /ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados");
                exit();
            } else {
                header('Location: /ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados');
                exit();
            }
        }
    }

    public function eliminar()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->estadoModel->eliminar($id);

            $user = Auth::user();
            $accion = "Eliminación de estado de ticket";
            $detalle = "{$user['name']} eliminó el estado (ID {$id}).";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=EstadoTicket/ListarEstados');
            exit;
        }
    }
}
