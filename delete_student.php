<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->execute([$id]);
    echo "✅ Student deleted successfully!";
}
