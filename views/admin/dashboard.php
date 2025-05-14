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
                <!-- Header with actions -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Dashboard</h1>
                        <p class="text-sm text-muted-foreground">Overview of your event management system</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="btn-hover inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                            Download Report
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <?php
                    require_once '../../config/dbconn.php';
                    try {
                        // Total Events
                        $stmt = $conn->query("SELECT COUNT(*) FROM events");
                        $totalEvents = $stmt->fetchColumn();

                        // Upcoming Events
                        $stmt = $conn->query("SELECT COUNT(*) FROM events WHERE event_date > NOW()");
                        $upcomingEvents = $stmt->fetchColumn();

                        // Total Participants
                        $stmt = $conn->query("SELECT COUNT(*) FROM participants");
                        $totalParticipants = $stmt->fetchColumn();

                        // Total Registrations
                        $stmt = $conn->query("SELECT COUNT(*) FROM registrations WHERE status = 'registered'");
                        $totalRegistrations = $stmt->fetchColumn();
                    } catch(PDOException $e) {
                        error_log("Error fetching dashboard stats: " . $e->getMessage());
                    }
                    ?>

                    <div class="stat-card p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Total Events</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-2xl font-bold"><?= number_format($totalEvents) ?></span>
                        </div>
                    </div>

                    <div class="stat-card p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Upcoming Events</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-2xl font-bold"><?= number_format($upcomingEvents) ?></span>
                        </div>
                    </div>

                    <div class="stat-card p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Total Participants</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-2xl font-bold"><?= number_format($totalParticipants) ?></span>
                        </div>
                    </div>

                    <div class="stat-card p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Total Registrations</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-2xl font-bold"><?= number_format($totalRegistrations) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="hover-lift p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium">Events by Category</span>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="eventsByCategoryChart"></canvas>
                        </div>
                    </div>

                    <div class="hover-lift p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium">Registration Trends</span>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="registrationTrendsChart"></canvas>
                        </div>
                    </div>

                    <div class="hover-lift p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium">Attendance Rates</span>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="attendanceRateChart"></canvas>
                        </div>
                    </div>

                    <div class="hover-lift p-4 rounded-lg border bg-card text-card-foreground">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium">Popular Events</span>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="eventPopularityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Events Table - Minimal Design -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium">Recent Events</span>
                            <a href="events.php" class="text-sm text-primary hover:underline">View all</a>
                        </div>
                        <div class="relative w-full overflow-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Event</th>
                                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Date</th>
                                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    <?php
                                    try {
                                        $stmt = $conn->query("
                                            SELECT 
                                                e.*,
                                                COUNT(r.registration_id) as registration_count
                                            FROM events e
                                            LEFT JOIN registrations r ON e.event_id = r.event_id
                                            GROUP BY e.event_id
                                            ORDER BY e.event_date DESC
                                            LIMIT 5
                                        ");

                                        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $title = html_entity_decode($event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            $eventDate = new DateTime($event['event_date']);
                                            $now = new DateTime();
                                            $status = $eventDate > $now ? 
                                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Upcoming</span>' : 
                                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700">Past</span>';
                                            ?>
                                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                                <td class="h-10 px-2 text-left align-middle font-medium text-muted-foreground"><?= $title ?></td>
                                                <td class="h-10 px-2 text-left align-middle font-medium text-muted-foreground"><?= $eventDate->format('M j, Y g:i A') ?></td>
                                                <td class="h-10 px-2 text-left align-middle font-medium text-muted-foreground"><?= $status ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } catch(PDOException $e) {
                                        echo "<tr><td colspan='3' class='text-center py-4 text-red-600'>Error loading recent events</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch data for charts
        fetch('../../controllers/admin/getDashboardStats.php')
            .then(response => response.json())
            .then(data => {
                // Events by Category Chart
                new Chart(document.getElementById('eventsByCategoryChart'), {
                    type: 'doughnut',
            data: {
                        labels: data.eventsByCategory.map(item => item.category),
                datasets: [{
                            data: data.eventsByCategory.map(item => item.count),
                            backgroundColor: [
                                '#3B82F6', // blue
                                '#10B981', // green
                                '#8B5CF6', // purple
                                '#F59E0B', // yellow
                                '#EF4444'  // red
                            ]
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Registration Trends Chart
                new Chart(document.getElementById('registrationTrendsChart'), {
                    type: 'line',
                    data: {
                        labels: data.registrationTrends.map(item => item.date),
                        datasets: [{
                            label: 'Registrations',
                            data: data.registrationTrends.map(item => item.count),
                            borderColor: '#3B82F6',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                    }
                }
            }
        });

                // Attendance Rate Chart
                new Chart(document.getElementById('attendanceRateChart'), {
            type: 'bar',
            data: {
                        labels: data.attendanceRates.map(item => item.event_name),
                datasets: [{
                            label: 'Attendance Rate (%)',
                            data: data.attendanceRates.map(item => item.rate),
                            backgroundColor: '#3B82F6',
                            borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Attendance: ${context.raw}%`;
                                    }
                                }
                    }
                },
                scales: {
                    y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Attendance Rate (%)'
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });

                // Event Popularity Chart
                new Chart(document.getElementById('eventPopularityChart'), {
                    type: 'radar',
                    data: {
                        labels: data.eventPopularity.map(item => item.event_name),
                        datasets: [{
                            label: 'Registrations',
                            data: data.eventPopularity.map(item => item.registration_count),
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: '#3B82F6',
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#3B82F6'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 5
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Registrations: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error loading chart data:', error));
        });
    </script>
</body>
</html>