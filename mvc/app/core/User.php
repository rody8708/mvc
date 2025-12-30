<?php
namespace App\Core;

use App\Core\Functions;
use App\Core\Logger;


class User
{
    const ROLES = [
        1 => 'user',
        2 => 'admin',
        3 => 'editor',
        4 => 'supervisor',
        5 => 'moderator',
    ];

    protected static $userJson = null;

    // 🔥 Cargar el JSON del usuario una sola vez
    protected static function loadUserJson() {
        if (self::$userJson === null && isset($_SESSION['user']['id'])) {
            self::$userJson = Functions::getUserJson($_SESSION['user']['id']);            
        }
    }

    // 🔥 Obtener el ID del rol
    public static function roleId() {
        self::loadUserJson();
        return self::$userJson['role_id'] ?? 1; // 🔥 1 = user por defecto

    }

    // 🔥 Obtener el nombre del rol
    public static function roleName() {
        $roleId = self::roleId();
        return self::ROLES[$roleId] ?? 'unknown';
    }

    // 🔥 Verificar si es Admin
    public static function isAdmin() {        
        return self::roleId() == 2;
    }

    // 🔥 Verificar si es Editor
    public static function isEditor() {
        return self::roleId() == 3;
    }

    // 🔥 Verificar si es Supervisor
    public static function isSupervisor() {
        return self::roleId() == 4;
    }

    // 🔥 Verificar si es Moderador
    public static function isModerator() {
        return self::roleId() == 5;
    }

    // 🔥 Verificar si es usuario normal
    public static function isUser() {
        return self::roleId() == 1;
    }
}
?>