<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['event_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Event ID is required']);
    exit();
}

try {
    // Get event details
    $stmt = $conn->prepare("
        SELECT title, event_date
        FROM events
        WHERE event_id = ?
    ");
    $stmt->execute([$_GET['event_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        $event['title'] = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Get attendees
    $stmt = $conn->prepare("
        SELECT 
            p.first_name,
            p.last_name,
            p.email,
            c.checkin_time,
            CASE 
                WHEN c.checkin_id IS NOT NULL THEN 'checked_in'
                ELSE 'registered'
            END as status
        FROM registrations r
        JOIN participants p ON r.participant_id = p.participant_id
        LEFT JOIN checkins c ON r.participant_id = c.participant_id AND r.event_id = c.event_id
        WHERE r.event_id = ?
        ORDER BY p.last_name, p.first_name
    ");
    $stmt->execute([$_GET['event_id']]);
    $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($attendees as &$attendee) {
        $attendee['name'] = html_entity_decode($attendee['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Format the data
    $response = [
        'event' => [
            'title' => $event['title'],
            'date' => date('F j, Y g:i A', strtotime($event['event_date']))
        ],
        'attendees' => array_map(function($attendee) {
            return [
                'name' => $attendee['first_name'] . ' ' . $attendee['last_name'],
                'email' => $attendee['email'],
                'checkin_time' => $attendee['checkin_time'] 
                    ? date('g:i A', strtotime($attendee['checkin_time'])) 
                    : null,
                'status' => $attendee['status']
            ];
        }, $attendees)
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 