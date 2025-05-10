<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $sort = $_GET['sort'] ?? 'date_desc';

    $query = "SELECT * FROM events WHERE admin_id = ?";
    $params = [$_SESSION['admin_id']];

    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $searchTerm = "%{$search}%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
    }

    if (!empty($status)) {
        if ($status === 'upcoming') {
            $query .= " AND event_date > NOW()";
        } else if ($status === 'past') {
            $query .= " AND event_date <= NOW()";
        }
    }

    switch ($sort) {
        case 'date_asc':
            $query .= " ORDER BY event_date ASC";
            break;
        case 'date_desc':
            $query .= " ORDER BY event_date DESC";
            break;
        case 'title':
            $query .= " ORDER BY title ASC";
            break;
        default:
            $query .= " ORDER BY created_at DESC";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($events);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 