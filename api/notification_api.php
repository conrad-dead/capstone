<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Check if user is logged in and is a pharmacist
$roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
if (!isset($_SESSION['user_id']) || !in_array($roleName, ['pharmacist','pharmacists'], true)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_notifications':
        getNotifications($pdo);
        break;
    case 'mark_as_read':
        markAsRead($pdo);
        break;
    case 'delete_notification':
        deleteNotification($pdo);
        break;
    case 'clear_all_notifications':
        clearAllNotifications($pdo);
        break;
    case 'create_notification':
        createNotification($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getNotifications($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                n.id,
                n.title,
                n.message,
                n.type,
                n.status,
                n.read_status,
                n.created_at,
                n.drug_id,
                d.name as drug_name
            FROM notifications n
            LEFT JOIN drugs d ON n.drug_id = d.id
            WHERE n.user_id = ? OR n.user_id IS NULL
            ORDER BY n.created_at DESC
            LIMIT 100
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the notifications
        $formattedNotifications = array_map(function($notification) {
            return [
                'id' => $notification['id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'status' => $notification['status'],
                'read' => $notification['read_status'] == 1,
                'created_at' => $notification['created_at'],
                'drug_id' => $notification['drug_id'],
                'drug_name' => $notification['drug_name']
            ];
        }, $notifications);
        
        echo json_encode([
            'success' => true,
            'notifications' => $formattedNotifications
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function markAsRead($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = $input['notification_id'] ?? null;
    
    if (!$notificationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET read_status = 1 
            WHERE id = ? AND (user_id = ? OR user_id IS NULL)
        ");
        
        $result = $stmt->execute([$notificationId, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification not found or already read']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteNotification($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = $input['notification_id'] ?? null;
    
    if (!$notificationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            DELETE FROM notifications 
            WHERE id = ? AND (user_id = ? OR user_id IS NULL)
        ");
        
        $result = $stmt->execute([$notificationId, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function clearAllNotifications($pdo) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM notifications 
            WHERE user_id = ? OR user_id IS NULL
        ");
        
        $result = $stmt->execute([$_SESSION['user_id']]);
        
        echo json_encode(['success' => true, 'message' => 'All notifications cleared successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createNotification($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = $input['title'] ?? '';
    $message = $input['message'] ?? '';
    $type = $input['type'] ?? 'info';
    $status = $input['status'] ?? 'active';
    $drugId = $input['drug_id'] ?? null;
    $userId = $input['user_id'] ?? $_SESSION['user_id'];
    
    if (!$title || !$message) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title and message are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, status, drug_id, user_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$title, $message, $type, $status, $drugId, $userId]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Notification created successfully',
                'notification_id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create notification']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Helper function to create system notifications
function createSystemNotification($pdo, $title, $message, $type = 'info', $drugId = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, status, drug_id, user_id, created_at) 
            VALUES (?, ?, ?, 'active', ?, NULL, NOW())
        ");
        
        return $stmt->execute([$title, $message, $type, $drugId]);
        
    } catch (PDOException $e) {
        error_log("Error creating system notification: " . $e->getMessage());
        return false;
    }
}

// Helper function to create user-specific notifications
function createUserNotification($pdo, $userId, $title, $message, $type = 'info', $drugId = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, status, drug_id, user_id, created_at) 
            VALUES (?, ?, ?, 'active', ?, ?, NOW())
        ");
        
        return $stmt->execute([$title, $message, $type, $drugId, $userId]);
        
    } catch (PDOException $e) {
        error_log("Error creating user notification: " . $e->getMessage());
        return false;
    }
}
?>
