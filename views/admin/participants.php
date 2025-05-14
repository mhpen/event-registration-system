<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: adminLogin.php');
    exit();
}

// Add database connection
require_once '../../config/dbconn.php';
?>
<?php include '../shared/header.php'; ?>

<body class="bg-background">
    <div class="flex h-screen">
        <?php include '../shared/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto bg-background">
            <div class="p-6">
                <!-- Header with actions -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Participants</h1>
                        <p class="text-sm text-muted-foreground">Manage registered participants</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="Search participants..." 
                                    class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <svg class="w-4 h-4 absolute right-3 top-2.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Status Filter -->
                            <select id="statusFilter" 
                                class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>

                            <!-- Sort -->
                            <select id="sortBy" 
                                class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="name_asc">Name (A-Z)</option>
                                <option value="name_desc">Name (Z-A)</option>
                                <option value="date_asc">Registration (Oldest)</option>
                                <option value="date_desc">Registration (Newest)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add this after the header section and before the participants table -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
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
                            $stmt = $conn->query("SELECT COUNT(*) FROM participants");
                            $totalParticipants = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $totalParticipants ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">registered</div>
                        </div>
                    </div>

                    <!-- Active Participants -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Active Participants</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM participants WHERE status = 'active'");
                            $activeParticipants = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $activeParticipants ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">active</div>
                        </div>
                    </div>

                    <!-- Total Events Attended -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Events Attended</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("SELECT COUNT(DISTINCT event_id) FROM checkins");
                            $totalEventsAttended = $stmt->fetchColumn();
                            ?>
                            <div class="text-2xl font-bold"><?= $totalEventsAttended ?></div>
                            <div class="ml-2 text-xs text-muted-foreground">events</div>
                        </div>
                    </div>

                    <!-- Average Attendance Rate -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Participation Rate</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <?php
                            $stmt = $conn->query("
                                SELECT 
                                    ROUND(
                                        (COUNT(DISTINCT c.participant_id) * 100.0) / 
                                        COUNT(DISTINCT p.participant_id), 1
                                    ) as participation_rate
                                FROM participants p
                                LEFT JOIN checkins c ON p.participant_id = c.participant_id
                            ");
                            $participationRate = $stmt->fetchColumn() ?: 0;
                            ?>
                            <div class="text-2xl font-bold"><?= $participationRate ?>%</div>
                            <div class="ml-2 text-xs text-muted-foreground">average rate</div>
                        </div>
                    </div>
                </div>

                <!-- Participants Table -->
                <div class="rounded-lg border bg-card text-card-foreground">
                    <div class="relative w-full overflow-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Email</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Phone</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Registered</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT * FROM participants ORDER BY registered_at DESC");
                                        while ($participant = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $fullName = htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']);
                                        $registeredDate = new DateTime($participant['registered_at']);
                                        $status = $participant['status'] === 'active' ? 
                                            '<span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>' : 
                                            '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">Inactive</span>';
                                        ?>
                                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                            <td class="p-4 align-middle">
                                                <div>
                                                    <div class="font-medium"><?= $fullName ?></div>
                                                    <div class="text-sm text-muted-foreground">ID: <?= $participant['participant_id'] ?></div>
                                                </div>
                                            </td>
                                            <td class="p-4 align-middle"><?= htmlspecialchars($participant['email']) ?></td>
                                            <td class="p-4 align-middle"><?= htmlspecialchars($participant['phone']) ?></td>
                                            <td class="p-4 align-middle"><?= $registeredDate->format('M j, Y') ?></td>
                                            <td class="p-4 align-middle"><?= $status ?></td>
                                            <td class="p-4 align-middle">
                                                <div class="flex items-center gap-2">
                                                    <button onclick="viewParticipant(<?= $participant['participant_id'] ?>)" 
                                                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="toggleStatus(<?= $participant['participant_id'] ?>, '<?= $participant['status'] ?>')" 
                                                        class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 px-3 <?= $participant['status'] === 'active' ? 'text-green-500' : 'text-gray-500' ?>">
                                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <?= ucfirst($participant['status']) ?>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='6' class='p-4 text-center text-sm text-red-500'>Error loading participants</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Participant Modal -->
    <div id="viewParticipantModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden z-50">
        <div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Participant Details</h3>
                    <p class="text-sm text-muted-foreground mt-1">View complete participant information</p>
                </div>
                <button onclick="closeViewModal()" class="text-muted-foreground hover:text-foreground">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div id="participantDetails" class="space-y-4">
                <!-- Participant details will be populated here -->
            </div>
        </div>
    </div>

    <script>
        function viewParticipant(participantId) {
            fetch(`../../controllers/admin/getParticipantDetails.php?id=${participantId}`)
                .then(response => response.json())
                .then(participant => {
                    if (participant.error) {
                        throw new Error(participant.error);
                    }
                    
                    const details = document.getElementById('participantDetails');
                    const registeredDate = new Date(participant.registered_at);
                    
                    details.innerHTML = `
                        <div class="space-y-4">
                            <div class="pb-4 border-b">
                                <h4 class="text-xl font-semibold">${participant.first_name} ${participant.last_name}</h4>
                                <p class="text-sm text-muted-foreground mt-1">ID: ${participant.participant_id}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Email</p>
                                    <p class="mt-1">${participant.email}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Phone</p>
                                    <p class="mt-1">${participant.phone}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Registered Date</p>
                                    <p class="mt-1">${registeredDate.toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Status</p>
                                    <p class="mt-1">
                                        <span class="inline-flex items-center rounded-md ${participant.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20'} px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                            ${participant.status.charAt(0).toUpperCase() + participant.status.slice(1)}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t">
                                <h5 class="text-sm font-medium text-muted-foreground mb-3">Events Attended</h5>
                                ${participant.events_attended.length > 0 ? `
                                    <div class="space-y-2">
                                        ${participant.events_attended.map(event => `
                                            <div class="flex items-center justify-between p-2 rounded-md border">
                                                <div>
                                                    <p class="font-medium">${event.title}</p>
                                                    <p class="text-sm text-muted-foreground">${new Date(event.event_date).toLocaleDateString()}</p>
                        </div>
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                    ${new Date(event.checkin_time).toLocaleTimeString()}
                                                </span>
                        </div>
                                        `).join('')}
                        </div>
                                ` : `
                                    <p class="text-sm text-muted-foreground">No events attended yet</p>
                                `}
                        </div>
                        </div>
                    `;
                    
                    document.getElementById('viewParticipantModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load participant details: ' + error.message);
                });
        }

        function closeViewModal() {
            document.getElementById('viewParticipantModal').classList.add('hidden');
        }

        function toggleStatus(participantId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (!confirm(`Are you sure you want to change the participant's status to ${newStatus}?`)) {
                return;
            }

            fetch('../../controllers/admin/updateParticipantStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    participant_id: participantId,
                    status: newStatus
                })
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

        // Replace the existing search/filter JavaScript with this updated version
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const sortBy = document.getElementById('sortBy');
            const tableBody = document.querySelector('tbody');

            function filterAndSortTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();
                const sort = sortBy.value;

                // Convert rows to array for sorting
                const rows = Array.from(tableBody.querySelectorAll('tr'));

                // Filter rows
                const filteredRows = rows.filter(row => {
                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const rowStatus = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                    const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                    const matchesStatus = !status || rowStatus.includes(status);

                    return matchesSearch && matchesStatus;
                });

                // Sort rows
                filteredRows.sort((a, b) => {
                    const aName = a.querySelector('td:first-child .font-medium').textContent;
                    const bName = b.querySelector('td:first-child .font-medium').textContent;
                    const aDate = new Date(a.querySelector('td:nth-child(4)').textContent);
                    const bDate = new Date(b.querySelector('td:nth-child(4)').textContent);

                    switch (sort) {
                        case 'name_asc':
                            return aName.localeCompare(bName);
                        case 'name_desc':
                            return bName.localeCompare(aName);
                        case 'date_asc':
                            return aDate - bDate;
                        case 'date_desc':
                            return bDate - aDate;
                        default:
                            return 0;
                    }
                });

                // Clear table
                tableBody.innerHTML = '';

                // Show message if no results
                if (filteredRows.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="p-4 text-center text-sm text-muted-foreground">
                                No participants found matching your criteria
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Add filtered and sorted rows back to table
                filteredRows.forEach(row => {
                    tableBody.appendChild(row);
                });
            }

            // Add event listeners
            searchInput.addEventListener('input', filterAndSortTable);
            statusFilter.addEventListener('change', filterAndSortTable);
            sortBy.addEventListener('change', filterAndSortTable);

            // Initialize sort
            filterAndSortTable();
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-background/80')) {
                closeViewModal();
            }
        });
    </script>
</body>
</html> 