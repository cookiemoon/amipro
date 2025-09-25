<div class="container" data-bind="with: login">

  <main class="main-content">
    <h2 class="login-title">Login</h2>

    <div class="alert alert-error" data-bind="html: serverErrorMessage, visible: serverErrorMessage"></div>
    
    <form class="login-form" data-bind="submit: loginUser">
      <div class="input-group">
        <input type="text" class="form-input" id="username" placeholder=" " required 
             data-bind="value: username, valueUpdate: 'afterkeydown'">
        <label for="username" class="form-label">ID</label>
      </div>
      <div class="input-group password">
        <input type="password" class="form-input" id="password" placeholder=" " required 
             data-bind="value: password">
        <label for="password" class="form-label">Password</label>
      </div>
      <button type="submit" class="continue-button" data-bind="text: buttonText, disable: isLoading() || !isValid()"></button>
    </form>

    <div class="registration-link">
      <span>If you do not have an account, please proceed to the </span>
      <a href="<?php echo \Uri::create('auth/register'); ?>" class="gradient-text">registration page</a>
      <span>.</span>
    </div>
  </main>
</div>