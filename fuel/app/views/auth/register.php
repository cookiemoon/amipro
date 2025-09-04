<?php
/**
 * FuelPHP Registration View
 * Place this file at: fuel/app/views/auth/register.php
 */
?>
<div class="container">
    <!-- Gradient Header -->
    <header class="header">
        <h1>あみぷろ</h1>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Registration Title -->
        <h2 class="registration-title">新登録</h2>

        <?php if (isset($error_message) && $error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message) && $success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <?php echo Form::open(array(
            'action' => Uri::create('auth/process_register'), 
            'method' => 'post', 
            'class' => 'registration-form', 
            'id' => 'registrationForm'
        )); ?>
            
            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
            
            <!-- ID Input -->
            <div class="input-group" id="idGroup">
                <?php echo Form::input('username', 
                    isset($form_data['username']) ? $form_data['username'] : Input::post('username'), 
                    array(
                        'class' => 'form-input',
                        'id' => 'username',
                        'placeholder' => ' ',
                        'required' => 'required'
                    )
                ); ?>
                <label for="username" class="form-label">ID</label>
                <div class="error-message" id="usernameError"></div>
            </div>

            <!-- Password Input -->
            <div class="input-group password" id="passwordGroup">
                <?php echo Form::password('password', '', array(
                    'class' => 'form-input',
                    'id' => 'password',
                    'placeholder' => ' ',
                    'required' => 'required'
                )); ?>
                <label for="password" class="form-label">パスワード</label>
                <div class="error-message" id="passwordError"></div>
            </div>

            <!-- Password Confirmation Input -->
            <div class="input-group password-confirm" id="passwordConfirmGroup">
                <?php echo Form::password('password_confirm', '', array(
                    'class' => 'form-input',
                    'id' => 'password_confirm',
                    'placeholder' => ' ',
                    'required' => 'required'
                )); ?>
                <label for="password_confirm" class="form-label">パスワード確認</label>
                <div class="error-message" id="passwordConfirmError"></div>
            </div>

            <!-- Password Requirements -->
            <div class="password-requirements">
                <p class="requirements-text">パスワードは英数字で8～15字以内</p>
            </div>

            <!-- Continue Button -->
            <button type="submit" class="continue-button" id="continueBtn">
                続く
                <span class="loading" id="loading" style="display: none;">...</span>
            </button>
        
        <?php echo Form::close(); ?>

        <!-- Login Link -->
        <div class="login-link">
            <span>すでに登録の方は</span>
            <a href="<?php echo Uri::create('auth/login'); ?>" class="gradient-text">ログインページ</a>
            <span>へ</span>
        </div>
    </main>
</div>