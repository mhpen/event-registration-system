<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;
    
    $sql = "
        SELECT 
            c.*,
            CONCAT(p.first_name, ' ', p.last_name) as participant_name,
            e.title as event_title
        FROM checkins c
        JOIN participants p ON c.participant_id = p.participant_id
        JOIN events e ON c.event_id = e.event_id
    ";
    
    if ($eventId) {
        $sql .= " WHERE c.event_id = ?";
    }
    
    $sql .= " ORDER BY c.checkin_time DESC LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    
    if ($eventId) {
        $stmt->execute([$eventId]);
    } else {
        $stmt->execute();
    }
    
    $checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode HTML entities
    foreach ($checkins as &$checkin) {
        $checkin['event_title'] = html_entity_decode($checkin['event_title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $checkin['participant_name'] = html_entity_decode($checkin['participant_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    echo json_encode($checkins);
    
} catch(Exception $e) {
    error_log("Error in getCheckins.php: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch check-ins']);
}
?> 