document.addEventListener('DOMContentLoaded', () => {
    // --- Global Variables ---
    let dashboardData = {
        medicines: [],
        categories: [],
        stats: {},
        alerts: []
    };
    
    let charts = {
        stockChart: null,
        expiryChart: null
    };
    
    // --- API URLs ---
    const API_BASE = '../api/drug_api.php';
    const CATEGORIES_URL = `${API_BASE}?resource=categories`;
    const DRUGS_URL = `${API_BASE}?resource=drugs`;
    
    // --- Utility Functions ---
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    function formatNumber(num) {
        return new Intl.NumberFormat().format(num);
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
    
    // --- API Functions ---
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
    
    // --- Dashboard Statistics ---
    function calculateStats(medicines) {
        const now = new Date();
        const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        
        const stats = {
            totalMedicines: medicines.length,
            lowStockItems: medicines.filter(m => m.quantity <= 20).length,
            expiringSoon: medicines.filter(m => {
                if (!m.expiry_date) return false;
                const expiryDate = new Date(m.expiry_date);
                return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
            }).length,
            totalCategories: dashboardData.categories.length,
            outOfStock: medicines.filter(m => m.quantity === 0).length,
            expired: medicines.filter(m => {
                if (!m.expiry_date) return false;
                return new Date(m.expiry_date) < now;
            }).length
        };
        
        dashboardData.stats = stats;
        return stats;
    }
    
    function updateStatistics(stats) {
        document.getElementById('totalMedicines').textContent = formatNumber(stats.totalMedicines);
        document.getElementById('lowStockItems').textContent = formatNumber(stats.lowStockItems);
        document.getElementById('expiringSoon').textContent = formatNumber(stats.expiringSoon);
        document.getElementById('totalCategories').textContent = formatNumber(stats.totalCategories);
        
        // Update last updated time
        document.getElementById('lastUpdated').textContent = getTimeAgo(new Date());
    }
    
    // --- Charts ---
    function createStockChart(medicines) {
        const ctx = document.getElementById('stockChart');
        if (!ctx) return;
        
        // Group medicines by category
        const categoryData = {};
        medicines.forEach(medicine => {
            const categoryName = medicine.category_name || 'Uncategorized';
            if (!categoryData[categoryName]) {
                categoryData[categoryName] = 0;
            }
            categoryData[categoryName] += medicine.quantity;
        });
        
        const labels = Object.keys(categoryData);
        const data = Object.values(categoryData);
        
        if (charts.stockChart) {
            charts.stockChart.destroy();
        }
        
        charts.stockChart = new Chart(ctx, {
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
    
    function createExpiryChart(medicines) {
        const ctx = document.getElementById('expiryChart');
        if (!ctx) return;
        
        const now = new Date();
        const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        const sixtyDaysFromNow = new Date(now.getTime() + (60 * 24 * 60 * 60 * 1000));
        const ninetyDaysFromNow = new Date(now.getTime() + (90 * 24 * 60 * 60 * 1000));
        
        const expiring30Days = medicines.filter(m => {
            if (!m.expiry_date) return false;
            const expiryDate = new Date(m.expiry_date);
            return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
        }).length;
        
        const expiring60Days = medicines.filter(m => {
            if (!m.expiry_date) return false;
            const expiryDate = new Date(m.expiry_date);
            return expiryDate <= sixtyDaysFromNow && expiryDate > thirtyDaysFromNow;
        }).length;
        
        const expiring90Days = medicines.filter(m => {
            if (!m.expiry_date) return false;
            const expiryDate = new Date(m.expiry_date);
            return expiryDate <= ninetyDaysFromNow && expiryDate > sixtyDaysFromNow;
        }).length;
        
        if (charts.expiryChart) {
            charts.expiryChart.destroy();
        }
        
        charts.expiryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['30 Days', '60 Days', '90 Days'],
                datasets: [{
                    label: 'Medicines Expiring',
                    data: [expiring30Days, expiring60Days, expiring90Days],
                    backgroundColor: ['#EF4444', '#F59E0B', '#10B981'],
                    borderColor: ['#DC2626', '#D97706', '#059669'],
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
    
    // --- Recent Activity ---
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
                    <p class="text-xs text-gray-400">${formatDate(medicine.expiry_date)}</p>
                </div>
            </div>
        `).join('');
    }
    
    // --- Alerts System ---
    function generateAlerts(medicines) {
        const alerts = [];
        const now = new Date();
        
        // Check for low stock
        const lowStockMedicines = medicines.filter(m => m.quantity <= 20);
        if (lowStockMedicines.length > 0) {
            alerts.push({
                type: 'warning',
                title: 'Low Stock Alert',
                message: `${lowStockMedicines.length} medicine(s) have low stock (≤20 units)`,
                icon: 'exclamation-triangle',
                action: () => checkLowStock()
            });
        }
        
        // Check for expiring medicines
        const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        const expiringMedicines = medicines.filter(m => {
            if (!m.expiry_date) return false;
            const expiryDate = new Date(m.expiry_date);
            return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
        });
        
        if (expiringMedicines.length > 0) {
            alerts.push({
                type: 'danger',
                title: 'Expiry Alert',
                message: `${expiringMedicines.length} medicine(s) expiring within 30 days`,
                icon: 'clock',
                action: () => checkExpiringMedicines()
            });
        }
        
        // Check for out of stock
        const outOfStockMedicines = medicines.filter(m => m.quantity === 0);
        if (outOfStockMedicines.length > 0) {
            alerts.push({
                type: 'danger',
                title: 'Out of Stock',
                message: `${outOfStockMedicines.length} medicine(s) are out of stock`,
                icon: 'times-circle',
                action: () => checkOutOfStock()
            });
        }
        
        // Check for expired medicines
        const expiredMedicines = medicines.filter(m => {
            if (!m.expiry_date) return false;
            return new Date(m.expiry_date) < now;
        });
        
        if (expiredMedicines.length > 0) {
            alerts.push({
                type: 'danger',
                title: 'Expired Medicines',
                message: `${expiredMedicines.length} medicine(s) have expired`,
                icon: 'calendar-times',
                action: () => checkExpiredMedicines()
            });
        }
        
        dashboardData.alerts = alerts;
        updateAlertsDisplay(alerts);
    }
    
    function updateAlertsDisplay(alerts) {
        const alertsContainer = document.getElementById('alertsContainer');
        const alertsSection = document.getElementById('alertsSection');
        
        if (!alertsContainer) return;
        
        if (alerts.length === 0) {
            alertsSection.classList.add('hidden');
            return;
        }
        
        alertsSection.classList.remove('hidden');
        alertsContainer.innerHTML = alerts.map(alert => `
            <div class="flex items-center justify-between p-4 bg-${alert.type === 'danger' ? 'red' : 'orange'}-50 border border-${alert.type === 'danger' ? 'red' : 'orange'}-200 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-${alert.type === 'danger' ? 'red' : 'orange'}-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-${alert.icon} text-${alert.type === 'danger' ? 'red' : 'orange'}-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-${alert.type === 'danger' ? 'red' : 'orange'}-900">${alert.title}</p>
                        <p class="text-sm text-${alert.type === 'danger' ? 'red' : 'orange'}-700">${alert.message}</p>
                    </div>
                </div>
                <button onclick="handleAlertAction(${alerts.indexOf(alert)})" class="text-${alert.type === 'danger' ? 'red' : 'orange'}-600 hover:text-${alert.type === 'danger' ? 'red' : 'orange'}-800 font-medium text-sm">
                    View Details
                </button>
            </div>
        `).join('');
    }
    
    // --- Quick Actions ---
    window.addNewMedicine = function() {
        window.location.href = './manage_inventory.php';
    };
    
    window.checkLowStock = function() {
        const lowStockMedicines = dashboardData.medicines.filter(m => m.quantity <= 20);
        if (lowStockMedicines.length === 0) {
            showNotification('No low stock items found!', 'success');
            return;
        }
        
        const medicineList = lowStockMedicines.map(m => 
            `• ${m.name} (${m.quantity} units)`
        ).join('\n');
        
        Swal.fire({
            title: 'Low Stock Items',
            html: `<div class="text-left"><pre class="text-sm">${medicineList}</pre></div>`,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    };
    
    window.generateReport = function() {
        window.location.href = './reports.php';
    };
    
    window.viewNotifications = function() {
        window.location.href = './notifications.php';
    };
    
    window.viewAllActivity = function() {
        window.location.href = './manage_inventory.php';
    };
    
    window.handleAlertAction = function(alertIndex) {
        const alert = dashboardData.alerts[alertIndex];
        if (alert && alert.action) {
            alert.action();
        }
    };
    
    // --- Dashboard Refresh ---
    window.refreshDashboard = async function() {
        showLoading();
        try {
            await loadDashboardData();
            showNotification('Dashboard refreshed successfully!', 'success');
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            showNotification('Failed to refresh dashboard', 'error');
        } finally {
            hideLoading();
        }
    };
    
    // --- Main Dashboard Load ---
    async function loadDashboardData() {
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
            createStockChart(medicines);
            createExpiryChart(medicines);
            updateRecentActivity(medicines);
            generateAlerts(medicines);
            
            // Update notification badge
            const totalAlerts = dashboardData.alerts.length;
            const notificationBadge = document.getElementById('notificationBadge');
            if (notificationBadge) {
                notificationBadge.textContent = totalAlerts;
                notificationBadge.style.display = totalAlerts > 0 ? 'flex' : 'none';
            }
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showNotification('Failed to load dashboard data', 'error');
        }
    }
    
    // --- Auto-refresh functionality ---
    function startAutoRefresh() {
        // Refresh every 5 minutes
        setInterval(async () => {
            try {
                await loadDashboardData();
                console.log('Dashboard auto-refreshed');
            } catch (error) {
                console.error('Auto-refresh failed:', error);
            }
        }, 5 * 60 * 1000);
    }
    
    // --- Keyboard Shortcuts ---
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + R to refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshDashboard();
        }
        
        // Ctrl/Cmd + N to go to notifications
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            viewNotifications();
        }
        
        // Ctrl/Cmd + I to go to inventory
        if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
            e.preventDefault();
            window.location.href = './manage_inventory.php';
        }
    });
    
    // --- Initialize Dashboard ---
    async function initDashboard() {
        showLoading();
        try {
            await loadDashboardData();
            startAutoRefresh();
        } catch (error) {
            console.error('Failed to initialize dashboard:', error);
            showNotification('Failed to initialize dashboard', 'error');
        } finally {
            hideLoading();
        }
    }
    
    // Start the dashboard
    initDashboard();
});
