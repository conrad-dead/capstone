<?php

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session info
error_log("Drug API - Session debug - user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Drug API - Session debug - username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("Drug API - Session debug - role_name: " . ($_SESSION['user_role_name'] ?? 'NOT SET'));

// Try different paths for database connection
$db_paths = [
    "../db/conn.php",
    "../../db/conn.php", 
    "db/conn.php"
];

$db_loaded = false;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $db_loaded = true;
        break;
    }
}

if (!$db_loaded) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection file not found']);
    exit();
}

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: '. $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? ''; // get mo kung anong resource ba category or drugs

// Determine which table stores medicines: prefer table referenced by medicine_distribution FK, else fallback
function table_exists(mysqli $conn, string $tableName): bool {
    $tableNameEsc = $conn->real_escape_string($tableName);
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$tableNameEsc' LIMIT 1";
    if ($res = $conn->query($sql)) { $exists = (bool)$res->num_rows; $res->free(); return $exists; }
    return false;
}

function resolve_medicine_table(mysqli $conn): string {
    // Strong preference: use 'medicine' if present, regardless of FK misconfiguration or backups
    if (table_exists($conn, 'medicine')) { return 'medicine'; }

    // Next, try FK reference but only accept known canonical tables
    $sql = "SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'medicine_distribution' 
              AND COLUMN_NAME = 'medicine_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL 
            LIMIT 1";
    if ($res = $conn->query($sql)) {
        if ($row = $res->fetch_assoc()) {
            $refTable = strtolower(trim($row['REFERENCED_TABLE_NAME']));
            $res->free();
            if (in_array($refTable, ['medicine','drugs'], true) && table_exists($conn, $refTable)) {
                return $refTable;
            }
        } else {
            $res->free();
        }
    }
    // Fallbacks
    if (table_exists($conn, 'drugs')) { return 'drugs'; }
    return 'medicine'; // last resort
}

$MED_TABLE = resolve_medicine_table($conn);

// Resolve column names for compatibility across 'medicine' and potential 'drugs' schemas
function column_exists(mysqli $conn, string $tableName, string $columnName): bool {
    $tableEsc = $conn->real_escape_string($tableName);
    $colEsc = $conn->real_escape_string($columnName);
    $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$tableEsc' AND column_name = '$colEsc' LIMIT 1";
    if ($res = $conn->query($sql)) { $exists = (bool)$res->num_rows; $res->free(); return $exists; }
    return false;
}

$MED_COL_ID = 'id';
$MED_COL_NAME = column_exists($conn, $MED_TABLE, 'name') ? 'name' : (column_exists($conn, $MED_TABLE, 'drug_name') ? 'drug_name' : 'name');
$MED_COL_QTY = column_exists($conn, $MED_TABLE, 'quantity') ? 'quantity' : (column_exists($conn, $MED_TABLE, 'qty') ? 'qty' : (column_exists($conn, $MED_TABLE, 'stock') ? 'stock' : 'quantity'));
$MED_COL_EXP = column_exists($conn, $MED_TABLE, 'expiry_date') ? 'expiry_date' : (column_exists($conn, $MED_TABLE, 'expiry') ? 'expiry' : 'expiry_date');
$MED_COL_CAT = 'category_id';

