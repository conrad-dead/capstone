<?php


    session_start();
    $current_page = basename($_SERVER['PHP_SELF']);
    // if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //     header('Location: ../login.php');
    //     exit();
    // }

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h3 class="text-lg font-semibold text-gray-700">Barangay Count</h3>
                        <p class="mt-2 text-3xl font-bold text-blue-600" id="barangayCount">0</p>
                    </div>
                </div>
                

            <!-- User Section -->
            <section class="bg-white p-6 rounded-xl shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Users</h3>
                    <button id="openModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700"> Add user</button>
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
                                
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- drug Section -->
            <section class="bg-white p-6 rounded-xl shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Medicine</h3>
                    <button id="openModalDrug" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700"> Add Drug</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-3 text-left text-gray-700">#</th>
                                <th class="p-3 text-left text-gray-700">Name</th>
                                <th class="p-3 text-left text-gray-700">Category</th>
                                <th class="p-3 text-left text-gray-700">QUANTITY</th>
                                <th class="p-3 text-left text-gray-700">EXPIRY DATE</th>
                                <th class="p-3 text-left text-gray-700">ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="drugTableBody">
                            <tr class="border-t">
                                
                            </tr>
                            
                        </tbody>
                    </table>
                </div>

                <div id="drugPagination" class="flex justify-center items-center space-x-2 mt-6">
                    <!-- Pagination buttons will be rendered here by JavaScript -->
                </div>
            </section>

            <!-- Modal for user-->
            <div id="modalUser" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
                <!-- Modal Content -->
                <div class="bg-white rounded-2xl shadow-lg w-96 p-6 relative">
                
                    <!-- Close Button -->
                    <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl">&times;</button>
                    
                    <h2 class="text-2xl font-semibold mb-4">Add New User</h2>
                    
                    <form class="space-y-4" id="userForm">
                        <!-- Username -->
                        <div>
                            <label class="block text-gray-700">Username</label>
                            <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Enter username" required>
                        </div>
                        
                        <!-- Password -->
                        <div>
                            <label class="block text-gray-700">Password</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Enter password" required>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-gray-700">Confirm Password</label>
                            <input type="password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Confirm password" required>
                        </div>

                        <!-- User Role -->
                        <div>
                            <label class="block text-gray-700">User Role</label>
                            <select name="role" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Nurse">Nurse</option>
                                <option value="Health Worker">Health Worker</option>
                                <option value="Clerk">Clerk</option>
                            </select>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-gray-700">Contact Number</label>
                            <input type="text" name="contact" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Enter contact number" required>
                        </div>

                        <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Submit
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal for Drug-->
            <div id="modalDrug" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
                <!-- Modal Content -->
                <div class="bg-white rounded-2xl shadow-lg w-96 p-6 relative">
                
                <!-- Close Button -->
                <button id="closeModalDrug" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl">&times;</button>
                
                <h2 class="text-2xl font-semibold mb-4">Popup Form</h2>
                
                <form class="space-y-4">
                    <div>
                    <label class="block text-gray-700">Name</label>
                    <input type="text" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your name">
                    </div>

                    <div>
                    <label class="block text-gray-700">Email</label>
                    <input type="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your email">
                    </div>

                    <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Submit
                    </button>
                </form>
                </div>
            </div>

            </main>
        </div>
    </div>
    
    <script src="../js/admin_dashboard.js"></script>
    <script src="../js/user_management.js"></script>

    <script>
        const modalUser = document.getElementById("modalUser");
        const openModalUser = document.getElementById("openModal");
        const closeModalUser = document.getElementById("closeModal");

        openModalUser.addEventListener("click", () => {
        modalUser.classList.remove("hidden");
        });

        closeModalUser.addEventListener("click", () => {
        modalUser.classList.add("hidden");
        });

        window.addEventListener("click", (e) => {
        if (e.target === modalUser) {
            modalUser.classList.add("hidden");
        }
        });

        // Drug Modal
        const modalDrug = document.getElementById('modalDrug');
        const openModalDrug = document.getElementById('openModalDrug');
        const closeModalDrug = document.getElementById('closeModalDrug');

        openModalDrug.addEventListener('click', () => {
        modalDrug.classList.remove('hidden');
        });

        closeModalDrug.addEventListener('click', () => {
        modalDrug.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
        if (e.target === modalDrug) {
            modalDrug.classList.add('hidden');
        }
        });

        // //for deleting the user in the admin dashboard 
        // const userTableBody = document.getElementById('userTableBody');

        // if (userTableBody) {
        //     userTableBody.addEventListener('click', async (event) => {
        //         if (event.target.classList.contains('delete-user-btn')) {
        //             event.preventDefault();
        //             const userIdToDelete = event.target.dataset.userId;

        //             const confirmed = await showConfirm(`Are you sure you want to delete this User ID: ${userIdToDelete}`);

        //             if (confirmed) {
        //                 try {
        //                     const response = await fetch('../api/user_api.php?resource=users', {
        //                         method: 'DELETE',
        //                         headers: {'Content-Type': 'application/json'},
        //                         body: JSON.stringify({id: userIdToDelete})
        //                     });

        //                     const result = await response.json();

        //                     if (result) {
        //                         displayMessage(result.message, 'success');
        //                     } else {
        //                         displayMessage(result.message, 'error');
        //                     }
        //                 } catch (error) {
        //                     console.error('Error deleting user: ', error);
        //                     displayMessage('Failed to delete user due to network error');
                            
        //                 }
        //             }
        //         }
        //     });
        // }

        // async function showConfirm(message) {
        //     const result = await Swal.fire({
        //         title: 'Are you sure?',
        //         text: message,
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Yes, proceed!'
        //     });
        //     return result.isConfirmed;
        // }

        // function displayMessage(message, type) {
        //     Swal.fire({
        //         icon: type,
        //         title: (type === 'success' ? 'Success!' : 'Error!'),
        //         text: message,
        //         confirmButtonText: 'OK'
        //     });
        // }
    </script>
</body>
</html>