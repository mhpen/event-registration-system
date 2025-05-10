<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    exit('Unauthorized');
}

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

try {
    $query = "SELECT * FROM events WHERE admin_id = ?";
    $params = [$_SESSION['admin_id']];

    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
    }

    if ($status) {
        if ($status === 'upcoming') {
            $query .= " AND event_date > NOW()";
        } else {
            $query .= " AND event_date <= NOW()";
        }
    }

    switch ($sort) {
        case 'date_asc':
            $query .= " ORDER BY event_date ASC";
            break;
        case 'title':
            $query .= " ORDER BY title ASC";
            break;
        default:
            $query .= " ORDER BY event_date DESC";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Include the getEvents.php file to use its HTML generation
    include 'getEvents.php';
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 