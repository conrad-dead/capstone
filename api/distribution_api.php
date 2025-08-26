<?php
session_start();

require_once '../db/conn.php';

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';

// Basic auth guard: require login and clinician role
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

function is_clinician(): bool {
    $roleName = isset($_SESSION['user_role_name']) ? strtolower(trim($_SESSION['user_role_name'])) : '';
    return in_array($roleName, ['clinician', 'doctor', 'nurse', 'midwife'], true);
}

function is_admin(): bool {
    return isset($_SESSION['user_role_id']) && (int)$_SESSION['user_role_id'] === 1;
}

// Determine medicine table
function resolve_medicine_table($conn): string {
    $sql = "SHOW TABLES LIKE 'medicine'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            return 'medicine';
        }
    }
    return 'drugs';
}

$MED_TABLE = resolve_medicine_table($conn);

switch ($resource) {
    case 'available_medicines':
        switch($method) {
            case 'GET':
                require_login();
                
                $medicines = [];
                $sql = "SELECT id, name, quantity, expiry_date, category_id 
                        FROM $MED_TABLE 
                        WHERE quantity > 0 AND expiry_date >= CURDATE()
                        ORDER BY name ASC";
                
                if ($result = $conn->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        $medicines[] = $row;
                    }
                    $result->free();
                    echo json_encode(['success' => true, 'data' => $medicines]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching medicines: ' . $conn->error]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
        break;
        
    case 'distribute':
        switch($method) {
            case 'POST':
                require_login();
                if (!is_clinician() && !is_admin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Only clinicians can distribute medicines']);
                    break;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $patient_id = intval($input['patient_id'] ?? 0);
                $medicine_id = intval($input['medicine_id'] ?? 0);
                $quantity = intval($input['quantity'] ?? 0);
                $prescription_date = trim($input['prescription_date'] ?? date('Y-m-d'));
                $notes = trim($input['notes'] ?? '');
                $doctor_id = $_SESSION['user_id'];
                
                if ($patient_id <= 0 || $medicine_id <= 0 || $quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Patient, medicine, and quantity are required']);
                    break;
                }
                
                // Check if patient exists
                $check_patient_sql = "SELECT id FROM patients WHERE id = ?";
                if ($stmt = $conn->prepare($check_patient_sql)) {
                    $stmt->bind_param("i", $patient_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows === 0) {
                        echo json_encode(['success' => false, 'message' => 'Patient not found']);
                        $stmt->close();
                        break;
                    }
                    $stmt->close();
                }
                
                // Check medicine availability and stock
                $check_medicine_sql = "SELECT id, name, quantity FROM $MED_TABLE WHERE id = ? AND quantity > 0 AND expiry_date >= CURDATE()";
                if ($stmt = $conn->prepare($check_medicine_sql)) {
                    $stmt->bind_param("i", $medicine_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $medicine = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!$medicine) {
                        echo json_encode(['success' => false, 'message' => 'Medicine not available or out of stock']);
                        break;
                    }
                    
                    if ($medicine['quantity'] < $quantity) {
                        echo json_encode(['success' => false, 'message' => "Insufficient stock. Available: {$medicine['quantity']}"]);
                        break;
                    }
                }
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert distribution record
                    $insert_sql = "INSERT INTO medicine_distribution (patient_id, doctor_id, medicine_id, quantity, prescription_date, distribution_date, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', NOW())";
                    if ($stmt = $conn->prepare($insert_sql)) {
                        $distribution_date = date('Y-m-d');
                        $stmt->bind_param("iiissss", $patient_id, $doctor_id, $medicine_id, $quantity, $prescription_date, $distribution_date, $notes);
                        
                        if (!$stmt->execute()) {
                            throw new Exception('Error recording distribution: ' . $stmt->error);
                        }
                        $distribution_id = $stmt->insert_id;
                        $stmt->close();
                        
                        // Update medicine stock
                        $update_stock_sql = "UPDATE $MED_TABLE SET quantity = quantity - ? WHERE id = ?";
                        if ($update_stmt = $conn->prepare($update_stock_sql)) {
                            $update_stmt->bind_param("ii", $quantity, $medicine_id);
                            if (!$update_stmt->execute()) {
                                throw new Exception('Error updating stock: ' . $update_stmt->error);
                            }
                            $update_stmt->close();
                        }
                        
                        $conn->commit();
                        echo json_encode(['success' => true, 'message' => 'Medicine distributed successfully!', 'distribution_id' => $distribution_id]);
                    } else {
                        throw new Exception('Database error: Could not prepare statement');
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
        break;
        
    case 'my_distributions':
        switch($method) {
            case 'GET':
                require_login();
                if (!is_clinician() && !is_admin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Access denied']);
                    break;
                }
                
                $doctor_id = $_SESSION['user_id'];
                $page = intval($_GET['page'] ?? 1);
                $limit = intval($_GET['limit'] ?? 10);
                $offset = ($page - 1) * $limit;
                
                // Build where conditions
                $where_conditions = ["md.doctor_id = ?"];
                $params = [$doctor_id];
                $param_types = 'i';
                
                if (!empty($_GET['patient_name'])) {
                    $where_conditions[] = "CONCAT(p.first_name, ' ', p.last_name) LIKE ?";
                    $params[] = '%' . $_GET['patient_name'] . '%';
                    $param_types .= 's';
                }
                
                if (!empty($_GET['medicine_name'])) {
                    $where_conditions[] = "m.name LIKE ?";
                    $params[] = '%' . $_GET['medicine_name'] . '%';
                    $param_types .= 's';
                }
                
                if (!empty($_GET['date'])) {
                    $where_conditions[] = "DATE(md.distribution_date) = ?";
                    $params[] = $_GET['date'];
                    $param_types .= 's';
                }
                
                $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
                
                // Get total count
                $count_sql = "SELECT COUNT(*) as total FROM medicine_distribution md 
                             LEFT JOIN patients p ON md.patient_id = p.id 
                             LEFT JOIN $MED_TABLE m ON md.medicine_id = m.id 
                             $where_clause";
                
                $total_distributions = 0;
                if ($stmt = $conn->prepare($count_sql)) {
                    $stmt->bind_param($param_types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $total_distributions = $result->fetch_assoc()['total'];
                    $stmt->close();
                }
                
                // Get distributions
                $sql = "SELECT md.*, 
                               CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                               m.name as medicine_name,
                               m.quantity as current_stock
                        FROM medicine_distribution md
                        LEFT JOIN patients p ON md.patient_id = p.id
                        LEFT JOIN $MED_TABLE m ON md.medicine_id = m.id
                        $where_clause
                        ORDER BY md.distribution_date DESC
                        LIMIT ? OFFSET ?";
                
                $distributions = [];
                if ($stmt = $conn->prepare($sql)) {
                    $all_params = array_merge($params, [$limit, $offset]);
                    $all_param_types = $param_types . 'ii';
                    $stmt->bind_param($all_param_types, ...$all_params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()) {
                        $distributions[] = $row;
                    }
                    $result->free();
                    $stmt->close();
                    
                    echo json_encode([
                        'success' => true, 
                        'data' => $distributions, 
                        'total' => $total_distributions,
                        'page' => $page,
                        'limit' => $limit
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching distributions: ' . $conn->error]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
        break;
        
    case 'distribution_stats':
        switch($method) {
            case 'GET':
                require_login();
                if (!is_clinician() && !is_admin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Access denied']);
                    break;
                }
                
                $doctor_id = $_SESSION['user_id'];
                
                // Today's distributions
                $sql_today = "SELECT COUNT(*) as count FROM medicine_distribution WHERE doctor_id = ? AND DATE(distribution_date) = CURDATE()";
                $today_count = 0;
                if ($stmt = $conn->prepare($sql_today)) {
                    $stmt->bind_param("i", $doctor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $today_count = $result->fetch_assoc()['count'];
                    $stmt->close();
                }
                
                // This month's distributions
                $sql_month = "SELECT COUNT(*) as count FROM medicine_distribution WHERE doctor_id = ? AND MONTH(distribution_date) = MONTH(CURDATE()) AND YEAR(distribution_date) = YEAR(CURDATE())";
                $month_count = 0;
                if ($stmt = $conn->prepare($sql_month)) {
                    $stmt->bind_param("i", $doctor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $month_count = $result->fetch_assoc()['count'];
                    $stmt->close();
                }
                
                // Total patients served
                $sql_patients = "SELECT COUNT(DISTINCT patient_id) as count FROM medicine_distribution WHERE doctor_id = ?";
                $patients_count = 0;
                if ($stmt = $conn->prepare($sql_patients)) {
                    $stmt->bind_param("i", $doctor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $patients_count = $result->fetch_assoc()['count'];
                    $stmt->close();
                }
                
                // Most distributed medicines
                $sql_top_medicines = "SELECT m.name, SUM(md.quantity) as total_quantity
                                     FROM medicine_distribution md
                                     LEFT JOIN $MED_TABLE m ON md.medicine_id = m.id
                                     WHERE md.doctor_id = ?
                                     GROUP BY md.medicine_id, m.name
                                     ORDER BY total_quantity DESC
                                     LIMIT 5";
                $top_medicines = [];
                if ($stmt = $conn->prepare($sql_top_medicines)) {
                    $stmt->bind_param("i", $doctor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $top_medicines[] = $row;
                    }
                    $stmt->close();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'today_distributions' => $today_count,
                        'month_distributions' => $month_count,
                        'patients_served' => $patients_count,
                        'top_medicines' => $top_medicines
                    ]
                ]);
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API resource specified']);
        break;
}

$conn->close();
exit();
?>
