<!-- Sidebar -->
<div class="w-72 bg-white shadow-lg h-screen fixed left-0">
    <div class="p-6">
        <!-- Logo and Brand -->
        <div class="flex items-center mb-8">
            <div class="bg-blue-600 p-2 rounded-xl">
                <img src="../../assets/logo.png" alt="Logo" class="h-8 w-8">
            </div>
            <span class="ml-3 text-xl font-semibold text-gray-800">Event Registration</span>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="space-y-1">
            <a href="../admin/dashboard.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="../admin/events.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Events</span>
            </a>

            <a href="../admin/participants.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'participants.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Participants</span>
            </a>

            <div class="border-t border-gray-100 my-4"></div>

            <a href="../admin/communications.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'communications.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span>Communications</span>
            </a>

            <a href="../admin/attendance.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Attendance</span>
            </a>

            <a href="../admin/checkin.php" 
                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'checkin.php' ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : ''; ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M15 11v3m0 0v3m0-3h3m-3 0h-3"/>
                </svg>
                <span>Check-in</span>
            </a>
        </nav>

        <!-- User Profile Section -->
        <div class="absolute bottom-0 left-0 right-0 p-6">
            <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                <img src="https://ui-avatars.com/api/?name=Admin&background=random" class="w-10 h-10 rounded-full">
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800">Admin User</p>
                    <p class="text-xs text-gray-500">admin@example.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add margin to main content to account for fixed sidebar -->
<div class="ml-72"></div> 