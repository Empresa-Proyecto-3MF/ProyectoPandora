<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Controllers/HistorialController.php';

class RegisterController
{
    private $historialController;

    public function __construct()
    {
        $this->historialController = new HistorialController();
    }

    public function Register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            // Requisito: nombre obligatorio
            if ($username === '') {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/Register&error=NombreRequerido');
                exit;
            }

            // Requisito: prohibir espacios/blancos en contraseña
            if (preg_match('/\s/', (string)$password)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/Register&error=PasswordEspacios');
                exit;
            }
            // Requisito: contraseña mínima de 8 caracteres
            if (strlen((string)$password) < 8) {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/Register&error=PasswordCorta');
                exit;
            }

            // Validación de email delegada al cliente (JS); servidor solo controla duplicados

            $result = $this->RegisterUser($username, $email, $password);
            
            $accion = "Registro de usuario";
            $detalle = "Se creó la cuenta de {$username} (email {$email}). Resultado: {$result}.";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=Auth/Login');
            exit;
        } else {
            include_once __DIR__ . '/../Views/Auth/Register.php';
        }
    }

    public function RegisterAdmin()
    {
        // Solo un Administrador puede acceder a esta acción (GET/POST)
        Auth::checkRole('Administrador');
        $user = Auth::user();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Cliente';

            // Requisito: nombre obligatorio
            if ($username === '') {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/RegisterAdmin&error=NombreRequerido');
                exit;
            }

            // Requisito: prohibir espacios/blancos en contraseña
            if (preg_match('/\s/', (string)$password)) {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/RegisterAdmin&error=PasswordEspacios');
                exit;
            }
            // Requisito: contraseña mínima de 8 caracteres
            if (strlen((string)$password) < 8) {
                header('Location: /ProyectoPandora/Public/index.php?route=Register/RegisterAdmin&error=PasswordCorta');
                exit;
            }

            // Validación de email delegada al cliente (JS); servidor solo controla duplicados

            $result = $this->RegisterUserWithRole($username, $email, $password, $role);

            $accion = "Registro de usuario por administrador";
            $detalle = "Se creó la cuenta {$username} (email {$email}) con rol {$role} desde el panel de administración. Resultado: {$result}.";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: /ProyectoPandora/Public/index.php?route=Admin/ListarUsers');
            exit;
        } else {
            include_once __DIR__ . '/../Views/Admin/Register.php';
        }
    }

    function RegisterUser($username, $email, $password)
    {
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());
        $role = ($email === 'admin@admin.com') ? 'Administrador' : 'Cliente';
        $res = $userModel->registerIfNotExists($username, $email, $password, $role);
        if ($res === 'exists') {
            header("Location: /ProyectoPandora/Public/index.php?route=Register/Register&error=EmailYaRegistrado");
            exit;
        }
        return $res === 'ok' ? 'User registered successfully.' : 'Error registering user.';
    }

    public function RegisterUserWithRole($username, $email, $password, $role)
    {
        // Defensa en profundidad: aunque esta ruta ya está protegida, validamos aquí también
        Auth::checkRole('Administrador');
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());

        $res = $userModel->registerIfNotExists($username, $email, $password, $role);
        if ($res === 'exists') {
            // Simples y consistentes: volver al formulario de registro admin con error
            header("Location: /ProyectoPandora/Public/index.php?route=Register/RegisterAdmin&error=EmailYaRegistrado");
            exit;
        }

        if ($res === 'ok') {
            
            if (strcasecmp($role, 'Tecnico') === 0) {
                require_once __DIR__ . '/../Models/Rating.php';
                
                $newUser = $userModel->findByEmail($email);
                if ($newUser) {
                    
                    $conn = $db->getConnection();
                    
                    $stmtT = $conn->prepare("SELECT id FROM tecnicos WHERE user_id = ? LIMIT 1");
                    if ($stmtT) {
                        $stmtT->bind_param('i', $newUser['id']);
                        $stmtT->execute();
                        $tec = $stmtT->get_result()->fetch_assoc();
                        if ($tec && isset($tec['id'])) {
                            $ratingM = new RatingModel($conn);
                            
                            @$conn->query("INSERT IGNORE INTO ticket_ratings (ticket_id, tecnico_id, cliente_id, stars, comment) VALUES (0, ".(int)$tec['id'].", 0, 3, 'Seed inicial 3★')");
                        }
                    }
                }
            }
            return "Usuario registrado correctamente con rol: $role";
        } else {
            return "Error al registrar usuario.";
        }
    }

    // Nota: validación de email realizada en el cliente (JS). Aquí solo se controla existencia y creación.
}
