<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'smtp' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'dpkmurtadak@gmail.com',
        'password' => 'rtavuxuptmyxzgli',
        'encryption' => 'tls',
        'timeout' => 30,
    ),

    'from' => array(
        'email' => 'dpkmurtadak@gmail.com',
        'name' => 'SurveyKiwi',
    ),

    /*
     * Temporary survey creator email.
     *
     * This will later come from the User/Creator module.
     */
    'creator_email' => 'john@example.com',
);