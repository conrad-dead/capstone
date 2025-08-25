<?php
session_start();

// Debug session info
error_log("Patient API - Session debug - user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Patient API - Session debug - username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("Patient API - Session debug - role_name: " . ($_SESSION['user_role_name'] ?? 'NOT SET'));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../db/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Patient API - Unauthorized access attempt");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please log in']);
    exit();
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$resource = $_GET['resource'] ?? '';

switch ($resource) {
    case 'patients':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getPatients();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    case 'patient':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getPatient();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            createPatient();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            updatePatient();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deletePatient();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid resource']);
        break;
}

function getPatients() {
    global $conn;
    
    try {
        $sql = 'SELECT 
                    p.id, 
                    p.patient_code, 
                    p.first_name, 
                    p.middle_name, 
                    p.last_name, 
                    p.birth_date, 
                    p.gender, 
                    p.contact_number,
                    p.address,
                    b.barangay_name
                FROM patients_table p
                LEFT JOIN barangay_table b ON p.barangay_id = b.id
                ORDER BY p.last_name, p.first_name ASC';
        
        $result = $conn->query($sql);
        
        if ($result) {
            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $patients]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch patients: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getPatient() {
    global $conn;
    
    $patient_id = $_GET['id'] ?? null;
    if (!$patient_id) {
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('SELECT 
                                    p.id, 
                                    p.patient_code, 
                                    p.first_name, 
                                    p.middle_name, 
                                    p.last_name, 
                                    p.birth_date, 
                                    p.gender, 
                                    p.marital_status,
                                    p.blood_type,
                                    p.contact_number,
                                    p.address,
                                    p.emergency_contact_name,
                                    p.emergency_contact_number,
                                    p.occupation,
                                    p.registration_date,
                                    b.barangay_name
                                FROM patients_table p
                                LEFT JOIN barangay_table b ON p.barangay_id = b.id
                                WHERE p.id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $patient = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $patient]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Patient not found']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function createPatient() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['patient_code', 'first_name', 'last_name', 'birth_date', 'gender'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $stmt = $conn->prepare('INSERT INTO patients_table 
                                (patient_code, first_name, middle_name, last_name, birth_date, gender, 
                                 marital_status, blood_type, address, barangay_id, contact_number, 
                                 emergency_contact_name, emergency_contact_number, occupation, registration_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        
        if ($stmt) {
            $stmt->bind_param('ssssssssssssss', 
                $input['patient_code'],
                $input['first_name'],
                $input['middle_name'] ?? '',
                $input['last_name'],
                $input['birth_date'],
                $input['gender'],
                $input['marital_status'] ?? '',
                $input['blood_type'] ?? '',
                $input['address'] ?? '',
                $input['barangay_id'] ?? null,
                $input['contact_number'] ?? '',
                $input['emergency_contact_name'] ?? '',
                $input['emergency_contact_number'] ?? '',
                $input['occupation'] ?? ''
            );
            
            if ($stmt->execute()) {
                $patient_id = $conn->insert_id;
                echo json_encode(['success' => true, 'message' => 'Patient created successfully', 'data' => ['id' => $patient_id]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create patient: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updatePatient() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $patient_id = $_GET['id'] ?? null;
    
    if (!$patient_id || !$input) {
        echo json_encode(['success' => false, 'message' => 'Patient ID and update data are required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('UPDATE patients_table SET 
                                patient_code = ?, first_name = ?, middle_name = ?, last_name = ?, 
                                birth_date = ?, gender = ?, marital_status = ?, blood_type = ?, 
                                address = ?, barangay_id = ?, contact_number = ?, 
                                emergency_contact_name = ?, emergency_contact_number = ?, occupation = ?
                                WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('ssssssssssssssi', 
                $input['patient_code'],
                $input['first_name'],
                $input['middle_name'] ?? '',
                $input['last_name'],
                $input['birth_date'],
                $input['gender'],
                $input['marital_status'] ?? '',
                $input['blood_type'] ?? '',
                $input['address'] ?? '',
                $input['barangay_id'] ?? null,
                $input['contact_number'] ?? '',
                $input['emergency_contact_name'] ?? '',
                $input['emergency_contact_number'] ?? '',
                $input['occupation'] ?? '',
                $patient_id
            );
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update patient: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deletePatient() {
    global $conn;
    
    $patient_id = $_GET['id'] ?? null;
    if (!$patient_id) {
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('DELETE FROM patients_table WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $patient_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete patient: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
