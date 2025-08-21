<?php
    session_start();
    $current_page = basename($_SERVER['PHP_SELF']);
    // $roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
    // if (!isset($_SESSION['user_id']) || !in_array($roleName, ['pharmacist','pharmacists'], true)) {
    //     header('Location: ../login.php');
    //     exit();
    // }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
            border-color: #4299e1;
        }
    </style>
    </head>
<body>
    <div class="min-h-screen flex bg-gray-100">
        <!-- Sidebar -->
        <div class="flex flex-col h-screen bg-gray-800 text-white w-64 text-lg">
            <div class="mb-8 py-4">
                <h1 class="text-3xl font-extrabold text-white tracking-wide leading-none px-6 py-4 border-b border-gray-700">RHU GAMU</h1>
            </div>
            <nav class="flex-1 overflow-y-auto">
                <div class="px-2 py-4 space-y-1">
                    <div class="flex items-center space-x-2 py-2.5 px-4 rounded bg-gray-900 text-white">
                        <span>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    <div class="flex items-center space-x-2 py-2.5 px-4 rounded text-gray-300">
                        <span>Pharmacist Dashboard</span>
                    </div>
                </div>
            </nav>
            <div class="px-2 border-t border-gray-700">
                <a href="../logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-4 mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Medicine Distribution Center</h1>
            </header>

            <main class="p-6 space-y-10">
                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="flex border-b border-gray-200">
                        <button id="tabDistributeBtn" class="tab-button active px-4 py-2 font-semibold text-gray-700 border-b-2 border-blue-600">Distribute Medicine</button>
                        <button id="tabReportsBtn" class="tab-button px-4 py-2 font-semibold text-gray-700">Reports</button>
                        <button id="tabManagementBtn" class="tab-button px-4 py-2 font-semibold text-gray-700">Management</button>
                    </div>
                </div>

                <!-- Distribute Medicine - Now Priority Tab -->
                <section id="tabDistribute" class="bg-white rounded-lg shadow-xl p-8 tab-content active">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Distribute Medicine</h2>
                    <form id="distributionForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="distDrugSelect">Drug</label>
                            <select id="distDrugSelect" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                <option value="">Select drug</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="distQuantity">Quantity</label>
                                <input type="number" id="distQuantity" min="1" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="distRecipient">Recipient (optional)</label>
                                <input type="text" id="distRecipient" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Patient name / notes">
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="w-full md:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-lg transition duration-150 ease-in-out">Record Distribution</button>
                        </div>
                    </form>
                    
                    <!-- Recent Distributions -->
                    <div class="mt-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Distributions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Drug</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                                    </tr>
                                </thead>
                                <tbody id="distTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No records</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="distPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                    </div>
                </section>

                <!-- Reports -->
                <section id="tabReports" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Distribution Reports</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Top Distributed Drugs - This Month</h3>
                            <canvas id="chartTopMonth" height="200"></canvas>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Top Distributed Drugs - This Year</h3>
                            <canvas id="chartTopYear" height="200"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Management Tab - Moved from Priority -->
                <section id="tabManagement" class="bg-white rounded-lg shadow-xl p-8 tab-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Drug Inventory Management</h2>
                    
                    <!-- Manage Drugs Form -->
                    <div class="mb-8">
                        <h3 id="drugFormTitle" class="text-xl font-semibold text-gray-800 mb-4">Add/Edit Drug</h3>
                        <form id="drugForm" class="space-y-4">
                            <input type="hidden" id="drugId" name="id">
                            <div>
                                <label for="drugName" class="block text-sm font-medium text-gray-700 mb-1">Drug Name</label>
                                <input type="text" id="drugName" name="name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., Paracetamol 500mg">
                            </div>
                            <div>
                                <label for="drugCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select id="drugCategory" name="category_id" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select a category</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="drugQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" id="drugQuantity" name="quantity" required min="0" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., 100">
                                </div>
                                <div>
                                    <label for="drugExpiryDate" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                    <input type="date" id="drugExpiryDate" name="expiry_date" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="flex space-x-4">
                                <button type="submit" id="drugSubmitButton" class="flex-1 justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">Add Drug</button>
                                <button type="button" id="cancelDrugEditButton" class="hidden flex-1 justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">Cancel Edit</button>
                            </div>
                        </form>
                    </div>

                    <!-- Drug Inventory Table -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Current Drug Inventory</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="drugTableBody" class="bg-white divide-y divide-gray-200">
                                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="drugPagination" class="flex justify-center items-center space-x-2 mt-6"></div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/pharmacists_dashboard.js"></script>
</body>
</html>
