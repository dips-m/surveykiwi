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
}