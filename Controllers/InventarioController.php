<?php 
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Inventario.php';
require_once __DIR__ . '/HistorialController.php';
require_once __DIR__ . '/../Models/InventoryCategory.php';
require_once __DIR__ . '/../Core/Storage.php';
require_once __DIR__ . '/../Core/Middleware.php';
require_once __DIR__ . '/../Core/Flash.php';

class InventarioController
{
    private $categoryModel;
    private $inventarioModel;
    private $historialController;

    public function __construct()
    {
        $db = new Database();
        $db->connectDatabase();
        $this->inventarioModel = new InventarioModel($db->getConnection());
    $this->categoryModel = new InventoryCategoryModel($db->getConnection());
        $this->historialController = new HistorialController();
    }

    public function listarInventario()
    {
        
        
        Auth::checkRole(['Supervisor', 'Tecnico']);
        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
        exit;
    }

    public function mostrarCrear()
    {
        
    Middleware::requireRoles(['Supervisor']);
        $categorias = $this->inventarioModel->listarCategorias();
        include_once __DIR__ . '/../Views/Inventario/CrearItem.php';
    }

    public function crear()
    {
        
    Middleware::requireRoles(['Supervisor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
            $name_item = trim((string)($_POST['name_item'] ?? ''));
            $valor_unitario = (float)($_POST['valor_unitario'] ?? 0);
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));
            $stock_actual = (int)($_POST['stock_actual'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 0);
            $foto_item = 'NoItem.jpg';

            
            $errores = [];
            if (!$categoria_id) { $errores[] = 'Seleccioná una categoría.'; }
            if ($name_item === '') { $errores[] = 'Indicá el nombre del ítem.'; }
            if ($valor_unitario < 0) { $errores[] = 'El valor unitario no puede ser negativo.'; }
            if ($stock_actual < 0) { $errores[] = 'La cantidad inicial no puede ser negativa.'; }
            if ($stock_minimo < 0) { $errores[] = 'El stock mínimo no puede ser negativo.'; }

            if ($errores) {
                $categorias = $this->inventarioModel->listarCategorias();
                $errorMsg = implode(' ', $errores);
                $old = compact('categoria_id','name_item','valor_unitario','descripcion','stock_actual','stock_minimo');
                include_once __DIR__ . '/../Views/Inventario/CrearItem.php';
                return;
            }

            if (!empty($_FILES['foto_item']['name'])) {
                $stored = Storage::storeUploadedFile($_FILES['foto_item'], 'inventory');
                if (!$stored) {
                    $categorias = $this->inventarioModel->listarCategorias();
                    $errorMsg = "Error al subir la imagen del ítem.";
                    include_once __DIR__ . '/../Views/Inventario/CrearItem.php';
                    return;
                }
                $foto_item = $stored['relative'];
            }

            
            $existente = $this->inventarioModel->findByCategoryAndName((int)$categoria_id, $name_item);
            if ($existente) {
                if ($stock_actual <= 0) {
                    $categorias = $this->inventarioModel->listarCategorias();
                    $errorMsg = 'La cantidad a ingresar debe ser mayor que 0 para un ítem existente.';
                    $old = compact('categoria_id','name_item','valor_unitario','descripcion','stock_actual','stock_minimo');
                    include_once __DIR__ . '/../Views/Inventario/CrearItem.php';
                    return;
                }
                $ok = $this->inventarioModel->sumarStock((int)$existente['id'], (int)$stock_actual);
            } else {
                $ok = $this->inventarioModel->crear($categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo);
            }

            if ($ok) {
                $user = Auth::user();
                $accion = $existente ? "Ingreso de stock" : "Alta de ítem en inventario";
                $detalle = $existente
                    ? "{$user['name']} agregó {$stock_actual} unidad(es) a '{$name_item}' (ID {$existente['id']}). Nuevo stock reflejado en la ficha."
                    : "{$user['name']} dio de alta el ítem '{$name_item}' con stock inicial {$stock_actual} (stock mínimo sugerido {$stock_minimo}).";
                $this->historialController->agregarAccion($accion, $detalle);
                
                $finalStock = $existente ? ($existente['stock_actual'] + $stock_actual) : $stock_actual;
                if ($finalStock < $stock_minimo) {
                    Flash::set('warning', "El ítem '{$name_item}' está por debajo del stock mínimo ({$finalStock} < {$stock_minimo}).");
                } else if ($finalStock == $stock_minimo) {
                    Flash::set('info', "El ítem '{$name_item}' alcanzó exactamente el stock mínimo ({$stock_minimo}).");
                } else if ($finalStock <= ($stock_minimo * 1.2)) {
                    Flash::set('info', "El ítem '{$name_item}' está cerca del stock mínimo ({$finalStock} / {$stock_minimo}). Considera reponer pronto.");
                }
                Flash::successQuiet('Inventario actualizado.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                exit;
            } else {
                Flash::error('No se pudo crear o actualizar el ítem de inventario.');
                header('Location: /ProyectoPandora/Public/index.php?route=Inventario/CrearItem');
                exit;
            }
        }
        $this->mostrarCrear();
    }

    public function eliminar()
    {
        
    Middleware::requireRoles(['Supervisor']);
        $id = $_GET['id'] ?? null;
        if ($id && $this->inventarioModel->eliminar($id)) {
            $user = Auth::user();
            $this->historialController->agregarAccion(
                "Baja de ítem en inventario",
                "{$user['name']} eliminó el ítem con ID {$id} del inventario."
            );
            Flash::successQuiet('Ítem eliminado.');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            exit;
        } else {
            Flash::error('No se pudo eliminar el ítem.');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            exit;
        }
    }

    public function mostrarActualizar()
    {
        
        Auth::checkRole(['Supervisor']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario&error=1');
            exit;
        }
    $item = $this->inventarioModel->obtenerPorId($id);
    $categorias = $this->inventarioModel->listarCategorias();
    
    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario&id=' . urlencode((string)$id));
    exit;
    }

    public function editar()
    {
        
        Auth::checkRole(['Supervisor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
            $name_item = trim((string)($_POST['name_item'] ?? ''));
            $valor_unitario = (float)($_POST['valor_unitario'] ?? 0);
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));
            $stock_actual = (int)($_POST['stock_actual'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 0);
            $foto_item_actual = trim((string)($_POST['foto_item_actual'] ?? ''));
            $foto_item = $foto_item_actual !== '' ? $foto_item_actual : 'NoItem.jpg';

            if (!empty($_FILES['foto_item']['name'])) {
                $stored = Storage::storeUploadedFile($_FILES['foto_item'], 'inventory');
                if ($stored) {
                    $foto_item = $stored['relative'];
                }
            }

            
            if (!$id || !$categoria_id || $name_item === '' || $valor_unitario < 0 || $stock_actual < 0 || $stock_minimo < 0) {
                Flash::error('Datos inválidos para actualizar el ítem.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                exit;
            }

            if ($this->inventarioModel->actualizar($id, $categoria_id, $name_item, $valor_unitario, $descripcion, $foto_item, $stock_actual, $stock_minimo)) {
                $user = Auth::user();
                $this->historialController->agregarAccion(
                    "Edición de ítem en inventario",
                    "{$user['name']} actualizó la ficha de '{$name_item}' (ID {$id})."
                );
                if ($stock_actual < $stock_minimo) {
                    Flash::set('warning', "El ítem '{$name_item}' permanece por debajo del stock mínimo ({$stock_actual} < {$stock_minimo}).");
                } else if ($stock_actual == $stock_minimo) {
                    Flash::set('info', "El ítem '{$name_item}' quedó exactamente en el stock mínimo ({$stock_minimo}).");
                } else if ($stock_actual <= ($stock_minimo * 1.2)) {
                    Flash::set('info', "El ítem '{$name_item}' está cerca del stock mínimo ({$stock_actual} / {$stock_minimo}). Considera reponer pronto.");
                }
                Flash::successQuiet('Stock sumado correctamente.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                exit;
            } else {
                Flash::error('No se pudo guardar los cambios del ítem.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                exit;
            }
        }
    
    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
    exit;
    }

    public function sumarStock()
    {
        
        Auth::checkRole(['Supervisor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);
            if ($id > 0 && $cantidad > 0) {
                if ($this->inventarioModel->sumarStock($id, $cantidad)) {
                    $user = Auth::user();
                    $this->historialController->agregarAccion(
                        'Ingreso de stock',
                        "{$user['name']} sumó {$cantidad} unidad(es) al ítem ID {$id}."
                    );
                    
                    $item = $this->inventarioModel->obtenerPorId($id);
                    if ($item) {
                        $name_item = $item['name_item'] ?? 'Ítem';
                        $stock_actual = (int)($item['stock_actual'] ?? 0);
                        $stock_minimo = (int)($item['stock_minimo'] ?? 0);
                        if ($stock_actual < $stock_minimo) {
                            Flash::set('warning', "El ítem '{$name_item}' sigue bajo stock mínimo ({$stock_actual} < {$stock_minimo}).");
                        } else if ($stock_actual == $stock_minimo) {
                            Flash::set('info', "El ítem '{$name_item}' alcanzó el stock mínimo ({$stock_minimo}).");
                        } else if ($stock_actual <= ($stock_minimo * 1.2)) {
                            Flash::set('info', "El ítem '{$name_item}' está cerca del stock mínimo ({$stock_actual} / {$stock_minimo}).");
                        }
                    }
                    Flash::successQuiet('Item actualizado.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                    exit;
                }
            }
        }
        Flash::error('Operación inválida para sumar stock.');
        header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
        exit;
    }
    
    public function listarCategorias()
    {
        
    Middleware::requireRoles(['Administrador', 'Supervisor']);
        $categorias = $this->inventarioModel->listarCategorias();
        include_once __DIR__ . '/../Views/Inventario/ListaCategoria.php';
    }

    public function mostrarCrearCategoria()
    {
        
    Middleware::requireRoles(['Administrador', 'Supervisor']);
        include_once __DIR__ . '/../Views/Inventario/CrearCategoria.php';
    }

    public function crearCategoria()
    {
        
    Middleware::requireRoles(['Administrador', 'Supervisor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            if ($this->categoryModel->createCategory($name)) {
                $user = Auth::user();
                $accion = "Creación de categoría de inventario";
                $detalle = "{$user['name']} creó la categoría de inventario '{$name}'.";
                $this->historialController->agregarAccion($accion, $detalle);
                
                if (($user['role'] ?? '') === 'Supervisor') {
                    Flash::successQuiet('Item actualizado.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                } else {
                    Flash::successQuiet('Categoría creada.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
                }
                exit;
            } else {
                $user = Auth::user();
                if (($user['role'] ?? '') === 'Supervisor') {
                    Flash::error('No se pudo crear la categoría.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                } else {
                    Flash::error('No se pudo crear la categoría.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
                }
                exit;
            }
        }
        $this->mostrarCrearCategoria();
    }

    public function eliminarCategoriaInventario()
    {
        
        Auth::checkRole(['Administrador', 'Supervisor']);
        $id = $_GET['id'] ?? null;
    if ($id && $this->categoryModel->deleteCategory((int)$id)) {
            $user = Auth::user();
            $accion = "Eliminación de categoría de inventario";
            $detalle = "{$user['name']} eliminó la categoría de inventario (ID {$id}).";
            $this->historialController->agregarAccion($accion, $detalle);
            if (($user['role'] ?? '') === 'Supervisor') {
                Flash::successQuiet('Categoría actualizada.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            } else {
                Flash::successQuiet('Categoría actualizada.');
                header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
            }
            exit;
        } else {
            $user = Auth::user();
            if (($user['role'] ?? '') === 'Supervisor') {
                Flash::error('No se pudo eliminar la categoría.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            } else {
                Flash::error('No se pudo eliminar la categoría.');
                header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
            }
            exit;
        }
    }

    
    public function mostrarActualizarCategoria()
    {
        
        Auth::checkRole(['Administrador', 'Supervisor']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $user = Auth::user();
            if (($user['role'] ?? '') === 'Supervisor') {
                Flash::error('ID de categoría inválido.');
                header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            } else {
                Flash::error('ID de categoría inválido.');
                header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
            }
            exit;
        }
        $user = Auth::user();
        $backUrl = (($user['role'] ?? '') === 'Supervisor')
            ? '/ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario'
            : '/ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias';
        $categoria = $this->categoryModel->obtenerCategoryPorId($id);
        include_once __DIR__ . '/../Views/Inventario/ActualizarCategoria.php';
    }

    
    public function editarCategoria()
    {
        
        Auth::checkRole(['Administrador', 'Supervisor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? '';
            if ($this->categoryModel->actualizarCategory($id, $name)) {
                $user = Auth::user();
                $accion = "Edición de categoría de inventario";
                $detalle = "{$user['name']} renombró/ajustó la categoría '{$name}' (ID {$id}).";
                $this->historialController->agregarAccion($accion, $detalle);
                if (($user['role'] ?? '') === 'Supervisor') {
                    Flash::successQuiet('Categoría eliminada.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                } else {
                    Flash::successQuiet('Categoría eliminada.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
                }
                exit;
            } else {
                $user = Auth::user();
                if (($user['role'] ?? '') === 'Supervisor') {
                    Flash::error('No se pudo actualizar la categoría.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
                } else {
                    Flash::error('No se pudo actualizar la categoría.');
                    header('Location: /ProyectoPandora/Public/index.php?route=Inventario/ListarCategorias');
                }
                exit;
            }
        }
        $this->mostrarActualizarCategoria();
    }

    
    public function actualizarCategoria()
    {
        
    Middleware::requireRoles(['Supervisor']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            Flash::error('ID inválido.');
            header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario');
            exit;
        }
    $item = $this->inventarioModel->obtenerPorId($id);
    $categorias = $this->inventarioModel->listarCategorias();
    header('Location: /ProyectoPandora/Public/index.php?route=Supervisor/GestionInventario&id=' . urlencode((string)$id));
    exit;
    }
}

?>