document.addEventListener('DOMContentLoaded', () => {
	// --- Elements ---
	const tabDistributeBtn = document.getElementById('tabDistributeBtn');
	const tabReportsBtn = document.getElementById('tabReportsBtn');
	const tabManagementBtn = document.getElementById('tabManagementBtn');
	const tabDistribute = document.getElementById('tabDistribute');
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

	// Distribute elements
	const distributionForm = document.getElementById('distributionForm');
	const distDrugSelect = document.getElementById('distDrugSelect');
	const distQuantityInput = document.getElementById('distQuantity');
	const distRecipientInput = document.getElementById('distRecipient');
	const distTableBody = document.getElementById('distTableBody');
	const distPagination = document.getElementById('distPagination');

	// --- State ---
	let editingDrugId = null;
	let allCategories = [];
	let currentPage = 1;
	const drugsPerPage = 10;
	let distPage = 1;
	const distPerPage = 10;

	// --- API URLs ---
	const CATEGORY_API_URL = '../api/drug_api.php?resource=categories';
	const DRUG_API_URL = '../api/drug_api.php?resource=drugs';
	const DIST_API_URL = '../api/drug_api.php?resource=distributions';

	// --- Helpers ---
	function displayMessage(message, type) {
		if (window.Swal) {
			Swal.fire({ icon: type, title: (type === 'success' ? 'Success!' : 'Error!'), text: message, confirmButtonText: 'OK' });
		} else {
			console.log(type + ': ' + message);
		}
	}

	async function showConfirm(message) {
		if (window.Swal) {
			const result = await Swal.fire({ title: 'Are you sure?', text: message, icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, proceed!' });
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
			const response = await fetch(CATEGORY_API_URL, { method: 'GET' });
			const result = await response.json();
			if (result.success) {
				allCategories = result.data;
				populateDrugCategoryDropDown(allCategories);
				// also populate distribution drug dropdown with live drugs
				await populateDistributionDrugDropdown();
			} else {
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.error('Error fetching categories:', error);
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
			}
		});
		
		// Show selected tab and activate button
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

	// Capitalize drug name words on blur
	drugNameInput.addEventListener('blur', () => {
		const raw = drugNameInput.value.trim();
		const capitalized = raw.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
		drugNameInput.value = capitalized;
	});

	drugForm.addEventListener('submit', async (event) => {
		event.preventDefault();

		let rawName = drugNameInput.value.trim();
		let capitalizeName = rawName.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
		drugNameInput.value = capitalizeName;

		const drugData = {
			name: capitalizeName,
			category_id: parseInt(drugCategorySelect.value),
			quantity: parseInt(drugQuantityInput.value),
			expiry_date: drugExpiryDateInput.value
		};

		let method = 'POST';
		let url = DRUG_API_URL;
		if (editingDrugId) {
			method = 'PUT';
			drugData.id = editingDrugId;
		}

		try {
			const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(drugData) });
			const result = await response.json();
			if (result.success) {
				displayMessage(result.message, 'success');
				resetDrugForm();
				fetchDrugs();
			} else {
				displayMessage(result.message, 'error');
			}
		} catch (error) {
			console.log(`Error ${method}ing drug: `, error);
			displayMessage(`Failed to ${method} drug due to a network error.`, 'error');
		}
	});

	drugTableBody.addEventListener('click', async (event) => {
		if (event.target.classList.contains('edit-drug-btn')) {
			event.preventDefault();
			const row = event.target.closest('tr');
			const drugData = JSON.parse(row.dataset.drug);
			populateDrugFormForEdit(drugData);
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
					} else {
						displayMessage(result.message, 'error');
					}
				} catch (error) {
					console.log('Error deleting drug: ', error);
					displayMessage('Failed to delete drug due to a network error.', 'error');
				}
			}
		}
	});

	// Initial load
	fetchCategories();
	fetchDrugs();
	showTab('distribute'); // Start with distribution tab as priority
});
