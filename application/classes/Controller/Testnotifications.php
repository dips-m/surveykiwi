<?php defined('SYSPATH') or die('No direct script access.');


class Controller_TestNotifications extends Controller
{
    public function action_index()
    {
        $task = Minion_Task::factory(
            array(
                'task' => 'notifications',
            )
        );

        $task->execute();

        $this->response->body(
            'Notification task executed.'
        );
    }
}