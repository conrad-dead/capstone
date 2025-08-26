document.addEventListener('DOMContentLoaded', () => {
    // --- Global Variables ---
    let dashboardData = {
        medicines: [],
        categories: [],
        stats: {}
    };
    
    // --- API URLs ---
    const API_BASE = '../api/drug_api.php';
    const CATEGORIES_URL = `${API_BASE}?resource=categories`;
    const DRUGS_URL = `${API_BASE}?resource=drugs`;
    
    // --- Utility Functions ---
    function showLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) loadingOverlay.classList.remove('hidden');
    }
    
    function hideLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) loadingOverlay.classList.add('hidden');
    }
    
    function formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }
    
    function getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }
    
    function showNotification(message, type = 'info') {
        if (window.Swal) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            console.log(`${type}: ${message}`);
        }
    }
    
    // --- API Functions ---
    async function fetchCategories() {
        try {
            const response = await fetch(CATEGORIES_URL);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            if (result.success) {
                dashboardData.categories = result.data;
                return result.data;
            } else {
                console.error('Failed to fetch categories:', result.message);
                showNotification(`Categories API Error: ${result.message}`, 'error');
                return [];
            }
        } catch (error) {
            console.error('Error fetching categories:', error);
            showNotification(`Categories API Error: ${error.message}`, 'error');
            return [];
        }
    }
    
    async function fetchDrugs() {
        try {
            const response = await fetch(`${DRUGS_URL}&page=1&limit=1000`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            if (result.success) {
                dashboardData.medicines = result.data;
                return result.data;
            } else {
                console.error('Failed to fetch drugs:', result.message);
                showNotification(`Drugs API Error: ${result.message}`, 'error');
                return [];
            }
        } catch (error) {
            console.error('Error fetching drugs:', error);
            showNotification(`Drugs API Error: ${error.message}`, 'error');
            return [];
        }
    }
    
    // --- Statistics Functions ---
    function calculateStats(medicines) {
        const now = new Date();
        const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        
        const stats = {
            totalItems: medicines.length,
            lowStockItems: medicines.filter(m => m.quantity <= 20).length,
            expiringSoon: medicines.filter(m => {
                if (!m.expiry_date) return false;
                const expiryDate = new Date(m.expiry_date);
                return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
            }).length,
            totalCategories: dashboardData.categories.length,
            outOfStock: medicines.filter(m => m.quantity === 0).length,
            expired: medicines.filter(m => {
                if (!m.expiry_date) return false;
                return new Date(m.expiry_date) < now;
            }).length
        };
        
        dashboardData.stats = stats;
        return stats;
    }
    
    function updateStatistics(stats) {
        const totalItems = document.getElementById('totalItems');
        const lowStockCount = document.getElementById('lowStockCount');
        const expiringCount = document.getElementById('expiringCount');
        const categoryCount = document.getElementById('categoryCount');
        const lastUpdated = document.getElementById('lastUpdated');
        
        if (totalItems) totalItems.textContent = formatNumber(stats.totalItems);
        if (lowStockCount) lowStockCount.textContent = formatNumber(stats.lowStockItems);
        if (expiringCount) expiringCount.textContent = formatNumber(stats.expiringSoon);
        if (categoryCount) categoryCount.textContent = formatNumber(stats.totalCategories);
        if (lastUpdated) lastUpdated.textContent = getTimeAgo(new Date());
    }
    
    // --- Table Functions ---
    function renderDrugTable(drugs) {
        const drugTableBody = document.getElementById('drugTableBody');
        if (!drugTableBody) return;
        
        drugTableBody.innerHTML = '';
        if (!drugs || drugs.length === 0) {
            drugTableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No medicines found. Add some above!</td></tr>';
            return;
        }
        
        // Group medicines by category
        const medicinesByCategory = {};
        drugs.forEach(drug => {
            const categoryName = drug.category_name || 'Uncategorized';
            if (!medicinesByCategory[categoryName]) {
                medicinesByCategory[categoryName] = [];
            }
            medicinesByCategory[categoryName].push(drug);
        });
        
        // Render each category section
        Object.keys(medicinesByCategory).forEach(categoryName => {
            const medicines = medicinesByCategory[categoryName];
            
            // Add category header row
            const categoryHeader = document.createElement('tr');
            categoryHeader.className = 'bg-gray-50 border-b-2 border-gray-200';
            categoryHeader.innerHTML = `
                <td colspan="7" class="px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-tags text-blue-600"></i>
                            <h4 class="text-lg font-semibold text-gray-800">${categoryName}</h4>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                ${medicines.length} item${medicines.length !== 1 ? 's' : ''}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <span class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                                Good: ${medicines.filter(m => m.quantity > 20).length}
                            </span>
                            <span class="flex items-center">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"></div>
                                Low: ${medicines.filter(m => m.quantity <= 20 && m.quantity > 0).length}
                            </span>
                            <span class="flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                                Out: ${medicines.filter(m => m.quantity === 0).length}
                            </span>
                        </div>
                    </div>
                </td>
            `;
            drugTableBody.appendChild(categoryHeader);
            
            // Add medicines for this category
            medicines.forEach(drug => {
                const row = document.createElement('tr');
                row.dataset.drug = JSON.stringify(drug);
                row.className = 'table-row-hover border-b border-gray-100';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drug.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${drug.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${drug.category_name || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${drug.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${drug.expiry_date || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${getStatusBadge(drug)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-4 edit-drug-btn transition-colors duration-200" data-drug-id="${drug.id}">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <button class="text-red-600 hover:text-red-900 delete-drug-btn transition-colors duration-200" data-drug-id="${drug.id}">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </td>
                `;
                drugTableBody.appendChild(row);
            });
        });
    }
    
    function getStatusBadge(medicine) {
        const now = new Date();
        const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        
        if (medicine.quantity === 0) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>';
        } else if (medicine.quantity <= 20) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Low Stock</span>';
        } else if (medicine.expiry_date && new Date(medicine.expiry_date) <= thirtyDaysFromNow && new Date(medicine.expiry_date) >= now) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Expiring Soon</span>';
        } else {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Good Stock</span>';
        }
    }
    
    // --- Form Functions ---
    let editingDrugId = null;
    
    async function handleDrugSubmit(event) {
        event.preventDefault();
        
        const drugForm = document.getElementById('drugForm');
        const formData = new FormData(drugForm);
        const drugData = {
            name: formData.get('name'),
            category_id: parseInt(formData.get('category_id')),
            quantity: parseInt(formData.get('quantity')),
            expiry_date: formData.get('expiry_date')
        };

        if (editingDrugId) {
            drugData.id = editingDrugId;
        }

        try {
            const response = await fetch(DRUGS_URL, {
                method: editingDrugId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(drugData)
            });

            const result = await response.json();
            if (result.success) {
                showNotification(result.message, 'success');
                resetDrugForm();
                await refreshData();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error submitting drug:', error);
            showNotification('Failed to save drug. Please try again.', 'error');
        }
    }

    function resetDrugForm() {
        const drugForm = document.getElementById('drugForm');
        const drugSubmitButton = document.getElementById('drugSubmitButton');
        const cancelDrugEditButton = document.getElementById('cancelDrugEditButton');
        
        if (drugForm) drugForm.reset();
        editingDrugId = null;
        if (drugSubmitButton) drugSubmitButton.textContent = 'Add Medicine';
        if (cancelDrugEditButton) cancelDrugEditButton.classList.add('hidden');
    }

    function editDrug(drugId) {
        const row = document.querySelector(`[data-drug-id="${drugId}"]`).closest('tr');
        const drug = JSON.parse(row.dataset.drug);
        
        editingDrugId = drug.id;
        
        const drugNameInput = document.getElementById('drugName');
        const drugCategorySelect = document.getElementById('drugCategory');
        const drugQuantityInput = document.getElementById('drugQuantity');
        const drugExpiryDateInput = document.getElementById('drugExpiryDate');
        const drugSubmitButton = document.getElementById('drugSubmitButton');
        const cancelDrugEditButton = document.getElementById('cancelDrugEditButton');
        
        if (drugNameInput) drugNameInput.value = drug.name;
        if (drugCategorySelect) drugCategorySelect.value = drug.category_id;
        if (drugQuantityInput) drugQuantityInput.value = drug.quantity;
        if (drugExpiryDateInput) drugExpiryDateInput.value = drug.expiry_date || '';
        if (drugSubmitButton) drugSubmitButton.textContent = 'Update Medicine';
        if (cancelDrugEditButton) cancelDrugEditButton.classList.remove('hidden');
        
        // Show modal
        const drugModal = document.getElementById('drugModal');
        if (drugModal) drugModal.classList.remove('hidden');
    }

    async function deleteDrug(drugId) {
        const confirmed = await Swal.fire({
            title: 'Are you sure?',
            text: 'Are you sure you want to delete this medicine?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        });
        
        if (!confirmed.isConfirmed) return;

        try {
            const response = await fetch(DRUGS_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: drugId })
            });

            const result = await response.json();
            if (result.success) {
                showNotification(result.message, 'success');
                await refreshData();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting drug:', error);
            showNotification('Failed to delete drug. Please try again.', 'error');
        }
    }

    function populateCategorySelect() {
        const drugCategorySelect = document.getElementById('drugCategory');
        
        if (drugCategorySelect) {
            drugCategorySelect.innerHTML = '<option value="">Select Category</option>';
            dashboardData.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                drugCategorySelect.appendChild(option);
            });
        }
    }

    function populateCategoryButtons() {
        const categoryButtonsContainer = document.getElementById('categoryButtons');
        if (!categoryButtonsContainer) return;

        // Keep the "All Categories" button, remove others
        const allCategoriesBtn = categoryButtonsContainer.querySelector('button[onclick="filterByCategory(\'all\')"]');
        categoryButtonsContainer.innerHTML = '';
        if (allCategoriesBtn) {
            categoryButtonsContainer.appendChild(allCategoriesBtn);
        }

        // Add category buttons
        dashboardData.categories.forEach(category => {
            const button = document.createElement('button');
            button.className = 'category-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 flex items-center space-x-2';
            button.onclick = () => filterByCategory(category.id);
            button.innerHTML = `
                <i class="fas fa-tag"></i>
                <span>${category.name}</span>
            `;
            categoryButtonsContainer.appendChild(button);
        });
    }

    // --- Event Listeners ---
    function setupEventListeners() {
        // Drug form submission
        const drugForm = document.getElementById('drugForm');
        if (drugForm) {
            drugForm.addEventListener('submit', handleDrugSubmit);
        }

        // Cancel edit button
        const cancelDrugEditButton = document.getElementById('cancelDrugEditButton');
        if (cancelDrugEditButton) {
            cancelDrugEditButton.addEventListener('click', resetDrugForm);
        }

        // Modal controls
        const addDrugBtn = document.getElementById('addDrugBtn');
        const closeDrugModal = document.getElementById('closeDrugModal');
        const drugModal = document.getElementById('drugModal');

        if (addDrugBtn) {
            addDrugBtn.addEventListener('click', () => {
                resetDrugForm();
                if (drugModal) drugModal.classList.remove('hidden');
            });
        }

        if (closeDrugModal) {
            closeDrugModal.addEventListener('click', () => {
                if (drugModal) drugModal.classList.add('hidden');
                resetDrugForm();
            });
        }

        // Close modal when clicking outside
        if (drugModal) {
            drugModal.addEventListener('click', (e) => {
                if (e.target === drugModal) {
                    drugModal.classList.add('hidden');
                    resetDrugForm();
                }
            });
        }

        // Table event delegation
        const drugTableBody = document.getElementById('drugTableBody');
        if (drugTableBody) {
            drugTableBody.addEventListener('click', (e) => {
                if (e.target.classList.contains('edit-drug-btn') || e.target.closest('.edit-drug-btn')) {
                    e.preventDefault();
                    const drugId = e.target.dataset.drugId || e.target.closest('.edit-drug-btn').dataset.drugId;
                    editDrug(drugId);
                } else if (e.target.classList.contains('delete-drug-btn') || e.target.closest('.delete-drug-btn')) {
                    e.preventDefault();
                    const drugId = e.target.dataset.drugId || e.target.closest('.delete-drug-btn').dataset.drugId;
                    deleteDrug(drugId);
                }
            });
        }

        // Search and filter event listeners
        const searchDrugsInput = document.getElementById('searchDrugs');
        const filterStatusSelect = document.getElementById('filterStatus');

        if (searchDrugsInput) {
            searchDrugsInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase().trim();
                if (searchTerm) {
                    // Filter medicines by search term
                    const filteredMedicines = dashboardData.medicines.filter(drug => 
                        drug.name.toLowerCase().includes(searchTerm) ||
                        (drug.category_name && drug.category_name.toLowerCase().includes(searchTerm)) ||
                        drug.id.toString().includes(searchTerm)
                    );
                    renderDrugTable(filteredMedicines);
                } else {
                    // Show all medicines
                    renderDrugTable(dashboardData.medicines);
                }
            });
        }

        if (filterStatusSelect) {
            filterStatusSelect.addEventListener('change', (e) => {
                const selectedStatus = e.target.value;
                if (selectedStatus) {
                    let filteredMedicines = [];
                    const now = new Date();
                    const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
                    
                    switch (selectedStatus) {
                        case 'active':
                            // Show medicines with good stock
                            filteredMedicines = dashboardData.medicines.filter(drug => drug.quantity > 20);
                            break;
                        case 'inactive':
                            // Show medicines with low or no stock
                            filteredMedicines = dashboardData.medicines.filter(drug => drug.quantity <= 20);
                            break;
                        case 'expiring':
                            // Show medicines expiring soon
                            filteredMedicines = dashboardData.medicines.filter(drug => {
                                if (!drug.expiry_date) return false;
                                const expiryDate = new Date(drug.expiry_date);
                                return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
                            });
                            break;
                        case 'out-of-stock':
                            // Show medicines with zero quantity
                            filteredMedicines = dashboardData.medicines.filter(drug => drug.quantity === 0);
                            break;
                        default:
                            filteredMedicines = dashboardData.medicines;
                    }
                    renderDrugTable(filteredMedicines);
                } else {
                    // Show all medicines
                    renderDrugTable(dashboardData.medicines);
                }
            });
        }
    }

    // --- Global Functions ---
    window.refreshData = async function() {
        showLoading();
        try {
            await fetchCategories();
            await fetchDrugs();
            const stats = calculateStats(dashboardData.medicines);
            updateStatistics(stats);
            renderDrugTable(dashboardData.medicines);
            showNotification('Data refreshed successfully!', 'success');
        } catch (error) {
            console.error('Error refreshing data:', error);
            showNotification('Failed to refresh data', 'error');
        } finally {
            hideLoading();
        }
    };
    
    window.filterByCategory = function(categoryId) {
        // Update button styles
        const categoryButtons = document.querySelectorAll('.category-btn');
        categoryButtons.forEach(btn => {
            btn.classList.remove('active', 'bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700');
        });

        // Highlight selected button
        if (categoryId === 'all') {
            const allBtn = document.querySelector('button[onclick="filterByCategory(\'all\')"]');
            if (allBtn) {
                allBtn.classList.add('active', 'bg-blue-600', 'text-white');
                allBtn.classList.remove('bg-gray-200', 'text-gray-700');
            }
            renderDrugTable(dashboardData.medicines);
        } else {
            const selectedBtn = document.querySelector(`button[onclick="filterByCategory(${categoryId})"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('active', 'bg-blue-600', 'text-white');
                selectedBtn.classList.remove('bg-gray-200', 'text-gray-700');
            }
            
            // Filter medicines by selected category
            const filteredMedicines = dashboardData.medicines.filter(drug => 
                drug.category_id == categoryId
            );
            renderDrugTable(filteredMedicines);
        }
    };

    window.clearFilters = function() {
        const searchDrugsInput = document.getElementById('searchDrugs');
        const filterStatusSelect = document.getElementById('filterStatus');
        
        if (searchDrugsInput) searchDrugsInput.value = '';
        if (filterStatusSelect) filterStatusSelect.value = '';
        
        // Reset category buttons
        const categoryButtons = document.querySelectorAll('.category-btn');
        categoryButtons.forEach(btn => {
            btn.classList.remove('active', 'bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700');
        });
        
        // Highlight "All Categories" button
        const allBtn = document.querySelector('button[onclick="filterByCategory(\'all\')"]');
        if (allBtn) {
            allBtn.classList.add('active', 'bg-blue-600', 'text-white');
            allBtn.classList.remove('bg-gray-200', 'text-gray-700');
        }
        
        // Refresh the table with cleared filters
        renderDrugTable(dashboardData.medicines);
        showNotification('Filters cleared', 'info');
    };
    
    window.testAPIs = async function() {
        showLoading();
        try {
            console.log('=== API Debug Test ===');
            
            // Test categories API
            console.log('Testing categories API...');
            const categoriesResponse = await fetch(CATEGORIES_URL);
            console.log('Categories response status:', categoriesResponse.status);
            const categoriesResult = await categoriesResponse.json();
            console.log('Categories result:', categoriesResult);
            
            // Test drugs API
            console.log('Testing drugs API...');
            const drugsResponse = await fetch(`${DRUGS_URL}&page=1&limit=10`);
            console.log('Drugs response status:', drugsResponse.status);
            const drugsResult = await drugsResponse.json();
            console.log('Drugs result:', drugsResult);
            
            // Show results
            let message = 'API Test Results:\n\n';
            message += `Categories API: ${categoriesResponse.status} - ${categoriesResult.success ? 'SUCCESS' : 'FAILED'}\n`;
            message += `Drugs API: ${drugsResponse.status} - ${drugsResult.success ? 'SUCCESS' : 'FAILED'}\n\n`;
            
            if (categoriesResult.success && drugsResult.success) {
                message += '✅ All APIs working correctly!';
                showNotification(message, 'success');
            } else {
                message += '❌ API errors detected. Check console for details.';
                showNotification(message, 'error');
            }
            
        } catch (error) {
            console.error('API test error:', error);
            showNotification(`API Test Error: ${error.message}`, 'error');
        } finally {
            hideLoading();
        }
    };
    
    // --- Initialization ---
    async function initializePage() {
        showLoading();
        try {
            // Check if user is logged in by testing a simple API call
            const testResponse = await fetch(CATEGORIES_URL);
            if (testResponse.status === 401) {
                showNotification('You are not logged in. Please log in to access this page.', 'error');
                setTimeout(() => {
                    window.location.href = '../login.php';
                }, 2000);
                return;
            }
            
            await fetchCategories();
            await fetchDrugs();
            const stats = calculateStats(dashboardData.medicines);
            updateStatistics(stats);
            renderDrugTable(dashboardData.medicines);
            populateCategorySelect();
            populateCategoryButtons();
            setupEventListeners();
        } catch (error) {
            console.error('Error initializing page:', error);
            showNotification('Failed to load data: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }
    
    initializePage();
});
