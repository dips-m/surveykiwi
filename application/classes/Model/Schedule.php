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
     *
     * @return array
     */
    public function validate_save($post, $survey_id, $schedule_id = 0)
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

        $start_timestamp = date('Y-m-d H:i:s', $start_time);
        $end_timestamp = date('Y-m-d H:i:s', $end_time);

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
        }
        else
        {
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
                    $reminders_enabled,
                    $is_active,
                    $now,
                    $now
                ))
                ->execute();

            /*
            * IMPORTANT:
            * Use the newly created schedule ID.
            */
            $schedule_id = (int) $insert_id;
        }

        /*
        * Remove old generated entries.
        */
        DB::delete('survey_schedule_entries')
            ->where('survey_schedule_id', '=', $schedule_id)
            ->execute();

        /*
        * Generate new entries.
        */
        $entries = $this->generate_schedule_entries(
            $frequency,
            $start_time,
            $end_time
        );

        /*
        * Store generated entries.
        */
        foreach ($entries as $entry)
        {
            DB::insert(
                'survey_schedule_entries',
                array(
                    'survey_schedule_id',
                    'start_date',
                    'end_date',
                    'status',
                    'creator_email_sent',
                    'created_at',
                    'updated_at'
                )
            )
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

    public function generate_schedule_entries($frequency, $start_time, $end_time)
    {
        $entries = array();
        $now = time();

        if ($frequency === 'once')
        {
            $entries[] = array(
                'start_date' => date('Y-m-d H:i:s', $start_time),
                'end_date' => date('Y-m-d H:i:s', $end_time),
                'status' => $end_time < $now ? 'past' : 'future'
            );

            return $entries;
        }

        $start_time_of_day = date('H:i:s', $start_time);
        $end_time_of_day = date('H:i:s', $end_time);
        $current_occurrence = $start_time;

        while ($current_occurrence <= $end_time && count($entries) < 1000)
        {
            $occurrence_date = date('Y-m-d', $current_occurrence);

            $occurrence_start = strtotime(
                $occurrence_date . ' ' . $start_time_of_day
            );

            $occurrence_end = strtotime(
                $occurrence_date . ' ' . $end_time_of_day
            );

            if ($occurrence_end > $end_time)
            {
                break;
            }

            $entries[] = array(
                'start_date' => date('Y-m-d H:i:s', $occurrence_start),
                'end_date' => date('Y-m-d H:i:s', $occurrence_end),
                'status' => $occurrence_end < $now ? 'past' : 'future'
            );

            switch ($frequency)
            {
                case 'daily':
                    $current_occurrence = strtotime('+1 day', $current_occurrence);
                    break;

                case 'weekly':
                    $current_occurrence = strtotime('+1 week', $current_occurrence);
                    break;

                case 'monthly':
                    $current_occurrence = strtotime('+1 month', $current_occurrence);
                    break;

                case 'quarterly':
                    $current_occurrence = strtotime('+3 months', $current_occurrence);
                    break;

                default:
                    break 2;
            }
        }

        return $entries;
    }
}