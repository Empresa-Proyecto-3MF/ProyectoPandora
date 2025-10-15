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

    /**
     * Registra un usuario solo si el email no existe.
     * Retorna 'ok' si creó, 'exists' si ya estaba, 'error' si falló.
     */
    public function registerIfNotExists(string $username, string $email, string $password, string $role = 'Cliente'): string
    {
        $exists = $this->findByEmail($email);
        if ($exists) return 'exists';
        return $this->createUser($username, $email, $password, $role) ? 'ok' : 'error';
    }
}
