<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

$stmt = $pdo->query("SELECT * FROM students ORDER BY student_id ASC");
$students = $stmt->fetchAll();
?>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Matricule</th>
        <th>Group</th>
    </tr>
    <?php foreach ($students as $s): ?>
    <tr>
        <td><?= $s['student_id'] ?></td>
        <td><?= htmlspecialchars($s['fullname']) ?></td>
        <td><?= htmlspecialchars($s['matricule']) ?></td>
        <td><?= htmlspecialchars($s['group_id']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
