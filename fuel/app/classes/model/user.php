<?php

class Model_User extends \Orm\Model
{
  protected static $_table_name = 'users';

  protected static $_properties = [
    'id',
    'username',
    'password',
    'created_at',
  ];

  protected static $_observers = [
    'Orm\\Observer_CreatedAt' => [
      'events' => ['before_insert'],
      'mysql_timestamp' => true,
    ],
  ];

  // 新登録管理
  public static function register($username, $password)
  {
    \Log::info('Attempting to register user: ' . $username, __METHOD__);

    if (static::username_exists($username)) {
      return false;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $user = static::forge([
      'username' => $username,
      'password' => $hashed_password,
    ]);

    $user->save();

    return $user;
  }

  // ログイン認証
  public static function authenticate($username, $password)
  {
    \Log::info('Attempting to authenticate user: ' . $username, __METHOD__);
    $user = static::query()->where('username', $username)->get_one();

    if ($user && password_verify($password, $user->password)) {
      \Session::set('user_id', $user->id);
      \Cookie::set('user_id', $user->id, 60 * 60 * 24 * 30);
      return $user->id;
    } else if ($user) {
      \Log::warning('Password verification failed for user: ' . $username, __METHOD__);
    }

    return false;
  }

  // ユーザー名の存在確認
  public static function username_exists($username)
  {
    $user = static::query()->where('username', $username)->get_one();
    return !empty($user);
  }
}