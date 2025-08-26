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
    <title>Medicine Distribution - RHU GAMU</title>
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
        
        /* Distribution form styles */
        .distribution-form {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        /* Prescription status styles */
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-completed { background-color: #dbeafe; color: #1e40af; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
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
                    <a href="#" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg text-white shadow-lg">
                        <i class="fas fa-hand-holding-medical w-5"></i>
                        <span class="text-sm font-medium">Distribution</span>
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
                        <h1 class="text-2xl font-bold text-gray-900">Medicine Distribution</h1>
                        <p class="text-gray-600">Track medicine distribution to patients with doctor accountability</p>
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
                        <button onclick="openDistributionModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>New Distribution</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Distribution Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Distributions</p>
                                <p class="text-2xl font-bold text-gray-900" id="distributionsToday">-</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-arrow-up"></i>
                                    <span id="todayGrowth">0%</span> from yesterday
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">This Month</p>
                                <p class="text-2xl font-bold text-gray-900" id="distributionsMonth">-</p>
                                <p class="text-xs text-blue-600 mt-1">
                                    <i class="fas fa-chart-line"></i>
                                    Total distributions
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-info rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Prescriptions</p>
                                <p class="text-2xl font-bold text-orange-600" id="pendingPrescriptions">-</p>
                                <p class="text-xs text-orange-600 mt-1">
                                    <i class="fas fa-clock"></i>
                                    Awaiting distribution
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-warning rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Active Doctors</p>
                                <p class="text-2xl font-bold text-gray-900" id="activeDoctors">-</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-user-md"></i>
                                    Prescribing this month
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-success rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-md text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <div class="relative">
                                <input type="text" id="searchDistributions" placeholder="Search by patient name, doctor, or medicine..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <select id="filterDoctor" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">All Doctors</option>
                        </select>
                        <select id="filterStatus" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <input type="date" id="filterDate" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <button onclick="clearFilters()" class="px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Distribution Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Distribution Records</h3>
                        <p class="text-sm text-gray-600 mt-1">Track all medicine distributions with doctor and patient details</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prescribed By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="distributionTableBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading distributions...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="distributionPagination" class="flex justify-center items-center space-x-2 p-6 border-t border-gray-200"></div>
                </div>

                <!-- Recent Activity -->
                <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Distribution Activity</h3>
                    <div id="recentActivity" class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-pills text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Loading recent activity...</p>
                                <p class="text-xs text-gray-500">Please wait</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Modal -->
    <div id="distributionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl animate-fade-in-up">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">New Medicine Distribution</h3>
                    <button id="closeDistributionModal" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="distributionForm" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient Selection with Search -->
                        <div>
                            <label for="patientSearch" class="block text-sm font-medium text-gray-700 mb-2">Patient Search</label>
                            <div class="relative">
                                <input type="text" id="patientSearch" placeholder="Search patient by name, ID, or contact..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <div id="patientSearchResults" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Search results will be populated here -->
                                </div>
                            </div>
                            <input type="hidden" id="patientSelect" name="patient_id" required>
                            <div id="selectedPatientInfo" class="mt-2 p-3 bg-blue-50 rounded-lg hidden">
                                <!-- Selected patient info will be shown here -->
                            </div>
                        </div>
                        
                        <!-- Doctor Selection -->
                        <div>
                            <label for="doctorSelect" class="block text-sm font-medium text-gray-700 mb-2">Prescribing Doctor</label>
                            <select id="doctorSelect" name="doctor_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select Doctor</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Medicine Selection with Validation -->
                        <div>
                            <label for="medicineSelect" class="block text-sm font-medium text-gray-700 mb-2">Medicine</label>
                            <select id="medicineSelect" name="medicine_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select Medicine</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Available stock will be shown</p>
                            <div id="medicineValidation" class="mt-2 hidden">
                                <!-- Medicine validation info will be shown here -->
                            </div>
                        </div>
                        
                        <!-- Quantity -->
                        <div>
                            <label for="quantityInput" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" id="quantityInput" name="quantity" min="1" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="Enter quantity">
                            <p class="text-xs text-gray-500 mt-1">Available: <span id="availableStock">-</span></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Prescription Date -->
                        <div>
                            <label for="prescriptionDate" class="block text-sm font-medium text-gray-700 mb-2">Prescription Date</label>
                            <input type="date" id="prescriptionDate" name="prescription_date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        </div>
                        
                        <!-- Distribution Date -->
                        <div>
                            <label for="distributionDate" class="block text-sm font-medium text-gray-700 mb-2">Distribution Date</label>
                            <input type="date" id="distributionDate" name="distribution_date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        </div>
                    </div>
                    
                    <!-- Prescription Validation -->
                    <div id="prescriptionValidation" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hidden">
                        <h4 class="text-sm font-medium text-yellow-800 mb-2">Prescription Validation</h4>
                        <div id="validationResults" class="space-y-2">
                            <!-- Validation results will be shown here -->
                        </div>
                    </div>
                    
                    <!-- Drug Interaction Warnings -->
                    <div id="drugInteractions" class="bg-red-50 border border-red-200 rounded-lg p-4 hidden">
                        <h4 class="text-sm font-medium text-red-800 mb-2">⚠️ Drug Interaction Warnings</h4>
                        <div id="interactionWarnings" class="space-y-2">
                            <!-- Drug interaction warnings will be shown here -->
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div>
                        <label for="notesInput" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="notesInput" name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="Additional notes about the distribution..."></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <div class="flex space-x-2">
                            <button type="button" onclick="validatePrescription()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Validate Prescription
                            </button>
                            <button type="button" onclick="checkDrugInteractions()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Check Interactions
                            </button>
                        </div>
                        <div class="flex space-x-4">
                            <button type="button" onclick="closeDistributionModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Record Distribution
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Print Distribution Record Modal -->
    <div id="printModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl animate-fade-in-up">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">Print Distribution Record</h3>
                    <button onclick="closePrintModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div id="printContent" class="bg-gray-50 p-6 rounded-lg">
                        <!-- Print content will be generated here -->
                    </div>
                    
                    <div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-200">
                        <button onclick="closePrintModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            Cancel
                        </button>
                        <button onclick="printDistributionRecord()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-print mr-2"></i>
                            Print Record
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
            <span class="text-gray-700">Processing...</span>
        </div>
    </div>

    <script src="../js/pharmacists_distribute.js"></script>
</body>
</html>
