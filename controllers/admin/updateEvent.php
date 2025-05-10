<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Get and sanitize all form data
    $event_id = (int)$_POST['event_id'];
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $event_date = $_POST['event_date'];
    $event_end_date = $_POST['event_end_date'];
    $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
    $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : -1;
    $category = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');

    // Validate required fields
    if (empty($title) || empty($description) || empty($event_date) || 
        empty($event_end_date) || empty($location) || empty($category)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        exit();
    }

    // Validate dates
    $start = new DateTime($event_date);
    $end = new DateTime($event_end_date);
    $now = new DateTime();

    // For updates, we might want to allow editing past events, so we'll skip the future date check
    // But we'll still ensure end date is after start date
    if ($end <= $start) {
        echo json_encode(['success' => false, 'error' => 'End date must be after start date']);
        exit();
    }

    // First check if the event exists and belongs to the current admin
    $checkStmt = $conn->prepare("SELECT admin_id FROM events WHERE event_id = ?");
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch();

    if (!$event || $event['admin_id'] != $_SESSION['admin_id']) {
        echo json_encode(['success' => false, 'error' => 'Event not found or unauthorized']);
        exit();
    }

    // Update the event with all fields
    $sql = "UPDATE events SET 
            title = ?, 
            description = ?, 
            event_date = ?, 
            event_end_date = ?, 
            location = ?, 
            capacity = ?, 
            category = ? 
            WHERE event_id = ? AND admin_id = ?";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        $title,
        $description,
        $event_date,
        $event_end_date,
        $location,
        $capacity,
        $category,
        $event_id,
        $_SESSION['admin_id']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => [
                'event_id' => $event_id,
                'title' => $title,
                'description' => $description,
                'event_date' => $event_date,
                'event_end_date' => $event_end_date,
                'location' => $location,
                'capacity' => $capacity,
                'category' => $category
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update event']);
    }
} catch(PDOException $e) {
    error_log("Database error in updateEvent.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch(Exception $e) {
    error_log("General error in updateEvent.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
}
exit();
?> 