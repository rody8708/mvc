<?php
// app/app/db/LicenseDatabase.php

namespace App\Db;

use PDO;

class LicenseDatabase
{
    // Ruta absoluta basada en el directorio actual de este archivo
    private const DB_PATH_LICENSE = __DIR__ . '/invoices_license.sqlite';

    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'sqlite:' . self::DB_PATH_LICENSE;

            self::$pdo = new PDO($dsn);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            self::$pdo->exec('PRAGMA foreign_keys = ON;');
        }

        return self::$pdo;
    }
}
