<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Logger;

class Model {
    protected static $db = null; // Conexión compartida (singleton)

    public function __construct() {
        if (self::$db === null) {
            try {
                self::$db = new PDO("sqlite:" . DB_PATH);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // ✅ Registrar conexión exitosa
                //Logger::info("Conexión a la base de datos establecida.");
            } catch (PDOException $e) {
                // ❌ Registrar error de conexión
                Logger::error("Error al conectar a la base de datos: " . $e->getMessage());
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
    }

    /**
     * Obtener la instancia PDO
     */
    public static function getDb() {
        if (self::$db === null) {
            // Forzar la conexión si no existe
            new static(); // Esto ejecuta el constructor y establece la conexión
        }
        return self::$db;
    }
}

?>