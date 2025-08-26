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
    <title>Reports & Analytics - RHU GAMU</title>
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
        
        /* Chart container styles */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Export button styles */
        .export-btn {
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
                    <a href="#" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg text-white shadow-lg">
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
                        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                        <p class="text-gray-600">Comprehensive insights into your pharmacy inventory</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshReports()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                        <button onclick="exportReport('pdf')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-file-pdf"></i>
                            <span>PDF</span>
                        </button>
                        <button onclick="exportReport('excel')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-file-excel"></i>
                            <span>Excel</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Report Type Selector -->
            <div class="bg-white shadow-sm border-b border-gray-200 p-4">
                <div class="flex items-center space-x-6">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="report_type" value="inventory" checked class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Inventory Report</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="report_type" value="distribution" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Distribution Report</span>
                    </label>
                </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                                <p class="text-sm font-medium text-gray-600">Total Categories</p>
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

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                                <p class="text-2xl font-bold text-orange-600" id="lowStockItems">-</p>
                                <p class="text-xs text-orange-600 mt-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Need attention
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
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Category Distribution Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Category Distribution</h3>
                            <div class="flex space-x-2">
                                <button onclick="toggleChartView('category')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-chart-pie mr-1"></i>Pie
                                </button>
                                <button onclick="toggleChartView('category')" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                    <i class="fas fa-chart-bar mr-1"></i>Bar
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>

                    <!-- Stock Levels Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Stock Levels</h3>
                            <div class="flex space-x-2">
                                <button onclick="toggleChartView('stock')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-chart-bar mr-1"></i>Bar
                                </button>
                                <button onclick="toggleChartView('stock')" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                    <i class="fas fa-chart-line mr-1"></i>Line
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Top Categories Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Top Categories</h3>
                            <p class="text-sm text-gray-600 mt-1">Categories with highest medicine count</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody id="topCategoriesTable" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                            <p class="text-sm text-gray-600 mt-1">Latest inventory updates</p>
                        </div>
                        <div class="p-6">
                            <div id="recentActivity" class="space-y-4">
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-pills text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Loading activity...</p>
                                        <p class="text-xs text-gray-500">Please wait</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Reports</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button onclick="exportToPDF()" class="flex items-center justify-center space-x-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-file-pdf text-red-600 text-xl"></i>
                            <span class="font-medium">Export to PDF</span>
                        </button>
                        <button onclick="exportToExcel()" class="flex items-center justify-center space-x-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-file-excel text-green-600 text-xl"></i>
                            <span class="font-medium">Export to Excel</span>
                        </button>
                        <button onclick="exportToCSV()" class="flex items-center justify-center space-x-2 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-file-csv text-blue-600 text-xl"></i>
                            <span class="font-medium">Export to CSV</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading reports...</span>
        </div>
    </div>

    <script>
        // Global variables
        let dashboardData = {
            medicines: [],
            categories: [],
            stats: {}
        };
        
        let charts = {
            categoryChart: null,
            stockChart: null
        };

        // API URLs
        const API_BASE = '../api/drug_api.php';
        const CATEGORIES_URL = `${API_BASE}?resource=categories`;
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
        async function fetchCategories() {
            try {
                const response = await fetch(CATEGORIES_URL);
                const result = await response.json();
                if (result.success) {
                    dashboardData.categories = result.data;
                    return result.data;
                } else {
                    console.error('Failed to fetch categories:', result.message);
                    return [];
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
                return [];
            }
        }

        async function fetchDrugs() {
            try {
                const response = await fetch(`${DRUGS_URL}&page=1&limit=1000`);
                const result = await response.json();
                if (result.success) {
                    dashboardData.medicines = result.data;
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

        // Statistics calculation
        function calculateStats(medicines) {
            const now = new Date();
            const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
            
            const stats = {
                totalMedicines: medicines.length,
                totalCategories: dashboardData.categories.length,
                lowStockItems: medicines.filter(m => m.quantity <= 20).length,
                expiringSoon: medicines.filter(m => {
                    if (!m.expiry_date) return false;
                    const expiryDate = new Date(m.expiry_date);
                    return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
                }).length
            };
            
            dashboardData.stats = stats;
            return stats;
        }

        function updateStatistics(stats) {
            document.getElementById('totalMedicines').textContent = formatNumber(stats.totalMedicines);
            document.getElementById('totalCategories').textContent = formatNumber(stats.totalCategories);
            document.getElementById('lowStockItems').textContent = formatNumber(stats.lowStockItems);
            document.getElementById('expiringSoon').textContent = formatNumber(stats.expiringSoon);
            document.getElementById('lastUpdated').textContent = 'Just now';
        }

        // Chart functions
        function createCategoryChart(medicines) {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;

            // Group medicines by category
            const categoryData = {};
            medicines.forEach(medicine => {
                const categoryName = medicine.category_name || 'Uncategorized';
                if (!categoryData[categoryName]) {
                    categoryData[categoryName] = 0;
                }
                categoryData[categoryName]++;
            });

            const labels = Object.keys(categoryData);
            const data = Object.values(categoryData);

            if (charts.categoryChart) {
                charts.categoryChart.destroy();
            }

            charts.categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }

        function createStockChart(medicines) {
            const ctx = document.getElementById('stockChart');
            if (!ctx) return;

            // Group medicines by stock level
            const stockLevels = {
                'Out of Stock': medicines.filter(m => m.quantity === 0).length,
                'Low Stock (1-20)': medicines.filter(m => m.quantity > 0 && m.quantity <= 20).length,
                'Medium Stock (21-100)': medicines.filter(m => m.quantity > 20 && m.quantity <= 100).length,
                'High Stock (100+)': medicines.filter(m => m.quantity > 100).length
            };

            const labels = Object.keys(stockLevels);
            const data = Object.values(stockLevels);

            if (charts.stockChart) {
                charts.stockChart.destroy();
            }

            charts.stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Medicines',
                        data: data,
                        backgroundColor: ['#EF4444', '#F59E0B', '#3B82F6', '#10B981'],
                        borderColor: ['#DC2626', '#D97706', '#2563EB', '#059669'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Table population
        function updateTopCategoriesTable(medicines) {
            const tableBody = document.getElementById('topCategoriesTable');
            if (!tableBody) return;

            // Group medicines by category
            const categoryData = {};
            medicines.forEach(medicine => {
                const categoryName = medicine.category_name || 'Uncategorized';
                if (!categoryData[categoryName]) {
                    categoryData[categoryName] = 0;
                }
                categoryData[categoryName]++;
            });

            // Sort by count and get top 5
            const sortedCategories = Object.entries(categoryData)
                .sort(([,a], [,b]) => b - a)
                .slice(0, 5);

            const totalMedicines = medicines.length;

            tableBody.innerHTML = sortedCategories.map(([category, count]) => {
                const percentage = ((count / totalMedicines) * 100).toFixed(1);
                return `
                    <tr class="table-row-hover">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${count}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${percentage}%</td>
                    </tr>
                `;
            }).join('');
        }

        function updateRecentActivity(medicines) {
            const activityContainer = document.getElementById('recentActivity');
            if (!activityContainer) return;

            // Sort medicines by ID (assuming newer items have higher IDs)
            const recentMedicines = medicines
                .sort((a, b) => b.id - a.id)
                .slice(0, 5);

            activityContainer.innerHTML = recentMedicines.map(medicine => `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-pills text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${medicine.name}</p>
                        <p class="text-xs text-gray-500">${medicine.category_name || 'Uncategorized'} • Qty: ${medicine.quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">ID: ${medicine.id}</p>
                        <p class="text-xs text-gray-400">${medicine.expiry_date || 'N/A'}</p>
                    </div>
                </div>
            `).join('');
        }

        // Export functions
        window.exportReport = function(format) {
            const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'inventory';
            
            if (format === 'pdf') {
                exportToPDF(reportType);
            } else if (format === 'excel') {
                exportToExcel(reportType);
            } else {
                showNotification('Invalid export format', 'error');
            }
        };

        window.exportToPDF = function(reportType) {
            const url = `../api/export_api.php?format=pdf&type=${reportType}`;
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.download = `${reportType}_report_${new Date().toISOString().split('T')[0]}.html`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('PDF report generated! Use browser print to save as PDF.', 'success');
        };

        window.exportToExcel = function(reportType) {
            const url = `../api/export_api.php?format=excel&type=${reportType}`;
            const link = document.createElement('a');
            link.href = url;
            link.download = `${reportType}_report_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('Excel report downloaded successfully!', 'success');
        };

        window.refreshReports = async function() {
            showLoading();
            try {
                await loadReportsData();
                showNotification('Reports refreshed successfully!', 'success');
            } catch (error) {
                console.error('Error refreshing reports:', error);
                showNotification('Failed to refresh reports', 'error');
            } finally {
                hideLoading();
            }
        };

        window.toggleChartView = function(chartType) {
            showNotification('Chart view toggle coming soon!', 'info');
        };

        // Main data loading
        async function loadReportsData() {
            try {
                // Fetch data in parallel
                const [categories, medicines] = await Promise.all([
                    fetchCategories(),
                    fetchDrugs()
                ]);

                // Calculate statistics
                const stats = calculateStats(medicines);

                // Update UI
                updateStatistics(stats);
                createCategoryChart(medicines);
                createStockChart(medicines);
                updateTopCategoriesTable(medicines);
                updateRecentActivity(medicines);

            } catch (error) {
                console.error('Error loading reports data:', error);
                showNotification('Failed to load reports data', 'error');
            }
        }

        // Initialize
        async function initReports() {
            showLoading();
            try {
                await loadReportsData();
            } catch (error) {
                console.error('Failed to initialize reports:', error);
                showNotification('Failed to initialize reports', 'error');
            } finally {
                hideLoading();
            }
        }

        // Start the reports
        document.addEventListener('DOMContentLoaded', initReports);
    </script>
</body>
</html>


