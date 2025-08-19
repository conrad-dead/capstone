document.addEventListener('DOMContentLoaded', () => {
	const distDrugSelect = document.getElementById('distDrugSelect');
	const distQuantityInput = document.getElementById('distQuantity');
	const distRecipientInput = document.getElementById('distRecipient');
	const distributionForm = document.getElementById('distributionForm');
	const distTableBody = document.getElementById('distTableBody');
	const distPagination = document.getElementById('distPagination');
	const kpiDistToday = document.getElementById('kpiDistToday');
	const kpiDistMonth = document.getElementById('kpiDistMonth');
	const kpiLowStock = document.getElementById('kpiLowStock');

	let distPage = 1;
	const distPerPage = 10;

	const DRUGS_API = '../api/drug_api.php?resource=drugs';
	const DIST_API = '../api/drug_api.php?resource=distributions';

	function displayMessage(message, type) {
		if (window.Swal) {
			Swal.fire({ icon: type, title: (type === 'success' ? 'Success!' : 'Error!'), text: message, confirmButtonText: 'OK' });
		} else {
			console.log(type + ': ' + message);
		}
	}

	async function populateDistDrugs() {
		try {
			const response = await fetch(`${DRUGS_API}&page=1&limit=1000`);
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
			console.log('Error loading drugs', e);
		}
	}

	async function fetchDistList() {
		distTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>';
		try {
			const response = await fetch(`${DIST_API}&page=${distPage}&limit=${distPerPage}`);
			const result = await response.json();
			if (result.success) {
				renderDistTable(result.data);
				renderDistPagination(result.total);
			} else {
				distTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
			}
		} catch (e) {
			distTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Network error</td></tr>';
		}
	}

	function renderDistTable(rows) {
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
		prev.addEventListener('click', () => { if (distPage > 1) { distPage--; fetchDistList(); } });
		distPagination.appendChild(prev);
		for (let i=1; i<= totalPages; i++) {
			const b = document.createElement('button');
			b.textContent = i;
			if (i === distPage) b.classList.add('active-page');
			b.addEventListener('click', () => { distPage = i; fetchDistList(); });
			distPagination.appendChild(b);
		}
		const next = document.createElement('button');
		next.textContent = 'Next';
		next.disabled = distPage === totalPages;
		next.addEventListener('click', () => { if (distPage < totalPages) { distPage++; fetchDistList(); } });
		distPagination.appendChild(next);
	}

	async function fetchKpis() {
		try {
			const res = await fetch(`${DIST_API}&stats=summary`);
			const result = await res.json();
			if (result.success && result.data) {
				if (kpiDistToday) kpiDistToday.textContent = result.data.distributions_today;
				if (kpiDistMonth) kpiDistMonth.textContent = result.data.distributed_month_quantity;
				if (kpiLowStock) kpiLowStock.textContent = result.data.low_stock_count;
			}
		} catch (e) { /* ignore */ }
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
				const response = await fetch(DIST_API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ drug_id, quantity_given, recipient }) });
				const result = await response.json();
				if (result.success) {
					displayMessage(result.message, 'success');
					distQuantityInput.value = '';
					distRecipientInput.value = '';
					populateDistDrugs();
					fetchDistList();
					fetchKpis();
				} else {
					displayMessage(result.message, 'error');
				}
			} catch (e) {
				displayMessage('Failed to record distribution.', 'error');
			}
		});
	}

	populateDistDrugs();
	fetchDistList();
	fetchKpis();
});


