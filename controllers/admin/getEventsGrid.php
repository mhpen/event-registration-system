<?php
require_once '../../config/dbconn.php';

try {
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
        // Decode HTML entities
        $title = htmlspecialchars_decode($event['title']);
        $description = htmlspecialchars_decode($event['description']);
        $location = htmlspecialchars_decode($event['location']);
        
        // Format the date
        $eventDate = new DateTime($event['event_date']);
        $formattedDate = $eventDate->format('M d, Y h:i A');

        echo "<div class='bg-white p-4 rounded-lg shadow'>";
        echo "<h3 class='font-semibold'>" . $title . "</h3>";
        echo "<p class='text-sm text-gray-600 mt-2'>" . (strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description) . "</p>";
        echo "<div class='mt-4 flex justify-between items-center'>";
        echo "<span class='text-sm'>" . $formattedDate . "</span>";
        echo "<button onclick='viewEvent(" . $event['event_id'] . ")' class='text-blue-600 hover:text-blue-800'>View Details</button>";
        echo "</div>";
        echo "</div>";
    }
} catch(PDOException $e) {
    error_log("Error fetching events for grid view: " . $e->getMessage());
    echo "<div class='col-span-3 text-center py-4 text-red-600'>Error loading events</div>";
}
?> 