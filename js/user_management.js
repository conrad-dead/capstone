document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const form = document.getElementById('createUserForm')
            const barangayField = document.getElementById('barangayField');
            const barangayInput = document.getElementById('barangay');
            const barangaySearchResults = document.getElementById('barangaySearchResults');

            const barangayData = [
                "Barcolan", "Buenavista", "Dammao", "District I", "District II", "District III",
                "Furao", "Guibang", "Lenzon", "Linglingay", "Mabini", "Pintor",
                "Rizal", "Songsong", "Union", "Upi"
            ];

            roleSelect.addEventListener('change', function () {
                if (roleSelect.value === 'bhw') {
                    barangayField.classList.remove('hidden');
                    barangayInput.setAttribute('required', 'required');
                } else {
                    barangayField.classList.add('hidden');
                    barangayInput.removeAttribute('required');
                    barangayInput.value = "";
                    barangaySearchResults.classList.add('hidden');
                    barangaySearchResults.innerHTML = '';
                }
            });

            barangayInput.addEventListener('input', function () {
                const searchTerm = barangayInput.value.toLowerCase();
                barangaySearchResults.innerHTML = '';

                if (searchTerm.length > 0) {
                    const filteredBarangays = barangayData.filter(function (barangay) {
                        return barangay.toLowerCase().includes(searchTerm);
                    });

                    if (filteredBarangays.length > 0) {
                        filteredBarangays.forEach(function (barangay) {
                            const item = document.createElement('div');
                            item.classList.add('autocomplete-item', 'cursor-pointer', 'px-4', 'py-2', 'hover:bg-blue-100');
                            item.textContent = barangay;
                            item.addEventListener('click', function () {
                                barangayInput.value = barangay;
                                barangaySearchResults.classList.add('hidden');
                            });
                            barangaySearchResults.appendChild(item);
                        });
                        barangaySearchResults.classList.remove('hidden');
                    } else {
                       const noMatch = document.createElement('div');
                        noMatch.textContent = 'No barangay found';
                        noMatch.classList.add('text-gray-500', 'px-4', 'py-2');
                        barangaySearchResults.appendChild(noMatch);
                        barangaySearchResults.classList.remove('hidden');
                    }
                } else {
                    barangaySearchResults.classList.add('hidden');
                }
            });

            barangayInput.addEventListener('blur', function () {
                setTimeout(function () {
                    barangaySearchResults.classList.add('hidden');
                }, 100);
            });

            barangayInput.addEventListener('focus', function () {
                const searchTerm = barangayInput.value.toLowerCase();
                const filteredBarangays = barangayData.filter(function (barangay) {
                    return barangay.toLowerCase().includes(searchTerm);
                });

                if (filteredBarangays.length > 0) {
                    barangaySearchResults.classList.remove('hidden');
                }
            });

            //handling form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                //collect data
                const formData = {
                    first_name: document.getElementById('first_name').value.trim(),
                    last_name: document.getElementById('last_name').value.trim(),
                    password: document.getElementById('password').value.trim(),
                    confirm_password: document.getElementById('confirm_password').value.trim(),
                    role: document.getElementById('role').value.trim(),
                    barangay: document.getElementById('barangay').value || ''.trim() 
                }

                //submit using the Fetch API or ajax (simulate success here)
                fetch('../api/create_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            title: 'User Created',
                            text: 'The user has been successfully created',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Something went wrong',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to create user.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });