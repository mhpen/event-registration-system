<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['participant_id']) || !isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            e.*,
            r.registration_id
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.participant_id = :participant_id
        WHERE e.event_id = :event_id
    ");
    
    $stmt->execute([
        ':participant_id' => $_SESSION['participant_id'],
        ':event_id' => $_GET['id']
    ]);
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        // Sanitize the output
        $event = array_map('htmlspecialchars', $event);
        echo json_encode($event);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} 