<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['participant']) || !isset($_GET['event_id'])) {
    header('Location: ../../views/client/login.php');
    exit();
}

try {
    $participant_id = $_SESSION['participant_id'];
    $event_id = $_GET['event_id'];
    
    // Delete the registration
    $stmt = $conn->prepare("DELETE FROM registrations WHERE participant_id = ? AND event_id = ?");
    $result = $stmt->execute([$participant_id, $event_id]);
    
    if ($result) {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'available';
        header('Location: ../../views/client/dashboard.php?success=2&tab=' . $tab);
    } else {
        header('Location: ../../views/client/dashboard.php?error=1');
    }
} catch(PDOException $e) {
    error_log("Unregistration error: " . $e->getMessage());
    header('Location: ../../views/client/dashboard.php?error=1');
}
exit();
?> 