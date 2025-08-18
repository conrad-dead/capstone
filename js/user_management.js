document.addEventListener('DOMContentLoaded', () => {
    // --- User Form Elements ---
    const userForm = document.getElementById('userForm');
    const userFormTitle = document.getElementById('userFormTitle');
    const userIdInput = document.getElementById('userId');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const roleSelect = document.getElementById('role'); // Now for role_id
    const contactNumberInput = document.getElementById('contact_number'); // New: Contact Number Input
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

    // Debugging: Log elements to console to verify they are found
    console.log('usersTabBtn element:', usersTabBtn);
    console.log('rolesTabBtn element:', rolesTabBtn);
    console.log('usersTabContent element:', usersTabContent);
    console.log('rolesTabContent element:', rolesTabContent);

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
        if (usersTabBtn) usersTabBtn.classList.remove('active');
        if (rolesTabBtn) rolesTabBtn.classList.remove('active');
        if (usersTabContent) usersTabContent.classList.remove('active');
        if (rolesTabContent) rolesTabContent.classList.remove('active');

        // Activate the selected tab and show its content
        if (tabName === 'users') {
            if (usersTabBtn) usersTabBtn.classList.add('active');
            if (usersTabContent) usersTabContent.classList.add('active');
            fetchUsers(); // Refresh users when tab is shown
            fetchRoles(); // Also refresh roles for user form dropdown
        } else if (tabName === 'roles') {
            if (rolesTabBtn) rolesTabBtn.classList.add('active');
            if (rolesTabContent) rolesTabContent.classList.add('active');
            fetchRoles(); // Refresh roles when tab is shown
        }
    }

    // Add event listeners with null checks to prevent TypeError
    if (usersTabBtn) {
        usersTabBtn.addEventListener('click', () => showTab('users'));
    } else {
        console.error("Error: 'usersTabBtn' element not found in the DOM. Check HTML ID.");
    }

    if (rolesTabBtn) {
        rolesTabBtn.addEventListener('click', () => showTab('roles'));
    } else {
        console.error("Error: 'rolesTabBtn' element not found in the DOM. Check HTML ID.");
    }

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
        if (userForm) userForm.reset();
        if (userFormTitle) userFormTitle.textContent = 'Create User';
        if (submitButton) submitButton.textContent = 'Create User';
        if (passwordInput) passwordInput.setAttribute('required', 'required');
        if (confirmPasswordInput) confirmPasswordInput.setAttribute('required', 'required');
        if (cancelEditButton) cancelEditButton.classList.add('hidden');
        if (barangayField) barangayField.classList.add('hidden');
        if (barangayInput) {
            barangayInput.removeAttribute('required');
            barangayInput.value = '';
        }
        if (barangaySearchResults) {
            barangaySearchResults.classList.add('hidden');
            barangaySearchResults.innerHTML = '';
        }
        editingUserId = null;
        // Re-populate roles dropdown to ensure it's fresh
        populateRoleDropdown(allRoles);
    }

    function populateUserFormForEdit(user) {
        if (userFormTitle) userFormTitle.textContent = 'Edit User';
        if (submitButton) submitButton.textContent = 'Update User';
        if (passwordInput) passwordInput.removeAttribute('required');
        if (confirmPasswordInput) confirmPasswordInput.removeAttribute('required');
        if (passwordInput) passwordInput.value = '';
        if (confirmPasswordInput) confirmPasswordInput.value = '';
        if (cancelEditButton) cancelEditButton.classList.remove('hidden');

        if (userIdInput) userIdInput.value = user.id;
        if (usernameInput) usernameInput.value = user.username;
        if (roleSelect) roleSelect.value = user.role_id; // Set by role_id
        if (contactNumberInput) contactNumberInput.value = user.contact_number || ''; // Populate contact number

        // Trigger change event to show/hide barangay field if needed
        const event = new Event('change');
        if (roleSelect) roleSelect.dispatchEvent(event); 

        if (user.role_name === 'bhw') { // Check role_name for BHW
            if (barangayInput) {
                barangayInput.value = user.barangay || '';
                barangayInput.setAttribute('required', 'required');
            }
        } else {
            if (barangayInput) {
                barangayInput.value = '';
                barangayInput.removeAttribute('required');
            }
        }
        editingUserId = user.id;
    }

    if (cancelEditButton) cancelEditButton.addEventListener('click', resetUserForm);

    // Populate role dropdown dynamically
    function populateRoleDropdown(roles) {
        if (roleSelect) roleSelect.innerHTML = '<option value="">Select role</option>'; // Clear existing
        roles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.name;
            if (roleSelect) roleSelect.appendChild(option);
        });
    }

    // --- Dynamic Field Visibility (Barangay) ---
    const barangays = [ // This list should match the one in your PHP file
        "Barcolan", "Buenavista", "Dammao", "District I (Pob.)", 
        "District II (Pob.)", "District III (Pob.)", "Furao", "Guibang", 
        "Lenzon", "Linglingay", "Mabini", "Pintor", "Rizal", "Songsong", 
        "Union", "Upi"
    ];

    if (roleSelect) {
        roleSelect.addEventListener('change', () => {
            const selectedRoleOption = roleSelect.options[roleSelect.selectedIndex];
            // Check the text content of the selected option to determine if it's 'Bhw'
            if (selectedRoleOption && selectedRoleOption.textContent === 'bhw') {
                if (barangayField) barangayField.classList.remove('hidden');
                if (barangayInput) barangayInput.setAttribute('required', 'required');
            } else {
                if (barangayField) barangayField.classList.add('hidden');
                if (barangayInput) {
                    barangayInput.removeAttribute('required');
                    barangayInput.value = '';
                }
                if (barangaySearchResults) {
                    barangaySearchResults.classList.add('hidden');
                    barangaySearchResults.innerHTML = '';
                }
            }
        });
    }

    // --- Barangay Search/Autocomplete Logic ---
    if (barangayInput) {
        barangayInput.addEventListener('input', () => {
            const searchTerm = barangayInput.value.toLowerCase();
            if (barangaySearchResults) barangaySearchResults.innerHTML = '';

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
                            if (barangayInput) barangayInput.value = barangay;
                            if (barangaySearchResults) barangaySearchResults.classList.add('hidden');
                        });
                        if (barangaySearchResults) barangaySearchResults.appendChild(item);
                    });
                    if (barangaySearchResults) barangaySearchResults.classList.remove('hidden');
                } else {
                    if (barangaySearchResults) barangaySearchResults.classList.add('hidden');
                }
            } else {
                if (barangaySearchResults) barangaySearchResults.classList.add('hidden');
            }
        });

        barangayInput.addEventListener('blur', () => {
            setTimeout(() => {
                if (barangaySearchResults && !barangaySearchResults.contains(document.activeElement)) {
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
                    if (barangaySearchResults) barangaySearchResults.classList.remove('hidden');
                }
            }
        });
    }


    async function fetchUsers() {
        if (userTableBody) userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading users...</td></tr>`;
        try {
            const response = await fetch(USER_API_URL, { method: 'GET' });
            const result = await response.json();

            if (result.success) {
                allUsers = result.data;
                renderUserTable(allUsers);
            } else {
                if (userTableBody) userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                displayMessage(result.message, 'error');
            }
        } catch (error) {
            if (userTableBody) userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
            console.error('Error fetching users:', error);
            displayMessage('Failed to connect to the server. Please try again later.', 'error');
        }
    }

    function renderUserTable(users) {
        if (userTableBody) userTableBody.innerHTML = '';
        if (users.length === 0) {
            if (userTableBody) userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found in the system. Create one above!</td></tr>`;
            return;
        }
        users.forEach(user => {
            const row = document.createElement('tr');
            row.dataset.user = JSON.stringify(user); 
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${user.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.username}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.role_name}</td> <!-- Display role_name -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.contact_number || 'N/A'}</td> <!-- Display contact_number -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.barangay || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.created_at || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-user-btn" data-user-id="${user.id}">Edit</a>
                    <a href="#" class="text-red-600 hover:text-red-900 delete-user-btn" data-user-id="${user.id}">Delete</a>
                </td>
            `;
            if (userTableBody) userTableBody.appendChild(row);
        });
    }

    if (userForm) {
        userForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(userForm);
            const userData = Object.fromEntries(formData.entries());

            // Ensure role_id is an integer
            userData.role_id = parseInt(userData.role_id);

            // Conditionally add/remove password and confirm_password
            if (passwordInput && confirmPasswordInput) {
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
            }


            // Determine if selected role is BHW to handle barangay
            const selectedRoleObject = allRoles.find(role => parseInt(role.id) === userData.role_id);
            if (
                selectedRoleObject &&
                selectedRoleObject.name.toLowerCase() === 'bhw' &&
                barangayInput &&
                barangayInput.value.trim() !== ''
            ) {
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
                console.log('Submitting user data:', userData);
                console.log('Selected Role:', selectedRoleObject);
                console.log('Barangay value:', barangayInput.value);
                console.log('Final user data:', userData);
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
    }

    if (userTableBody) {
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
    }

    // --- Role Management Functions ---

    function resetRoleForm() {
        if (roleForm) roleForm.reset();
        if (roleFormTitle) roleFormTitle.textContent = 'Manage Roles';
        if (roleSubmitButton) roleSubmitButton.textContent = 'Add Role';
        if (cancelRoleEditButton) cancelRoleEditButton.classList.add('hidden');
        editingRoleId = null;
    }

    function populateRoleFormForEdit(role) {
        if (roleFormTitle) roleFormTitle.textContent = 'Edit Role';
        if (roleSubmitButton) roleSubmitButton.textContent = 'Update Role';
        if (cancelRoleEditButton) cancelRoleEditButton.classList.remove('hidden');
        if (roleManageIdInput) roleManageIdInput.value = role.id;
        if (roleManageNameInput) roleManageNameInput.value = role.name;
        if (roleManageDescriptionInput) roleManageDescriptionInput.value = role.description || '';
        editingRoleId = role.id;
    }

    if (cancelRoleEditButton) cancelRoleEditButton.addEventListener('click', resetRoleForm);

    async function fetchRoles() {
        if (roleTableBody) roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading roles...</td></tr>`;
        try {
            const response = await fetch(ROLE_API_URL, { method: 'GET' });
            const result = await response.json();
            if (result.success) {
                allRoles = result.data; // Store roles for user form dropdown
                renderRoleTable(allRoles);
                populateRoleDropdown(allRoles); // Update user form dropdown
            } else {
                if (roleTableBody) roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                displayMessage(result.message, 'error');
            }
        } catch (error) {
            if (roleTableBody) roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
            console.error('Error fetching roles:', error);
            displayMessage('Failed to connect to the server. Please try again later.', 'error');
        }
    }

    function renderRoleTable(roles) {
        if (roleTableBody) roleTableBody.innerHTML = '';
        if (roles.length === 0) {
            if (roleTableBody) roleTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No roles found. Add some above!</td></tr>`;
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
            if (roleTableBody) roleTableBody.appendChild(row);
        });
    }

    if (roleForm) {
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
    }

    if (roleTableBody) {
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
    }

    // Initial setup: Show users tab by default and fetch data
    showTab('users');
});
