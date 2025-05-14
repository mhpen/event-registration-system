<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['event_id'])) {
        throw new Exception('Event ID is required');
    }

    // First delete related records (registrations, etc.)
    $stmt = $conn->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->execute([$data['event_id']]);
    
    $stmt = $conn->prepare("DELETE FROM checkins WHERE event_id = ?");
    $stmt->execute([$data['event_id']]);

    // Then delete the event
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$data['event_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Event deleted successfully'
    ]);

} catch(Exception $e) {
    error_log("Error in deleteEvent.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete event: ' . $e->getMessage()
    ]);
}
?> 