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

    // 🔥 Load the user JSON only once
    protected static function loadUserJson() {
        if (self::$userJson === null && isset($_SESSION['user']['id'])) {
            self::$userJson = Functions::getUserJson($_SESSION['user']['id']);            
        }
    }

    // 🔥 Get the role ID
    public static function roleId() {
        self::loadUserJson();
        return self::$userJson['role_id'] ?? 1; // 🔥 1 = default user

    }

    // 🔥 Get the role name
    public static function roleName() {
        $roleId = self::roleId();
        return self::ROLES[$roleId] ?? 'unknown';
    }

    // 🔥 Check if Admin
    public static function isAdmin() {        
        return self::roleId() == 2;
    }

    // 🔥 Check if Editor
    public static function isEditor() {
        return self::roleId() == 3;
    }

    // 🔥 Check if Supervisor
    public static function isSupervisor() {
        return self::roleId() == 4;
    }

    // 🔥 Check if Moderator
    public static function isModerator() {
        return self::roleId() == 5;
    }

    // 🔥 Check if normal user
    public static function isUser() {
        return self::roleId() == 1;
    }
}
?>