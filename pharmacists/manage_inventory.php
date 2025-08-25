<?php
    session_start();
    $roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
    if (!isset($_SESSION['user_id']) || !in_array($roleName, ['pharmacy','pharmacist','pharmacists'], true)) {
        header('Location: ../login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(66,153,225,.5); 
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
                    <div class="flex items-center space-x-3 py-3 px-4 rounded-lg text-blue-100 hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm font-medium">Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <a href="./pharmacists_dashboard.php" class="flex items-center space-x-3 py-3 px-4 rounded-lg text-blue-100 hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Pharmacist Dashboard</span>
                    </a>
                    <div class="flex items-center space-x-3 py-3 px-4 rounded-lg bg-blue-700 text-white shadow-lg">
                        <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="text-sm font-medium">Manage Inventory</span>
                    </div>
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
            <!-- Header -->
            <header class="bg-white shadow-sm rounded-lg p-6 mb-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Drug Inventory Management</h1>
                        <p class="text-gray-600 mt-1">Add, edit, and manage drug inventory</p>
                    </div>
                    <button id="addDrugBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-plus"></i>
                        <span>Add Drug</span>
                    </button>
                </div>
            </header>

            <main class="space-y-8">
                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-wrap gap-4">
                        <input type="text" id="searchDrugs" placeholder="Search drugs..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 flex-1 min-w-64">
                        <select id="filterCategory" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                        </select>
                        <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Drug Table -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="overflow-x-auto">
                                                 <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                 <tr>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="drugTableBody" class="bg-white divide-y divide-gray-200">
                                 <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>
                             </tbody>
                         </table>
                    </div>
                    <div id="drugPagination" class="flex justify-center items-center space-x-2 p-6"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Drug Modal -->
    <div id="drugModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 id="drugModalTitle" class="text-xl font-semibold text-gray-800">Add New Drug</h3>
                    <button id="closeDrugModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                                 <form id="drugForm" class="p-6 space-y-4">
                     <input type="hidden" id="drugId" name="id">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div>
                             <label for="drugName" class="block text-sm font-medium text-gray-700 mb-1">Drug Name</label>
                             <input type="text" id="drugName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                         </div>
                         <div>
                             <label for="drugCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                             <select id="drugCategory" name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                 <option value="">Select Category</option>
                             </select>
                         </div>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div>
                             <label for="drugQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                             <input type="number" id="drugQuantity" name="quantity" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                         </div>
                         <div>
                             <label for="drugExpiryDate" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                             <input type="date" id="drugExpiryDate" name="expiry_date" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                         </div>
                     </div>
                     <div class="flex justify-end space-x-3 pt-4">
                         <button type="button" id="cancelDrugEditButton" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 hidden">Cancel Edit</button>
                         <button type="submit" id="drugSubmitButton" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Drug</button>
                     </div>
                 </form>
            </div>
        </div>
    </div>

    <script src="../js/pharmacists_dashboard_clean.js"></script>
</body>
</html>


