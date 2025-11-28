<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['student_id'];
    $fullname = trim($_POST['fullname']);
    $matricule = trim($_POST['matricule']);
    $group_id = trim($_POST['group_id']);

    $stmt = $pdo->prepare("UPDATE students SET fullname=?, matricule=?, group_id=? WHERE student_id=?");
    $stmt->execute([$fullname, $matricule, $group_id, $id]);
    echo "✅ Student updated successfully!";
}
