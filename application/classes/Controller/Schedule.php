<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Schedule extends Controller_Template
{
    public $template = 'layout/master';

    public function action_index()
    {
        $id = (int) $this->request->param('id');
        $survey = $this->_get_survey_or_404($id);

        // Fetch existing schedule if present
        $schedule = DB::select()
            ->from('survey_schedules')
            ->where('survey_id', '=', $id)
            ->execute()
            ->current();

        // Fetch existing participants
        $participants = DB::select()
            ->from('survey_participants')
            ->where('survey_id', '=', $id)
            ->execute()
            ->as_array();

        // Calculate future dates directly if schedule parameters exist
        $scheduleDates = [];
        if ($schedule && !empty($schedule['frequency']) && !empty($schedule['start_date']) && !empty($schedule['end_date']))
        {
            $scheduleDates = $this->calculate_future_dates($schedule['frequency'], $schedule['start_date'], $schedule['end_date']);
        }

        $this->template->content = View::factory('survey/schedule')
            ->set('survey', $survey)
            ->set('schedule', $schedule)
            ->set('scheduleDates', $scheduleDates)
            ->set('participants', $participants);
    }

    /**
     * Handle main schedule form saving (Frequency, Start/End Dates, Reminders)
     */
    public function action_save()
    {
        if ($this->request->method() !== HTTP_Request::POST) {
            $this->_json_response('error', 'Invalid request method.');
            return;
        }

        $id = (int) $this->request->param('id');
        $schedule_id = (int) $this->request->post('schedule_id');

        try {
            $this->_get_survey_or_404($id);

            // Validate schedule fields
            $post = Validation::factory($this->request->post());

            $post->rule('frequency', 'not_empty')
                ->rule('start_date', 'not_empty')
                ->rule('end_date', 'not_empty');

            if (!$post->check()) {
                $this->_json_response(
                    'error',
                    'Please fill out all required fields.'
                );
                return;
            }

            $frequency = $post['frequency'];
            $start_date = $post['start_date'];
            $end_date = $post['end_date'];

            $start_time = strtotime($start_date);
            $end_time = strtotime($end_date);

            if ($start_time < (time() - 60)) {
                $this->_json_response(
                    'error',
                    'Start date and time cannot be in the past.'
                );
                return;
            }

            if ($end_time <= $start_time) {
                $this->_json_response(
                    'error',
                    'End date and time must be greater than start date.'
                );
                return;
            }

            // Check existing participants
            $existing_participants_count = DB::select(
                array(DB::expr('COUNT(*)'), 'total')
            )
                ->from('survey_participants')
                ->where('survey_id', '=', $id)
                ->execute()
                ->get('total');

            // Check uploaded CSV
            $file = isset($_FILES['participants_file'])
                ? $_FILES['participants_file']
                : NULL;

            $has_file = (
                $file &&
                isset($file['error']) &&
                $file['error'] === UPLOAD_ERR_OK
            );

            // CSV required only when no participants exist
            if ((int) $existing_participants_count === 0 && !$has_file) {
                $this->_json_response(
                    'error',
                    'Participants are required. Please upload a CSV file.'
                );
                return;
            }

            // Validate CSV if uploaded
            if ($has_file) {

                $extension = strtolower(
                    pathinfo($file['name'], PATHINFO_EXTENSION)
                );

                if ($extension !== 'csv') {
                    $this->_json_response(
                        'error',
                        'Please upload a valid CSV file.'
                    );
                    return;
                }
            }

            $start_timestamp = date('Y-m-d H:i:s', $start_time);
            $end_timestamp = date('Y-m-d H:i:s', $end_time);

            $reminders_enabled =
                isset($_POST['reminders_enabled']) ? 1 : 0;

            $reminder_interval =
                (int) $this->request->post('reminder_interval_days');

            $is_active =
                isset($_POST['is_active']) ? 1 : 0;

            // Check existing schedule
            $existing = DB::select('id')
                ->from('survey_schedules')
                ->where('id', '=', $schedule_id)
                ->where('survey_id', '=', $id)
                ->execute()
                ->current();

            if ($existing) {

                DB::update('survey_schedules')
                    ->set(array(
                        'frequency' => $frequency,
                        'start_date' => $start_timestamp,
                        'end_date' => $end_timestamp,
                        'next_run_at' => $start_timestamp,
                        'reminders_enabled' => $reminders_enabled,
                        'reminder_interval_days' => $reminder_interval,
                        'is_active' => $is_active,
                        'updated_at' => date('Y-m-d H:i:s')
                    ))
                    ->where('id', '=', $schedule_id)
                    ->where('survey_id', '=', $id)
                    ->execute();

            } else {

                DB::insert('survey_schedules', array(
                    'survey_id',
                    'frequency',
                    'start_date',
                    'end_date',
                    'next_run_at',
                    'reminders_enabled',
                    'reminder_interval_days',
                    'is_active',
                    'created_at',
                    'updated_at'
                ))
                    ->values(array(
                        $id,
                        $frequency,
                        $start_timestamp,
                        $end_timestamp,
                        $start_timestamp,
                        $reminders_enabled,
                        $reminder_interval,
                        $is_active,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s')
                    ))
                    ->execute();
            }

            // Import only when a new CSV is uploaded
            if ($has_file) {
                $imported_count = $this->_import_csv_participants(
                    $id,
                    $file
                );

                $this->_json_response(
                    'success',
                    'Schedule saved successfully. ' .
                    $imported_count .
                    ' participants imported.'
                );

                return;
            }

            // Existing participants remain unchanged
            $this->_json_response(
                'success',
                'Schedule saved successfully.'
            );

            return;

        } catch (Exception $e) {

            Kohana::$log->add(
                Kohana::ERROR,
                $e->getMessage()
            );

            $this->_json_response(
                'error',
                $e->getMessage()
            );

            return;
        }
    }


private function _import_csv_participants($survey_id, $file)
{
    // Get existing participant IDs before importing
    $existing_participants = DB::select('id')
        ->from('survey_participants')
        ->where('survey_id', '=', $survey_id)
        ->execute()
        ->as_array();

    $existing_participant_ids = array();

    foreach ($existing_participants as $participant) {
        $existing_participant_ids[] = $participant['id'];
    }

    $handle = fopen($file['tmp_name'], 'r');

    if (!$handle) {
        throw new Exception('Unable to open CSV file.');
    }

    $new_participants = array();
    $row_number = 0;

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {

        $row_number++;

        // Skip header
        if ($row_number === 1) {
            continue;
        }

        // Skip empty rows
        if (empty($data) || count($data) < 3) {
            continue;
        }

        $first_name = trim($data[0]);
        $last_name = trim($data[1]);
        $email = trim($data[2]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fclose($handle);

            throw new Exception(
                'Invalid email address on CSV row ' . $row_number . '.'
            );
        }

        $new_participants[] = array(
            'survey_id' => $survey_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        );
    }

    fclose($handle);

    if (empty($new_participants)) {
        throw new Exception(
            'The CSV file does not contain any valid participants.'
        );
    }

    /*
     * Insert new participants first.
     */

    foreach ($new_participants as $participant) {

        DB::insert('survey_participants', array(
            'survey_id',
            'first_name',
            'last_name',
            'email',
            'created_at',
            'updated_at'
        ))
            ->values(array(
                $participant['survey_id'],
                $participant['first_name'],
                $participant['last_name'],
                $participant['email'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ))
            ->execute();
    }

    /*
     * New participants inserted successfully.
     * Now delete the old participants.
     */
    if (!empty($existing_participant_ids)) {

        DB::delete('survey_participants')
            ->where('id', 'IN', $existing_participant_ids)
            ->execute();
    }

    return count($new_participants);
}

private function calculate_future_dates($frequency, $start_date_val, $end_date_val)
{
    if (empty($frequency) || empty($start_date_val) || empty($end_date_val))
    {
        return array();
    }

    $start_time = strtotime($start_date_val);
    $schedule_end_time = strtotime($end_date_val);
    $current_datetime = time();

    $future_dates = array();

    // Keep the configured times
    $start_time_of_day = date('H:i:s', $start_time);
    $end_time_of_day = date('H:i:s', $schedule_end_time);

    if ($frequency === 'once')
    {
        // Only show if the schedule has not ended
        if ($schedule_end_time >= $current_datetime)
        {
            $future_dates[] = array(
                'start_date' => date('Y-m-d H:i:s', $start_time),
                'end_date'   => date('Y-m-d H:i:s', $schedule_end_time)
            );
        }

        return $future_dates;
    }

    $current_occurrence = $start_time;

    while (
        $current_occurrence <= $schedule_end_time &&
        count($future_dates) < 50
    )
    {
        $occurrence_date = date(
            'Y-m-d',
            $current_occurrence
        );

        $occurrence_start = strtotime(
            $occurrence_date . ' ' . $start_time_of_day
        );

        $occurrence_end = strtotime(
            $occurrence_date . ' ' . $end_time_of_day
        );

        /*
         * Only include upcoming occurrences.
         *
         * If the occurrence has already ended,
         * skip it.
         */
        if ($occurrence_end >= $current_datetime)
        {
            $future_dates[] = array(
                'start_date' => date(
                    'Y-m-d H:i:s',
                    $occurrence_start
                ),
                'end_date' => date(
                    'Y-m-d H:i:s',
                    $occurrence_end
                )
            );
        }

        switch ($frequency)
        {
            case 'daily':
                $current_occurrence = strtotime(
                    '+1 day',
                    $current_occurrence
                );
                break;

            case 'weekly':
                $current_occurrence = strtotime(
                    '+1 week',
                    $current_occurrence
                );
                break;

            case 'monthly':
                $current_occurrence = strtotime(
                    '+1 month',
                    $current_occurrence
                );
                break;

            case 'quarterly':
                $current_occurrence = strtotime(
                    '+3 months',
                    $current_occurrence
                );
                break;

            default:
                break 2;
        }
    }

    return $future_dates;
}
    
    private function _get_survey_or_404($id)
    {
        if ($id <= 0) {
            throw HTTP_Exception::factory(404, 'Survey not found.');
        }

        $survey = DB::select()->from('surveys')->where('id', '=', $id)->execute()->current();
        if (!$survey) {
            throw HTTP_Exception::factory(404, 'Survey not found.');
        }
        return $survey;
    }

    private function _json_response($status, $message, $http_status = 200)
    {
        $this->auto_render = FALSE;

        $this->response->status($http_status);
        $this->response->headers('Content-Type', 'application/json');

        $this->response->body(json_encode(array(
            'status' => $status,
            'message' => $message
        )));
    }
}
