<?php

// PHP-6: Pool PDO único + opciones seguras (prepares reales, exceptions, utf8mb4).
// Antes: una conexión nueva por query. Ahora: PDO singleton.
class Db {
    private static $pdo = null;

    public static function pdo() {
        if (self::$pdo === null) {
            $dbhost = dbhost;
            $dbname = dbname;
            $dbuser = dbuser;
            $dbpass = dbpass;
            try {
                self::$pdo = new PDO(
                    "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
                    $dbuser,
                    $dbpass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    ]
                );
            } catch (PDOException $e) {
                if (function_exists('logWithRequestId')) {
                    logWithRequestId('DB connect fail: ' . $e->getMessage());
                } else {
                    error_log('DB connect fail: ' . $e->getMessage());
                }
                http_response_code(500);
                die('Database unavailable');
            }
        }
        return self::$pdo;
    }
}

function conexion($sql){
    return Db::pdo()->prepare($sql);
}

function query($sql, $get = '', $params = []) {
    try {
        $stmt = conexion($sql);
        if (!is_array($params)) $params = [];
        $stmt->execute($params);

        $get = strtoupper($get);
        switch ($get) {
            case 'ALL':
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            case 'COLUMNS':
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            case 'ARRAY':
                return $stmt->fetch(PDO::FETCH_ASSOC);
            case 'ARRAY_ALL':
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            case 'ERROR_JSON':
                JSON($stmt->errorInfo(), 400);
                return false;
            case 'ERROR':
                var_dump($stmt->errorInfo());
                return false;
            default:
                return $stmt->fetch(PDO::FETCH_OBJ);
        }
    } catch (PDOException $e) {
        if (function_exists('logWithRequestId')) {
            logWithRequestId("Database error: " . $e->getMessage(), ['sql' => $sql]);
        } else {
            error_log("Database error: " . $e->getMessage() . " | SQL: " . $sql);
        }
        if ($get === 'ERROR_JSON') {
            JSON(['error' => 'Database error'], 400);
        }
        return false;
    }
}
