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
    
    // Patient info display elements
    const displayPatientName = document.getElementById('displayPatientName');
    const displayPatientAge = document.getElementById('displayPatientAge');
    const displayPatientGender = document.getElementById('displayPatientGender');
    const displayPatientContact = document.getElementById('displayPatientContact');
    const displayPatientAddress = document.getElementById('displayPatientAddress');
    const displayPatientBlood = document.getElementById('displayPatientBlood');

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
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('text-gray-300', 'hover:bg-gray-700');
            }
        });
        
        // Show selected tab and activate button
        const tabIndex = tabs.findIndex(tab => tab.toLowerCase() === tabName);
        if (tabIndex !== -1) {
            if (tabContents[tabIndex]) tabContents[tabIndex].classList.remove('hidden');
            if (tabButtons[tabIndex]) {
                tabButtons[tabIndex].classList.add('active', 'bg-blue-600', 'text-white');
                tabButtons[tabIndex].classList.remove('text-gray-300', 'hover:bg-gray-700');
            }
        }

        // Load tab-specific data
        switch(tabName) {
            case 'patients':
                loadPatients();
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
        if (searchTerm.length < 2) return;

        const filteredPatients = allPatients.filter(patient => 
            patient.patient_code.toLowerCase().includes(searchTerm) ||
            patient.first_name.toLowerCase().includes(searchTerm) ||
            patient.last_name.toLowerCase().includes(searchTerm)
        );

        // Update patient select with filtered results
        patientSelect.innerHTML = '<option value="">Choose patient...</option>';
        filteredPatients.forEach(patient => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = `${patient.patient_code} - ${patient.first_name} ${patient.last_name}`;
            patientSelect.appendChild(option);
        });
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

        displayPatientName.textContent = `${patient.first_name} ${patient.middle_name ? patient.middle_name + ' ' : ''}${patient.last_name}`;
        displayPatientAge.textContent = `${actualAge} years old`;
        displayPatientGender.textContent = patient.gender;
        displayPatientContact.textContent = patient.contact_number || 'N/A';
        displayPatientAddress.textContent = patient.address || 'N/A';
        displayPatientBlood.textContent = patient.blood_type || 'N/A';
    }

    function hidePatientInfo() {
        patientInfo.classList.add('hidden');
        patientId.value = '';
        currentPatient = null;
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
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
            return;
        }

        records.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900">${new Date(record.visit_date).toLocaleDateString()}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${record.patient_name || 'Unknown'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${record.diagnosis || record.disease_name || 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${record.treatment || 'N/A'}</td>
                <td class="px-6 py-4 text-sm font-medium">
                    <button onclick="viewRecord(${record.id})" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                    <button onclick="editRecord(${record.id})" class="text-green-600 hover:text-green-900">Edit</button>
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
        } catch (error) {
            console.error('Error loading reports:', error);
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
