<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Create audit_trail table if it doesn't exist
function createAuditTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `audit_trail` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `username` varchar(255) NOT NULL,
        `action` varchar(255) NOT NULL,
        `details` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_action` (`action`),
        KEY `idx_timestamp` (`timestamp`),
        CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    return $conn->query($sql);
}

// Create the audit table
createAuditTable($conn);

// Basic auth guard
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }
}

switch($method) {
    case 'POST':
        require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $action = trim($input['action'] ?? '');
        $details = trim($input['details'] ?? '');
        $user_id = intval($_SESSION['user_id']);
        $username = $_SESSION['username'] ?? 'Unknown';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($action)) {
            echo json_encode(['success' => false, 'message' => 'Action is required']);
            break;
        }
        
        $sql = "INSERT INTO audit_trail (user_id, username, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssss", $user_id, $username, $action, $details, $ip_address, $user_agent);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Audit trail recorded successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error recording audit trail: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement']);
        }
        break;
        
    case 'GET':
        require_login();
        
        // Get audit trail with pagination and filters
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = ($page - 1) * $limit;
        $user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $action_filter = isset($_GET['action']) ? trim($_GET['action']) : '';
        $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
        
        // Build WHERE clause
        $where_conditions = [];
        $params = [];
        $types = '';
        
        if ($user_filter > 0) {
            $where_conditions[] = "user_id = ?";
            $params[] = $user_filter;
            $types .= 'i';
        }
        
        if (!empty($action_filter)) {
            $where_conditions[] = "action LIKE ?";
            $params[] = "%$action_filter%";
            $types .= 's';
        }
        
        if (!empty($date_from)) {
            $where_conditions[] = "DATE(timestamp) >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if (!empty($date_to)) {
            $where_conditions[] = "DATE(timestamp) <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM audit_trail $where_clause";
        $total_records = 0;
        
        if ($stmt = $conn->prepare($count_sql)) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $total_records = $row['total'];
            }
            $stmt->close();
        }
        
        // Get audit records
        $sql = "SELECT * FROM audit_trail $where_clause ORDER BY timestamp DESC LIMIT ? OFFSET ?";
        $audit_records = [];
        
        if ($stmt = $conn->prepare($sql)) {
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $audit_records[] = $row;
            }
            $stmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'data' => $audit_records,
            'total_records' => $total_records,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_records / $limit)
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>
