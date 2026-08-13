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
    public function validate_save($post, $survey_id, $file = NULL, $schedule_id = 0)
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
        if ((int) $schedule_id <= 0 && $start_time < (time() - 60))
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
                    'reminders_enabled' => $reminders_enabled,
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
                'reminders_enabled',
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
                $is_active,
                $now,
                $now
            ))
            ->execute();


        return $insert_id;
    }

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