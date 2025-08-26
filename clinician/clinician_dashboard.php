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
    <title>Clinician Dashboard - RHU GAMU</title>
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
        
        /* Tab styles */
        .tab-button {
            transition: all 0.3s ease;
            position: relative;
        }
        .tab-button.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        .tab-button:hover:not(.active) {
            background-color: #f1f5f9;
            transform: translateY(-1px);
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
                        <i class="fas fa-user-md text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">RHU GAMU</h1>
                        <p class="text-xs text-blue-200">Clinical System</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-blue-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm"><?php echo htmlspecialchars($clinician_name); ?></p>
                            <p class="text-xs text-blue-200"><?php echo ucfirst($roleName); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-2">
                    <button id="tabPatientBtn" class="tab-button active w-full flex items-center space-x-3 p-3 rounded-lg text-white shadow-lg">
                        <i class="fas fa-stethoscope w-5"></i>
                        <span class="text-sm font-medium">Patient Interview</span>
                    </button>
                    <button id="tabRecordsBtn" class="tab-button w-full flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-file-medical w-5"></i>
                        <span class="text-sm font-medium">Medical Records</span>
                    </button>
                    <button id="tabPatientsBtn" class="tab-button w-full flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-users w-5"></i>
                        <span class="text-sm font-medium">Patient List</span>
                    </button>
                    <button id="tabAddPatientBtn" class="tab-button w-full flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-user-plus w-5"></i>
                        <span class="text-sm font-medium">Add Patient</span>
                    </button>
                    <a href="medicine_distribution.php" class="w-full flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-pills w-5"></i>
                        <span class="text-sm font-medium">Medicine Distribution</span>
                    </a>
                    <button id="tabReportsBtn" class="tab-button w-full flex items-center space-x-3 p-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="text-sm font-medium">Reports</span>
                    </button>
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
                        <h1 class="text-2xl font-bold text-gray-900">Clinician Dashboard</h1>
                        <p class="text-gray-600">Manage patient consultations and medical records</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900" id="lastUpdated">Just now</p>
                        </div>
                        <button onclick="refreshDashboard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 overflow-y-auto h-full">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Patients</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalPatientsCount">0</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Consultations</p>
                                <p class="text-2xl font-bold text-gray-900" id="todayConsultationsCount">0</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-success rounded-lg flex items-center justify-center">
                                <i class="fas fa-stethoscope text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">New Patients</p>
                                <p class="text-2xl font-bold text-gray-900" id="newPatientsCount">0</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-warning rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-plus text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Active Patients</p>
                                <p class="text-2xl font-bold text-gray-900" id="activePatientsCount">0</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-info rounded-lg flex items-center justify-center">
                                <i class="fas fa-heartbeat text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Interview Tab -->
                <section id="tabPatient" class="bg-white rounded-xl shadow-sm p-8 tab-content active animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Patient Interview & Consultation</h2>
                        <div class="flex items-center space-x-2">
                            <span class="status-dot bg-green-500"></span>
                            <span class="text-sm text-gray-600">Ready for consultation</span>
                        </div>
                    </div>
                    
                    <!-- Patient Search/Selection -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border border-blue-100">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-search text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Select Patient</h3>
                                <p class="text-sm text-gray-600">Search or select a patient to begin consultation</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                                <div class="relative">
                                    <input type="text" id="patientSearch" placeholder="Enter patient code, name, or contact..." 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <div id="searchResults" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden">
                                        <!-- Search results will be populated here -->
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Or Select from List</label>
                                <select id="patientSelect" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">Choose patient...</option>
                                </select>
                            </div>
                            <div class="flex items-end space-x-3">
                                <button id="newPatientBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                    <i class="fas fa-plus"></i>
                                    <span>New Patient</span>
                                </button>
                                <button id="refreshPatientsBtn" class="px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Form -->
                    <form id="interviewForm" class="space-y-6">
                        <input type="hidden" id="patientId" name="patient_id">
                        <input type="hidden" id="clinicianId" name="clinician_id" value="<?php echo $clinician_id; ?>">
                        
                        <!-- Patient Info Display -->
                        <div id="patientInfo" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border border-blue-100 animate-fade-in-up">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-user-injured text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Patient Information</h3>
                                        <p class="text-sm text-gray-600">Complete patient details and medical history</p>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <button id="viewPatientHistoryBtn" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                        <i class="fas fa-history"></i>
                                        <span>View History</span>
                                    </button>
                                    <button id="editPatientBtn" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit Patient</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Basic Information</h4>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">Name:</span> <span id="displayPatientName" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Age:</span> <span id="displayPatientAge" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Gender:</span> <span id="displayPatientGender" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Blood Type:</span> <span id="displayPatientBlood" class="font-medium">-</span></div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-phone text-blue-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Contact Information</h4>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">Contact:</span> <span id="displayPatientContact" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Address:</span> <span id="displayPatientAddress" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Barangay:</span> <span id="displayPatientBarangay" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Emergency:</span> <span id="displayPatientEmergency" class="font-medium">-</span></div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-heartbeat text-purple-600"></i>
                                        </div>
                                        <h4 class="font-semibold text-gray-900">Medical Information</h4>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">Medical History:</span> <span id="displayPatientMedicalHistory" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Allergies:</span> <span id="displayPatientAllergies" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">PhilHealth:</span> <span id="displayPatientPhilHealth" class="font-medium">-</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Insurance:</span> <span id="displayPatientInsurance" class="font-medium">-</span></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Visits Summary -->
                            <div id="recentVisits" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                <div class="flex items-center space-x-2 mb-4">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-calendar-check text-orange-600"></i>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Recent Visits</h4>
                                </div>
                                <div id="recentVisitsList" class="text-sm text-gray-600">
                                    <div class="text-center py-4 text-gray-500">
                                        <i class="fas fa-calendar-times text-2xl mb-2"></i>
                                        <p>No recent visits found</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chief Complaints -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Chief Complaints</h4>
                            </div>
                            <textarea id="chiefComplaints" name="chief_complaints" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                      placeholder="Describe the patient's main symptoms and concerns..."></textarea>
                        </div>

                        <!-- Vital Signs -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-green-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Vital Signs</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                    <input type="text" id="bloodPressure" name="bp" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="e.g., 120/80">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Temperature (°C)</label>
                                    <input type="number" id="temperature" name="temperature" step="0.1" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="36.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                                    <input type="number" id="height" name="height" step="0.1" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="165.0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                    <input type="number" id="weight" name="weight" step="0.1" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="60.0">
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis & Treatment -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-2 mb-4">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-stethoscope text-purple-600"></i>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Diagnosis</h4>
                                </div>
                                <select id="diagnosisSelect" name="disease_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 mb-4">
                                    <option value="">Select diagnosis...</option>
                                </select>
                                <textarea id="diagnosisNotes" name="diagnosis" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                          placeholder="Additional diagnosis notes..."></textarea>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="flex items-center space-x-2 mb-4">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-pills text-blue-600"></i>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Treatment Plan</h4>
                                </div>
                                <textarea id="treatment" name="treatment" rows="6" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                          placeholder="Describe the treatment plan, medications, and follow-up..."></textarea>
                            </div>
                        </div>

                        <!-- Visit Details -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-orange-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Visit Details</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Visit Mode</label>
                                    <select id="visitMode" name="visit_mode" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        <option value="Walk-in">Walk-in</option>
                                        <option value="Visited">Home Visit</option>
                                        <option value="Referral">Referral</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                                    <select id="barangaySelect" name="barangay_id" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        <option value="">Select barangay...</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Consultation Time</label>
                                    <input type="time" id="consultationTime" name="consultation_time" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                </div>
                            </div>
                        </div>

                        <!-- Referral Information -->
                        <div id="referralInfo" class="hidden bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-exchange-alt text-yellow-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">Referral Information</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Referred From</label>
                                    <input type="text" id="referredFrom" name="referred_from" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="Referring facility/doctor">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Referred To</label>
                                    <input type="text" id="referredTo" name="referred_to" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                           placeholder="Receiving facility/doctor">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex space-x-4 pt-6">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-save"></i>
                                <span>Save Medical Record</span>
                            </button>
                            <button type="button" id="clearFormBtn" class="px-8 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-lg font-semibold hover:from-gray-600 hover:to-gray-700 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-eraser"></i>
                                <span>Clear Form</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Medical Records Tab -->
                <section id="tabRecords" class="bg-white rounded-xl shadow-sm p-8 tab-content hidden animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Medical Records Management</h2>
                        <div class="flex items-center space-x-2">
                            <span class="status-dot bg-blue-500"></span>
                            <span class="text-sm text-gray-600">Records management</span>
                        </div>
                    </div>
                    
                    <!-- Search Filters -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border border-blue-100">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-search text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Search Records</h3>
                                <p class="text-sm text-gray-600">Filter medical records by patient and date range</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
                                <select id="recordPatientFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">All Patients</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                                <input type="date" id="recordDateFrom" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                                <input type="date" id="recordDateTo" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div class="flex items-end">
                                <button id="searchRecordsBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                    <i class="fas fa-search"></i>
                                    <span>Search</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Records Table -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosis</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recordsTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-file-medical text-4xl mb-2 text-gray-300"></i>
                                            <p>No records found</p>
                                        </div>
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="recordsPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                </section>

                <!-- Patient List Tab -->
                <section id="tabPatients" class="bg-white rounded-xl shadow-sm p-8 tab-content hidden animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Patient Directory</h2>
                        <div class="flex items-center space-x-2">
                            <span class="status-dot bg-green-500"></span>
                            <span class="text-sm text-gray-600">Patient management</span>
                        </div>
                    </div>
                    
                    <!-- Patient Search -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8 border border-green-100">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Patient Search</h3>
                                <p class="text-sm text-gray-600">Search and manage patient records</p>
                            </div>
                        </div>
                        
                        <div class="flex space-x-4">
                            <input type="text" id="patientListSearch" placeholder="Search patients by name, code, or barangay..." 
                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                            <button id="addPatientBtn" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-plus"></i>
                                <span>Add New Patient</span>
                            </button>
                        </div>
                    </div>

                    <!-- Patients Table -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Code</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age/Gender</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barangay</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="patientsTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users text-4xl mb-2 text-gray-300"></i>
                                            <p>Loading patients...</p>
                                        </div>
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="patientsPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                </section>

                <!-- Add Patient Tab -->
                <section id="tabAddPatient" class="bg-white rounded-xl shadow-sm p-8 tab-content hidden animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Add New Patient</h2>
                        <div class="flex items-center space-x-2">
                            <span class="status-dot bg-purple-500"></span>
                            <span class="text-sm text-gray-600">Patient registration</span>
                        </div>
                    </div>
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
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
                                <textarea id="ap_allergies" name="allergies" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="List any known allergies..."></textarea>
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
                        <div class="pt-6">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-save"></i>
                                <span>Save Patient</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Reports Tab -->
                <section id="tabReports" class="bg-white rounded-xl shadow-sm p-8 tab-content hidden animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Clinical Reports</h2>
                        <div class="flex items-center space-x-2">
                            <span class="status-dot bg-orange-500"></span>
                            <span class="text-sm text-gray-600">Analytics & reports</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Patient Consultations</h3>
                            </div>
                            <div class="space-y-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h4 class="font-semibold text-blue-800">Today's Consultations</h4>
                                    <p class="text-2xl font-bold text-blue-600" id="todayConsultations">0</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h4 class="font-semibold text-green-800">This Month</h4>
                                    <p class="text-2xl font-bold text-green-600" id="monthConsultations">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-stethoscope text-white"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Common Diagnoses</h3>
                            </div>
                            <div id="commonDiagnoses" class="space-y-2">
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <p class="text-gray-500">Loading diagnoses...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Section -->
                    <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-download text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Export Reports</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <button id="exportConsultationsBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-file-pdf"></i>
                                <span>Export Consultations</span>
                            </button>
                            <button id="exportPatientsBtn" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Patients</span>
                            </button>
                            <button id="exportDiagnosesBtn" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                                <i class="fas fa-chart-bar"></i>
                                <span>Export Diagnoses</span>
                            </button>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Patient History Modal -->
    <div id="patientHistoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">Patient Medical History</h3>
                    <button onclick="closePatientHistoryModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                    <div id="patientHistoryContent">
                        <!-- Patient history content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Edit Modal -->
    <div id="patientEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">Edit Patient Information</h3>
                    <button onclick="closePatientEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                    <form id="patientEditForm" class="space-y-6">
                        <!-- Patient edit form will be loaded here -->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <script src="../js/clinician_dashboard.js"></script>
</body>
</html>