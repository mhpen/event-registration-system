<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID is required');
    }

    $stmt = $conn->prepare("
        SELECT 
            c.checkin_id,
            c.checkin_time,
            e.title as event_title,
            CONCAT(p.first_name, ' ', p.last_name) as full_name
        FROM checkins c
        JOIN events e ON c.event_id = e.event_id
        JOIN participants p ON c.participant_id = p.participant_id
        WHERE c.event_id = ?
        ORDER BY c.checkin_time DESC
        LIMIT 10
    ");
    
    $stmt->execute([$_GET['event_id']]);
    $checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'checkins' => $checkins
    ]);

} catch(Exception $e) {
    error_log("Error in getRecentCheckins.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 