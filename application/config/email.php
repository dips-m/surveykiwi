<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'smtp' => array(
        'host' => 'sandbox.smtp.mailtrap.io',
        'port' => 2525,
        'username' => 'YOUR_MAILTRAP_USERNAME',
        'password' => 'YOUR_MAILTRAP_PASSWORD',
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