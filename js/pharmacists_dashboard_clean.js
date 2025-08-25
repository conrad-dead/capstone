document.addEventListener('DOMContentLoaded', () => {
	// --- Elements ---
	const tabReportsBtn = document.getElementById('tabReportsBtn');
	const tabManagementBtn = document.getElementById('tabManagementBtn');
	const tabReports = document.getElementById('tabReports');
	const tabManagement = document.getElementById('tabManagement');
	const drugForm = document.getElementById('drugForm');
	const drugFormTitle = document.getElementById('drugFormTitle');
	const drugIdInput = document.getElementById('drugId');
	const drugNameInput = document.getElementById('drugName');
	const drugCategorySelect = document.getElementById('drugCategory');
	const drugQuantityInput = document.getElementById('drugQuantity');
	const drugExpiryDateInput = document.getElementById('drugExpiryDate');
	const drugSubmitButton = document.getElementById('drugSubmitButton');
	const cancelDrugEditButton = document.getElementById('cancelDrugEditButton');
	const drugTableBody = document.getElementById('drugTableBody');
	const drugPagination = document.getElementById('drugPagination');

	// --- State ---
	let editingDrugId = null;
	let allCategories = [];
	let currentPage = 1;
	const drugsPerPage = 10;

	// --- API URLs ---
	const CATEGORY_API_URL = '../api/drug_api.php?resource=categories';
	const DRUG_API_URL = '../api/drug_api.php?resource=drugs';

	// --- Helpers ---
	function displayMessage(message, type) {
		if (window.Swal) {
			Swal.fire({ 
				icon: type, 
				title: (type === 'success' ? 'Success!' : 'Error!'), 
				text: message, 
				confirmButtonText: 'OK',
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000
			});
		} else {
			console.log(type + ': ' + message);
		}
	}

	async function showConfirm(message) {
		if (window.Swal) {
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
		} else {
			return confirm(message);
		}
	}

	// --- API Functions ---
	async function fetchCategories() {
		try {
			const response = await fetch(CATEGORY_API_URL, { method: 'GET' });
			const result = await response.json();
			if (result.success) {
				allCategories = result.data;
				populateCategorySelect();
			} else {
				console.error('Failed to fetch categories:', result.message);
			}
		} catch (error) {
			console.error('Error fetching categories:', error);
		}
	}

	async function fetchDrugs() {
		drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>';
		try {
			const response = await fetch(`${DRUG_API_URL}&page=${currentPage}&limit=${drugsPerPage}`, { method: 'GET' });
			const result = await response.json();
			if (result.success) {
				renderDrugTable(result.data);
				renderPaginationControls(result.total_drugs);
			} else {
				drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error fetching drugs:', error);
			drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>';
			displayMessage('Failed to connect to the server. Please try again later.', 'error');
		}
	}

	function renderDrugTable(drugs) {
		drugTableBody.innerHTML = '';
		if (!drugs || drugs.length === 0) {
			drugTableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No medicines found. Add some above!</td></tr>';
			return;
		}
		drugs.forEach(drug => {
			const row = document.createElement('tr');
			row.dataset.drug = JSON.stringify(drug);
			row.className = 'table-row-hover';
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
	}

	function renderPaginationControls(totalDrugs) {
		drugPagination.innerHTML = '';
		const totalPages = Math.ceil(totalDrugs / drugsPerPage);
		if (totalPages <= 1) return;

		const prevButton = document.createElement('button');
		prevButton.textContent = 'Previous';
		prevButton.classList.add('pagination-button');
		prevButton.disabled = currentPage === 1;
		prevButton.addEventListener('click', () => { if (currentPage > 1) { currentPage--; fetchDrugs(); } });
		drugPagination.appendChild(prevButton);

		for (let i = 1; i <= totalPages; i++) {
			const pageButton = document.createElement('button');
			pageButton.textContent = i;
			pageButton.classList.add('pagination-button');
			if (i === currentPage) pageButton.classList.add('active-page');
			pageButton.addEventListener('click', () => { currentPage = i; fetchDrugs(); });
			drugPagination.appendChild(pageButton);
		}

		const nextButton = document.createElement('button');
		nextButton.textContent = 'Next';
		nextButton.classList.add('pagination-button');
		nextButton.disabled = currentPage === totalPages;
		nextButton.addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; fetchDrugs(); } });
		drugPagination.appendChild(nextButton);
	}

	function populateCategorySelect() {
		drugCategorySelect.innerHTML = '<option value="">Select Category</option>';
		allCategories.forEach(category => {
			const option = document.createElement('option');
			option.value = category.id;
			option.textContent = category.name;
			drugCategorySelect.appendChild(option);
		});
		
		// Also populate the filter category dropdown
		const filterCategorySelect = document.getElementById('filterCategory');
		if (filterCategorySelect) {
			filterCategorySelect.innerHTML = '<option value="">All Categories</option>';
			allCategories.forEach(category => {
				const option = document.createElement('option');
				option.value = category.id;
				option.textContent = category.name;
				filterCategorySelect.appendChild(option);
			});
		}
	}

	// --- Form Handlers ---
	async function handleDrugSubmit(event) {
		event.preventDefault();
		
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
			const response = await fetch(DRUG_API_URL, {
				method: editingDrugId ? 'PUT' : 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(drugData)
			});

			const result = await response.json();
			if (result.success) {
				displayMessage(result.message, 'success');
				resetDrugForm();
				fetchDrugs();
			} else {
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error submitting drug:', error);
			displayMessage('Failed to save drug. Please try again.', 'error');
		}
	}

	function resetDrugForm() {
		drugForm.reset();
		editingDrugId = null;
		drugSubmitButton.textContent = 'Add Drug';
		cancelDrugEditButton.classList.add('hidden');
	}

	function editDrug(drugId) {
		const row = document.querySelector(`[data-drug-id="${drugId}"]`).closest('tr');
		const drug = JSON.parse(row.dataset.drug);
		
		editingDrugId = drug.id;
		drugNameInput.value = drug.name;
		drugCategorySelect.value = drug.category_id;
		drugQuantityInput.value = drug.quantity;
		drugExpiryDateInput.value = drug.expiry_date || '';
		
		drugSubmitButton.textContent = 'Update Drug';
		cancelDrugEditButton.classList.remove('hidden');
		
		// Show modal
		document.getElementById('drugModal').classList.remove('hidden');
	}

	async function deleteDrug(drugId) {
		const confirmed = await showConfirm('Are you sure you want to delete this drug?');
		if (!confirmed) return;

		try {
			const response = await fetch(DRUG_API_URL, {
				method: 'DELETE',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ id: drugId })
			});

			const result = await response.json();
			if (result.success) {
				displayMessage(result.message, 'success');
				fetchDrugs();
			} else {
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error deleting drug:', error);
			displayMessage('Failed to delete drug. Please try again.', 'error');
		}
	}

	// --- Event Listeners ---
	if (drugForm) {
		drugForm.addEventListener('submit', handleDrugSubmit);
	}

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
			drugModal.classList.remove('hidden');
		});
	}

	if (closeDrugModal) {
		closeDrugModal.addEventListener('click', () => {
			drugModal.classList.add('hidden');
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
	if (drugTableBody) {
		drugTableBody.addEventListener('click', (e) => {
			if (e.target.classList.contains('edit-drug-btn')) {
				e.preventDefault();
				const drugId = e.target.dataset.drugId;
				editDrug(drugId);
			} else if (e.target.classList.contains('delete-drug-btn')) {
				e.preventDefault();
				const drugId = e.target.dataset.drugId;
				deleteDrug(drugId);
			}
		});
	}

	// --- Search and Filter Event Listeners ---
	const searchDrugsInput = document.getElementById('searchDrugs');
	const filterCategorySelect = document.getElementById('filterCategory');
	const filterStatusSelect = document.getElementById('filterStatus');

	if (searchDrugsInput) {
		searchDrugsInput.addEventListener('input', (e) => {
			// Add search functionality here if needed
			console.log('Search:', e.target.value);
		});
	}

	if (filterCategorySelect) {
		filterCategorySelect.addEventListener('change', (e) => {
			// Add category filter functionality here if needed
			console.log('Category filter:', e.target.value);
		});
	}

	if (filterStatusSelect) {
		filterStatusSelect.addEventListener('change', (e) => {
			// Add status filter functionality here if needed
			console.log('Status filter:', e.target.value);
		});
	}

	// --- Additional Functions ---
	window.refreshData = async function() {
		showLoading();
		try {
			await fetchCategories();
			await fetchDrugs();
			updateStatistics();
			showNotification('Data refreshed successfully!', 'success');
		} catch (error) {
			console.error('Error refreshing data:', error);
			showNotification('Failed to refresh data', 'error');
		} finally {
			hideLoading();
		}
	};

	window.clearFilters = function() {
		if (searchDrugsInput) searchDrugsInput.value = '';
		if (filterCategorySelect) filterCategorySelect.value = '';
		if (filterStatusSelect) filterStatusSelect.value = '';
		// Refresh the table with cleared filters
		fetchDrugs();
	};

	function showLoading() {
		const loadingOverlay = document.getElementById('loadingOverlay');
		if (loadingOverlay) loadingOverlay.classList.remove('hidden');
	}

	function hideLoading() {
		const loadingOverlay = document.getElementById('loadingOverlay');
		if (loadingOverlay) loadingOverlay.classList.add('hidden');
	}

	function updateStatistics() {
		// Update the statistics cards
		const totalItems = document.getElementById('totalItems');
		const lowStockCount = document.getElementById('lowStockCount');
		const expiringCount = document.getElementById('expiringCount');
		const categoryCount = document.getElementById('categoryCount');
		const lastUpdated = document.getElementById('lastUpdated');

		if (totalItems) totalItems.textContent = dashboardData.medicines.length || 0;
		if (lowStockCount) lowStockCount.textContent = dashboardData.medicines.filter(m => m.quantity <= 20).length || 0;
		if (expiringCount) {
			const now = new Date();
			const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
			const expiring = dashboardData.medicines.filter(m => {
				if (!m.expiry_date) return false;
				const expiryDate = new Date(m.expiry_date);
				return expiryDate <= thirtyDaysFromNow && expiryDate >= now;
			}).length;
			expiringCount.textContent = expiring || 0;
		}
		if (categoryCount) categoryCount.textContent = dashboardData.categories.length || 0;
		if (lastUpdated) lastUpdated.textContent = 'Just now';
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

	// --- Initialization ---
	async function initializePage() {
		showLoading();
		try {
			await fetchCategories();
			await fetchDrugs();
			updateStatistics();
		} catch (error) {
			console.error('Error initializing page:', error);
			showNotification('Failed to load data', 'error');
		} finally {
			hideLoading();
		}
	}

	initializePage();
});
