// Fallback displayMessage if not available
if (typeof displayMessage !== 'function') {
    function displayMessage(message, type) {
        console.log((type || 'info') + ': ' + message);
    }
}

let allUsers = [];

document.addEventListener('DOMContentLoaded', () => {
    fetchUsersCount();
    fetchUsers();
});

async function fetchUsersCount() {
    const userCountElement = document.getElementById('user-count');
    
    userCountElement.textContent = 'Loading..';

    try {
        const response = await fetch('../api/user_api.php?resource=users');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.data) {
            userCountElement.textContent = data.data.length;
        } else {
            console.log('API ERROR', data.message);
            userCountElement.textContent = 'Error';
        }
    } catch (error) {
        console.log('Fetch Error', error);
        userCountElement.textContent = 'Error';
    }
}


async function fetchUsers() {
        const userTableBody = document.getElementById('userTableBody');
        if (userTableBody) userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading users...</td></tr>`;
        try {
            const response = await fetch('../api/user_api.php?resource=users', { method: 'GET' });
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
        const userTableBody = document.getElementById('userTableBody');
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.created_at || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-user-btn" data-user-id="${user.id}">Edit</a>
                    <a href="#" class="text-red-600 hover:text-red-900 delete-user-btn" data-user-id="${user.id}">Delete</a>
                </td>
            `;
            if (userTableBody) userTableBody.appendChild(row);
        });
}