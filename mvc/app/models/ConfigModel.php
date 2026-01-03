<?php

namespace App\Models;

use App\Core\Model;

class ConfigModel extends Model {

    public function getAllSettings() {
        $stmt = self::getDb()->query("SELECT * FROM settings");
        return $stmt->fetchAll();
    }

    public function updateSetting($key, $value) {
        $stmt = self::getDb()->prepare("UPDATE settings SET value = ? WHERE key = ?");
        return $stmt->execute([$value, $key]);
    }

    public function getSetting($key) {
        $stmt = self::getDb()->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }
}