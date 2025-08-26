document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const distributionForm = document.getElementById('distributionForm');
    const distributionModal = document.getElementById('distributionModal');
    const closeDistributionModal = document.getElementById('closeDistributionModal');
    const distributionTableBody = document.getElementById('distributionTableBody');
    const distributionPagination = document.getElementById('distributionPagination');
    
    // Form elements
    const patientSearch = document.getElementById('patientSearch');
    const patientSearchResults = document.getElementById('patientSearchResults');
    const selectedPatientInfo = document.getElementById('selectedPatientInfo');
    const patientSelect = document.getElementById('patientSelect');
    const doctorSelect = document.getElementById('doctorSelect');
    const medicineSelect = document.getElementById('medicineSelect');
    const quantityInput = document.getElementById('quantityInput');
    const prescriptionDate = document.getElementById('prescriptionDate');
    const distributionDate = document.getElementById('distributionDate');
    const notesInput = document.getElementById('notesInput');
    const availableStock = document.getElementById('availableStock');
    const medicineValidation = document.getElementById('medicineValidation');
    const prescriptionValidation = document.getElementById('prescriptionValidation');
    const drugInteractions = document.getElementById('drugInteractions');
    const validationResults = document.getElementById('validationResults');
    const interactionWarnings = document.getElementById('interactionWarnings');
    const printModal = document.getElementById('printModal');
    const printContent = document.getElementById('printContent');
    
    // Filter elements
    const searchDistributions = document.getElementById('searchDistributions');
    const filterDoctor = document.getElementById('filterDoctor');
    const filterStatus = document.getElementById('filterStatus');
    const filterDate = document.getElementById('filterDate');
    
    // Statistics elements
    const distributionsToday = document.getElementById('distributionsToday');
    const distributionsMonth = document.getElementById('distributionsMonth');
    const pendingPrescriptions = document.getElementById('pendingPrescriptions');
    const activeDoctors = document.getElementById('activeDoctors');
    const lastUpdated = document.getElementById('lastUpdated');
    
    // --- State ---
    let currentPage = 1;
    const distributionsPerPage = 10;
    let allDistributions = [];
    let allPatients = [];
    let allDoctors = [];
    let allMedicines = [];
    let currentFilters = {
        search: '',
        doctor: '',
        status: '',
        date: ''
    };

    // --- API URLs ---
    const API_BASE = '../api/drug_api.php';
    const DRUGS_URL = `${API_BASE}?resource=drugs`;
    const DISTRIBUTIONS_URL = `${API_BASE}?resource=distributions`;
    const PATIENTS_URL = '../api/patient_api.php?resource=patients';
    const USERS_URL = '../api/user_api.php?resource=users';
    const AUDIT_URL = '../api/audit_api.php';

    // --- Utility Functions ---
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
            day: 'numeric' 
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

    // --- API Functions ---
    async function fetchPatients() {
        try {
            const response = await fetch(PATIENTS_URL);
            const result = await response.json();
            if (result.success) {
                allPatients = result.data || [];
                populatePatientSelect();
                return result.data;
            } else {
                console.error('Failed to fetch patients:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error fetching patients:', error);
            return [];
        }
    }

    async function fetchDoctors() {
        try {
            const response = await fetch(`${USERS_URL}&role=clinician`);
            const result = await response.json();
            if (result.success) {
                allDoctors = result.data || [];
                populateDoctorSelect();
                populateFilterDoctor();
                return result.data;
            } else {
                console.error('Failed to fetch doctors:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error fetching doctors:', error);
            return [];
        }
    }

    async function fetchMedicines() {
        try {
            const response = await fetch(`${DRUGS_URL}&page=1&limit=1000`);
            const result = await response.json();
            if (result.success) {
                allMedicines = result.data || [];
                populateMedicineSelect();
                return result.data;
            } else {
                console.error('Failed to fetch medicines:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error fetching medicines:', error);
            return [];
        }
    }

    async function fetchDistributions() {
        showLoading();
        try {
            const params = new URLSearchParams({
                page: currentPage,
                limit: distributionsPerPage,
                ...currentFilters
            });
            
            const response = await fetch(`${DISTRIBUTIONS_URL}&${params}`);
            const result = await response.json();
            
            if (result.success) {
                allDistributions = result.data || [];
                renderDistributionTable(allDistributions);
                renderDistributionPagination(result.total || 0);
                return result.data;
            } else {
                console.error('Failed to fetch distributions:', result.message);
                showNotification('Failed to load distributions', 'error');
                return [];
            }
        } catch (error) {
            console.error('Error fetching distributions:', error);
            showNotification('Network error loading distributions', 'error');
            return [];
        } finally {
            hideLoading();
        }
    }

    async function fetchDistributionStats() {
        try {
            const response = await fetch(`${DISTRIBUTIONS_URL}&stats=summary`);
            const result = await response.json();
            
            if (result.success && result.data) {
                updateStatistics(result.data);
            }
        } catch (error) {
            console.error('Error fetching distribution stats:', error);
        }
    }

    // --- Population Functions ---
    function populatePatientSelect() {
        if (!patientSelect) return;
        
        patientSelect.innerHTML = '<option value="">Select Patient</option>';
        allPatients.forEach(patient => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = `${patient.first_name} ${patient.last_name} (${patient.patient_id || 'N/A'})`;
            patientSelect.appendChild(option);
        });
    }

    function populateDoctorSelect() {
        if (!doctorSelect) return;
        
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
        allDoctors.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name}`;
            doctorSelect.appendChild(option);
        });
    }

    function populateFilterDoctor() {
        if (!filterDoctor) return;
        
        filterDoctor.innerHTML = '<option value="">All Doctors</option>';
        allDoctors.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name}`;
            filterDoctor.appendChild(option);
        });
    }

    function populateMedicineSelect() {
        if (!medicineSelect) return;
        
        medicineSelect.innerHTML = '<option value="">Select Medicine</option>';
        allMedicines.forEach(medicine => {
            const option = document.createElement('option');
            option.value = medicine.id;
            option.textContent = `${medicine.name} (Stock: ${medicine.quantity})`;
            option.dataset.stock = medicine.quantity;
            medicineSelect.appendChild(option);
        });
    }

    // --- Render Functions ---
    function renderDistributionTable(distributions) {
        if (!distributionTableBody) return;
        
        distributionTableBody.innerHTML = '';
        
        if (!distributions || distributions.length === 0) {
            distributionTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No distributions found. Create your first distribution above!
                    </td>
                </tr>
            `;
            return;
        }

        distributions.forEach(distribution => {
            const row = document.createElement('tr');
            row.className = 'table-row-hover';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${formatDate(distribution.distribution_date)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                    ${distribution.patient_name || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    ${distribution.medicine_name || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    ${distribution.quantity}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    ${distribution.doctor_name || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    ${getStatusBadge(distribution.status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="viewDistributionDetails(${distribution.id})" class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-200">
                        <i class="fas fa-eye mr-1"></i>View
                    </button>
                    <button onclick="printDistributionRecord(${distribution.id})" class="text-purple-600 hover:text-purple-900 mr-3 transition-colors duration-200">
                        <i class="fas fa-print mr-1"></i>Print
                    </button>
                    <button onclick="editDistribution(${distribution.id})" class="text-green-600 hover:text-green-900 mr-3 transition-colors duration-200">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button onclick="deleteDistribution(${distribution.id})" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </td>
            `;
            distributionTableBody.appendChild(row);
        });
    }

    function renderDistributionPagination(total) {
        if (!distributionPagination) return;
        
        distributionPagination.innerHTML = '';
        const totalPages = Math.ceil(total / distributionsPerPage);
        
        if (totalPages <= 1) return;

        // Previous button
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Previous';
        prevButton.className = 'pagination-button';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchDistributions();
            }
        });
        distributionPagination.appendChild(prevButton);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = `pagination-button ${i === currentPage ? 'active-page' : ''}`;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                fetchDistributions();
            });
            distributionPagination.appendChild(pageButton);
        }

        // Next button
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Next';
        nextButton.className = 'pagination-button';
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                fetchDistributions();
            }
        });
        distributionPagination.appendChild(nextButton);
    }

    function getStatusBadge(status) {
        const statusConfig = {
            'pending': { class: 'status-pending', text: 'Pending' },
            'approved': { class: 'status-approved', text: 'Approved' },
            'completed': { class: 'status-completed', text: 'Completed' },
            'cancelled': { class: 'status-cancelled', text: 'Cancelled' }
        };
        
        const config = statusConfig[status] || { class: 'status-pending', text: 'Unknown' };
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.class}">${config.text}</span>`;
    }

    function updateStatistics(stats) {
        if (distributionsToday) distributionsToday.textContent = formatNumber(stats.distributions_today || 0);
        if (distributionsMonth) distributionsMonth.textContent = formatNumber(stats.distributions_month || 0);
        if (pendingPrescriptions) pendingPrescriptions.textContent = formatNumber(stats.pending_prescriptions || 0);
        if (activeDoctors) activeDoctors.textContent = formatNumber(stats.active_doctors || 0);
        if (lastUpdated) lastUpdated.textContent = 'Just now';
    }

    function updateRecentActivity() {
        const recentActivity = document.getElementById('recentActivity');
        if (!recentActivity) return;

        const recentDistributions = allDistributions.slice(0, 5);
        
        if (recentDistributions.length === 0) {
            recentActivity.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-3xl mb-4"></i>
                    <p class="text-gray-500">No recent distributions</p>
                </div>
            `;
            return;
        }

        recentActivity.innerHTML = recentDistributions.map(distribution => `
            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-pills text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        ${distribution.medicine_name} distributed to ${distribution.patient_name}
                    </p>
                    <p class="text-xs text-gray-500">
                        Prescribed by ${distribution.doctor_name} • ${getTimeAgo(distribution.distribution_date)}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">${distribution.quantity}</p>
                    <p class="text-xs text-gray-500">units</p>
                </div>
            </div>
        `).join('');
    }

    // --- Enhanced Functions ---
    async function handlePatientSearch(e) {
        const searchTerm = e.target.value.trim();
        
        if (searchTerm.length < 2) {
            patientSearchResults.classList.add('hidden');
            return;
        }
        
        try {
            const response = await fetch(`${PATIENTS_URL}&search=${encodeURIComponent(searchTerm)}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                displayPatientSearchResults(result.data);
                patientSearchResults.classList.remove('hidden');
            } else {
                patientSearchResults.innerHTML = '<div class="p-3 text-gray-500">No patients found</div>';
                patientSearchResults.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error searching patients:', error);
        }
    }
    
    function displayPatientSearchResults(patients) {
        patientSearchResults.innerHTML = patients.map(patient => `
            <div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0" 
                 onclick="selectPatient(${patient.id}, '${patient.first_name} ${patient.last_name}', '${patient.contact_number || 'N/A'}', '${patient.address || 'N/A'}')">
                <div class="font-medium text-gray-900">${patient.first_name} ${patient.last_name}</div>
                <div class="text-sm text-gray-600">ID: ${patient.id} | Contact: ${patient.contact_number || 'N/A'}</div>
            </div>
        `).join('');
    }
    
    // --- Modal Functions ---
    window.openDistributionModal = function() {
        if (distributionModal) {
            distributionModal.classList.remove('hidden');
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            if (prescriptionDate) prescriptionDate.value = today;
            if (distributionDate) distributionDate.value = today;
        }
    };

    window.closeDistributionModal = function() {
        if (distributionModal) {
            distributionModal.classList.add('hidden');
            resetDistributionForm();
        }
    };

    function resetDistributionForm() {
        if (distributionForm) {
            distributionForm.reset();
        }
        if (availableStock) availableStock.textContent = '-';
    }

    // --- Form Handlers ---
    if (distributionForm) {
        distributionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(distributionForm);
            const distributionData = {
                patient_id: parseInt(formData.get('patient_id')),
                doctor_id: parseInt(formData.get('doctor_id')),
                medicine_id: parseInt(formData.get('medicine_id')),
                quantity: parseInt(formData.get('quantity')),
                prescription_date: formData.get('prescription_date'),
                distribution_date: formData.get('distribution_date'),
                notes: formData.get('notes') || ''
            };

            // Validation
            if (!distributionData.patient_id || !distributionData.doctor_id || !distributionData.medicine_id || !distributionData.quantity) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            if (distributionData.quantity <= 0) {
                showNotification('Quantity must be greater than 0', 'error');
                return;
            }

            // Check stock availability
            const selectedMedicine = allMedicines.find(m => m.id === distributionData.medicine_id);
            if (selectedMedicine && distributionData.quantity > selectedMedicine.quantity) {
                showNotification(`Insufficient stock. Available: ${selectedMedicine.quantity}`, 'error');
                return;
            }

            showLoading();
            try {
                const response = await fetch(DISTRIBUTIONS_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(distributionData)
                });

                const result = await response.json();
                
                if (result.success) {
                    // Log audit trail
                    await logAuditTrail('DISTRIBUTION_CREATED', `Created distribution: Patient ID ${distributionData.patient_id}, Medicine ID ${distributionData.medicine_id}, Quantity ${distributionData.quantity}`);
                    
                    showNotification('Distribution recorded successfully!', 'success');
                    closeDistributionModal();
                    await fetchDistributions();
                    await fetchDistributionStats();
                    updateRecentActivity();
                } else {
                    showNotification(result.message || 'Failed to record distribution', 'error');
                }
            } catch (error) {
                console.error('Error recording distribution:', error);
                showNotification('Network error recording distribution', 'error');
            } finally {
                hideLoading();
            }
        });
    }

    // --- Event Listeners ---
    if (closeDistributionModal) {
        closeDistributionModal.addEventListener('click', closeDistributionModal);
    }

    if (distributionModal) {
        distributionModal.addEventListener('click', (e) => {
            if (e.target === distributionModal) {
                closeDistributionModal();
            }
        });
    }

    // Medicine selection change
    if (medicineSelect) {
        medicineSelect.addEventListener('change', (e) => {
            const selectedMedicine = allMedicines.find(m => m.id === parseInt(e.target.value));
            if (selectedMedicine && availableStock) {
                availableStock.textContent = selectedMedicine.quantity;
            } else if (availableStock) {
                availableStock.textContent = '-';
            }
        });
    }

    // Filter event listeners
    if (searchDistributions) {
        searchDistributions.addEventListener('input', (e) => {
            currentFilters.search = e.target.value;
            currentPage = 1;
            fetchDistributions();
        });
    }

    if (filterDoctor) {
        filterDoctor.addEventListener('change', (e) => {
            currentFilters.doctor = e.target.value;
            currentPage = 1;
            fetchDistributions();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', (e) => {
            currentFilters.status = e.target.value;
            currentPage = 1;
            fetchDistributions();
        });
    }

    if (filterDate) {
            filterDate.addEventListener('change', (e) => {
        currentFilters.date = e.target.value;
        currentPage = 1;
        fetchDistributions();
    });
    
    // Patient search functionality
    patientSearch.addEventListener('input', handlePatientSearch);
    patientSearch.addEventListener('focus', () => {
        if (patientSearch.value.length > 0) {
            patientSearchResults.classList.remove('hidden');
        }
    });
    
    // Hide search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!patientSearch.contains(e.target) && !patientSearchResults.contains(e.target)) {
            patientSearchResults.classList.add('hidden');
        }
    });
    }

    // --- Action Functions ---
    window.refreshData = async function() {
        showLoading();
        try {
            await Promise.all([
                fetchDistributions(),
                fetchDistributionStats()
            ]);
            updateRecentActivity();
            showNotification('Data refreshed successfully!', 'success');
        } catch (error) {
            console.error('Error refreshing data:', error);
            showNotification('Failed to refresh data', 'error');
        } finally {
            hideLoading();
        }
    };

    window.clearFilters = function() {
        currentFilters = { search: '', doctor: '', status: '', date: '' };
        if (searchDistributions) searchDistributions.value = '';
        if (filterDoctor) filterDoctor.value = '';
        if (filterStatus) filterStatus.value = '';
        if (filterDate) filterDate.value = '';
        currentPage = 1;
        fetchDistributions();
    };

    window.viewDistributionDetails = function(distributionId) {
        showNotification('View details functionality coming soon!', 'info');
    };

    window.editDistribution = function(distributionId) {
        showNotification('Edit distribution functionality coming soon!', 'info');
    };

    // Additional enhanced functions
    window.selectPatient = function(patientId, patientName, contact, address) {
        patientSelect.value = patientId;
        patientSearch.value = patientName;
        selectedPatientInfo.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-blue-900">${patientName}</div>
                    <div class="text-sm text-blue-700">Contact: ${contact}</div>
                    <div class="text-sm text-blue-700">Address: ${address}</div>
                </div>
                <button type="button" onclick="clearPatientSelection()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        selectedPatientInfo.classList.remove('hidden');
        patientSearchResults.classList.add('hidden');
        
        // Check for existing prescriptions
        checkPatientPrescriptions(patientId);
    };
    
    window.clearPatientSelection = function() {
        patientSelect.value = '';
        patientSearch.value = '';
        selectedPatientInfo.classList.add('hidden');
        prescriptionValidation.classList.add('hidden');
    };
    
    window.validatePrescription = async function() {
        const patientId = patientSelect.value;
        const medicineId = medicineSelect.value;
        const quantity = quantityInput.value;
        const doctorId = doctorSelect.value;
        
        if (!patientId || !medicineId || !quantity || !doctorId) {
            showNotification('Please fill in all required fields first', 'warning');
            return;
        }
        
        showLoading();
        try {
            // Check stock availability
            const medicine = allMedicines.find(m => m.id == medicineId);
            if (medicine && quantity > medicine.quantity) {
                validationResults.innerHTML = `
                    <div class="text-red-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Insufficient stock! Available: ${medicine.quantity}, Requested: ${quantity}
                    </div>
                `;
                prescriptionValidation.classList.remove('hidden');
                return;
            }
            
            validationResults.innerHTML = `
                <div class="text-green-700">
                    <i class="fas fa-check-circle mr-1"></i>
                    Prescription validation passed
                </div>
            `;
            prescriptionValidation.classList.remove('hidden');
        } catch (error) {
            console.error('Error validating prescription:', error);
            showNotification('Error validating prescription', 'error');
        } finally {
            hideLoading();
        }
    };
    
    window.checkDrugInteractions = async function() {
        const medicineId = medicineSelect.value;
        const patientId = patientSelect.value;
        
        if (!medicineId || !patientId) {
            showNotification('Please select both patient and medicine first', 'warning');
            return;
        }
        
        showLoading();
        try {
            const currentMedicine = allMedicines.find(m => m.id == medicineId);
            
            interactionWarnings.innerHTML = `
                <div class="text-green-700">
                    <i class="fas fa-check-circle mr-1"></i>
                    No known drug interactions detected for ${currentMedicine.name}
                </div>
            `;
            drugInteractions.classList.remove('hidden');
        } catch (error) {
            console.error('Error checking drug interactions:', error);
            showNotification('Error checking drug interactions', 'error');
        } finally {
            hideLoading();
        }
    };
    
    window.printDistributionRecord = function(distributionId) {
        const distribution = allDistributions.find(d => d.id == distributionId);
        if (!distribution) {
            showNotification('Distribution record not found', 'error');
            return;
        }
        
        const printContent = generatePrintContent(distribution);
        document.getElementById('printContent').innerHTML = printContent;
        printModal.classList.remove('hidden');
    };
    
    window.closePrintModal = function() {
        printModal.classList.add('hidden');
    };
    
    function generatePrintContent(distribution) {
        const patient = allPatients.find(p => p.id == distribution.patient_id);
        const doctor = allDoctors.find(d => d.id == distribution.doctor_id);
        const medicine = allMedicines.find(m => m.id == distribution.medicine_id);
        
        return `
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">RHU GAMU - Medicine Distribution Record</h2>
                <p class="text-gray-600">Official Distribution Certificate</p>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">Patient Information</h3>
                    <p><strong>Name:</strong> ${patient ? `${patient.first_name} ${patient.last_name}` : 'N/A'}</p>
                    <p><strong>ID:</strong> ${distribution.patient_id}</p>
                    <p><strong>Contact:</strong> ${patient ? patient.contact_number || 'N/A' : 'N/A'}</p>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">Prescription Details</h3>
                    <p><strong>Doctor:</strong> ${doctor ? doctor.username : 'N/A'}</p>
                    <p><strong>Prescription Date:</strong> ${formatDate(distribution.prescription_date)}</p>
                    <p><strong>Distribution Date:</strong> ${formatDate(distribution.distribution_date)}</p>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="font-bold text-gray-900 mb-2">Medicine Details</h3>
                <p><strong>Medicine:</strong> ${medicine ? medicine.name : 'N/A'}</p>
                <p><strong>Quantity:</strong> ${distribution.quantity} units</p>
                <p><strong>Notes:</strong> ${distribution.notes || 'None'}</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600">
                    <strong>Important:</strong> This medicine has been dispensed as prescribed. 
                    Please follow the doctor's instructions and complete the full course of treatment.
                </p>
            </div>
        `;
    }
    
    async function checkPatientPrescriptions(patientId) {
        try {
            const response = await fetch(`${DISTRIBUTIONS_URL}&patient_id=${patientId}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                showPrescriptionHistory(result.data);
            }
        } catch (error) {
            console.error('Error checking patient prescriptions:', error);
        }
    }
    
    async function logAuditTrail(action, details) {
        try {
            await fetch(AUDIT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: action,
                    details: details,
                    timestamp: new Date().toISOString()
                })
            });
        } catch (error) {
            console.error('Error logging audit trail:', error);
        }
    }
    
    function showPrescriptionHistory(prescriptions) {
        const recentPrescriptions = prescriptions.slice(0, 5); // Show last 5
        validationResults.innerHTML = `
            <div class="text-sm text-yellow-700">
                <div class="font-medium mb-2">Recent Prescriptions:</div>
                ${recentPrescriptions.map(p => `
                    <div class="ml-2 mb-1">
                        • ${p.medicine_name} - ${p.quantity} units on ${formatDate(p.distribution_date)}
                    </div>
                `).join('')}
            </div>
        `;
        prescriptionValidation.classList.remove('hidden');
    }
    
    window.deleteDistribution = async function(distributionId) {
        const confirmed = await Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            showLoading();
            try {
                const response = await fetch(DISTRIBUTIONS_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: distributionId })
                });

                const result = await response.json();
                
                if (result.success) {
                    // Log audit trail
                    await logAuditTrail('DISTRIBUTION_DELETED', `Deleted distribution ID: ${distributionId}`);
                    
                    showNotification('Distribution deleted successfully!', 'success');
                    await fetchDistributions();
                    await fetchDistributionStats();
                    updateRecentActivity();
                } else {
                    showNotification(result.message || 'Failed to delete distribution', 'error');
                }
            } catch (error) {
                console.error('Error deleting distribution:', error);
                showNotification('Network error deleting distribution', 'error');
            } finally {
                hideLoading();
            }
        }
    };

    // --- Initialization ---
    async function initializePage() {
        showLoading();
        try {
            await Promise.all([
                fetchPatients(),
                fetchDoctors(),
                fetchMedicines(),
                fetchDistributions(),
                fetchDistributionStats()
            ]);
            updateRecentActivity();
        } catch (error) {
            console.error('Error initializing page:', error);
            showNotification('Failed to initialize page', 'error');
        } finally {
            hideLoading();
        }
    }

    // Start the application
    initializePage();
});


