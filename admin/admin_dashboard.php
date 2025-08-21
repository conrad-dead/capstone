<?php


    session_start();
    $current_page = basename($_SERVER['PHP_SELF']);
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role_id']) || (int)$_SESSION['user_role_id'] !== 1) {
        header('Location: ../login.php');
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js for statistics -->
    <!-- Para sa Chart! -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Custom styles for Inter font and smoother transitions */
        body {
            font-family: 'Inter', sans-serif;
        }
        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); /* Blue-500 equivalent focus ring */
            border-color: #4299e1; /* Blue-500 */
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen flex">

        <!-- Sidebar -->
       <div class="flex flex-col h-screen bg-gray-800 text-white w-64 text-lg">
            <div class="mb-8 py-4">
                <h1 class="text-3xl font-extrabold text-white tracking-wide leading-tight leading-none px-6 py-4 border-b border-gray-700">RHU GAMU</h1>
            </div>

            <!--Admin Navigation-->
            <?php include '../includes/navigation.php'; ?>
            
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-4 mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Overview</h1>
            </header>

            <main class="p-6 space-y-10">
            <!-- Add charts, tables, etc. -->

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h3 class="text-lg font-semibold text-gray-700">RHU Team</h3>
                        <p class="mt-2 text-3xl font-bold text-blue-600" id="user-count">120</p>
                    </div>
                </div>

            <!-- User Section -->
            <section class="bg-white p-6 rounded-xl shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Users</h3>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add User</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-3 text-left text-gray-700">#</th>
                                <th class="p-3 text-left text-gray-700">Name</th>
                                <th class="p-3 text-left text-gray-700">Role</th>
                                <th class="p-3 text-left text-gray-700">Created</th>
                                <th class="p-3 text-left text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <tr class="border-t">
                                <td class="p-3">1</td>
                                <td class="p-3">Juan Dela Cruz</td>
                                <td class="p-3">juan@example.com</td>
                                <td class="p-3">Admin</td>
                                <td class="p-3">
                                    <button class="text-blue-600 hover:underline">Edit</button>
                                    <button class="text-red-600 hover:underline ml-2">Delete</button>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </section>
            </main>
        </div>
    </div>
    
    <script src="../js/admin_dashboard.js"></script>
</body>
</html>