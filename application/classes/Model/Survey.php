<?php defined('SYSPATH') OR die('No direct script access.');


class Model_Survey
{
    /**
     * Number of surveys displayed per page.
     */
    const ITEMS_PER_PAGE = 10;

    /**
     * Get surveys for dashboard.
     *
     * @return array
     */
    public function get_dashboard_surveys()
    {
        return DB::select(
                's.id',
                's.title',
                's.description',
                's.status',
                array(
                    DB::expr(
                        'COUNT(DISTINCT q.id)'
                    ),
                    'questions'
                )
            )
            ->from(
                array('surveys', 's')
            )
            ->join(
                array('survey_questions', 'q'),
                'LEFT'
            )
            ->on(
                'q.survey_id',
                '=',
                's.id'
            )
            ->group_by(
                's.id',
                's.title',
                's.description',
                's.status'
            )
            ->order_by(
                's.id',
                'DESC'
            )
            ->limit(5)
            ->execute()
            ->as_array();
    }


    /**
     * Get surveys for the dashboard.
     *
     * @return array
     */
    public function get_dashboard_counts()
    {
        return DB::select(
                array(
                    DB::expr(
                        "COUNT(*)"
                    ),
                    'total'
                ),
                array(
                    DB::expr(
                        "SUM(status = 'published')"
                    ),
                    'published'
                ),
                array(
                    DB::expr(
                        "SUM(status = 'draft')"
                    ),
                    'draft'
                ),
                array(
                    DB::expr(
                        "SUM(status = 'closed')"
                    ),
                    'closed'
                )
            )
            ->from('surveys')
            ->execute()
            ->current();
    }


    /**
     * Get survey list with pagination.
     *
     * @param integer $page
     *
     * @return array
     */
    public function get_list($page = 1)
    {
        $page = max(1, (int) $page);

        $total = $this->count();

        $total_pages = (int) ceil(
            $total / self::ITEMS_PER_PAGE
        );

        if ($total_pages > 0 && $page > $total_pages)
        {
            $page = $total_pages;
        }

        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        $surveys = DB::select(
                's.id',
                's.title',
                's.description',
                's.status',
                's.created_at',

                array(
                    DB::expr(
                        '(SELECT COUNT(*)
                          FROM survey_questions q
                          WHERE q.survey_id = s.id)'
                    ),
                    'questions'
                ),

                array(
                    DB::expr(
                        '(SELECT COUNT(*)
                          FROM survey_participants p
                          WHERE p.survey_id = s.id)'
                    ),
                    'participants'
                ),

