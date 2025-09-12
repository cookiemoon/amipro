<?php

class Controller_Auth extends Controller_Hybrid
{
    public $template = 'template';
    protected $format = 'json';

    public function before()
    {
        parent::before();
        if(!$this->is_restful()) {
            $this->template->js = 'auth.js';
            $this->template->css = 'auth.css';
        }
    }

    // --- ログイン ---

    // ログインページの表示
    public function get_login()
    {
        if (\Session::get('user_id') || \Cookie::get('user_id')) {
            if (!\Session::get('user_id') && \Cookie::get('user_id')) {
                \Session::set('user_id', \Cookie::get('user_id'));
            }
            \Response::redirect('dashboard');
        }
        
        $this->template->title = 'ログイン - あみぷろ';
        $this->template->content = \View::forge('auth/login');
    }

    // ログイン処理
    public function post_login()
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $user_id = \Model_User::authenticate(
            \Input::post('username'),
            \Input::post('password')
        );

        if ($user_id) {
            return $this->response(['success' => true]);
        } else {
            return $this->response(['success' => false, 'error' => 'IDまたはパスワードが正しくありません。'], 401);
        }
    }

    // --- 新登録 ---

    // 新登録ページの表示
    public function get_register()
    {
        if (\Session::get('user_id') || \Cookie::get('user_id')) {
            if (!\Session::get('user_id') && \Cookie::get('user_id')) {
                \Session::set('user_id', \Cookie::get('user_id'));
            }
            \Response::redirect('dashboard');
        }

        $this->template->title = '新規登録 - あみぷろ';
        $this->template->content = \View::forge('auth/register');
    }

    // 新登録処理
    public function post_register()
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $val = \Validation::forge();
        $val->add_field('username', 'ID', 'required|min_length[3]|max_length[50]');
        $val->add_field('password', 'パスワード', 'required|min_length[8]|match_field[password_confirm]');
        $val->add_field('password_confirm', 'パスワード確認', 'required');

        if ($val->run())
        {
            $user = \Model_User::register(
                $val->validated('username'),
                $val->validated('password')
            );

            if ($user) {
                \Session::set('user_id', $user->id);
                return $this->response(['success' => true, 'user_id' => $user->id]);
            } else {
                return $this->response(['success' => false, 'error' => 'このIDは既に使用されています。'], 409);
            }
        }
        
        return $this->response(['success' => false, 'error' => 'IDとパスワードを正しく入力してください。'], 400);
    }

    // --- ログアウト ---

    // ログアウト処理
    public function action_logout()
    {
        \Session::delete('user_id');
        \Cookie::delete('user_id');
        \Response::redirect('auth/login');
    }
}