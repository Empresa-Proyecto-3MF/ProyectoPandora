<?php

class NotificationModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create(
        string $title,
        string $body,
        string $audience = 'ALL',
        ?string $role = null,
        ?int $userId = null,
        ?int $createdBy = null
    ): ?int {
        $stmt = $this->conn->prepare(
            'INSERT INTO notifications (title, body, audience, audience_role, target_user_id, created_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('ssssii', $title, $body, $audience, $role, $userId, $createdBy);
        if (!$stmt->execute()) {
            return null;
        }

        $notifId = (int) $this->conn->insert_id;

        if ($audience === 'ALL') {
            $result = $this->conn->query('SELECT id FROM users');
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $this->attachNotificationUser($notifId, (int) $row['id']);
                }
            }
        } elseif ($audience === 'ROLE' && $role) {
            $stmtMap = $this->conn->prepare('SELECT id FROM users WHERE role = ?');
            if ($stmtMap) {
                $stmtMap->bind_param('s', $role);
                if ($stmtMap->execute()) {
                    $res = $stmtMap->get_result();
                    while ($row = $res->fetch_assoc()) {
                        $this->attachNotificationUser($notifId, (int) $row['id']);
                    }
                }
                $stmtMap->close();
            }
        } elseif ($audience === 'USER' && $userId) {
            $this->attachNotificationUser($notifId, (int) $userId);
        }

        return $notifId;
    }

    public function listForUser(int $userId, string $role, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->conn->prepare(
            "SELECT n.id, n.title, n.body, n.created_at,
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
             LIMIT ? OFFSET ?"
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('isiii', $userId, $role, $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countUnread(int $userId, string $role): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS c
             FROM notifications n
             LEFT JOIN notification_user nu
               ON nu.notification_id = n.id AND nu.user_id = ?
             WHERE (
                 n.audience = 'ALL'
                 OR (n.audience = 'ROLE' AND n.audience_role = ?)
                 OR (n.audience = 'USER' AND n.target_user_id = ?)
             ) AND COALESCE(nu.is_read, 0) = 0"
        );
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('isi', $userId, $role, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['c'] ?? 0);
    }

    public function markRead(int $userId, int $notificationId): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO notification_user (notification_id, user_id, is_read, read_at)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $notificationId, $userId);
        return $stmt->execute();
    }

    private function attachNotificationUser(int $notifId, int $userId): void
    {
        $stmt = $this->conn->prepare('INSERT IGNORE INTO notification_user (notification_id, user_id) VALUES (?, ?)');
        if (!$stmt) {
            return;
        }

        $stmt->bind_param('ii', $notifId, $userId);
        $stmt->execute();
        $stmt->close();
    }
}
