(function() {
    'use strict';

    function initializeSettings() {
        console.log('Settings page JavaScript initialized');

        // Password toggle functionality
        document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });
    });

    // User type conditional fields
    const userTypeSelect = document.getElementById('user_type');
    const institutionTypeGroup = document.getElementById('institution-type-group');
    const institutionNameGroup = document.getElementById('institution-name-group');
    const institutionAddressGroup = document.getElementById('institution-address-group');
    const institutionPhoneGroup = document.getElementById('institution-phone-group');
    const institutionTypeSelect = document.getElementById('institution_type');
    const institutionNameInput = document.getElementById('institution_name');
    const institutionAddressInput = document.getElementById('institution_address');
    const institutionPhoneInput = document.getElementById('institution_phone');

    function toggleInstitutionFields() {
        const userType = userTypeSelect.value;

        if (userType === 'instansi' || userType === 'akademisi') {
            institutionTypeGroup.classList.add('show');
            institutionNameGroup.classList.add('show');
            institutionTypeSelect.required = true;
            institutionNameInput.required = true;

            // Show address and phone fields only for instansi (but keep them optional)
            if (userType === 'instansi') {
                institutionAddressGroup.classList.add('show');
                institutionPhoneGroup.classList.add('show');
                institutionAddressInput.required = false;
                institutionPhoneInput.required = false;
            } else {
                institutionAddressGroup.classList.remove('show');
                institutionPhoneGroup.classList.remove('show');
                institutionAddressInput.required = false;
                institutionPhoneInput.required = false;
                institutionAddressInput.value = '';
                institutionPhoneInput.value = '';
            }
        } else {
            institutionTypeGroup.classList.remove('show');
            institutionNameGroup.classList.remove('show');
            institutionAddressGroup.classList.remove('show');
            institutionPhoneGroup.classList.remove('show');
            institutionTypeSelect.required = false;
            institutionNameInput.required = false;
            institutionAddressInput.required = false;
            institutionPhoneInput.required = false;
            institutionTypeSelect.value = '';
            institutionNameInput.value = '';
            institutionAddressInput.value = '';
            institutionPhoneInput.value = '';
        }
    }

    // Initialize conditional fields on page load
    toggleInstitutionFields();

    userTypeSelect.addEventListener('change', toggleInstitutionFields);

    // Form validation
    const profileForm = document.getElementById('profile-form');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');

    function validateName() {
        const name = nameInput.value.trim();
        const nameError = document.getElementById('name-error');
        
        if (name.length < 2) {
            showError(nameInput, nameError, 'Nama harus minimal 2 karakter');
            return false;
        }
        
        if (!/^[a-zA-Z\s\-\.']+$/.test(name)) {
            showError(nameInput, nameError, 'Nama hanya boleh mengandung huruf, spasi, tanda hubung, titik, dan apostrof');
            return false;
        }
        
        hideError(nameInput, nameError);
        return true;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailError = document.getElementById('email-error');
        
        if (!email) {
            showError(emailInput, emailError, 'Email wajib diisi');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError(emailInput, emailError, 'Format email tidak valid');
            return false;
        }
        
        hideError(emailInput, emailError);
        return true;
    }

    // Email uniqueness check (client-side UX)
    const currentEmail = document.querySelector('meta[name="user-email"]')?.getAttribute('content') || '';
    let emailAvailable = true;
    
    async function checkEmailUniqueness() {
        const email = emailInput.value.trim();
        const emailError = document.getElementById('email-error');
        const emailSuccess = document.getElementById('email-success');
        
        // If email unchanged, treat as available
        if (email === currentEmail) {
            emailAvailable = true;
            emailSuccess.querySelector('span').textContent = '';
            emailSuccess.style.display = 'none';
            hideError(emailInput, emailError);
            return true;
        }
        
        try {
            const res = await fetch('/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            emailAvailable = !!data.available;
            if (emailAvailable) {
                emailSuccess.querySelector('span').textContent = data.message || 'Email tersedia';
                emailSuccess.style.display = 'flex';
                hideError(emailInput, emailError);
            } else {
                emailSuccess.style.display = 'none';
                showError(emailInput, emailError, data.message || 'Email sudah digunakan oleh pengguna lain');
            }
        } catch (err) {
            // On error, do not block submission; fallback to server-side
            emailAvailable = true;
            emailSuccess.style.display = 'none';
        }
        return emailAvailable;
    }

    // Password validation (only for password form)
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    function validatePassword() {
        if (!passwordInput) return true; // Not on password form

        const password = passwordInput.value;
        const passwordError = document.getElementById('password-error');

        if (!password) {
            showError(passwordInput, passwordError, 'Password baru wajib diisi');
            return false;
        }

        if (password.length < 8) {
            showError(passwordInput, passwordError, 'Password harus minimal 8 karakter');
            return false;
        }

        if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
            showError(passwordInput, passwordError, 'Password harus mengandung huruf kecil, huruf besar, dan angka');
            return false;
        }

        hideError(passwordInput, passwordError);
        return true;
    }

    function validatePasswordConfirmation() {
        if (!passwordConfirmationInput) return true; // Not on password form

        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;
        const confirmationError = document.getElementById('password-confirmation-error');

        if (!passwordConfirmation) {
            showError(passwordConfirmationInput, confirmationError, 'Konfirmasi password wajib diisi');
            return false;
        }

        if (password !== passwordConfirmation) {
            showError(passwordConfirmationInput, confirmationError, 'Konfirmasi password tidak cocok');
            return false;
        }

        hideError(passwordConfirmationInput, confirmationError);
        return true;
    }

    function showError(input, errorElement, message) {
        input.classList.add('error');
        errorElement.querySelector('span').textContent = message;
        errorElement.style.display = 'flex';
    }

    function hideError(input, errorElement) {
        input.classList.remove('error');
        errorElement.style.display = 'none';
    }

    // Institution field validations
    function validateInstitutionType() {
        const userType = userTypeSelect.value;
        const errorEl = document.getElementById('institution_type-error');
        if (userType === 'instansi' || userType === 'akademisi') {
            if (!institutionTypeSelect.value) {
                showError(institutionTypeSelect, errorEl, 'Jenis instansi/akademisi wajib dipilih');
                return false;
            }
            hideError(institutionTypeSelect, errorEl);
        } else {
            hideError(institutionTypeSelect, errorEl);
        }
        return true;
    }

    function validateInstitutionName() {
        const userType = userTypeSelect.value;
        const errorEl = document.getElementById('institution_name-error');
        if (userType === 'instansi' || userType === 'akademisi') {
            const name = (institutionNameInput.value || '').trim();
            if (name.length < 2) {
                showError(institutionNameInput, errorEl, 'Nama instansi/akademisi harus minimal 2 karakter');
                return false;
            }
            hideError(institutionNameInput, errorEl);
        } else {
            hideError(institutionNameInput, errorEl);
        }
        return true;
    }

    function validateInstitutionAddress() {
        const userType = userTypeSelect.value;
        const errorEl = document.getElementById('institution_address-error');
        if (userType === 'instansi') {
            const addr = (institutionAddressInput.value || '').trim();
            if (addr !== '' && addr.length < 10) {
                showError(institutionAddressInput, errorEl, 'Alamat institusi harus minimal 10 karakter');
                return false;
            }
            hideError(institutionAddressInput, errorEl);
        } else {
            hideError(institutionAddressInput, errorEl);
        }
        return true;
    }

    function validateInstitutionPhone() {
        const userType = userTypeSelect.value;
        const errorEl = document.getElementById('institution_phone-error');
        if (userType === 'instansi') {
            const phone = (institutionPhoneInput.value || '').trim();
            if (phone !== '') {
                const allowed = /^[\+]?[0-9\s\-\(\)]{8,20}$/;
                if (!allowed.test(phone) || phone.length < 10) {
                    showError(institutionPhoneInput, errorEl, 'Nomor telepon tidak valid atau terlalu pendek');
                    return false;
                }
            }
            hideError(institutionPhoneInput, errorEl);
        } else {
            hideError(institutionPhoneInput, errorEl);
        }
        return true;
    }

    // Event listeners for real-time validation
    if (nameInput) nameInput.addEventListener('blur', validateName);
    if (emailInput) {
        emailInput.addEventListener('blur', async () => {
            if (validateEmail()) {
                await checkEmailUniqueness();
            }
        });
    }
    if (institutionTypeSelect) institutionTypeSelect.addEventListener('change', validateInstitutionType);
    if (institutionNameInput) institutionNameInput.addEventListener('blur', validateInstitutionName);
    if (institutionAddressInput) institutionAddressInput.addEventListener('blur', validateInstitutionAddress);
    if (institutionPhoneInput) institutionPhoneInput.addEventListener('blur', validateInstitutionPhone);
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword();
            if (passwordConfirmationInput && passwordConfirmationInput.value) {
                validatePasswordConfirmation();
            }
        });
    }
    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', validatePasswordConfirmation);
    }

    // Form submission
    if (profileForm) {
        console.log('Profile form found, attaching submit handler');
        profileForm.addEventListener('submit', async function(e) {
            console.log('Profile form submit event triggered');
            e.preventDefault(); // Prevent default first, then allow if valid

            const isNameValid = validateName();
            const isEmailValid = validateEmail();
            const isTypeValid = validateInstitutionType();
            const isInstNameValid = validateInstitutionName();
            const isAddrValid = validateInstitutionAddress();
            const isPhoneValid = validateInstitutionPhone();

            if (isEmailValid) {
                await checkEmailUniqueness();
            }

            const allValid = isNameValid && isEmailValid && emailAvailable && isTypeValid && isInstNameValid && isAddrValid && isPhoneValid;
            console.log('Profile form validation results:', {
                isNameValid,
                isEmailValid,
                emailAvailable,
                isTypeValid,
                isInstNameValid,
                isAddrValid,
                isPhoneValid,
                allValid
            });

            if (allValid) {
                console.log('Profile form is valid, submitting...');
                // Disable submit button to prevent double submission
                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
                }
                profileForm.submit(); // Actually submit the form
            } else {
                console.log('Profile form validation failed');
            }
        });
    }

    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        console.log('Password form found, attaching submit handler');
        passwordForm.addEventListener('submit', function(e) {
            console.log('Password form submit event triggered');
            e.preventDefault(); // Prevent default first, then allow if valid

            const isPasswordValid = validatePassword();
            const isConfirmationValid = validatePasswordConfirmation();

            const allValid = isPasswordValid && isConfirmationValid;
            console.log('Password form validation results:', {
                isPasswordValid,
                isConfirmationValid,
                allValid
            });

            if (allValid) {
                console.log('Password form is valid, submitting...');
                // Disable submit button to prevent double submission
                const submitBtn = passwordForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengubah...';
                }
                passwordForm.submit(); // Actually submit the form
            } else {
                console.log('Password form validation failed');
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    console.log('DOM is loading, waiting for DOMContentLoaded event');
    document.addEventListener('DOMContentLoaded', initializeSettings);
} else {
    // DOM is already loaded
    console.log('DOM already loaded, initializing settings immediately');
    initializeSettings();
}

console.log('user-dashboard-settings.js loaded successfully');
})();

