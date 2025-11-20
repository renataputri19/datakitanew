document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
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

    let validationState = {
        email: false,
        userType: false,
        institutionType: true,
        institutionName: true,
        institutionAddress: true,
        institutionPhone: true,
        password: false,
        passwordConfirm: false
    };

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

    function updateSubmitButton() {
        const allValid = Object.values(validationState).every(state => state === true);
        submitBtn.disabled = !allValid;
    }

    function validateEmail() {
        const email = emailInput.value.trim();

        if (email === '') {
            showError('email', 'Alamat email wajib diisi');
            validationState.email = false;
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Silakan masukkan alamat email yang valid');
            validationState.email = false;
            return false;
        }

        checkEmailUniqueness(email);
        return true;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
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
                validationState.email = true;
            } else {
                showError('email', data.message);
                validationState.email = false;
            }
            updateSubmitButton();
        })
        .catch(error => {
            console.error('Error checking email:', error);
            showSuccess('email', 'Format email valid');
            validationState.email = true;
            updateSubmitButton();
        });
    }

    const debouncedEmailCheck = debounce(checkEmailUniqueness, 500);

    function validateUserType() {
        const userType = userTypeSelect.value;

        if (userType === '') {
            showError('userType', 'Silakan pilih jenis pengguna');
            validationState.userType = false;
            return false;
        }

        showSuccess('userType');
        validationState.userType = true;
        return true;
    }

    function validateInstitutionType() {
        if (validationState.institutionType === true && !institutionFields.classList.contains('show')) {
            return true;
        }

        const institutionType = institutionTypeSelect.value;

        if (institutionType === '') {
            showError('institutionType', 'Silakan pilih jenis institusi');
            validationState.institutionType = false;
            return false;
        }

        clearValidation('institutionType');
        validationState.institutionType = true;
        return true;
    }

    function validateInstitutionName() {
        if (validationState.institutionName === true && !institutionFields.classList.contains('show')) {
            return true;
        }

        const institutionName = institutionNameInput.value.trim();

        if (institutionName === '') {
            showError('institutionName', 'Silakan masukkan nama institusi Anda');
            validationState.institutionName = false;
            return false;
        }

        if (institutionName.length < 2) {
            showError('institutionName', 'Nama institusi harus minimal 2 karakter');
            validationState.institutionName = false;
            return false;
        }

        clearValidation('institutionName');
        validationState.institutionName = true;
        return true;
    }

    function validateInstitutionAddress() {
        // Institution address is now optional for all user types
        validationState.institutionAddress = true;
        
        const institutionAddress = institutionAddressInput.value.trim();
        
        // Only validate if user has entered something
        if (institutionAddress !== '' && institutionAddress.length < 10) {
            showError('institutionAddress', 'Alamat institusi harus minimal 10 karakter');
            validationState.institutionAddress = false;
            return false;
        }

        clearValidation('institutionAddress');
        validationState.institutionAddress = true;
        return true;
    }

    function validateInstitutionPhone() {
        // Institution phone is now optional for all user types
        validationState.institutionPhone = true;
        
        const institutionPhone = institutionPhoneInput.value.trim();
        
        // Only validate if user has entered something
        if (institutionPhone !== '') {
            // Basic phone validation (numbers, spaces, hyphens, parentheses, plus sign)
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,20}$/;
            if (!phoneRegex.test(institutionPhone)) {
                showError('institutionPhone', 'Format nomor telepon tidak valid');
                validationState.institutionPhone = false;
                return false;
            }
        }

        clearValidation('institutionPhone');
        validationState.institutionPhone = true;
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;

        if (password === '') {
            showError('password', 'Password wajib diisi');
            strengthFill.className = 'strength-fill';
            strengthText.textContent = '';
            validationState.password = false;
            return false;
        }

        let strength = 0;
        let feedback = [];

        if (password.length >= 8) strength += 1;
        else feedback.push('minimal 8 karakter');

        if (/[A-Z]/.test(password)) strength += 1;
        else feedback.push('huruf besar');

        if (/[a-z]/.test(password)) strength += 1;
        else feedback.push('huruf kecil');

        if (/\d/.test(password)) strength += 1;
        else feedback.push('angka');

        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
        else feedback.push('karakter khusus');

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

        if (strength < 3) {
            showError('password', `Password memerlukan: ${feedback.join(', ')}`);
            validationState.password = false;
            return false;
        }

        clearValidation('password');
        validationState.password = true;
        return true;
    }

    function validatePasswordConfirm() {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;

        if (passwordConfirm === '') {
            showError('password_confirmation', 'Silakan konfirmasi password Anda');
            validationState.passwordConfirm = false;
            return false;
        }

        if (password !== passwordConfirm) {
            showError('password_confirmation', 'Password tidak cocok');
            validationState.passwordConfirm = false;
            return false;
        }

        showSuccess('password_confirmation', 'Password cocok');
        validationState.passwordConfirm = true;
        return true;
    }

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        if (type === 'text') {
            eyeIcon.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>';
        } else {
            eyeIcon.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
        }
    });

    togglePasswordConfirm.addEventListener('click', function() {
        const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmInput.setAttribute('type', type);

        if (type === 'text') {
            eyeIconConfirm.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>';
        } else {
            eyeIconConfirm.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
        }
    });

    userTypeSelect.addEventListener('change', function() {
        const userType = this.value;

        if (userType === 'instansi' || userType === 'akademisi') {
            institutionFields.classList.add('show');
            validationState.institutionType = false;
            validationState.institutionName = false;
            
            // Show address and phone fields only for instansi
            if (userType === 'instansi') {
                institutionAddressGroup.style.display = 'block';
                institutionPhoneGroup.style.display = 'block';
                // Keep address and phone as optional (true) for instansi users
                validationState.institutionAddress = true;
                validationState.institutionPhone = true;
            } else {
                institutionAddressGroup.style.display = 'none';
                institutionPhoneGroup.style.display = 'none';
                validationState.institutionAddress = true;
                validationState.institutionPhone = true;
                institutionAddressInput.value = '';
                institutionPhoneInput.value = '';
            }
        } else {
            institutionFields.classList.remove('show');
            institutionAddressGroup.style.display = 'none';
            institutionPhoneGroup.style.display = 'none';
            validationState.institutionType = true;
            validationState.institutionName = true;
            validationState.institutionAddress = true;
            validationState.institutionPhone = true;
            institutionTypeSelect.value = '';
            institutionNameInput.value = '';
            institutionAddressInput.value = '';
            institutionPhoneInput.value = '';
        }

        validateUserType();
        updateSubmitButton();
    });

    emailInput.addEventListener('input', function() {
        const email = emailInput.value.trim();

        if (email === '') {
            showError('email', 'Alamat email wajib diisi');
            validationState.email = false;
            updateSubmitButton();
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Silakan masukkan alamat email yang valid');
            validationState.email = false;
            updateSubmitButton();
            return;
        }

        debouncedEmailCheck(email);
    });

    emailInput.addEventListener('blur', validateEmail);

    userTypeSelect.addEventListener('change', function() {
        validateUserType();
        updateSubmitButton();
    });

    institutionTypeSelect.addEventListener('change', function() {
        validateInstitutionType();
        updateSubmitButton();
    });

    institutionNameInput.addEventListener('input', function() {
        validateInstitutionName();
        updateSubmitButton();
    });

    institutionNameInput.addEventListener('blur', function() {
        validateInstitutionName();
        updateSubmitButton();
    });

    institutionAddressInput.addEventListener('input', function() {
        validateInstitutionAddress();
        updateSubmitButton();
    });

    institutionAddressInput.addEventListener('blur', function() {
        validateInstitutionAddress();
        updateSubmitButton();
    });

    institutionPhoneInput.addEventListener('input', function() {
        validateInstitutionPhone();
        updateSubmitButton();
    });

    institutionPhoneInput.addEventListener('blur', function() {
        validateInstitutionPhone();
        updateSubmitButton();
    });

    passwordInput.addEventListener('input', function() {
        validatePassword();
        validatePasswordConfirm();
        updateSubmitButton();
    });

    passwordInput.addEventListener('blur', function() {
        validatePassword();
        updateSubmitButton();
    });

    passwordConfirmInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;

        if (passwordConfirm === '') {
            clearValidation('password_confirmation');
            validationState.passwordConfirm = false;
            updateSubmitButton();
            return;
        }

        if (password !== passwordConfirm) {
            showError('password_confirmation', 'Password tidak cocok');
            validationState.passwordConfirm = false;
        } else {
            showSuccess('password_confirmation', 'Password cocok');
            validationState.passwordConfirm = true;
        }
        updateSubmitButton();
    });

    passwordConfirmInput.addEventListener('blur', function() {
        validatePasswordConfirm();
        updateSubmitButton();
    });

    function clearAllValidationErrors() {
        // Clear all error messages and reset form validation states
        const errorDivs = document.querySelectorAll('.error-message.show');
        errorDivs.forEach(div => div.classList.remove('show'));
        
        // Remove error and valid classes from all inputs
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.classList.remove('error', 'valid');
        });
        
        // Reset validation state object to initial state
        validationState = {
            email: false,
            userType: false,
            institutionType: false,
            institutionName: false,
            password: false,
            passwordConfirm: false
        };
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const emailValid = validateEmail();
        const userTypeValid = validateUserType();
        const institutionTypeValid = validateInstitutionType();
        const institutionNameValid = validateInstitutionName();
        const institutionAddressValid = validateInstitutionAddress();
        const institutionPhoneValid = validateInstitutionPhone();
        const passwordValid = validatePassword();
        const passwordConfirmValid = validatePasswordConfirm();

        const isValid = emailValid && userTypeValid && institutionTypeValid && institutionNameValid && institutionAddressValid && institutionPhoneValid && passwordValid && passwordConfirmValid;

        if (!isValid) {
            const firstErrorField = form.querySelector('.form-input.error, .form-select.error');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
            return;
        }

        // Clear validation errors before successful submission
        clearAllValidationErrors();

        submitBtn.disabled = true;
        submitText.style.display = 'none';
        loadingSpinner.style.display = 'block';

        this.submit();
    });

    if (emailInput.value) {
        emailInput.dispatchEvent(new Event('input'));
    }
    if (userTypeSelect.value) {
        userTypeSelect.dispatchEvent(new Event('change'));
    }
    if (passwordInput.value) {
        passwordInput.dispatchEvent(new Event('input'));
    }
});

