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
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Scan QR Code
                        </button>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Manual Check-in
                        </button>
                    </div>
                </div>

                <!-- Active Events for Check-in -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">Active Events</h2>
                        <!-- Active events list -->
                    </div>
                </div>

                <!-- Recent Check-ins -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">Recent Check-ins</h2>
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-4">Participant</th>
                                    <th class="pb-4">Event</th>
                                    <th class="pb-4">Check-in Time</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <!-- Check-in rows will be dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 