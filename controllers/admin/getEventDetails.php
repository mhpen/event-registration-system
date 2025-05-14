<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Event ID is required');
    }

    $stmt = $conn->prepare("
        SELECT 
            e.*,
            em.meeting_link,
            COUNT(DISTINCT r.registration_id) as registration_count,
            COUNT(DISTINCT c.checkin_id) as checkin_count
        FROM events e
        LEFT JOIN event_meetings em ON e.event_id = em.event_id
        LEFT JOIN registrations r ON e.event_id = r.event_id
        LEFT JOIN checkins c ON e.event_id = c.event_id
        WHERE e.event_id = ?
        GROUP BY e.event_id
    ");
    
    $stmt->execute([$_GET['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    // Decode HTML entities
    $event['title'] = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $event['description'] = html_entity_decode($event['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $event['location'] = html_entity_decode($event['location'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo json_encode($event);

} catch(Exception $e) {
    error_log("Error in getEventDetails.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>