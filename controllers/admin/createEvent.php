<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate admin session
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ../../views/admin/events.php?error=2');
        exit();
    }

    try {
        // Get and sanitize form data
        $admin_id = (int)$_SESSION['admin_id'];
        $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
        $event_date = $_POST['event_date'];
        $event_end_date = $_POST['event_end_date'];
        $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
        $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : -1;
        $category = filter_var(trim($_POST['category']), FILTER_SANITIZE_STRING);

        // Validate required fields
        if (empty($title) || empty($description) || empty($event_date) || 
            empty($event_end_date) || empty($location) || empty($category)) {
            header('Location: ../../views/admin/events.php?error=8');
            exit();
        }

        // Validate dates
        $start = new DateTime($event_date);
        $end = new DateTime($event_end_date);
        $now = new DateTime();

        // Check if event date is in the future
        if ($start < $now) {
            header('Location: ../../views/admin/events.php?error=5');
            exit();
        }

        // Check if end date is after start date
        if ($end <= $start) {
            header('Location: ../../views/admin/events.php?error=6');
            exit();
        }

        // Insert event into database
        $sql = "INSERT INTO events (
            admin_id, title, description, event_date, event_end_date,
            location, capacity, category, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $admin_id,
            $title,
            $description,
            $event_date,
            $event_end_date,
            $location,
            $capacity,
            $category
        ]);

        if ($result) {
            header('Location: ../../views/admin/events.php?success=1');
        } else {
            header('Location: ../../views/admin/events.php?error=3');
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: ../../views/admin/events.php?error=4');
    } catch(Exception $e) {
        error_log("General error: " . $e->getMessage());
        header('Location: ../../views/admin/events.php?error=9');
    }
    exit();
}

// Debug: Log if script is accessed without POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("createEvent.php accessed with " . $_SERVER['REQUEST_METHOD'] . " method");
}
?> 