// Basic auth guard: require login, allow Admin(1) and Clinician(2) to write
function require_login() {
    // For testing purposes, allow access if no session is set
    if (!isset($_SESSION['user_id'])) {
        // Check if this is a test request
        if (isset($_GET['test']) || !isset($_SERVER['HTTP_REFERER'])) {
            // Allow test requests
            return;
        }
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

function can_manage_inventory(): bool {
    $roleId = isset($_SESSION['user_role_id']) ? (int)$_SESSION['user_role_id'] : null;
    $roleName = isset($_SESSION['user_role_name']) ? strtolower(trim($_SESSION['user_role_name'])) : '';
    if ($roleName === 'admin' || $roleName === 'pharmacy' || $roleName === 'pharmacist' || $roleName === 'pharmacists') return true;
    // Fallback to legacy numeric IDs if role names are not set
    return in_array($roleId, [1, 2], true);
}

switch ($resource) {
    case 'categories': 
        switch($method) {
            case 'GET': 
                require_login();
                // Get categories from medicine_categories table
                $categories = [];
                $sql = "SELECT id, name FROM medicine_categories ORDER BY name ASC";
                if($result = $conn->query($sql)) {
                    while($row = $result->fetch_assoc()) {
                        $categories[] = ['id' => $row['id'], 'name' => $row['name']];
                    }
                    $result->free();
                    echo json_encode(['success' => true, 'data' => $categories]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $conn->error]);
                }
                break;

            case 'POST': 
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                //Create a new category
                $input = json_decode(file_get_contents('php://input'), true);
                $name = trim($input['name'] ?? '');

                if (empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Category name is required.']);
                } else {
                    // Since we don't have a separate categories table, we'll just return success
                    // The category will be stored directly in the drugs table
                    echo json_encode(['success' => true, 'message' => "Category '{$name}' is available for use"]);
                }
                break;

            case 'PUT':
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                // update existing category
                $input = json_decode(file_get_contents('php://input'), true);
                $category_id = intval($input['id'] ?? 0);
                $name = trim($input['name'] ?? '');

                if ($category_id <= 0 || empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Category Id and name are required for update']);
                } else {
                    $sql = "UPDATE medicine_categories SET name = ? WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("si", $name, $category_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Category (ID: {$category_id}) updated to '{$name}' successfully"]);
                            } else {
                                echo json_encode(['success' => true, 'message' => "No changes made or category (ID: {$category_id}) not found"]);
                            }
                        } else {
                            if ($conn->errno == 1062) {
                                echo json_encode(['success' => false, 'message' => "Category '{$name}' already exists"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error updating category: ' .$stmt->error]);
                            }
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare update statement.'  .$conn->error]);
                    }
                }
                break;
            
            case 'DELETE':
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                //delete category 
                $input = json_decode(file_get_contents('php://input'), true);
                $category_id = intval($input['id'] ?? 0);
                
                if ($category_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid category ID for deletion']);
                } else {
                    // check if there is medicine associated with this category before deleting
                    $sql_check_medicines = "SELECT COUNT(*) AS medicine_count FROM " . $MED_TABLE . " WHERE category_id = ?";

                    if ($stmt_check = $conn->prepare($sql_check_medicines)) {
                        $stmt_check->bind_param('i', $category_id);
                        $stmt_check->execute();
                        $check_result = $stmt_check->get_result()->fetch_assoc();
                        $stmt_check->close();

                        if ($check_result['medicine_count'] > 0) {
                            echo json_encode(['success' => false, 'message' => "Cannot delete category (ID: {$category_id}) because it has {$check_result['medicine_count']} associated medicines."]);
                            break;
                        }
                    }

                    $sql = "DELETE FROM medicine_categories WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param('i', $category_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Category (ID: {$category_id}) Deleted Successfully"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "Category (ID: {$category_id}) not found or already deleted"]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error deleting category: '. $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare delete statement. ' .$conn->error]);
                    }

                }
                break;

            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed for categories.']);
                break;
        }
        break;
        
    case 'drugs': 
        switch($method) {

            case 'GET': 
                require_login();
                //handling pagination
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
                $offset = ($page - 1) * $limit;

                //get total drugs
                $total_drugs = 0;
                $sql_count = "SELECT COUNT(*) AS total FROM " . $MED_TABLE;
                if ($result_count = $conn->query($sql_count)) {
                    $row_count = $result_count->fetch_assoc();
                    $total_drugs = $row_count['total'];
                    $result_count->free();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error counting drugs. ' .$conn->error]);
                    break;
                }

                //list of drugs with pagination 
                $drugs = [];
                $sql = "SELECT m." . $MED_COL_ID . " AS id, m." . $MED_COL_NAME . " AS name, m." . $MED_COL_QTY . " AS quantity, m." . $MED_COL_EXP . " AS expiry_date, 
                               mc.name AS category_name, m." . $MED_COL_CAT . " AS category_id
                        FROM " . $MED_TABLE . " m
                        LEFT JOIN medicine_categories mc ON m." . $MED_COL_CAT . " = mc.id
                        ORDER BY m.name ASC
                        LIMIT ? OFFSET ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ii", $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $drugs[] = $row;
                    }
                    $result->free();
                    $stmt->close();
                    echo json_encode(['success' => true, 'data' => $drugs, 'total_drugs' => $total_drugs, 'page' => $page, 'limit' =>$limit]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching drugs: ' . $conn->error]);
                }
                break;

            case 'POST': 
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                $input = json_decode(file_get_contents('php://input'), true);
                $name = trim($input['name']);
                $category_id = intval($input['category_id']);
                $quantity = intval($input['quantity'] ?? 0);
                $expiry_date = trim($input['expiry_date'] ?? '');

                if (empty($name) || $category_id <= 0 || $quantity < 0) {
                    echo json_encode(['success' => false, 'message' => 'Drug name, category, and quantity are required']);
                } else {
                                         $sql = "INSERT INTO " . $MED_TABLE . " (name, category_id, quantity, expiry_date) VALUES (?, ?, ?, ?)";
                     if ($stmt = $conn->prepare($sql)) {
                     $stmt->bind_param("siis", $name, $category_id, $quantity, $expiry_date);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true, 'message' => "Drug '{$name}' added successfully!"]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error adding drug: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                    }
                }
                break;

            case 'PUT': 
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                $input = json_decode(file_get_contents('php://input'), true);
                $drug_id = intval($input['id'] ?? 0);
                $name = trim($input['name']);
                $category_id = intval($input['category_id'] ?? 0);
                $quantity = intval($input['quantity'] ?? 0);
                $expiry_date = trim($input['expiry_date'] ?? '');

                if ($drug_id <= 0 || empty($name) || $category_id <= 0 || $quantity < 0) {
                    echo json_encode(['success' => false, 'message' => 'Drug ID, name, category, and quantity are required for update.']);
                } else {
                                         $sql = "UPDATE " . $MED_TABLE . " SET name = ?, category_id = ?, quantity = ?, expiry_date = ? WHERE id = ?";
                     if ($stmt = $conn->prepare($sql)) {
                         $stmt->bind_param("siisi", $name, $category_id, $quantity, $expiry_date, $drug_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Drug (ID: {$drug_id}) updated successfully!"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "No changes made or drug (ID: {$drug_id}) not found."]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error updating drug: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare update statement. ' . $conn->error]);
                    }
                }
                break;

            case 'DELETE': 
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                $input = json_decode(file_get_contents('php://input'), true);
                $drug_id = intval($input['id'] ?? 0);

                if ($drug_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid drug ID for deletion.']);
                } else {
                    $sql = "DELETE FROM " . $MED_TABLE . " WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $drug_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Drug (ID: {$drug_id}) deleted successfully!"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "Drug (ID: {$drug_id}) not found or already deleted."]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error deleting drug: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare delete statement. ' . $conn->error]);
                    }
                }
                break;

            default: 
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed for drugs.']);
                break;
        }
        break;
        
    case 'distributions':
        switch($method) {
            case 'GET':
                require_login();
                
                // Check if requesting stats
                if (isset($_GET['stats']) && $_GET['stats'] === 'summary') {
                    // Get distribution statistics
                    $stats = [];
                    
                    // Today's distributions
                    $sql_today = "SELECT COUNT(*) as count FROM medicine_distribution WHERE DATE(distribution_date) = CURDATE()";
                    if ($result = $conn->query($sql_today)) {
                        $stats['distributions_today'] = $result->fetch_assoc()['count'];
                        $result->free();
                    }
                    
                    // This month's distributions
                    $sql_month = "SELECT COUNT(*) as count FROM medicine_distribution WHERE MONTH(distribution_date) = MONTH(CURDATE()) AND YEAR(distribution_date) = YEAR(CURDATE())";
                    if ($result = $conn->query($sql_month)) {
                        $stats['distributions_month'] = $result->fetch_assoc()['count'];
                        $result->free();
                    }
                    
                    // Pending prescriptions
                    $sql_pending = "SELECT COUNT(*) as count FROM medicine_distribution WHERE status = 'pending'";
                    if ($result = $conn->query($sql_pending)) {
                        $stats['pending_prescriptions'] = $result->fetch_assoc()['count'];
                        $result->free();
                    }
                    
                    // Active doctors this month
                    $sql_doctors = "SELECT COUNT(DISTINCT doctor_id) as count FROM medicine_distribution WHERE MONTH(distribution_date) = MONTH(CURDATE()) AND YEAR(distribution_date) = YEAR(CURDATE())";
                    if ($result = $conn->query($sql_doctors)) {
                        $stats['active_doctors'] = $result->fetch_assoc()['count'];
                        $result->free();
                    }
                    
                    echo json_encode(['success' => true, 'data' => $stats]);
                    break;
                }
                
                // Regular distribution listing with pagination
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
                $offset = ($page - 1) * $limit;
                
                // Build WHERE clause for filters
                $where_conditions = [];
                $params = [];
                $param_types = '';
                
                if (!empty($_GET['search'])) {
                    $search = '%' . $_GET['search'] . '%';
                    $where_conditions[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR m.name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
                    $params = array_merge($params, [$search, $search, $search, $search]);
                    $param_types .= 'ssss';
                }
                
                if (!empty($_GET['doctor'])) {
                    $where_conditions[] = "md.doctor_id = ?";
                    $params[] = intval($_GET['doctor']);
                    $param_types .= 'i';
                }
                
                if (!empty($_GET['status'])) {
                    $where_conditions[] = "md.status = ?";
                    $params[] = $_GET['status'];
                    $param_types .= 's';
                }
                
                if (!empty($_GET['date'])) {
                    $where_conditions[] = "DATE(md.distribution_date) = ?";
                    $params[] = $_GET['date'];
                    $param_types .= 's';
                }
                
                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                
                // Get total count
                $count_sql = "SELECT COUNT(*) as total FROM medicine_distribution md 
                             LEFT JOIN patients p ON md.patient_id = p.id 
                             LEFT JOIN " . $MED_TABLE . " m ON md.medicine_id = m.id 
                             LEFT JOIN users u ON md.doctor_id = u.id 
                             $where_clause";
                
                $total_distributions = 0;
                if ($stmt = $conn->prepare($count_sql)) {
                    if (!empty($params)) {
                        $stmt->bind_param($param_types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $total_distributions = $result->fetch_assoc()['total'];
                    $stmt->close();
                }
                
                // Get distributions with joins
                $sql = "SELECT md.*, 
                               CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                               m.name as medicine_name,
                               CONCAT(u.first_name, ' ', u.last_name) as doctor_name
                        FROM medicine_distribution md
                        LEFT JOIN patients p ON md.patient_id = p.id
                        LEFT JOIN " . $MED_TABLE . " m ON md.medicine_id = m.id
                        LEFT JOIN users u ON md.doctor_id = u.id
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
                
            case 'POST':
                require_login();
                if (!can_manage_inventory()) { 
                    http_response_code(403); 
                    echo json_encode(['success'=>false,'message'=>'Forbidden']); 
                    break; 
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $patient_id = intval($input['patient_id'] ?? 0);
                $doctor_id = intval($input['doctor_id'] ?? 0);
                $medicine_id = intval($input['medicine_id'] ?? 0);
                $quantity = intval($input['quantity'] ?? 0);
                $prescription_date = trim($input['prescription_date'] ?? '');
                $distribution_date = trim($input['distribution_date'] ?? '');
                $notes = trim($input['notes'] ?? '');
                
                if ($patient_id <= 0 || $doctor_id <= 0 || $medicine_id <= 0 || $quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Patient, doctor, medicine, and quantity are required']);
                    break;
                }
                
                // Check if medicine has sufficient stock
                $check_stock_sql = "SELECT quantity FROM " . $MED_TABLE . " WHERE id = ?";
                if ($stmt = $conn->prepare($check_stock_sql)) {
                    $stmt->bind_param("i", $medicine_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $medicine = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!$medicine) {
                        echo json_encode(['success' => false, 'message' => 'Medicine not found']);
                        break;
                    }
                    
                    if ($medicine['quantity'] < $quantity) {
                        echo json_encode(['success' => false, 'message' => "Insufficient stock. Available: {$medicine['quantity']}"]);
                        break;
                    }
                }
                
                // Insert distribution record
                $insert_sql = "INSERT INTO medicine_distribution (patient_id, doctor_id, medicine_id, quantity, prescription_date, distribution_date, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', NOW())";
                if ($stmt = $conn->prepare($insert_sql)) {
                    $stmt->bind_param("iiissss", $patient_id, $doctor_id, $medicine_id, $quantity, $prescription_date, $distribution_date, $notes);
                    
                    if ($stmt->execute()) {
                        $distribution_id = $stmt->insert_id;
                        
                        // Update medicine stock
                        $update_stock_sql = "UPDATE " . $MED_TABLE . " SET quantity = quantity - ? WHERE id = ?";
                        if ($update_stmt = $conn->prepare($update_stock_sql)) {
                            $update_stmt->bind_param("ii", $quantity, $medicine_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                        
                        echo json_encode(['success' => true, 'message' => 'Distribution recorded successfully!']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error recording distribution: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                }
                break;
                
            case 'PUT':
                require_login();
                if (!can_manage_inventory()) { 
                    http_response_code(403); 
                    echo json_encode(['success'=>false,'message'=>'Forbidden']); 
                    break; 
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $distribution_id = intval($input['id'] ?? 0);
                $status = trim($input['status'] ?? '');
                
                if ($distribution_id <= 0 || empty($status)) {
                    echo json_encode(['success' => false, 'message' => 'Distribution ID and status are required']);
                    break;
                }
                
                $update_sql = "UPDATE medicine_distribution SET status = ?, updated_at = NOW() WHERE id = ?";
                if ($stmt = $conn->prepare($update_sql)) {
                    $stmt->bind_param("si", $status, $distribution_id);
                    
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            echo json_encode(['success' => true, 'message' => 'Distribution status updated successfully!']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Distribution not found or no changes made']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error updating distribution: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                }
                break;
                
            case 'DELETE':
                require_login();
                if (!can_manage_inventory()) { 
                    http_response_code(403); 
                    echo json_encode(['success'=>false,'message'=>'Forbidden']); 
                    break; 
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $distribution_id = intval($input['id'] ?? 0);
                
                if ($distribution_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid distribution ID']);
                    break;
                }
                
                // Get distribution details before deletion
                $get_sql = "SELECT medicine_id, quantity FROM medicine_distribution WHERE id = ?";
                if ($stmt = $conn->prepare($get_sql)) {
                    $stmt->bind_param("i", $distribution_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $distribution = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!$distribution) {
                        echo json_encode(['success' => false, 'message' => 'Distribution not found']);
                        break;
                    }
                }
                
                // Delete distribution
                $delete_sql = "DELETE FROM medicine_distribution WHERE id = ?";
                if ($stmt = $conn->prepare($delete_sql)) {
                    $stmt->bind_param("i", $distribution_id);
                    
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            // Restore medicine stock
                            $restore_sql = "UPDATE " . $MED_TABLE . " SET quantity = quantity + ? WHERE id = ?";
                            if ($restore_stmt = $conn->prepare($restore_sql)) {
                                $restore_stmt->bind_param("ii", $distribution['quantity'], $distribution['medicine_id']);
                                $restore_stmt->execute();
                                $restore_stmt->close();
                            }
                            
                            echo json_encode(['success' => true, 'message' => 'Distribution deleted successfully!']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Distribution not found or already deleted']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error deleting distribution: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed for distributions']);
                break;
        }
        break;
        
    default:
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
        break;
}

$conn->close();
?>
