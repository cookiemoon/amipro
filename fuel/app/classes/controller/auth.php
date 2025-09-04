<?php
/**
 * FuelPHP Auth Controller
 * This file has been REFACTORED to include validation logic.
 */

class Controller_Auth extends Controller_Template
{
    public $template = 'template';

    public function before()
    {
        parent::before();
        $this->template->title = 'あみぷろ';
        $this->template->css = array('auth.css');
        $this->template->js = array('auth.js');
    }

    // --- LOGIN ACTIONS ---
    public function action_login()
    {
        if (\Session::get('user_id')) {
            \Response::redirect('dashboard');
        }
        $this->template->title = 'ログイン - あみぷろ';
        $this->template->content = \View::forge('auth/login');
    }

    public function action_process_login()
    {
        if (\Input::method() === 'POST')
        {
            // Call the static authenticate method on the model
            $user_id = Model_User::authenticate(
                \Input::post('username'),
                \Input::post('password')
            );

            if ($user_id)
            {
                // Authentication successful, set session and redirect
                \Response::redirect('dashboard');
            }
            else
            {
                // Authentication failed, set an error message
                $data['error_message'] = 'ユーザー名またはパスワードが正しくありません。';
            }
        }

        // Load the login form view, passing any error messages
        return \View::forge('auth/login', $data);
    }

    // --- REGISTRATION ACTIONS ---
    public function action_register()
    {
        if (\Auth::check()) {
            \Response::redirect('dashboard');
        }
        $this->template->content = \View::forge('auth/register');
        $this->template->title = '新規登録 - あみぷろ';
    }

    public function action_process_register()
    {
        // Check if the form was submitted
        if (\Input::method() === 'POST')
        {
            try
            {
                // Validate username and password
                /*$val = \Validation::forge();
                $val->add_field('username', 'ユーザー名', 'required|min_length[3]|max_length[20]|valid_string[alpha,numeric,underscore]|unique_username');
                $val->add_field('password', 'パスワード', 'required|min_length[6]|max_length[255]');

                if (!$val->run())
                {
                    // Validation failed, return to the registration form with errors
                    $data['error_message'] = "ユーザー名とパスワードを正しく入力してください。";
                    return \View::forge('auth/register', $data);
                }*/

                // Call the static register method on the model
                Model_User::register(
                    \Input::post('username'),
                    \Input::post('password')
                );

                // Redirect to a success page or login page
                \Response::redirect('dashboard');
            }
            catch (\Exception $e)
            {
                // Handle the error (e.g., duplicate username)
                // You would typically display an error message to the user
                $data['error_message'] = "ユーザー名は既に使用されています。別のユーザー名を選んでください。";
            }
        }

        // Load the registration form view
        return \View::forge('auth/register');
    }

    // --- LOGOUT ACTION ---
    public function action_logout()
    {
        \Session::delete('user_id');
        \Session::set_flash('success', 'ログアウトしました。');
        \Response::redirect('auth/login');
    }
}