<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Survey extends Controller_Template
{
    public $template = 'layout/master';

    /**
     * Survey listing.
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

        $this->template->content =
            View::factory('survey/index')
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
            return $this->_error_page(
                'Survey Not Found',
                'Sorry, the survey you are looking for '
                . 'does not exist or is no longer available.',
                404
            );
        }

        $questions =
            $model->get_questions($id);

        $schedule =
            $model->get_active_schedule($id);

        $participant_count =
            $model->get_participant_count($id);

        $this->template->content =
            View::factory('survey/view')
                ->set('survey', $survey)
                ->set('questions', $questions)
                ->set('schedule', $schedule)
                ->set(
                    'participant_count',
                    $participant_count
                );
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
            return $this->_error_page(
                'Survey Not Found',
                'Sorry, the survey you are looking for '
                . 'does not exist or is no longer available.',
                404
            );
        }

        $schedule =
            $model->get_active_schedule($id);

        $this->template->content =
            View::factory('survey/schedule')
                ->set('survey', $survey)
                ->set('schedule', $schedule);
    }

    /**
     * Display common error page.
     *
     * @param string  $title
     * @param string  $message
     * @param integer $status
     * @return void
     */
    protected function _error_page(
        $title,
        $message,
        $status = 404
    )
    {
        $this->response->status(
            (int) $status
        );

        $this->template->content =
            View::factory('errors/message')
                ->set('title', $title)
                ->set('message', $message);
    }
}