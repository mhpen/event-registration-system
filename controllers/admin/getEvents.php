<?php
require_once '../../config/dbconn.php';

try {
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
        // Decode HTML entities before output
        $title = htmlspecialchars_decode($event['title']);
        $description = htmlspecialchars_decode($event['description']);
        $location = htmlspecialchars_decode($event['location']);
        
        // Format the date
        $eventDate = new DateTime($event['event_date']);
        $formattedDate = $eventDate->format('M d, Y h:i A');
        
        // Determine status
        $now = new DateTime();
        $status = ($eventDate > $now) ? 
            '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Upcoming</span>' : 
            '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">Past</span>';

        echo "<tr class='hover:bg-gray-50'>";
        echo "<td class='py-4 px-4 border-b'>" . $title . "</td>";
        echo "<td class='py-4 px-4 border-b'>" . $formattedDate . "</td>";
        echo "<td class='py-4 px-4 border-b'>" . $location . "</td>";
        echo "<td class='py-4 px-4 border-b max-w-xs'>" . 
             "<p class='truncate'>" . (strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description) . "</p>" . 
             "</td>";
        echo "<td class='py-4 px-4 border-b'>" . $status . "</td>";
        echo "<td class='py-4 px-4 border-b'>
                <div class='flex items-center space-x-3'>
                    <!-- View Button -->
                    <button onclick='viewEvent(" . $event['event_id'] . ")' 
                        class='p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors'>
                        <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' 
                                d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' 
                                d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/>
                        </svg>
                    </button>

                    <!-- Edit Button -->
                    <button onclick='editEvent(" . $event['event_id'] . ")' 
                        class='p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors'>
                        <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' 
                                d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/>
                        </svg>
                    </button>

                    <!-- Delete Button -->
                    <button onclick='deleteEvent(" . $event['event_id'] . ")' 
                        class='p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors'>
                        <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' 
                                d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/>
                        </svg>
                    </button>
                </div>
              </td>";
        echo "</tr>";
    }
} catch(PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    echo "<tr><td colspan='6' class='text-center py-4 text-red-600'>Error loading events</td></tr>";
}
?> 