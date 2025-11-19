<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Controllers/HistorialController.php';
require_once __DIR__ . '/../Core/Flash.php';

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

            
            if ($username === '') {
                Flash::error('El nombre es obligatorio.');
                header('Location: index.php?route=Register/Register');
                exit;
            }

            
            if (preg_match('/\s/', (string)$password)) {
                Flash::error('La contraseña no puede contener espacios ni caracteres en blanco.');
                header('Location: index.php?route=Register/Register');
                exit;
            }
            
            if (strlen((string)$password) < 8) {
                Flash::error('La contraseña debe tener al menos 8 caracteres.');
                header('Location: index.php?route=Register/Register');
                exit;
            }

            

            $result = $this->RegisterUser($username, $email, $password);
            
            $accion = "Registro de usuario";
            $detalle = "Se creó la cuenta de {$username} (email {$email}). Resultado: {$result}.";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: index.php?route=Auth/Login');
            exit;
        } else {
            include_once __DIR__ . '/../Views/Auth/Register.php';
        }
    }

    public function RegisterAdmin()
    {
        
        Auth::checkRole('Administrador');
        $user = Auth::user();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Cliente';

            
            if ($username === '') {
                Flash::error('El nombre es obligatorio.');
                header('Location: index.php?route=Register/RegisterAdmin');
                exit;
            }

            
            if (preg_match('/\s/', (string)$password)) {
                Flash::error('La contraseña no puede contener espacios ni caracteres en blanco.');
                header('Location: index.php?route=Register/RegisterAdmin');
                exit;
            }
            
            if (strlen((string)$password) < 8) {
                Flash::error('La contraseña debe tener al menos 8 caracteres.');
                header('Location: index.php?route=Register/RegisterAdmin');
                exit;
            }

            

            $result = $this->RegisterUserWithRole($username, $email, $password, $role);

            $accion = "Registro de usuario por administrador";
            $detalle = "Se creó la cuenta {$username} (email {$email}) con rol {$role} desde el panel de administración. Resultado: {$result}.";
            $this->historialController->agregarAccion($accion, $detalle);

            header('Location: index.php?route=Admin/ListarUsers');
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
            Flash::error('El correo electrónico ya está registrado. Por favor, usa otro.');
            header("Location: index.php?route=Register/Register");
            exit;
        }
        return $res === 'ok' ? 'User registered successfully.' : 'Error registering user.';
    }

    public function RegisterUserWithRole($username, $email, $password, $role)
    {
        
        Auth::checkRole('Administrador');
        $db = new Database();
        $db->connectDatabase();
        $userModel = new UserModel($db->getConnection());

        $res = $userModel->registerIfNotExists($username, $email, $password, $role);
        if ($res === 'exists') {
            
            Flash::error('El correo electrónico ya está registrado. Por favor, usa otro.');
            header("Location: index.php?route=Register/RegisterAdmin");
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

    
}
