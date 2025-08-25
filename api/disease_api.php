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
    case 'diseases':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getDiseases();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    case 'disease':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            getDisease();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            createDisease();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            updateDisease();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deleteDisease();
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid resource']);
        break;
}

function getDiseases() {
    global $conn;
    
    try {
        $sql = 'SELECT id, name, description FROM disease ORDER BY name ASC';
        $result = $conn->query($sql);
        
        if ($result) {
            $diseases = [];
            while ($row = $result->fetch_assoc()) {
                $diseases[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $diseases]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch diseases: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getDisease() {
    global $conn;
    
    $disease_id = $_GET['id'] ?? null;
    if (!$disease_id) {
        echo json_encode(['success' => false, 'message' => 'Disease ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('SELECT id, name, description FROM disease WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $disease_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $disease = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $disease]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Disease not found']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function createDisease() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    if (empty($input['name'])) {
        echo json_encode(['success' => false, 'message' => 'Disease name is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('INSERT INTO disease (name, description) VALUES (?, ?)');
        
        if ($stmt) {
            $stmt->bind_param('ss', $input['name'], $input['description'] ?? '');
            
            if ($stmt->execute()) {
                $disease_id = $conn->insert_id;
                echo json_encode(['success' => true, 'message' => 'Disease created successfully', 'data' => ['id' => $disease_id]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create disease: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateDisease() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $disease_id = $_GET['id'] ?? null;
    
    if (!$disease_id || !$input) {
        echo json_encode(['success' => false, 'message' => 'Disease ID and update data are required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('UPDATE disease SET name = ?, description = ? WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('ssi', $input['name'], $input['description'] ?? '', $disease_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Disease updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update disease: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteDisease() {
    global $conn;
    
    $disease_id = $_GET['id'] ?? null;
    if (!$disease_id) {
        echo json_encode(['success' => false, 'message' => 'Disease ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare('DELETE FROM disease WHERE id = ?');
        
        if ($stmt) {
            $stmt->bind_param('i', $disease_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Disease deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete disease: ' . $stmt->error]);
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
