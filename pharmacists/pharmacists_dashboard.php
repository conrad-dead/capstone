<?php
    session_start();
    $roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
    
    // Debug session info
    error_log("Session debug - user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log("Session debug - username: " . ($_SESSION['username'] ?? 'NOT SET'));
    error_log("Session debug - role_name: " . ($_SESSION['user_role_name'] ?? 'NOT SET'));
    error_log("Session debug - role_id: " . ($_SESSION['user_role_id'] ?? 'NOT SET'));
    
    // Allow Pharmacy, Pharmacist, Pharmacists
    if (!isset($_SESSION['user_id']) || !in_array($roleName, ['pharmacy','pharmacist','pharmacists'], true)) {
        error_log("Access denied - user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . ", role_name: " . $roleName);
        header('Location: ../login.php');
        exit();
    }
    
    error_log("Access granted for user: " . $_SESSION['username'] . " with role: " . $roleName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        input:focus, select:focus { 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); 
            border-color: #4299e1; 
        }
        .pagination-button { 
            @apply px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200; 
        }
        .active-page { 
            @apply bg-blue-600 text-white border-blue-600 hover:bg-blue-700; 
        }
        
        /* Professional animations and effects */
        .dashboard-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Alert animations */
        .alert-enter {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Table row hover effects */
        .table-row-hover {
            transition: all 0.2s ease;
        }
        .table-row-hover:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }
        
        /* Professional button effects */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Professional shadows */
        .shadow-professional {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Gradient backgrounds */
        .bg-gradient-blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-active { background-color: #10b981; }
        .status-warning { background-color: #f59e0b; }
        .status-critical { background-color: #ef4444; }
    </style>
    </head>
<body>
    <div class="min-h-screen flex bg-gray-100">
        <!-- Sidebar -->
        <div class="flex flex-col h-screen bg-gradient-to-b from-blue-800 to-blue-900 text-white w-64 text-lg shadow-xl">
            <div class="mb-8 py-6">
                <div class="px-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">RHU GAMU</h1>
                            <p class="text-xs text-blue-200">Pharmacy Management</p>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto">
                <div class="px-2 py-4 space-y-1">
                    <div class="flex items-center space-x-3 py-3 px-4 rounded-lg bg-blue-700 text-white shadow-lg">
                        <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm font-medium">Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <div class="flex items-center space-x-3 py-3 px-4 rounded-lg bg-blue-700 text-white shadow-lg">
                        <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Pharmacist Dashboard</span>
                    </div>
                    <a href="./manage_inventory.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg text-blue-100 hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="text-sm font-medium">Manage Inventory</span>
                    </a>
                    <a href="./reports.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg text-blue-100 hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Reports</span>
                    </a>
                    <a href="./notifications.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg text-blue-100 hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5L9 15m0 0V9m0 6H3"></path>
                        </svg>
                        <span class="text-sm font-medium">Notifications</span>
                        <span id="notificationCounter" class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">0</span>
                    </a>
                </div>
            </nav>
            <div class="px-2 border-t border-blue-700">
                <a href="../logout.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg transition duration-200 hover:bg-blue-700 text-blue-100 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="text-sm font-medium">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-6 mb-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Drug Inventory Management</h1>
                        <p class="text-gray-600 mt-1">Monitor stock levels, manage inventory, and track expiry dates</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshAllData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="p-6 space-y-10">
                <!-- Dashboard Overview -->
                <div id="dashboardStats" class="bg-white rounded-lg shadow-sm p-6">
                    <!-- Dashboard stats will be populated by JavaScript -->
                </div>



                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="flex border-b border-gray-200">
                        <button id="tabReportsBtn" class="tab-button px-6 py-3 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">Reports</button>
                        <button id="tabManagementBtn" class="tab-button px-6 py-3 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">Management</button>
                    </div>
                </div>

                <!-- Reports -->
                <section id="tabReports" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Reports</h2>
                    <div class="text-center text-gray-500 py-8">
                        <p>Reports functionality has been removed.</p>
                    </div>
                </section>



                <!-- Management Tab -->
                <section id="tabManagement" class="bg-white rounded-lg shadow-xl p-8 tab-content">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Drug Inventory Management</h2>
                    
                    <!-- Manage Drugs Form -->
                    <div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-100">
                        <h3 id="drugFormTitle" class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add/Edit Drug
                        </h3>
                        <form id="drugForm" class="space-y-4">
                            <input type="hidden" id="drugId" name="id">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="drugName" class="block text-sm font-medium text-gray-700 mb-1">Drug Name</label>
                                    <input type="text" id="drugName" name="name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200" placeholder="e.g., Paracetamol 500mg">
                                </div>
                                <div>
                                    <label for="drugCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select id="drugCategory" name="category_id" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200">
                                        <option value="">Select a category</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="drugQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" id="drugQuantity" name="quantity" required min="0" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200" placeholder="e.g., 100">
                                </div>
                                <div>
                                    <label for="drugExpiryDate" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                    <input type="date" id="drugExpiryDate" name="expiry_date" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200">
                                </div>
                            </div>
                            <div class="flex space-x-4 pt-2">
                                <button type="submit" id="drugSubmitButton" class="flex-1 justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Drug
                                </button>
                                <button type="button" id="cancelDrugEditButton" class="hidden flex-1 justify-center py-3 px-6 border border-gray-300 rounded-lg shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel Edit
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Drug Inventory Table -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">Current Drug Inventory</h3>
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <input type="text" id="searchDrugs" placeholder="Search drugs..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <select id="filterCategory" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">All Categories</option>
                                </select>
                                <button onclick="clearFilters()" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="searchResultsCount" class="hidden text-sm text-gray-600 mb-3 px-2"></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="drugTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="drugPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                        
                        <!-- Professional Table Legend -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Status Indicators</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-100 rounded-full mr-2"></div>
                                    <span class="text-gray-600">Out of Stock</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-100 rounded-full mr-2"></div>
                                    <span class="text-gray-600">Low Stock (≤20)</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-50 rounded-full mr-2"></div>
                                    <span class="text-gray-600">Expiring Soon (≤30 days)</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-100 rounded-full mr-2"></div>
                                    <span class="text-gray-600">Expired</span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Professional Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium">RHU GAMU Pharmacy Management System</p>
                        <p class="text-xs text-gray-400">Professional drug inventory management</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">© 2024 RHU GAMU. All rights reserved.</p>
                    <p class="text-xs text-gray-400">Version 2.0 - Enhanced Professional Dashboard</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Keyboard Shortcuts Help -->
    <div class="fixed bottom-4 right-4">
        <button onclick="showKeyboardShortcuts()" class="bg-gray-800 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 transition-colors duration-200" data-tooltip="Keyboard Shortcuts">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/pharmacists_dashboard.js"></script>
</body>
</html>
