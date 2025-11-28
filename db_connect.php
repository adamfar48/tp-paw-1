<?php
require_once 'config.php';

function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);

    } catch (PDOException $e) {
        // Log errors (optional)
        file_put_contents('db_errors.log', date('Y-m-d H:i:s') . " - Connection failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        die("Database connection failed. Please try again later.");
    }
}
