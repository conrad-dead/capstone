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

                document.querySelectorAll('span[id$="_error"]').forEach(span => span.textContent = '');
                
                //collect input
                const first_name = document.getElementById('first_name').value.trim();
                const last_name = document.getElementById('last_name').value.trim();
                const password = document.getElementById('password').value.trim();
                const confirm_password = document.getElementById('confirm_password').value.trim();
                const role = document.getElementById('role').value.trim();
                const barangay = document.getElementById('barangay').value || ''.trim();

                // form validation
                let hasError = false;

                if(!first_name) {
                    document.getElementById('first_name_error').textContent = "First name is required";
                    hasError = true;
                }

                if(!last_name) {
                    document.getElementById('last_name_error').textContent = "Last name is required";
                    hasError = true;
                }

                if(!password) {
                    document.getElementById('password_error').textContent = "Password is required";
                    hasError = true;
                } else if (password.length < 6) {
                    document.getElementById('password_error').textContent = "Password must be at least 6 character long";
                    hasError = true;
                }

                if(!confirm_password) {
                    document.getElementById('confirm_password_error').textContent = "Please confirm your password";
                    hasError = true;
                } else if (password !== confirm_password) {
                    document.getElementById('confirm_password_error').textContent = "Password do not match";
                    hasError = true;
                }
                
                if (!role) {
                    document.getElementById('role_error').textContent = "Role is required";
                    hasError = true;
                }
                if (role === 'bhw' && !barangay) {
                    document.getElementById('barangay_error').textContent = 'Barangay is required for BHW.';
                    hasError = true;
                }

                //check kung may error
                if (hasError) {
                    return; // stop kana dyan ya
                }

                let final_barangay;

                if (role === "bhw") {
                    final_barangay = barangay;
                } else {
                    final_barangay = null;
                }

                const formData = {
                    first_name,
                    last_name,
                    password,
                    confirm_password,
                    role,
                    barangay: final_barangay
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