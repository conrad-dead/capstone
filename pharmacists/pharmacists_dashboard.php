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
    <title>Pharmacy Dashboard - RHU GAMU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="#" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg text-white shadow-lg">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="text-sm font-medium">Dashboard</span>
                    </a>
                    <a href="./manage_inventory.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
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
                        <h1 class="text-2xl font-bold text-gray-900">Pharmacy Dashboard</h1>
                        <p class="text-gray-600">Welcome back! Here's what's happening with your inventory.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshDashboard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Medicines</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalMedicines">-</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-arrow-up"></i>
                                    <span id="medicineGrowth">0%</span> from last month
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-pills text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                                <p class="text-2xl font-bold text-orange-600" id="lowStockItems">-</p>
                                <p class="text-xs text-orange-600 mt-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Needs attention
                                </p>
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
                                <p class="text-2xl font-bold text-red-600" id="expiringSoon">-</p>
                                <p class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-clock"></i>
                                    Within 30 days
                                </p>
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
                                <p class="text-2xl font-bold text-gray-900" id="totalCategories">-</p>
                                <p class="text-xs text-blue-600 mt-1">
                                    <i class="fas fa-tags"></i>
                                    Active categories
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-info rounded-lg flex items-center justify-center">
                                <i class="fas fa-tags text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Stock Level Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Levels by Category</h3>
                        <div class="h-64">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>

                    <!-- Expiry Timeline -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expiry Timeline</h3>
                        <div class="h-64">
                            <canvas id="expiryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                            <button onclick="viewAllActivity()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View All
                            </button>
                        </div>
                        <div id="recentActivity" class="space-y-4">
                            <!-- Activity items will be populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="addNewMedicine()" class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg transition-colors duration-200 flex items-center space-x-3">
                                <i class="fas fa-plus"></i>
                                <span>Add Medicine</span>
                            </button>
                            <button onclick="checkLowStock()" class="w-full bg-orange-600 hover:bg-orange-700 text-white p-3 rounded-lg transition-colors duration-200 flex items-center space-x-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Check Low Stock</span>
                            </button>
                            <button onclick="generateReport()" class="w-full bg-green-600 hover:bg-green-700 text-white p-3 rounded-lg transition-colors duration-200 flex items-center space-x-3">
                                <i class="fas fa-file-alt"></i>
                                <span>Generate Report</span>
                            </button>
                            <button onclick="viewNotifications()" class="w-full bg-purple-600 hover:bg-purple-700 text-white p-3 rounded-lg transition-colors duration-200 flex items-center space-x-3">
                                <i class="fas fa-bell"></i>
                                <span>View Notifications</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div id="alertsSection" class="mt-8 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Important Alerts</h3>
                    <div id="alertsContainer" class="space-y-3">
                        <!-- Alerts will be populated by JavaScript -->
                    </div>
                </div>
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

    <script src="../js/pharmacists_dashboard_enhanced.js"></script>
</body>
</html>
