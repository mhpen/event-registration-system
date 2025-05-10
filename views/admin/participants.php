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
                    <h1 class="text-2xl font-semibold text-gray-800">Participant Management</h1>
                    <button onclick="openAddParticipantModal()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Participant
                    </button>
                </div>

                <!-- Participant List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Email</th>
                                    <th class="pb-4">Phone</th>
                                    <th class="pb-4">Events Attended</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php
                                require_once '../../config/dbconn.php';
                                
                                try {
                                    $stmt = $conn->prepare("
                                        SELECT 
                                            p.*,
                                            COALESCE(COUNT(DISTINCT r.event_id), 0) as events_attended
                                        FROM participants p
                                        LEFT JOIN registrations r ON p.participant_id = r.participant_id
                                        WHERE p.status IS NOT NULL  -- Ensure status exists
                                        GROUP BY 
                                            p.participant_id, 
                                            p.first_name, 
                                            p.last_name, 
                                            p.email, 
                                            p.phone, 
                                            p.status
                                        ORDER BY p.last_name, p.first_name
                                    ");
                                    $stmt->execute();
                                    
                                    if ($stmt->rowCount() > 0) {
                                        while ($participant = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $fullName = htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']);
                                            // Add default value for status if not set
                                            $participantStatus = isset($participant['status']) ? $participant['status'] : 'inactive';
                                            $status = $participantStatus == 'active' ? 
                                                '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Active</span>' : 
                                                '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Inactive</span>';
                                            
                                            echo "<tr class='hover:bg-gray-50'>";
                                            echo "<td class='py-4'>{$fullName}</td>";
                                            echo "<td>" . htmlspecialchars($participant['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($participant['phone']) . "</td>";
                                            echo "<td>{$participant['events_attended']}</td>";
                                            echo "<td>{$status}</td>";
                                            echo "<td class='space-x-2'>
                                                    <button onclick='viewParticipant({$participant['participant_id']})' 
                                                        class='text-blue-600 hover:text-blue-800'>View</button>
                                                    <button onclick='editParticipant({$participant['participant_id']})' 
                                                        class='text-green-600 hover:text-green-800'>Edit</button>
                                                    <button onclick='toggleStatus({$participant['participant_id']})' 
                                                        class='text-red-600 hover:text-red-800'>
                                                        " . ($participantStatus == 'active' ? 'Deactivate' : 'Activate') . "
                                                    </button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center py-4 text-gray-500'>No participants found</td></tr>";
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='6' class='text-center py-4 text-red-600'>Error: " . $e->getMessage() . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Participant Modal -->
    <div id="addParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Add New Participant</h2>
                <button onclick="closeAddParticipantModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addParticipantForm" action="../../controllers/admin/addParticipant.php" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="firstName">
                        First Name
                    </label>
                    <input type="text" id="firstName" name="firstName" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="lastName">
                        Last Name
                    </label>
                    <input type="text" id="lastName" name="lastName" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input type="email" id="email" name="email" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Phone
                    </label>
                    <input type="tel" id="phone" name="phone" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeAddParticipantModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Participant</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Participant Modal -->
    <div id="viewParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Participant Details</h2>
                <button onclick="closeViewModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="participantDetails" class="space-y-4">
                <!-- Details will be populated dynamically -->
            </div>
        </div>
    </div>

    <!-- Edit Participant Modal -->
    <div id="editParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Edit Participant</h2>
                <button onclick="closeEditModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editParticipantForm" action="../../controllers/admin/updateParticipant.php" method="POST">
                <input type="hidden" id="edit_participant_id" name="participant_id">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_firstName">
                        First Name
                    </label>
                    <input type="text" id="edit_firstName" name="firstName" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_lastName">
                        Last Name
                    </label>
                    <input type="text" id="edit_lastName" name="lastName" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_email">
                        Email
                    </label>
                    <input type="email" id="edit_email" name="email" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_phone">
                        Phone
                    </label>
                    <input type="tel" id="edit_phone" name="phone" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Participant</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddParticipantModal() {
            document.getElementById('addParticipantModal').classList.remove('hidden');
        }

        function closeAddParticipantModal() {
            document.getElementById('addParticipantModal').classList.add('hidden');
            document.getElementById('addParticipantForm').reset();
        }

        function viewParticipant(id) {
            fetch(`../../controllers/admin/getParticipant.php?id=${id}`)
                .then(response => response.json())
                .then(participant => {
                    const details = document.getElementById('participantDetails');
                    details.innerHTML = `
                        <div class="border-b pb-4">
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium">${participant.first_name} ${participant.last_name}</p>
                        </div>
                        <div class="border-b pb-4">
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">${participant.email}</p>
                        </div>
                        <div class="border-b pb-4">
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium">${participant.phone}</p>
                        </div>
                        <div class="border-b pb-4">
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium">${participant.status}</p>
                        </div>
                        <div class="pt-4">
                            <p class="text-sm text-gray-600">Registered Events</p>
                            <p class="font-medium">${participant.events_attended} events</p>
                        </div>
                    `;
                    document.getElementById('viewParticipantModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load participant details');
                });
        }

        function closeViewModal() {
            document.getElementById('viewParticipantModal').classList.add('hidden');
        }

        function editParticipant(id) {
            fetch(`../../controllers/admin/getParticipant.php?id=${id}`)
                .then(response => response.json())
                .then(participant => {
                    document.getElementById('edit_participant_id').value = participant.participant_id;
                    document.getElementById('edit_firstName').value = participant.first_name;
                    document.getElementById('edit_lastName').value = participant.last_name;
                    document.getElementById('edit_email').value = participant.email;
                    document.getElementById('edit_phone').value = participant.phone;
                    
                    document.getElementById('editParticipantModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load participant details');
                });
        }

        function closeEditModal() {
            document.getElementById('editParticipantModal').classList.add('hidden');
            document.getElementById('editParticipantForm').reset();
        }

        function toggleStatus(id) {
            if (confirm('Are you sure you want to change this participant\'s status?')) {
                fetch(`../../controllers/admin/toggleParticipantStatus.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update status');
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = [
                document.getElementById('addParticipantModal'),
                document.getElementById('viewParticipantModal'),
                document.getElementById('editParticipantModal')
            ];
            
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        // Show success/error messages if present in URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                alert('Operation completed successfully!');
            }
            if (urlParams.has('error')) {
                alert('An error occurred. Please try again.');
            }
        });
    </script>
</body>
</html> 