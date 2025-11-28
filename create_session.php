<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $group_id  = $_POST['group_id'] ?? null;

    if (!$course_id || !$group_id) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $date = date("Y-m-d");

    try {
        // 1️⃣ Create new session
        $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, status) VALUES (?, ?, ?, 'opened')");
        $stmt->execute([$course_id, $group_id, $date]);
        $session_id = $pdo->lastInsertId();

        // 2️⃣ Fetch all students in this group
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE group_id = ?");
        $stmt->execute([$group_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3️⃣ Initialize attendance for each student as 'absent'
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, status, date) VALUES (?, 'absent', ?)");
        foreach ($students as $s) {
            $stmt->execute([$s['student_id'], $date]);
        }

        echo json_encode([
            'success' => true,
            'session_id' => $session_id,
            'students_initialized' => count($students)
        ]);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
