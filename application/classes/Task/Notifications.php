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

        echo 'Scheduled at: '
            . $schedule['next_run_at']
            . PHP_EOL;


        /*
         * Find the existing log for this exact
         * scheduled occurrence.
         */
        $schedule_log =
            $survey_model->get_schedule_log(
                $schedule['survey_id'],
                $schedule['next_run_at']
            );


        /*
         * Create the log only once.
         */
        if ($schedule_log)
        {
            $schedule_log_id =
                (int) $schedule_log['id'];

            echo 'Existing schedule log: '
                . $schedule_log_id
                . PHP_EOL;
        }
        else
        {
            $schedule_log_id =
                $survey_model->create_schedule_log(
                    $schedule['survey_id'],
                    $schedule['next_run_at'],
                    'failed'
                );

            echo 'Created schedule log: '
                . $schedule_log_id
                . PHP_EOL;
        }


        /*
         * Get only participants who:
         *
         * - have never been sent an invitation, OR
         * - had their previous invitation rejected.
         */
        $participants =
            $survey_model->get_pending_participants(
                $schedule['survey_id'],
                $schedule_log_id
            );


        echo 'Pending participants: '
            . count($participants)
            . PHP_EOL;


        /*
         * Nothing to send.
         *
         * This happens when all participants have
         * already successfully received their invitation.
         */
        if (empty($participants))
        {
            $survey_model->update_schedule_log_status(
                $schedule_log_id,
                'success'
            );

            echo 'No pending participants.'
                . PHP_EOL;

            return;
        }


        $all_sent = TRUE;


        foreach ($participants as $participant)
        {
            echo 'Sending to: '
                . $participant['email']
                . PHP_EOL;


            /*
             * Send the email.
             */
            $sent =
                $mailer->send_survey_invitation(
                    $participant,
                    $schedule
                );


            if ($sent)
            {
                /*
                 * Record successful attempt.
                 */
                $survey_model->create_invitation(
                    $schedule_log_id,
                    $participant['id'],
                    'sent'
                );


                echo '  '
                    . $participant['email']
                    . ' ........ SENT'
                    . PHP_EOL;
            }
            else
            {
                $all_sent = FALSE;


                /*
                 * Record failed attempt.
                 *
                 * This participant will be retried
                 * during the next cron execution.
                 */
                $survey_model->create_invitation(
                    $schedule_log_id,
                    $participant['id'],
                    'bounced'
                );


                echo '  '
                    . $participant['email']
                    . ' ........ FAILED'
                    . PHP_EOL;
            }


            /*
             * Temporary delay for Mailtrap testing.
             *
             * Remove or configure this when using
             * a production SMTP provider.
             */
            sleep(5);
        }


        /*
         * Update the schedule log.
         */
        if ($all_sent)
        {
            $survey_model->update_schedule_log_status(
                $schedule_log_id,
                'success'
            );

            echo 'Schedule completed successfully.'
                . PHP_EOL;
        }
        else
        {
            /*
             * Keep status as failed.
             *
             * The next cron execution will retry
             * only the failed participants.
             */
            $survey_model->update_schedule_log_status(
                $schedule_log_id,
                'failed'
            );

            echo 'Schedule completed with failures.'
                . PHP_EOL;
        }
    }
}