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
        
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        
        /* Card hover effects */
        .medicine-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .medicine-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Status indicators */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-available { background-color: #10b981; }
        .status-low { background-color: #f59e0b; }
        .status-expired { background-color: #ef4444; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="clinician_dashboard.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <a href="distribution_history.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-history mr-2"></i>Distribution History
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Medicine Distribution</h1>
            <p class="text-gray-600">Distribute available medicines to patients</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-pills text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Distributions</p>
                        <p class="text-2xl font-semibold text-gray-900" id="todayDistributions">-</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900" id="monthDistributions">-</p>
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
            
            <div class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-medicine-bottle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Available Medicines</p>
                        <p class="text-2xl font-semibold text-gray-900" id="availableMedicines">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Available Medicines -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Available Medicines</h2>
                        <p class="text-sm text-gray-600">Select a medicine to distribute to a patient</p>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <input type="text" id="medicineSearch" placeholder="Search medicines..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div id="medicinesList" class="space-y-4">
                            <!-- Medicines will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribution Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow sticky top-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Distribute Medicine</h2>
                        <p class="text-sm text-gray-600">Fill in the details to distribute medicine</p>
                    </div>
                    <div class="p-6">
                        <form id="distributionForm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
                                    <select id="patientSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Select a patient...</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Medicine</label>
                                    <input type="text" id="selectedMedicine" readonly 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <input type="hidden" id="selectedMedicineId">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input type="number" id="quantity" min="1" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1">Available: <span id="availableQuantity">-</span></p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prescription Date</label>
                                    <input type="date" id="prescriptionDate" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea id="notes" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                              placeholder="Additional notes about the prescription..."></textarea>
                                </div>
                                
                                <button type="submit" 
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i>Distribute Medicine
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Distributions -->
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Distributions</h2>
                </div>
                <div class="p-6">
                    <div id="recentDistributions">
                        <!-- Recent distributions will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let medicines = [];
        let patients = [];
        let selectedMedicine = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadAvailableMedicines();
            loadPatients();
            loadRecentDistributions();
            setDefaultDate();
            
            // Event listeners
            document.getElementById('medicineSearch').addEventListener('input', filterMedicines);
            document.getElementById('distributionForm').addEventListener('submit', handleDistribution);
        });

        function setDefaultDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('prescriptionDate').value = today;
        }

        async function loadStats() {
            try {
                const response = await fetch('../api/distribution_api.php?resource=distribution_stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('todayDistributions').textContent = data.data.today_distributions;
                    document.getElementById('monthDistributions').textContent = data.data.month_distributions;
                    document.getElementById('patientsServed').textContent = data.data.patients_served;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadAvailableMedicines() {
            try {
                const response = await fetch('../api/distribution_api.php?resource=available_medicines');
                const data = await response.json();
                
                if (data.success) {
                    medicines = data.data;
                    document.getElementById('availableMedicines').textContent = medicines.length;
                    renderMedicines(medicines);
                }
            } catch (error) {
                console.error('Error loading medicines:', error);
            }
        }

        async function loadPatients() {
            try {
                const response = await fetch('../api/patient_api.php?resource=patients');
                const data = await response.json();
                
                if (data.success) {
                    patients = data.data;
                    const select = document.getElementById('patientSelect');
                    select.innerHTML = '<option value="">Select a patient...</option>';
                    
                    patients.forEach(patient => {
                        const option = document.createElement('option');
                        option.value = patient.id;
                        option.textContent = `${patient.first_name} ${patient.last_name}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }

        async function loadRecentDistributions() {
            try {
                const response = await fetch('../api/distribution_api.php?resource=my_distributions&limit=5');
                const data = await response.json();
                
                if (data.success) {
                    renderRecentDistributions(data.data);
                }
            } catch (error) {
                console.error('Error loading recent distributions:', error);
            }
        }

        function renderMedicines(medicinesToRender) {
            const container = document.getElementById('medicinesList');
            container.innerHTML = '';
            
            if (medicinesToRender.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-8">No medicines available</p>';
                return;
            }
            
            medicinesToRender.forEach(medicine => {
                const card = document.createElement('div');
                card.className = 'medicine-card bg-gray-50 rounded-lg p-4 cursor-pointer border-2 border-transparent hover:border-blue-300';
                
                const expiryDate = new Date(medicine.expiry_date);
                const today = new Date();
                const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                
                let statusClass = 'status-available';
                let statusText = 'Available';
                
                if (daysUntilExpiry <= 0) {
                    statusClass = 'status-expired';
                    statusText = 'Expired';
                } else if (daysUntilExpiry <= 30) {
                    statusClass = 'status-low';
                    statusText = 'Expiring Soon';
                }
                
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">${medicine.name}</h3>
                            <p class="text-sm text-gray-600">Quantity: ${medicine.quantity}</p>
                            <p class="text-sm text-gray-600">Expires: ${medicine.expiry_date}</p>
                        </div>
                        <div class="flex items-center">
                            <span class="status-dot ${statusClass}"></span>
                            <span class="text-sm text-gray-600">${statusText}</span>
                        </div>
                    </div>
                `;
                
                card.addEventListener('click', () => selectMedicine(medicine));
                container.appendChild(card);
            });
        }

        function selectMedicine(medicine) {
            selectedMedicine = medicine;
            document.getElementById('selectedMedicine').value = medicine.name;
            document.getElementById('selectedMedicineId').value = medicine.id;
            document.getElementById('availableQuantity').textContent = medicine.quantity;
            document.getElementById('quantity').max = medicine.quantity;
            document.getElementById('quantity').value = '';
            
            // Highlight selected medicine
            document.querySelectorAll('.medicine-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
        }

        function filterMedicines() {
            const searchTerm = document.getElementById('medicineSearch').value.toLowerCase();
            const filtered = medicines.filter(medicine => 
                medicine.name.toLowerCase().includes(searchTerm)
            );
            renderMedicines(filtered);
        }

        async function handleDistribution(event) {
            event.preventDefault();
            
            if (!selectedMedicine) {
                Swal.fire('Error', 'Please select a medicine first', 'error');
                return;
            }
            
            const formData = {
                patient_id: document.getElementById('patientSelect').value,
                medicine_id: selectedMedicine.id,
                quantity: document.getElementById('quantity').value,
                prescription_date: document.getElementById('prescriptionDate').value,
                notes: document.getElementById('notes').value
            };
            
            if (!formData.patient_id || !formData.quantity) {
                Swal.fire('Error', 'Please fill in all required fields', 'error');
                return;
            }
            
            if (parseInt(formData.quantity) > selectedMedicine.quantity) {
                Swal.fire('Error', 'Quantity exceeds available stock', 'error');
                return;
            }
            
            try {
                const response = await fetch('../api/distribution_api.php?resource=distribute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire('Success', 'Medicine distributed successfully!', 'success');
                    
                    // Reset form
                    document.getElementById('distributionForm').reset();
                    document.getElementById('selectedMedicine').value = '';
                    document.getElementById('selectedMedicineId').value = '';
                    document.getElementById('availableQuantity').textContent = '-';
                    selectedMedicine = null;
                    
                    // Remove selection highlight
                    document.querySelectorAll('.medicine-card').forEach(card => {
                        card.classList.remove('border-blue-500', 'bg-blue-50');
                    });
                    
                    // Reload data
                    loadStats();
                    loadAvailableMedicines();
                    loadRecentDistributions();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error distributing medicine:', error);
                Swal.fire('Error', 'An error occurred while distributing medicine', 'error');
            }
        }

        function renderRecentDistributions(distributions) {
            const container = document.getElementById('recentDistributions');
            
            if (distributions.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-8">No recent distributions</p>';
                return;
            }
            
            const table = document.createElement('table');
            table.className = 'min-w-full divide-y divide-gray-200';
            table.innerHTML = `
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${distributions.map(dist => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${dist.patient_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dist.medicine_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dist.quantity}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dist.distribution_date}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${dist.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                    ${dist.status}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            `;
            
            container.innerHTML = '';
            container.appendChild(table);
        }
    </script>
</body>
</html>
