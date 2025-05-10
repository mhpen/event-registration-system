<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['participant']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../../views/client/login.php');
    exit();
}

try {
    $participant_id = $_SESSION['participant_id'];
    $event_id = $_POST['event_id'];
    
    // Check if already registered
    $checkStmt = $conn->prepare("SELECT registration_id FROM registrations WHERE participant_id = ? AND event_id = ?");
    $checkStmt->execute([$participant_id, $event_id]);
    if ($checkStmt->fetch()) {
        header('Location: ../../views/client/dashboard.php?error=1');
        exit();
    }

    // Generate unique registration code (Format: EVT-{eventId}-{participantId}-{random})
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    $registration_code = sprintf("EVT-%d-%d-%s", $event_id, $participant_id, $random);
    
    // Register for the event
    $stmt = $conn->prepare("
        INSERT INTO registrations 
        (participant_id, event_id, registration_code, status, created_at) 
        VALUES (?, ?, ?, 'registered', NOW())
    ");
    $result = $stmt->execute([$participant_id, $event_id, $registration_code]);
    
    if ($result) {
        // Get the registration details for QR code
        $regStmt = $conn->prepare("
            SELECT r.registration_code, e.title, e.event_date, e.location
            FROM registrations r
            JOIN events e ON r.event_id = e.event_id
            WHERE r.participant_id = ? AND r.event_id = ?
            ORDER BY r.created_at DESC LIMIT 1
        ");
        $regStmt->execute([$participant_id, $event_id]);
        $registration = $regStmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['last_registration'] = [
            'code' => $registration_code,
            'event' => $registration['title'],
            'date' => $registration['event_date'],
            'location' => $registration['location']
        ];
        
        header('Location: ../../views/client/dashboard.php?success=1');
    } else {
        error_log("Failed to register participant {$participant_id} for event {$event_id}");
        header('Location: ../../views/client/dashboard.php?error=2');
    }
} catch(PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    header('Location: ../../views/client/dashboard.php?error=2');
}
exit();
?> 