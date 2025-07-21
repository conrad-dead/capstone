<?php

//session_start();

require_once '../db/conn.php';

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed' . $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';



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
                } else if ($password !== $confirm_password) {
                    echo json_encode(['success' => false, 'message' => 'Password do not match']);
                } else if (strlen($password) < 6) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
                } else if ($role_name === 'bhw' && empty($barangay)) {
                    echo json_encode(['success' => false, 'message' => 'Barangay is required for BHW users']);
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    //insert data to the database
                    $sql = "INSERT INTO users (username, password, role_id, barangay, contact_number) VALUE (?, ?, ?, ?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ssiss", $username, $password, $role_id, $barangay, $contact_number);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true, 'message' => "User '{$username}' created successfully"]);
                        } else {
                            if ($conn->error == 1062) {
                                echo json_encode(['success' => false, 'message' => "Username '{$username}' already exists. Please try again"]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error creating user: '.$stmt->error]);
                            }
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement. ' .$conn->error]);
                    }
                }
                break;
            
            case 'PUT':
                
        }
}

?>