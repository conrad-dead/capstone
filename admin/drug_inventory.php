<?php
    session_start();
    $current_page = basename($_SERVER['PHP_SELF']);
    $roleOkById = isset($_SESSION['user_role_id']) && in_array((int)$_SESSION['user_role_id'], [1, 2], true);
    $roleName = isset($_SESSION['user_role_name']) ? strtolower(trim($_SESSION['user_role_name'])) : '';
    $roleOkByName = in_array($roleName, ['admin', 'pharmacist', 'pharmacists'], true);
    if (!isset($_SESSION['user_id']) || (!$roleOkById && !$roleOkByName)) {
        header('Location: ../login.php');
        exit();
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Inventory</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
            border-color: #4299e1;
        }
        /* Styles for the tab interface */
        .tab-button {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: #4a5568; /* gray-700 */
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
        }
        .tab-button:hover {
            color: #2d3748; /* gray-900 */
            border-color: #a0aec0; /* gray-400 */
        }
        .tab-button.active {
            color: #2b6cb0; /* blue-700 */
            border-color: #2b6cb0; /* blue-700 */
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <div class="min-h-screen flex">

        <!-- Sidebar-->
        <div class="flex flex-col h-screen bg-gray-800 text-white w-64 text-lg">
            <div class="mb-8 py-4">
                <h1 class="text-3xl font-extrabold text-white tracking-wide leading-none px-6 py-4 border-b border-gray-700">RHU GAMU</h1>
            </div>

            <!--Admin Navigation-->
            <?php include "../includes/navigation.php"?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-4 mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Drug Inventory Management</h1>
            </header>

            <!-- Tab Navigation for Categories and Drugs -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="flex border-b border-gray-200">
                    <button id="drugsTabBtn" class="tab-button active">Manage Drugs</button>
                    <button id="categoriesTabBtn" class="tab-button">Manage Categories</button>
                </div>
            </div>

            <!-- Tab Content for Drugs -->
            <div id="drugsTabContent" class="tab-content active bg-white rounded-lg shadow-xl p-8">
                <h2 id="drugFormTitle" class="text-2xl font-bold text-gray-800 mb-6">Manage Drugs</h2>
                <form id="drugForm" class="space-y-4">
                    <input type="hidden" id="drugId" name="id">
                    <div>
                        <label for="drugName" class="block text-sm font-medium text-gray-700 mb-1">Drug Name</label>
                        <input
                            type="text"
                            id="drugName"
                            name="name"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., Paracetamol 500mg"
                        >
                    </div>
                    <div>
                        <label for="drugCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select
                            id="drugCategory"
                            name="category_id"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Select a category</option>
                            <!-- Categories will be dynamically loaded here -->
                        </select>
                    </div>
                    <div>
                        <label for="drugQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input
                            type="number"
                            id="drugQuantity"
                            name="quantity"
                            required
                            min="0"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., 100"
                        >
                    </div>
                    <div>
                        <label for="drugExpiryDate" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input
                            type="date"
                            id="drugExpiryDate"
                            name="expiry_date"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                    <div class="flex space-x-4">
                        <button
                            type="submit"
                            id="drugSubmitButton"
                            class="flex-1 justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                        >
                            Add Drug
                        </button>
                        <button
                            type="button"
                            id="cancelDrugEditButton"
                            class="hidden flex-1 justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                        >
                            Cancel Edit
                        </button>
                    </div>
                </form>

                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Current Drug Inventory</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="drugTableBody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls for Drugs -->
                <div id="drugPagination" class="flex justify-center items-center space-x-2 mt-6">
                    <!-- Pagination buttons will be rendered here by JavaScript -->
                </div>
            </div>

            <!-- Tab Content for Categories -->
            <div id="categoriesTabContent" class="tab-content bg-white rounded-lg shadow-xl p-8">
                <h2 id="categoryFormTitle" class="text-2xl font-bold text-gray-800 mb-6">Manage Categories</h2>
                <form id="categoryForm" class="space-y-4">
                    <input type="hidden" id="categoryId" name="id">
                    <div>
                        <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                        <input
                            type="text"
                            id="categoryName"
                            name="name"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., Analgesics, Antibiotics"
                        >
                        <span id="name_error" class="text-red-500 text-sm"></span>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            type="submit"
                            id="categorySubmitButton"
                            class="flex-1 justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out"
                        >
                            Add Category
                        </button>
                        <button
                            type="button"
                            id="cancelCategoryEditButton"
                            class="hidden flex-1 justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                        >
                            Cancel Edit
                        </button>
                    </div>
                </form>

                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Existing Categories</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoryTableBody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading categories...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/drug_inventory.js"></script>
    <script src="../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</body>