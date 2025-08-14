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
$resource = $_GET['resource']; // get mo kung anong resource ba category or drugs

switch ($resource) {
    case 'categories': 
        switch($method) {
            case 'GET': 
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
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API resource specified.']);
        break;
}
?>