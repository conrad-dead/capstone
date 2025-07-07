document.addEventListener('DOMContentLoaded', () =>{

    // ---Tab Elements ---//
    const drugsTabBtn = document.getElementById('drugsTabBtn');
    const categoriesTabBtn = document.getElementById('categoriesTabBtn');
    const drugsTabContent = document.getElementById('drugsTabContent');
    const categoriesTabContent = document.getElementById('categoriesTabContent');

    // Category Elements
    const categoryForm = document.getElementById('categoryForm');
    const categoryFormTitle = document.getElementById('categoryFormTitle');
    const categoryIdInput = document.getElementById('categoryId');
    const categoryNameInput = document.getElementById('categoryName');
    const categorySubmitButton = document.getElementById('categorySubmitButton');
    const categoryTableBody = document.getElementById('categoryTableBody');
    const cancelCategoryEditButton = document.getElementById('cancelCategoryEditButton');

    //drug elements
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

    let editingCategoryId = null;
    let editingDrugId = null;
    let allCategories = []; // para sa dropdowns

    const CATEGORY_API_URL = '../api/drug_api.php?resource=categories';
    const DRUG_API_URL = '../api/drug_api.php?resource=drugs';

    //switching to category or drug
    function showTab(tabName) {
        //disable lahat ng drugs table
        drugsTabBtn.classList.remove('active');
        categoriesTabBtn.classList.remove('active');
        drugsTabContent.classList.remove('active');
        categoriesTabContent.classList.remove('active');

        // active selected tab and show yung content
        if (tabName === 'drugs') {
            drugsTabBtn.classList.add('active');
            drugsTabContent.classList.add('active');
            fetchDrugs();
        } else if (tabName === 'categories') {
            categoriesTabBtn.classList.add('active');
            categoriesTabContent.classList.add('active');
            fetchCategories();
        }
    }

    drugsTabBtn.addEventListener('click', () => showTab('drugs'));
    categoriesTabBtn.addEventListener('click', () => showTab('categories'));

    //sweet alert function 
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

    function resetCategoryForm() {
        categoryForm.reset();
        categoryFormTitle.textContent = 'Manage Categories';
        categorySubmitButton.textContent = 'Add Category';
        cancelCategoryEditButton.classList.add('hidden');
        editingCategoryId = null;
    }

    function populateCategoryFormForEdit(category) {
        categoryFormTitle.textContent = "Edit Category";
        categorySubmitButton.textContent = "Update Category";
        cancelCategoryEditButton.classList.remove('hidden');
        categoryIdInput.value = category.id;
        categoryNameInput.value = category.name;
        editingCategoryId = category.id;
    }

    cancelCategoryEditButton.addEventListener('click', resetCategoryForm);

    async function fetchCategories() {
        categoryTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading categories...</td></tr>`;
        try {
            const response = await fetch(CATEGORY_API_URL, { method: 'GET' });
            const result = await response.json();
            console.log('Categories fetched:', result);
            if (result.success) {
                allCategories = result.data; // store for dropdown
                renderCategoryTable(allCategories);
                populateDrugCategoryDropDown(allCategories);
            } else {
                categoryTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                displayMessage(result.message, 'error');
            }
        }catch (error) {
            categoryTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
            console.error('Error fetching categories:', error);
            displayMessage('Failed to connect to the server. Please try again later.', 'error');
        }
    }

    function renderCategoryTable(categories) {
        categoryTableBody.innerHTML = '';
        if (categories.length == 0) {
            categoryTableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No categories found.</td></tr>`;
            return;
        }
        categories.forEach(category => {
            const row = document.createElement('tr');
            row.dataset.category = JSON.stringify(category);
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${category.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${category.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${category.created_at || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-4 edit-category-btn" data-category-id="${category.id}">Edit</a>
                    <a href="#" class="text-red-600 hover:text-red-900 delete-category-btn" data-category-id="${category.id}">Delete</a>
                </td>
            `;
            categoryTableBody.appendChild(row);
        });
    }


    //---------------------category Form submission----------------------------
    categoryForm.addEventListener ('submit', async (event) => {
        event.preventDefault();

        document.querySelectorAll('span[id$="_error"]').forEach(span => span.textContent = '');


        const name = categoryNameInput.value.trim();


        let hasError = false;

        //form validation
        if (!name) {
             document.getElementById('name_error').textContent = "Drug name is required";
            hasError = true;
        }

        if (hasError) {
            return;
        }

        const categoryData = {
            name: name
        };

        let method = 'POST';
        let url = CATEGORY_API_URL;

        if (editingCategoryId) {
            method = 'PUT';
            categoryData.id = editingCategoryId;
        } 


        try {
            const response = await fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify(categoryData)
            });
            const result = await response.json();
            if (result.success) {
                displayMessage(result.message, 'success');
                resetCategoryForm();
                fetchCategories();
            }else {
                displayMessage(result.message, 'error');
            }
        } catch (error){
            console.error(`Error ${method}ing category:`, error);
            displayMessage(`Failed to ${method} category due to a network error.`, 'error');
        }
    });

    categoryTableBody.addEventListener('click', async (event) => {
        if (event.target.classList.contains('edit-category-btn')) {
            event.preventDefault();
            const row = event.target.closest('tr');
            const categoryData = JSON.parse(row.dataset.category);
            populateCategoryFormForEdit(categoryData);
        } else if (event.target.classList.contains('delete-category-btn')) {
            event.preventDefault();
            const categoryIdToDelete = event.target.dataset.categoryId;
            const confirmed = await showConfirm(`Are you sure you want to delete category ID: ${categoryIdToDelete}?`);
            if(confirmed) {
                try {
                    const response = await fetch(CATEGORY_API_URL, {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: categoryIdToDelete})
                    });
                    const result = await response.json();
                    if (result.success) {
                        displayMessage(result.message, 'success');
                        fetchCategories();
                    } else {
                        displayMessage(result.message, 'error');
                    }
                } catch (error) {
                    console.log('Error deleting category:' , error);
                    displayMessage('Failed to delete category due to a network error.' , 'error');
                }
            }
        }
    });


    // -----------------------------------------drug management---------------------------------------------------------

    function resetDrugForm() {
        drugForm.reset();
        drugFormTitle.textContent = 'Manage Drugs';
        drugSubmitButton.textContent = 'Add Drug';
        cancelDrugEditButton.classList.add('hidden');
        editingDrugId = null;
    }

    function populateDrugFormForEdit(drug) {
        drugFormTitle.textContent = 'Edit Drug';
        drugSubmitButton.textContent= 'Update Drug';
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
        })
    }

    async function fetchDrugs() {
        drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading drugs...</td></tr>`;
        try {
            const response = await fetch(DRUG_API_URL, { method: 'GET' });
            const result = await response.json();
            if (result.success) {
                renderDrugTable(result.data);
            } else {
                drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${result.message}</td></tr>`;
                displayMessage(result.message, 'error');
            }
        } catch (error) {
            drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Network error or API not available.</td></tr>`;
            console.error('Error fetching drugs:', error);
            displayMessage('Failed to connect to the server. Please try again later.', 'error');
        }
    }

    function renderDrugTable(drugs) {
        drugTableBody.innerHTML = "";
        if(drugs.length === 0) {
            drugTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No drugs found. Add some above!</td></tr>`;
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


    // -------------------------Drug Form submission---------------------------------------------

    drugForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const drugData = {
            name: drugNameInput.value.trim(),
            category_id: parseInt(drugCategorySelect.value),
            quantity: parseInt(drugQuantityInput.value),
            expiry_date: drugExpiryDateInput.value
        };

        let method = 'POST';
        let url = DRUG_API_URL;

        if(editingDrugId) {
            method = 'PUT';
            drugData.id = editingDrugId;
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers:{
                    'Content-Type': 'application/json'
                },
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
        } catch {
            console.log(`Error ${method}ing drug: `, error);
            displayMessage(`Failed to ${method} drug due to a network error: `, `error`);
        }
    });


    drugTableBody.addEventListener('click', async (event) => {
        if(event.target.classList.contains('edit-drug-btn')) {
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
                    const response = await fetch(DRUG_API_URL, {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: drugIdToDelete})
                    });
                    const result = await response.json();

                    if(result.success) {
                        displayMessage(result.message, 'success');
                        fetchDrugs();
                    } else {
                        displayMessage(result.message, 'error');
                    }
                } catch (error){
                    console.log('Error deleting drug: ' , error);
                    displayMessage('Failed to delete drug due to a network error.' , error);
                }
            }
        }
    });

    showTab('drugs');
    
})