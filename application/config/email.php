<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'smtp' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'YOUR_SMTP_USERNAME',
        'password' => 'YOUR_SMTP_PASSWORD',
        'encryption' => 'tls',
        'timeout' => 30,
    ),

    'from' => array(
        'email' => 'no-reply@surveykiwi.test',
        'name' => 'SurveyKiwi',
    ),

    /*
     * Temporary survey creator email.
     *
     * This will later come from the User/Creator module.
     */
    'creator_email' => 'john@example.com',
);