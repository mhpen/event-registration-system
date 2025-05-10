<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $sql = "SELECT * FROM events WHERE event_id = ? AND admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$event_id, $_SESSION['admin_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        // Properly decode HTML entities for all text fields
        $event = array_map(function($value) {
            // Only decode string values
            return is_string($value) ? html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $value;
        }, $event);
        
        echo json_encode([
            'success' => true,
            'data' => $event
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Event not found'
        ]);
    }
} catch(Exception $e) {
    error_log("Error in getEventDetails.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch event details'
    ]);
}
?>