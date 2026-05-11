document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const userTypeSelect = document.getElementById('user_type');
    const institutionFields = document.getElementById('institutionFields');
    const institutionTypeSelect = document.getElementById('institution_type');
    const institutionNameInput = document.getElementById('institution_name');
    const institutionAddressGroup = document.getElementById('institutionAddressGroup');
    const institutionPhoneGroup = document.getElementById('institutionPhoneGroup');
    const institutionAddressInput = document.getElementById('institution_address');
    const institutionPhoneInput = document.getElementById('institution_phone');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    // Only track the async email check result — everything else is read live from the DOM
    let emailAvailable = false;

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + 'Error');
        const successDiv = document.getElementById(inputId + 'Success');
        if (input && errorDiv) {
            input.classList.add('error');
            input.classList.remove('valid');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            if (successDiv) successDiv.classList.remove('show');
        }
    }

    function showSuccess(inputId, message = '') {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + 'Error');
        const successDiv = document.getElementById(inputId + 'Success');
        if (input && errorDiv) {
            input.classList.remove('error');
            input.classList.add('valid');
            errorDiv.classList.remove('show');
            if (successDiv && message) {
                successDiv.textContent = message;
                successDiv.classList.add('show');
            }
        }
    }

    function clearValidation(inputId) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + 'Error');
        const successDiv = document.getElementById(inputId + 'Success');
        if (input && errorDiv) {
            input.classList.remove('error', 'valid');
            errorDiv.classList.remove('show');
            if (successDiv) successDiv.classList.remove('show');
        }
    }

    // Always computed fresh from current DOM values — never stale
    function updateSubmitButton() {
        const userType = userTypeSelect.value;
        const isInstitution = userType === 'instansi' || userType === 'akademisi';
        const showsAddrPhone = userType === 'instansi';

        // Address/phone are optional. Only the max length matters — matches backend.
        const addrLenOk = institutionAddressInput.value.length <= 500;
        const phoneLenOk = institutionPhoneInput.value.length <= 20;

        const checks = [
            nameInput.value.trim().length >= 2,
            emailAvailable,
            userType !== '',
            !isInstitution || institutionTypeSelect.value !== '',
            !isInstitution || institutionNameInput.value.trim().length >= 2,
            addrLenOk,
            phoneLenOk,
            passwordInput.value.length >= 8,
            passwordInput.value === passwordConfirmInput.value && passwordConfirmInput.value !== ''
        ];

        submitBtn.disabled = !checks.every(Boolean);
    }

    // --- Validation functions (provide UI feedback; don't control button state) ---

    function validateName() {
        const name = nameInput.value.trim();
        if (name === '') {
            showError('name', 'Nama lengkap wajib diisi');
            return false;
        }
        if (name.length < 2) {
            showError('name', 'Nama lengkap harus minimal 2 karakter');
            return false;
        }
        showSuccess('name');
        return true;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        if (email === '') {
            showError('email', 'Alamat email wajib diisi');
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Silakan masukkan alamat email yang valid');
            return false;
        }
        checkEmailUniqueness(email);
        return true;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => { clearTimeout(timeout); func(...args); };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function checkEmailUniqueness(email) {
        fetch('/check-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                showSuccess('email', data.message);
                emailAvailable = true;
            } else {
                showError('email', data.message);
                emailAvailable = false;
            }
            updateSubmitButton();
        })
        .catch(() => {
            // Network error — allow the form through; server will validate
            showSuccess('email', 'Format email valid');
            emailAvailable = true;
            updateSubmitButton();
        });
    }

    const debouncedEmailCheck = debounce(checkEmailUniqueness, 500);

    function validateUserType() {
        if (userTypeSelect.value === '') {
            showError('userType', 'Silakan pilih jenis pengguna');
            return false;
        }
        showSuccess('userType');
        return true;
    }

    function validateInstitutionType() {
        if (!institutionFields.classList.contains('show')) return true;
        if (institutionTypeSelect.value === '') {
            showError('institutionType', 'Silakan pilih jenis institusi');
            return false;
        }
        clearValidation('institutionType');
        return true;
    }

    function validateInstitutionName() {
        if (!institutionFields.classList.contains('show')) return true;
        const name = institutionNameInput.value.trim();
        if (name === '') {
            showError('institutionName', 'Silakan masukkan nama institusi Anda');
            return false;
        }
        if (name.length < 2) {
            showError('institutionName', 'Nama institusi harus minimal 2 karakter');
            return false;
        }
        clearValidation('institutionName');
        return true;
    }

    function validateInstitutionAddress() {
        // Optional field — only complain if it's longer than the backend allows.
        if (institutionAddressInput.value.length > 500) {
            showError('institutionAddress', 'Alamat tidak boleh lebih dari 500 karakter');
            return false;
        }
        clearValidation('institutionAddress');
        return true;
    }

    function validateInstitutionPhone() {
        // Optional field — only complain if it's longer than the backend allows.
        if (institutionPhoneInput.value.length > 20) {
            showError('institutionPhone', 'Nomor telepon tidak boleh lebih dari 20 karakter');
            return false;
        }
        clearValidation('institutionPhone');
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;

        if (password === '') {
            showError('password', 'Password wajib diisi');
            strengthFill.className = 'strength-fill';
            strengthText.textContent = '';
            return false;
        }

        if (password.length < 8) {
            showError('password', 'Password minimal 8 karakter');
            strengthFill.className = 'strength-fill strength-weak';
            strengthText.textContent = 'Password terlalu pendek';
            strengthText.className = 'text-sm text-red-600';
            return false;
        }

        let strength = 1;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

        strengthFill.className = 'strength-fill';
        if (strength <= 2) {
            strengthFill.classList.add('strength-weak');
            strengthText.textContent = 'Password lemah';
            strengthText.className = 'text-sm text-red-600';
        } else if (strength === 3) {
            strengthFill.classList.add('strength-fair');
            strengthText.textContent = 'Password cukup';
            strengthText.className = 'text-sm text-yellow-600';
        } else if (strength === 4) {
            strengthFill.classList.add('strength-good');
            strengthText.textContent = 'Password baik';
            strengthText.className = 'text-sm text-blue-600';
        } else {
            strengthFill.classList.add('strength-strong');
            strengthText.textContent = 'Password kuat';
            strengthText.className = 'text-sm text-green-600';
        }

        clearValidation('password');
        return true;
    }

    function validatePasswordConfirm() {
        const password = passwordInput.value;
        const confirm = passwordConfirmInput.value;
        if (confirm === '') {
            showError('password_confirmation', 'Silakan konfirmasi password Anda');
            return false;
        }
        if (password !== confirm) {
            showError('password_confirmation', 'Password tidak cocok');
            return false;
        }
        showSuccess('password_confirmation', 'Password cocok');
        return true;
    }

    // --- User type change: show/hide institution fields ---

    userTypeSelect.addEventListener('change', function() {
        const userType = this.value;

        if (userType === 'instansi' || userType === 'akademisi') {
            institutionFields.classList.add('show');
            if (userType === 'instansi') {
                institutionAddressGroup.style.display = 'block';
                institutionPhoneGroup.style.display = 'block';
            } else {
                institutionAddressGroup.style.display = 'none';
                institutionPhoneGroup.style.display = 'none';
                institutionAddressInput.value = '';
                institutionPhoneInput.value = '';
            }
        } else {
            institutionFields.classList.remove('show');
            institutionAddressGroup.style.display = 'none';
            institutionPhoneGroup.style.display = 'none';
            institutionTypeSelect.value = '';
            institutionNameInput.value = '';
            institutionAddressInput.value = '';
            institutionPhoneInput.value = '';
            clearValidation('institutionType');
            clearValidation('institutionName');
        }

        validateUserType();
        updateSubmitButton();
    });

    // --- Field event listeners ---

    nameInput.addEventListener('input', function() { validateName(); updateSubmitButton(); });
    nameInput.addEventListener('blur', function() { validateName(); updateSubmitButton(); });

    emailInput.addEventListener('input', function() {
        const email = emailInput.value.trim();
        if (email === '') {
            showError('email', 'Alamat email wajib diisi');
            emailAvailable = false;
            updateSubmitButton();
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Silakan masukkan alamat email yang valid');
            emailAvailable = false;
            updateSubmitButton();
            return;
        }
        debouncedEmailCheck(email);
    });
    emailInput.addEventListener('blur', validateEmail);

    institutionTypeSelect.addEventListener('change', function() {
        validateInstitutionType();
        updateSubmitButton();
    });

    institutionNameInput.addEventListener('input', function() { validateInstitutionName(); updateSubmitButton(); });
    institutionNameInput.addEventListener('blur', function() { validateInstitutionName(); updateSubmitButton(); });

    institutionAddressInput.addEventListener('input', function() { validateInstitutionAddress(); updateSubmitButton(); });
    institutionAddressInput.addEventListener('blur', function() { validateInstitutionAddress(); updateSubmitButton(); });

    institutionPhoneInput.addEventListener('input', function() { validateInstitutionPhone(); updateSubmitButton(); });
    institutionPhoneInput.addEventListener('blur', function() { validateInstitutionPhone(); updateSubmitButton(); });

    passwordInput.addEventListener('input', function() {
        validatePassword();
        // re-check confirm live so mismatch clears when passwords match again
        if (passwordConfirmInput.value !== '') validatePasswordConfirm();
        updateSubmitButton();
    });
    passwordInput.addEventListener('blur', function() { validatePassword(); updateSubmitButton(); });

    passwordConfirmInput.addEventListener('input', function() {
        if (passwordConfirmInput.value === '') {
            clearValidation('password_confirmation');
        } else {
            validatePasswordConfirm();
        }
        updateSubmitButton();
    });
    passwordConfirmInput.addEventListener('blur', function() { validatePasswordConfirm(); updateSubmitButton(); });

    // --- Password visibility toggles ---

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.innerHTML = type === 'text'
            ? '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>'
            : '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
    });

    togglePasswordConfirm.addEventListener('click', function() {
        const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmInput.setAttribute('type', type);
        eyeIconConfirm.innerHTML = type === 'text'
            ? '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>'
            : '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
    });

    // --- Submit ---

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Run all validators to surface any remaining errors
        const valid =
            validateName() &
            validateEmail() &
            validateUserType() &
            validateInstitutionType() &
            validateInstitutionName() &
            validateInstitutionAddress() &
            validateInstitutionPhone() &
            validatePassword() &
            validatePasswordConfirm();

        if (!valid) {
            const firstError = form.querySelector('.form-input.error, .form-select.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }

        submitBtn.disabled = true;
        submitText.style.display = 'none';
        loadingSpinner.style.display = 'block';
        this.submit();
    });

    // Bootstrap state for pre-filled values (e.g. old() after server error)
    if (nameInput.value) nameInput.dispatchEvent(new Event('input'));
    if (emailInput.value) emailInput.dispatchEvent(new Event('input'));
    if (userTypeSelect.value) userTypeSelect.dispatchEvent(new Event('change'));
    if (passwordInput.value) passwordInput.dispatchEvent(new Event('input'));
});
