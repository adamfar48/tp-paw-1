<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

$today = date('Y-m-d');

// Fetch all students
$stmt = $pdo->query("SELECT student_id, fullname, matricule, group_id FROM students ORDER BY student_id");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's attendance
$attStmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE date = ?");
$attStmt->execute([$today]);
$attendanceData = $attStmt->fetchAll(PDO::FETCH_ASSOC);

// Map student_id => attendance
$attendanceMap = [];
foreach($attendanceData as $a){
    $attendanceMap[$a['student_id']] = $a['status'] === 'present' ? true : false;
}

// Merge attendance into student data
foreach($students as &$s){
    $s['attendance'] = [];
    for($i=0; $i<6; $i++){ // assume 6 sessions
        $s['attendance'][$i] = $attendanceMap[$s['student_id']] ?? false;
    }
    $s['participation'] = $s['attendance']; // if participation same as attendance
}

echo json_encode($students);
?>
