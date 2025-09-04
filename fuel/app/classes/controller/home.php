<?php
class Controller_Home extends Controller
{
    public function action_index()
    {
        // Get the current user's username without using Auth package
        $user_id = \Session::get('user_id');

        // 2. Find the user in the database using the ID.
        $current_user = \Model_User::find($user_id);

        // You can now access the user's data as properties of the object.
        if ($current_user) {
            $data['username'] = $current_user->username;
            // echo $current_user->email;
            // echo $current_user->id;
        } else {
            // Handle the case where the user is not found
            $data['username'] = 'Guest';
        }

        // Return the view
        return \View::forge('simple_page', $data);
    }
}