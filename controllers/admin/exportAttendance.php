<?php
session_start();
require_once '../../config/dbconn.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/adminLogin.php');
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');

// Create output handle
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, ['Event', 'Date', 'Participant', 'Email', 'Status', 'Check-in Time']);

try {
    $query = "
        SELECT 
            e.title as event_title,
            e.event_date,
            p.first_name,
            p.last_name,
            p.email,
            CASE 
                WHEN c.checkin_id IS NOT NULL THEN 'Checked In'
                ELSE 'Registered Only'
            END as status,
            c.checkin_time
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        JOIN participants p ON r.participant_id = p.participant_id
        LEFT JOIN checkins c ON r.participant_id = c.participant_id AND r.event_id = c.event_id
    ";

    $params = [];
    if (isset($_GET['event_id'])) {
        $query .= " WHERE e.event_id = ?";
        $params[] = $_GET['event_id'];
    }

    $query .= " ORDER BY e.event_date DESC, p.last_name, p.first_name";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['event_title'],
            date('Y-m-d g:i A', strtotime($row['event_date'])),
            $row['first_name'] . ' ' . $row['last_name'],
            $row['email'],
            $row['status'],
            $row['checkin_time'] ? date('Y-m-d g:i A', strtotime($row['checkin_time'])) : 'N/A'
        ]);
    }

} catch(PDOException $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
}

fclose($output);
?> 