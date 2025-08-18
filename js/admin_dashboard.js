document.addEventListener('DOMContentLoaded', () => {
    fetchUsers();
});

async function fetchUsers() {
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