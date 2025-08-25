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
    case 'barangays':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getBarangays();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    case 'barangay':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getBarangay();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            createBarangay();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            updateBarangay();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deleteBarangay();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    default:
        // Default behavior: return count (for backward compatibility)
        getBarangayCount();
        break;
}

function getBarangayCount() {
    global $conn;
    
    try {
        $sql = "SELECT COUNT(*) AS count FROM barangay_table";
        $result = $conn->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['success' => true, 'count' => $row['count']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getBarangays() {
    global $conn;
    
    try {
        $sql = 'SELECT id, barangay_name FROM barangay_table ORDER BY barangay_name ASC';
        $result = $conn->query($sql);
        
        if ($result) {
            $barangays = [];
            while ($row = $result->fetch_assoc()) {
                $barangays[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $barangays]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch barangays: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getBarangay() {
    global $conn;
    
    $barangay_id = $_GET['id'] ?? null;
    if (!$barangay_id) {
        echo json_encode(['success' => false, 'message' => 'Barangay ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('SELECT id, barangay_name FROM barangay_table WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $barangay_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $barangay = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $barangay]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Barangay not found']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function createBarangay() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    if (empty($input['barangay_name'])) {
        echo json_encode(['success' => false, 'message' => 'Barangay name is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('INSERT INTO barangay_table (barangay_name) VALUES (?)');
        
        if ($stmt) {
            $stmt->bind_param('s', $input['barangay_name']);
            
            if ($stmt->execute()) {
                $barangay_id = $conn->insert_id;
                echo json_encode(['success' => true, 'message' => 'Barangay created successfully', 'data' => ['id' => $barangay_id]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create barangay: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateBarangay() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $barangay_id = $_GET['id'] ?? null;
    
    if (!$barangay_id || !$input) {
        echo json_encode(['success' => false, 'message' => 'Barangay ID and update data are required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('UPDATE barangay_table SET barangay_name = ? WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('si', $input['barangay_name'], $barangay_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Barangay updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update barangay: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteBarangay() {
    global $conn;
    
    $barangay_id = $_GET['id'] ?? null;
    if (!$barangay_id) {
        echo json_encode(['success' => false, 'message' => 'Barangay ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('DELETE FROM barangay_table WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $barangay_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Barangay deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete barangay: ' . $stmt->error]);
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