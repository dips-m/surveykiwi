<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Auth extends Controller
{
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

    public function action_logout()
    {
        $auth = new Service_Auth;

        $auth->logout();

        HTTP::redirect('login');
        return;
    }
}