 <?php
header('Content-Type: application/json');
include_once "../db/conn.php";


if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}


header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];



switch($method) {
    case 'POST':
        $input_data = json_decode(file_get_contents('php://input'), true);

        if($input_data == null) {
            echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON input.']);
            exit();
        }

        $first_name = trim($input_data['first_name']);
        $last_name = trim($input_data['last_name']);
        $password = trim($input_data['password']);
        $confirm_password = trim($input_data['confirm_password']);
        $role = $input_data['role'];

        //get barangay only if the bhw is selected
        if($role === 'bhw') {
            $barangay = trim($input_data['barangay']);
        } else {
            $barangay = null;
        }

        //validation
        // if (empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password) || empty($role)){
        //     echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        //     exit();
        // }

        // //check password
        // if($password !== $confirm_password){
        //     echo json_encode(['success' => false, 'message' => 'Password do not much']);
        //     exit();
        // }

        // if($password < 6) {
        //     echo json_encode(['success' => false, 'message' => 'Password must be at least 6 character long.']);
        //     exit();
        // } 

        // if($role === 'bhw' && empty($barangay)){
        //     echo json_encode(['success' => false, 'message' => 'Barangay is required for BHW users.']);
        //     exit();
        // }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        //add in the data base

        //---------Todo--------------------
        // add data into the data base
        $sql = "INSERT INTO clinicians (first_name, last_name, role, contact_number, barangay_id) VALUE (?, ?, ?, ?, ?)";

        echo json_encode(['success' => true, 'message' => 'User created (dummy response)']);
        exit();
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // Example: validate and save to DB...

//     echo json_encode([
//         'success' => true,
//         'message' => 'User created successfully.'
//     ]);
//     exit;
// }

// echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>