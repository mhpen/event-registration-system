<?php
require_once '../../config/dbconn.php';

try {
    $stmt = $conn->query("SELECT * FROM events ORDER BY created_at DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
        echo "<tr class='hover:bg-gray-50'>";
        echo "<td class='py-4'>" . htmlspecialchars($event['title']) . "</td>";
        echo "<td class='py-4'>" . date('M d, Y', strtotime($event['event_date'])) . "</td>";
        echo "<td class='py-4'>" . htmlspecialchars($event['location']) . "</td>";
        echo "<td class='py-4'>" . htmlspecialchars(substr($event['description'], 0, 50)) . "...</td>";
        echo "<td class='py-4'>" . (strtotime($event['event_date']) > time() ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Upcoming</span>' : '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full">Past</span>') . "</td>";
        echo "<td class='py-4 flex space-x-2'>";
        // View button
        echo "<button onclick='viewEvent({$event['event_id']})' class='p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/>
                </svg>
              </button>";
        // Edit button
        echo "<button onclick='editEvent({$event['event_id']})' class='p-1.5 text-green-600 hover:bg-green-50 rounded-lg'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/>
                </svg>
              </button>";
        // Delete button
        echo "<button onclick='deleteEvent({$event['event_id']})' class='p-1.5 text-red-600 hover:bg-red-50 rounded-lg'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/>
                </svg>
              </button>";
        echo "</td>";
        echo "</tr>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 