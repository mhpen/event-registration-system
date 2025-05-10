<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];

    // First check if the event exists and belongs to the current admin
    $checkStmt = $conn->prepare("SELECT admin_id FROM events WHERE event_id = ?");
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch();

    if (!$event || $event['admin_id'] != $_SESSION['admin_id']) {
        echo json_encode(['success' => false, 'error' => 'Event not found or unauthorized']);
        exit();
    }

    // Update the event
    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE event_id = ? AND admin_id = ?");
    $result = $stmt->execute([$title, $description, $event_date, $location, $event_id, $_SESSION['admin_id']]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update event']);
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
exit();
?> 