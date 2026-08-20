<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Controller_Admin_Template
    extends Controller_Template
{
    public $template = 'layout/master';

    /**
     * Authenticate all admin pages.
     */
    public function before()
    {
        parent::before();

        $auth = new Service_Auth;

        if (!$auth->check())
        {
            HTTP::redirect('login');
            return;
        }

        $this->template->current_user = $auth->user();
    }
}