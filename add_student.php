<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $matricule = trim($_POST["matricule"] ?? "");
    $group_id = trim($_POST["group_id"] ?? "");

    $errors = [];
    if($fullname === "" || !preg_match("/^[A-Za-z ]+$/", $fullname)) $errors[] = "Full name is invalid.";
    if($matricule === "") $errors[] = "Matricule is required.";
    if($group_id === "") $errors[] = "Group is required.";

    if(!empty($errors)) {
        foreach($errors as $e) echo "<p style='color:red;'>$e</p>";
        echo "<a href='index.php'>Go back</a>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)");
        $stmt->execute([$fullname, $matricule, $group_id]);
        echo "<p style='color:green;'>✅ Student added successfully!</p>";
        echo "<a href='index.php'>Go back</a>";
    } catch (PDOException $e) {
        if($e->getCode() == 23000) echo "<p style='color:red;'>Matricule already exists.</p>";
        else echo "<p style='color:red;'>Database error: " . $e->getMessage() . "</p>";
        echo "<a href='index.php'>Go back</a>";
    }
}
?>
