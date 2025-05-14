<?php
session_start();
require_once '../../config/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get total events
    $stmt = $conn->query("SELECT COUNT(*) FROM events");
    $totalEvents = $stmt->fetchColumn();

    // Get total registrations
    $stmt = $conn->query("SELECT COUNT(*) FROM registrations");
    $totalRegistrations = $stmt->fetchColumn();

    // Get total check-ins
    $stmt = $conn->query("SELECT COUNT(*) FROM checkins");
    $totalCheckins = $stmt->fetchColumn();

    // Calculate average attendance rate
    $averageAttendanceRate = $totalRegistrations > 0 
        ? round(($totalCheckins / $totalRegistrations) * 100, 1) 
        : 0;

    // Get event-specific attendance data
    $stmt = $conn->query("
        SELECT 
            e.event_id,
            e.title,
            e.event_date,
            e.location,
            COUNT(DISTINCT r.registration_id) as registrations,
            COUNT(DISTINCT c.checkin_id) as checkins,
            CASE 
                WHEN COUNT(DISTINCT r.registration_id) > 0 
                THEN ROUND((COUNT(DISTINCT c.checkin_id) * 100.0) / COUNT(DISTINCT r.registration_id), 1)
                ELSE 0 
            END as attendance_rate
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        LEFT JOIN checkins c ON e.event_id = c.event_id
        GROUP BY e.event_id
        ORDER BY e.event_date DESC
    ");

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return all data
    echo json_encode([
        'totalEvents' => $totalEvents,
        'totalRegistrations' => $totalRegistrations,
        'totalCheckins' => $totalCheckins,
        'averageAttendanceRate' => $averageAttendanceRate,
        'events' => $events
    ]);

} catch(Exception $e) {
    error_log("Error in getAttendanceData.php: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch attendance data']);
}
?> 