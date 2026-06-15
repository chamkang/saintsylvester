<?php
require_once __DIR__ . '/../config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (SSMF_DB_DRIVER === 'mysql') {
        $pdo = new PDO(
            'mysql:host=' . SSMF_MYSQL_HOST . ';dbname=' . SSMF_MYSQL_NAME . ';charset=utf8mb4',
            SSMF_MYSQL_USER, SSMF_MYSQL_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        $dir = dirname(SSMF_SQLITE_PATH);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $pdo = new PDO('sqlite:' . SSMF_SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

function db_ready(): bool {
    try {
        db()->query("SELECT 1 FROM services LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
