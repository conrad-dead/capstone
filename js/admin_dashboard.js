document.addEventListener('DOMContentLoaded', () => {
    fetchUsersCount();
    fetchUsers();
    fetchDrugs(); 
    fetchAndDisplayBarangayCount();



});

// ================= USERS =================
const userTableBody = document.getElementById('userTableBody');
let allUsers = [];

async function fetchUsersCount() {
    const userCountElement = document.getElementById('user-count');
    userCountElement.textContent = 'Loading..';

    try {
        const response = await fetch('../api/user_api.php?resource=users');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        userCountElement.textContent = (data.success && data.data) ? data.data.length : 'Error';
    } catch (error) {
        console.error('Fetch Error', error);
        userCountElement.textContent = 'Error';
    }
}

async function fetchUsers() {
    if (userTableBody) {
        userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading users...</td></tr>`;
    }

    try {
        const response = await fetch('../api/user_api.php?resource=users');
        const result = await response.json();

        if (result.success) {
            allUsers = result.data;
            renderUserTable(allUsers);
        } else {
            userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
        console.error('Error fetching users:', error);
    }
}

function renderUserTable(users) {
    userTableBody.innerHTML = '';
    if (users.length === 0) {
        userTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found in the system. Create one above!</td></tr>`;
        return;
    }

    users.forEach(user => {
        const row = document.createElement('tr');
        row.dataset.user = JSON.stringify(user);
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${user.id}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${user.username}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${user.role_name}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${user.created_at || 'N/A'}</td>
            <td class="px-6 py-4 text-sm font-medium">
                <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-user-btn" data-user-id="${user.id}">Edit</a>
                <a href="#" class="text-red-600 hover:text-red-900 delete-user-btn" data-user-id="${user.id}">Delete</a>
            </td>
        `;
        userTableBody.appendChild(row);
    });
}

// ================= DRUGS =================
const drugTableBody = document.getElementById('drugTableBody');
const drugPagination = document.getElementById('drugPagination');

const DRUG_API_URL = '../api/drug_api.php?resource=drugs';
let currentPage = 1;
const drugsPerPage = 10;

async function fetchDrugs() {
    drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>`;

    try {
        const response = await fetch(`${DRUG_API_URL}&page=${currentPage}&limit=${drugsPerPage}`);
        const result = await response.json();

        if (result.success) {
            renderDrugTable(result.data);
            renderPaginationControls(result.total_drugs);
        } else {
            drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
        console.error('Error fetching drugs:', error);
    }
}

function renderDrugTable(drugs) {
    drugTableBody.innerHTML = '';
    if (drugs.length === 0) {
        drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No drugs found. Add some above!</td></tr>`;
        return;
    }

    drugs.forEach(drug => {
        const row = document.createElement('tr');
        row.dataset.drug = JSON.stringify(drug);
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${drug.id}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${drug.name}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${drug.category_name || 'N/A'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${drug.quantity}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${drug.expiry_date || 'N/A'}</td>
            <td class="px-6 py-4 text-right text-sm font-medium">
                <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-drug-btn" data-drug-id="${drug.id}">Edit</a>
                <a href="#" class="text-red-600 hover:text-red-900 delete-drug-btn" data-drug-id="${drug.id}">Delete</a>
            </td>
        `;
        drugTableBody.appendChild(row);
    });
}

function renderPaginationControls(totalDrugs) {
    drugPagination.innerHTML = '';
    const totalPages = Math.ceil(totalDrugs / drugsPerPage);

    if (totalPages <= 1) return;

    // Prev button
    const prevButton = document.createElement('button');
    prevButton.textContent = 'Previous';
    prevButton.classList.add('pagination-button');
    prevButton.disabled = currentPage === 1;
    prevButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchDrugs();
        }
    });
    drugPagination.appendChild(prevButton);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement('button');
        pageButton.textContent = i;
        pageButton.classList.add('pagination-button');
        if (i === currentPage) pageButton.classList.add('active-page');
        pageButton.addEventListener('click', () => {
            currentPage = i;
            fetchDrugs();
        });
        drugPagination.appendChild(pageButton);
    }

    // Next button
    const nextButton = document.createElement('button');
    nextButton.textContent = 'Next';
    nextButton.classList.add('pagination-button');
    nextButton.disabled = currentPage === totalPages;
    nextButton.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            fetchDrugs();
        }
    });
    drugPagination.appendChild(nextButton);
}


//======== Barangay Count ===============
function fetchAndDisplayBarangayCount() {
            // Set the display text to 'Loading...' while the fetch request is in progress
            const countElement = document.getElementById('barangayCount');
            countElement.textContent = 'Loading...';

            // Use the Fetch API to get data from the PHP script
            fetch('../api/barangay_api.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update the HTML element with the count if the data is valid
                    if (data.count !== undefined) {
                        countElement.textContent = data.count;
                    } else {
                        countElement.textContent = 'Error: ' + data.error;
                        console.error('Error fetching count:', data.error);
                    }
                })
                .catch(error => {
                    // Handle and log any network or other errors
                    console.error('Fetch error:', error);
                    countElement.textContent = 'Failed to load count.';
                });
        }




//for deleting user in admin dashboard
