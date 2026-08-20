<?php defined('SYSPATH') OR die('No direct script access.');

class Model_User extends Model
{
    /**
     * Find active user by email.
     *
     * @param string $email
     *
     * @return array|FALSE
     */
    public function find_by_email($email)
    {
        return DB::select(
                'id',
                'name',
                'email',
                'password',
                'status'
            )
            ->from('users')
            ->where('email', '=', $email)
            ->where('status', '=', 'active')
            ->limit(1)
            ->execute()
            ->current();
    }
}