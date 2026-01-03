<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LogModel extends Model {
    
    public function getLogs($perPage, $offset) {
        $stmt = self::getDb()->prepare("SELECT * FROM logs ORDER BY id DESC LIMIT :perPage OFFSET :offset");
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countLogs() {
            $stmt = self::getDb()->query("SELECT COUNT(*) FROM logs");
            return $stmt->fetchColumn();
        }

    public function getFilteredLogs($limit, $offset, $filters = []) {
        $sql = "SELECT * FROM logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user'])) {
            $sql .= " AND user LIKE :user";
            $params[':user'] = '%' . $filters['user'] . '%';
        }

        if (!empty($filters['level'])) {
            $sql .= " AND level = :level";
            $params[':level'] = $filters['level'];
        }

        if (!empty($filters['ip'])) {
            $sql .= " AND ip = :ip";
            $params[':ip'] = $filters['ip'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action LIKE :action";
            $params[':action'] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(timestamp) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(timestamp) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY timestamp DESC LIMIT :limit OFFSET :offset";

        $stmt = self::getDb()->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFilteredLogs($filters = []) {
        $sql = "SELECT COUNT(*) FROM logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user'])) {
            $sql .= " AND user LIKE :user";
            $params[':user'] = '%' . $filters['user'] . '%';
        }

        if (!empty($filters['level'])) {
            $sql .= " AND level = :level";
            $params[':level'] = $filters['level'];
        }

        if (!empty($filters['ip'])) {
            $sql .= " AND ip = :ip";
            $params[':ip'] = $filters['ip'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action LIKE :action";
            $params[':action'] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(timestamp) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(timestamp) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $stmt = self::getDb()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getAllLogs() {
        $stmt = self::getDb()->query("SELECT id, user, ip, os, browser, action, level, timestamp FROM logs ORDER BY timestamp DESC");
        return $stmt->fetchAll();
    }


}

?>