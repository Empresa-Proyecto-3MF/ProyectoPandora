<?php
require_once __DIR__ . '../../Core/Database.php';
class UserModel
{

    private $connection;

    public function __construct($dbConnection)
    {
        $this->connection = $dbConnection;
    }

    public function createUser($username, $email, $password, $role = 'Cliente')
    {
        $stmt = $this->connection->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }
    public function findByEmail($email)
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }
    public function updateRole($userId, $newRole)
    {
        $stmt = $this->connection->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $newRole, $userId);
            return $stmt->execute();
        }
        return false;
    }
    public function getAllUsers()
    {
        $stmt = $this->connection->prepare("SELECT * FROM users");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    public function getAllClientes()
    {
        $sql = "SELECT c.id, u.id AS user_id, u.name,  u.role, u.created_at, u.email
            FROM clientes c
            INNER JOIN users u ON c.user_id = u.id";
        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllTecnicos()
    {
        $sql = "SELECT 
                    t.id,
                    t.user_id,
                    u.name,
                    u.email,
                    u.role,
                    u.created_at,
                    u.img_perfil,
                    t.disponibilidad,
                    t.especialidad,
                    COALESCE(ta.cantidad, 0) AS tickets_asignados,
                    COALESCE(tabiertos.cantidad, 0) AS tickets_activos
                FROM tecnicos t 
                INNER JOIN users u ON t.user_id = u.id
                LEFT JOIN (
                    SELECT tecnico_id, COUNT(*) AS cantidad
                    FROM tickets
                    WHERE tecnico_id IS NOT NULL
                    GROUP BY tecnico_id
                ) ta ON ta.tecnico_id = t.id
                LEFT JOIN (
                    SELECT tecnico_id, COUNT(*) AS cantidad
                    FROM tickets
                    WHERE tecnico_id IS NOT NULL AND fecha_cierre IS NULL
                    GROUP BY tecnico_id
                ) tabiertos ON tabiertos.tecnico_id = t.id";
        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllSupervisores()
    {
        $sql = "SELECT u.id, u.name, u.email, u.role, u.created_at
            FROM users u
            INNER JOIN supervisores s ON u.id = s.user_id";
        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllAdministradores()
    {
        $sql = "SELECT u.id, u.name, u.email, u.role, u.created_at
            FROM users u
            INNER JOIN administradores a ON u.id = a.user_id";
        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function findById($userId)
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }
    public function updateUser($userId, $name, $email, $role)
    {
        $stmt = $this->connection->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssi", $name, $email, $role, $userId);
            return $stmt->execute();
        }
        return false;
    }
    public function deleteUser($userId)
    {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            return $stmt->execute();
        }
        return false;
    }
    public function setTecnicoEstado($tecnico_id, $estado)
    {
        $stmt = $this->connection->prepare("UPDATE tecnicos SET disponibilidad = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $estado, $tecnico_id);
            return $stmt->execute();
        }
        return false;
    }
    public function actualizarPerfil($id, $name, $email, $img_perfil)
    {
        $sql = "UPDATE users SET name = ?, email = ?, img_perfil = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $img_perfil, $id);
        return $stmt->execute();
    }

    



    public function registerIfNotExists(string $username, string $email, string $password, string $role = 'Cliente'): string
    {
        $exists = $this->findByEmail($email);
        if ($exists) return 'exists';
        return $this->createUser($username, $email, $password, $role) ? 'ok' : 'error';
    }

    public function storeRememberToken(int $userId, string $selector, string $tokenHash, string $expiresAt): bool
    {
        $sql = "UPDATE users SET remember_selector = ?, remember_token = ?, remember_expires_at = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sssi", $selector, $tokenHash, $expiresAt, $userId);
        return $stmt->execute();
    }

    public function clearRememberToken(int $userId): bool
    {
        $sql = "UPDATE users SET remember_selector = NULL, remember_token = NULL, remember_expires_at = NULL WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function findByRememberSelector(string $selector): ?array
    {
        $sql = "SELECT * FROM users WHERE remember_selector = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    
    public function setResetCodeByEmail(string $email, string $code, string $expiresAt): bool
    {
        
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET reset_code = ?, reset_expires_at = ?, reset_attempts = 0, reset_locked_until = NULL WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sss", $hash, $expiresAt, $email);
        return $stmt->execute();
    }

    public function verifyResetCode(string $email, string $code): array
    {
        
        $sql = "SELECT id, reset_code, reset_expires_at, reset_attempts FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'reason' => 'db-error'];
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        if (!$user) return ['ok' => false, 'reason' => 'not-found'];

        
        if (empty($user['reset_code']) || empty($user['reset_expires_at'])) {
            return ['ok' => false, 'reason' => 'no-request'];
        }
        $now = new DateTime('now');
        $exp = new DateTime($user['reset_expires_at']);
        if ($now > $exp) {
            return ['ok' => false, 'reason' => 'expired'];
        }

        
        if (!empty($user['reset_locked_until'])) {
            $lockedUntil = new DateTime($user['reset_locked_until']);
            if ($now < $lockedUntil) {
                return ['ok' => false, 'reason' => 'locked'];
            }
        }

        if (password_verify((string)$code, (string)$user['reset_code'])) {
            
            $this->clearResetAttempts((int)$user['id']);
            return ['ok' => true, 'reason' => 'ok'];
        }

        
        $attempts = (int)$user['reset_attempts'];
        $attempts++;
        $this->incrementResetAttempts((int)$user['id']);
        if ($attempts + 1 >= 5) { 
            $lockUntil = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
            $this->setLockUntil((int)$user['id'], $lockUntil);
            return ['ok' => false, 'reason' => 'locked'];
        }
        return ['ok' => false, 'reason' => 'invalid'];
    }

    private function incrementResetAttempts(int $userId): void
    {
        $sql = "UPDATE users SET reset_attempts = COALESCE(reset_attempts,0) + 1 WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }

    private function clearResetAttempts(int $userId): void
    {
        $sql = "UPDATE users SET reset_attempts = 0, reset_locked_until = NULL WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }

    private function setLockUntil(int $userId, string $lockUntil): void
    {
        $sql = "UPDATE users SET reset_locked_until = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $lockUntil, $userId);
            $stmt->execute();
        }
    }

    public function updatePasswordByEmail(string $email, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, reset_code = NULL, reset_expires_at = NULL, reset_attempts = 0, reset_locked_until = NULL WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ss", $hash, $email);
        return $stmt->execute();
    }
}
