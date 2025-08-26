<?php
session_start();
// Allow multiple clinician-type roles (Clinician, Doctor, Nurse, Midwife, Other)
$roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
$isClinician = in_array($roleName, ['clinician','doctor','nurse','midwife','other'], true);
if (!isset($_SESSION['user_id']) || !$isClinician) {
    header('Location: ../login.php');
    exit();
}

$clinician_id = $_SESSION['user_id'];
$clinician_name = $_SESSION['username'] ?? 'Clinician';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution History - RHU GAMU</title>
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
        
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        
        /* Status indicators */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-completed { background-color: #10b981; }
        .status-pending { background-color: #f59e0b; }
        .status-cancelled { background-color: #ef4444; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="clinician_dashboard.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($clinician_name); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Distribution History</h1>
            <p class="text-gray-600">View and manage your medicine distribution records</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-pills text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Distributions</p>
                        <p class="text-2xl font-semibold text-gray-900" id="totalDistributions">-</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Completed</p>
                        <p class="text-2xl font-semibold text-gray-900" id="completedDistributions">-</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900" id="pendingDistributions">-</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Patients Served</p>
                        <p class="text-2xl font-semibold text-gray-900" id="patientsServed">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Patient Name</label>
                        <input type="text" id="patientFilter" placeholder="Search patient..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medicine Name</label>
                        <input type="text" id="medicineFilter" placeholder="Search medicine..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="dateFilter" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="statusFilter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button onclick="applyFilters()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Distribution History Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Distribution Records</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Showing</span>
                        <select id="pageSize" onchange="loadDistributions()" 
                                class="px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-sm text-gray-600">records per page</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prescription Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distribution Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="distributionsTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalRecords">0</span> results
                    </div>
                    <div class="flex items-center space-x-2">
                        <button id="prevPage" onclick="previousPage()" 
                                class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <span class="text-sm text-gray-600">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                        <button id="nextPage" onclick="nextPage()" 
                                class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Distribution Details</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4" id="modalContent">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentPage = 1;
        let totalPages = 1;
        let totalRecords = 0;
        let pageSize = 10;
        let distributions = [];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadDistributions();
            
            // Event listeners
            document.getElementById('patientFilter').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') applyFilters();
            });
            document.getElementById('medicineFilter').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') applyFilters();
            });
        });

        async function loadStats() {
            try {
                const response = await fetch('../api/distribution_api.php?resource=distribution_stats');
                const data = await response.json();
                
                if (data.success) {
                    // Calculate additional stats
                    const totalDist = data.data.today_distributions + data.data.month_distributions;
                    document.getElementById('totalDistributions').textContent = totalDist;
                    document.getElementById('completedDistributions').textContent = data.data.month_distributions;
                    document.getElementById('pendingDistributions').textContent = '0'; // You might want to add this to the API
                    document.getElementById('patientsServed').textContent = data.data.patients_served;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadDistributions() {
            try {
                const params = new URLSearchParams({
                    page: currentPage,
                    limit: pageSize
                });

                // Add filters
                const patientFilter = document.getElementById('patientFilter').value;
                const medicineFilter = document.getElementById('medicineFilter').value;
                const dateFilter = document.getElementById('dateFilter').value;
                const statusFilter = document.getElementById('statusFilter').value;

                if (patientFilter) params.append('patient_name', patientFilter);
                if (medicineFilter) params.append('medicine_name', medicineFilter);
                if (dateFilter) params.append('date', dateFilter);
                if (statusFilter) params.append('status', statusFilter);

                const response = await fetch(`../api/distribution_api.php?resource=my_distributions&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    distributions = data.data;
                    totalRecords = data.total;
                    totalPages = Math.ceil(totalRecords / pageSize);
                    
                    renderDistributions();
                    updatePagination();
                }
            } catch (error) {
                console.error('Error loading distributions:', error);
            }
        }

        function renderDistributions() {
            const tbody = document.getElementById('distributionsTableBody');
            tbody.innerHTML = '';
            
            if (distributions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <p>No distribution records found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            distributions.forEach(dist => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                const statusClass = dist.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                  dist.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                  'bg-red-100 text-red-800';
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${dist.patient_name || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${dist.medicine_name || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${dist.quantity}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${dist.prescription_date || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${dist.distribution_date || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${dist.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                        ${dist.notes || 'No notes'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="viewDetails(${dist.id})" class="text-blue-600 hover:text-blue-900 mr-2">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        function updatePagination() {
            const start = (currentPage - 1) * pageSize + 1;
            const end = Math.min(currentPage * pageSize, totalRecords);
            
            document.getElementById('showingStart').textContent = totalRecords > 0 ? start : 0;
            document.getElementById('showingEnd').textContent = end;
            document.getElementById('totalRecords').textContent = totalRecords;
            document.getElementById('currentPage').textContent = currentPage;
            document.getElementById('totalPages').textContent = totalPages;
            
            document.getElementById('prevPage').disabled = currentPage <= 1;
            document.getElementById('nextPage').disabled = currentPage >= totalPages;
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                loadDistributions();
            }
        }

        function nextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                loadDistributions();
            }
        }

        function applyFilters() {
            currentPage = 1;
            loadDistributions();
        }

        async function viewDetails(distributionId) {
            try {
                const distribution = distributions.find(d => d.id === distributionId);
                if (!distribution) return;
                
                const modalContent = document.getElementById('modalContent');
                modalContent.innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Patient Name</label>
                                <p class="text-sm text-gray-900">${distribution.patient_name || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Medicine</label>
                                <p class="text-sm text-gray-900">${distribution.medicine_name || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <p class="text-sm text-gray-900">${distribution.quantity}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    distribution.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                    distribution.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'
                                }">
                                    ${distribution.status}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prescription Date</label>
                                <p class="text-sm text-gray-900">${distribution.prescription_date || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Distribution Date</label>
                                <p class="text-sm text-gray-900">${distribution.distribution_date || 'N/A'}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="text-sm text-gray-900">${distribution.notes || 'No notes provided'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Created At</label>
                            <p class="text-sm text-gray-900">${distribution.created_at || 'N/A'}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('detailsModal').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading distribution details:', error);
            }
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
