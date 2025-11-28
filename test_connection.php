<?php
require_once 'db_connect.php';

try {
    $pdo = getDbConnection();
    echo "✅ Connection successful!";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
