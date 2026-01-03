<?php
namespace App\Models;

use App\Core\Model;

class NotificationModel extends Model {

    public function getUserNotifications($userId) {
        $sql = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at desc";

        $stmt = self::getDb()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead($id) {
        $stmt = self::getDb()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function create($user_id, $message, $level = 'info') {
        $stmt = self::getDb()->prepare("INSERT INTO notifications (user_id, message, level) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $message, $level]);
    }

    public function markAllAsRead($userId) {
        $stmt = self::getDb()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    public function getAllNotifications() {
        $stmt = self::getDb()->query("SELECT id, user_id, message, level, is_read, created_at FROM notifications ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

}
