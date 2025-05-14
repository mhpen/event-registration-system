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
        throw new Exception('Participant ID is required');
    }

    // Get participant details
    $stmt = $conn->prepare("
        SELECT * FROM participants 
        WHERE participant_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participant) {
        throw new Exception('Participant not found');
    }

    // Get events attended
    $stmt = $conn->prepare("
        SELECT 
            e.title,
            e.event_date,
            c.checkin_time
        FROM checkins c
        JOIN events e ON c.event_id = e.event_id
        WHERE c.participant_id = ?
        ORDER BY c.checkin_time DESC
    ");
    $stmt->execute([$_GET['id']]);
    $participant['events_attended'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'participant_id' => $participant['participant_id'],
        'first_name' => $participant['first_name'],
        'last_name' => $participant['last_name'],
        'email' => $participant['email'],
        'phone' => $participant['phone'],
        'status' => $participant['status'],
        'registered_at' => $participant['registered_at'],
        'events_attended' => $participant['events_attended']
    ]);

} catch(Exception $e) {
    error_log("Error in getParticipantDetails.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 