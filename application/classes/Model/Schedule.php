<?php defined('SYSPATH') or die('No direct script access.');

class Model_Schedule
{
    /**
    * Get the active/latest schedule for a survey.
    * @param integer $survey_id
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
     * Get all active recurrence rules ordered by sort order.
     */
    public function get_recurrence_rules()
    {
        return DB::select()
            ->from('schedule_recurrence_rules')
            ->where('is_active', '=', 1)
            ->order_by('sort_order', 'ASC')
            ->execute()
            ->as_array();
    }


    /**
     * Get generated schedule entries for a schedule.
     */
    public function get_entries_by_schedule_id($schedule_id)
    {
        return DB::select()
            ->from('survey_schedule_entries')
            ->where('survey_schedule_id', '=', (int) $schedule_id)
            ->order_by('start_date', 'ASC')
            ->execute()
            ->as_array();
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
     * @param int $schedule_id
     * @return array
     */
    public function validate_save($post, $survey_id, $file = NULL, $schedule_id = 0)
    {
        // Validate required schedule fields.
        $validation = Validation::factory($post);

        $validation
            ->rule('frequency', 'not_empty')
            ->rule('start_date', 'not_empty')
            ->rule('start_time', 'not_empty')
            ->rule('end_date', 'not_empty')
            ->rule('end_time', 'not_empty');

        if (!$validation->check())
        {
            return array('valid' => FALSE, 'message' => 'Please fill out all required fields.');
        }

        $frequency = isset($post['frequency']) ? trim($post['frequency']) : '';
        $start_date = isset($post['start_date']) ? trim($post['start_date']) : '';
        $start_time = isset($post['start_time']) ? trim($post['start_time']) : '';
        $end_date = isset($post['end_date']) ? trim($post['end_date']) : '';
        $end_time = isset($post['end_time']) ? trim($post['end_time']) : '';

        $recurrence_rule_id = isset($post['recurrence_rule_id']) && $post['recurrence_rule_id'] !== ''
            ? (int) $post['recurrence_rule_id']
            : NULL;

        $weekday = isset($post['weekday']) && $post['weekday'] !== ''
            ? (int) $post['weekday']
            : NULL;

        $month_day = isset($post['month_day']) && $post['month_day'] !== ''
            ? (int) $post['month_day']
            : NULL;

        $quarter_month = isset($post['quarter_month']) && $post['quarter_month'] !== ''
            ? (int) $post['quarter_month']
            : NULL;

        $allowed_frequencies = array('once', 'daily', 'weekly', 'monthly', 'quarterly');

        if (!in_array($frequency, $allowed_frequencies, TRUE))
        {
            return array('valid' => FALSE, 'message' => 'Invalid schedule frequency.');
        }

        $start_date_timestamp = strtotime($start_date);
        $end_date_timestamp = strtotime($end_date);

        if ($start_date_timestamp === FALSE || $end_date_timestamp === FALSE)
        {
            return array('valid' => FALSE, 'message' => 'Invalid start or end date.');
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start_time) ||
            !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end_time))
        {
            return array('valid' => FALSE, 'message' => 'Invalid start or end time.');
        }

        $start_datetime = strtotime($start_date . ' ' . $start_time);
        $end_datetime = strtotime($end_date . ' ' . $end_time);

        if ($start_datetime === FALSE || $end_datetime === FALSE)
        {
            return array('valid' => FALSE, 'message' => 'Invalid schedule date or time.');
        }

        /*
        * Start date/time validation is independent of recurrence.
        *
        * Do NOT validate:
        * - weekly start date against weekday
        * - monthly start date against month_day
        * - quarterly start date against quarter_month/month_day
        */
        if ((int) $schedule_id <= 0 && $start_datetime < (time() - 60))
        {
            return array('valid' => FALSE, 'message' => 'Start date and time cannot be in the past.');
        }

        if ($end_date_timestamp < $start_date_timestamp)
        {
            return array('valid' => FALSE, 'message' => 'End date cannot be before start date.');
        }

