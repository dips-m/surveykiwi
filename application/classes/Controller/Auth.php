<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Handles user authentication: login, logout, and
 * guarding all other actions behind an auth check.
 */
class Controller_Auth extends Controller
{
    /**
     * Runs before every action in this controller.
     * Enforces that the user is logged in, except for
     * the login action itself (which must be reachable
     * by anonymous/unauthenticated visitors).
     */
    public function before()
    {
        parent::before();

        // Login page must be accessible without authentication.
        if ($this->request->action() === 'login')
        {
            return;
        }

        $auth = new Service_Auth;

        if (!$auth->check())
        {
            HTTP::redirect('login');
            return;
        }
    }

    /**
     * Displays the login form (GET) and processes
     * login attempts (POST).
     */
    public function action_login()
    {
        $auth = new Service_Auth;

        if ($auth->check())
        {
            HTTP::redirect('dashboard');
            return;
        }

        $errors = array();
        $email = '';

        if ($this->request->method() === Request::POST)
        {
            $email = trim($this->request->post('email'));
            $password = $this->request->post('password');

            if ($email === '')
            {
                $errors['email'] = 'Email address is required.';
            }

            if ($password === '')
            {
                $errors['password'] = 'Password is required.';
            }

            if (empty($errors))
            {
                if ($auth->login($email, $password))
                {
                    HTTP::redirect('dashboard');
                    return;
                }

                $errors['login'] =
                    'Invalid email address or password.';
            }
        }

        $view = View::factory('auth/login');

        $view->errors = $errors;
        $view->email = $email;

        $this->response->body($view);
    }

    /**
     * Logs the current user out and redirects to the
     * login page.
     */
    public function action_logout()
    {
        $auth = new Service_Auth;

        $auth->logout();

        HTTP::redirect('login');
        return;
    }
}