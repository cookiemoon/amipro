function AppViewModel() {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl || '/';

    // --- Login ViewModel ---
    self.login = new function() {
        const loginSelf = this;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function updateToken(newToken) {
            csrfToken = newToken;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
        }

        loginSelf.username = ko.observable('');
        loginSelf.password = ko.observable('');
        loginSelf.serverErrorMessage = ko.observable(null);
        loginSelf.isLoading = ko.observable(false);
        loginSelf.isValid = ko.computed(() => {
            return loginSelf.username().length > 0 &&
                   loginSelf.password().length > 0
        });
        loginSelf.buttonText = ko.computed(() => loginSelf.isLoading() ? '...' : '続く');

        loginSelf.loginUser = function() {
            if (!loginSelf.isValid()) return;

            loginSelf.isLoading(true);
            loginSelf.serverErrorMessage(null);
            
            const formData = new FormData();
            formData.append('username', loginSelf.username());
            formData.append('password', loginSelf.password());
            formData.append('fuel_csrf_token', csrfToken);

            fetch(`${baseUrl}login`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_csrf_token) {
                    updateToken(data.new_csrf_token);
                }
                if (data.success) {
                    window.location.href = `${baseUrl}projects`;
                } else {
                    loginSelf.serverErrorMessage(data.error || 'ログインに失敗しました。');
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                loginSelf.serverErrorMessage('エラーが発生しました。');
            })
            .finally(() => {
                loginSelf.isLoading(false);
            });
        };
    };

    // --- Register ViewModel ---
    self.register = new function() {
        const regSelf = this;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function updateToken(newToken) {
            csrfToken = newToken;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
        }

        regSelf.username = ko.observable('');
        regSelf.password = ko.observable('');
        regSelf.passwordConfirm = ko.observable('');
        regSelf.serverErrorMessage = ko.observable(null);
        regSelf.isLoading = ko.observable(false);
        regSelf.buttonText = ko.computed(() => regSelf.isLoading() ? '...' : '続く');

        regSelf.usernameError = ko.computed(() => {
            const u = regSelf.username();
            var error = null;
            if (u.length > 0 && u.length < 3) error = 'IDは3文字以上で入力してください。';
            if (u.length > 50) error = 'IDは50文字以内で入力してください。';
            return error;
        });

        regSelf.passwordError = ko.computed(() => {
            const p = regSelf.password();
            var error = null;
            if (p.length > 0 && !/^[a-zA-Z0-9]+$/.test(p)) error = 'パスワードを正しく入力してください。';
            if (p.length > 0 && p.length < 8) error = 'パスワードを正しく入力してください。';
            if (p.length > 15) error = 'パスワードを正しく入力してください。';
            return error;
        });

        regSelf.passwordConfirmError = ko.computed(() => {
            var error = null;
            if (regSelf.passwordConfirm().length > 0 && regSelf.password() !== regSelf.passwordConfirm()) {
                error = 'パスワードが一致しません。';
            }
            return error;
        });

        regSelf.isValid = ko.computed(() => {
            return regSelf.username().length > 0 &&
                   regSelf.password().length > 0 &&
                   regSelf.passwordConfirm().length > 0 &&
                   !regSelf.usernameError() &&
                   !regSelf.passwordError() &&
                   !regSelf.passwordConfirmError();
        });
        
        regSelf.buttonText = ko.computed(() => regSelf.isLoading() ? '...' : '続く');
        regSelf.registerUser = function() {
            if (!regSelf.isValid()) return;

            regSelf.isLoading(true);
            regSelf.serverErrorMessage(null);
            
            const formData = new FormData();
            formData.append('username', regSelf.username());
            formData.append('password', regSelf.password());
            formData.append('password_confirm', regSelf.passwordConfirm());
            formData.append('fuel_csrf_token', csrfToken);

            fetch(`${baseUrl}register`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_csrf_token) {
                    updateToken(data.new_csrf_token);
                }
                if (data.success) {
                    alert("アカウントが正常に作成されました。");
                    window.location.href = `${baseUrl}projects`;
                } else {
                    regSelf.serverErrorMessage(data.error || '登録に失敗しました。');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                regSelf.serverErrorMessage('エラーが発生しました。');
            })
            .finally(() => {
                regSelf.isLoading(false);
            });
        };
    };
}

ko.applyBindings(new AppViewModel());