<?php defined('SYSPATH') or die('No direct script access.');


class Model_Schedule
{
    /**
     * Get the active/latest schedule for a survey.
     *
     * @param integer $survey_id
     *
     * @return array
     */
    public function get_by_survey_id($survey_id)
    {
        return DB::select()
            ->from('survey_schedules')
            ->where('survey_id', '=', (int) $survey_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->execute()
            ->current();
    }


    /**
     * Validate schedule save data.
     *
     * CSV is required only when the survey
     * does not already have participants.
     *
     * @param array      $post
     * @param integer    $survey_id
     * @param array|null $file
     *
     * @return array
     */
    public function validate_save($post, $survey_id, $file = NULL)
    {
        /*
         * Validate required schedule fields.
         */
        $validation = Validation::factory($post);

        $validation
            ->rule('frequency', 'not_empty')
            ->rule('start_date', 'not_empty')
            ->rule('end_date', 'not_empty');

        if (!$validation->check())
        {
            return array(
                'valid' => FALSE,
                'message' => 'Please fill out all required fields.'
            );
        }


        $frequency = $post['frequency'];
        $start_date = $post['start_date'];
        $end_date = $post['end_date'];


        /*
         * Validate frequency.
         */
        $allowed_frequencies = array(
            'once',
            'daily',
            'weekly',
            'monthly',
            'quarterly'
        );

        if (!in_array($frequency, $allowed_frequencies, TRUE))
        {
            return array(
                'valid' => FALSE,
                'message' => 'Invalid schedule frequency.'
            );
        }


        /*
         * Convert dates to timestamps.
         */
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);


        if ($start_time === FALSE || $end_time === FALSE)
        {
            return array(
                'valid' => FALSE,
                'message' => 'Invalid start or end date.'
            );
        }


        /*
         * Start date cannot be in the past.
         */
        if ($start_time < (time() - 60))
        {
            return array(
                'valid' => FALSE,
                'message' => 'Start date and time cannot be in the past.'
            );
        }


        /*
         * End date must be after start date.
         */
        if ($end_time <= $start_time)
        {
            return array(
                'valid' => FALSE,
                'message' => 'End date and time must be greater than start date.'
            );
        }


        /*
         * Check existing participants.
         */
        $participant_model = Model::factory('Participant');

        $existing_participants_count =
        $participant_model->count_by_survey($survey_id);


        /*
         * Check whether a valid CSV file was uploaded.
         */
        $has_file = (
            $file &&
            isset($file['error']) &&
            $file['error'] === UPLOAD_ERR_OK
        );


        /*
         * CSV is required when no participants
         * currently exist for the survey.
         */
        if (
            (int) $existing_participants_count === 0 &&
            !$has_file
        )
        {
            return array(
                'valid' => FALSE,
                'message' => 'Participants are required. Please upload a CSV file.'
            );
        }


        /*
         * Validate CSV extension when uploaded.
         */
        if ($has_file)
        {
            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            if ($extension !== 'csv')
            {
                return array(
                    'valid' => FALSE,
                    'message' => 'Please upload a valid CSV file.'
                );
            }
        }


        return array(
            'valid' => TRUE,
            'message' => ''
        );
    }


    /**
     * Create or update a survey schedule.
     *
     * @param integer $survey_id
     * @param integer $schedule_id
     * @param array   $post
     *
     * @return integer
     */
    public function save($survey_id, $schedule_id, $post)
    {
        $survey_id = (int) $survey_id;
        $schedule_id = (int) $schedule_id;


        $frequency = $post['frequency'];
        $start_date = $post['start_date'];
        $end_date = $post['end_date'];


        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);


        $start_timestamp = date(
            'Y-m-d H:i:s',
            $start_time
        );

        $end_timestamp = date(
            'Y-m-d H:i:s',
            $end_time
        );


        /*
         * Reminders are kept because they already exist
         * in the current schedule table/form.
         */
        $reminders_enabled = isset($post['reminders_enabled'])
            ? 1
            : 0;


        $reminder_interval = isset($post['reminder_interval_days'])
            ? (int) $post['reminder_interval_days']
            : 0;


        $is_active = isset($post['is_active'])
            ? 1
            : 0;


        $now = date('Y-m-d H:i:s');


        /*
         * Check whether the submitted schedule exists
         * for this survey.
         */
        $existing = NULL;


        if ($schedule_id > 0)
        {
            $existing = DB::select('id')
                ->from('survey_schedules')
                ->where('id', '=', $schedule_id)
                ->where('survey_id', '=', $survey_id)
                ->execute()
                ->current();
        }


        /*
         * Update existing schedule.
         */
        if ($existing)
        {
            DB::update('survey_schedules')
                ->set(array(
                    'frequency' => $frequency,
                    'start_date' => $start_timestamp,
                    'end_date' => $end_timestamp,
                    'next_run_at' => $start_timestamp,
                    'reminders_enabled' => $reminders_enabled,
                    'reminder_interval_days' => $reminder_interval,
                    'is_active' => $is_active,
                    'updated_at' => $now
                ))
                ->where('id', '=', $schedule_id)
                ->where('survey_id', '=', $survey_id)
                ->execute();


            return $schedule_id;
        }


        /*
         * Create new schedule.
         */
        list($insert_id, $rows) = DB::insert(
            'survey_schedules',
            array(
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
            )
        )
            ->values(array(
                $survey_id,
                $frequency,
                $start_timestamp,
                $end_timestamp,
                $start_timestamp,
                $reminders_enabled,
                $reminder_interval,
                $is_active,
                $now,
                $now
            ))
            ->execute();


