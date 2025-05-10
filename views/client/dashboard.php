<?php
session_start();
if (!isset($_SESSION['participant'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Dashboard - Event Registration System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold">Event Registration System</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationBtn" class="text-gray-500 hover:text-gray-700">
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">2</span>
                            <i class="fas fa-bell text-xl"></i>
                        </button>
                    </div>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['participant_name']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add this after the navigation section -->
    <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <?php
                    switch ($_GET['success']) {
                        case '1':
                            echo "Successfully registered for the event!";
                            break;
                        case '2':
                            echo "Successfully unregistered from the event.";
                            break;
                        default:
                            echo "Operation completed successfully.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?php
                    switch ($_GET['error']) {
                        case '1':
                            echo "You are already registered for this event.";
                            break;
                        case '2':
                            echo "Failed to process your request. Please try again.";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Add this right after the success/error alerts div -->
    <script>
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
    </script>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <div class="flex space-x-8">
                <button onclick="switchTab('available')" 
                    id="availableTab" 
                    class="border-b-2 border-blue-500 py-2 px-1 text-blue-600 font-medium">
                    Available Events
                </button>
                <button onclick="switchTab('registered')" 
                    id="registeredTab" 
                    class="py-2 px-1 text-gray-500 hover:text-gray-700">
                    My Registrations
                </button>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="mb-6 flex justify-between items-center">
            <div class="flex space-x-4 items-center">
                <input type="text" id="searchInput" placeholder="Search events..." 
                    class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select id="categoryFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <option value="tech">Technology</option>
                    <option value="business">Business</option>
                    <option value="social">Social</option>
                </select>
            </div>
        </div>

        <!-- Events Grid -->
        <div id="availableEvents" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            require_once '../../config/dbconn.php';
            
            try {
                // Get all upcoming events and user's registrations
                $stmt = $conn->prepare("
                    SELECT 
                        e.*,
                        r.registration_id,
                        r.status as registration_status,
                        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as registered_count
                    FROM events e
                    LEFT JOIN registrations r ON e.event_id = r.event_id AND r.participant_id = ?
                    WHERE e.event_date > NOW()
                    ORDER BY e.event_date ASC
                ");
                $stmt->execute([$_SESSION['participant_id']]);
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($events as $event) {
                    // Decode HTML entities for text fields
                    $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $description = html_entity_decode($event['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $location = html_entity_decode($event['location'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200" 
                         data-category="<?php echo htmlspecialchars($event['category']); ?>">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold"><?php echo $title; ?></h3>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    <?php echo $event['registered_count']; ?> registered
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-4 line-clamp-2"><?php echo $description; ?></p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-calendar-alt w-5"></i>
                                    <span><?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt w-5"></i>
                                    <span><?php echo $location; ?></span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button onclick="viewEventDetails(<?php echo $event['event_id']; ?>)" 
                                    class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-info-circle"></i> View Details
                                </button>
                                
                                <?php if ($event['registration_id']): ?>
                                    <button onclick="unregisterFromEvent(<?php echo $event['event_id']; ?>)" 
                                        class="px-4 py-2 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors">
                                        Unregister
                                    </button>
                                <?php else: ?>
                                    <button onclick="registerForEvent(<?php echo $event['event_id']; ?>)" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                        Register
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            ?>
        </div>

        <div id="registeredEvents" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            try {
                // Get user's registered events
                $stmt = $conn->prepare("
                    SELECT 
                        e.*,
                        r.registration_code,
                        r.status as registration_status,
                        r.created_at as registration_date
                    FROM registrations r
                    JOIN events e ON r.event_id = e.event_id
                    WHERE r.participant_id = ?
                    ORDER BY e.event_date ASC
                ");
                $stmt->execute([$_SESSION['participant_id']]);
                $registeredEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($registeredEvents as $event) {
                    $isPast = strtotime($event['event_date']) < time();
                    // Decode HTML entities
                    $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $description = html_entity_decode($event['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $location = html_entity_decode($event['location'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200" 
                         data-category="<?php echo htmlspecialchars($event['category']); ?>">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold"><?php echo $title; ?></h3>
                                <span class="px-2 py-1 <?php echo $isPast ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'; ?> rounded-full text-sm">
                                    <?php echo $isPast ? 'Past Event' : 'Upcoming'; ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-4 line-clamp-2"><?php echo $description; ?></p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-calendar-alt w-5"></i>
                                    <span><?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt w-5"></i>
                                    <span><?php echo $location; ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-ticket-alt w-5"></i>
                                    <span>Registration Code: <?php echo $event['registration_code']; ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-clock w-5"></i>
                                    <span>Registered on: <?php echo date('M d, Y', strtotime($event['registration_date'])); ?></span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button onclick="viewEventDetails(<?php echo $event['event_id']; ?>)" 
                                    class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-info-circle"></i> View Details
                                </button>
                                
                                <?php if (!$isPast): ?>
                                    <button onclick="unregisterFromEvent(<?php echo $event['event_id']; ?>)" 
                                        class="px-4 py-2 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors">
                                        <i class="fas fa-times"></i> Unregister
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                
                if (empty($registeredEvents)) {
                    echo '<div class="col-span-3 text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>You haven\'t registered for any events yet.</p>
                          </div>';
                }
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            ?>
        </div>
    </main>

    <!-- Event Details Modal -->
    <div id="eventModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-semibold" id="modalTitle"></h2>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalContent" class="space-y-4">
                <!-- Content will be dynamically inserted here -->
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeEventModal()" 
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" id="confirmationTitle">Confirm Action</h3>
            <p id="confirmationMessage" class="text-gray-600 mb-6"></p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeConfirmationModal()" 
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmButton"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Add QR Code generation script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    document.querySelectorAll('[id^="qrcode-"]').forEach(element => {
        const code = element.id.replace('qrcode-', '');
        new QRCode(element, {
            text: code,
            width: 128,
            height: 128
        });
    });
    </script>

    <script>
        // Tab switching functionality
        function switchTab(tab) {
            const availableTab = document.getElementById('availableTab');
            const registeredTab = document.getElementById('registeredTab');
            const availableEvents = document.getElementById('availableEvents');
            const registeredEvents = document.getElementById('registeredEvents');

            if (tab === 'available') {
                availableTab.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
                availableTab.classList.remove('text-gray-500');
                registeredTab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600');
                registeredTab.classList.add('text-gray-500');
                availableEvents.classList.remove('hidden');
                registeredEvents.classList.add('hidden');
            } else {
                registeredTab.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
                registeredTab.classList.remove('text-gray-500');
                availableTab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600');
                availableTab.classList.add('text-gray-500');
                registeredEvents.classList.remove('hidden');
                availableEvents.classList.add('hidden');
            }
        }

        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const eventCards = document.querySelectorAll('.bg-white.rounded-lg');

            function filterEvents() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCategory = categoryFilter.value.toLowerCase();

                eventCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p').textContent.toLowerCase();
                    const category = card.getAttribute('data-category') ? card.getAttribute('data-category').toLowerCase() : '';

                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    const matchesCategory = !selectedCategory || category === selectedCategory;

                    if (matchesSearch && matchesCategory) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterEvents);
            categoryFilter.addEventListener('change', filterEvents);
        });

        function viewEventDetails(eventId) {
            fetch(`../../controllers/client/getEventDetails.php?id=${eventId}`)
                .then(response => response.json())
                .then(event => {
                    document.getElementById('modalTitle').textContent = event.title;
                    
                    // Format the date
                    const eventDate = new Date(event.event_date);
                    const formattedDate = eventDate.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Create the modal content with more details
                    document.getElementById('modalContent').innerHTML = `
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700 leading-relaxed">${event.description}</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">
                                        <i class="far fa-calendar-alt mr-2"></i>Date & Time
                                    </h4>
                                    <p class="text-gray-700">${formattedDate}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>Location
                                    </h4>
                                    <p class="text-gray-700">${event.location}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">
                                        <i class="fas fa-users mr-2"></i>Category
                                    </h4>
                                    <p class="text-gray-700">${event.category || 'Not specified'}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">
                                        <i class="fas fa-ticket-alt mr-2"></i>Registration Status
                                    </h4>
                                    <p class="text-gray-700">${event.registration_id ? 'Registered' : 'Not Registered'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Show the modal
                    const modal = document.getElementById('eventModal');
                    modal.style.display = 'flex';
                    
                    // Add click event to close modal when clicking outside
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            closeEventModal();
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching event details:', error);
                    alert('Failed to load event details. Please try again.');
                });
        }

        function closeEventModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEventModal();
            }
        });

        function registerForEvent(eventId) {
            document.getElementById('confirmationTitle').textContent = 'Confirm Registration';
            document.getElementById('confirmationMessage').textContent = 'Are you sure you want to register for this event?';
            document.getElementById('confirmButton').onclick = () => {
                // Create and submit a form instead of direct navigation
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../controllers/client/registerEvent.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'event_id';
                input.value = eventId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };
            document.getElementById('confirmationModal').style.display = 'flex';
        }

        function unregisterFromEvent(eventId) {
            document.getElementById('confirmationTitle').textContent = 'Confirm Unregistration';
            document.getElementById('confirmationMessage').textContent = 'Are you sure you want to unregister from this event?';
            document.getElementById('confirmButton').onclick = () => {
                window.location.href = `../../controllers/client/unregisterEvent.php?event_id=${eventId}`;
            };
            document.getElementById('confirmationModal').style.display = 'flex';
        }

        function closeConfirmationModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }
    </script>
</body>
</html> 