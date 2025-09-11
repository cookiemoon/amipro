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

    public static function register($username, $password)
    {
        // Hash the password before creating the user object
        // Hashed password is more secure since we cannot access the original text
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if username already exists
        if (static::username_exists($username)) {
            return false;
        }

        // Create a new User object
        $user = static::forge([
            'username' => $username,
            'password' => $hashed_password,
        ]);

        // Save the new user to the database
        $user->save();

        return $user;
    }

    /**
     * Authenticate user login
     * Returns user ID if successful, false otherwise
     */
    public static function authenticate($username, $password)
    {
        // Validate user manually
        $user = static::query()->where('username', $username)->get_one();

        if ($user && password_verify($password, $user->password)) {
            // If the password is correct, set the session and return the user ID
            \Log::debug('Password verified for user ID: ' . $user->id, __METHOD__);
            \Session::set('user_id', $user->id);
            \Cookie::set('user_id', $user->id, 60 * 60 * 24 * 30);
            return $user->id;
        }

        return false;
    }

    /**
     * Check if username already exists (for validation)
     */
    public static function username_exists($username)
    {
        $user = static::query()->where('username', $username)->get_one();
        return !empty($user);
    }
}