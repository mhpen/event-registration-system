<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: adminLogin.php');
    exit();
}
?>
<?php include '../shared/header.php'; ?>

<body class="bg-background">
    <div class="flex h-screen">
        <?php include '../shared/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto bg-background">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Attendance Dashboard</h1>
                        <p class="text-sm text-muted-foreground">Monitor event attendance and check-in statistics</p>
                    </div>
                </div>

                <!-- KPI Cards -->
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
                            <div class="text-2xl font-bold" id="totalEvents">-</div>
                            <div class="ml-2 text-xs text-muted-foreground">events</div>
                        </div>
                    </div>

                    <!-- Total Registrations -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Registrations</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <div class="text-2xl font-bold" id="totalRegistrations">-</div>
                            <div class="ml-2 text-xs text-muted-foreground">registrations</div>
                        </div>
                    </div>

                    <!-- Total Check-ins -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Check-ins</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <div class="text-2xl font-bold" id="totalCheckins">-</div>
                            <div class="ml-2 text-xs text-muted-foreground">check-ins</div>
                        </div>
                    </div>

                    <!-- Average Attendance Rate -->
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                        <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Attendance Rate</h3>
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex items-center pt-2">
                            <div class="text-2xl font-bold" id="attendanceRate">-</div>
                            <div class="ml-2 text-xs text-muted-foreground">average rate</div>
                        </div>
                    </div>
                </div>

                <!-- Event Attendance Table -->
                <div class="rounded-lg border bg-card text-card-foreground">
                    <div class="flex items-center justify-between p-6 pb-4">
                        <h4 class="text-lg font-medium">Event Attendance</h4>
                        <div class="flex items-center gap-2">
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="Search events..." 
                                    class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <svg class="w-4 h-4 absolute right-3 top-2.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Status Filter -->
                            <select id="statusFilter" 
                                class="h-9 px-3 py-1 text-sm rounded-md border border-input bg-background ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">All Events</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="past">Past</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative w-full overflow-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Event</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Registrations</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Check-ins</th>
                                    <th class="h-10 px-4 text-left align-middle font-medium text-muted-foreground">Attendance Rate</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody" class="[&_tr:last-child]:border-0">
                                <!-- Table data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch attendance data and update UI
        function fetchAttendanceData() {
            fetch('../../controllers/admin/getAttendanceData.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Update KPI cards
                    document.getElementById('totalEvents').textContent = data.totalEvents;
                    document.getElementById('totalRegistrations').textContent = data.totalRegistrations;
                    document.getElementById('totalCheckins').textContent = data.totalCheckins;
                    document.getElementById('attendanceRate').textContent = data.averageAttendanceRate + '%';

                    // Populate table
                    const tableBody = document.getElementById('attendanceTableBody');
                    tableBody.innerHTML = data.events.map(event => `
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <td class="p-4 align-middle">
                                <div class="font-medium">${event.title}</div>
                                <div class="text-sm text-muted-foreground">${event.location}</div>
                            </td>
                            <td class="p-4 align-middle">${new Date(event.event_date).toLocaleDateString()}</td>
                            <td class="p-4 align-middle">${event.registrations}</td>
                            <td class="p-4 align-middle">${event.checkins}</td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <div class="w-16">${event.attendance_rate}%</div>
                                    <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-primary" style="width: ${event.attendance_rate}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    // Initialize search and filter
                    initializeSearchAndFilter();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load attendance data: ' + error.message);
                });
        }

        // Search and filter functionality
        function initializeSearchAndFilter() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('#attendanceTableBody tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();

                rows.forEach(row => {
                    const title = row.querySelector('td:first-child').textContent.toLowerCase();
                    const date = new Date(row.querySelector('td:nth-child(2)').textContent);
                    const isUpcoming = date > new Date();
                    
                    const matchesSearch = title.includes(searchTerm);
                    const matchesStatus = !status || 
                        (status === 'upcoming' && isUpcoming) || 
                        (status === 'past' && !isUpcoming);

                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            statusFilter.addEventListener('change', filterTable);
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', fetchAttendanceData);
    </script>
</body>
</html> 