                array(
                    DB::expr(
                        '(SELECT sc.frequency
                          FROM survey_schedules sc
                          WHERE sc.survey_id = s.id
                          AND sc.is_active = 1
                          ORDER BY sc.id DESC
                          LIMIT 1)'
                    ),
                    'frequency'
                )
            )
            ->from(array('surveys', 's'))
            ->order_by('s.created_at', 'DESC')
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->execute()
            ->as_array();

        return array(
            'items'        => $surveys,
            'current_page' => $page,
            'per_page'     => self::ITEMS_PER_PAGE,
            'total'        => $total,
            'total_pages'  => $total_pages,
        );
    }


    /**
     * Count all surveys.
     *
     * @return integer
     */
    public function count()
    {
        return (int) DB::select(
                array(DB::expr('COUNT(*)'), 'total')
            )
            ->from('surveys')
            ->execute()
            ->get('total');
    }


    /**
     * Get a survey by ID.
     *
     * @param integer $id
     *
     * @return array
     */
    public function get_by_id($id)
    {
        return DB::select()
            ->from('surveys')
            ->where('id', '=', (int) $id)
            ->execute()
            ->current();
    }


    /**
     * Get survey questions.
     *
     * @param integer $survey_id
     *
     * @return array
     */
    public function get_questions($survey_id)
    {
        return DB::select()
            ->from('survey_questions')
            ->where('survey_id', '=', (int) $survey_id)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->execute()
            ->as_array();
    }


    /**
     * Get the active schedule for a survey.
     *
     * @param integer $survey_id
     *
     * @return array
     */
    public function get_active_schedule($survey_id)
    {
        return DB::select()
            ->from('survey_schedules')
            ->where('survey_id', '=', (int) $survey_id)
            ->where('is_active', '=', 1)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->execute()
            ->current();
    }


    /**
     * Get participant count for a survey.
     *
     * @param integer $survey_id
     *
     * @return integer
     */
    public function get_participant_count($survey_id)
    {
        return (int) DB::select(
                array(DB::expr('COUNT(*)'), 'total')
            )
            ->from('survey_participants')
            ->where('survey_id', '=', (int) $survey_id)
            ->execute()
            ->get('total');
    }


    /**
     * Get dashboard survey status counts.
     *
     * @param array $surveys
     *
     * @return array
     */
    public function get_status_counts(array $surveys)
    {
        $counts = array(
            'total'     => count($surveys),
            'published' => 0,
            'draft'     => 0,
            'closed'    => 0,
        );

        foreach ($surveys as $survey)
        {
            $status = $survey['status'];

            if (isset($counts[$status]))
            {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    /**
     * Get active survey schedules starting within
     * the next one hour.
     *
     * @return array
     */
    public function get_upcoming_schedules()
    {
        return DB::select(
            'sse.id',
            'sse.survey_schedule_id',
            'sse.start_date',
            'sse.end_date',
            'ss.survey_id',
            's.title'
        )
        ->from(
            array('survey_schedule_entries', 'sse')
        )
        ->join(
            array('survey_schedules', 'ss'),
            'INNER'
        )
        ->on(
            'ss.id',
            '=',
            'sse.survey_schedule_id'
        )
        ->join(
            array('surveys', 's'),
            'INNER'
        )
        ->on(
            's.id',
            '=',
            'ss.survey_id'
        )
        ->where(
            'ss.is_active',
            '=',
            1
        )
        ->where(
            'sse.status',
            '=',
            'future'
        )
        ->where(
            'sse.start_date',
            '>=',
            DB::expr('NOW()')
        )
        ->where(
            'sse.start_date',
            '<=',
            DB::expr(
                'DATE_ADD(NOW(), INTERVAL 1 HOUR)'
            )
        )
        ->where(
            's.status',
            '=',
            'published'
        )
        ->order_by(
            'sse.start_date',
            'ASC'
        )
        ->execute()
        ->as_array();
    }

    /**
     * Get participants for a survey.
     *
     * @param integer $survey_id
     * @return array
     */
    public function get_participants($survey_id)
    {
        return DB::select(
            'id',
            'first_name',
            'last_name',
            'email'
        )
        ->from('survey_participants')
        ->where(
            'survey_id',
            '=',
            (int) $survey_id
        )
        ->execute()
        ->as_array();
    }

    /**
     * Create schedule log.
     *
     * Initially created as failed and changed to success
     * only when all participant emails are sent successfully.
     *
     * @param integer $survey_id
     * @param string $executed_at
     * @param string $status
     * @return integer
     */
    public function create_schedule_log($survey_id, $executed_at, $status)
    {
        list($id) = DB::insert('survey_schedule_logs')
            ->columns(array(
                'survey_id',
                'executed_at',
                'status',
            ))
            ->values(array(
                (int) $survey_id,
                $executed_at,
                $status,
            ))
            ->execute();

        return (int) $id;
    }


    /**
     * Update schedule log status.
     *
     * @param integer $id
     * @param string $status
     * @return void
     */
    public function update_schedule_log_status($id, $status)
    {
        DB::update('survey_schedule_logs')
            ->set(array(
                'status' => $status,
            ))
            ->where('id', '=', (int) $id)
            ->execute();
    }


    /**
     * Create invitation record.
     *
     * @param integer $schedule_log_id
     * @param integer $participant_id
     * @param string $status
     * @return integer
     */
    public function create_invitation($schedule_entry_id, $participant_id, $status)
    {
        return DB::insert(
            'survey_invitations',
            array(
                'survey_schedule_entry_id',
                'survey_participant_id',
                'status',
                'sent_at',
                'created_at',
                'updated_at'
            )
        )
        ->values(
            array(
                (int) $schedule_entry_id,
                (int) $participant_id,
                $status,
                $status === 'sent'
                    ? date('Y-m-d H:i:s')
                    : NULL,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            )
        )
        ->execute();
    }

    public function update_invitation_status($schedule_entry_id, $participant_id, $status)
    {
        return DB::update('survey_invitations')
            ->set(
                array(
                    'status' => $status,
                    'sent_at' => $status === 'sent'
                        ? date('Y-m-d H:i:s')
                        : NULL,
                    'updated_at' => date('Y-m-d H:i:s')
                )
            )
            ->where(
                'survey_schedule_entry_id',
                '=',
                (int) $schedule_entry_id
            )
            ->where(
                'survey_participant_id',
                '=',
                (int) $participant_id
            )
            ->execute();
    }

    public function save_invitation($schedule_entry_id, $participant_id, $status)
    {
        $existing = DB::select(
            'id'
        )
        ->from('survey_invitations')
        ->where(
            'survey_schedule_entry_id',
            '=',
            (int) $schedule_entry_id
        )
        ->where(
            'survey_participant_id',
            '=',
            (int) $participant_id
        )
        ->limit(1)
        ->execute()
        ->current();
    
        if ($existing)
        {
            return $this->update_invitation_status(
                $schedule_entry_id,
                $participant_id,
                $status
            );
        }
    
        return $this->create_invitation(
            $schedule_entry_id,
            $participant_id,
            $status
        );
    }

    /**
     * Get the schedule log for a specific survey occurrence.
     *
     * The combination of survey_id + next_run_at represents
     * one execution of a recurring survey.
     *
     * @param int    $survey_id
     * @param string $next_run_at
     *
     * @return array|null
     */
    public function get_schedule_log($survey_id, $next_run_at)
    {
        return DB::select()
            ->from('survey_schedule_logs')
            ->where('survey_id', '=', (int) $survey_id)
            ->where('executed_at', '=', $next_run_at)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->execute()
            ->current();
    }

    /**
     * Get participants for a schedule occurrence along with
     * their latest invitation status.
     *
     * @param int $survey_id
     * @param int $schedule_log_id
     *
     * @return array
     */
    public function get_participants_with_invitation_status($survey_id, $schedule_log_id)
    {
        $participants = DB::select(
                'id',
                'first_name',
                'last_name',
                'email'
            )
            ->from('survey_participants')
            ->where('survey_id', '=', (int) $survey_id)
            ->order_by('id', 'ASC')
            ->execute()
            ->as_array();

        if (empty($participants))
        {
            return array();
        }

        /*
        * Get all invitation records for this schedule.
        */
        $invitations = DB::select(
                'id',
                'survey_participant_id',
                'status'
            )
            ->from('survey_invitations')
            ->where(
                'survey_schedule_log_id',
                '=',
                (int) $schedule_log_id
            )
            ->order_by('id', 'DESC')
            ->execute()
            ->as_array();

        /*
        * Keep only the latest invitation for each participant.
        */
        $latest_status = array();

        foreach ($invitations as $invitation)
        {
            $participant_id =
                (int) $invitation['survey_participant_id'];

            if ( ! isset($latest_status[$participant_id]))
            {
                $latest_status[$participant_id] =
                    $invitation['status'];
            }
        }

        /*
        * Add latest invitation status to each participant.
        */
        foreach ($participants as &$participant)
        {
            $participant_id = (int) $participant['id'];

            if (isset($latest_status[$participant_id]))
            {
                $participant['invitation_status'] =
                    $latest_status[$participant_id];
            }
            else
            {
                $participant['invitation_status'] = NULL;
            }
        }

        unset($participant);

        return $participants;
    }

    /**
     * Get participants who need an email.
     *
     * A participant needs an email when:
     *
     * - No invitation exists yet, OR
     * - The latest invitation was bounced.
     *
     * Participants whose latest invitation is sent are skipped.
     *
     * @param int $survey_id
     * @param int $schedule_log_id
     *
     * @return array
     */
    public function get_pending_participants($schedule_entry_id, $survey_id)
    {
        return DB::query(
            Database::SELECT,
            '
            SELECT
                sp.id,
                sp.first_name,
                sp.last_name,
                sp.email
    
            FROM survey_participants sp
    
            LEFT JOIN survey_invitations si
                ON si.survey_participant_id = sp.id
                AND si.survey_schedule_entry_id = :schedule_entry_id
    
            WHERE sp.survey_id = :survey_id
    
            AND (
                si.id IS NULL
                OR si.status = "fail"
            )
            '
        )
        ->param(
            ':schedule_entry_id',
            (int) $schedule_entry_id
        )
        ->param(
            ':survey_id',
            (int) $survey_id
        )
        ->execute()
        ->as_array();
    }

    /**
     * Get schedule entries requiring creator notification.
     *
     * @return array
     */
    public function get_creator_notification_entries()
    {
        return DB::select(
                'sse.id as schedule_id',
                'sse.survey_schedule_id',
                'sse.start_date',
                'sse.end_date',
                's.id',
                's.title',
                's.description'
            )
            ->from(
                array('survey_schedule_entries', 'sse')
            )
            ->join(
                array('survey_schedules', 'ss'),
                'INNER'
            )
            ->on(
                'ss.id',
                '=',
                'sse.survey_schedule_id'
            )
            ->join(
                array('surveys', 's'),
                'INNER'
            )
            ->on(
                's.id',
                '=',
                'ss.survey_id'
            )
            ->where(
                'ss.is_active',
                '=',
                1
            )
            ->where(
                's.status',
                '=',
                'published'
            )
            ->where(
                'sse.creator_email_sent',
                '=',
                0
            )
            ->where(
                'sse.start_date',
                '>=',
                DB::expr('NOW()')
            )
            ->where(
                'sse.start_date',
                '<=',
                DB::expr(
                    'DATE_ADD(NOW(), INTERVAL 1 HOUR)'
                )
            )
            ->order_by(
                'sse.start_date',
                'ASC'
            )
            ->execute()
            ->as_array();
    }

    /**
     * Mark creator notification as sent.
     *
     * @param int $entry_id
     * @return void
     */
    public function mark_creator_email_sent($entry_id)
    {
        DB::update('survey_schedule_entries')
            ->set(
                array(
                    'creator_email_sent' => 1,
                    'updated_at' => DB::expr('NOW()')
                )
            )
            ->where(
                'id',
                '=',
                (int) $entry_id
            )
            ->execute();
    }
}