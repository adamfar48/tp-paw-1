<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

$today = date('Y-m-d');

// Fetch all students
$studentsStmt = $pdo->query("SELECT student_id FROM students");
$students = $studentsStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch attendance for today
$totalAbs = 0;
$totalPar = 0;

$attStmt = $pdo->prepare("SELECT status FROM attendance WHERE student_id=? AND date=?");
foreach($students as $student_id){
    $attStmt->execute([$student_id, $today]);
    $records = $attStmt->fetchAll(PDO::FETCH_COLUMN);
    foreach($records as $status){
        if($status === 'present') $totalPar++;
        else $totalAbs++;
    }
}

$count = count($students);
$avgPar = $count > 0 ? round($totalPar / $count, 2) : 0;

echo json_encode([
    'totalStudents' => $count,
    'totalAbsences' => $totalAbs,
    'averageParticipation' => $avgPar,
]);
