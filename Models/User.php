<?php

require_once __DIR__ . '../../Core/Database.php';

class UserModel
{
    private $connection;

    public function __construct($dbConnection)
    {
        $this->connection = $dbConnection;
    }

    public function createUser(string $username, string $email, string $password, string $role = 'Cliente'): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('ssss', $username, $email, $hash, $role);

        return $stmt->execute();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function updateRole(int $userId, string $newRole): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET role = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $newRole, $userId);
        return $stmt->execute();
    }

    public function getAllUsers(): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users');
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllClientes(): array
    {
        $sql = 'SELECT c.id, u.id AS user_id, u.name, u.role, u.created_at, u.email
                FROM clientes c
                INNER JOIN users u ON c.user_id = u.id';

        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllTecnicos(): array
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

    public function getAllSupervisores(): array
    {
        $sql = 'SELECT u.id, u.name, u.email, u.role, u.created_at
                FROM users u
                INNER JOIN supervisores s ON u.id = s.user_id';

        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllAdministradores(): array
    {
        $sql = 'SELECT u.id, u.name, u.email, u.role, u.created_at
                FROM users u
                INNER JOIN administradores a ON u.id = a.user_id';

        $result = $this->connection->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function updateUser(int $userId, string $name, string $email, string $role): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sssi', $name, $email, $role, $userId);
        return $stmt->execute();
    }

    public function deleteUser(int $userId): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM users WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);
        return $stmt->execute();
    }

    public function setTecnicoEstado(int $tecnicoId, string $estado): bool
    {
        $stmt = $this->connection->prepare('UPDATE tecnicos SET disponibilidad = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $estado, $tecnicoId);
        return $stmt->execute();
    }

    public function actualizarPerfil(int $id, string $name, string $email, ?string $imgPerfil): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET name = ?, email = ?, img_perfil = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sssi', $name, $email, $imgPerfil, $id);
        return $stmt->execute();
    }

    public function registerIfNotExists(string $username, string $email, string $password, string $role = 'Cliente'): string
    {
        if ($this->findByEmail($email)) {
            return 'exists';
        }

        return $this->createUser($username, $email, $password, $role) ? 'ok' : 'error';
    }

    public function storeRememberToken(int $userId, string $selector, string $tokenHash, string $expiresAt): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET remember_selector = ?, remember_token = ?, remember_expires_at = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sssi', $selector, $tokenHash, $expiresAt, $userId);
        return $stmt->execute();
    }

    public function clearRememberToken(int $userId): bool
    {
        $stmt = $this->connection->prepare('UPDATE users SET remember_selector = NULL, remember_token = NULL, remember_expires_at = NULL WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);
        return $stmt->execute();
    }

    public function findByRememberSelector(string $selector): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE remember_selector = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $selector);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function setResetCodeByEmail(string $email, string $code, string $expiresAt): bool
    {
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare('UPDATE users SET reset_code = ?, reset_expires_at = ?, reset_attempts = 0, reset_locked_until = NULL WHERE email = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sss', $hash, $expiresAt, $email);
        return $stmt->execute();
    }

    public function verifyResetCode(string $email, string $code): array
    {
        $stmt = $this->connection->prepare('SELECT id, reset_code, reset_expires_at, reset_attempts, reset_locked_until FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            return ['ok' => false, 'reason' => 'db-error'];
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user) {
            return ['ok' => false, 'reason' => 'not-found'];
        }

        if (empty($user['reset_code']) || empty($user['reset_expires_at'])) {
            return ['ok' => false, 'reason' => 'no-request'];
        }

        $now = new DateTime('now');
        $expires = new DateTime($user['reset_expires_at']);
        if ($now > $expires) {
            return ['ok' => false, 'reason' => 'expired'];
        }

        if (!empty($user['reset_locked_until'])) {
            $lockedUntil = new DateTime($user['reset_locked_until']);
            if ($now < $lockedUntil) {
                return ['ok' => false, 'reason' => 'locked'];
            }
        }

        if (password_verify($code, (string) $user['reset_code'])) {
            $this->clearResetAttempts((int) $user['id']);
            return ['ok' => true, 'reason' => 'ok'];
        }

        $attempts = (int) ($user['reset_attempts'] ?? 0) + 1;
        $this->incrementResetAttempts((int) $user['id']);

        if ($attempts >= 5) {
            $lockUntil = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
            $this->setLockUntil((int) $user['id'], $lockUntil);
            return ['ok' => false, 'reason' => 'locked'];
        }

        return ['ok' => false, 'reason' => 'invalid'];
    }

    private function incrementResetAttempts(int $userId): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET reset_attempts = COALESCE(reset_attempts, 0) + 1 WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        }
    }

    private function clearResetAttempts(int $userId): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET reset_attempts = 0, reset_locked_until = NULL WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        }
    }

    private function setLockUntil(int $userId, string $lockUntil): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET reset_locked_until = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $lockUntil, $userId);
            $stmt->execute();
        }
    }

    public function updatePasswordByEmail(string $email, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare(
            'UPDATE users SET password = ?, reset_code = NULL, reset_expires_at = NULL, reset_attempts = 0, reset_locked_until = NULL WHERE email = ?'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ss', $hash, $email);
        return $stmt->execute();
    }
}

