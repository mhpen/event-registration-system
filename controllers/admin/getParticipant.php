<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                p.*,
                COUNT(DISTINCT r.event_id) as events_attended
            FROM participants p
            LEFT JOIN registrations r ON p.participant_id = r.participant_id
            WHERE p.participant_id = :id
            GROUP BY p.participant_id
        ");
        
        $stmt->execute([':id' => $_GET['id']]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($participant);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No ID provided']);
}
?> 