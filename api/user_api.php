<?php

session_start();

require_once '../db/conn.php';

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed' . $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';

// Basic auth guard: require login and admin for write operations
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

function is_admin(): bool {
    return isset($_SESSION['user_role_id']) && (int)$_SESSION['user_role_id'] === 1;
}



// function for getting the role
function getRoleNameById($conn, $role_id) {
    $sql = "SELECT name FROM roles WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['name'] : null;
    }
    return null;
}



switch ($resource) {
    case 'users':
        switch($method) {
            case 'GET':
                //list of users
                $users = [];
                //join with role table to get role
                $sql = 'SELECT u.id, u.username, r.name AS role_name, r.id AS role_id, u.barangay, u.contact_number, u.created_at 
                        FROM users u 
                        JOIN roles r ON u.role_id = r.id
                    ORDER BY u.username ASC';

                if ($result = $conn->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        $row['role'] = $row['role_name'];
                        $users[] = $row;    
                    }
                    $result->free();
                    echo json_encode(['success' => true, 'data' => $users]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error Fetching users' . $conn->error]);
                }
                break;
            
            case 'POST':
                require_login();
                if (!is_admin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                //only allow admin to create users!
                // if (!isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403); // Forbidden
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrators can create users.']);
                //     break;
                // }

                $input = json_decode(file_get_contents('php://input'), true);

                $username = trim($input['username']);
                $password = $input['password'];
                $confirm_password = $input['confirm_password'] ?? '';
                $role_id = intval($input['role_id'] ?? 0);
                $contact_number = trim($input['contact_number'] ?? '');

                $role_name = getRoleNameById($conn, $role_id);
                $barangay = ($role_name === 'bhw') ? (trim($input['barangay'] ?? '') ?: NULL) : NULL;

                if (empty($username) || empty($password) || empty($confirm_password) || $role_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'All fields are required']);
                } elseif ($password !== $confirm_password) {
                    echo json_encode(['success' => false, 'message' => 'Password do not match']);
                } elseif (strlen($password) < 6) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
                } elseif ($role_name === 'bhw' && empty($barangay)) {
                    echo json_encode(['success' => false, 'message' => 'Barangay is required for BHW users']);
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    try {
                        //insert data to the database
                        $sql = "INSERT INTO users (username, password, role_id, barangay, contact_number) VALUES (?, ?, ?, ?, ?)";

                        if ($stmt = $conn->prepare($sql)) {
                            // palitan mo nang hashed_passowrd yun password!
                            $stmt->bind_param("ssiss", $username, $hashed_password, $role_id, $barangay, $contact_number);
                            if ($stmt->execute()) {
                                echo json_encode(['success' => true, 'message' => "User '{$username}' created successfully"]);
                            } else {
                                if ($conn->error == 1062) {
                                    echo json_encode(['success' => false, 'message' => "Username '{$username}' already exists. Please try again"]);
                                } else {
                                    echo json_encode(['success' => false, 'message' => 'Error creating user: ']);
                                }
                            }
                            $stmt->close();
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ']);
                        }

                    } catch  (mysqli_sql_exception $e){
                        if (str_contains($e->getMessage(), 'Duplicate entry')) {
                            echo json_encode(['success' => false, 'message' => "Username '{$username}' already exists"]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Database error: '. $e->getMessage()]);
                        }
                    }
                }
                break;
            
            case 'PUT':
                require_login();
                if (!is_admin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                
                //only admin can update user
                // if (isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403);
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrator can update user']);
                //     break
                // }

                $input = json_decode(file_get_contents('php://input'), true);

                $user_id = intval($input['id'] ?? 0);
                $username = trim($input['username'] ?? '');
                $password = $input['password'] ?? '';
                $confirm_password = $input['confirm_password'] ?? '';
                $role_id = intval($input['role_id'] ?? 0);
                $contact_number = trim($input['contact_number'] ?? '');

                $role_name = getRoleNameById($conn, $role_id);
                $barangay = ($role_name === 'bhw') ? (trim($input['barangay'] ?? '') ?: NULL) : NULL;

                if ($user_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid user id for update']);
                } elseif (empty($username) || $role_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Username and role are required']);
                } elseif (!empty($password) && $password !== $confirm_password)  {
                    echo json_encode(['success' => false, 'message' => 'Password does not match']);
                } elseif (!empty($password) && strlen($password) < 6) {
                    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 character']);
                } elseif ($role_name === 'bhw' && empty($barangay)) {
                    echo json_encode(['success' => false, 'message' => 'Barangay is required for BHW users']);
                } else {
                    $update_fields = "username = ?, role_id = ?, barangay = ?, contact_number = ?";
                    $bind_types = "siss";
                    $bind_params = [$username, $role_id, $barangay, $contact_number];

                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_fields .= ", password = ?";
                        $bind_types .= "s";
                        $bind_params[] = $hashed_password;
                    }

                    $update_fields .= " WHERE id = ?";
                    $bind_types .= "i";
                    $bind_params[] = $user_id;


                    $sql = "UPDATE users SET $update_fields";

                    if ($stmt = $conn->prepare($sql)) {

                        $refs = [];
                        foreach ($bind_params as $key => $value) {
                            $refs[$key] = &$bind_params[$key];
                        }
                        call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params));

                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "User '{$username}' (ID: {$user_id}) Updated successfully"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "No changes made or user (ID: {$user_id}) Not found"]);
                            }
                        } else {
                            if ($conn->errno == 1062) {
                                echo json_encode(['success' => false, 'message' => "Username '{$username}' already exist. Please choose a different one"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error updating user. ' .$stmt->error]);
                            }
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                    }
                }
                break;
            
            case 'DELETE':
                require_login();
                if (!is_admin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); break; }
                // Only allow 'admin' to delete users
                // if (!isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403); // Forbidden
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrators can delete users.']);
                //     break;
                // }                
            
                $input = json_decode(file_get_contents('php://input'), true);
                $user_id = intval($input['id'] ?? 0);

                if ($user_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid user ID for deletion']);
                } else {
                    $sql = "DELETE FROM users WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param('i', $user_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "User (ID: {$user_id}) deleted successfully"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "User (ID: {$user_id}) not found  or already deleted"]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare delete statement']);
                    }
                }
                break;
            
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not allowed for users']);
            break;
        }
    break;

    case 'roles':
        switch ($method) {
            case 'GET':
                // list of roles
                $roles = [];
                // Some databases may not have created_at column on roles; only select existing columns
                $sql = "SELECT id, name, description FROM roles ORDER BY name ASC";
                if ($result = $conn->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        $roles[] = $row;
                    }
                    $result->free();
                    echo json_encode(['success' => true, 'data' => $roles]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error Fetching roles' . $conn->error]);
                }
            break;

            case 'POST':
                //only admin can create a new role
                // if (!isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403);
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrator can create roles']);
                //     break;
                // }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $name = trim($input['name']);
                $description = trim($input['description']);

                if (empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Role name is required']);
                } else {
                    $sql = "INSERT INTO roles (name, description) VALUES (?,?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ss", $name, $description);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true, 'message' => "Role '{$name}' created successfully!"]);
                        } else {
                            if ($conn->error == 1062) {
                                echo json_encode(['success' => false, 'message' => "Role '{$name}' already exist."]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error creating role: ' . $stmt->error]);
                            }
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' . $conn->error]);
                    }
                }
                break;
            
            case 'PUT':
                //only admin can update role
                // if (!isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403); // Forbidden
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrators can update roles.']);
                //     break;
                // }

                $input = json_decode(file_get_contents('php://input'), true);
                $role_id = intval($input['id'] ?? 0);
                $name = trim($input['name']);
                $description = trim($input['description']);

                if ($role_id <= 0 || empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Role id and name are required']);
                } else {
                    $sql = "UPDATE roles SET name = ?, description = ? WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ssi", $name, $description, $role_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Role (ID: {$role_id}) update to '{$name}' successfully"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "No changes made or role (ID: {$role_id}) not found"]);
                            }
                        } else {
                            if ($conn->error == 1062) {
                                echo json_encode(['success' => false, 'message' => "Role '{$name}' already exist"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error Updating role: '. $stmt->error]);
                            }
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare update statement.' . $conn->error]);
                    }
                }
                break;
            
            case 'DELETE':
                //only admin can delete role
                // if (!isAdmin($conn, $authenticated_user_role_id)) {
                //     http_response_code(403); // Forbidden
                //     echo json_encode(['success' => false, 'message' => 'Access Denied: Only administrators can delete roles.']);
                //     break;
                // }

                $input = json_decode(file_get_contents('php://input'), true);
                $role_id = intval($input['id'] ?? 0);
                
                if ($role_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid role id']);
                } else {
                    // check if any users are assigned to this role 
                    $sql_check_users = "SELECT COUNT(*) AS user_count FROM users WHERE role_id = ?";
                    if ($stmt_check = $conn->prepare($sql_check_users)) {
                        $stmt_check->bind_param("i", $role_id);
                        $stmt_check->execute();
                        $check_result = $stmt_check->get_result()->fetch_assoc();
                        $stmt_check->close();

                        if ($check_result['user_count'] > 0) {
                            echo json_encode(['success' => false, 'message' => "Cannot delete role (ID: {$role_id}) because it has {$check_result['user_count']} assigned users. Reassign first! "]);
                            break;
                        }
                    }

                    $sql = "DELETE FROM roles WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $role_id);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(['success' => true, 'message' => "Role (ID: {$role_id}) deleted successfully"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => "Role (ID: {$role_id}) not found or already deleted."]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error deleting role: '. $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare delete statement. ' . $conn->error]);
                    }
                }
                break;
            
            default:
                http_response_code(405); 
                echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
                break;
           
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API resource specified.']);
        break;
}

$conn->close();
exit();

?>