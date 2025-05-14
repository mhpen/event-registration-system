<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['title', 'description', 'event_date', 'event_end_date', 'category', 'admin_id', 'event_type'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Start transaction
    $conn->beginTransaction();

    // Insert event
    $stmt = $conn->prepare("
        INSERT INTO events (
            title, description, event_date, event_end_date, 
            location, category, admin_id, event_type
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $location = $_POST['event_type'] === 'online' ? 'Online Event' : htmlspecialchars($_POST['location']);

    $stmt->execute([
        htmlspecialchars($_POST['title']),
        htmlspecialchars($_POST['description']),
        $_POST['event_date'],
        $_POST['event_end_date'],
        $location,
        htmlspecialchars($_POST['category']),
        $_POST['admin_id'],
        $_POST['event_type']
    ]);

    $eventId = $conn->lastInsertId();

    // If it's an online event, store the meeting link
    if ($_POST['event_type'] === 'online' && !empty($_POST['meeting_link'])) {
        $stmt = $conn->prepare("
            INSERT INTO event_meetings (event_id, meeting_link)
            VALUES (?, ?)
        ");
        $stmt->execute([$eventId, htmlspecialchars($_POST['meeting_link'])]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully'
    ]);

} catch(Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in createEvent.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create event: ' . $e->getMessage()
    ]);
}
?> 