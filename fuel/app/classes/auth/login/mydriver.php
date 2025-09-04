<?php

class Auth_Login_Mydriver extends \Auth\Auth_Login_Simpleauth
{
    public function create_user($username, $password, $email = null, $group = 1, array $profile_fields = array())
    {
        \Log::debug('--- Mydriver: create_user called ---');

        $user_check = \Model_User::query()->where('username', '=', $username)->get_one();

        if ($user_check)
        {
            \Log::error('Mydriver: Username check failed, user exists.');
            throw new \SimpleUserUpdateException('Username already exists', 101);
        }

        $password = trim($password);
        $user = \Model_User::forge(array(
            'username'        => (string) $username,
            'password'        => $this->hash_password($password),
            'group'           => (int) $group,
            'email'           => null,
            'last_login'      => '0',
            'login_hash'      => '',
            'profile_fields'  => $profile_fields,
            'created_at'      => \Date::forge()->get_timestamp(),
        ));

        \Log::debug('Mydriver: User object forged. Attempting to save...');

        // Try to save the user and get validation errors if it fails
        if ($user->save())
        {
            \Log::info('Mydriver: User saved successfully! ID: '.$user->id);
            return (int) $user->id;
        }
        else
        {
            // THIS IS THE CRITICAL DEBUG STEP
            // If the save fails, log the specific validation error.
            $error = $user->validation()->error();
            \Log::error('Mydriver: Model save failed. Validation Error: ' . print_r($error, true));
            return false;
        }
    }
}