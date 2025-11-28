<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? null;

    if (!$session_id) {
        echo json_encode(['error' => 'Missing session_id']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
        $stmt->execute([$session_id]);

        echo json_encode(['success' => true, 'message' => "Session $session_id closed"]);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
