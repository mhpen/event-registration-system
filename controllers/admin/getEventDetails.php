<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin']) || !isset($_GET['id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

try {
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND admin_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['admin_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        header('Content-Type: application/json');
        echo json_encode($event);
    } else {
        echo json_encode(['error' => 'Event not found']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>