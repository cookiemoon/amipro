<?php
/**
 * User Model
 * This file has been REFACTORED to remove validation logic.
 */

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
        $hashed_password = \Auth::hash_password($password);

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
        $hashed_password = \Auth::hash_password($password);

        // Check if corresponding user exists with username and pwd
        $valid_credentials = static::query()
            ->where('username', $username)
            ->where('password', $hashed_password)
            ->get_one();

        if ($valid_credentials)
        {
            // If credentials are valid, perform the login.
            // Force session since we aren't using auth package here
            \Session::set('user_id', $valid_credentials->id);
            return $valid_credentials->id;
        }

        // Invalid credentials mean username or password is incorrect
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
    
    /**
     * Custom validation rule for unique username
     */
    public static function _validation_unique_username($username)
    {
        if (static::username_exists($username))
        {
            \Validation::active()->set_message('unique_username', 'このユーザー名は既に使用されています。');
            return false;
        }
        return true;
    }
}