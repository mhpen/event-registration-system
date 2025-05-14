<!-- Sidebar -->
<div class="w-64 border-r bg-background h-screen flex flex-col fixed left-0">
    <!-- Logo and Brand -->
    <div class="p-4 border-b">
        <div class="flex items-center gap-2">
            <div class="rounded-md bg-primary/10 p-2">
                <span class="text-lg font-semibold text-primary">E</span>
            </div>
            <span class="font-medium">Event System</span>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 p-2">
        <div class="space-y-1">
            <a href="../admin/dashboard.php" 
                class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'; ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard
            </a>

            <a href="../admin/events.php" 
                class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'; ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Events
            </a>

            <a href="../admin/participants.php" 
                class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground <?php echo basename($_SERVER['PHP_SELF']) == 'participants.php' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'; ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Participants
            </a>

            <div class="my-2 border-t"></div>

            <a href="../admin/attendance.php" 
                class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'; ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Attendance
            </a>

            <a href="../admin/checkin.php" 
                class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground <?php echo basename($_SERVER['PHP_SELF']) == 'checkin.php' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'; ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Check-in
            </a>
        </div>
    </nav>

    <!-- User Menu -->
    <div class="border-t p-4 relative">
        <!-- Profile Button -->
        <button data-dropdown-toggle 
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground text-muted-foreground w-full justify-between">
            <div class="flex items-center gap-3">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Admin
            </div>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div id="dropdownMenu" class="absolute bottom-full left-0 w-full mb-1 hidden z-50">
            <div class="mx-4 rounded-lg border bg-background shadow-md">
                <a href="../../controllers/admin/logout.php" 
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground text-muted-foreground">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Update the margin div -->
<div class="ml-64 bg-background"></div>

<script>
// Get the profile button and dropdown menu
const profileButton = document.querySelector('[data-dropdown-toggle]');
const dropdownMenu = document.getElementById('dropdownMenu');

// Toggle dropdown when clicking the profile button
profileButton.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdownMenu.classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', () => {
    dropdownMenu.classList.add('hidden');
});

// Prevent dropdown from closing when clicking inside it
dropdownMenu.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Close dropdown when pressing escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        dropdownMenu.classList.add('hidden');
    }
});
</script> 