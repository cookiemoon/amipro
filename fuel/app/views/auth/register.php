<div class="container" data-bind="with: register">
    <main class="main-content">
        <h2 class="registration-title">新登録</h2>

        <div class="alert alert-error" data-bind="html: serverErrorMessage, visible: serverErrorMessage"></div>
        
        <form class="registration-form" data-bind="submit: registerUser">
            
            <div class="input-group">
                <input type="text" class="form-input" id="username" placeholder=" " required 
                       data-bind="value: username, valueUpdate: 'afterkeydown'">
                <label for="username" class="form-label">ID</label>
                <div class="error-message" data-bind="html: usernameError, visible: usernameError"></div>
            </div>

            <div class="input-group password">
                <input type="password" class="form-input" id="password" placeholder=" " required 
                       data-bind="value: password, valueUpdate: 'afterkeydown'">
                <label for="password" class="form-label">パスワード</label>
                <div class="error-message" data-bind="html: passwordError, visible: passwordError"></div>
            </div>

            <div class="input-group password-confirm">
                <input type="password" class="form-input" id="password_confirm" placeholder=" " required 
                       data-bind="value: passwordConfirm, valueUpdate: 'afterkeydown'">
                <label for="password_confirm" class="form-label">パスワード確認</label>
                <div class="error-message" data-bind="html: passwordConfirmError, visible: passwordConfirmError"></div>
            </div>

            <div class="password-requirements">
                <p class="requirements-text">パスワードは英数字で8～15字以内</p>
            </div>
            
            <button type="submit" class="continue-button" data-bind="text: buttonText, disable: isLoading() || !isValid()"></button>
        </form>

        <div class="login-link">
            <span>すでに登録の方は</span>
            <a href="<?php echo \Uri::create('auth/login'); ?>" class="gradient-text">ログインページ</a>
            <span>へ</span>
        </div>
    </main>
</div>