        if ($end_time <= $start_time)
        {
            return array('valid' => FALSE, 'message' => 'End time must be later than start time.');
        }

        if ($end_datetime <= $start_datetime)
        {
            return array('valid' => FALSE, 'message' => 'End date and time must be greater than start date and time.');
        }

        /*
        * Recurrence rules:
        *
        * 1 = Every day
        * 2 = Weekdays only
        * 3 = Same day of month
        * 4 = Last day of month
        * 5 = Same day of quarter
        * 6 = Last day of quarter
        *
        * Weekly does NOT use recurrence_rule_id.
        */
        if ($frequency === 'once')
        {
            $recurrence_rule_id = NULL;
            $weekday = NULL;
            $month_day = NULL;
            $quarter_month = NULL;
        }
        elseif ($frequency === 'daily')
        {
            $weekday = NULL;
            $month_day = NULL;
            $quarter_month = NULL;
        }
        elseif ($frequency === 'weekly')
        {
            $recurrence_rule_id = NULL;
            $month_day = NULL;
            $quarter_month = NULL;

            if ($weekday < 1 || $weekday > 7)
            {
                return array('valid' => FALSE, 'message' => 'Please select a valid weekday.');
            }
        }
        elseif ($frequency === 'monthly')
        {
            $weekday = NULL;
            $quarter_month = NULL;

            if ($recurrence_rule_id === 3)
            {
                if ($month_day < 1 || $month_day > 30)
                {
                    return array('valid' => FALSE, 'message' => 'Please select a valid day.');
                }
            }
            else
            {
                $month_day = NULL;
            }
        }
        elseif ($frequency === 'quarterly')
        {
            $weekday = NULL;

            if ($recurrence_rule_id === 5)
            {
                if ($month_day < 1 || $month_day > 30)
                {
                    return array('valid' => FALSE, 'message' => 'Please select a valid day.');
                }

                if ($quarter_month < 1 || $quarter_month > 3)
                {
                    return array('valid' => FALSE, 'message' => 'Please select a valid quarter month.');
                }
            }
            else
            {
                $month_day = NULL;
                $quarter_month = NULL;
            }
        }

        $participant_model = Model::factory('Participant');
        $existing_participants_count = $participant_model->count_by_survey($survey_id);

        $has_file = $file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;

        /*
        * CSV is required when no participants
        * currently exist for the survey.
        */
        if ((int) $existing_participants_count === 0 && !$has_file)
        {
            return array('valid' => FALSE, 'message' => 'Participants are required. Please upload a CSV file.');
        }

        if ($has_file)
        {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            // Validate CSV extension when uploaded
            if ($extension !== 'csv')
            {
                return array('valid' => FALSE, 'message' => 'Please upload a valid CSV file.');
            }
        }

