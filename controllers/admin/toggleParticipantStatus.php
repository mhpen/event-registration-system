<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("
            UPDATE participants 
            SET status = CASE 
                WHEN status = 'active' THEN 'inactive' 
                ELSE 'active' 
            END 
            WHERE participant_id = :id
        ");
        
        $stmt->execute([':id' => $_GET['id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
}
?> 