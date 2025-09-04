/**
 * Authentication Pages JavaScript (Login & Registration)
 * Place this file at: public/assets/js/auth.js
 * Unified client-side validation and interactions for both pages
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Determine current page based on form presence
    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');
    const isLoginPage = !!loginForm;
    const isRegisterPage = !!registrationForm;
    
    // Add body class for page-specific styling
    if (isLoginPage) {
        document.body.classList.add('login-page');
    } else if (isRegisterPage) {
        document.body.classList.add('register-page');
    }
    
    // Login Form Handling
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Clear previous errors
            clearErrors();
            
            // Basic validation
            let hasErrors = false;
            
            // Username validation
            if (!username) {
                showError('idGroup', 'usernameError', 'IDを入力してください');
                hasErrors = true;
            } else if (username.length > 50) {
                showError('idGroup', 'usernameError', 'IDは50文字以内で入力してください');
                hasErrors = true;
            }
            
            // Password validation
            if (!password) {
                showError('passwordGroup', 'passwordError', 'パスワードを入力してください');
                hasErrors = true;
            } else if (password.length < 6) {
                showError('passwordGroup', 'passwordError', 'パスワードは6文字以上で入力してください');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            showLoading();
            
            // Form will submit normally to FuelPHP controller
            return true;
        });
    }
    
    // Registration Form Handling
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const passwordConfirm = document.getElementById('password_confirm').value.trim();
            
            // Clear previous errors
            clearErrors();
            
            // Basic validation
            let hasErrors = false;
            
            // Username validation
            if (!username) {
                showError('idGroup', 'usernameError', 'IDを入力してください');
                hasErrors = true;
            } else if (username.length < 3) {
                showError('idGroup', 'usernameError', 'IDは3文字以上で入力してください');
                hasErrors = true;
            } else if (username.length > 50) {
                showError('idGroup', 'usernameError', 'IDは50文字以内で入力してください');
                hasErrors = true;
            } else if (!/^[a-zA-Z0-9\-]+$/.test(username)) {
                showError('idGroup', 'usernameError', 'IDは英数字とハイフンのみ使用できます');
                hasErrors = true;
            }
            
            // Password validation
            if (!password) {
                showError('passwordGroup', 'passwordError', 'パスワードを入力してください');
                hasErrors = true;
            } else if (password.length < 8) {
                showError('passwordGroup', 'passwordError', 'パスワードは8文字以上で入力してください');
                hasErrors = true;
            } else if (password.length > 15) {
                showError('passwordGroup', 'passwordError', 'パスワードは15文字以内で入力してください');
                hasErrors = true;
            } else if (!/^[a-zA-Z0-9]+$/.test(password)) {
                showError('passwordGroup', 'passwordError', 'パスワードは英数字のみ使用できます');
                hasErrors = true;
            }
            
            // Password confirmation validation (registration only)
            if (!passwordConfirm) {
                showError('passwordConfirmGroup', 'passwordConfirmError', 'パスワード確認を入力してください');
                hasErrors = true;
            } else if (password !== passwordConfirm) {
                showError('passwordConfirmGroup', 'passwordConfirmError', 'パスワードが一致しません');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            showLoading();
            
            // Form will submit normally to FuelPHP controller
            return true;
        });
    }
    
    // Real-time validation setup
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('blur', function() {
            validateUsername();
        });
        
        usernameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                validateUsername();
            } else {
                clearFieldError('idGroup', 'usernameError');
            }
        });
    }
    
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            validatePassword();
        });
        
        passwordInput.addEventListener('input', function() {
            if (this.value.trim()) {
                validatePassword();
                if (isRegisterPage) {
                    updatePasswordStrength(this.value);
                }
            } else {
                clearFieldError('passwordGroup', 'passwordError');
                if (isRegisterPage) {
                    hidePasswordStrength();
                }
            }
            // Also re-validate password confirmation if on registration page
            if (isRegisterPage) {
                validatePasswordConfirm();
            }
        });
    }
    
    // Password confirmation field (registration only)
    const passwordConfirmInput = document.getElementById('password_confirm');
    if (passwordConfirmInput) {
        passwordConfirmInput.addEventListener('blur', function() {
            validatePasswordConfirm();
        });
        
        passwordConfirmInput.addEventListener('input', function() {
            if (this.value.trim()) {
                validatePasswordConfirm();
            } else {
                clearFieldError('passwordConfirmGroup', 'passwordConfirmError');
            }
        });
    }
    
    // Validation Functions
    function validateUsername() {
        const username = usernameInput.value.trim();
        
        if (!username) {
            showError('idGroup', 'usernameError', 'IDを入力してください');
            return false;
        } else if (isRegisterPage && username.length < 3) {
            showError('idGroup', 'usernameError', 'IDは3文字以上で入力してください');
            return false;
        } else if (username.length > 50) {
            showError('idGroup', 'usernameError', 'IDは50文字以内で入力してください');
            return false;
        } else if (isRegisterPage && !/^[a-zA-Z0-9\-]+$/.test(username)) {
            showError('idGroup', 'usernameError', 'IDは英数字とハイフンのみ使用できます');
            return false;
        } else {
            clearFieldError('idGroup', 'usernameError');
            showSuccess('idGroup');
            return true;
        }
    }
    
    function validatePassword() {
        const password = passwordInput.value.trim();
        const minLength = isRegisterPage ? 8 : 6;
        
        if (!password) {
            showError('passwordGroup', 'passwordError', 'パスワードを入力してください');
            return false;
        } else if (password.length < minLength) {
            showError('passwordGroup', 'passwordError', `パスワードは${minLength}文字以上で入力してください`);
            return false;
        } else if (isRegisterPage && password.length > 15) {
            showError('passwordGroup', 'passwordError', 'パスワードは15文字以内で入力してください');
            return false;
        } else if (isRegisterPage && !/^[a-zA-Z0-9]+$/.test(password)) {
            showError('passwordGroup', 'passwordError', 'パスワードは英数字のみ使用できます');
            return false;
        } else {
            clearFieldError('passwordGroup', 'passwordError');
            showSuccess('passwordGroup');
            return true;
        }
    }
    
    function validatePasswordConfirm() {
        if (!isRegisterPage || !passwordConfirmInput) return true;
        
        const password = passwordInput.value.trim();
        const passwordConfirm = passwordConfirmInput.value.trim();
        
        if (!passwordConfirm) {
            showError('passwordConfirmGroup', 'passwordConfirmError', 'パスワード確認を入力してください');
            return false;
        } else if (password !== passwordConfirm) {
            showError('passwordConfirmGroup', 'passwordConfirmError', 'パスワードが一致しません');
            return false;
        } else {
            clearFieldError('passwordConfirmGroup', 'passwordConfirmError');
            showSuccess('passwordConfirmGroup');
            return true;
        }
    }
    
    function updatePasswordStrength(password) {
        // Simple password strength calculation
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 20;
        
    }
    
    function hidePasswordStrength() {
        // Hide password strength indicator if implemented
    }
    
    function showError(groupId, errorId, message) {
        const group = document.getElementById(groupId);
        const errorElement = document.getElementById(errorId);
        
        if (group && errorElement) {
            group.classList.remove('success');
            group.classList.add('error');
            errorElement.textContent = message;
        }
    }
    
    function showSuccess(groupId) {
        const group = document.getElementById(groupId);
        
        if (group) {
            group.classList.remove('error');
            group.classList.add('success');
        }
    }
    
    function clearFieldError(groupId, errorId) {
        const group = document.getElementById(groupId);
        const errorElement = document.getElementById(errorId);
        
        if (group && errorElement) {
            group.classList.remove('error');
            group.classList.remove('success');
            errorElement.textContent = '';
        }
    }
    
    function clearErrors() {
        const errorGroups = document.querySelectorAll('.input-group.error');
        const successGroups = document.querySelectorAll('.input-group.success');
        const errorMessages = document.querySelectorAll('.error-message');
        
        errorGroups.forEach(group => group.classList.remove('error'));
        successGroups.forEach(group => group.classList.remove('success'));
        errorMessages.forEach(msg => msg.textContent = '');
    }
    
    function showLoading() {
        const continueBtn = document.getElementById('continueBtn');
        const loading = document.getElementById('loading');
        if (continueBtn && loading) {
            continueBtn.disabled = true;
            loading.style.display = 'inline';
        }
    }
    
    // Auto-hide alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
});