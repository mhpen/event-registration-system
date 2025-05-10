<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: adminLogin.php');
    exit();
}
?>
<?php include '../shared/header.php'; ?>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php include '../shared/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800">Attendance Tracking</h1>
                    <div class="flex space-x-4">
                        <select id="eventFilter" class="rounded-lg border px-4 py-2">
                            <option value="">All Events</option>
                            <?php
                            require_once '../../config/dbconn.php';
                            try {
                                $stmt = $conn->query("SELECT event_id, title FROM events ORDER BY event_date DESC");
                                while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    echo "<option value='{$event['event_id']}'>{$title}</option>";
                                }
                            } catch(PDOException $e) {
                                echo "<option value=''>Error loading events</option>";
                            }
                            ?>
                        </select>
                        <button onclick="exportAttendanceReport()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-download mr-2"></i>Export Report
                        </button>
                    </div>
                </div>

                <!-- Attendance Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <?php
                    try {
                        // Total Registrations
                        $stmt = $conn->query("SELECT COUNT(*) FROM registrations WHERE status = 'registered'");
                        $totalRegistrations = $stmt->fetchColumn();

                        // Total Check-ins
                        $stmt = $conn->query("SELECT COUNT(*) FROM checkins WHERE status = 'checked_in'");
                        $totalCheckins = $stmt->fetchColumn();

                        // Average Attendance Rate
                        $stmt = $conn->query("
                            SELECT 
                                ROUND(AVG(attendance_rate), 2) as avg_rate
                            FROM (
                                SELECT 
                                    e.event_id,
                                    (COUNT(DISTINCT c.checkin_id) * 100.0 / COUNT(DISTINCT r.registration_id)) as attendance_rate
                                FROM events e
                                LEFT JOIN registrations r ON e.event_id = r.event_id
                                LEFT JOIN checkins c ON e.event_id = c.event_id
                                GROUP BY e.event_id
                            ) as rates
                        ");
                        $avgAttendanceRate = $stmt->fetchColumn() ?: 0;
                    } catch(PDOException $e) {
                        error_log("Error fetching attendance stats: " . $e->getMessage());
                    }
                    ?>
                    
                    <!-- Stats Cards -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Registrations</h3>
                                <p class="text-2xl font-semibold"><?= number_format($totalRegistrations) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Check-ins</h3>
                                <p class="text-2xl font-semibold"><?= number_format($totalCheckins) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Average Attendance Rate</h3>
                                <p class="text-2xl font-semibold"><?= number_format($avgAttendanceRate, 1) ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <table class="w-full" id="attendanceTable">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-4">Event</th>
                                    <th class="pb-4">Date</th>
                                    <th class="pb-4">Total Registrations</th>
                                    <th class="pb-4">Checked In</th>
                                    <th class="pb-4">Attendance Rate</th>
                                    <th class="pb-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php
                                try {
                                    $stmt = $conn->query("
                                        SELECT 
                                            e.event_id,
                                            e.title,
                                            e.event_date,
                                            COUNT(DISTINCT r.registration_id) as total_registrations,
                                            COUNT(DISTINCT c.checkin_id) as total_checkins,
                                            CASE 
                                                WHEN COUNT(DISTINCT r.registration_id) > 0 
                                                THEN ROUND((COUNT(DISTINCT c.checkin_id) * 100.0 / COUNT(DISTINCT r.registration_id)), 1)
                                                ELSE 0 
                                            END as attendance_rate
                                        FROM events e
                                        LEFT JOIN registrations r ON e.event_id = r.event_id
                                        LEFT JOIN checkins c ON e.event_id = c.event_id
                                        GROUP BY e.event_id, e.title, e.event_date
                                        ORDER BY e.event_date DESC
                                    ");

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $title = html_entity_decode($row['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                        $attendanceClass = $row['attendance_rate'] >= 70 ? 'text-green-600' : 
                                                         ($row['attendance_rate'] >= 50 ? 'text-yellow-600' : 'text-red-600');
                                        ?>
                                        <tr>
                                            <td class="py-4"><?= $title ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($row['event_date'])) ?></td>
                                            <td><?= number_format($row['total_registrations']) ?></td>
                                            <td><?= number_format($row['total_checkins']) ?></td>
                                            <td class="<?= $attendanceClass ?>"><?= $row['attendance_rate'] ?>%</td>
                                            <td>
                                                <button onclick="viewEventDetails(<?= $row['event_id'] ?>)" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye mr-1"></i>View Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='6' class='text-center text-red-600 py-4'>Error loading attendance records</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800" id="modalTitle">Event Details</h2>
                <button onclick="closeEventModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="eventDetailsContent">
                <!-- Content will be dynamically loaded -->
            </div>
        </div>
    </div>

    <script>
        function viewEventDetails(eventId) {
            fetch(`../../controllers/admin/getEventAttendance.php?event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('eventDetailsModal');
                    const content = document.getElementById('eventDetailsContent');
                    
                    // Decode HTML entities in JavaScript
                    const decodeHTML = (html) => {
                        const textarea = document.createElement('textarea');
                        textarea.innerHTML = html;
                        return textarea.value;
                    };
                    
                    content.innerHTML = `
                        <div class="mb-4">
                            <h3 class="font-semibold">${decodeHTML(data.event.title)}</h3>
                            <p class="text-gray-600">${data.event.date}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b">
                                        <th class="pb-2">Participant</th>
                                        <th class="pb-2">Email</th>
                                        <th class="pb-2">Check-in Time</th>
                                        <th class="pb-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    ${data.attendees.map(attendee => `
                                        <tr>
                                            <td class="py-2">${decodeHTML(attendee.name)}</td>
                                            <td>${attendee.email}</td>
                                            <td>${attendee.checkin_time || 'Not checked in'}</td>
                                            <td>
                                                <span class="px-2 py-1 rounded-full text-sm ${
                                                    attendee.status === 'checked_in' 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : 'bg-yellow-100 text-yellow-800'
                                                }">
                                                    ${attendee.status === 'checked_in' ? 'Checked In' : 'Registered'}
                                                </span>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    modal.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load event details');
                });
        }

        function closeEventModal() {
            document.getElementById('eventDetailsModal').classList.add('hidden');
        }

        function exportAttendanceReport() {
            const eventId = document.getElementById('eventFilter').value;
            window.location.href = `../../controllers/admin/exportAttendance.php${eventId ? '?event_id=' + eventId : ''}`;
        }

        // Event filter functionality
        document.getElementById('eventFilter').addEventListener('change', function() {
            const eventId = this.value;
            const rows = document.querySelectorAll('#attendanceTable tbody tr');
            
            if (!eventId) {
                rows.forEach(row => row.style.display = '');
                return;
            }

            rows.forEach(row => {
                const shouldShow = row.querySelector('button')?.getAttribute('onclick')?.includes(eventId);
                row.style.display = shouldShow ? '' : 'none';
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventDetailsModal');
            if (event.target === modal) {
                closeEventModal();
            }
        }
    </script>
</body>
</html> 