        return array('valid' => TRUE, 'message' => '');
    }

    /**
    * Create or update a survey schedule.
    *
    * @param integer $survey_id
    * @param integer $schedule_id
    * @param array   $post
    * @return integer
    **/
    public function save($survey_id, $schedule_id, $post)
    {
        $survey_id = (int) $survey_id;
        $schedule_id = (int) $schedule_id;

        $frequency = $post['frequency'];
        $start_date = $post['start_date'];
        $start_time = $post['start_time'];
        $end_date = $post['end_date'];
        $end_time = $post['end_time'];

        $recurrence_rule_id = isset($post['recurrence_rule_id']) && $post['recurrence_rule_id'] !== ''
            ? (int) $post['recurrence_rule_id']
            : NULL;

        $weekday = isset($post['weekday']) && $post['weekday'] !== ''
            ? (int) $post['weekday']
            : NULL;

        $month_day = isset($post['month_day']) && $post['month_day'] !== ''
            ? (int) $post['month_day']
            : NULL;

        $quarter_month = isset($post['quarter_month']) && $post['quarter_month'] !== ''
            ? (int) $post['quarter_month']
            : NULL;

        /*
         * Normalize recurrence fields before saving.
         */
        if ($frequency === 'once')
        {
            $recurrence_rule_id = NULL;
            $weekday = NULL;
            $month_day = NULL;
            $quarter_month = NULL;
        }
        elseif ($frequency === 'daily')
        {
            $weekday = NULL;
            $month_day = NULL;
            $quarter_month = NULL;
        }
        elseif ($frequency === 'weekly')
        {
            $recurrence_rule_id = NULL;
            $month_day = NULL;
            $quarter_month = NULL;
        }
        elseif ($frequency === 'monthly')
        {
            $weekday = NULL;
            $quarter_month = NULL;

            if ($recurrence_rule_id === 4)
            {
                $month_day = NULL;
            }
        }
        
        $reminders_enabled = isset($post['reminders_enabled']) ? 1 : 0;
        $is_active = isset($post['is_active']) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

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

        DB::query(NULL, 'START TRANSACTION')->execute();

        try
        {
            $data = array(
                'frequency' => $frequency,
                'start_date' => date('Y-m-d', strtotime($start_date)),
                'start_time' => date('H:i:s', strtotime($start_time)),
                'end_date' => date('Y-m-d', strtotime($end_date)),
                'end_time' => date('H:i:s', strtotime($end_time)),
                'recurrence_rule_id' => $recurrence_rule_id,
                'weekday' => $weekday,
                'month_day' => $month_day,
                'quarter_month' => $quarter_month,
                'reminders_enabled' => $reminders_enabled,
                'is_active' => $is_active,
                'updated_at' => $now
            );

            //  Update existing schedule.
            if ($existing)
            {
                DB::update('survey_schedules')
                    ->set($data)
                    ->where('id', '=', $schedule_id)
                    ->where('survey_id', '=', $survey_id)
                    ->execute();
            }
            else
            {
                // Create new schedule.
                $data['survey_id'] = $survey_id;
                $data['created_at'] = $now;

                list($insert_id, $rows) = DB::insert('survey_schedules', array_keys($data))
                    ->values(array_values($data))
                    ->execute();

                $schedule_id = (int) $insert_id;
            }

            // Remove old generated entries.
            DB::delete('survey_schedule_entries')
                ->where('survey_schedule_id', '=', $schedule_id)
                ->execute();

            $entries = $this->generate_schedule_entries(
                $frequency,
                $start_date,
                $start_time,
                $end_date,
                $end_time,
                $recurrence_rule_id,
                $weekday,
                $month_day,
                $quarter_month
            );

            // Create new schedule entries.
            foreach ($entries as $entry)
            {
                DB::insert('survey_schedule_entries', array(
                    'survey_schedule_id',
                    'start_date',
                    'end_date',
                    'status',
                    'creator_email_sent',
                    'created_at',
                    'updated_at'
                ))
                    ->values(array(
                        $schedule_id,
                        $entry['start_date'],
                        $entry['end_date'],
                        $entry['status'],
                        0,
                        $now,
                        $now
                    ))
                    ->execute();
            }

            return $schedule_id;
        
        }
        catch (Exception $e)
        {
            DB::query(NULL, 'ROLLBACK')->execute();

            Kohana::$log->add(Log::ERROR, 'Schedule save failed: :error', array(
                ':error' => $e->getMessage()
            ));

            return array(
                'success' => FALSE,
                'message' => 'Unable to save the schedule. Please try again.'
            );
        }
    }

    /**
     * Generate schedule entries based on the configured recurrence rule.
     */
    public function generate_schedule_entries($frequency, $start_date, $start_time, $end_date, $end_time, $recurrence_rule_id = NULL, $weekday = NULL, $month_day = NULL, $quarter_month = NULL)
    {
        $entries = array();
        $now = time();

        $start_timestamp = strtotime($start_date . ' ' . $start_time);
        $end_timestamp = strtotime($end_date . ' ' . $end_time);

        if ($start_timestamp === FALSE || $end_timestamp === FALSE)
        {
            return $entries;
        }

        $start_date_timestamp = strtotime($start_date);
        $end_date_timestamp = strtotime($end_date);

        $start_time_value = date('H:i:s', strtotime($start_time));
        $end_time_value = date('H:i:s', strtotime($end_time));

        /*
        * ONCE
        */
        if ($frequency === 'once')
        {
            $entries[] = array(
                'start_date' => date('Y-m-d H:i:s', $start_timestamp),
                'end_date' => date('Y-m-d H:i:s', $end_timestamp),
                'status' => $end_timestamp < $now ? 'past' : 'future'
            );

            return $entries;
        }

        /*
        * DAILY
        */
        if ($frequency === 'daily')
        {
            $current_date = $start_date_timestamp;

            while ($current_date <= $end_date_timestamp && count($entries) < 1000)
            {
                $valid = FALSE;

                if ((int) $recurrence_rule_id === 1)
                {
                    $valid = TRUE;
                }
                elseif ((int) $recurrence_rule_id === 2)
                {
                    $day_of_week = (int) date('N', $current_date);

                    if ($day_of_week >= 1 && $day_of_week <= 5)
                    {
                        $valid = TRUE;
                    }
                }

                if ($valid)
                {
                    $occurrence_date = date('Y-m-d', $current_date);
                    $occurrence_start = strtotime($occurrence_date . ' ' . $start_time_value);
                    $occurrence_end = strtotime($occurrence_date . ' ' . $end_time_value);

                    if ($occurrence_start >= $start_timestamp && $occurrence_end <= $end_timestamp)
                    {
                        $entries[] = array(
                            'start_date' => date('Y-m-d H:i:s', $occurrence_start),
                            'end_date' => date('Y-m-d H:i:s', $occurrence_end),
                            'status' => $occurrence_end < $now ? 'past' : 'future'
                        );
                    }
                }

                $current_date = strtotime('+1 day', $current_date);
            }

            return $entries;
        }

        /*
        * WEEKLY
        *
        * Start date is only the boundary.
        * First occurrence is the selected weekday ON or AFTER start_date.
        */
        if ($frequency === 'weekly')
        {
            $current_date = $start_date_timestamp;

            while ($current_date <= $end_date_timestamp && count($entries) < 1000)
            {
                if ((int) date('N', $current_date) === (int) $weekday)
                {
                    $occurrence_date = date('Y-m-d', $current_date);
                    $occurrence_start = strtotime($occurrence_date . ' ' . $start_time_value);
                    $occurrence_end = strtotime($occurrence_date . ' ' . $end_time_value);

                    if ($occurrence_start >= $start_timestamp && $occurrence_end <= $end_timestamp)
                    {
                        $entries[] = array(
                            'start_date' => date('Y-m-d H:i:s', $occurrence_start),
                            'end_date' => date('Y-m-d H:i:s', $occurrence_end),
                            'status' => $occurrence_end < $now ? 'past' : 'future'
                        );
                    }
                }

                $current_date = strtotime('+1 day', $current_date);
            }

            return $entries;
        }

        /*
        * MONTHLY
        */
        if ($frequency === 'monthly')
        {
            $current_date = strtotime(date('Y-m-01', $start_date_timestamp));

            while ($current_date <= $end_date_timestamp && count($entries) < 1000)
            {
                $year = (int) date('Y', $current_date);
                $month = (int) date('n', $current_date);
                $days_in_month = (int) date('t', $current_date);

                $occurrence_date = NULL;

                /*
                * Rule 3 = Same day of month
                */
                if ((int) $recurrence_rule_id === 3)
                {
                    $day = min((int) $month_day, $days_in_month);

                    $occurrence_date = sprintf(
                        '%04d-%02d-%02d',
                        $year,
                        $month,
                        $day
                    );
                }

                /*
                * Rule 4 = Last day of month
                */
                elseif ((int) $recurrence_rule_id === 4)
                {
                    $occurrence_date = sprintf(
                        '%04d-%02d-%02d',
                        $year,
                        $month,
                        $days_in_month
                    );
                }

                if ($occurrence_date !== NULL)
                {
                    $occurrence_start = strtotime($occurrence_date . ' ' . $start_time_value);
                    $occurrence_end = strtotime($occurrence_date . ' ' . $end_time_value);

                    if ($occurrence_start >= $start_timestamp && $occurrence_end <= $end_timestamp)
                    {
                        $entries[] = array(
                            'start_date' => date('Y-m-d H:i:s', $occurrence_start),
                            'end_date' => date('Y-m-d H:i:s', $occurrence_end),
                            'status' => $occurrence_end < $now ? 'past' : 'future'
                        );
                    }
                }

                $next_month = strtotime('+1 month', $current_date);
                $current_date = strtotime(date('Y-m-01', $next_month));
            }

            return $entries;
        }

        /*
        * QUARTERLY
        */
        if ($frequency === 'quarterly')
        {
            $start_month = (int) date('n', $start_date_timestamp);
            $start_year = (int) date('Y', $start_date_timestamp);

            /*
            * Find the first month of the quarter containing start_date.
            */
            $quarter_start_month = (int) (floor(($start_month - 1) / 3) * 3 + 1);

            $current_date = strtotime(
                sprintf('%04d-%02d-01', $start_year, $quarter_start_month)
            );

            while ($current_date <= $end_date_timestamp && count($entries) < 1000)
            {
                $year = (int) date('Y', $current_date);
                $quarter_start_month = (int) date('n', $current_date);

                $occurrence_date = NULL;

                /*
                * Rule 5 = Same day of quarter
                */
                if ((int) $recurrence_rule_id === 5)
                {
                    /*
                    * 1 = Jan/Apr/Jul/Oct
                    * 2 = Feb/May/Aug/Nov
                    * 3 = Mar/Jun/Sep/Dec
                    */
                    $target_month = $quarter_start_month + ((int) $quarter_month - 1);

                    if ($target_month >= 1 && $target_month <= 12)
                    {
                        $target_month_timestamp = strtotime(
                            sprintf('%04d-%02d-01', $year, $target_month)
                        );

                        $days_in_month = (int) date('t', $target_month_timestamp);
                        $day = min((int) $month_day, $days_in_month);

                        $occurrence_date = sprintf(
                            '%04d-%02d-%02d',
                            $year,
                            $target_month,
                            $day
                        );
                    }
                }

                /*
                * Rule 6 = Last day of quarter
                */
                elseif ((int) $recurrence_rule_id === 6)
                {
                    $quarter_end_month = $quarter_start_month + 2;

                    $quarter_end_timestamp = strtotime(
                        sprintf('%04d-%02d-01', $year, $quarter_end_month)
                    );

                    $days_in_month = (int) date('t', $quarter_end_timestamp);

                    $occurrence_date = sprintf(
                        '%04d-%02d-%02d',
                        $year,
                        $quarter_end_month,
                        $days_in_month
                    );
                }

                if ($occurrence_date !== NULL)
                {
                    $occurrence_start = strtotime($occurrence_date . ' ' . $start_time_value);
                    $occurrence_end = strtotime($occurrence_date . ' ' . $end_time_value);

                    if ($occurrence_start >= $start_timestamp && $occurrence_end <= $end_timestamp)
                    {
                        $entries[] = array(
                            'start_date' => date('Y-m-d H:i:s', $occurrence_start),
                            'end_date' => date('Y-m-d H:i:s', $occurrence_end),
                            'status' => $occurrence_end < $now ? 'past' : 'future'
                        );
                    }
                }

                /*
                * Move to next quarter.
                */
                $next_quarter = strtotime('+3 months', $current_date);
                $current_date = strtotime(date('Y-m-01', $next_quarter));
            }

            return $entries;
        }

        return $entries;
    }
}