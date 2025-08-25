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
<<<<<<< HEAD
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
=======
			Swal.fire({ icon: type, title: (type === 'success' ? 'Success!' : 'Error!'), text: message, confirmButtonText: 'OK' });
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
		} else {
			console.log(type + ': ' + message);
		}
	}

	async function showConfirm(message) {
		if (window.Swal) {
<<<<<<< HEAD
			const result = await Swal.fire({ 
				title: 'Are you sure?', 
				text: message, 
				icon: 'warning', 
				showCancelButton: true, 
				confirmButtonColor: '#3085d6', 
				cancelButtonColor: '#d33', 
				confirmButtonText: 'Yes, proceed!' 
			});
=======
			const result = await Swal.fire({ title: 'Are you sure?', text: message, icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, proceed!' });
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
			return result.isConfirmed;
		}
		return confirm(message);
	}

	function resetDrugForm() {
		drugForm.reset();
		drugFormTitle.textContent = 'Manage Drugs';
		drugSubmitButton.textContent = 'Add Drug';
		cancelDrugEditButton.classList.add('hidden');
		editingDrugId = null;
	}

	function populateDrugFormForEdit(drug) {
		drugFormTitle.textContent = 'Edit Drug';
		drugSubmitButton.textContent = 'Update Drug';
		cancelDrugEditButton.classList.remove('hidden');
		drugIdInput.value = drug.id;
		drugNameInput.value = drug.name;
		drugCategorySelect.value = drug.category_id;
		drugQuantityInput.value = drug.quantity;
		drugExpiryDateInput.value = drug.expiry_date;
		editingDrugId = drug.id;
	}

<<<<<<< HEAD






	// --- Dashboard Statistics ---
	function updateDashboardStats(drugs) {
		if (!dashboardStats) return;
		
		const totalDrugs = drugs.length;
		const lowStockCount = drugs.filter(d => d.quantity <= LOW_STOCK_THRESHOLD).length;
		const expiredCount = drugs.filter(d => {
			if (!d.expiry_date) return false;
			return new Date(d.expiry_date) <= new Date();
		}).length;
		const expiringSoonCount = drugs.filter(d => {
			if (!d.expiry_date) return false;
			const daysUntilExpiry = Math.ceil((new Date(d.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
			return daysUntilExpiry > 0 && daysUntilExpiry <= EXPIRY_WARNING_DAYS;
		}).length;
		
		dashboardStats.innerHTML = `
			<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
				<div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-gray-500">Total Drugs</p>
							<p class="text-2xl font-semibold text-gray-900">${totalDrugs}</p>
						</div>
					</div>
				</div>
				
				<div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-gray-500">Low Stock</p>
							<p class="text-2xl font-semibold text-gray-900">${lowStockCount}</p>
						</div>
					</div>
				</div>
				
				<div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-gray-500">Expired</p>
							<p class="text-2xl font-semibold text-gray-900">${expiredCount}</p>
						</div>
					</div>
				</div>
				
				<div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-gray-500">Expiring Soon</p>
							<p class="text-2xl font-semibold text-gray-900">${expiringSoonCount}</p>
						</div>
					</div>
				</div>
			</div>
		`;
	}

	// --- Enhanced Search and Filter Functions ---
	let searchTimeout;
	
	function setupSearchAndFilter() {
		const searchInput = document.getElementById('searchDrugs');
		const filterSelect = document.getElementById('filterCategory');
		
		if (searchInput) {
			searchInput.addEventListener('input', (e) => {
				// Clear previous timeout
				clearTimeout(searchTimeout);
				
				// Debounce search for better performance
				searchTimeout = setTimeout(() => {
					searchTerm = e.target.value.toLowerCase();
					applyFilters();
					
					// Show search results count
					updateSearchResultsCount();
				}, 300);
			});
			
			// Add search input enhancements
			searchInput.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') {
					searchInput.value = '';
					searchTerm = '';
					applyFilters();
					updateSearchResultsCount();
				}
			});
		}
		
		if (filterSelect) {
			filterSelect.addEventListener('change', (e) => {
				selectedCategory = e.target.value;
				applyFilters();
				updateSearchResultsCount();
			});
		}
	}

	function applyFilters() {
		if (!allDrugs) return;
		
		filteredDrugs = allDrugs.filter(drug => {
			const matchesSearch = drug.name.toLowerCase().includes(searchTerm) ||
								drug.category_name?.toLowerCase().includes(searchTerm) ||
								drug.id.toString().includes(searchTerm);
			
			const matchesCategory = !selectedCategory || drug.category_id == selectedCategory;
			
			return matchesSearch && matchesCategory;
		});
		
		renderDrugTable(filteredDrugs);
		updateSearchResultsCount();
	}

	function updateSearchResultsCount() {
		const searchInput = document.getElementById('searchDrugs');
		const resultsCount = document.getElementById('searchResultsCount');
		
		if (!searchInput || !resultsCount) return;
		
		const totalCount = allDrugs.length;
		const filteredCount = filteredDrugs.length;
		
		if (searchTerm || selectedCategory) {
			resultsCount.textContent = `Showing ${filteredCount} of ${totalCount} drugs`;
			resultsCount.classList.remove('hidden');
		} else {
			resultsCount.classList.add('hidden');
		}
	}

	function clearFilters() {
		const searchInput = document.getElementById('searchDrugs');
		const filterSelect = document.getElementById('filterCategory');
		
		if (searchInput) searchInput.value = '';
		if (filterSelect) filterSelect.value = '';
		
		searchTerm = '';
		selectedCategory = '';
		applyFilters();
	}

	function populateFilterDropdown(categories) {
		const filterSelect = document.getElementById('filterCategory');
		if (!filterSelect) return;
		
		filterSelect.innerHTML = '<option value="">All Categories</option>';
		categories.forEach(category => {
			const option = document.createElement('option');
			option.value = category.id;
			option.textContent = category.name;
			filterSelect.appendChild(option);
		});
	}

	// --- Enhanced Drug Table Rendering ---
	function renderDrugTable(drugs) {
		if (!drugTableBody) return;
		
		drugTableBody.innerHTML = '';
		allDrugs = drugs; // Store for alerts
		
		if (!drugs || drugs.length === 0) {
			drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No drugs found. Add some above!</td></tr>';
			return;
		}
		
		// Update dashboard stats
		updateDashboardStats(drugs);
		
		drugs.forEach(drug => {
			const row = document.createElement('tr');
			row.dataset.drug = JSON.stringify(drug);
			
			// Determine row styling based on alerts
			let rowClasses = 'hover:bg-gray-50';
			if (drug.quantity === 0) {
				rowClasses += ' bg-red-50';
			} else if (drug.quantity <= LOW_STOCK_THRESHOLD) {
				rowClasses += ' bg-yellow-50';
			}
			
			// Check expiry
			if (drug.expiry_date) {
				const expiryDate = new Date(drug.expiry_date);
				const today = new Date();
				if (expiryDate <= today) {
					rowClasses += ' bg-red-100';
				} else if (expiryDate <= new Date(today.getTime() + EXPIRY_WARNING_DAYS * 24 * 60 * 60 * 1000)) {
					rowClasses += ' bg-orange-50';
				}
			}
			
			row.className = rowClasses;
			
			// Create quantity cell with visual indicators
			let quantityCell = drug.quantity;
			if (drug.quantity === 0) {
				quantityCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>`;
			} else if (drug.quantity <= LOW_STOCK_THRESHOLD) {
				quantityCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">${drug.quantity} (Low)</span>`;
			}
			
			// Create expiry cell with visual indicators
			let expiryCell = drug.expiry_date || 'N/A';
			if (drug.expiry_date) {
				const expiryDate = new Date(drug.expiry_date);
				const today = new Date();
				if (expiryDate <= today) {
					expiryCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>`;
				} else if (expiryDate <= new Date(today.getTime() + EXPIRY_WARNING_DAYS * 24 * 60 * 60 * 1000)) {
					const daysLeft = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
					expiryCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">${drug.expiry_date} (${daysLeft} days)</span>`;
				}
			}
			
			row.innerHTML = `
				<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drug.id}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.name}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.category_name || 'N/A'}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${quantityCell}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${expiryCell}</td>
				<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
					<a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-drug-btn" data-drug-id="${drug.id}">Edit</a>
					<a href="#" class="text-red-600 hover:text-red-900 delete-drug-btn" data-drug-id="${drug.id}">Delete</a>
				</td>
			`;
			drugTableBody.appendChild(row);
		});
	}

	// --- Missing Functions ---
=======
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
	cancelDrugEditButton.addEventListener('click', resetDrugForm);

	function populateDrugCategoryDropDown(categories) {
		drugCategorySelect.innerHTML = '<option value="">Select a category</option>';
		categories.forEach(category => {
			const option = document.createElement('option');
			option.value = category.id;
			option.textContent = category.name;
			drugCategorySelect.appendChild(option);
		});
	}

	async function fetchCategories() {
		try {
<<<<<<< HEAD
			console.log('Fetching categories from:', CATEGORY_API_URL);
			const response = await fetch(CATEGORY_API_URL, { method: 'GET' });
			console.log('Categories API response status:', response.status);
			console.log('Categories API response headers:', response.headers);
			
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			
			const result = await response.json();
			console.log('Categories API result:', result);
			if (result.success) {
				allCategories = result.data;
				console.log('Categories loaded:', allCategories);
				populateDrugCategoryDropDown(allCategories);
				populateFilterDropdown(allCategories); // Populate filter dropdown
			} else {
				console.error('Categories API returned error:', result.message);
=======
			const response = await fetch(CATEGORY_API_URL, { method: 'GET' });
			const result = await response.json();
			if (result.success) {
				allCategories = result.data;
				populateDrugCategoryDropDown(allCategories);
				// also populate distribution drug dropdown with live drugs
				await populateDistributionDrugDropdown();
			} else {
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error fetching categories:', error);
<<<<<<< HEAD
			displayMessage('Failed to load categories: ' + error.message, 'error');
=======
			displayMessage('Failed to load categories.', 'error');
		}
	}

	async function populateDistributionDrugDropdown() {
		// Load a large page of drugs for selection
		try {
			const response = await fetch(`${DRUG_API_URL}&page=1&limit=1000`);
			const result = await response.json();
			if (result.success) {
				distDrugSelect.innerHTML = '<option value="">Select drug</option>';
				result.data.forEach(drug => {
					const opt = document.createElement('option');
					opt.value = drug.id;
					opt.textContent = `${drug.name} (Qty: ${drug.quantity})`;
					distDrugSelect.appendChild(opt);
				});
			}
		} catch (e) {
			console.log('Error populating distribution drugs', e);
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
		}
	}

	async function fetchDrugs() {
<<<<<<< HEAD
		try {
			console.log('Fetching drugs from:', DRUG_API_URL);
			const response = await fetch(DRUG_API_URL, { method: 'GET' });
			console.log('Drugs API response status:', response.status);
			console.log('Drugs API response headers:', response.headers);
			
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			
			const result = await response.json();
			console.log('Drugs API result:', result);
			if (result.success) {
				allDrugs = result.data;
				filteredDrugs = [...allDrugs];
				console.log('Drugs loaded:', allDrugs);
				renderDrugsTable();
			} else {
				console.error('Drugs API returned error:', result.message);
=======
		drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>';
		try {
			const response = await fetch(`${DRUG_API_URL}&page=${currentPage}&limit=${drugsPerPage}`, { method: 'GET' });
			const result = await response.json();
			if (result.success) {
				renderDrugTable(result.data);
				renderPaginationControls(result.total_drugs);
			} else {
				drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error fetching drugs:', error);
<<<<<<< HEAD
			displayMessage('Failed to load drugs: ' + error.message, 'error');
		}
	}

=======
			drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>';
			displayMessage('Failed to connect to the server. Please try again later.', 'error');
		}
	}

	function renderDrugTable(drugs) {
		drugTableBody.innerHTML = '';
		if (!drugs || drugs.length === 0) {
			drugTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No drugs found. Add some above!</td></tr>';
			return;
		}
		drugs.forEach(drug => {
			const row = document.createElement('tr');
			row.dataset.drug = JSON.stringify(drug);
			row.innerHTML = `
				<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drug.id}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.name}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.category_name || 'N/A'}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.quantity}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.expiry_date || 'N/A'}</td>
				<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
					<a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-drug-btn" data-drug-id="${drug.id}">Edit</a>
					<a href="#" class="text-red-600 hover:text-red-900 delete-drug-btn" data-drug-id="${drug.id}">Delete</a>
				</td>
			`;
			drugTableBody.appendChild(row);
		});
	}

>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
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

<<<<<<< HEAD
	// --- Tabs behavior ---
	function showTab(name) {
		// Hide all tabs
		[tabReports, tabNotifications, tabManagement].forEach(el => { if (el) el.classList.add('hidden'); });
		
		// Remove active class from all tab buttons
		[tabReportsBtn, tabNotificationsBtn, tabManagementBtn].forEach(btn => {
			if (btn) {
				btn.classList.remove('bg-blue-50', 'border-blue-500', 'text-blue-700');
				btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
=======
	// ---------------- Distributions --------------------
	async function fetchDistributions() {
		distTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>';
		try {
			const response = await fetch(`${DIST_API_URL}&page=${distPage}&limit=${distPerPage}`);
			const result = await response.json();
			if (result.success) {
				renderDistributionTable(result.data);
				renderDistPagination(result.total);
			} else {
				distTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
			}
		} catch (e) {
			distTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Network error.</td></tr>';
		}
	}

	function renderDistributionTable(rows) {
		distTableBody.innerHTML = '';
		if (!rows || rows.length === 0) {
			distTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No records</td></tr>';
			return;
		}
		rows.forEach(r => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${r.date_issued}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${r.drug_name}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${r.quantity_given}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${r.recipient || 'N/A'}</td>
			`;
			distTableBody.appendChild(tr);
		});
	}

	function renderDistPagination(total) {
		distPagination.innerHTML = '';
		const totalPages = Math.ceil(total / distPerPage);
		if (totalPages <= 1) return;
		const prev = document.createElement('button');
		prev.textContent = 'Previous';
		prev.disabled = distPage === 1;
		prev.addEventListener('click', () => { if (distPage > 1) { distPage--; fetchDistributions(); } });
		distPagination.appendChild(prev);
		for (let i=1; i<= totalPages; i++) {
			const b = document.createElement('button');
			b.textContent = i;
			if (i === distPage) b.classList.add('active-page');
			b.addEventListener('click', () => { distPage = i; fetchDistributions(); });
			distPagination.appendChild(b);
		}
		const next = document.createElement('button');
		next.textContent = 'Next';
		next.disabled = distPage === totalPages;
		next.addEventListener('click', () => { if (distPage < totalPages) { distPage++; fetchDistributions(); } });
		distPagination.appendChild(next);
	}

	if (distributionForm) {
		distributionForm.addEventListener('submit', async (e) => {
			e.preventDefault();
			const drug_id = parseInt(distDrugSelect.value);
			const quantity_given = parseInt(distQuantityInput.value);
			const recipient = distRecipientInput.value.trim() || null;
			if (!drug_id || !quantity_given || quantity_given <= 0) {
				displayMessage('Select a drug and enter a valid quantity.', 'error');
				return;
			}
			try {
				const response = await fetch(DIST_API_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ drug_id, quantity_given, recipient }) });
				const result = await response.json();
				if (result.success) {
					displayMessage(result.message, 'success');
					distQuantityInput.value = '';
					distRecipientInput.value = '';
					fetchDrugs();
					populateDistributionDrugDropdown();
					fetchDistributions();
				} else {
					displayMessage(result.message, 'error');
				}
			} catch (e) {
				displayMessage('Failed to record distribution.', 'error');
			}
		});
	}

	// ---------------- Reports -------------------
	let chartMonth, chartYear;
	async function fetchTop(period) {
		const response = await fetch(`${DIST_API_URL}&aggregate=top&period=${period}`);
		return response.json();
	}

	async function renderCharts() {
		try {
			const [m, y] = await Promise.all([fetchTop('month'), fetchTop('year')]);
			const monthLabels = (m.data || []).map(r => r.name);
			const monthData = (m.data || []).map(r => parseInt(r.total_given));
			const yearLabels = (y.data || []).map(r => r.name);
			const yearData = (y.data || []).map(r => parseInt(r.total_given));
			const ctxM = document.getElementById('chartTopMonth').getContext('2d');
			const ctxY = document.getElementById('chartTopYear').getContext('2d');
			if (chartMonth) chartMonth.destroy();
			if (chartYear) chartYear.destroy();
			chartMonth = new Chart(ctxM, { type: 'bar', data: { labels: monthLabels, datasets: [{ label: 'Qty Given (Month)', data: monthData, backgroundColor: '#60a5fa' }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
			chartYear = new Chart(ctxY, { type: 'bar', data: { labels: yearLabels, datasets: [{ label: 'Qty Given (Year)', data: yearData, backgroundColor: '#34d399' }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
		} catch (e) {
			console.log('Error rendering charts', e);
		}
	}

	// Tabs behavior
	function showTab(name) {
		// Hide all tabs
		[tabDistribute, tabReports, tabManagement].forEach(el => { if (el) el.classList.add('hidden'); });
		
		// Remove active class from all tab buttons
		[tabDistributeBtn, tabReportsBtn, tabManagementBtn].forEach(btn => {
			if (btn) {
				btn.classList.remove('active', 'border-blue-600');
				btn.classList.add('border-transparent');
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
			}
		});
		
		// Show selected tab and activate button
<<<<<<< HEAD
		if (name === 'reports') { 
			tabReports.classList.remove('hidden'); 
			tabReportsBtn.classList.add('bg-blue-50', 'border-blue-500', 'text-blue-700');
			tabReportsBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
		}
		if (name === 'notifications') { 
			tabNotifications.classList.remove('hidden'); 
			tabNotificationsBtn.classList.add('bg-blue-50', 'border-blue-500', 'text-blue-700');
			tabNotificationsBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
		}
		if (name === 'management') { 
			tabManagement.classList.remove('hidden'); 
			tabManagementBtn.classList.add('bg-blue-50', 'border-blue-500', 'text-blue-700');
			tabManagementBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
		}
	}

	// --- Refresh functionality ---
	function refreshAllData() {
		const lastUpdatedElement = document.getElementById('lastUpdated');
		if (lastUpdatedElement) {
			lastUpdatedElement.textContent = 'Refreshing...';
		}
		
		Promise.all([
			fetchCategories(),
			fetchDrugs()
		]).then(() => {
			if (lastUpdatedElement) {
				const now = new Date();
				lastUpdatedElement.textContent = now.toLocaleTimeString();
			}
			displayMessage('Data refreshed successfully', 'success');
		}).catch(error => {
			console.error('Error refreshing data:', error);
			displayMessage('Failed to refresh data', 'error');
			if (lastUpdatedElement) {
				lastUpdatedElement.textContent = 'Error';
			}
		});
	}

	// Make refresh function globally available
	window.refreshAllData = refreshAllData;
	window.clearFilters = clearFilters;
	window.showKeyboardShortcuts = showKeyboardShortcuts;
	
	// --- Professional Dashboard Features ---
	function showKeyboardShortcuts() {
		if (window.Swal) {
			Swal.fire({
				title: 'Keyboard Shortcuts',
				html: `
					<div class="text-left space-y-2">
						<div class="flex justify-between">
							<span class="font-medium">Search Focus:</span>
							<kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + K</kbd>
						</div>
						<div class="flex justify-between">
							<span class="font-medium">Refresh Data:</span>
							<kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + R</kbd>
						</div>
						<div class="flex justify-between">
							<span class="font-medium">Clear Search:</span>
							<kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Escape</kbd>
						</div>
						<div class="flex justify-between">
							<span class="font-medium">Export Data:</span>
							<span class="text-sm text-gray-600">Click Export button</span>
						</div>
					</div>
				`,
				icon: 'info',
				confirmButtonText: 'Got it!',
				confirmButtonColor: '#3b82f6'
			});
		} else {
			alert('Keyboard Shortcuts:\nCtrl/Cmd + K: Search Focus\nCtrl/Cmd + R: Refresh Data\nEscape: Clear Search');
		}
	}

	function setupKeyboardShortcuts() {
		document.addEventListener('keydown', (e) => {
			// Ctrl/Cmd + K for search focus
			if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
				e.preventDefault();
				const searchInput = document.getElementById('searchDrugs');
				if (searchInput) {
					searchInput.focus();
					searchInput.select();
				}
			}
			
			// Ctrl/Cmd + R for refresh
			if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
				e.preventDefault();
				refreshAllData();
			}
			
			// Escape to clear search
			if (e.key === 'Escape') {
				const searchInput = document.getElementById('searchDrugs');
				if (searchInput && document.activeElement === searchInput) {
					clearFilters();
				}
			}
		});
	}

	function setupTooltips() {
		// Add tooltips to action buttons
		const tooltipElements = document.querySelectorAll('[data-tooltip]');
		tooltipElements.forEach(element => {
			element.addEventListener('mouseenter', (e) => {
				const tooltip = document.createElement('div');
				tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
				tooltip.textContent = e.target.dataset.tooltip;
				tooltip.style.top = (e.target.offsetTop - 30) + 'px';
				tooltip.style.left = (e.target.offsetLeft + e.target.offsetWidth / 2) + 'px';
				tooltip.id = 'tooltip';
				
				document.body.appendChild(tooltip);
			});
			
			element.addEventListener('mouseleave', () => {
				const tooltip = document.getElementById('tooltip');
				if (tooltip) tooltip.remove();
			});
		});
	}

	function addExportFunctionality() {
		// Add export button to dashboard
		const exportBtn = document.createElement('button');
		exportBtn.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2';
		exportBtn.innerHTML = `
			<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
			</svg>
			<span>Export</span>
		`;
		exportBtn.onclick = exportInventoryData;
		
		// Insert after refresh button
		const refreshBtn = document.querySelector('button[onclick="refreshAllData()"]');
		if (refreshBtn && refreshBtn.parentNode) {
			refreshBtn.parentNode.insertBefore(exportBtn, refreshBtn.nextSibling);
		}
	}

	function exportInventoryData() {
		if (!allDrugs || allDrugs.length === 0) {
			displayMessage('No data to export', 'warning');
			return;
		}
		
		// Create CSV content
		const headers = ['ID', 'Name', 'Category', 'Quantity', 'Expiry Date', 'Status'];
		const csvContent = [
			headers.join(','),
			...allDrugs.map(drug => {
				const status = getDrugStatus(drug);
				return [
					drug.id,
					`"${drug.name}"`,
					`"${drug.category_name || 'N/A'}"`,
					drug.quantity,
					drug.expiry_date || 'N/A',
					`"${status}"`
				].join(',');
			})
		].join('\n');
		
		// Create and download file
		const blob = new Blob([csvContent], { type: 'text/csv' });
		const url = window.URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = `inventory_${new Date().toISOString().split('T')[0]}.csv`;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		window.URL.revokeObjectURL(url);
		
		displayMessage('Inventory exported successfully', 'success');
	}

	function getDrugStatus(drug) {
		if (drug.quantity === 0) return 'Out of Stock';
		if (drug.quantity <= LOW_STOCK_THRESHOLD) return 'Low Stock';
		if (drug.expiry_date) {
			const expiryDate = new Date(drug.expiry_date);
			const today = new Date();
			if (expiryDate <= today) return 'Expired';
			if (expiryDate <= new Date(today.getTime() + EXPIRY_WARNING_DAYS * 24 * 60 * 60 * 1000)) return 'Expiring Soon';
		}
		return 'In Stock';
	}

	if (tabReportsBtn) tabReportsBtn.addEventListener('click', () => showTab('reports'));
	if (tabNotificationsBtn) tabNotificationsBtn.addEventListener('click', () => showTab('notifications'));
	if (tabManagementBtn) tabManagementBtn.addEventListener('click', () => showTab('management'));

	// --- Event Listeners ---
=======
		if (name === 'distribute') { 
			tabDistribute.classList.remove('hidden'); 
			tabDistributeBtn.classList.add('active', 'border-blue-600');
			tabDistributeBtn.classList.remove('border-transparent');
			fetchDistributions(); 
		}
		if (name === 'reports') { 
			tabReports.classList.remove('hidden'); 
			tabReportsBtn.classList.add('active', 'border-blue-600');
			tabReportsBtn.classList.remove('border-transparent');
			renderCharts(); 
		}
		if (name === 'management') { 
			tabManagement.classList.remove('hidden'); 
			tabManagementBtn.classList.add('active', 'border-blue-600');
			tabManagementBtn.classList.remove('border-transparent');
		}
	}
	
	if (tabDistributeBtn) tabDistributeBtn.addEventListener('click', () => showTab('distribute'));
	if (tabReportsBtn) tabReportsBtn.addEventListener('click', () => showTab('reports'));
	if (tabManagementBtn) tabManagementBtn.addEventListener('click', () => showTab('management'));

>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
	// Capitalize drug name words on blur
	drugNameInput.addEventListener('blur', () => {
		const raw = drugNameInput.value.trim();
		const capitalized = raw.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
		drugNameInput.value = capitalized;
	});

	drugForm.addEventListener('submit', async (event) => {
		event.preventDefault();

<<<<<<< HEAD
		// Professional validation
=======
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
		let rawName = drugNameInput.value.trim();
		let capitalizeName = rawName.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
		drugNameInput.value = capitalizeName;

		const drugData = {
			name: capitalizeName,
			category_id: parseInt(drugCategorySelect.value),
			quantity: parseInt(drugQuantityInput.value),
			expiry_date: drugExpiryDateInput.value
		};

<<<<<<< HEAD
		// Validate data before submission
		const validationErrors = validateDrugData(drugData);
		if (validationErrors.length > 0) {
			showValidationErrors(validationErrors);
			return;
		}

		// Set loading state
		setLoadingState(true, 'drugSubmitButton');
		const originalButtonText = drugSubmitButton.textContent;
		drugSubmitButton.innerHTML = `
			<div class="flex items-center justify-center">
				<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
				${editingDrugId ? 'Updating...' : 'Adding...'}
			</div>
		`;

=======
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
		let method = 'POST';
		let url = DRUG_API_URL;
		if (editingDrugId) {
			method = 'PUT';
			drugData.id = editingDrugId;
		}

		try {
<<<<<<< HEAD
			const response = await fetch(url, { 
				method, 
				headers: { 'Content-Type': 'application/json' }, 
				body: JSON.stringify(drugData) 
			});
			
			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}
			
=======
			const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(drugData) });
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
			const result = await response.json();
			if (result.success) {
				displayMessage(result.message, 'success');
				resetDrugForm();
				fetchDrugs();
<<<<<<< HEAD
				
				// Show success animation
				if (window.Swal) {
					Swal.fire({
						icon: 'success',
						title: editingDrugId ? 'Drug Updated!' : 'Drug Added!',
						text: result.message,
						timer: 2000,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				}
=======
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
			} else {
				displayMessage(result.message, 'error');
			}
		} catch (error) {
<<<<<<< HEAD
			handleApiError(error, `${method} drug`);
		} finally {
			// Reset loading state
			setLoadingState(false, 'drugSubmitButton');
			drugSubmitButton.innerHTML = originalButtonText;
=======
			console.log(`Error ${method}ing drug: `, error);
			displayMessage(`Failed to ${method} drug due to a network error.`, 'error');
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
		}
	});

	drugTableBody.addEventListener('click', async (event) => {
		if (event.target.classList.contains('edit-drug-btn')) {
			event.preventDefault();
			const row = event.target.closest('tr');
			const drugData = JSON.parse(row.dataset.drug);
			populateDrugFormForEdit(drugData);
<<<<<<< HEAD
			
			// Scroll to form
			document.getElementById('drugForm').scrollIntoView({ 
				behavior: 'smooth', 
				block: 'center' 
			});
		} else if (event.target.classList.contains('delete-drug-btn')) {
			event.preventDefault();
			const drugIdToDelete = event.target.dataset.drugId;
			const row = event.target.closest('tr');
			const drugData = JSON.parse(row.dataset.drug);
			
			// Professional confirmation dialog
			const confirmed = await showConfirm(`Are you sure you want to delete <strong>${drugData.name}</strong>?<br><br><span class="text-sm text-gray-600">This action cannot be undone.</span>`);
			
			if (confirmed) {
				// Set loading state on the delete button
				const deleteBtn = event.target;
				const originalText = deleteBtn.textContent;
				deleteBtn.innerHTML = `
					<div class="flex items-center">
						<div class="animate-spin rounded-full h-3 w-3 border-b-2 border-red-600 mr-2"></div>
						Deleting...
					</div>
				`;
				deleteBtn.disabled = true;
				
				try {
					const response = await fetch(DRUG_API_URL, { 
						method: 'DELETE', 
						headers: { 'Content-Type': 'application/json' }, 
						body: JSON.stringify({ id: drugIdToDelete }) 
					});
					
					if (!response.ok) {
						throw new Error(`HTTP ${response.status}: ${response.statusText}`);
					}
					
					const result = await response.json();
					if (result.success) {
						// Show success message
						displayMessage(result.message, 'success');
						
						// Animate row removal
						row.style.transition = 'all 0.3s ease';
						row.style.opacity = '0';
						row.style.transform = 'translateX(-100%)';
						
						setTimeout(() => {
							fetchDrugs();
						}, 300);
=======
		} else if (event.target.classList.contains('delete-drug-btn')) {
			event.preventDefault();
			const drugIdToDelete = event.target.dataset.drugId;
			const confirmed = await showConfirm(`Are you sure you want to delete drug ID: ${drugIdToDelete}?`);
			if (confirmed) {
				try {
					const response = await fetch(DRUG_API_URL, { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: drugIdToDelete }) });
					const result = await response.json();
					if (result.success) {
						displayMessage(result.message, 'success');
						fetchDrugs();
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
					} else {
						displayMessage(result.message, 'error');
					}
				} catch (error) {
<<<<<<< HEAD
					handleApiError(error, 'delete drug');
				} finally {
					// Reset button state
					deleteBtn.innerHTML = originalText;
					deleteBtn.disabled = false;
=======
					console.log('Error deleting drug: ', error);
					displayMessage('Failed to delete drug due to a network error.', 'error');
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
				}
			}
		}
	});

<<<<<<< HEAD
	// --- Auto-refresh functionality ---
	function startAutoRefresh() {
		// Refresh data every 5 minutes
		setInterval(() => {
			fetchDrugs();
		}, 5 * 60 * 1000);
	}

	// --- Initial load ---
	fetchCategories();
	fetchDrugs();
	showTab('management'); // Start with management tab as default
	startAutoRefresh(); // Start auto-refresh
	setupSearchAndFilter(); // Setup search and filter functionality
	setupKeyboardShortcuts(); // Setup keyboard shortcuts
	setupTooltips(); // Setup tooltips
	addExportFunctionality(); // Add export functionality


	// --- Professional Validation & Error Handling ---
	function validateDrugData(drugData) {
		const errors = [];
		
		// Drug name validation
		if (!drugData.name || drugData.name.trim().length < 3) {
			errors.push('Drug name must be at least 3 characters long');
		}
		
		// Category validation
		if (!drugData.category_id || drugData.category_id <= 0) {
			errors.push('Please select a valid category');
		}
		
		// Quantity validation
		if (drugData.quantity < 0) {
			errors.push('Quantity cannot be negative');
		}
		if (drugData.quantity > 999999) {
			errors.push('Quantity is too high');
		}
		
		// Expiry date validation
		if (drugData.expiry_date) {
			const expiryDate = new Date(drugData.expiry_date);
			const today = new Date();
			if (expiryDate <= today) {
				errors.push('Expiry date must be in the future');
			}
		}
		
		return errors;
	}

	function showValidationErrors(errors) {
		if (window.Swal) {
			Swal.fire({
				icon: 'error',
				title: 'Validation Errors',
				html: errors.map(error => `<div class="text-left">• ${error}</div>`).join(''),
				confirmButtonText: 'Fix Errors',
				confirmButtonColor: '#ef4444'
			});
		} else {
			alert('Validation errors:\n' + errors.join('\n'));
		}
	}

	// --- Enhanced Error Handling ---
	function handleApiError(error, context) {
		console.error(`Error in ${context}:`, error);
		
		let userMessage = 'An unexpected error occurred. Please try again.';
		
		if (error.name === 'TypeError' && error.message.includes('fetch')) {
			userMessage = 'Network error. Please check your connection and try again.';
		} else if (error.status === 401) {
			userMessage = 'Session expired. Please log in again.';
			setTimeout(() => window.location.href = '../login.php', 2000);
		} else if (error.status === 403) {
			userMessage = 'Access denied. You do not have permission for this action.';
		} else if (error.status === 404) {
			userMessage = 'Resource not found. Please refresh the page.';
		} else if (error.status >= 500) {
			userMessage = 'Server error. Please try again later.';
		}
		
		displayMessage(userMessage, 'error');
	}

	// --- Professional Loading States ---
	function setLoadingState(isLoading, elementId) {
		const element = document.getElementById(elementId);
		if (!element) return;
		
		if (isLoading) {
			element.classList.add('loading');
			element.disabled = true;
		} else {
			element.classList.remove('loading');
			element.disabled = false;
		}
	}

	function showGlobalLoading() {
		const loadingOverlay = document.createElement('div');
		loadingOverlay.id = 'globalLoading';
		loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
		loadingOverlay.innerHTML = `
			<div class="bg-white rounded-lg p-6 flex items-center space-x-3">
				<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
				<span class="text-gray-700">Processing...</span>
			</div>
		`;
		document.body.appendChild(loadingOverlay);
	}

	function hideGlobalLoading() {
		const loadingOverlay = document.getElementById('globalLoading');
		if (loadingOverlay) {
			loadingOverlay.remove();
		}
	}

	// --- Event Listeners Setup ---
	function setupEventListeners() {
		// Drug form submission
		if (drugForm) {
			drugForm.addEventListener('submit', handleDrugFormSubmit);
		}
		
		// Add drug button
		const addDrugBtn = document.getElementById('addDrugBtn');
		if (addDrugBtn) {
			addDrugBtn.addEventListener('click', () => {
				resetDrugForm();
				showDrugModal();
			});
		}
		
		// Close modal button
		const closeDrugModal = document.getElementById('closeDrugModal');
		if (closeDrugModal) {
			closeDrugModal.addEventListener('click', hideDrugModal);
		}
		
		// Cancel drug button
		const cancelDrug = document.getElementById('cancelDrug');
		if (cancelDrug) {
			cancelDrug.addEventListener('click', hideDrugModal);
		}
		
		// Cancel edit button
		if (cancelDrugEditButton) {
			cancelDrugEditButton.addEventListener('click', resetDrugForm);
		}
	}

	function setupSearchAndFilters() {
		// Search functionality
		const searchInput = document.getElementById('searchDrugs');
		if (searchInput) {
			searchInput.addEventListener('input', (e) => {
				searchTerm = e.target.value.toLowerCase();
				filterDrugs();
			});
		}
		
		// Category filter
		const filterCategory = document.getElementById('filterCategory');
		if (filterCategory) {
			filterCategory.addEventListener('change', (e) => {
				selectedCategory = e.target.value;
				filterDrugs();
			});
		}
	}

	function setupKeyboardShortcuts() {
		// Add keyboard shortcuts if needed
	}

	function setupTooltips() {
		// Add tooltips if needed
	}

	function addExportFunctionality() {
		// Add export functionality if needed
	}

	function showDrugModal() {
		const modal = document.getElementById('drugModal');
		if (modal) {
			modal.classList.remove('hidden');
		}
	}

	function hideDrugModal() {
		const modal = document.getElementById('drugModal');
		if (modal) {
			modal.classList.add('hidden');
		}
	}

	// --- Drug Form Handling ---
	async function handleDrugFormSubmit(e) {
		e.preventDefault();
		
		const formData = new FormData(drugForm);
		const drugData = {
			id: formData.get('id') || null,
			name: formData.get('name'),
			category_id: formData.get('category_id'),
			quantity: parseInt(formData.get('quantity')),
			expiry_date: formData.get('expiry_date') || null
		};
		
		// Validate form data
		const errors = validateDrugData(drugData);
		if (errors.length > 0) {
			showValidationErrors(errors);
			return;
		}
		
		try {
			showGlobalLoading();
			
			const url = drugData.id ? 
				`${DRUG_API_URL}&id=${drugData.id}` : 
				DRUG_API_URL;
			
			const method = drugData.id ? 'PUT' : 'POST';
			
			const response = await fetch(url, {
				method: method,
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(drugData)
			});
			
			const result = await response.json();
			
			if (result.success) {
				displayMessage(drugData.id ? 'Drug updated successfully!' : 'Drug added successfully!', 'success');
				hideDrugModal();
				resetDrugForm();
				fetchDrugs(); // Refresh the drugs list
			} else {
				displayMessage(result.message || 'Failed to save drug', 'error');
			}
		} catch (error) {
			handleApiError(error, 'saving drug');
		} finally {
			hideGlobalLoading();
		}
	}

	function filterDrugs() {
		filteredDrugs = allDrugs.filter(drug => {
			const matchesSearch = !searchTerm || 
				drug.name.toLowerCase().includes(searchTerm) ||
				(drug.category_name && drug.category_name.toLowerCase().includes(searchTerm));
			
			const matchesCategory = !selectedCategory || 
				drug.category_id == selectedCategory;
			
			return matchesSearch && matchesCategory;
		});
		
		renderDrugsTable();
	}

	function renderDrugsTable() {
		if (!drugTableBody) return;
		
		drugTableBody.innerHTML = '';
		
		if (filteredDrugs.length === 0) {
			drugTableBody.innerHTML = `
				<tr>
					<td colspan="6" class="px-6 py-4 text-center text-gray-500">
						No drugs found matching your criteria.
					</td>
				</tr>
			`;
			return;
		}
		
		filteredDrugs.forEach(drug => {
			const row = document.createElement('tr');
			row.className = 'hover:bg-gray-50';
			
			// Determine row styling based on stock levels
			if (drug.quantity === 0) {
				row.classList.add('bg-red-50');
			} else if (drug.quantity <= LOW_STOCK_THRESHOLD) {
				row.classList.add('bg-yellow-50');
			}
			
			// Check expiry
			if (drug.expiry_date) {
				const expiryDate = new Date(drug.expiry_date);
				const today = new Date();
				if (expiryDate <= today) {
					row.classList.add('bg-red-100');
				} else if (expiryDate <= new Date(today.getTime() + EXPIRY_WARNING_DAYS * 24 * 60 * 60 * 1000)) {
					row.classList.add('bg-orange-50');
				}
			}
			
			// Create quantity cell with visual indicators
			let quantityCell = drug.quantity;
			if (drug.quantity === 0) {
				quantityCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>`;
			} else if (drug.quantity <= LOW_STOCK_THRESHOLD) {
				quantityCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">${drug.quantity} (Low)</span>`;
			}
			
			// Create expiry cell with visual indicators
			let expiryCell = drug.expiry_date || 'N/A';
			if (drug.expiry_date) {
				const expiryDate = new Date(drug.expiry_date);
				const today = new Date();
				if (expiryDate <= today) {
					expiryCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>`;
				} else if (expiryDate <= new Date(today.getTime() + EXPIRY_WARNING_DAYS * 24 * 60 * 60 * 1000)) {
					const daysLeft = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
					expiryCell = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">${drug.expiry_date} (${daysLeft} days)</span>`;
				}
			}
			
			row.innerHTML = `
				<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drug.id}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.name}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drug.category_name || 'N/A'}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${quantityCell}</td>
				<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${expiryCell}</td>
				<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
					<button onclick="editDrug(${drug.id})" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
					<button onclick="deleteDrug(${drug.id})" class="text-red-600 hover:text-red-900">Delete</button>
				</td>
			`;
			
			drugTableBody.appendChild(row);
		});
	}

	// --- Drug Actions ---
	function editDrug(drugId) {
		const drug = allDrugs.find(d => d.id == drugId);
		if (drug) {
			populateDrugFormForEdit(drug);
			showDrugModal();
		}
	}

	async function deleteDrug(drugId) {
		const drug = allDrugs.find(d => d.id == drugId);
		if (!drug) return;
		
		const confirmed = await showConfirm(`Are you sure you want to delete "${drug.name}"? This action cannot be undone.`);
		if (!confirmed) return;
		
		try {
			showGlobalLoading();
			
			const response = await fetch(`${DRUG_API_URL}&id=${drugId}`, {
				method: 'DELETE'
			});
			
			const result = await response.json();
			
			if (result.success) {
				displayMessage('Drug deleted successfully!', 'success');
				fetchDrugs(); // Refresh the drugs list
			} else {
				displayMessage(result.message || 'Failed to delete drug', 'error');
			}
		} catch (error) {
			handleApiError(error, 'deleting drug');
		} finally {
			hideGlobalLoading();
		}
	}

	// Make functions globally available
	window.editDrug = editDrug;
	window.deleteDrug = deleteDrug;

	// --- Initialization ---
	console.log('Initializing pharmacists dashboard...');
	console.log('drugTableBody:', drugTableBody);
	console.log('drugForm:', drugForm);
	
	// Only initialize if we're on a page with the required elements
	if (drugTableBody && drugForm) {
		console.log('Required elements found, initializing...');
		// Initialize the page
		fetchCategories();
		fetchDrugs();
		setupEventListeners();
		setupSearchAndFilters();
		setupKeyboardShortcuts();
		setupTooltips();
		addExportFunctionality();
	} else {
		console.log('Required elements not found. This might be a different page.');
	}

=======
	// Initial load
	fetchCategories();
	fetchDrugs();
	showTab('distribute'); // Start with distribution tab as priority
>>>>>>> 752144dc532d595eaa7654e431baaff97e8bb2aa
});
