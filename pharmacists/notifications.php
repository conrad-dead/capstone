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
    <title>Notifications - RHU GAMU</title>
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
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        .animate-pulse-slow { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        
        /* Card hover effects */
        .notification-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
        
        /* Filter button styles */
        .filter-btn {
            transition: all 0.2s ease;
        }
        .filter-btn.active {
            background: #3b82f6;
            color: white;
        }
        .filter-btn:hover {
            transform: translateY(-1px);
        }
        
        /* Notification priority styles */
        .priority-high { border-left: 4px solid #ef4444; }
        .priority-medium { border-left: 4px solid #f59e0b; }
        .priority-low { border-left: 4px solid #10b981; }
        
        /* Empty state */
        .empty-state {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
                    <a href="./manage_inventory.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-boxes w-5"></i>
                        <span class="text-sm font-medium">Inventory</span>
                    </a>
                    <a href="./distribute.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-hand-holding-medical w-5"></i>
                        <span class="text-sm font-medium">Distribution</span>
                    </a>
                    <a href="./reports.php" class="flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="text-sm font-medium">Reports</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg text-white shadow-lg relative">
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
                        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                        <p class="text-gray-600">Stay updated with important alerts and updates</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshNotifications()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                        <button onclick="markAllAsRead()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-check-double"></i>
                            <span>Mark All Read</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Notification Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Alerts</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalAlerts">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-bell text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">High Priority</p>
                                <p class="text-2xl font-bold text-red-600" id="highPriorityCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-danger rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Medium Priority</p>
                                <p class="text-2xl font-bold text-orange-600" id="mediumPriorityCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-warning rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Unread</p>
                                <p class="text-2xl font-bold text-blue-600" id="unreadCount">-</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-info rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-64">
                            <div class="relative">
                                <input type="text" id="searchNotifications" placeholder="Search notifications..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="filterNotifications('all')" class="filter-btn active px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-all duration-200">
                                All
                            </button>
                            <button onclick="filterNotifications('high')" class="filter-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-all duration-200">
                                High Priority
                            </button>
                            <button onclick="filterNotifications('medium')" class="filter-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-all duration-200">
                                Medium Priority
                            </button>
                            <button onclick="filterNotifications('low')" class="filter-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-all duration-200">
                                Low Priority
                            </button>
                        </div>
                        <button onclick="clearFilters()" class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Notifications</h3>
                        <p class="text-sm text-gray-600 mt-1">Important alerts and updates for your pharmacy</p>
                    </div>
                    
                    <div id="notificationsContainer" class="p-6">
                        <!-- Loading state -->
                        <div id="loadingState" class="space-y-4">
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg animate-pulse">
                                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg animate-pulse">
                                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                    <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div id="emptyState" class="hidden text-center py-12 empty-state rounded-lg">
                            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-bell text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                            <p class="text-gray-600">You're all caught up! No new notifications at the moment.</p>
                        </div>

                        <!-- Notifications list will be populated here -->
                        <div id="notificationsList" class="space-y-4 hidden"></div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">Low Stock Alerts</p>
                                    <p class="text-sm text-gray-600">Get notified when medicines are running low</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">Expiry Alerts</p>
                                    <p class="text-sm text-gray-600">Get notified about expiring medicines</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">System Updates</p>
                                    <p class="text-sm text-gray-600">Receive notifications about system maintenance</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">Email Notifications</p>
                                    <p class="text-sm text-gray-600">Send important alerts to your email</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading notifications...</span>
        </div>
    </div>

    <script>
        // Global variables
        let notificationsData = {
            notifications: [],
            stats: {}
        };
        
        let currentFilter = 'all';

        // API URLs
        const API_BASE = '../api/drug_api.php';
        const DRUGS_URL = `${API_BASE}?resource=drugs`;

        // Utility functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            return `${Math.floor(diffInSeconds / 86400)}d ago`;
        }

        function showNotification(message, type = 'info') {
            if (window.Swal) {
                Swal.fire({
                    icon: type,
                    title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                console.log(`${type}: ${message}`);
            }
        }

        // API Functions
        async function fetchDrugs() {
            try {
                const response = await fetch(`${DRUGS_URL}&page=1&limit=1000`);
                const result = await response.json();
                if (result.success) {
                    return result.data;
                } else {
                    console.error('Failed to fetch drugs:', result.message);
                    return [];
                }
            } catch (error) {
                console.error('Error fetching drugs:', error);
                return [];
            }
        }

        // Generate notifications from medicine data
        function generateNotifications(medicines) {
            const notifications = [];
            const now = new Date();

            // Low stock notifications
            const lowStockMedicines = medicines.filter(m => m.quantity <= 20);
            lowStockMedicines.forEach(medicine => {
                notifications.push({
                    id: `low_stock_${medicine.id}`,
                    type: 'low_stock',
                    priority: medicine.quantity === 0 ? 'high' : 'medium',
                    title: medicine.quantity === 0 ? 'Out of Stock Alert' : 'Low Stock Alert',
                    message: `${medicine.name} is ${medicine.quantity === 0 ? 'out of stock' : `running low (${medicine.quantity} units remaining)`}`,
                    category: medicine.category_name || 'Uncategorized',
                    timestamp: new Date().toISOString(),
                    read: false,
                    action: 'restock',
                    medicineId: medicine.id
                });
            });

            // Expiry notifications
            const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expiringMedicines = medicines.filter(m => {
                if (!m.expiry_date) return false;
                const expiryDate = new Date(m.expiry_date);
                return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
            });

            expiringMedicines.forEach(medicine => {
                const expiryDate = new Date(medicine.expiry_date);
                const daysUntilExpiry = Math.ceil((expiryDate - now) / (1000 * 60 * 60 * 24));
                
                notifications.push({
                    id: `expiry_${medicine.id}`,
                    type: 'expiry',
                    priority: daysUntilExpiry <= 7 ? 'high' : daysUntilExpiry <= 14 ? 'medium' : 'low',
                    title: 'Expiry Alert',
                    message: `${medicine.name} expires in ${daysUntilExpiry} day${daysUntilExpiry !== 1 ? 's' : ''}`,
                    category: medicine.category_name || 'Uncategorized',
                    timestamp: new Date().toISOString(),
                    read: false,
                    action: 'review',
                    medicineId: medicine.id,
                    expiryDate: medicine.expiry_date
                });
            });

            // Sort by priority and timestamp
            notifications.sort((a, b) => {
                const priorityOrder = { high: 3, medium: 2, low: 1 };
                const priorityDiff = priorityOrder[b.priority] - priorityOrder[a.priority];
                if (priorityDiff !== 0) return priorityDiff;
                return new Date(b.timestamp) - new Date(a.timestamp);
            });

            return notifications;
        }

        // Calculate statistics
        function calculateStats(notifications) {
            const stats = {
                totalAlerts: notifications.length,
                highPriorityCount: notifications.filter(n => n.priority === 'high').length,
                mediumPriorityCount: notifications.filter(n => n.priority === 'medium').length,
                unreadCount: notifications.filter(n => !n.read).length
            };
            
            notificationsData.stats = stats;
            return stats;
        }

        function updateStatistics(stats) {
            document.getElementById('totalAlerts').textContent = formatNumber(stats.totalAlerts);
            document.getElementById('highPriorityCount').textContent = formatNumber(stats.highPriorityCount);
            document.getElementById('mediumPriorityCount').textContent = formatNumber(stats.mediumPriorityCount);
            document.getElementById('unreadCount').textContent = formatNumber(stats.unreadCount);
            document.getElementById('lastUpdated').textContent = 'Just now';

            // Update notification badge
            const notificationBadge = document.getElementById('notificationBadge');
            if (notificationBadge) {
                notificationBadge.textContent = stats.unreadCount;
                notificationBadge.style.display = stats.unreadCount > 0 ? 'flex' : 'none';
            }
        }

        function renderNotifications(notifications) {
            const container = document.getElementById('notificationsContainer');
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const notificationsList = document.getElementById('notificationsList');

            // Hide loading state
            loadingState.classList.add('hidden');

            if (!notifications || notifications.length === 0) {
                emptyState.classList.remove('hidden');
                notificationsList.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            notificationsList.classList.remove('hidden');

            notificationsList.innerHTML = notifications.map(notification => `
                <div class="notification-card bg-white border border-gray-200 rounded-lg p-4 priority-${notification.priority} animate-slide-in" data-notification-id="${notification.id}">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center ${
                                notification.priority === 'high' ? 'bg-red-100' :
                                notification.priority === 'medium' ? 'bg-orange-100' : 'bg-blue-100'
                            }">
                                <i class="fas ${
                                    notification.type === 'low_stock' ? 'fa-exclamation-triangle' :
                                    notification.type === 'expiry' ? 'fa-clock' : 'fa-info-circle'
                                } text-${
                                    notification.priority === 'high' ? 'red' :
                                    notification.priority === 'medium' ? 'orange' : 'blue'
                                }-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                        notification.priority === 'high' ? 'bg-red-100 text-red-800' :
                                        notification.priority === 'medium' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'
                                    }">
                                        ${notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)}
                                    </span>
                                    ${!notification.read ? '<span class="w-2 h-2 bg-blue-600 rounded-full"></span>' : ''}
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span><i class="fas fa-tag mr-1"></i>${notification.category}</span>
                                    <span><i class="fas fa-clock mr-1"></i>${getTimeAgo(notification.timestamp)}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button onclick="markAsRead('${notification.id}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-check mr-1"></i>Mark Read
                                    </button>
                                    <button onclick="handleNotificationAction('${notification.id}')" class="text-green-600 hover:text-green-800 text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-eye mr-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Filter functions
        window.filterNotifications = function(filter) {
            currentFilter = filter;
            
            // Update filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter notifications
            let filteredNotifications = notificationsData.notifications;
            if (filter !== 'all') {
                filteredNotifications = notificationsData.notifications.filter(n => n.priority === filter);
            }

            renderNotifications(filteredNotifications);
        };

        window.clearFilters = function() {
            currentFilter = 'all';
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('.filter-btn').classList.add('active');
            renderNotifications(notificationsData.notifications);
        };

        // Action functions
        window.markAsRead = function(notificationId) {
            const notification = notificationsData.notifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                const stats = calculateStats(notificationsData.notifications);
                updateStatistics(stats);
                renderNotifications(notificationsData.notifications);
                showNotification('Notification marked as read', 'success');
            }
        };

        window.markAllAsRead = function() {
            notificationsData.notifications.forEach(n => n.read = true);
            const stats = calculateStats(notificationsData.notifications);
            updateStatistics(stats);
            renderNotifications(notificationsData.notifications);
            showNotification('All notifications marked as read', 'success');
        };

        window.handleNotificationAction = function(notificationId) {
            const notification = notificationsData.notifications.find(n => n.id === notificationId);
            if (notification) {
                if (notification.action === 'restock') {
                    window.location.href = './manage_inventory.php';
                } else if (notification.action === 'review') {
                    showNotification(`Reviewing ${notification.title}`, 'info');
                }
            }
        };

        window.refreshNotifications = async function() {
            showLoading();
            try {
                await loadNotificationsData();
                showNotification('Notifications refreshed successfully!', 'success');
            } catch (error) {
                console.error('Error refreshing notifications:', error);
                showNotification('Failed to refresh notifications', 'error');
            } finally {
                hideLoading();
            }
        };

        // Search functionality
        document.getElementById('searchNotifications').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredNotifications = notificationsData.notifications.filter(notification => 
                notification.title.toLowerCase().includes(searchTerm) ||
                notification.message.toLowerCase().includes(searchTerm) ||
                notification.category.toLowerCase().includes(searchTerm)
            );
            renderNotifications(filteredNotifications);
        });

        // Main data loading
        async function loadNotificationsData() {
            try {
                const medicines = await fetchDrugs();
                const notifications = generateNotifications(medicines);
                notificationsData.notifications = notifications;
                
                const stats = calculateStats(notifications);
                updateStatistics(stats);
                renderNotifications(notifications);

            } catch (error) {
                console.error('Error loading notifications data:', error);
                showNotification('Failed to load notifications data', 'error');
            }
        }

        // Initialize
        async function initNotifications() {
            showLoading();
            try {
                await loadNotificationsData();
            } catch (error) {
                console.error('Failed to initialize notifications:', error);
                showNotification('Failed to initialize notifications', 'error');
            } finally {
                hideLoading();
            }
        }

        // Start the notifications
        document.addEventListener('DOMContentLoaded', initNotifications);
    </script>
</body>
</html>
