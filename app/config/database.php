<?php
/**
 * database.php — PDO Connection Helper
 * Singleton PDO instance untuk MariaDB / MySQL
 */

require_once __DIR__ . '/env.php';

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host    = env('DB_HOST',     'simou-db');
    $port    = (int) env('DB_PORT', 3306);
    $dbname  = env('DB_DATABASE', 'simou_db');
    $user    = env('DB_USERNAME', 'simou_user');
    $pass    = env('DB_PASSWORD', 'simou_password');

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    } catch (PDOException $e) {
        error_log('[SiMoU DB] Connection failed: ' . $e->getMessage());
        http_response_code(503);
        $msg = env('APP_ENV') === 'development'
            ? 'Database connection failed: ' . $e->getMessage()
            : 'Database connection failed. Please try again later.';
        die(json_encode(['error' => $msg]));
    }

    return $pdo;
}
