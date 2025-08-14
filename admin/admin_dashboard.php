<?php
    $current_page = basename($_SERVER['PHP_SELF']);
    session_start();
    if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('location: ../login.php');
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
    <div class="min-h-screen flex">

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

            <main class="p-6">
            <!-- Add charts, tables, etc. -->
            </main>
        </div>
    </div>    
</body>
</html>