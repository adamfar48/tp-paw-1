<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

$data = json_decode(file_get_contents('php://input'), true);
$today = date('Y-m-d');

if(!$data || !isset($data['attendance'])){
    http_response_code(400);
    echo json_encode(['error'=>'No data']);
    exit;
}

try {
    foreach($data['attendance'] as $student){
        $student_id = $student['student_id'];
        $attendance = $student['attendance']; 
        $participation = $student['participation'];

        // Loop over 6 sessions
        for($i=0;$i<6;$i++){
            $status = $attendance[$i] ? 'present' : 'absent';

            // Check if already exists
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id=? AND date=? AND session=?");
            $stmt->execute([$student_id, $today, $i+1]);
            $exists = $stmt->fetch();

            if($exists){
                $upd = $pdo->prepare("UPDATE attendance SET status=? WHERE id=?");
                $upd->execute([$status, $exists['id']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO attendance (student_id, status, date, session) VALUES (?,?,?,?)");
                $ins->execute([$student_id, $status, $today, $i+1]);
            }
        }

        // Participation same logic if needed
    }

    echo json_encode(['success'=>true]);

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
?>
