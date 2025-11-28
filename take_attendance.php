<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

date_default_timezone_set('UTC');
$today = date("Y-m-d");

// Check if attendance for today exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
if ($stmt->fetchColumn() > 0) {
    echo "<p style='color:red; font-size:18px;'>❌ Attendance for today has already been taken.</p>";
    echo "<a href='index.php'>⬅ Back to Home</a>";
    exit;
}

// Load students
$stmt = $pdo->query("SELECT * FROM students ORDER BY student_id");
$students = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($students as $s) {
        $status = $_POST['status'][$s['student_id']] ?? 'absent';

        // Participation only counts if present
        $participation = ($status === 'present' && isset($_POST['participation'][$s['student_id']])) ? 1 : 0;

        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, date, session, status, participation)
            VALUES (?, ?, 1, ?, ?)
        ");
        $stmt->execute([$s['student_id'], $today, $status, $participation]);
    }

    echo "<p style='color:green; font-size:18px;'>✅ Attendance saved successfully for {$today}.</p>";
    echo "<a href='index.php'>⬅ Back to Home</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Take Attendance</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Take Attendance - <?= $today ?></h1>

<form method="post" action="">
    <table border="1" cellpadding="8">
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Group</th>
            <th>Status</th>
            <th>Participation</th>
        </tr>

        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td><?= htmlspecialchars($s['fullname']) ?></td>
            <td><?= htmlspecialchars($s['group_id']) ?></td>

            <!-- Present / Absent -->
            <td>
                <label><input type="radio" name="status[<?= $s['student_id'] ?>]" value="present" checked> Present</label>
                <label><input type="radio" name="status[<?= $s['student_id'] ?>]" value="absent"> Absent</label>
            </td>

            <!-- Participation checkbox -->
            <td style="text-align:center;">
                <input type="checkbox" name="participation[<?= $s['student_id'] ?>]"> Participated
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <button type="submit">Submit Attendance</button>
</form>
</body>
</html>
