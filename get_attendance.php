<?php
require 'db_connect.php';

$date = date('Y-m-d');

try {
    $stmt = $conn->query("SELECT student_id, session, status, type FROM attendance WHERE date='$date'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach($rows as $row) {
        $id = $row['student_id'];
        if(!isset($result[$id])) {
            $result[$id] = [
                'student_id' => $id,
                'attendance' => array_fill(0,6,false),
                'participation' => array_fill(0,6,false)
            ];
        }

        $index = $row['session'] - 1;
        $val = ($row['status'] === 'present');
        if($row['type'] === 'attendance') {
            $result[$id]['attendance'][$index] = $val;
        } else {
            $result[$id]['participation'][$index] = $val;
        }
    }

    echo json_encode(array_values($result));
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
