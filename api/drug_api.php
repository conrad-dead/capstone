<?php

session_start();

include_once "../db/conn.php";

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: '. $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? ''; // get mo kung anong resource ba category or drugs

// Basic auth guard: require login, allow Admin(1) and Clinician(2) to write
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

function can_manage_inventory(): bool {
    $roleId = isset($_SESSION['user_role_id']) ? (int)$_SESSION['user_role_id'] : null;
    $roleName = isset($_SESSION['user_role_name']) ? strtolower(trim($_SESSION['user_role_name'])) : '';
    if ($roleName === 'admin' || $roleName === 'pharmacist' || $roleName === 'pharmacists') return true;
    // Fallback to legacy numeric IDs if role names are not set
    return in_array($roleId, [1, 2], true);
}

// Ensure distributions table exists (idempotent)
function ensure_distributions_table($conn) {
    $createSql = "CREATE TABLE IF NOT EXISTS drug_distributions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        drug_id INT NOT NULL,
        quantity_given INT NOT NULL,
        date_issued DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        user_id INT NULL,
        recipient VARCHAR(255) NULL,
        FOREIGN KEY (drug_id) REFERENCES drugs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $conn->query($createSql);
}

switch ($resource) {
    case 'categories': 
        switch($method) {
            case 'GET': 
                require_login();
                // list of categories
                $categories = [];
                $sql = "SELECT id, name, created_at FROM drug_categories ORDER BY name ASC";
                if($result = $conn->query($sql)) {
                    while($row = $result->fetch_assoc()) {
                        $categories[] = $row;
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
                    $sql = "INSERT INTO drug_categories (name) VALUES (?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("s", $name);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true, 'message' => "Category '{$name}' created successfully"]);
                        } else {
                            if ($conn->errno == 1062) {
                                echo json_encode(['success' => false, 'message' => "Category '{$name}' already exists"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $stmt->error]);
                            }
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
                // update existing category
                $input = json_decode(file_get_contents('php://input'), true);
                $category_id = intval($input['id'] ?? 0);
                $name = trim($input['name'] ?? '');

                if ($category_id <= 0 || empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Category Id and name are required for update']);
                } else {
                    $sql = "UPDATE drug_categories SET name = ? WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("si", $name, $category_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Category (ID: {$category_id}) updated tp '{$name}' successfully"]);
                            } else {
                                echo json_encode(['success' => true, 'message' => "No changes made or category (ID: {$category_id}) not found"]);
                            }
                        } else {
                            if ($conn->errno == 1062) {
                                echo json_encode(['success' => false, 'message' => "Category '{$name}' already exist"]);
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
                    // check if there is drug associated with this category before deleting
                    $sql_check_drugs = "SELECT COUNT(*) AS drug_count FROM drugs WHERE category_id = ?";

                    if ($stmt_check = $conn->prepare($sql_check_drugs)) {
                        $stmt_check->bind_param('i', $category_id);
                        $stmt_check->execute();
                        $check_result = $stmt_check->get_result()->fetch_assoc();
                        $stmt_check->close();

                        if ($check_result['drug_count'] > 0) {
                            echo json_encode(['success' => false, 'message' => "Cannot delete category (ID: {$category_id}) because it has {$check_result['drug_count']} associated drugs."]);
                            break;
                        }
                    }

                    $sql = "DELETE FROM drug_categories WHERE id = ?";
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
                $sql_count = "SELECT COUNT(*) AS total FROM drugs";
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
                $sql = "SELECT d.id, d.name, d.quantity, d.expiry_date, d.created_at, c.name AS category_name, c.id AS category_id
                        FROM drugs d
                        LEFT JOIN drug_categories c ON d.category_id = c.id
                        ORDER BY d.name ASC
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
                    $sql = "INSERT INTO drugs (name, category_id, quantity, expiry_date) VALUES (?, ?, ?, ?)";
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
                    $sql = "UPDATE drugs SET name = ?, category_id = ?, quantity = ?, expiry_date = ? WHERE id = ?";
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
                    $sql = "DELETE FROM drugs WHERE id = ?";
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
        ensure_distributions_table($conn);
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
                    if ($res = $conn->query("SELECT COUNT(*) AS c FROM drug_distributions WHERE DATE(date_issued) = CURRENT_DATE()")) {
                        $row = $res->fetch_assoc();
                        $summary['distributions_today'] = (int)$row['c'];
                        $res->free();
                    }
                    // month sum
                    if ($res = $conn->query("SELECT COALESCE(SUM(quantity_given),0) AS s FROM drug_distributions WHERE YEAR(date_issued)=YEAR(CURRENT_DATE()) AND MONTH(date_issued)=MONTH(CURRENT_DATE())")) {
                        $row = $res->fetch_assoc();
                        $summary['distributed_month_quantity'] = (int)$row['s'];
                        $res->free();
                    }
                    // low stock
                    if ($res = $conn->query("SELECT COUNT(*) AS c FROM drugs WHERE quantity < 20")) {
                        $row = $res->fetch_assoc();
                        $summary['low_stock_count'] = (int)$row['c'];
                        $res->free();
                    }
                    echo json_encode(['success' => true, 'data' => $summary]);
                    break;
                }
                if ($aggregate === 'top' && in_array($period, ['month','year'], true)) {
                    $dateCondition = $period === 'month' ? "DATE_FORMAT(dd.date_issued, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')" : "YEAR(dd.date_issued) = YEAR(CURRENT_DATE())";
                    $sql = "SELECT d.id, d.name, SUM(dd.quantity_given) AS total_given
                            FROM drug_distributions dd
                            JOIN drugs d ON d.id = dd.drug_id
                            WHERE $dateCondition
                            GROUP BY d.id, d.name
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
                if ($resCount = $conn->query("SELECT COUNT(*) AS total FROM drug_distributions")) {
                    $rowc = $resCount->fetch_assoc();
                    $total = (int)$rowc['total'];
                    $resCount->free();
                }
                $rows = [];
                $sql = "SELECT dd.id, dd.drug_id, d.name AS drug_name, dd.quantity_given, dd.date_issued, dd.user_id, dd.recipient
                        FROM drug_distributions dd
                        JOIN drugs d ON d.id = dd.drug_id
                        ORDER BY dd.date_issued DESC
                        LIMIT ? OFFSET ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('ii', $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) { $rows[] = $row; }
                    $result->free();
                    $stmt->close();
                    echo json_encode(['success' => true, 'data' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching distributions: ' . $conn->error]);
                }
                break;
            case 'POST':
                require_login();
                if (!can_manage_inventory()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                $input = json_decode(file_get_contents('php://input'), true);
                $drug_id = intval($input['drug_id'] ?? 0);
                $quantity_given = intval($input['quantity_given'] ?? 0);
                $recipient = isset($input['recipient']) ? trim($input['recipient']) : null;
                if ($drug_id <= 0 || $quantity_given <= 0) { echo json_encode(['success'=>false,'message'=>'drug_id and positive quantity_given are required']); break; }
                // Transaction: check stock, decrement, insert distribution
                $conn->begin_transaction();
                try {
                    // lock row
                    $sql = "SELECT quantity FROM drugs WHERE id = ? FOR UPDATE";
                    if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                    $stmt->bind_param('i', $drug_id);
                    $stmt->execute();
                    $stmt->bind_result($current_qty);
                    if (!$stmt->fetch()) { $stmt->close(); throw new Exception('Drug not found'); }
                    $stmt->close();
                    if ($current_qty < $quantity_given) { throw new Exception('Insufficient stock'); }
                    $new_qty = $current_qty - $quantity_given;
                    $sql = "UPDATE drugs SET quantity = ? WHERE id = ?";
                    if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                    $stmt->bind_param('ii', $new_qty, $drug_id);
                    if (!$stmt->execute()) { $stmt->close(); throw new Exception('Failed to update stock: ' . $stmt->error); }
                    $stmt->close();
                    $sql = "INSERT INTO drug_distributions (drug_id, quantity_given, user_id, recipient) VALUES (?,?,?,?)";
                    if (!($stmt = $conn->prepare($sql))) { throw new Exception('Prepare failed: ' . $conn->error); }
                    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                    $stmt->bind_param('iiis', $drug_id, $quantity_given, $uid, $recipient);
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