document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    let isEmailValid = false;
    let isPasswordValid = false;

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + 'Error');

        if (input && errorDiv) {
            input.classList.add('error');
            input.classList.remove('valid');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
        }
    }

    function showSuccess(inputId) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + 'Error');

        if (input && errorDiv) {
            input.classList.remove('error');
            input.classList.add('valid');
            errorDiv.classList.remove('show');
        }
    }

    function updateSubmitButton() {
        if (isEmailValid && isPasswordValid) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    emailInput.addEventListener('input', function() {
        const email = this.value.trim();

        if (email === '') {
            showError('email', 'Email wajib diisi');
            isEmailValid = false;
        } else if (!validateEmail(email)) {
            showError('email', 'Format email tidak valid');
            isEmailValid = false;
        } else {
            showSuccess('email');
            isEmailValid = true;
        }

        updateSubmitButton();
    });

    passwordInput.addEventListener('input', function() {
        const password = this.value;

        if (password === '') {
            showError('password', 'Kata sandi wajib diisi');
            isPasswordValid = false;
        } else {
            showSuccess('password');
            isPasswordValid = true;
        }

        updateSubmitButton();
    });

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        if (type === 'text') {
            eyeIcon.innerHTML = `
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
            `;
        } else {
            eyeIcon.innerHTML = `
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            `;
        }
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
        
        // Reset validation state
        isEmailValid = false;
        isPasswordValid = false;
    }

    form.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        let hasErrors = false;

        if (!email || !validateEmail(email)) {
            showError('email', 'Format email tidak valid');
            hasErrors = true;
        }

        if (!password) {
            showError('password', 'Kata sandi wajib diisi');
            hasErrors = true;
        }

        if (hasErrors) {
            e.preventDefault();
            return;
        }

        // Clear validation errors before successful submission
        clearAllValidationErrors();

        submitBtn.disabled = true;
        submitText.style.display = 'none';
        loadingSpinner.style.display = 'block';
    });

    if (emailInput.value) {
        emailInput.dispatchEvent(new Event('input'));
    }
});

