<?php

class NotificationModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crear notificación; audience: ALL | ROLE | USER
    public function create(string $title, string $body, string $audience = 'ALL', ?string $role = null, ?int $userId = null, ?int $createdBy = null): ?int
    {
        $sql = "INSERT INTO notifications (title, body, audience, audience_role, target_user_id, created_by) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('ssssii', $title, $body, $audience, $role, $userId, $createdBy);
        if (!$stmt->execute()) return null;
        $notifId = (int)$this->conn->insert_id;

        // Pre-popular notification_user para performance de contadores
        if ($audience === 'ALL') {
            $q = $this->conn->query("SELECT id FROM users");
            while ($row = $q && $q->fetch_assoc() ? $q->fetch_assoc() : null) {}
        }
        return $notifId;
    }

    public function listForUser(int $userId, string $role, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT n.id, n.title, n.body, n.created_at,
                       COALESCE(nu.is_read, 0) AS is_read
                FROM notifications n
                LEFT JOIN notification_user nu
                  ON nu.notification_id = n.id AND nu.user_id = ?
                WHERE (
                    n.audience = 'ALL'
                    OR (n.audience = 'ROLE' AND n.audience_role = ?)
                    OR (n.audience = 'USER' AND n.target_user_id = ?)
                )
                ORDER BY n.id DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('isiii', $userId, $role, $userId, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        return $data;
    }

    public function countUnread(int $userId, string $role): int
    {
        $sql = "SELECT COUNT(*) AS c
                FROM notifications n
                LEFT JOIN notification_user nu
                  ON nu.notification_id = n.id AND nu.user_id = ?
                WHERE (
                    n.audience = 'ALL'
                    OR (n.audience = 'ROLE' AND n.audience_role = ?)
                    OR (n.audience = 'USER' AND n.target_user_id = ?)
                ) AND COALESCE(nu.is_read, 0) = 0";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param('isi', $userId, $role, $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['c'] ?? 0);
    }

    public function markRead(int $userId, int $notificationId): bool
    {
        // Upsert sencillo
        $sql = "INSERT INTO notification_user (notification_id, user_id, is_read, read_at)
                VALUES (?,?,1,NOW())
                ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ii', $notificationId, $userId);
        return $stmt->execute();
    }
}
