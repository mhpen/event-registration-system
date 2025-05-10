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

// Debug information
error_log("Current session data in events.php: " . print_r($_SESSION, true));
echo "<!-- Debug: Admin ID: " . $_SESSION['admin_id'] . " -->";
?>
<?php include '../shared/header.php'; ?>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
</style>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php include '../shared/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800">Event Management</h1>
                    <div class="flex items-center space-x-4">
                        <!-- Search Bar -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search events..." 
                                class="w-64 px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 absolute right-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Filter Dropdown -->
                        <select id="statusFilter" class="px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past</option>
                        </select>

                        <!-- Sort Dropdown -->
                        <select id="sortBy" class="px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="date_desc">Date (Newest)</option>
                            <option value="date_asc">Date (Oldest)</option>
                            <option value="title">Title (A-Z)</option>
                        </select>

                        <!-- View Toggle -->
                        <button onclick="toggleView()" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="viewIcon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Create Event Button -->
                        <button onclick="openEventModal()" 
                            class="bg-blue-600 text-white rounded-lg p-2 hover:bg-blue-700 flex items-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Event List -->
                <div id="tableView" class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Event Name</th>
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Date</th>
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Location</th>
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Description</th>
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Status</th>
                                    <th class="pb-4 px-4 font-semibold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php include '../../controllers/admin/getEvents.php'; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="grid grid-cols-3 gap-6 hidden">
                    <?php include '../../controllers/admin/getEventsGrid.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-6 border w-[600px] shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Create New Event</h2>
                <button onclick="closeEventModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="createEventForm" action="../../controllers/admin/createEvent.php" method="POST">
                <div class="space-y-4">
                    <!-- Title and Category -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="title">
                                Event Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="category">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category" name="category" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Select category</option>
                                <option value="Conference">Conference</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Training">Training</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Social">Social Event</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="description">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" required rows="3"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="event_date">
                                Start Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="event_date" name="event_date" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="event_end_date">
                                End Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="event_end_date" name="event_end_date" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Location and Capacity -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                                Location <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="location" name="location" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="capacity">
                                Capacity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="capacity" name="capacity" required min="-1" value="-1"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Enter -1 for unlimited capacity</p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="eventFormError" class="hidden mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-md"></div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeEventModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update the View Event Modal -->
    <div id="viewEventModal" class="modal-overlay fixed inset-0 hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Event Details</h2>
                    <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="eventDetails" class="space-y-4">
                    <!-- Event details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Edit Event</h2>
                <button onclick="closeEditModal()" class="text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editEventForm" method="POST">
                <input type="hidden" id="edit_event_id" name="event_id">
                
                <!-- Title and Category -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_title">
                            Event Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_title" name="title" required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_category">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select id="edit_category" name="category" required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select category</option>
                            <option value="Conference">Conference</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Training">Training</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Social">Social Event</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_description">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="edit_description" name="description" required rows="3"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <!-- Date and Time -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_event_date">
                            Start Date & Time <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="edit_event_date" name="event_date" required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_event_end_date">
                            End Date & Time <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="edit_event_end_date" name="event_end_date" required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Location and Capacity -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_location">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_location" name="location" required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="edit_capacity">
                            Capacity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_capacity" name="capacity" required min="-1"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Enter -1 for unlimited capacity</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="eventFormError" class="hidden mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-md"></div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-6 border w-[600px] shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Create New Event</h2>
                <button onclick="closeAddEventModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="addEventForm" action="../../controllers/admin/createEvent.php" method="POST">
                <input type="hidden" name="admin_id" value="<?php echo $_SESSION['admin_id']; ?>">
                
                <!-- Event Details -->
                <div class="space-y-4">
                    <!-- Title and Category -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="add_title">
                                Event Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="add_title" name="title" required
                                placeholder="Enter event title"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="category">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category" name="category" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Select category</option>
                                <option value="Conference">Conference</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Training">Training</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Social">Social Event</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="description">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" 
                            name="description" 
                            required 
                            rows="3"
                            placeholder="Enter event description"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="event_date">
                                Start Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="event_date" name="event_date" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="event_end_date">
                                End Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="event_end_date" name="event_end_date" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Location and Capacity -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                                Location <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="location" name="location" required
                                placeholder="Enter venue location"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="capacity">
                                Capacity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="capacity" name="capacity" required min="1"
                                placeholder="Max participants"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="eventFormError" class="hidden mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-md"></div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeAddEventModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEventModal() {
            document.getElementById('eventModal').classList.remove('hidden');
            // Initialize date/time fields
            const now = new Date();
            const nextHour = new Date(now);
            nextHour.setHours(nextHour.getHours() + 1);
            nextHour.setMinutes(0);
            
            const twoHoursLater = new Date(nextHour);
            twoHoursLater.setHours(twoHoursLater.getHours() + 2);
            
            document.getElementById('event_date').value = nextHour.toISOString().slice(0, 16);
            document.getElementById('event_end_date').value = twoHoursLater.toISOString().slice(0, 16);
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            document.getElementById('createEventForm').reset();
            clearError();
        }

        function toggleView() {
            const tableView = document.getElementById('tableView');
            const gridView = document.getElementById('gridView');
            const viewToggleText = document.getElementById('viewToggleText');

            if (tableView.classList.contains('hidden')) {
                tableView.classList.remove('hidden');
                gridView.classList.add('hidden');
                viewToggleText.textContent = 'Grid View';
            } else {
                tableView.classList.add('hidden');
                gridView.classList.remove('hidden');
                viewToggleText.textContent = 'Table View';
            }
        }

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                window.location.href = '../../controllers/admin/deleteEvent.php?id=' + eventId;
            }
        }

        // Show success/error messages
        <?php if (isset($_GET['success'])): ?>
            alert('Event operation completed successfully!');
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <?php
            $errorMessages = [
                '1' => 'An error occurred during the operation.',
                '2' => 'Admin session is invalid. Please try logging in again.',
                '3' => 'Failed to create event in database. Please try again.',
                '4' => 'Database error occurred. Please contact support.',
                '5' => 'Event start date must be in the future.',
                '6' => 'Event end date must be after start date.',
                '7' => 'Event duration cannot exceed 12 hours.',
                '8' => 'All required fields must be filled out.',
                '9' => 'An unexpected error occurred. Please try again.'
            ];
            $errorMessage = isset($errorMessages[$_GET['error']]) ? 
                $errorMessages[$_GET['error']] : 
                'An unknown error occurred.';
            ?>
            <script>
                showError('<?php echo addslashes($errorMessage); ?>');
            </script>
        <?php endif; ?>

        function viewEvent(eventId) {
            fetch(`../../controllers/admin/getEventDetails.php?id=${eventId}`)
                .then(response => response.json())
                .then(event => {
                    if (event.error) {
                        alert(event.error);
                        return;
                    }
                    
                    const details = document.getElementById('eventDetails');
                    details.innerHTML = `
                        <div class="border-b pb-4">
                            <h3 class="text-lg font-semibold text-blue-600">${event.title}</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 py-4 border-b">
                            <div>
                                <p class="font-medium text-gray-600">Date & Time</p>
                                <p>${new Date(event.event_date).toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-600">Location</p>
                                <p>${event.location}</p>
                            </div>
                        </div>
                        <div class="pt-4">
                            <p class="font-medium text-gray-600 mb-2">Description</p>
                            <p class="text-gray-700 whitespace-pre-wrap">${event.description}</p>
                        </div>
                    `;
                    document.getElementById('viewEventModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load event details');
                });
        }

        function closeViewModal() {
            document.getElementById('viewEventModal').classList.add('hidden');
        }

        // Add this after your existing JavaScript

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const sortBy = document.getElementById('sortBy');

            function filterAndSortEvents() {
                const searchTerm = searchInput.value.toLowerCase();
                const status = statusFilter.value;
                const sort = sortBy.value;

                fetch(`../../controllers/admin/filterEvents.php?search=${searchTerm}&status=${status}&sort=${sort}`)
                    .then(response => response.json())
                    .then(events => {
                        updateEventsList(events);
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Add event listeners
            searchInput.addEventListener('input', filterAndSortEvents);
            statusFilter.addEventListener('change', filterAndSortEvents);
            sortBy.addEventListener('change', filterAndSortEvents);

            function updateEventsList(events) {
                const tableBody = document.querySelector('#tableView tbody');
                const gridView = document.getElementById('gridView');
                
                // Update table view
                tableBody.innerHTML = events.map(event => `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3">${event.title}</td>
                        <td>${new Date(event.event_date).toLocaleDateString()}</td>
                        <td>${event.location}</td>
                        <td>${event.description.substring(0, 50)}...</td>
                        <td>${getStatusBadge(event.event_date)}</td>
                        <td class="space-x-2">
                            <button onclick="viewEvent(${event.event_id})" class="text-blue-600 hover:text-blue-800">View</button>
                            <button onclick="editEvent(${event.event_id})" class="text-green-600 hover:text-green-800">Edit</button>
                            <button onclick="deleteEvent(${event.event_id})" class="text-red-600 hover:text-red-800">Delete</button>
                        </td>
                    </tr>
                `).join('');

                // Update grid view
                gridView.innerHTML = events.map(event => `
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="font-semibold">${event.title}</h3>
                        <p class="text-sm text-gray-600 mt-2">${event.description.substring(0, 100)}...</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-sm">${new Date(event.event_date).toLocaleDateString()}</span>
                            <button onclick="viewEvent(${event.event_id})" class="text-blue-600 hover:text-blue-800">View Details</button>
                        </div>
                    </div>
                `).join('');
            }

            function getStatusBadge(date) {
                const eventDate = new Date(date);
                const now = new Date();
                const isPast = eventDate < now;
                
                return isPast 
                    ? '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">Past</span>'
                    : '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Upcoming</span>';
            }
        });

        function editEvent(eventId) {
            // Fetch event details
            fetch(`../../controllers/admin/getEventDetails.php?id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const event = data.data;
                        
                        // Populate the edit form with decoded values
                        document.getElementById('edit_event_id').value = event.event_id;
                        document.getElementById('edit_title').value = decodeHTMLEntities(event.title);
                        document.getElementById('edit_description').value = decodeHTMLEntities(event.description);
                        document.getElementById('edit_category').value = event.category;
                        
                        // Format dates for datetime-local input
                        const startDate = new Date(event.event_date);
                        const endDate = new Date(event.event_end_date);
                        document.getElementById('edit_event_date').value = startDate.toISOString().slice(0, 16);
                        document.getElementById('edit_event_end_date').value = endDate.toISOString().slice(0, 16);
                        
                        document.getElementById('edit_location').value = decodeHTMLEntities(event.location);
                        document.getElementById('edit_capacity').value = event.capacity;

                        // Show the modal
                        document.getElementById('editEventModal').classList.remove('hidden');
                    } else {
                        showError(data.error || 'Failed to fetch event details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to fetch event details');
                });
        }

        // Add this helper function to decode HTML entities in JavaScript
        function decodeHTMLEntities(text) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = text;
            return textarea.value;
        }

        function closeEditModal() {
            document.getElementById('editEventModal').classList.add('hidden');
            document.getElementById('editEventForm').reset();
            clearError();
        }

        // Add form submission handler for edit form
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
                    // Refresh the events list or update the specific row
                    window.location.reload(); // Or implement a more elegant solution to update just the changed row
                } else {
                    showError(data.error || 'Failed to update event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to update event');
            });
        });

        // Replace the form validation JavaScript with this simplified version
        document.getElementById('addEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearError();

            // Get form values
            const startDate = new Date(document.getElementById('event_date').value);
            const endDate = new Date(document.getElementById('event_end_date').value);

            // Only validate that end date is after start date
            if (endDate <= startDate) {
                showError('Event end date must be after start date');
                return;
            }

            // If validation passes, submit the form
            this.submit();
        });

        // Keep only these essential functions
        function showError(message, modalId = null) {
            const errorDiv = modalId 
                ? document.querySelector(`#${modalId} #eventFormError`)
                : document.getElementById('eventFormError');
            
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.remove('hidden');
            }
        }

        function clearError() {
            const errorDiv = document.getElementById('eventFormError');
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handling
            document.getElementById('createEventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const startDate = new Date(document.getElementById('event_date').value);
                const endDate = new Date(document.getElementById('event_end_date').value);
                const now = new Date();

                clearError();

                // Validate dates
                if (startDate < now) {
                    showError('Event start date must be in the future');
                    return;
                }

                if (endDate <= startDate) {
                    showError('Event end date must be after start date');
                    return;
                }

                // If validation passes, submit the form
                this.submit();
            });

            // Update end date when start date changes
            document.getElementById('event_date').addEventListener('change', function() {
                const startDate = new Date(this.value);
                const endDate = new Date(startDate);
                endDate.setHours(endDate.getHours() + 2);
                document.getElementById('event_end_date').value = endDate.toISOString().slice(0, 16);
            });
        });
    </script>
</body>
</html>