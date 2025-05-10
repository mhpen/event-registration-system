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
                    <h1 class="text-2xl font-semibold text-gray-800">Check-in Management</h1>
                    <div class="flex space-x-4">
                        <button onclick="openQRScanner()" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            <i class="fas fa-qrcode mr-2"></i>Scan QR Code
                        </button>
                        <button onclick="openManualCheckin()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-user-check mr-2"></i>Manual Check-in
                        </button>
                    </div>
                </div>

                <!-- Active Events for Check-in -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">Active Events</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php
                            require_once '../../config/dbconn.php';
                            
                            try {
                                $stmt = $conn->prepare("
                                    SELECT * FROM events 
                                    WHERE event_date >= CURDATE() 
                                    AND event_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                    ORDER BY event_date ASC
                                ");
                                $stmt->execute();
                                
                                while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Decode HTML entities for text fields
                                    $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $location = html_entity_decode($event['location'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    
                                    echo "
                                    <div class='border rounded-lg p-4 hover:bg-gray-50 cursor-pointer'
                                         onclick='selectEvent({$event['event_id']})'>
                                        <h3 class='font-semibold'>{$title}</h3>
                                        <p class='text-sm text-gray-600'>" . date('g:i A', strtotime($event['event_date'])) . "</p>
                                        <p class='text-sm text-gray-600'>{$location}</p>
                                    </div>";
                                }
                            } catch(PDOException $e) {
                                echo "<div class='col-span-3 text-center text-red-600'>Error: " . $e->getMessage() . "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Check-ins -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold">Recent Check-ins</h2>
                            <div class="flex items-center space-x-4">
                                <!-- Event Filter Dropdown -->
                                <select id="eventFilter" onchange="filterCheckins()" 
                                    class="border rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Events</option>
                                    <?php
                                    try {
                                        $stmt = $conn->prepare("
                                            SELECT DISTINCT e.event_id, e.title 
                                            FROM events e
                                            JOIN checkins c ON e.event_id = c.event_id
                                            WHERE e.event_date >= CURDATE() 
                                            AND e.event_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                            ORDER BY e.event_date ASC
                                        ");
                                        $stmt->execute();
                                        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            echo "<option value='{$event['event_id']}'>{$title}</option>";
                                        }
                                    } catch(PDOException $e) {
                                        echo "<option value=''>Error loading events</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div id="checkinsTableContainer">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b">
                                        <th class="pb-4 px-4 font-semibold text-gray-600">Participant</th>
                                        <th class="pb-4 px-4 font-semibold text-gray-600">Event</th>
                                        <th class="pb-4 px-4 font-semibold text-gray-600">Check-in Time</th>
                                        <th class="pb-4 px-4 font-semibold text-gray-600">Status</th>
                                        <th class="pb-4 px-4 font-semibold text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" id="checkinsTableBody">
                                    <!-- Check-ins will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Check-in Modal -->
    <div id="manualCheckinModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Manual Check-in</h2>
                <button onclick="closeManualCheckin()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="manualCheckinForm" onsubmit="submitManualCheckin(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="eventSelect">
                        Select Event
                    </label>
                    <select id="eventSelect" name="event_id" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select an event...</option>
                        <?php
                        try {
                            $stmt = $conn->prepare("
                                SELECT event_id, title, event_date 
                                FROM events 
                                WHERE event_date >= CURDATE() 
                                AND event_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                ORDER BY event_date ASC
                            ");
                            $stmt->execute();
                            while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                echo "<option value='" . $event['event_id'] . "'>" . 
                                    $title . " - " . 
                                    date('g:i A', strtotime($event['event_date'])) . 
                                    "</option>";
                            }
                        } catch(PDOException $e) {
                            echo "<option value=''>Error loading events</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="registrationCode">
                        Registration Code
                    </label>
                    <input type="text" id="registrationCode" name="registration_code" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Enter registration code">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        rows="3"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeManualCheckin()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Check In</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add QR Scanner Modal -->
    <div id="qrScannerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Scan QR Code</h2>
                <button onclick="closeQRScanner()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center">
                <div id="qr-reader" class="mb-4"></div>
                <div id="qr-reader-results"></div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let selectedEventId = null;
        let html5QrcodeScanner = null;

        function selectEvent(eventId) {
            selectedEventId = eventId;
            // Highlight selected event
            document.querySelectorAll('.border').forEach(el => {
                if (el.getAttribute('onclick') === `selectEvent(${eventId})`) {
                    el.classList.add('border-blue-500', 'bg-blue-50');
                    // Update the event filter dropdown
                    document.getElementById('eventFilter').value = eventId;
                    // Load check-ins for this event
                    loadCheckins(eventId);
                } else {
                    el.classList.remove('border-blue-500', 'bg-blue-50');
                }
            });
        }

        function openManualCheckin() {
            if (!selectedEventId) {
                alert('Please select an event first');
                return;
            }
            document.getElementById('eventSelect').value = selectedEventId;
            document.getElementById('manualCheckinModal').classList.remove('hidden');
        }

        function closeManualCheckin() {
            document.getElementById('manualCheckinModal').classList.add('hidden');
            document.getElementById('manualCheckinForm').reset();
        }

        function submitManualCheckin(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            console.log('Submitting check-in:', {
                event_id: formData.get('event_id'),
                registration_code: formData.get('registration_code')
            });
            
            fetch('../../controllers/admin/processCheckin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Check-in successful!');
                    closeManualCheckin();
                    window.location.reload();
                } else {
                    alert(data.message || 'Check-in failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function openQRScanner() {
            if (!selectedEventId) {
                alert('Please select an event first');
                return;
            }
            
            document.getElementById('qrScannerModal').classList.remove('hidden');
            
            if (html5QrcodeScanner === null) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", { fps: 10, qrbox: 250 }
                );
                
                html5QrcodeScanner.render((decodedText) => {
                    // Handle QR code scan success
                    processQRCode(decodedText);
                });
            }
        }

        function closeQRScanner() {
            document.getElementById('qrScannerModal').classList.add('hidden');
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }
        }

        function processQRCode(registrationCode) {
            const formData = new FormData();
            formData.append('event_id', selectedEventId);
            formData.append('registration_code', registrationCode);
            formData.append('check_in_method', 'qr');

            fetch('../../controllers/admin/processCheckin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Check-in successful!');
                    closeQRScanner();
                    window.location.reload();
                } else {
                    alert(data.message || 'Check-in failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function loadCheckins(eventId = '') {
            fetch(`../../controllers/admin/getCheckins.php?event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('checkinsTableBody');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">
                                    No check-ins found for this event
                                </td>
                            </tr>`;
                        return;
                    }

                    data.forEach(checkin => {
                        const row = `
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-4">${checkin.participant_name}</td>
                                <td class="py-4 px-4">${checkin.event_title}</td>
                                <td class="py-4 px-4">${formatDate(checkin.checkin_time)}</td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-1 ${checkin.status === 'checked_in' ? 
                                        'bg-green-100 text-green-800' : 
                                        'bg-yellow-100 text-yellow-800'} rounded-full">
                                        ${checkin.status === 'checked_in' ? 'Checked In' : 'Pending'}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <button onclick="viewCheckinDetails(${checkin.checkin_id})" 
                                        class="text-blue-600 hover:text-blue-800">
                                        View
                                    </button>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('checkinsTableBody').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4 text-red-600">
                                Error loading check-ins
                            </td>
                        </tr>`;
                });
        }

        function filterCheckins() {
            const eventId = document.getElementById('eventFilter').value;
            loadCheckins(eventId);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Load all check-ins initially
        document.addEventListener('DOMContentLoaded', () => {
            loadCheckins();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = [
                document.getElementById('manualCheckinModal'),
                document.getElementById('qrScannerModal')
            ];
            
            modals.forEach(modal => {
                if (event.target === modal) {
                    if (modal.id === 'qrScannerModal') {
                        closeQRScanner();
                    } else {
                        modal.classList.add('hidden');
                    }
                }
            });
        }
    </script>
</body>
</html> 