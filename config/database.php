<?php
/**
 * NOXARA - Database Singleton (MySQLi)
 */

class Database {
    private static ?mysqli $instance = null;

    public static function getInstance(): mysqli {
        if (self::$instance === null) {
            self::$instance = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            if (self::$instance->connect_error) {
                error_log('DB Connection Error: ' . self::$instance->connect_error);
                die('Koneksi database gagal. Silakan hubungi administrator.');
            }
            self::$instance->set_charset('utf8mb4');
            self::$instance->query("SET time_zone = '+07:00'");
        }
        return self::$instance;
    }

    public static function close(): void {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }
}

function db(): mysqli {
    return Database::getInstance();
}
