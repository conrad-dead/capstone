document.addEventListener('DOMContentLoaded', () => {
    // Tab management
    const tabs = ['Patient', 'Records', 'Patients', 'AddPatient', 'Reports'];
    const tabButtons = tabs.map(tab => document.getElementById(`tab${tab}Btn`));
    const tabContents = tabs.map(tab => document.getElementById(`tab${tab}`));

    // Form elements
    const interviewForm = document.getElementById('interviewForm');
    const patientSearch = document.getElementById('patientSearch');
    const patientSelect = document.getElementById('patientSelect');
    const patientInfo = document.getElementById('patientInfo');
    const patientId = document.getElementById('patientId');
    const searchResults = document.getElementById('searchResults');
    const refreshPatientsBtn = document.getElementById('refreshPatientsBtn');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const viewPatientHistoryBtn = document.getElementById('viewPatientHistoryBtn');
    const editPatientBtn = document.getElementById('editPatientBtn');
    const quickStats = document.getElementById('quickStats');
    
    // Patient info display elements
    const displayPatientName = document.getElementById('displayPatientName');
    const displayPatientAge = document.getElementById('displayPatientAge');
    const displayPatientGender = document.getElementById('displayPatientGender');
    const displayPatientContact = document.getElementById('displayPatientContact');
    const displayPatientAddress = document.getElementById('displayPatientAddress');
    const displayPatientBlood = document.getElementById('displayPatientBlood');
    const displayPatientBarangay = document.getElementById('displayPatientBarangay');
    const displayPatientEmergency = document.getElementById('displayPatientEmergency');
    const displayPatientMedicalHistory = document.getElementById('displayPatientMedicalHistory');
    const displayPatientAllergies = document.getElementById('displayPatientAllergies');
    const displayPatientPhilHealth = document.getElementById('displayPatientPhilHealth');
    const displayPatientInsurance = document.getElementById('displayPatientInsurance');
    const recentVisitsList = document.getElementById('recentVisitsList');

    // Form fields
    const chiefComplaints = document.getElementById('chiefComplaints');
    const bloodPressure = document.getElementById('bloodPressure');
    const temperature = document.getElementById('temperature');
    const height = document.getElementById('height');
    const weight = document.getElementById('weight');
    const diagnosisSelect = document.getElementById('diagnosisSelect');
    const diagnosisNotes = document.getElementById('diagnosisNotes');
    const treatment = document.getElementById('treatment');
    const visitMode = document.getElementById('visitMode');
    const barangaySelect = document.getElementById('barangaySelect');
    const consultationTime = document.getElementById('consultationTime');
    const referredFrom = document.getElementById('referredFrom');
    const referredTo = document.getElementById('referredTo');
    const referralInfo = document.getElementById('referralInfo');

    // API URLs
    const PATIENT_API = '../api/patient_api.php?resource=patients';
    const MEDICAL_RECORD_API = '../api/medical_record_api.php';
    const DISEASE_API = '../api/disease_api.php';
    const BARANGAY_API = '../api/barangay_api.php';
    const PATIENT_CREATE_API = '../api/patient_api.php?resource=patient';

    // State
    let currentPatient = null;
    let allPatients = [];
    let allDiseases = [];
    let allBarangays = [];

    // Initialize
    initializeTabs();
    loadInitialData();
    setupEventListeners();

    // Global functions for modals
    window.closePatientHistoryModal = function() {
        document.getElementById('patientHistoryModal').classList.add('hidden');
    };

    window.closePatientEditModal = function() {
        document.getElementById('patientEditModal').classList.add('hidden');
    };

    window.refreshDashboard = function() {
        // Update last updated time
        const now = new Date();
        document.getElementById('lastUpdated').textContent = now.toLocaleTimeString();
        
        // Refresh all data
        loadInitialData();
        
        // Show success message
        showAlert('Dashboard refreshed successfully!', 'success');
    };

    window.showPatientHistory = function() {
        if (!currentPatient) {
            showAlert('Please select a patient first.', 'error');
            return;
        }
        
        const modal = document.getElementById('patientHistoryModal');
        const content = document.getElementById('patientHistoryContent');
        
        // Show loading
        content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><div class="mt-2">Loading patient history...</div></div>';
        modal.classList.remove('hidden');
        
        // Load patient history
        loadPatientHistory(currentPatient.id);
    };

    window.showPatientEdit = function() {
        if (!currentPatient) {
            showAlert('Please select a patient first.', 'error');
            return;
        }
        
        const modal = document.getElementById('patientEditModal');
        const form = document.getElementById('patientEditForm');
        
        // Show loading
        form.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><div class="mt-2">Loading patient data...</div></div>';
        modal.classList.remove('hidden');
        
        // Load patient edit form
        loadPatientEditForm(currentPatient.id);
    };

    function initializeTabs() {
        tabButtons.forEach((btn, index) => {
            if (btn) {
                btn.addEventListener('click', () => showTab(tabs[index].toLowerCase()));
            }
        });
    }

    function showTab(tabName) {
        // Hide all tabs
        tabContents.forEach(tab => {
            if (tab) tab.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        tabButtons.forEach(btn => {
            if (btn) {
                btn.classList.remove('active', 'bg-blue-700', 'text-white', 'shadow-lg');
                btn.classList.add('text-blue-100', 'hover:bg-blue-700');
            }
        });
        
        // Show selected tab and activate button
        const tabIndex = tabs.findIndex(tab => tab.toLowerCase() === tabName);
        if (tabIndex !== -1) {
            if (tabContents[tabIndex]) {
                tabContents[tabIndex].classList.remove('hidden');
                tabContents[tabIndex].classList.add('animate-fade-in-up');
            }
            if (tabButtons[tabIndex]) {
                tabButtons[tabIndex].classList.add('active', 'bg-blue-700', 'text-white', 'shadow-lg');
                tabButtons[tabIndex].classList.remove('text-blue-100', 'hover:bg-blue-700');
            }
        }

        // Load tab-specific data
        switch(tabName) {
            case 'patients':
                loadPatientsList();
                break;
            case 'addpatient':
                // ensure barangays are loaded for the form
                if (!allBarangays || allBarangays.length === 0) {
                    loadBarangays();
                }
                populateAddPatientBarangays();
                break;
            case 'records':
                loadMedicalRecords();
                break;
            case 'reports':
                loadReports();
                break;
        }
    }

    async function loadInitialData() {
        try {
            await Promise.all([
                loadPatients(),
                loadDiseases(),
                loadBarangays()
            ]);
        } catch (error) {
            console.error('Error loading initial data:', error);
        }
    }

    async function loadPatients() {
        try {
            const response = await fetch(PATIENT_API);
            const result = await response.json();
            if (result.success) {
                allPatients = result.data;
                populatePatientDropdowns();
            }
        } catch (error) {
            console.error('Error loading patients:', error);
        }
    }

    async function loadDiseases() {
        try {
            const response = await fetch(DISEASE_API);
            const result = await response.json();
            if (result.success) {
                allDiseases = result.data;
                populateDiseaseDropdown();
            }
        } catch (error) {
            console.error('Error loading diseases:', error);
        }
    }

    async function loadBarangays() {
        try {
            const response = await fetch(BARANGAY_API);
            const result = await response.json();
            if (result.success) {
                allBarangays = result.data;
                populateBarangayDropdown();
            }
        } catch (error) {
            console.error('Error loading barangays:', error);
        }
    }

    function populatePatientDropdowns() {
        // Populate patient select for interview form
        patientSelect.innerHTML = '<option value="">Choose patient...</option>';
        allPatients.forEach(patient => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = `${patient.patient_code} - ${patient.first_name} ${patient.last_name}`;
            patientSelect.appendChild(option);
        });

        // Populate patient filter for records
        const recordPatientFilter = document.getElementById('recordPatientFilter');
        if (recordPatientFilter) {
            recordPatientFilter.innerHTML = '<option value="">All Patients</option>';
            allPatients.forEach(patient => {
                const option = document.createElement('option');
                option.value = patient.id;
                option.textContent = `${patient.patient_code} - ${patient.first_name} ${patient.last_name}`;
                recordPatientFilter.appendChild(option);
            });
        }
    }

    function populateDiseaseDropdown() {
        diagnosisSelect.innerHTML = '<option value="">Select diagnosis...</option>';
        allDiseases.forEach(disease => {
            const option = document.createElement('option');
            option.value = disease.id;
            option.textContent = disease.name;
            diagnosisSelect.appendChild(option);
        });
    }

    function populateBarangayDropdown() {
        barangaySelect.innerHTML = '<option value="">Select barangay...</option>';
        allBarangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.id;
            option.textContent = barangay.barangay_name;
            barangaySelect.appendChild(option);
        });
        populateAddPatientBarangays();
    }

    function populateAddPatientBarangays() {
        const apBarangay = document.getElementById('ap_barangay_id');
        if (!apBarangay) return;
        apBarangay.innerHTML = '<option value="">Select barangay</option>';
        allBarangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.id;
            option.textContent = barangay.barangay_name;
            apBarangay.appendChild(option);
        });
    }

    function setupEventListeners() {
        // Patient selection
        if (patientSelect) {
            patientSelect.addEventListener('change', handlePatientSelection);
        }

        // Patient search
        if (patientSearch) {
            patientSearch.addEventListener('input', handlePatientSearch);
        }

        // Visit mode change
        if (visitMode) {
            visitMode.addEventListener('change', handleVisitModeChange);
        }

        // Form submission
        if (interviewForm) {
            interviewForm.addEventListener('submit', handleInterviewSubmission);
        }

        // Clear form
        const clearFormBtn = document.getElementById('clearFormBtn');
        if (clearFormBtn) {
            clearFormBtn.addEventListener('click', clearInterviewForm);
        }

        // New patient button
        const newPatientBtn = document.getElementById('newPatientBtn');
        if (newPatientBtn) {
            newPatientBtn.addEventListener('click', () => showTab('patients'));
        }

        // Add patient button
        const addPatientBtn = document.getElementById('addPatientBtn');
        if (addPatientBtn) {
            addPatientBtn.addEventListener('click', showAddPatientModal);
        }

        // Add patient form submit
        const addPatientForm = document.getElementById('addPatientForm');
        if (addPatientForm) {
            addPatientForm.addEventListener('submit', handleAddPatientSubmission);
        }

        // Patient list search
        const patientListSearch = document.getElementById('patientListSearch');
        if (patientListSearch) {
            patientListSearch.addEventListener('input', handlePatientListSearch);
        }

        // Export buttons
        const exportConsultationsBtn = document.getElementById('exportConsultationsBtn');
        if (exportConsultationsBtn) {
            exportConsultationsBtn.addEventListener('click', exportConsultations);
        }

        const exportPatientsBtn = document.getElementById('exportPatientsBtn');
        if (exportPatientsBtn) {
            exportPatientsBtn.addEventListener('click', exportPatients);
        }

        const exportDiagnosesBtn = document.getElementById('exportDiagnosesBtn');
        if (exportDiagnosesBtn) {
            exportDiagnosesBtn.addEventListener('click', exportDiagnoses);
        }
    }

    function handlePatientSelection(event) {
        const selectedPatientId = event.target.value;
        if (selectedPatientId) {
            const patient = allPatients.find(p => p.id == selectedPatientId);
            if (patient) {
                displayPatientInfo(patient);
                currentPatient = patient;
            }
        } else {
            hidePatientInfo();
            currentPatient = null;
        }
    }

    function handlePatientSearch(event) {
        const searchTerm = event.target.value.toLowerCase();
        
        if (searchTerm.length < 2) {
            hideSearchResults();
            return;
        }

        const filteredPatients = allPatients.filter(patient => 
            patient.patient_code.toLowerCase().includes(searchTerm) ||
            patient.first_name.toLowerCase().includes(searchTerm) ||
            patient.last_name.toLowerCase().includes(searchTerm) ||
            (patient.contact_number && patient.contact_number.includes(searchTerm))
        );

        displaySearchResults(filteredPatients);
    }

    function displaySearchResults(patients) {
        if (!searchResults) return;
        
        searchResults.innerHTML = '';
        
        if (patients.length === 0) {
            searchResults.innerHTML = '<div class="p-3 text-gray-500 text-center">No patients found</div>';
        } else {
            patients.slice(0, 10).forEach(patient => {
                const div = document.createElement('div');
                div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0';
                div.innerHTML = `
                    <div class="font-medium text-gray-900">${patient.first_name} ${patient.last_name}</div>
                    <div class="text-sm text-gray-600">Code: ${patient.patient_code} | Contact: ${patient.contact_number || 'N/A'}</div>
                `;
                div.addEventListener('click', () => selectPatientFromSearch(patient));
                searchResults.appendChild(div);
            });
        }
        
        showSearchResults();
    }

    function showSearchResults() {
        if (searchResults) {
            searchResults.classList.remove('hidden');
        }
    }

    function hideSearchResults() {
        if (searchResults) {
            searchResults.classList.add('hidden');
        }
    }

    function selectPatientFromSearch(patient) {
        patientSearch.value = `${patient.first_name} ${patient.last_name}`;
        hideSearchResults();
        displayPatientInfo(patient);
        currentPatient = patient;
        patientId.value = patient.id;
    }

    function handleVisitModeChange(event) {
        if (event.target.value === 'Referral') {
            referralInfo.classList.remove('hidden');
        } else {
            referralInfo.classList.add('hidden');
        }
    }

    function displayPatientInfo(patient) {
        patientInfo.classList.remove('hidden');
        patientId.value = patient.id;
        
        // Calculate age
        const birthDate = new Date(patient.birth_date);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        const actualAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate()) ? age - 1 : age;

        // Basic Information
        displayPatientName.textContent = `${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name}`;
        displayPatientAge.textContent = `${actualAge} years old`;
        displayPatientGender.textContent = patient.gender;
        displayPatientBlood.textContent = patient.blood_type || 'N/A';
        
        // Contact Information
        displayPatientContact.textContent = patient.contact_number || 'N/A';
        displayPatientAddress.textContent = patient.address || 'N/A';
        displayPatientBarangay.textContent = patient.barangay_name || 'N/A';
        displayPatientEmergency.textContent = patient.emergency_contact_name ? 
            `${patient.emergency_contact_name} (${patient.emergency_contact_number || 'N/A'})` : 'N/A';
        
        // Medical Information
        displayPatientMedicalHistory.textContent = patient.medical_history || 'None recorded';
        displayPatientAllergies.textContent = patient.allergies || 'None recorded';
        displayPatientPhilHealth.textContent = patient.is_philhealth_member == 1 ? 
            `${patient.philhealth_type || 'Member'} - ${patient.philhealth_number || 'N/A'}` : 'Not a member';
        displayPatientInsurance.textContent = patient.insurance_number || 'N/A';
        
        // Load recent visits
        loadRecentVisits(patient.id);
        
        // Show quick stats
        showQuickStats();
    }

    function hidePatientInfo() {
        patientInfo.classList.add('hidden');
        patientId.value = '';
        currentPatient = null;
        hideQuickStats();
    }

    async function loadRecentVisits(patientId) {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=records&patient_id=${patientId}&limit=5`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                const visitsHtml = result.data.map(record => `
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                        <div>
                            <div class="font-medium">${record.visit_date}</div>
                            <div class="text-xs text-gray-500">${record.chief_complaints || 'No complaints recorded'}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">${record.diagnosis || 'No diagnosis'}</div>
                            <div class="text-xs text-blue-600">${record.visit_mode || 'Walk-in'}</div>
                        </div>
                    </div>
                `).join('');
                recentVisitsList.innerHTML = visitsHtml;
            } else {
                recentVisitsList.innerHTML = '<div class="text-center py-2 text-gray-500">No recent visits found</div>';
            }
        } catch (error) {
            console.error('Error loading recent visits:', error);
            recentVisitsList.innerHTML = '<div class="text-center py-2 text-red-500">Error loading visits</div>';
        }
    }

    async function showQuickStats() {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=stats`);
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('totalPatientsCount').textContent = result.data.total_patients || 0;
                document.getElementById('todayConsultationsCount').textContent = result.data.today_consultations || 0;
                document.getElementById('newPatientsCount').textContent = result.data.new_patients || 0;
                document.getElementById('activePatientsCount').textContent = result.data.active_patients || 0;
                quickStats.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error loading quick stats:', error);
        }
    }

    function hideQuickStats() {
        if (quickStats) {
            quickStats.classList.add('hidden');
        }
    }

    async function loadPatientHistory(patientId) {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=records&patient_id=${patientId}`);
            const result = await response.json();
            
            const content = document.getElementById('patientHistoryContent');
            
            if (result.success && result.data.length > 0) {
                const historyHtml = `
                    <div class="space-y-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">Patient: ${currentPatient.first_name} ${currentPatient.last_name}</h4>
                            <p class="text-sm text-blue-600">Total Visits: ${result.data.length}</p>
                        </div>
                        
                        <div class="space-y-4">
                            ${result.data.map(record => `
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h5 class="font-semibold text-gray-800">Visit Date: ${record.visit_date}</h5>
                                            <p class="text-sm text-gray-600">Mode: ${record.visit_mode || 'Walk-in'}</p>
                                        </div>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                            ${record.clinician_name || 'Unknown Clinician'}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <strong>Chief Complaints:</strong>
                                            <p class="text-gray-700 mt-1">${record.chief_complaints || 'None recorded'}</p>
                                        </div>
                                        <div>
                                            <strong>Vital Signs:</strong>
                                            <p class="text-gray-700 mt-1">
                                                BP: ${record.bp || 'N/A'} | Temp: ${record.temperature || 'N/A'}°C<br>
                                                Height: ${record.height || 'N/A'}cm | Weight: ${record.weight || 'N/A'}kg
                                            </p>
                                        </div>
                                        <div>
                                            <strong>Diagnosis:</strong>
                                            <p class="text-gray-700 mt-1">${record.diagnosis || 'None recorded'}</p>
                                        </div>
                                        <div>
                                            <strong>Treatment:</strong>
                                            <p class="text-gray-700 mt-1">${record.treatment || 'None recorded'}</p>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
                content.innerHTML = historyHtml;
            } else {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-500 mb-4">No medical history found for this patient.</div>
                        <button onclick="closePatientHistoryModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Close
                        </button>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading patient history:', error);
            document.getElementById('patientHistoryContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="text-red-500 mb-4">Error loading patient history. Please try again.</div>
                    <button onclick="closePatientHistoryModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Close
                    </button>
                </div>
            `;
        }
    }

    async function loadPatientEditForm(patientId) {
        try {
            const response = await fetch(`${PATIENT_API}&id=${patientId}`);
            const result = await response.json();
            
            const form = document.getElementById('patientEditForm');
            
            if (result.success && result.data) {
                const patient = result.data;
                const formHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Patient Code</label>
                            <input type="text" name="patient_code" value="${patient.patient_code || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="first_name" value="${patient.first_name || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="${patient.last_name || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="text" name="contact_number" value="${patient.contact_number || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="address" value="${patient.address || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medical History</label>
                            <textarea name="medical_history" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">${patient.medical_history || ''}</textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-6">
                        <button type="button" onclick="closePatientEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Patient
                        </button>
                    </div>
                `;
                form.innerHTML = formHtml;
                
                // Add form submission handler
                form.onsubmit = handlePatientUpdate;
            } else {
                form.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-500 mb-4">Error loading patient data.</div>
                        <button onclick="closePatientEditModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Close
                        </button>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading patient edit form:', error);
            document.getElementById('patientEditForm').innerHTML = `
                <div class="text-center py-8">
                    <div class="text-red-500 mb-4">Error loading patient data. Please try again.</div>
                    <button onclick="closePatientEditModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Close
                    </button>
                </div>
            `;
        }
    }

    async function handlePatientUpdate(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());
        data.id = currentPatient.id;
        
        try {
            const response = await fetch(PATIENT_CREATE_API, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            if (result.success) {
                showAlert('Patient updated successfully!', 'success');
                closePatientEditModal();
                // Refresh patient data
                await loadPatients();
                if (currentPatient) {
                    const updatedPatient = allPatients.find(p => p.id == currentPatient.id);
                    if (updatedPatient) {
                        displayPatientInfo(updatedPatient);
                        currentPatient = updatedPatient;
                    }
                }
            } else {
                showAlert(result.message || 'Failed to update patient.', 'error');
            }
        } catch (error) {
            console.error('Error updating patient:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    async function handleInterviewSubmission(event) {
        event.preventDefault();
        
        if (!currentPatient) {
            showAlert('Please select a patient first.', 'error');
            return;
        }

        const formData = new FormData(interviewForm);
        const data = Object.fromEntries(formData.entries());
        
        // Add current timestamp if consultation time is not set
        if (!data.consultation_time) {
            data.consultation_time = new Date().toTimeString().slice(0, 5);
        }

        try {
            const response = await fetch(MEDICAL_RECORD_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                showAlert('Medical record saved successfully!', 'success');
                clearInterviewForm();
                // Switch to records tab to show the new record
                showTab('records');
            } else {
                showAlert(result.message || 'Failed to save medical record.', 'error');
            }
        } catch (error) {
            console.error('Error saving medical record:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    function clearInterviewForm() {
        interviewForm.reset();
        hidePatientInfo();
        referralInfo.classList.add('hidden');
        currentPatient = null;
    }

    async function loadMedicalRecords() {
        try {
            const patientFilter = document.getElementById('recordPatientFilter')?.value || '';
            const dateFrom = document.getElementById('recordDateFrom')?.value || '';
            const dateTo = document.getElementById('recordDateTo')?.value || '';

            let url = `${MEDICAL_RECORD_API}?resource=records`;
            if (patientFilter) url += `&patient_id=${patientFilter}`;
            if (dateFrom) url += `&date_from=${dateFrom}`;
            if (dateTo) url += `&date_to=${dateTo}`;

            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                renderMedicalRecordsTable(result.data);
                renderRecordsPagination(result.total_records || result.data.length);
            } else {
                showAlert(result.message || 'Failed to load medical records.', 'error');
            }
        } catch (error) {
            console.error('Error loading medical records:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    function renderMedicalRecordsTable(records) {
        const tbody = document.getElementById('recordsTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (records.length === 0) {
            tbody.innerHTML = `
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-file-medical text-4xl mb-2 text-gray-300"></i>
                        <p>No records found</p>
                    </div>
                </td></tr>
            `;
            return;
        }

        records.forEach(record => {
            const row = document.createElement('tr');
            row.className = 'table-row-hover';
            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900">${new Date(record.visit_date).toLocaleDateString()}</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">${record.patient_name || 'Unknown'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${record.diagnosis || record.disease_name || 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${record.treatment || 'N/A'}</td>
                <td class="px-6 py-4 text-sm font-medium">
                    <button onclick="viewRecord(${record.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editRecord(${record.id})" class="text-green-600 hover:text-green-900">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function renderRecordsPagination(totalRecords) {
        // Implementation for pagination
        const pagination = document.getElementById('recordsPagination');
        if (!pagination) return;
        
        // Simple pagination implementation
        pagination.innerHTML = `<p class="text-gray-600">Total records: ${totalRecords}</p>`;
    }

    async function loadReports() {
        try {
            // Load consultation counts
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=stats`);
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('todayConsultations').textContent = result.data.today_consultations || 0;
                document.getElementById('monthConsultations').textContent = result.data.month_consultations || 0;
            }

            // Load common diagnoses
            await loadCommonDiagnoses();
        } catch (error) {
            console.error('Error loading reports:', error);
        }
    }

    async function loadCommonDiagnoses() {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=diagnoses`);
            const result = await response.json();
            
            const diagnosesDiv = document.getElementById('commonDiagnoses');
            if (result.success && result.data.length > 0) {
                const diagnosesHtml = result.data.slice(0, 5).map((diagnosis, index) => `
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">${diagnosis.disease_name || diagnosis.diagnosis}</span>
                            <span class="text-sm text-gray-500">${diagnosis.count} cases</span>
                        </div>
                    </div>
                `).join('');
                diagnosesDiv.innerHTML = diagnosesHtml;
            } else {
                diagnosesDiv.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-sm"><p class="text-gray-500">No diagnoses data available</p></div>';
            }
        } catch (error) {
            console.error('Error loading common diagnoses:', error);
            document.getElementById('commonDiagnoses').innerHTML = '<div class="bg-white p-4 rounded-lg shadow-sm"><p class="text-red-500">Error loading diagnoses</p></div>';
        }
    }

    async function loadPatientsList() {
        try {
            const response = await fetch(PATIENT_API);
            const result = await response.json();
            
            if (result.success) {
                renderPatientsTable(result.data);
            } else {
                showAlert(result.message || 'Failed to load patients.', 'error');
            }
        } catch (error) {
            console.error('Error loading patients list:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    function renderPatientsTable(patients) {
        const tbody = document.getElementById('patientsTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (patients.length === 0) {
            tbody.innerHTML = `
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-users text-4xl mb-2 text-gray-300"></i>
                        <p>No patients found</p>
                    </div>
                </td></tr>
            `;
            return;
        }

        patients.forEach(patient => {
            // Calculate age
            const birthDate = new Date(patient.birth_date);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            const actualAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate()) ? age - 1 : age;

            const row = document.createElement('tr');
            row.className = 'table-row-hover';
            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">${patient.patient_code}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${actualAge} / ${patient.gender}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${patient.contact_number || 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${patient.barangay_name || 'N/A'}</td>
                <td class="px-6 py-4 text-sm font-medium">
                    <button onclick="viewPatientFromList(${patient.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editPatientFromList(${patient.id})" class="text-green-600 hover:text-green-900 mr-3">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deletePatient(${patient.id})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function handlePatientListSearch(event) {
        const searchTerm = event.target.value.toLowerCase();
        const filteredPatients = allPatients.filter(patient => 
            patient.patient_code.toLowerCase().includes(searchTerm) ||
            patient.first_name.toLowerCase().includes(searchTerm) ||
            patient.last_name.toLowerCase().includes(searchTerm) ||
            (patient.barangay_name && patient.barangay_name.toLowerCase().includes(searchTerm))
        );
        renderPatientsTable(filteredPatients);
    }

    // Global functions for patient list actions
    window.viewPatientFromList = function(patientId) {
        const patient = allPatients.find(p => p.id == patientId);
        if (patient) {
            showTab('patient');
            displayPatientInfo(patient);
            currentPatient = patient;
        }
    };

    window.editPatientFromList = function(patientId) {
        const patient = allPatients.find(p => p.id == patientId);
        if (patient) {
            currentPatient = patient;
            showPatientEdit();
        }
    };

    window.deletePatient = function(patientId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deletePatientConfirmed(patientId);
            }
        });
    };

    async function deletePatientConfirmed(patientId) {
        try {
            const response = await fetch(`${PATIENT_CREATE_API}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: patientId })
            });
            
            const result = await response.json();
            if (result.success) {
                showAlert('Patient deleted successfully!', 'success');
                await loadPatients();
                loadPatientsList();
            } else {
                showAlert(result.message || 'Failed to delete patient.', 'error');
            }
        } catch (error) {
            console.error('Error deleting patient:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    // Export functions
    async function exportConsultations() {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=export&type=consultations`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `consultations_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showAlert('Consultations exported successfully!', 'success');
        } catch (error) {
            console.error('Error exporting consultations:', error);
            showAlert('Failed to export consultations.', 'error');
        }
    }

    async function exportPatients() {
        try {
            const response = await fetch(`${PATIENT_API}&export=1`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `patients_${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showAlert('Patients exported successfully!', 'success');
        } catch (error) {
            console.error('Error exporting patients:', error);
            showAlert('Failed to export patients.', 'error');
        }
    }

    async function exportDiagnoses() {
        try {
            const response = await fetch(`${MEDICAL_RECORD_API}?resource=export&type=diagnoses`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `diagnoses_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showAlert('Diagnoses exported successfully!', 'success');
        } catch (error) {
            console.error('Error exporting diagnoses:', error);
            showAlert('Failed to export diagnoses.', 'error');
        }
    }

    function showAddPatientModal() {
        showTab('addpatient');
    }

    async function handleAddPatientSubmission(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Basic required checks align with API
        const required = ['patient_code','first_name','last_name','birth_date','gender'];
        for (const f of required) {
            if (!data[f] || String(data[f]).trim() === '') {
                showAlert(`Field '${f}' is required`, 'error');
                return;
            }
        }

        // Normalize optional numeric/boolean-like fields
        const optionalInts = ['barangay_id','mother_id','father_id','spouse_id'];
        optionalInts.forEach(k => { if (data[k] === '') data[k] = null; });
        const optionalBools = ['dswd_nhts','is_4ps_member','is_philhealth_member','pcb_member'];
        optionalBools.forEach(k => { if (data[k] === '') data[k] = 0; });

        try {
            const res = await fetch(PATIENT_CREATE_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                showAlert('Patient created successfully', 'success');
                form.reset();
                await loadPatients();
                showTab('patients');
            } else {
                showAlert(result.message || 'Failed to create patient', 'error');
            }
        } catch (e) {
            console.error('Error creating patient', e);
            showAlert('Network error. Please try again.', 'error');
        }
    }

    function showAlert(message, type = 'info') {
        if (window.Swal) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
                text: message,
                confirmButtonText: 'OK'
            });
        } else {
            alert(message);
        }
    }

    // Global functions for table actions
    window.viewRecord = function(recordId) {
        showAlert(`Viewing record ${recordId} - functionality to be implemented.`, 'info');
    };

    window.editRecord = function(recordId) {
        showAlert(`Editing record ${recordId} - functionality to be implemented.`, 'info');
    };

    // Search records button
    const searchRecordsBtn = document.getElementById('searchRecordsBtn');
    if (searchRecordsBtn) {
        searchRecordsBtn.addEventListener('click', loadMedicalRecords);
    }

    // Set current time for consultation
    if (consultationTime) {
        const now = new Date();
        consultationTime.value = now.toTimeString().slice(0, 5);
    }
});
