<?php

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
}



?>