<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Auth
{
    const SESSION_USER_ID = 'auth_user_id';
    const SESSION_USER = 'auth_user';


    /**
     * Authenticate user.
     *
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function login($email, $password)
    {
        $model = new Model_User;

        $user = $model->find_by_email($email);

        if ( ! $user)
        {
            return FALSE;
        }

        if ( ! password_verify($password, $user['password']))
        {
            return FALSE;
        }

        $session = Session::instance();

        // Prevent session fixation.
        $session->regenerate();

        $session->set(
            self::SESSION_USER_ID,
            $user['id']
        );

        $session->set(
            self::SESSION_USER,
            array(
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            )
        );

        return TRUE;
    }


    /**
     * Check whether user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return (bool) Session::instance()->get(
            self::SESSION_USER_ID
        );
    }


    /**
     * Get logged-in user.
     *
     * @return array|NULL
     */
    public function user()
    {
        return Session::instance()->get(
            self::SESSION_USER
        );
    }


    /**
     * Logout current user.
     *
     * @return void
     */
    public function logout()
    {
        $session = Session::instance();

        $session->delete(self::SESSION_USER_ID);
        $session->delete(self::SESSION_USER);

        $session->regenerate(TRUE);
    }
}