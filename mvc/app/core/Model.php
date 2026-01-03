<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Logger;

class Model {
    protected static $db = null; // Shared connection (singleton)

    public function __construct() {
        if (self::$db === null) {
            try {
                self::$db = new PDO("sqlite:" . DB_PATH);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // ✅ Log successful connection
                //Logger::info("Database connection established.");
            } catch (PDOException $e) {
                // ❌ Log connection error
                Logger::error("Error connecting to the database: " . $e->getMessage());
                die("Database connection error: " . $e->getMessage());
            }
        }
    }

    /**
     * Get the PDO instance
     */
    public static function getDb() {
        if (self::$db === null) {
            // Force the connection if it does not exist
            new static(); // This executes the constructor and establishes the connection
        }
        return self::$db;
    }
}

?>