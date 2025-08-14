<?php
// session_start();
$is_logged_in = isset($_SESSION['user_id']);
$user_role_id = $_SESSION['user_role_id'];
?>

<?php if ($is_logged_in):?>
<!-- Sidebar -->
<nav class="flex-1 overflow-y-auto">
    <!-- Top Navigation Links -->
    <div class="px-2 py-4 space-y-1">

        <a href="admin_dashboard.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 'bg-gray-900 text-white' : 'text-gray-300'; ?>">
            <span>Welcome <?php echo htmlspecialchars($_SESSION['username']);?></span>
        </a>

        <a href="admin_dashboard.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo $current_page == 'admin_dashboard.php' ? 'bg-gray-900 text-white' : 'text-gray-300'; ?>">
            <!-- Disease Overview -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            </path>
            </svg>
            <span>Disease Overview</span>
        </a>

        <a href="#" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <!-- Location Analysis -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            </path>
            </svg>
            <span>Location Analysis</span>
        </a>

        <?php if ($user_role_id == 1 || $user_role_id == 2): // admin and pharmacists can access this one?>
        <a href="drug_inventory.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo $current_page == 'drug_inventory.php' ? 'bg-gray-900 text-white' : 'text-gray-300'; ?>">
            <!-- Drug Inventory Icon (Pill Bottle) -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span>Drug Inventory</span>
        </a>
        <?php endif; ?>

        <a href="#" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <!-- Reports -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
            </svg>
            <span>Reports</span>
        </a>

        
        <?php if ($user_role_id == 1): //only admin can go in user management?>
        <a href="user_management.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo $current_page == 'user_management.php' ? 'bg-gray-900 text-white' : 'text-gray-300'; ?>">
            <!-- User Management -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
            </path>
            </svg>
            <span>User Management</span>
        </a>
        <?php endif; ?>
    <!-- 
        <a href="#" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
            </svg>
            <span>Schedule</span>
        </a> -->
    </div>
</nav>

            <!-- Bottom Link (Settings) -->
<div class="px-2 border-t border-gray-700">

    <a href="../logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>Logout</span>
    </a>

    <a href="#" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>Settings</span>
    </a>
</div>

<?php endif;?>