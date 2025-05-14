<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: adminLogin.php');
    exit();
}

// Check if admin_id is set
if (!isset($_SESSION['admin_id'])) {
    error_log("Admin ID not set in session. Session data: " . print_r($_SESSION, true));
    header('Location: adminLogin.php?error=2');
    exit();
}

// Add database connection
require_once '../../config/dbconn.php';

// Debug information
error_log("Current session data in events.php: " . print_r($_SESSION, true));
echo "<!-- Debug: Admin ID: " . $_SESSION['admin_id'] . " -->";

// At the top of the file, after database connection
date_default_timezone_set('Asia/Manila'); // Set to your timezone
?>
<?php include '../shared/header.php'; ?>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
</style>

<body class="bg-background">
    <div class="flex h-screen">
        <?php include '../shared/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto bg-background">
            <div class="p-6">
                <!-- Header with actions -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Events</h1>
                        <p class="text-sm text-muted-foreground">Manage and organize your events</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <!-- Search -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search events..." 
                                    class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <svg class="w-4 h-4 absolute right-3 top-2.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                            <!-- Filter -->
                            <select id="statusFilter" 
                                class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="past">Past</option>
                        </select>
                        </div>

                        <button onclick="openCreateEventModal()" 
                            class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Event
                        </button>
                    </div>
                </div>

                <!-- Add this after the header section and before the events table -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
                    <!-- Total Events -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Events</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM events");
                            $totalEvents = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $totalEvents ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">events</div>
                        </div>
                    </div>

                    <!-- Active Events -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Active Events</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("
                                SELECT COUNT(*) FROM events 
                                WHERE DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') BETWEEN 
                                    DATE_FORMAT(event_date, '%Y-%m-%d %H:%i:%s') AND 
                                    DATE_FORMAT(event_end_date, '%Y-%m-%d %H:%i:%s')
                            ");
                            $activeEvents = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $activeEvents ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">ongoing</div>
                        </div>
                    </div>

                    <!-- Total Participants -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Participants</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("
                                SELECT COUNT(DISTINCT participant_id) 
                                FROM checkins
                            ");
                            $totalParticipants = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $totalParticipants ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">registered</div>
                        </div>
                    </div>

                    <!-- Average Attendance -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Average Attendance</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("
                                SELECT ROUND(AVG(attendance_count), 0) as avg_attendance
                                FROM (
                                    SELECT event_id, COUNT(*) as attendance_count
                                    FROM checkins
                                    GROUP BY event_id
                                ) as event_counts
                            ");
                            $avgAttendance = $stmt->fetchColumn() ?: 0;
                            ?>
                            <div class="text-2xl font-bold"><?= $avgAttendance ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">per event</div>
                        </div>
                    </div>
                </div>

                <!-- Events Table -->
                <div class="rounded-lg border bg-card text-card-foreground">
                    <div class="relative w-full overflow-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Event</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Location</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
                                    while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $title = html_entity_decode($event['title']);
                                        $location = html_entity_decode($event['location']);
                                        
                                        // Create DateTime objects with microseconds
                                        $eventStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['event_date']);
                                        $eventEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['event_end_date']);
                                        $now = new DateTime();

                                        // Remove microseconds for accurate comparison
                                        $eventStartDate->setTime(
                                            $eventStartDate->format('H'),
                                            $eventStartDate->format('i'),
                                            $eventStartDate->format('s')
                                        );
                                        $eventEndDate->setTime(
                                            $eventEndDate->format('H'),
                                            $eventEndDate->format('i'),
                                            $eventEndDate->format('s')
                                        );
                                        $now->setTime(
                                            $now->format('H'),
                                            $now->format('i'),
                                            $now->format('s')
                                        );

                                        // Determine event status with precise comparison
                                        if ($now->format('Y-m-d H:i:s') < $eventStartDate->format('Y-m-d H:i:s')) {
                                            $status = '<span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">upcoming</span>';
                                        } elseif ($now->format('Y-m-d H:i:s') >= $eventStartDate->format('Y-m-d H:i:s') && 
                                                  $now->format('Y-m-d H:i:s') <= $eventEndDate->format('Y-m-d H:i:s')) {
                                            $status = '<span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">ongoing</span>';
                                        } else {
                                            $status = '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">past</span>';
                                        }
                                        ?>
                                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                            <td class="p-4 align-middle"><?= $title ?></td>
                                            <td class="p-4 align-middle">
                                                <?= $eventStartDate->format('M j, Y g:i A') ?><br>
                                                <span class="text-xs text-muted-foreground">to</span><br>
                                                <?= $eventEndDate->format('M j, Y g:i A') ?>
                                            </td>
                                            <td class="p-4 align-middle"><?= $location ?></td>
                                            <td class="p-4 align-middle"><?= $status ?></td>
                                            <td class="p-4 align-middle">
                                                <div class="flex items-center gap-2">
                                                    <button onclick="viewEvent(<?= $event['event_id'] ?>)" 
                                                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="editEvent(<?= $event['event_id'] ?>)" 
                                                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deleteEvent(<?= $event['event_id'] ?>)" 
                                                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 text-red-500">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='5' class='p-4 text-center text-sm text-red-500'>Error loading events</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="eventModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden">
        <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 rounded-lg">
            <div class="flex flex-col space-y-1.5 text-center sm:text-left">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Create Event</h3>
                <p class="text-sm text-muted-foreground">Fill in the event details below</p>
            </div>
            
            <form id="createEventForm" action="../../controllers/admin/createEvent.php" method="POST" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="title">
                            Event Title
                        </label>
                    <input type="text" id="title" name="title" required
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="description">
                            Description
                        </label>
                    <textarea id="description" name="description" required rows="3"
                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                    </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="event_date">
                            Start Date & Time
                        </label>
                        <input type="datetime-local" id="event_date" name="event_date" required
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="event_end_date">
                            End Date & Time
                        </label>
                        <input type="datetime-local" id="event_end_date" name="event_end_date" required
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="event_type">
                        Event Type
                    </label>
                    <select id="event_type" name="event_type" required onchange="toggleLocationField()"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Select event type</option>
                        <option value="offline">In-Person</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div id="locationFields" class="space-y-2">
                    <div id="physicalLocation">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="location">
                            Location
                        </label>
                        <input type="text" id="location" name="location"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Enter physical location">
                </div>

                    <div id="onlineLocation" class="hidden">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="meeting_link">
                            Meeting Link
                            </label>
                        <div class="flex gap-2">
                            <input type="url" id="meeting_link" name="meeting_link"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Enter meeting URL">
                            <button type="button" onclick="generateMeetingLink()" 
                                class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                                Generate
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="category">
                        Category
                            </label>
                    <select id="category" name="category" required
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Select a category</option>
                        <option value="Conference">Conference</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="participant_limit">
                        Participant Limit
                        <span class="text-xs text-muted-foreground">(Leave empty for unlimited)</span>
                    </label>
                    <input 
                        type="number" 
                        id="participant_limit" 
                        name="participant_limit" 
                        min="1"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Enter maximum number of participants"
                    >
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEventModal()"
                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update the View Event Modal -->
    <div id="viewEventModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden z-50">
        <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Event Details</h3>
                    <p class="text-sm text-muted-foreground mt-1">View complete event information</p>
                </div>
                <button onclick="closeViewModal()" class="text-muted-foreground hover:text-foreground">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="eventDetails" class="space-y-4">
                <!-- Event details will be populated here -->
                        </div>
                        </div>
                    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden">
        <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 rounded-lg">
            <div class="flex flex-col space-y-1.5 text-center sm:text-left">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Edit Event</h3>
                <p class="text-sm text-muted-foreground">Update the event details below</p>
                    </div>

            <form id="editEventForm" class="space-y-4">
                <input type="hidden" id="edit_event_id" name="event_id">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none" for="edit_title">Event Title</label>
                    <input type="text" id="edit_title" name="title" required
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none" for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" required rows="3"
                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                        </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="edit_event_date">Start Date & Time</label>
                        <input type="datetime-local" id="edit_event_date" name="event_date" required
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="edit_event_end_date">End Date & Time</label>
                        <input type="datetime-local" id="edit_event_end_date" name="event_end_date" required
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none" for="edit_location">Location</label>
                    <input type="text" id="edit_location" name="location" required
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none" for="edit_category">Category</label>
                    <select id="edit_category" name="category" required
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="Conference">Conference</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none" for="edit_participant_limit">
                        Participant Limit
                        <span class="text-xs text-muted-foreground">(Leave empty for unlimited)</span>
                    </label>
                    <input 
                        type="number" 
                        id="edit_participant_limit" 
                        name="participant_limit" 
                        min="1"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Enter maximum number of participants"
                    >
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()"
                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add this confirmation modal for delete -->
    <div id="deleteConfirmModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden">
        <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-sm translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 rounded-lg">
            <div class="flex flex-col space-y-2 text-center sm:text-left">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Delete Event</h3>
                <p class="text-sm text-muted-foreground">Are you sure you want to delete this event? This action cannot be undone.</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteModal()"
                    class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                    class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-500 text-white hover:bg-red-600 h-9 px-4 py-2">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    if (!row.id || row.id !== 'noEventsRow') {  // Skip the "No events found" row
                        const title = row.cells[0].textContent.toLowerCase();
                        const status = row.cells[3].querySelector('span').textContent.toLowerCase();
                        const matchesSearch = title.includes(searchTerm);
                        const matchesStatus = !statusValue || status === statusValue;
                        
                        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                    }
                });

                // Update "No events found" message
                const visibleRows = Array.from(document.querySelectorAll('tbody tr')).filter(row => 
                    row.style.display !== 'none' && (!row.id || row.id !== 'noEventsRow')
                ).length;
                
                const noEventsRow = document.getElementById('noEventsRow');
                if (visibleRows === 0) {
                    if (!noEventsRow) {
                        const tbody = document.querySelector('tbody');
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr id="noEventsRow">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    No events found matching your criteria
                                </td>
                            </tr>
                        `);
                    }
                } else if (noEventsRow) {
                    noEventsRow.remove();
                }
            }

            // Add event listeners
            searchInput.addEventListener('input', filterTable);
            statusFilter.addEventListener('change', filterTable);

            // Initial filter
            filterTable();
        });

        function openCreateEventModal() {
            document.getElementById('eventModal').classList.remove('hidden');
            // Set minimum date to today
            const today = new Date().toISOString().slice(0, 16);
            document.getElementById('event_date').min = today;
            document.getElementById('event_end_date').min = today;
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            document.getElementById('createEventForm').reset();
        }

        // Update end date when start date changes
        document.getElementById('event_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setHours(endDate.getHours() + 2); // Default duration 2 hours
            document.getElementById('event_end_date').value = endDate.toISOString().slice(0, 16);
            document.getElementById('event_end_date').min = this.value;
        });

        // Form submission handling
        document.getElementById('createEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const eventType = document.getElementById('event_type').value;
            const meetingLinkInput = document.getElementById('meeting_link');
            
            if (eventType === 'online') {
                if (!meetingLinkInput.value) {
                    meetingLinkInput.setCustomValidity('Please enter or generate a meeting link');
                    meetingLinkInput.reportValidity();
                    return;
                }
                
                try {
                    new URL(meetingLinkInput.value);
                    meetingLinkInput.setCustomValidity('');
                } catch (_) {
                    meetingLinkInput.setCustomValidity('Please enter a valid URL starting with http:// or https://');
                    meetingLinkInput.reportValidity();
                    return;
                }
            }
            
            const formData = new FormData(this);
            formData.append('admin_id', <?= $_SESSION['admin_id'] ?>);

            fetch('../../controllers/admin/createEvent.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEventModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to create event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to create event');
            });
        });

        // Close modal when clicking outside
        document.getElementById('eventModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEventModal();
            }
        });

        function viewEvent(eventId) {
            fetch(`../../controllers/admin/getEventDetails.php?id=${eventId}`)
                .then(response => response.json())
                .then(event => {
                    if (event.error) {
                        throw new Error(event.error);
                    }
                    
                    const details = document.getElementById('eventDetails');
                    const eventDate = new Date(event.event_date);
                    const eventEndDate = new Date(event.event_end_date);
                    
                    details.innerHTML = `
                        <div class="space-y-4">
                            <div class="pb-4 border-b">
                                <h4 class="text-xl font-semibold">${event.title}</h4>
                                <p class="text-sm text-muted-foreground mt-1">${event.category}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Start Date & Time</p>
                                    <p class="mt-1">${eventDate.toLocaleString()}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">End Date & Time</p>
                                    <p class="mt-1">${eventEndDate.toLocaleString()}</p>
                                </div>
                        </div>
                            
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Location</p>
                                <p class="mt-1">${event.location}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Description</p>
                                <p class="mt-1 whitespace-pre-wrap">${event.description}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Total Registrations</p>
                                    <p class="text-2xl font-bold mt-1">${event.registration_count}</p>
                                    ${event.participant_limit ? 
                                        `<p class="text-xs text-muted-foreground">Limited to ${event.participant_limit} participants</p>` 
                                        : '<p class="text-xs text-muted-foreground">No participant limit</p>'
                                    }
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Check-ins</p>
                                    <p class="text-2xl font-bold mt-1">${event.checkin_count}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('viewEventModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load event details: ' + error.message);
                });
        }

        function closeViewModal() {
            document.getElementById('viewEventModal').classList.add('hidden');
        }

        let currentDeleteId = null;

        function editEvent(eventId) {
            fetch(`../../controllers/admin/getEventDetails.php?id=${eventId}`)
                .then(response => response.json())
                .then(event => {
                    document.getElementById('edit_event_id').value = event.event_id;
                    document.getElementById('edit_title').value = event.title;
                    document.getElementById('edit_description').value = event.description;
                    document.getElementById('edit_event_date').value = event.event_date.slice(0, 16);
                    document.getElementById('edit_event_end_date').value = event.event_end_date.slice(0, 16);
                    document.getElementById('edit_location').value = event.location;
                    document.getElementById('edit_category').value = event.category;
                    document.getElementById('edit_participant_limit').value = event.participant_limit || '';
                    
                    document.getElementById('editEventModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load event details');
                });
        }

        function closeEditModal() {
            document.getElementById('editEventModal').classList.add('hidden');
            document.getElementById('editEventForm').reset();
        }

        document.getElementById('editEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../../controllers/admin/updateEvent.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update event');
            });
        });

        function deleteEvent(eventId) {
            currentDeleteId = eventId;
            document.getElementById('deleteConfirmModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').classList.add('hidden');
            currentDeleteId = null;
        }

        function confirmDelete() {
            if (!currentDeleteId) return;
            
            fetch('../../controllers/admin/deleteEvent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    event_id: currentDeleteId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete event');
            });
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-background/80')) {
                closeEditModal();
                closeDeleteModal();
            }
        });

        function toggleLocationField() {
            const eventType = document.getElementById('event_type').value;
            const physicalLocation = document.getElementById('physicalLocation');
            const onlineLocation = document.getElementById('onlineLocation');
            const locationInput = document.getElementById('location');
            const meetingLinkInput = document.getElementById('meeting_link');
            
            if (eventType === 'online') {
                physicalLocation.classList.add('hidden');
                onlineLocation.classList.remove('hidden');
                locationInput.removeAttribute('required');
                meetingLinkInput.setAttribute('required', 'required');
                // Only generate meeting link if empty
                if (!meetingLinkInput.value) {
                    generateMeetingLink();
                }
            } else {
                physicalLocation.classList.remove('hidden');
                onlineLocation.classList.add('hidden');
                locationInput.setAttribute('required', 'required');
                meetingLinkInput.removeAttribute('required');
                meetingLinkInput.value = ''; // Clear meeting link when switching to offline
            }
        }

        function generateMeetingLink() {
            const meetingLinkInput = document.getElementById('meeting_link');
            if (meetingLinkInput.value && !confirm('Do you want to replace the existing meeting link?')) {
                return;
            }
            const randomId = Math.random().toString(36).substring(2, 10);
            meetingLinkInput.value = `https://samplemeetlink.com/${randomId}`;
        }

        // Replace the existing URL validation code with this:
        document.getElementById('meeting_link').addEventListener('input', function(e) {
            const input = e.target;
            if (!input.value) {
                input.setCustomValidity('');
                return;
            }
            
            try {
                new URL(input.value);
                input.setCustomValidity('');
            } catch (_) {
                input.setCustomValidity('Please enter a valid URL starting with http:// or https://');
            }
        });
    </script>
</body>
</html>