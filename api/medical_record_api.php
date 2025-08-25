<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../db/conn.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$resource = $_GET['resource'] ?? '';

switch ($resource) {
    case 'records':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getMedicalRecords();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    case 'record':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getMedicalRecord();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            createMedicalRecord();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            updateMedicalRecord();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deleteMedicalRecord();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    case 'stats':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getMedicalRecordStats();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid resource']);
        break;
}

function getMedicalRecords() {
    global $conn;
    
    $patient_id = $_GET['patient_id'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_from'] ?? '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    
    try {
        $where_conditions = [];
        $params = [];
        $param_types = '';
        
        if ($patient_id) {
            $where_conditions[] = 'mr.patient_id = ?';
            $params[] = $patient_id;
            $param_types .= 'i';
        }
        
        if ($date_from) {
            $where_conditions[] = 'mr.visit_date >= ?';
            $params[] = $date_from;
            $param_types .= 's';
        }
        
        if ($date_to) {
            $where_conditions[] = 'mr.visit_date <= ?';
            $params[] = $date_to;
            $param_types .= 's';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Count total records
        $count_sql = "SELECT COUNT(*) as total FROM medical_record mr $where_clause";
        $count_stmt = $conn->prepare($count_sql);
        
        if ($count_stmt) {
            if (!empty($params)) {
                $count_stmt->bind_param($param_types, ...$params);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_records = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        } else {
            $total_records = 0;
        }
        
        // Get records with pagination
        $sql = "SELECT 
                    mr.id, 
                    mr.patient_id, 
                    mr.clinician_id, 
                    mr.disease_id, 
                    mr.diagnosis, 
                    mr.treatment, 
                    mr.visit_date, 
                    mr.barangay_id, 
                    mr.consultation_time, 
                    mr.visit_mode, 
                    mr.referred_from, 
                    mr.referred_to, 
                    mr.chief_complaints, 
                    mr.bp, 
                    mr.temperature, 
                    mr.height, 
                    mr.weight,
                    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                    p.patient_code,
                    d.name AS disease_name,
                    c.first_name AS clinician_first_name,
                    c.last_name AS clinician_last_name
                FROM medical_record mr
                LEFT JOIN patients_table p ON mr.patient_id = p.id
                LEFT JOIN disease d ON mr.disease_id = d.id
                LEFT JOIN clinicians_table c ON mr.clinician_id = c.id
                $where_clause
                ORDER BY mr.visit_date DESC, mr.consultation_time DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            if (!empty($params)) {
                $params[] = $limit;
                $params[] = $offset;
                $param_types .= 'ii';
                $stmt->bind_param($param_types, ...$params);
            } else {
                $stmt->bind_param('ii', $limit, $offset);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $records = [];
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
            
            $stmt->close();
            
            echo json_encode([
                'success' => true, 
                'data' => $records, 
                'total_records' => $total_records,
                'page' => $page,
                'limit' => $limit
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getMedicalRecord() {
    global $conn;
    
    $record_id = $_GET['id'] ?? null;
    if (!$record_id) {
        echo json_encode(['success' => false, 'message' => 'Record ID is required']);
        return;
    }
    
    try {
        $sql = "SELECT 
                    mr.*,
                    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                    p.patient_code,
                    d.name AS disease_name,
                    CONCAT(c.first_name, ' ', c.last_name) AS clinician_name
                FROM medical_record mr
                LEFT JOIN patients_table p ON mr.patient_id = p.id
                LEFT JOIN disease d ON mr.disease_id = d.id
                LEFT JOIN clinicians_table c ON mr.clinician_id = c.id
                WHERE mr.id = ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $record_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $record = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $record]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Medical record not found']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function createMedicalRecord() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['patient_id', 'clinician_id', 'visit_date'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $conn->begin_transaction();
        
        // Insert medical record
        $sql = "INSERT INTO medical_record 
                (patient_id, clinician_id, disease_id, diagnosis, treatment, visit_date, 
                 barangay_id, consultation_time, visit_mode, referred_from, referred_to, 
                 chief_complaints, bp, temperature, height, weight) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('iiissssssssssddd', 
                $input['patient_id'],
                $input['clinician_id'],
                $input['disease_id'] ?? null,
                $input['diagnosis'] ?? null,
                $input['treatment'] ?? null,
                $input['visit_date'],
                $input['barangay_id'] ?? null,
                $input['consultation_time'] ?? null,
                $input['visit_mode'] ?? 'Walk-in',
                $input['referred_from'] ?? null,
                $input['referred_to'] ?? null,
                $input['chief_complaints'] ?? null,
                $input['bp'] ?? null,
                $input['temperature'] ?? null,
                $input['height'] ?? null,
                $input['weight'] ?? null
            );
            
            if ($stmt->execute()) {
                $record_id = $conn->insert_id;
                
                // Insert vitals record if vital signs are provided
                if (!empty($input['bp']) || !empty($input['temperature']) || !empty($input['height']) || !empty($input['weight'])) {
                    $vitals_sql = "INSERT INTO vitals_record 
                                   (medical_record_id, bp, temperature, height, weight, recorded_at) 
                                   VALUES (?, ?, ?, ?, ?, NOW())";
                    $vitals_stmt = $conn->prepare($vitals_sql);
                    
                    if ($vitals_stmt) {
                        $vitals_stmt->bind_param('isddd', 
                            $record_id,
                            $input['bp'] ?? null,
                            $input['temperature'] ?? null,
                            $input['height'] ?? null,
                            $input['weight'] ?? null
                        );
                        $vitals_stmt->execute();
                        $vitals_stmt->close();
                    }
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Medical record created successfully', 'data' => ['id' => $record_id]]);
            } else {
                throw new Exception('Failed to create medical record: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception('Failed to prepare statement');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateMedicalRecord() {
    global $conn;
    
    $record_id = $_GET['id'] ?? null;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$record_id || !$input) {
        echo json_encode(['success' => false, 'message' => 'Record ID and update data are required']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        // Update medical record
        $sql = "UPDATE medical_record SET 
                disease_id = ?, diagnosis = ?, treatment = ?, visit_date = ?, 
                barangay_id = ?, consultation_time = ?, visit_mode = ?, 
                referred_from = ?, referred_to = ?, chief_complaints = ?, 
                bp = ?, temperature = ?, height = ?, weight = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('issssssssssdddi', 
                $input['disease_id'] ?? null,
                $input['diagnosis'] ?? null,
                $input['treatment'] ?? null,
                $input['visit_date'],
                $input['barangay_id'] ?? null,
                $input['consultation_time'] ?? null,
                $input['visit_mode'] ?? 'Walk-in',
                $input['referred_from'] ?? null,
                $input['referred_to'] ?? null,
                $input['chief_complaints'] ?? null,
                $input['bp'] ?? null,
                $input['temperature'] ?? null,
                $input['height'] ?? null,
                $input['weight'] ?? null,
                $record_id
            );
            
            if ($stmt->execute()) {
                // Update or insert vitals record
                if (!empty($input['bp']) || !empty($input['temperature']) || !empty($input['height']) || !empty($input['weight'])) {
                    // Check if vitals record exists
                    $check_sql = "SELECT id FROM vitals_record WHERE medical_record_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    
                    if ($check_stmt) {
                        $check_stmt->bind_param('i', $record_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        $check_stmt->close();
                        
                        if ($check_result->num_rows > 0) {
                            // Update existing vitals record
                            $vitals_sql = "UPDATE vitals_record SET 
                                           bp = ?, temperature = ?, height = ?, weight = ?, recorded_at = NOW()
                                           WHERE medical_record_id = ?";
                            $vitals_stmt = $conn->prepare($vitals_sql);
                            
                            if ($vitals_stmt) {
                                $vitals_stmt->bind_param('sdddi', 
                                    $input['bp'] ?? null,
                                    $input['temperature'] ?? null,
                                    $input['height'] ?? null,
                                    $input['weight'] ?? null,
                                    $record_id
                                );
                                $vitals_stmt->execute();
                                $vitals_stmt->close();
                            }
                        } else {
                            // Insert new vitals record
                            $vitals_sql = "INSERT INTO vitals_record 
                                           (medical_record_id, bp, temperature, height, weight, recorded_at) 
                                           VALUES (?, ?, ?, ?, ?, NOW())";
                            $vitals_stmt = $conn->prepare($vitals_sql);
                            
                            if ($vitals_stmt) {
                                $vitals_stmt->bind_param('isddd', 
                                    $record_id,
                                    $input['bp'] ?? null,
                                    $input['temperature'] ?? null,
                                    $input['height'] ?? null,
                                    $input['weight'] ?? null
                                );
                                $vitals_stmt->execute();
                                $vitals_stmt->close();
                            }
                        }
                    }
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Medical record updated successfully']);
            } else {
                throw new Exception('Failed to update medical record: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception('Failed to prepare statement');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteMedicalRecord() {
    global $conn;
    
    $record_id = $_GET['id'] ?? null;
    if (!$record_id) {
        echo json_encode(['success' => false, 'message' => 'Record ID is required']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        // Delete vitals record first (due to foreign key constraint)
        $vitals_sql = "DELETE FROM vitals_record WHERE medical_record_id = ?";
        $vitals_stmt = $conn->prepare($vitals_sql);
        
        if ($vitals_stmt) {
            $vitals_stmt->bind_param('i', $record_id);
            $vitals_stmt->execute();
            $vitals_stmt->close();
        }
        
        // Delete medical record
        $sql = "DELETE FROM medical_record WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $record_id);
            
            if ($stmt->execute()) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Medical record deleted successfully']);
            } else {
                throw new Exception('Failed to delete medical record: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception('Failed to prepare statement');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getMedicalRecordStats() {
    global $conn;
    
    try {
        $stats = [
            'today_consultations' => 0,
            'month_consultations' => 0,
            'total_records' => 0
        ];
        
        // Today's consultations
        $today_sql = "SELECT COUNT(*) as count FROM medical_record WHERE DATE(visit_date) = CURRENT_DATE()";
        $today_result = $conn->query($today_sql);
        if ($today_result) {
            $stats['today_consultations'] = $today_result->fetch_assoc()['count'];
        }
        
        // This month's consultations
        $month_sql = "SELECT COUNT(*) as count FROM medical_record WHERE YEAR(visit_date) = YEAR(CURRENT_DATE()) AND MONTH(visit_date) = MONTH(CURRENT_DATE())";
        $month_result = $conn->query($month_sql);
        if ($month_result) {
            $stats['month_consultations'] = $month_result->fetch_assoc()['count'];
        }
        
        // Total records
        $total_sql = "SELECT COUNT(*) as count FROM medical_record";
        $total_result = $conn->query($total_sql);
        if ($total_result) {
            $stats['total_records'] = $total_result->fetch_assoc()['count'];
        }
        
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