        return $insert_id;
    }


    // /**
    //  * Import participants from CSV.
    //  *
    //  * Existing participants are replaced only
    //  * after the new CSV participants have been
    //  * successfully parsed and inserted.
    //  *
    //  * @param integer $survey_id
    //  * @param array   $file
    //  *
    //  * @return integer
    //  *
    //  * @throws Exception
    //  */
    // public function import_csv_participants($survey_id, $file)
    // {
    //     $survey_id = (int) $survey_id;


    //     /*
    //      * Get existing participant IDs before importing.
    //      */
    //     $existing_participants = DB::select('id')
    //         ->from('survey_participants')
    //         ->where('survey_id', '=', $survey_id)
    //         ->execute()
    //         ->as_array();


    //     $existing_participant_ids = array();


    //     foreach ($existing_participants as $participant)
    //     {
    //         $existing_participant_ids[] = $participant['id'];
    //     }


    //     /*
    //      * Open CSV file.
    //      */
    //     $handle = fopen($file['tmp_name'], 'r');


    //     if (!$handle)
    //     {
    //         throw new Exception(
    //             'Unable to open CSV file.'
    //         );
    //     }


    //     $new_participants = array();
    //     $row_number = 0;


    //     /*
    //      * Read CSV rows.
    //      */
    //     while (
    //         ($data = fgetcsv($handle, 1000, ',')) !== FALSE
    //     )
    //     {
    //         $row_number++;


    //         /*
    //          * Skip header.
    //          */
    //         if ($row_number === 1)
    //         {
    //             continue;
    //         }


    //         /*
    //          * Skip empty/incomplete rows.
    //          */
    //         if (
    //             empty($data) ||
    //             count($data) < 3
    //         )
    //         {
    //             continue;
    //         }


    //         $first_name = trim($data[0]);
    //         $last_name = trim($data[1]);
    //         $email = trim($data[2]);


    //         /*
    //          * Validate email.
    //          */
    //         if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    //         {
    //             fclose($handle);

    //             throw new Exception(
    //                 'Invalid email address on CSV row ' .
    //                 $row_number .
    //                 '.'
    //             );
    //         }


    //         $new_participants[] = array(
    //             'survey_id' => $survey_id,
    //             'first_name' => $first_name,
    //             'last_name' => $last_name,
    //             'email' => $email
    //         );
    //     }


    //     fclose($handle);


    //     /*
    //      * CSV must contain at least one valid participant.
    //      */
    //     if (empty($new_participants))
    //     {
    //         throw new Exception(
    //             'The CSV file does not contain any valid participants.'
    //         );
    //     }


    //     /*
    //      * Insert new participants first.
    //      */
    //     foreach ($new_participants as $participant)
    //     {
    //         DB::insert(
    //             'survey_participants',
    //             array(
    //                 'survey_id',
    //                 'first_name',
    //                 'last_name',
    //                 'email',
    //                 'created_at',
    //                 'updated_at'
    //             )
    //         )
    //             ->values(array(
    //                 $participant['survey_id'],
    //                 $participant['first_name'],
    //                 $participant['last_name'],
    //                 $participant['email'],
    //                 date('Y-m-d H:i:s'),
    //                 date('Y-m-d H:i:s')
    //             ))
    //             ->execute();
    //     }


    //     /*
    //      * Delete old participants only after
    //      * new participants were inserted.
    //      */
    //     if (!empty($existing_participant_ids))
    //     {
    //         DB::delete('survey_participants')
    //             ->where(
    //                 'id',
    //                 'IN',
    //                 $existing_participant_ids
    //             )
    //             ->execute();
    //     }


    //     return count($new_participants);
    // }


    /**
     * Calculate future schedule dates.
     *
     * @param string $frequency
     * @param string $start_date_val
     * @param string $end_date_val
     *
     * @return array
     */
    public function calculate_future_dates(
        $frequency,
        $start_date_val,
        $end_date_val
    )
    {
        if (
            empty($frequency) ||
            empty($start_date_val) ||
            empty($end_date_val)
        )
        {
            return array();
        }


        $start_time = strtotime($start_date_val);
        $schedule_end_time = strtotime($end_date_val);
        $current_datetime = time();


        if (
            $start_time === FALSE ||
            $schedule_end_time === FALSE
        )
        {
            return array();
        }


        $future_dates = array();


        /*
         * Keep the configured times.
         */
        $start_time_of_day = date(
            'H:i:s',
            $start_time
        );

        $end_time_of_day = date(
            'H:i:s',
            $schedule_end_time
        );


        /*
         * Once schedule.
         */
        if ($frequency === 'once')
        {
            if ($schedule_end_time >= $current_datetime)
            {
                $future_dates[] = array(
                    'start_date' => date(
                        'Y-m-d H:i:s',
                        $start_time
                    ),
                    'end_date' => date(
                        'Y-m-d H:i:s',
                        $schedule_end_time
                    )
                );
            }


            return $future_dates;
        }


        $current_occurrence = $start_time;


        /*
         * Limit displayed occurrences to 50,
         * same as the existing implementation.
         */
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
                $occurrence_date .
                ' ' .
                $start_time_of_day
            );


            $occurrence_end = strtotime(
                $occurrence_date .
                ' ' .
                $end_time_of_day
            );


            /*
             * Only include upcoming occurrences.
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


            /*
             * Move to next occurrence.
             */
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
}