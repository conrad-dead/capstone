<?php
session_start();

require "db/conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password.";
    } else {
        $sql = "SELECT u.id, u.username, u.password, u.role_id, r.name AS role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $db_username, $hashed_password, $role_id, $role_name);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    // Password is correct, start a new session
                    session_regenerate_id(true); // Regenerate session ID for security
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['user_role_id'] = (int)$role_id; // Store role ID in session
                    $_SESSION['user_role_name'] = $role_name; // Store role name in session


                    // redirect to the respective dashboard based on role name
                    $role_name_lc = strtolower(trim($role_name));
                    if ($role_name_lc === 'admin') {
                        header('Location: admin/admin_dashboard.php');
                        exit();
                    }
                    // Clinician and medical staff go to clinician dashboard
                    if (in_array($role_name_lc, ['clinician','doctor','nurse','midwife','other'], true)) {
                        header('Location: clinician/clinician_dashboard.php');
                        exit();
                    }
                    // Pharmacy roles go to pharmacist dashboard
                    if (in_array($role_name_lc, ['pharmacy','pharmacist','pharmacists'], true)) {
                        header('Location: pharmacists/pharmacists_dashboard.php');
                        exit();
                    }
                    if ($role_name_lc === 'bhw') {
                        header('Location: bhw/bhw_dashboard.php');
                        exit();
                    }
                    // fallback: send unknown roles to login
                    header('Location: login.php');
                    exit();

                    
                    

                    // Redirect to a protected page (e.g., user management)
                    // header("Location: admin/user_management.php");
                    // exit();
                } else {
                    $login_error = "Invalid username or password.";
                }
            } else {
                $login_error = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $login_error = "Database error: Could not prepare statement.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RHU Gamu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
            border-color: #4299e1;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">RHU GAMU Login</h2>
        
        <?php if (!empty($login_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $login_error; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Enter your username"
                >
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Enter your password"
                >
            </div>
            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                >
                    Login
                </button>
            </div>
        </form>
    </div>
</body>
</html>
