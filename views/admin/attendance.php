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
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Export Report
                    </button>
                </div>

                <!-- Attendance Stats -->
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <!-- Attendance statistics cards -->
                </div>

                <!-- Attendance Records -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-4">Event</th>
                                    <th class="pb-4">Date</th>
                                    <th class="pb-4">Total Participants</th>
                                    <th class="pb-4">Attended</th>
                                    <th class="pb-4">Attendance Rate</th>
                                    <th class="pb-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <!-- Attendance rows will be dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 