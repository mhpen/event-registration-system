<?php
session_start();
require_once '../../config/dbconn.php';

// Add this after the session and require statements
define('DEVELOPMENT_MODE', true); // Set to false in production

if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $registration_code = trim($_POST['registration_code']);
        $event_id = $_POST['event_id'];

        // Debug log
        error_log("Check-in attempt - Code: $registration_code, Event: $event_id");

        // Add this right after getting the registration code and event_id
        error_log("Check-in attempt details - Code: $registration_code, Event ID: $event_id");

        // Modify the query to check the registration code first
        $stmt = $conn->prepare("
            SELECT 
                r.*, 
                p.first_name, 
                p.last_name,
                e.title as event_title,
                e.event_date,
                e.event_id as actual_event_id
            FROM registrations r
            JOIN participants p ON r.participant_id = p.participant_id
            JOIN events e ON r.event_id = e.event_id
            WHERE r.registration_code = :code
        ");

        $stmt->execute([':code' => $registration_code]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registration) {
            error_log("Found registration - Event ID in registration: " . $registration['event_id'] . 
                      ", Selected Event ID: " . $event_id);
            
            // Check if the event IDs match
            if ($registration['event_id'] != $event_id) {
                $message = "Registration code is for event: " . $registration['event_title'] . 
                          " (ID: " . $registration['event_id'] . "). " .
                          "Selected event ID: " . $event_id;
                error_log("Event mismatch - " . $message);
                echo json_encode(['success' => false, 'message' => $message]);
                exit();
            }
            
            // Verify event date
            $eventDate = new DateTime($registration['event_date']);
            $now = new DateTime();

            if (DEVELOPMENT_MODE) {
                // In development mode, allow check-ins regardless of date
                error_log("Development mode: Bypassing date restrictions");
            } else {
                // Production date validation
                $checkInWindow = clone $eventDate;
                $checkInWindow->modify('-2 hours');

                $eventEndDate = clone $eventDate;
                $eventEndDate->modify('+4 hours');

                if ($now < $checkInWindow) {
                    $message = sprintf(
                        "Check-in will open 2 hours before the event (%s)",
                        $checkInWindow->format('F j, Y g:i A')
                    );
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                } elseif ($now > $eventEndDate) {
                    $message = "This event has ended. Check-in was available until " . 
                               $eventEndDate->format('F j, Y g:i A');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                }
            }
            
            // Check if already checked in
            $stmt = $conn->prepare("
                SELECT checkin_id, checkin_time 
                FROM checkins 
                WHERE participant_id = :participant_id 
                AND event_id = :event_id
            ");
            
            $stmt->execute([
                ':participant_id' => $registration['participant_id'],
                ':event_id' => $event_id
            ]);
            
            if ($checkin = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Participant already checked in at ' . 
                        date('g:i A', strtotime($checkin['checkin_time']))
                ]);
                exit();
            }
            
            // Process check-in
            $conn->beginTransaction();

            try {
                // Insert check-in record
                $stmt = $conn->prepare("
                    INSERT INTO checkins (participant_id, event_id, notes, status)
                    VALUES (:participant_id, :event_id, :notes, 'checked_in')
                ");
                
                $stmt->execute([
                    ':participant_id' => $registration['participant_id'],
                    ':event_id' => $event_id,
                    ':notes' => $_POST['notes'] ?? null
                ]);
                
                // Update registration status
                $stmt = $conn->prepare("
                    UPDATE registrations 
                    SET status = 'checked_in' 
                    WHERE registration_id = :registration_id
                ");
                $stmt->execute([':registration_id' => $registration['registration_id']]);

                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Check-in successful',
                    'participant' => [
                        'name' => $registration['first_name'] . ' ' . $registration['last_name'],
                        'event' => $registration['event_title'],
                        'code' => $registration['registration_code']
                    ]
                ]);

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            
        } else {
            error_log("No registration found for code: " . $registration_code);
            echo json_encode(['success' => false, 'message' => 'Invalid registration code']);
            exit();
        }
        
    } catch(PDOException $e) {
        error_log("Check-in error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 