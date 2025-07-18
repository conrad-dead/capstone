<?php
    $current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom styles for Inter font and smoother transitions */
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
        /* Styles for the barangay search dropdown */
        .autocomplete-results {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: absolute;
            width: calc(100% - 1rem);
            z-index: 50;
            margin-top: 0.25rem;
        }
        .autocomplete-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #edf2f7;
        }
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        .autocomplete-item:hover {
            background-color: #f7fafc;
            color: #2b6cb0;
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
                <h1 class="text-2xl font-semibold text-gray-800">User Management</h1>
            </header>

            <!-- Tab Navigation for Users and Roles -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="flex border-b border-gray-200">
                    <button id="usersTabBtn" class="tab-button active">Manage Users</button>
                    <button id="rolesTabBtn" class="tab-button">Manage Roles</button>
                </div>
            </div>

            <!-- Tab Content for Users -->
            <div id="usersTabContent" class="tab-content active bg-white rounded-lg shadow-xl p-8">
                <h2 id="userFormTitle" class="text-2xl font-bold text-gray-800">Create User</h2>
                <form id="userForm" class="space-y-6">
                    <!-- Hidden field to store user ID when editing -->
                    <input type="hidden" id="userId" name="id">

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Enter username"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Enter password (leave blank to keep current)"
                        >
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Confirm password"
                        >
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">User Role</label>
                        <select
                            id="role"
                            name="role_id" <!-- Changed name to role_id -->
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Select role</option>
                            <!-- Roles will be dynamically loaded here -->
                        </select>
                    </div>

                    <!-- New: Contact Number Field -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input
                            type="text"
                            id="contact_number"
                            name="contact_number"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., +639123456789"
                        >
                    </div>

                    <!-- Barangay field and autocomplete container -->
                    <div id="barangayField" class="hidden relative">
                        <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                        <input
                            type="text"
                            id="barangay"
                            name="barangay"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Search or select barangay"
                            autocomplete="off"
                        >
                        <div id="barangaySearchResults" class="autocomplete-results hidden">
                            <!-- Search results will be dynamically inserted here -->
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button
                            type="submit"
                            id="submitButton"
                            class="flex-1 justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                        >
                            Create User
                        </button>
                        <button
                            type="button"
                            id="cancelEditButton"
                            class="hidden flex-1 justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                        >
                            Cancel Edit
                        </button>
                    </div>
                </form>

                <!--Table for displaying users-->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Existing Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Username
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact Number
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Barangay
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created At
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- User rows will be dynamically inserted here by JavaScript -->
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content for Roles -->
            <div id="rolesTabContent" class="tab-content bg-white rounded-lg shadow-xl p-8">
                <h2 id="roleFormTitle" class="text-2xl font-bold text-gray-800 mb-6">Manage Roles</h2>
                <form id="roleForm" class="space-y-4">
                    <input type="hidden" id="roleManageId" name="id">
                    <div>
                        <label for="roleManageName" class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                        <input
                            type="text"
                            id="roleManageName"
                            name="name"
                            required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., Nurse, Administrator"
                        >
                    </div>
                    <div>
                        <label for="roleManageDescription" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea
                            id="roleManageDescription"
                            name="description"
                            rows="3"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Brief description of the role"
                        ></textarea>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            type="submit"
                            id="roleSubmitButton"
                            class="flex-1 justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out"
                        >
                            Add Role
                        </button>
                        <button
                            type="button"
                            id="cancelRoleEditButton"
                            class="hidden flex-1 justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                        >
                            Cancel Edit
                        </button>
                    </div>
                </form>

                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Existing Roles</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="roleTableBody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading roles...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Link to external JavaScript file -->
    <script src="../js/user_management.js"></script>
</body>
</html>


