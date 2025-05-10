<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if admin_id exists in session
    if (!isset($_SESSION['admin_id'])) {
        error_log("Admin ID not set in session");
        header('Location: ../../views/admin/events.php?error=2');
        exit();
    }

    $admin_id = $_SESSION['admin_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];

    try {
        // For debugging
        error_log("Attempting to create event with admin_id: " . $admin_id);
        
        $stmt = $conn->prepare("INSERT INTO events (admin_id, title, description, event_date, location, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$admin_id, $title, $description, $event_date, $location]);
        
        if ($result) {
            header('Location: ../../views/admin/events.php?success=1');
        } else {
            error_log("Failed to insert event: " . print_r($stmt->errorInfo(), true));
            header('Location: ../../views/admin/events.php?error=3');
        }
        exit();
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: ../../views/admin/events.php?error=4');
        exit();
    }
}
?> 