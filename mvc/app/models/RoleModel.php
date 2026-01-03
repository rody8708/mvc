<?php

namespace App\Models;

use App\Core\Model;

class RoleModel extends Model {

    public function getAllRoles() {
        $stmt = self::getDb()->query("SELECT * FROM roles");
        return $stmt->fetchAll();
    }

    public function getRoleById($id) {
        $stmt = self::getDb()->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createRole($name) {
        $stmt = self::getDb()->prepare("INSERT INTO roles (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function deleteRole($id) {
        $stmt = self::getDb()->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }
}