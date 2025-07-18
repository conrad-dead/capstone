
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
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading users...</td>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- User Form Elements ---
            const userForm = document.getElementById('userForm');
            const userFormTitle = document.getElementById('userFormTitle');
            const userIdInput = document.getElementById('userId');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const roleSelect = document.getElementById('role'); // Now for role_id
            const barangayField = document.getElementById('barangayField');
            const barangayInput = document.getElementById('barangay');
            const barangaySearchResults = document.getElementById('barangaySearchResults');
            const submitButton = document.getElementById('submitButton');
            const cancelEditButton = document.getElementById('cancelEditButton');
            const userTableBody = document.getElementById('userTableBody');

            // --- Role Management Elements ---
            const roleForm = document.getElementById('roleForm');
            const roleFormTitle = document.getElementById('roleFormTitle');
            const roleManageIdInput = document.getElementById('roleManageId');
            const roleManageNameInput = document.getElementById('roleManageName');
            const roleManageDescriptionInput = document.getElementById('roleManageDescription');
            const roleSubmitButton = document.getElementById('roleSubmitButton');
            const cancelRoleEditButton = document.getElementById('cancelRoleEditButton');
            const roleTableBody = document.getElementById('roleTableBody');

            // --- Tab Elements ---
            const usersTabBtn = document.getElementById('usersTabBtn');
            const rolesTabBtn = document.getElementById('rolesTabBtn');
            const usersTabContent = document.getElementById('usersTabContent');
            const rolesTabContent = document.getElementById('rolesTabContent');

            // --- Global State ---
            let editingUserId = null;
            let editingRoleId = null;
            let allRoles = []; // Stores all fetched roles for dynamic dropdown

            // --- API Endpoint URLs ---
            const USER_API_URL = '../api/user_api.php?resource=users';
            const ROLE_API_URL = '../api/user_api.php?resource=roles'; // New API endpoint for roles

            // --- Tab Switching Logic ---
            function showTab(tabName) {
                // Deactivate all tab buttons and hide all tab contents
                usersTabBtn.classList.remove('active');
                rolesTabBtn.classList.remove('active');
                usersTabContent.classList.remove('active');
                rolesTabContent.classList.remove('active');

                // Activate the selected tab and show its content
                if (tabName === 'users') {
                    usersTabBtn.classList.add('active');
                    usersTabContent.classList.add('active');
                    fetchUsers(); // Refresh users when tab is shown
                    fetchRoles(); // Also refresh roles for user form dropdown
                } else if (tabName === 'roles') {
                    rolesTabBtn.classList.add('active');
                    rolesTabContent.classList.add('active');
                    fetchRoles(); // Refresh roles when tab is shown
                }
            }

            usersTabBtn.addEventListener('click', () => showTab('users'));
            rolesTabBtn.addEventListener('click', () => showTab('roles'));

            // --- SweetAlert Functions ---
            function displayMessage(message, type) {
                Swal.fire({
                    icon: type,
                    title: (type === 'success' ? 'Success!' : 'Error!'),
                    text: message,
                    confirmButtonText: 'OK'
                });
            }

            async function showConfirm(message) {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                });
                return result.isConfirmed;
            }

            // --- User Management Functions ---

            function resetUserForm() {
                userForm.reset();
                userFormTitle.textContent = 'Create User';
                submitButton.textContent = 'Create User';
                passwordInput.setAttribute('required', 'required');
                confirmPasswordInput.setAttribute('required', 'required');
                cancelEditButton.classList.add('hidden');
                barangayField.classList.add('hidden');
                barangayInput.removeAttribute('required');
                barangayInput.value = '';
                barangaySearchResults.classList.add('hidden');
                barangaySearchResults.innerHTML = '';
                editingUserId = null;
                // Re-populate roles dropdown to ensure it's fresh
                populateRoleDropdown(allRoles);
            }

            function populateUserFormForEdit(user) {
                userFormTitle.textContent = 'Edit User';
                submitButton.textContent = 'Update User';
                passwordInput.removeAttribute('required');
                confirmPasswordInput.removeAttribute('required');
                passwordInput.value = '';
                confirmPasswordInput.value = '';
                cancelEditButton.classList.remove('hidden');

                userIdInput.value = user.id;
                usernameInput.value = user.username;
                roleSelect.value = user.role_id; // Set by role_id

                // Trigger change event to show/hide barangay field if needed
                const event = new Event('change');
                roleSelect.dispatchEvent(event); 

                if (user.role_name === 'bhw') { // Check role_name for BHW
                    barangayInput.value = user.barangay || '';
                    barangayInput.setAttribute('required', 'required');
                } else {
                    barangayInput.value = '';
                    barangayInput.removeAttribute('required');
                }
                editingUserId = user.id;
            }

            cancelEditButton.addEventListener('click', resetUserForm);

            // Populate role dropdown dynamically
            function populateRoleDropdown(roles) {
                roleSelect.innerHTML = '<option value="">Select role</option>'; // Clear existing
                roles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    roleSelect.appendChild(option);
                });
            }

            // --- Dynamic Field Visibility (Barangay) ---
            const barangays = [ // This list should match the one in your PHP file
                "Aglipay", "Baguio", "Barangay I (Pob.)", "Barangay II (Pob.)", 
                "Barangay III (Pob.)", "Barangay IV (Pob.)", "Bungcag", "Camasi", 
                "Central", "Dabburab", "Furao", "Guinatan", "Guisi", "Malasin", 
                "Mabini", "Union"
            ];

            barangayInput.addEventListener('input', () => {
                const searchTerm = barangayInput.value.toLowerCase();
                barangaySearchResults.innerHTML = '';

                if (searchTerm.length > 0) {
                    const filteredBarangays = barangays.filter(barangay =>
                        barangay.toLowerCase().includes(searchTerm)
                    );

                    if (filteredBarangays.length > 0) {
                        filteredBarangays.forEach(barangay => {
                            const item = document.createElement('div');
                            item.classList.add('autocomplete-item');
                            item.textContent = barangay;
                            item.addEventListener('click', () => {
                                barangayInput.value = barangay;
                                barangaySearchResults.classList.add('hidden');
                            });
                            barangaySearchResults.appendChild(item);
                        });
                        barangaySearchResults.classList.remove('hidden');
                    } else {
                        barangaySearchResults.classList.add('hidden');
                    }
                } else {
                    barangaySearchResults.classList.add('hidden');
                }
            });

            barangayInput.addEventListener('blur', () => {
                setTimeout(() => {
                    if (!barangaySearchResults.contains(document.activeElement)) {
                        barangaySearchResults.classList.add('hidden');
                    }
                }, 100);
            });

            barangayInput.addEventListener('focus', () => {
                if (barangayInput.value.length > 0) {
                     const searchTerm = barangayInput.value.toLowerCase();
                     const filteredBarangays = barangays.filter(barangay =>
                        barangay.toLowerCase().includes(searchTerm)
                    );
                    if (filteredBarangays.length > 0) {
                        barangaySearchResults.classList.remove('hidden');
                    }
                }
            });


            async function fetchUsers() {
                userTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading users...</td></tr>`;
                try {
                    const response = await fetch(USER_API_URL, { method: 'GET' });
                    const result = await response.json();

                    if (result.success) {
                        allUsers = result.data;
                        renderUserTable(allUsers);
                    } else {
                        userTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                        displayMessage(result.message, 'error');
                    }
                } catch (error) {
                    userTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
                    console.error('Error fetching users:', error);
                    displayMessage('Failed to connect to the server. Please try again later.', 'error');
                }
            }

            function renderUserTable(users) {
                userTableBody.innerHTML = '';
                if (users.length === 0) {
                    userTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found in the system. Create one above!</td></tr>`;
                    return;
                }
                users.forEach(user => {
                    const row = document.createElement('tr');
                    row.dataset.user = JSON.stringify(user); 
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${user.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.username}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.role_name}</td> <!-- Display role_name -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.barangay || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.created_at || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-user-btn" data-user-id="${user.id}">Edit</a>
                            <a href="#" class="text-red-600 hover:text-red-900 delete-user-btn" data-user-id="${user.id}">Delete</a>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                });
            }

            userForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(userForm);
                const userData = Object.fromEntries(formData.entries());

                // Ensure role_id is an integer
                userData.role_id = parseInt(userData.role_id);

                // Conditionally add/remove password and confirm_password
                if (passwordInput.value === '' && confirmPasswordInput.value === '') {
                    delete userData.password;
                    delete userData.confirm_password;
                } else if (passwordInput.value !== confirmPasswordInput.value) {
                    displayMessage('Passwords do not match.', 'error');
                    return;
                } else if (passwordInput.value.length < 6) {
                    displayMessage('Password must be at least 6 characters long.', 'error');
                    return;
                }

                // Determine if selected role is BHW to handle barangay
                const selectedRoleObject = allRoles.find(role => role.id === userData.role_id);
                if (selectedRoleObject && selectedRoleObject.name === 'bhw' && barangayInput.value.trim() !== '') {
                    userData.barangay = barangayInput.value.trim();
                } else {
                    userData.barangay = null;
                }

                let method = 'POST';
                let url = USER_API_URL;

                if (editingUserId) {
                    method = 'PUT';
                    userData.id = editingUserId;
                }

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(userData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        displayMessage(result.message, 'success');
                        resetUserForm();
                        fetchUsers();
                    } else {
                        displayMessage(result.message, 'error');
                    }
                } catch (error) {
                    console.error(`Error ${method}ing user:`, error);
                    displayMessage(`Failed to ${method} user due to a network error. Please try again.`, 'error');
                }
            });

            userTableBody.addEventListener('click', async (event) => {
                if (event.target.classList.contains('delete-user-btn')) {
                    event.preventDefault();
                    const userIdToDelete = event.target.dataset.userId;

                    const confirmed = await showConfirm(`Are you sure you want to delete user ID: ${userIdToDelete}?`);

                    if (confirmed) {
                        try {
                            const response = await fetch(USER_API_URL, {
                                method: 'DELETE',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: userIdToDelete })
                            });

                            const result = await response.json();

                            if (result.success) {
                                displayMessage(result.message, 'success');
                                fetchUsers();
                            } else {
                                displayMessage(result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error deleting user:', error);
                            displayMessage('Failed to delete user due to a network error.', 'error');
                        }
                    }
                }
            });

            userTableBody.addEventListener('click', (event) => {
                if (event.target.classList.contains('edit-user-btn')) {
                    event.preventDefault();
                    const row = event.target.closest('tr');
                    const userData = JSON.parse(row.dataset.user);
                    populateUserFormForEdit(userData);
                }
            });

            // --- Role Management Functions ---

            function resetRoleForm() {
                roleForm.reset();
                roleFormTitle.textContent = 'Manage Roles';
                roleSubmitButton.textContent = 'Add Role';
                cancelRoleEditButton.classList.add('hidden');
                editingRoleId = null;
            }

            function populateRoleFormForEdit(role) {
                roleFormTitle.textContent = 'Edit Role';
                roleSubmitButton.textContent = 'Update Role';
                cancelRoleEditButton.classList.remove('hidden');
                roleManageIdInput.value = role.id;
                roleManageNameInput.value = role.name;
                roleManageDescriptionInput.value = role.description || '';
                editingRoleId = role.id;
            }

            cancelRoleEditButton.addEventListener('click', resetRoleForm);

            async function fetchRoles() {
                roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading roles...</td></tr>`;
                try {
                    const response = await fetch(ROLE_API_URL, { method: 'GET' });
                    const result = await response.json();
                    if (result.success) {
                        allRoles = result.data; // Store roles for user form dropdown
                        renderRoleTable(allRoles);
                        populateRoleDropdown(allRoles); // Update user form dropdown
                    } else {
                        roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                        displayMessage(result.message, 'error');
                    }
                } catch (error) {
                    roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
                    console.error('Error fetching roles:', error);
                    displayMessage('Failed to connect to the server. Please try again later.', 'error');
                }
            }

            function renderRoleTable(roles) {
                roleTableBody.innerHTML = '';
                if (roles.length === 0) {
                    roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No roles found. Add some above!</td></tr>`;
                    return;
                }
                roles.forEach(role => {
                    const row = document.createElement('tr');
                    row.dataset.role = JSON.stringify(role);
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${role.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${role.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${role.description || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${role.created_at || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-role-btn" data-role-id="${role.id}">Edit</a>
                            <a href="#" class="text-red-600 hover:text-red-900 delete-role-btn" data-role-id="${role.id}">Delete</a>
                        </td>
                    `;
                    roleTableBody.appendChild(row);
                });
            }

            roleForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const roleData = {
                    name: roleManageNameInput.value.trim(),
                    description: roleManageDescriptionInput.value.trim()
                };

                let method = 'POST';
                let url = ROLE_API_URL;

                if (editingRoleId) {
                    method = 'PUT';
                    roleData.id = editingRoleId;
                }

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(roleData)
                    });
                    const result = await response.json();
                    if (result.success) {
                        displayMessage(result.message, 'success');
                        resetRoleForm();
                        fetchRoles();
                    } else {
                        displayMessage(result.message, 'error');
                    }
                } catch (error) {
                    console.error(`Error ${method}ing role:`, error);
                    displayMessage(`Failed to ${method} role due to a network error.`, 'error');
                }
            });

            roleTableBody.addEventListener('click', async (event) => {
                if (event.target.classList.contains('edit-role-btn')) {
                    event.preventDefault();
                    const row = event.target.closest('tr');
                    const roleData = JSON.parse(row.dataset.role);
                    populateRoleFormForEdit(roleData);
                } else if (event.target.classList.contains('delete-role-btn')) {
                    event.preventDefault();
                    const roleIdToDelete = event.target.dataset.roleId;
                    const confirmed = await showConfirm(`Are you sure you want to delete role ID: ${roleIdToDelete}? This will fail if users are assigned to it.`);
                    if (confirmed) {
                        try {
                            const response = await fetch(ROLE_API_URL, {
                                method: 'DELETE',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: roleIdToDelete })
                            });
                            const result = await response.json();
                            if (result.success) {
                                displayMessage(result.message, 'success');
                                fetchRoles();
                            } else {
                                displayMessage(result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error deleting role:', error);
                            displayMessage('Failed to delete role due to a network error.', 'error');
                        }
                    }
                }
            });

            // Initial setup: Show users tab by default and fetch data
            showTab('users');
        });
    </script>
</body>
</html>
