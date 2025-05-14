<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get events by category
    $stmt = $conn->query("
        SELECT 
            category,
            COUNT(*) as count
        FROM events
        GROUP BY category
        ORDER BY count DESC
    ");
    $eventsByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get registration trends (last 7 days)
    $stmt = $conn->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM registrations
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $registrationTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates for better display
    foreach ($registrationTrends as &$trend) {
        $trend['date'] = date('M j', strtotime($trend['date']));
    }

    // Decode category names
    foreach ($eventsByCategory as &$category) {
        $category['category'] = html_entity_decode($category['category'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Get attendance rates for events
    $stmt = $conn->query("
        SELECT 
            e.title as event_name,
            COUNT(DISTINCT r.registration_id) as total_registrations,
            COUNT(DISTINCT c.checkin_id) as total_checkins,
            ROUND((COUNT(DISTINCT c.checkin_id) * 100.0 / 
                NULLIF(COUNT(DISTINCT r.registration_id), 0)), 1) as rate
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        LEFT JOIN checkins c ON e.event_id = c.event_id
        WHERE e.event_date < NOW()
        GROUP BY e.event_id, e.title
        HAVING total_registrations > 0
        ORDER BY e.event_date DESC
        LIMIT 5
    ");
    $attendanceRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get event popularity (most registrations)
    $stmt = $conn->query("
        SELECT 
            e.title as event_name,
            COUNT(r.registration_id) as registration_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        GROUP BY e.event_id, e.title
        ORDER BY registration_count DESC
        LIMIT 6
    ");
    $eventPopularity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode HTML entities for all text fields
    foreach ($attendanceRates as &$rate) {
        $rate['event_name'] = html_entity_decode($rate['event_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    foreach ($eventPopularity as &$event) {
        $event['event_name'] = html_entity_decode($event['event_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    echo json_encode([
        'eventsByCategory' => $eventsByCategory,
        'registrationTrends' => $registrationTrends,
        'attendanceRates' => $attendanceRates,
        'eventPopularity' => $eventPopularity
    ]);

} catch(Exception $e) {
    error_log("Error in getDashboardStats.php: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch dashboard statistics']);
}
?> 