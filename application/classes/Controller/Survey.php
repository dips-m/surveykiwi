<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Survey extends Controller_Template
{
    public $template = 'layout/master';

    /**
     * Survey listing
     */
    public function action_index()
    {
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
                        "(SELECT frequency
                          FROM survey_schedules sc
                          WHERE sc.survey_id = s.id
                          AND sc.is_active = 1
                          ORDER BY sc.id DESC
                          LIMIT 1)"
                    ),
                    'frequency'
                )
            )
            ->from(array('surveys', 's'))
            ->order_by('s.created_at', 'DESC')
            ->execute()
            ->as_array();

        $this->template->content = View::factory('survey/index')
            ->set('surveys', $surveys);
    }


    /**
     * Survey schedule page
     */
    public function action_schedule()
    {
        $id = (int) $this->request->param('id');

        if ($id <= 0)
        {
            throw HTTP_Exception::factory(
                404,
                'Survey not found.'
            );
        }

        $survey = DB::select()
            ->from('surveys')
            ->where('id', '=', $id)
            ->execute()
            ->current();

        if ( ! $survey)
        {
            throw HTTP_Exception::factory(
                404,
                'Survey not found.'
            );
        }

        $this->template->content = View::factory('survey/schedule')
            ->set('survey', $survey);
    }
}