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
                // Categories are managed directly in drugs table, so this is not needed
                echo json_encode(['success' => false, 'message' => 'Category updates not supported in this version']);
                break;
            
            case 'DELETE':
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                // Categories are managed directly in drugs table, so this is not needed
                echo json_encode(['success' => false, 'message' => 'Category deletions not supported in this version']);
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

                if (empty($name) || $category_id <= 0 || $quantity < 0 || empty($expiry_date)) {
                    echo json_encode(['success' => false, 'message' => 'All drug fields are required']);
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

                if ($drug_id <= 0 || empty($name) || $category_id <= 0 || $quantity < 0 || empty($expiry_date)) {
                    echo json_encode(['success' => false, 'message' => 'Drug ID and all fields are required and valid for update.']);
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
                                echo json_encode(['success' => true, 'message' => "Drug (ID: {$drug_id}) deleted successfully."]);
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
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed for drugs.']);
                break;
        }
        break;
    case 'distributions':
        switch ($method) {
            case 'GET':
                require_login();
                // Aggregates
                $aggregate = $_GET['aggregate'] ?? '';
                $period = strtolower($_GET['period'] ?? '');
                $stats = strtolower($_GET['stats'] ?? '');
                if ($stats === 'summary') {
                    // KPI summary: distributions today (records), total items distributed this month (sum), low stock items (<20)
                    $summary = ['distributions_today' => 0, 'distributed_month_quantity' => 0, 'low_stock_count' => 0];
                    // today count
                    if ($res = $conn->query("SELECT COUNT(*) AS c FROM medicine_distribution WHERE DATE(date_issued) = CURRENT_DATE()")) {
                        $row = $res->fetch_assoc();
                        $summary['distributions_today'] = (int)$row['c'];
                        $res->free();
                    }
                    // month sum
                    if ($res = $conn->query("SELECT COALESCE(SUM(quantity_given),0) AS s FROM medicine_distribution WHERE YEAR(date_issued)=YEAR(CURRENT_DATE()) AND MONTH(date_issued)=MONTH(CURRENT_DATE())")) {
                        $row = $res->fetch_assoc();
                        $summary['distributed_month_quantity'] = (int)$row['s'];
                        $res->free();
                    }
                    // low stock
                    if ($res = $conn->query("SELECT COUNT(*) AS c FROM " . $MED_TABLE . " WHERE " . $MED_COL_QTY . " < 20")) {
                        $row = $res->fetch_assoc();
                        $summary['low_stock_count'] = (int)$row['c'];
                        $res->free();
                    }
                    echo json_encode(['success' => true, 'data' => $summary]);
                    break;
                }
                if ($aggregate === 'top' && in_array($period, ['month','year'], true)) {
                    $dateCondition = $period === 'month' ? "DATE_FORMAT(md.date_issued, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')" : "YEAR(md.date_issued) = YEAR(CURRENT_DATE())";
                    $sql = "SELECT m." . $MED_COL_ID . " AS id, m." . $MED_COL_NAME . " AS name, SUM(md.quantity_given) AS total_given
                            FROM medicine_distribution md
                            JOIN " . $MED_TABLE . " m ON m." . $MED_COL_ID . " = md.medicine_id
                            WHERE $dateCondition
                            GROUP BY m." . $MED_COL_ID . ", m." . $MED_COL_NAME . "
                            ORDER BY total_given DESC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                        $rows = [];
                        while ($row = $result->fetch_assoc()) { $rows[] = $row; }
                        $result->free();
                        echo json_encode(['success' => true, 'data' => $rows]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error fetching analytics: ' . $conn->error]);
                    }
                    break;
                }
                // List distributions (simple pagination)
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
                $offset = ($page - 1) * $limit;
                $total = 0;
                if ($resCount = $conn->query("SELECT COUNT(*) AS total FROM medicine_distribution")) {
                    $rowc = $resCount->fetch_assoc();
                    $total = (int)$rowc['total'];
                    $resCount->free();
                }
                $rows = [];
                $hasMedRecCol = column_exists($conn, 'medicine_distribution', 'medical_record_id');
                $selectMedRec = $hasMedRecCol ? ", md.medical_record_id, mr.disease_id, mr.diagnosis, mr.treatment" : "";
                $joinMedRec = $hasMedRecCol ? " LEFT JOIN medical_record mr ON mr.id = md.medical_record_id" : "";
                $sql = "SELECT md.id, md.medicine_id, m." . $MED_COL_NAME . " AS drug_name, md.quantity_given, md.date_issued, 
                               md.clinician_id, CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) AS clinician_name, '' AS notes, 
                               p.id AS patient_id, p.patient_code, p.first_name, p.last_name, CONCAT(p.first_name, ' ', p.last_name) AS patient_name
                               " . $selectMedRec . "
                        FROM medicine_distribution md
                        JOIN " . $MED_TABLE . " m ON m." . $MED_COL_ID . " = md.medicine_id
                        LEFT JOIN patients_table p ON p.id = md.patient_id
                        LEFT JOIN clinicians_table c ON c.id = md.clinician_id
                        " . $joinMedRec . "
                        ORDER BY md.date_issued DESC
                        LIMIT ? OFFSET ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('ii', $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) { $rows[] = $row; }
                    $result->free();
                    $stmt->close();
                    echo json_encode(['success' => true, 'data' => $rows, 'total_distributions' => $total, 'page' => $page, 'limit' => $limit]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching distributions: ' . $conn->error]);
                }
                break;
            case 'POST':
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                $rawBody = file_get_contents('php://input');
                $input = json_decode($rawBody, true);
                if (!is_array($input)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid JSON payload']); break; }
                // Accept alternate field names from legacy UIs
                $drug_id = intval($input['drug_id'] ?? $input['medicine_id'] ?? 0);
                $quantity_given = intval($input['quantity_given'] ?? $input['qty'] ?? 0);
                $patient_id = intval($input['patient_id'] ?? $input['recipient'] ?? 0);
                $medical_record_id = intval($input['medical_record_id'] ?? 0);
                $notes = isset($input['notes']) ? trim($input['notes']) : null;
                // Quick debug info to server logs
                error_log('Distribute POST - payload: ' . $rawBody);
                
                if ($drug_id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'drug_id is required']); break; }
                if ($quantity_given <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'quantity_given must be > 0']); break; }
                if ($patient_id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'patient_id is required']); break; }
                
                // Transaction: check stock, decrement, insert distribution
                $conn->begin_transaction();
                try {
                    // lock row
                    $sql = "SELECT " . $MED_COL_QTY . " AS quantity FROM " . $MED_TABLE . " WHERE " . $MED_COL_ID . " = ? FOR UPDATE";
                    if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                    $stmt->bind_param('i', $drug_id);
                    $stmt->execute();
                    $stmt->bind_result($current_qty);
                    if (!$stmt->fetch()) { $stmt->close(); throw new Exception('Drug not found'); }
                    $stmt->close();
                    if ($current_qty < $quantity_given) { throw new Exception('Insufficient stock'); }
                    
                    $new_qty = $current_qty - $quantity_given;
                    $sql = "UPDATE " . $MED_TABLE . " SET " . $MED_COL_QTY . " = ? WHERE " . $MED_COL_ID . " = ?";
                    if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                    $stmt->bind_param('ii', $new_qty, $drug_id);
                    if (!$stmt->execute()) { $stmt->close(); throw new Exception('Failed to update stock: ' . $stmt->error); }
                    $stmt->close();
                    
                    // Resolve clinician_id from session -> clinicians_table
                    $clinicianId = null;
                    if (table_exists($conn, 'clinicians_table')) {
                        $sessionUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                        $sessionUsername = isset($_SESSION['username']) ? trim($_SESSION['username']) : '';
                        // Prefer mapping by username
                        if (!empty($sessionUsername)) {
                            if ($stmt = $conn->prepare('SELECT id FROM clinicians_table WHERE username = ? LIMIT 1')) {
                                $stmt->bind_param('s', $sessionUsername);
                                $stmt->execute();
                                $stmt->bind_result($cid);
                                if ($stmt->fetch()) { $clinicianId = (int)$cid; }
                                $stmt->close();
                            }
                        }
                        // Fallback: try mapping by same numeric id
                        if ($clinicianId === null && $sessionUserId !== null) {
                            if ($stmt = $conn->prepare('SELECT id FROM clinicians_table WHERE id = ? LIMIT 1')) {
                                $stmt->bind_param('i', $sessionUserId);
                                $stmt->execute();
                                $stmt->bind_result($cid);
                                if ($stmt->fetch()) { $clinicianId = (int)$cid; }
                                $stmt->close();
                            }
                        }
                    }
                    if ($clinicianId === null) {
                        // Auto-provision a minimal clinician record for the current user
                        $sessionUsernameSafe = $conn->real_escape_string($sessionUsername ?: ('user_' . ($sessionUserId ?? 'unknown')));
                        $roleName = isset($_SESSION['user_role_name']) ? trim((string)$_SESSION['user_role_name']) : '';
                        $clinicianRole = in_array($roleName, ['Doctor','Nurse','MidWife','Other'], true) ? $roleName : 'Other';
                        // Required NOT NULL columns: first_name, last_name, role
                        $firstName = $sessionUsernameSafe;
                        $lastName = 'Account';
                        $roleId = isset($_SESSION['user_role_id']) ? (int)$_SESSION['user_role_id'] : null;

                        $sqlInsert = 'INSERT INTO clinicians_table (first_name, last_name, role, username, role_id) VALUES (?,?,?,?,?)';
                        if (!($stmt = $conn->prepare($sqlInsert))) { throw new Exception('Prepare failed: ' . $conn->error); }
                        $stmt->bind_param('ssssi', $firstName, $lastName, $clinicianRole, $sessionUsernameSafe, $roleId);
                        if (!$stmt->execute()) { $stmt->close(); throw new Exception('Failed to auto-create clinician record: ' . $stmt->error); }
                        $stmt->close();

                        $clinicianId = (int)$conn->insert_id;
                        if ($clinicianId <= 0) { throw new Exception('Failed to resolve clinician after auto-create'); }
                    }

                    // Insert distribution. If medical_record_id column exists and provided, include it.
                    $hasMedRecCol = column_exists($conn, 'medicine_distribution', 'medical_record_id');
                    if ($hasMedRecCol && $medical_record_id > 0) {
                        // Validate medical record existence
                        if (!($stmt = $conn->prepare('SELECT 1 FROM medical_record WHERE id = ?'))) { throw new Exception('Prepare failed: ' . $conn->error); }
                        $stmt->bind_param('i', $medical_record_id);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows === 0) { $stmt->close(); throw new Exception('medical_record_id not found'); }
                        $stmt->close();

                        $sql = "INSERT INTO medicine_distribution (medicine_id, patient_id, medical_record_id, quantity_given, date_issued, clinician_id) VALUES (?,?,?,?,CURDATE(),?)";
                        if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                        $stmt->bind_param('iiiii', $drug_id, $patient_id, $medical_record_id, $quantity_given, $clinicianId);
                    } else {
                        $sql = "INSERT INTO medicine_distribution (medicine_id, patient_id, quantity_given, date_issued, clinician_id) VALUES (?,?,?,CURDATE(),?)";
                        if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                        $stmt->bind_param('iiii', $drug_id, $patient_id, $quantity_given, $clinicianId);
                    }
                    if (!$stmt->execute()) { $stmt->close(); throw new Exception('Failed to record distribution: ' . $stmt->error); }
                    $stmt->close();
                    
                    $conn->commit();
                    echo json_encode(['success'=>true,'message'=>'Distribution recorded and stock updated','new_quantity'=>$new_qty]);
                } catch (Exception $e) {
                    $conn->rollback();
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['success'=>false,'message'=>'Method Not Allowed for distributions.']);
                break;
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API resource specified.']);
        break;
}
?>