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
    <title>Pharmacist - Notifications & Alerts</title>
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
        
        .alert-item {
            transition: all 0.3s ease;
        }
        .alert-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .alert-critical {
            border-left: 4px solid #ef4444;
            background-color: #fef2f2;
        }
        .alert-warning {
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
        }
        .alert-info {
            border-left: 4px solid #06b6d4;
            background-color: #ecfeff;
        }
        .alert-success {
            border-left: 4px solid #10b981;
            background-color: #f0fdf4;
        }
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
                    <div class="flex items-center space-x-3 py-3 px-4 rounded-lg bg-blue-700 text-white shadow-lg">
                        <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5L9 15m0 0V9m0 6H3"></path>
                        </svg>
                        <span class="text-sm font-medium">Notifications</span>
                        <span id="alertCounter" class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">0</span>
                    </div>
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
                        <h1 class="text-3xl font-bold text-gray-800">Notifications & Alerts Center</h1>
                        <p class="text-gray-600 mt-1">Monitor stock alerts, expiry warnings, and system notifications</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshAlerts()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Refresh</span>
                        </button>
                        <button onclick="markAllAsRead()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Mark All Read</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="space-y-6">
                <!-- Alert Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Critical Alerts</p>
                                <p class="text-2xl font-semibold text-gray-900" id="criticalCount">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Warnings</p>
                                <p class="text-2xl font-semibold text-gray-900" id="warningCount">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Info</p>
                                <p class="text-2xl font-semibold text-gray-900" id="infoCount">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total</p>
                                <p class="text-2xl font-semibold text-gray-900" id="totalCount">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
                        <div class="text-sm text-gray-500">
                            <span id="filteredCount">0</span> of <span id="totalAlerts">0</span> alerts
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <select id="alertTypeFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="expired">Expired</option>
                            <option value="expiring_soon">Expiring Soon</option>
                            <option value="system">System</option>
                        </select>
                        <select id="alertSeverityFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Severity</option>
                            <option value="critical">Critical</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                            Apply Filters
                        </button>
                        <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Alerts List -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div id="alertsList" class="space-y-4">
                        <!-- Alerts will be populated by JavaScript -->
                    </div>
                    
                    <!-- No Alerts Message -->
                    <div id="noAlertsMessage" class="hidden text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No alerts</h3>
                        <p class="mt-1 text-sm text-gray-500">Your inventory is in good shape. No alerts at this time.</p>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-500">Loading alerts...</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let allAlerts = [];
        let filteredAlerts = [];
        const LOW_STOCK_THRESHOLD = 20;
        const EXPIRY_WARNING_DAYS = 30;

        // Load alerts on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAlerts();
        });

        async function loadAlerts() {
            try {
                showLoading(true);
                
                // Fetch drugs data
                const response = await fetch('../api/drug_api.php?resource=drugs');
                const data = await response.json();
                
                if (data.success) {
                    const drugs = data.data || [];
                    allAlerts = generateAlerts(drugs);
                    filteredAlerts = [...allAlerts];
                    updateAlertCounts();
                    renderAlerts();
                } else {
                    console.error('Failed to load drugs:', data.message);
                    showNoAlerts();
                }
            } catch (error) {
                console.error('Error loading alerts:', error);
                showNoAlerts();
            } finally {
                showLoading(false);
            }
        }

        function generateAlerts(drugs) {
            const alerts = [];
            const today = new Date();
            
            drugs.forEach(drug => {
                // Low stock alert
                if (drug.quantity <= LOW_STOCK_THRESHOLD) {
                    alerts.push({
                        id: `low_stock_${drug.id}`,
                        type: 'low_stock',
                        severity: drug.quantity === 0 ? 'critical' : 'warning',
                        message: `${drug.name} is running low on stock (${drug.quantity} remaining)`,
                        drug: drug,
                        timestamp: new Date(),
                        read: false
                    });
                }
                
                // Expiry alert
                if (drug.expiry_date) {
                    const expiryDate = new Date(drug.expiry_date);
                    const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                    
                    if (daysUntilExpiry <= 0) {
                        alerts.push({
                            id: `expired_${drug.id}`,
                            type: 'expired',
                            severity: 'critical',
                            message: `${drug.name} has expired on ${drug.expiry_date}`,
                            drug: drug,
                            timestamp: new Date(),
                            read: false
                        });
                    } else if (daysUntilExpiry <= EXPIRY_WARNING_DAYS) {
                        alerts.push({
                            id: `expiring_${drug.id}`,
                            type: 'expiring_soon',
                            severity: 'warning',
                            message: `${drug.name} expires in ${daysUntilExpiry} days`,
                            drug: drug,
                            timestamp: new Date(),
                            read: false
                        });
                    }
                }
            });

            // Add some system notifications
            alerts.push({
                id: 'system_1',
                type: 'system',
                severity: 'info',
                message: 'Monthly inventory check is due. Please review all stock levels.',
                timestamp: new Date(),
                read: false
            });

            return alerts.sort((a, b) => {
                // Sort by severity (critical first, then warning, then info)
                const severityOrder = { critical: 3, warning: 2, info: 1 };
                return severityOrder[b.severity] - severityOrder[a.severity];
            });
        }

        function renderAlerts() {
            const container = document.getElementById('alertsList');
            const noAlertsDiv = document.getElementById('noAlertsMessage');
            
            if (filteredAlerts.length === 0) {
                container.innerHTML = '';
                noAlertsDiv.classList.remove('hidden');
                return;
            }

            noAlertsDiv.classList.add('hidden');
            
            container.innerHTML = filteredAlerts.map(alert => `
                <div class="alert-item alert-${alert.severity} bg-white border rounded-lg p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="flex-shrink-0">
                                    ${getAlertIcon(alert.severity)}
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900">${alert.message}</h3>
                                    <p class="text-sm text-gray-500">${formatDate(alert.timestamp)}</p>
                                </div>
                                ${!alert.read ? '<span class="bg-blue-500 text-white text-xs rounded-full px-2 py-1">New</span>' : ''}
                            </div>
                            ${alert.drug ? `
                                <div class="mt-2 text-xs text-gray-600">
                                    <span class="font-medium">Drug ID:</span> ${alert.drug.id} | 
                                    <span class="font-medium">Category:</span> ${alert.drug.category || 'N/A'} | 
                                    <span class="font-medium">Quantity:</span> ${alert.drug.quantity} | 
                                    <span class="font-medium">Expiry:</span> ${alert.drug.expiry_date || 'N/A'}
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex items-center space-x-2">
                            ${!alert.read ? 
                                `<button onclick="markAsRead('${alert.id}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Mark as Read</button>` : 
                                `<span class="text-xs text-gray-400">Read</span>`
                            }
                            <button onclick="dismissAlert('${alert.id}')" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getAlertIcon(severity) {
            const icons = {
                'critical': '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
                'warning': '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
                'info': '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };
            return icons[severity] || icons['info'];
        }

        function formatDate(date) {
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) {
                return 'Just now';
            } else if (diffInHours < 24) {
                return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
        }

        function updateAlertCounts() {
            const criticalCount = allAlerts.filter(a => a.severity === 'critical').length;
            const warningCount = allAlerts.filter(a => a.severity === 'warning').length;
            const infoCount = allAlerts.filter(a => a.severity === 'info').length;
            const totalCount = allAlerts.length;
            const unreadCount = allAlerts.filter(a => !a.read).length;

            document.getElementById('criticalCount').textContent = criticalCount;
            document.getElementById('warningCount').textContent = warningCount;
            document.getElementById('infoCount').textContent = infoCount;
            document.getElementById('totalCount').textContent = totalCount;
            document.getElementById('alertCounter').textContent = unreadCount;
            document.getElementById('totalAlerts').textContent = totalCount;
            document.getElementById('filteredCount').textContent = filteredAlerts.length;

            // Hide counter if no unread alerts
            const counter = document.getElementById('alertCounter');
            if (unreadCount > 0) {
                counter.classList.remove('hidden');
            } else {
                counter.classList.add('hidden');
            }
        }

        function markAsRead(alertId) {
            const alert = allAlerts.find(a => a.id === alertId);
            if (alert) {
                alert.read = true;
                filteredAlerts = [...allAlerts];
                updateAlertCounts();
                renderAlerts();
            }
        }

        function dismissAlert(alertId) {
            if (confirm('Are you sure you want to dismiss this alert?')) {
                allAlerts = allAlerts.filter(a => a.id !== alertId);
                filteredAlerts = [...allAlerts];
                updateAlertCounts();
                renderAlerts();
            }
        }

        function markAllAsRead() {
            if (confirm('Mark all alerts as read?')) {
                allAlerts.forEach(alert => alert.read = true);
                filteredAlerts = [...allAlerts];
                updateAlertCounts();
                renderAlerts();
            }
        }

        function applyFilters() {
            const typeFilter = document.getElementById('alertTypeFilter').value;
            const severityFilter = document.getElementById('alertSeverityFilter').value;
            
            filteredAlerts = allAlerts.filter(alert => {
                const typeMatch = !typeFilter || alert.type === typeFilter;
                const severityMatch = !severityFilter || alert.severity === severityFilter;
                
                return typeMatch && severityMatch;
            });
            
            updateAlertCounts();
            renderAlerts();
        }

        function clearFilters() {
            document.getElementById('alertTypeFilter').value = '';
            document.getElementById('alertSeverityFilter').value = '';
            filteredAlerts = [...allAlerts];
            updateAlertCounts();
            renderAlerts();
        }

        function refreshAlerts() {
            loadAlerts();
        }

        function showLoading(show) {
            const loadingDiv = document.getElementById('loadingState');
            const alertsList = document.getElementById('alertsList');
            const noAlertsDiv = document.getElementById('noAlertsMessage');
            
            if (show) {
                loadingDiv.classList.remove('hidden');
                alertsList.innerHTML = '';
                noAlertsDiv.classList.add('hidden');
            } else {
                loadingDiv.classList.add('hidden');
            }
        }

        function showNoAlerts() {
            const noAlertsDiv = document.getElementById('noAlertsMessage');
            const alertsList = document.getElementById('alertsList');
            
            alertsList.innerHTML = '';
            noAlertsDiv.classList.remove('hidden');
        }
    </script>
</body>
</html>
