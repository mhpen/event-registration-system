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
    
    if (!isset($data['participant_id']) || !isset($data['status'])) {
        throw new Exception('Participant ID and status are required');
    }

    if (!in_array($data['status'], ['active', 'inactive'])) {
        throw new Exception('Invalid status value');
    }

    $stmt = $conn->prepare("
        UPDATE participants 
        SET status = ? 
        WHERE participant_id = ?
    ");
    
    $stmt->execute([$data['status'], $data['participant_id']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Participant not found');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);

} catch(Exception $e) {
    error_log("Error in updateParticipantStatus.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status: ' . $e->getMessage()
    ]);
}
?> 