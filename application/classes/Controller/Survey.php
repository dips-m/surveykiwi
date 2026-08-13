<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Survey extends Controller_Template
{
    public $template = 'layout/master';

    /**
     * Survey listing
     */
    public function action_index()
    {
        $page = (int) $this->request->query('page');

        if ($page < 1)
        {
            $page = 1;
        }

        $model = new Model_Survey;

        $result = $model->get_list($page);

        $this->template->content = View::factory('survey/index')
            ->set('surveys', $result['items'])
            ->set('pagination', $result);
    }


    /**
     * View survey details.
     */
    public function action_view()
    {
        $id = (int) $this->request->param('id');

        $model = new Model_Survey;

        $survey = $model->get_by_id($id);

        if ( ! $survey)
        {
            throw HTTP_Exception::factory(
                404,
                'Survey not found.'
            );
        }

        $questions = $model->get_questions($id);

        $schedule = $model->get_active_schedule($id);

        $participant_count = $model->get_participant_count($id);

        $this->template->content = View::factory('survey/view')
            ->set('survey', $survey)
            ->set('questions', $questions)
            ->set('schedule', $schedule)
            ->set('participant_count', $participant_count);
    }

    /**
     * Survey schedule page.
     */
    public function action_schedule()
    {
        $id = (int) $this->request->param('id');

        $model = new Model_Survey;

        $survey = $model->get_by_id($id);

        if ( ! $survey)
        {
            throw HTTP_Exception::factory(
                404,
                'Survey not found.'
            );
        }

        $schedule = $model->get_active_schedule($id);

        $this->template->content = View::factory('survey/schedule')
            ->set('survey', $survey)
            ->set('schedule', $schedule);
    }
}