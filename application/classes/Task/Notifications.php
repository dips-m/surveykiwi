<?php defined('SYSPATH') or die('No direct script access.');


/**
 * Send survey notifications for upcoming scheduled surveys.
 *
 * Checks for surveys scheduled to start within the next hour
 * and sends notifications to participants who have not
 * successfully received an invitation.
 *
 * Failed invitations are retried on the next cron execution
 * while the survey remains within the notification window.
 *
 * @package    SurveyKiwi
 * @category   Tasks
 */
class Task_Notifications extends Minion_Task
{
    protected $_defaults = array();


    /**
     * Execute notification task.
     *
     * @param array $params
     *
     * @return void
     */
    protected function _execute(array $params)
    {
        echo 'Survey notification task started.'
            . PHP_EOL;


        $survey_model = new Model_Survey();
        $mailer = new Service_Mailer();


        /*
         * Get surveys scheduled to start within
         * the next one hour.
         */
        $schedules =
            $survey_model->get_upcoming_schedules();


        echo 'Upcoming surveys found: '
            . count($schedules)
            . PHP_EOL;


        foreach ($schedules as $schedule)
        {
            $this->_process_schedule(
                $survey_model,
                $mailer,
                $schedule
            );
        }


        echo 'Survey notification task completed.'
            . PHP_EOL;
    }


    /**
     * Process one scheduled survey occurrence.
     *
     * @param Model_Survey   $survey_model
     * @param Service_Mailer $mailer
     * @param array          $schedule
     *
     * @return void
     */
    protected function _process_schedule(
        Model_Survey $survey_model,
        Service_Mailer $mailer,
        array $schedule
    )
    {
        echo PHP_EOL;
    
        echo 'Survey: '
            . $schedule['title']
            . PHP_EOL;
    
        echo 'Schedule Entry ID: '
            . $schedule['id']
            . PHP_EOL;
    
        echo 'Scheduled at: '
            . $schedule['start_date']
            . PHP_EOL;
    
        /*
         * Get participants who have not successfully
         * received the invitation for this specific
         * schedule entry.
         */
        $participants =
            $survey_model->get_pending_participants(
                $schedule['id'],
                $schedule['survey_id']
            );
    
        echo 'Pending participants: '
            . count($participants)
            . PHP_EOL;
    
        if (empty($participants))
        {
            echo 'No pending participants.'
                . PHP_EOL;
    
            return;
        }
    
        foreach ($participants as $participant)
        {
            echo 'Sending to: '
                . $participant['email']
                . PHP_EOL;
    
            $sent = $mailer->send_survey_invitation(
                $participant,
                $schedule
            );
    
            if ($sent)
            {
                $survey_model->save_invitation(
                    $schedule['id'],
                    $participant['id'],
                    'sent'
                );
    
                echo $participant['email']
                    . ' ........ SENT'
                    . PHP_EOL;
            }
            else
            {
                $survey_model->save_invitation(
                    $schedule['id'],
                    $participant['id'],
                    'fail'
                );
    
                echo $participant['email']
                    . ' ........ FAILED'
                    . PHP_EOL;
            }
        }
    }
}