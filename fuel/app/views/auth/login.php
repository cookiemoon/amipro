<?php
/**
 * FuelPHP Login View
 * Place this file at: fuel/app/views/auth/login.php
 */
?>
<div class="container">
    <!-- Gradient Header -->
    <header class="header">
        <h1>あみぷろ</h1>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Login Title -->
        <h2 class="login-title">ログイン</h2>

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

        <!-- Login Form -->
        <?php echo Form::open(array(
            'action' => Uri::create('auth/process_login'), 
            'method' => 'post', 
            'class' => 'login-form', 
            'id' => 'loginForm'
        )); ?>
        
            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
            
            <!-- ID Input -->
            <div class="input-group" id="idGroup">
                <?php echo Form::input('username', Input::post('username'), array(
                    'class' => 'form-input',
                    'id' => 'username',
                    'placeholder' => ' ',
                    'required' => 'required'
                )); ?>
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

            <!-- Continue Button -->
            <button type="submit" class="continue-button" id="continueBtn">
                続く
                <span class="loading" id="loading" style="display: none;">...</span>
            </button>
        
        <?php echo Form::close(); ?>

        <!-- Registration Link -->
        <div class="registration-link">
            <span>未登録の方は</span>
            <a href="<?php echo Uri::create('auth/register'); ?>" class="gradient-text">新登録ページ</a>
            <span>へ</span>
        </div>
    </main>
</div>