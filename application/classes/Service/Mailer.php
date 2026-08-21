<?php defined('SYSPATH') or die('No direct script access.');

class Service_Mailer
{
    protected $_config;

    protected static $_phpmailer_loaded = FALSE;

    public function __construct()
    {
        $this->_config = Kohana::$config
            ->load('email')
            ->as_array();

        $local_file = APPPATH . 'config/email.php';

        if (file_exists($local_file))
        {
            $local_config = include $local_file;

            if (is_array($local_config))
            {
                $this->_config = array_replace_recursive(
                    $this->_config,
                    $local_config
                );
            }
        }
    }

    /**
     * Send survey invitation to participant.
     *
     * @param array $participant
     * @param array $schedule
     *
     * @return bool
     */
    public function send_survey_invitation(array $participant, array $schedule)
    {
        $name = trim($participant['first_name'] . ' ' . $participant['last_name']);

        return $this->_send(
            $participant['email'],
            $name,
            'Survey Invitation - ' . $schedule['title'],
            $this->_get_template($participant, $schedule),
            'You have been invited to complete the survey "' . $schedule['title'] . '".',
            'Survey invitation failed for :email. :error'
        );
    }

    /**
     * Send survey starting soon notification
     * to the survey creator.
     *
     * @param array $entry
     *
     * @return bool
     */
    public function send_creator_notification(array $entry)
    {
        $creator_email = $this->_config['creator_email'];

        return $this->_send(
            $creator_email,
            NULL,
            'Survey Starting Soon - ' . $entry['title'],
            $this->_get_creator_notification_template($entry),
            'Your survey "' . $entry['title'] . '" is scheduled to start at ' . $entry['start_date'] . '.',
            'Creator notification failed for :email. :error'
        );
    }

    /**
     * Shared send routine used by both public methods.
     * Removes the duplicated addAddress/isHTML/subject/body/
     * send/smtpClose/try-catch block that previously existed
     * twice.
     *
     * @param string      $to_email
     * @param string|null $to_name
     * @param string      $subject
     * @param string      $html_body
     * @param string      $alt_body
     * @param string      $error_log_message
     *
     * @return bool
     */
    protected function _send($to_email, $to_name, $subject, $html_body, $alt_body, $error_log_message)
    {
        $mail = $this->_create_mailer();

        try
        {
            if ($to_name)
            {
                $mail->addAddress($to_email, $to_name);
            }
            else
            {
                $mail->addAddress($to_email);
            }

            $mail->isHTML(TRUE);
            $mail->Subject = $subject;
            $mail->Body = $html_body;
            $mail->AltBody = $alt_body;
            
            $sent = $mail->send();

            $mail->smtpClose();

            return $sent;
        }
        catch (Exception $e)
        {
            Kohana::$log->add(
                Log::ERROR,
                $error_log_message,
                array(
                    ':email' => $to_email,
                    ':error' => $mail->ErrorInfo,
                )
            );

            return FALSE;
        }
    }

    /**
     * Create and configure PHPMailer instance.
     *
     * @return PHPMailer\PHPMailer\PHPMailer
     */
    protected function _create_mailer()
    {
        $this->_load_phpmailer();

        $mail = new PHPMailer\PHPMailer\PHPMailer(TRUE);

        $smtp = $this->_config['smtp'];
        $from = $this->_config['from'];

        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = TRUE;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->Port = (int) $smtp['port'];
        $mail->Timeout = (int) $smtp['timeout'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($from['email'], $from['name']);

        return $mail;
    }

    /**
     * Require the PHPMailer source files exactly once per
     * request instead of on every mailer instantiation.
     *
     * @return void
     */
    protected function _load_phpmailer()
    {
        if (self::$_phpmailer_loaded)
        {
            return;
        }

        require_once APPPATH . 'libraries/PHPMailer/src/Exception.php';
        require_once APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
        require_once APPPATH . 'libraries/PHPMailer/src/SMTP.php';

        self::$_phpmailer_loaded = TRUE;
    }

    /**
     * Build survey invitation email.
     *
     * @param array $participant
     * @param array $schedule
     *
     * @return string
     */
    protected function _get_template(array $participant, array $schedule)
    {
        $name = trim($participant['first_name'] . ' ' . $participant['last_name']);

        if ($name === '')
        {
            $name = 'Participant';
        }

        $survey = array(
            'title' => $schedule['title'],
            'description' => isset($schedule['description']) ? $schedule['description'] : '',
        );

        return View::factory('emails/survey_invitation')
            ->set('participant', $participant)
            ->set('participant_name', $name)
            ->set('survey', $survey)
            ->set('survey_url', URL::base(TRUE) . 'survey/' . $schedule['survey_id'], TRUE)
            ->render();
    }

    /**
     * Build creator notification email.
     *
     * @param array $entry
     *
     * @return string
     */
    protected function _get_creator_notification_template(array $entry)
    {
        return View::factory('emails/survey_creator_notification')
            ->set('survey', $entry)
            ->render();
    }
}