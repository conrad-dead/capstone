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
    <title>Clinician Dashboard - Patient Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        input:focus, select:focus, textarea:focus { outline: none; box-shadow: 0 0 0 3px rgba(66,153,225,.5); border-color: #4299e1; }
        .tab-button { transition: all 0.3s ease; }
        .tab-button.active { background-color: #3b82f6; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="flex flex-col h-screen bg-gray-800 text-white w-64 text-lg">
            <div class="mb-8 py-4">
                <h1 class="text-3xl font-extrabold text-white tracking-wide leading-none px-6 py-4 border-b border-gray-700">RHU GAMU</h1>
                <p class="px-6 text-sm text-gray-300">Welcome, <?php echo htmlspecialchars($clinician_name); ?></p>
            </div>
            <nav class="flex-1 overflow-y-auto">
                <div class="px-2 py-4 space-y-1">
                    <button id="tabPatientBtn" class="tab-button active w-full text-left py-2.5 px-4 rounded text-white bg-blue-600">Patient Interview</button>
                    <button id="tabRecordsBtn" class="tab-button w-full text-left py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Medical Records</button>
                    <button id="tabPatientsBtn" class="tab-button w-full text-left py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Patient List</button>
                    <button id="tabAddPatientBtn" class="tab-button w-full text-left py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Add Patient</button>
                    <button id="tabReportsBtn" class="tab-button w-full text-left py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Reports</button>
                </div>
            </nav>
            <div class="px-2 border-t border-gray-700">
                <a href="../logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Clinician Dashboard</h1>
                <p class="text-gray-600">Manage patient consultations and medical records</p>
            </header>

            <main class="space-y-8">
                <!-- Patient Interview Tab -->
                <section id="tabPatient" class="bg-white rounded-lg shadow-xl p-8 tab-content active">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Patient Interview & Consultation</h2>
                    
                    <!-- Patient Search/Selection -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3">Select Patient</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search Patient</label>
                                <input type="text" id="patientSearch" placeholder="Enter patient code or name..." class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Or Select from List</label>
                                <select id="patientSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Choose patient...</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button id="newPatientBtn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">New Patient</button>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Form -->
                    <form id="interviewForm" class="space-y-6">
                        <input type="hidden" id="patientId" name="patient_id">
                        <input type="hidden" id="clinicianId" name="clinician_id" value="<?php echo $clinician_id; ?>">
                        
                        <!-- Patient Info Display -->
                        <div id="patientInfo" class="hidden p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Patient Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div><strong>Name:</strong> <span id="displayPatientName">-</span></div>
                                <div><strong>Age:</strong> <span id="displayPatientAge">-</span></div>
                                <div><strong>Gender:</strong> <span id="displayPatientGender">-</span></div>
                                <div><strong>Contact:</strong> <span id="displayPatientContact">-</span></div>
                                <div><strong>Address:</strong> <span id="displayPatientAddress">-</span></div>
                                <div><strong>Blood Type:</strong> <span id="displayPatientBlood">-</span></div>
                            </div>
                        </div>

                        <!-- Chief Complaints -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Chief Complaints</label>
                            <textarea id="chiefComplaints" name="chief_complaints" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Describe the patient's main symptoms and concerns..."></textarea>
                        </div>

                        <!-- Vital Signs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                                <input type="text" id="bloodPressure" name="bp" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., 120/80">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                                <input type="number" id="temperature" name="temperature" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="36.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                                <input type="number" id="height" name="height" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="165.0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                                <input type="number" id="weight" name="weight" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="60.0">
                            </div>
                        </div>

                        <!-- Diagnosis & Treatment -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis</label>
                                <select id="diagnosisSelect" name="disease_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select diagnosis...</option>
                                </select>
                                <textarea id="diagnosisNotes" name="diagnosis" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md mt-2" placeholder="Additional diagnosis notes..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan</label>
                                <textarea id="treatment" name="treatment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Describe the treatment plan, medications, and follow-up..."></textarea>
                            </div>
                        </div>

                        <!-- Visit Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Visit Mode</label>
                                <select id="visitMode" name="visit_mode" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="Walk-in">Walk-in</option>
                                    <option value="Visited">Home Visit</option>
                                    <option value="Referral">Referral</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                <select id="barangaySelect" name="barangay_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select barangay...</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Consultation Time</label>
                                <input type="time" id="consultationTime" name="consultation_time" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Referral Information -->
                        <div id="referralInfo" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Referred From</label>
                                    <input type="text" id="referredFrom" name="referred_from" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Referring facility/doctor">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Referred To</label>
                                    <input type="text" id="referredTo" name="referred_to" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Receiving facility/doctor">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex space-x-4 pt-4">
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700 transition">Save Medical Record</button>
                            <button type="button" id="clearFormBtn" class="px-6 py-3 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition">Clear Form</button>
                        </div>
                    </form>
                </section>

                <!-- Medical Records Tab -->
                <section id="tabRecords" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Medical Records Management</h2>
                    
                    <!-- Search Filters -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                                <select id="recordPatientFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">All Patients</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                <input type="date" id="recordDateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                <input type="date" id="recordDateTo" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div class="flex items-end">
                                <button id="searchRecordsBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Search</button>
                            </div>
                        </div>
                    </div>

                    <!-- Records Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recordsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="recordsPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                </section>

                <!-- Patient List Tab -->
                <section id="tabPatients" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Patient Directory</h2>
                    
                    <!-- Patient Search -->
                    <div class="mb-6">
                        <div class="flex space-x-4">
                            <input type="text" id="patientListSearch" placeholder="Search patients by name, code, or barangay..." class="flex-1 px-4 py-2 border border-gray-300 rounded-md">
                            <button id="addPatientBtn" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Add New Patient</button>
                        </div>
                    </div>

                    <!-- Patients Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age/Gender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barangay</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading patients...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="patientsPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                </section>

                <!-- Add Patient Tab -->
                <section id="tabAddPatient" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add New Patient</h2>
                    <form id="addPatientForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Patient Code *</label>
                                <input type="text" id="ap_patient_code" name="patient_code" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., P-0001" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" id="ap_first_name" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <input type="text" id="ap_middle_name" name="middle_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" id="ap_last_name" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Birth Date *</label>
                                <input type="date" id="ap_birth_date" name="birth_date" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                                <select id="ap_gender" name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
                                <select id="ap_marital_status" name="marital_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                                <input type="text" id="ap_suffix" name="suffix" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Type</label>
                                <select id="ap_blood_type" name="blood_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <input type="text" id="ap_address" name="address" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                <select id="ap_barangay_id" name="barangay_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select barangay</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Birth Place</label>
                                <input type="text" id="ap_birth_place" name="birth_place" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="text" id="ap_contact_number" name="contact_number" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
                                <input type="text" id="ap_emergency_contact_name" name="emergency_contact_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Number</label>
                                <input type="text" id="ap_emergency_contact_number" name="emergency_contact_number" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                                <input type="text" id="ap_occupation" name="occupation" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Income Level</label>
                                <select id="ap_income_level" name="income_level" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medical History</label>
                                <textarea id="ap_medical_history" name="medical_history" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mother ID</label>
                                <input type="number" id="ap_mother_id" name="mother_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Father ID</label>
                                <input type="number" id="ap_father_id" name="father_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Spouse ID</label>
                                <input type="number" id="ap_spouse_id" name="spouse_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Maiden Name</label>
                                <input type="text" id="ap_maiden_name" name="maiden_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">DSWD NHTS</label>
                                <select id="ap_dswd_nhts" name="dswd_nhts" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">4Ps Member</label>
                                <select id="ap_is_4ps_member" name="is_4ps_member" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                                <select id="ap_education_level" name="education_level" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="None">None</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="High School">High School</option>
                                    <option value="Vocational">Vocational</option>
                                    <option value="College">College</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                                <select id="ap_employment_status" name="employment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="Student">Student</option>
                                    <option value="Employed">Employed</option>
                                    <option value="Retired">Retired</option>
                                    <option value="None">None</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Family Role</label>
                                <select id="ap_family_role" name="family_role" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Son">Son</option>
                                    <option value="Daughter">Daughter</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PhilHealth Member</label>
                                <select id="ap_is_philhealth_member" name="is_philhealth_member" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PhilHealth Type</label>
                                <select id="ap_philhealth_type" name="philhealth_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    <option value="FE – Private">FE – Private</option>
                                    <option value="FE – Government">FE – Government</option>
                                    <option value="IE">IE</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PhilHealth Number</label>
                                <input type="text" id="ap_philhealth_number" name="philhealth_number" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Insurance Number</label>
                                <input type="text" id="ap_insurance_number" name="insurance_number" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PCB Member</label>
                                <select id="ap_pcb_member" name="pcb_member" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition">Save Patient</button>
                        </div>
                    </form>
                </section>

                <!-- Reports Tab -->
                <section id="tabReports" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Clinical Reports</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Patient Consultations</h3>
                            <div class="space-y-4">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-semibold text-blue-800">Today's Consultations</h4>
                                    <p class="text-2xl font-bold text-blue-600" id="todayConsultations">0</p>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <h4 class="font-semibold text-green-800">This Month</h4>
                                    <p class="text-2xl font-bold text-green-600" id="monthConsultations">0</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Common Diagnoses</h3>
                            <div id="commonDiagnoses" class="space-y-2">
                                <p class="text-gray-500">Loading...</p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="../js/clinician_dashboard.js"></script>
</body>
</html>