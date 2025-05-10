<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    
    try {
        // First check if the event exists and belongs to the current admin
        $stmt = $conn->prepare("SELECT admin_id FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        if ($event && $event['admin_id'] == $_SESSION['admin_id']) {
            // Delete the event
            $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->execute([$event_id]);
            
            header('Location: ../../views/admin/events.php?success=1');
            exit();
        } else {
            header('Location: ../../views/admin/events.php?error=1');
            exit();
        }
    } catch(PDOException $e) {
        header('Location: ../../views/admin/events.php?error=1');
        exit();
    }
} else {
    header('Location: ../../views/admin/events.php?error=1');
    exit();
}
?> 