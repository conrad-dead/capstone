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
    <title>Inventory Management - RHU GAMU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        .animate-pulse-slow { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        
        /* Card hover effects */
        .stat-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Gradient backgrounds */
        .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        
        /* Status indicators */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Custom focus styles */
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
        
        /* Table styles */
        .table-row-hover:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        /* Pagination styles */
        .pagination-button { 
            @apply px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200; 
        }
        .active-page { 
            @apply bg-blue-600 text-white border-blue-600 hover:bg-blue-700; 
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-pills text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">RHU GAMU</h1>
                        <p class="text-xs text-blue-200">Pharmacy System</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-blue-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <p class="text-xs text-blue-200">Pharmacist</p>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="./pharmacists_dashboard.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="text-sm font-medium">Dashboard</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg text-white shadow-lg">
                        <i class="fas fa-boxes w-5"></i>
                        <span class="text-sm font-medium">Inventory</span>
                    </a>
                    <a href="./reports.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="text-sm font-medium">Reports</span>
                    </a>
                    <a href="./notifications.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200 relative">
                        <i class="fas fa-bell w-5"></i>
                        <span class="text-sm font-medium">Notifications</span>
                        <span id="notificationBadge" class="notification-badge">0</span>
                    </a>
                </nav>
            </div>
            
            <!-- Logout -->
            <div class="absolute bottom-0 w-64 p-6">
                <a href="../logout.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="text-sm font-medium">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
                        <p class="text-gray-600">Add, edit, and manage your medicine inventory</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                        <button id="addDrugBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Add Medicine</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Items</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalItems">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-pills text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Low Stock</p>
                                <p class="text-2xl font-bold text-orange-600" id="lowStockCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-warning rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Expiring Soon</p>
                                <p class="text-2xl font-bold text-red-600" id="expiringCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-danger rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Categories</p>
                                <p class="text-2xl font-bold text-gray-900" id="categoryCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-info rounded-lg flex items-center justify-center">
                                <i class="fas fa-tags text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <div class="relative">
                                <input type="text" id="searchDrugs" placeholder="Search medicines..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <select id="filterCategory" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">All Categories</option>
                        </select>
                        <select id="filterStatus" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button onclick="clearFilters()" class="px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Medicine Inventory</h3>
                        <p class="text-sm text-gray-600 mt-1">Manage your medicine stock and track inventory levels</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="drugTableBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading medicines...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="drugPagination" class="flex justify-center items-center space-x-2 p-6 border-t border-gray-200"></div>
                </div>

                <!-- Status Legend -->
                <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h4 class="text-sm font-medium text-gray-700 mb-4">Status Indicators</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">Out of Stock</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">Low Stock (≤20)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">Expiring Soon (≤30 days)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">Good Stock</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Medicine Modal -->
    <div id="drugModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl animate-fade-in-up">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 id="drugModalTitle" class="text-xl font-semibold text-gray-800">Add New Medicine</h3>
                    <button id="closeDrugModal" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="drugForm" class="p-6 space-y-6">
                    <input type="hidden" id="drugId" name="id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="drugName" class="block text-sm font-medium text-gray-700 mb-2">Medicine Name</label>
                            <input type="text" id="drugName" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="e.g., Paracetamol 500mg">
                        </div>
                        <div>
                            <label for="drugCategory" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="drugCategory" name="category_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="drugQuantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" id="drugQuantity" name="quantity" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="e.g., 100">
                        </div>
                        <div>
                            <label for="drugExpiryDate" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                            <input type="date" id="drugExpiryDate" name="expiry_date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" id="cancelDrugEditButton" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 hidden">
                            <i class="fas fa-times mr-2"></i>
                            Cancel Edit
                        </button>
                        <button type="submit" id="drugSubmitButton" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Add Medicine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <script src="../js/pharmacists_dashboard_clean.js"></script>
</body>
</html>


