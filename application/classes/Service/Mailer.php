<?php defined('SYSPATH') or die('No direct script access.');


class Service_Mailer
{
    protected $_config;


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


    public function send_survey_invitation(
        array $participant,
        array $schedule
    )
    {
        require_once APPPATH
            . 'libraries/PHPMailer/src/Exception.php';

        require_once APPPATH
            . 'libraries/PHPMailer/src/PHPMailer.php';

        require_once APPPATH
            . 'libraries/PHPMailer/src/SMTP.php';


        $mail = new PHPMailer\PHPMailer\PHPMailer(TRUE);


        try
        {
            $smtp = $this->_config['smtp'];
            $from = $this->_config['from'];

            $mail->isSMTP();

            // DEBUG
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'echo';

            $mail->Host = $smtp['host'];
            $mail->SMTPAuth = TRUE;
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];
            $mail->Port = (int) $smtp['port'];
            $mail->Timeout = (int) $smtp['timeout'];

            $mail->SMTPSecure =
                PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

            echo PHP_EOL;
            echo '--- SMTP CONFIG ---' . PHP_EOL;
            echo 'Host: ' . $mail->Host . PHP_EOL;
            echo 'Port: ' . $mail->Port . PHP_EOL;
            echo 'Username: ' . $mail->Username . PHP_EOL;
            echo 'Encryption: STARTTLS' . PHP_EOL;
            echo '-------------------' . PHP_EOL;

            $mail->setFrom(
                $from['email'],
                $from['name']
            );

            $recipient_name = trim(
                $participant['first_name']
                . ' '
                . $participant['last_name']
            );

            echo 'Recipient: '
                . $participant['email']
                . PHP_EOL;

            $mail->addAddress(
                $participant['email'],
                $recipient_name
            );

            $mail->isHTML(TRUE);

            $mail->Subject =
                'Survey Invitation - '
                . $schedule['title'];

            $mail->Body = $this->_get_template(
                $participant,
                $schedule
            );

            $mail->AltBody =
                'You have been invited to complete the survey "'
                . $schedule['title']
                . '".';

            echo 'Calling send()...' . PHP_EOL;

            $sent = $mail->send();

            echo 'send() returned: ';
            var_dump($sent);

            echo 'ErrorInfo: ';
            var_dump($mail->ErrorInfo);

            if ( ! $sent)
            {
                echo 'SMTP Error: ';
                var_dump($mail->ErrorInfo);

                return FALSE;
            }

            echo 'Email successfully accepted by SMTP server.'
                . PHP_EOL;

            $mail->smtpClose();

            return TRUE;
        }
        catch (Exception $e)
        {
            echo 'EXCEPTION:' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;

            echo 'PHPMailer ErrorInfo:' . PHP_EOL;
            echo $mail->ErrorInfo . PHP_EOL;

            return FALSE;
        }
        
    }

    /**
     * Build survey invitation email.
     *
     * @param array $participant
     * @param array $schedule
     *
     * @return string
     */
    protected function _get_template(
        array $participant,
        array $schedule
    )
    {
        $name = trim(
            $participant['first_name']
            . ' '
            . $participant['last_name']
        );

        if ($name === '')
        {
            $name = 'Participant';
        }

        $survey_url = URL::site('dashboard');

        $survey = array(
            'title'       => $schedule['title'],
            'description' => isset($schedule['description'])
                ? $schedule['description']
                : '',
        );

        return View::factory('emails/survey_invitation')
            ->set('participant', $participant)
            ->set('participant_name', $name)
            ->set('survey', $survey)
            ->set('survey_url', $survey_url)
            ->render();
    }
}