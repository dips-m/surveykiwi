<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller
{
    public function action_index()
    {
        // Temporary UI data.
        // This will later come from Survey models/database queries.
        $surveys = DB::select(
                's.id',
                's.title',
                's.description',
                's.status',
                array(DB::expr('COUNT(DISTINCT q.id)'), 'questions')
            )
            ->from(array('surveys', 's'))
            ->join(array('survey_questions', 'q'), 'LEFT')
                ->on('q.survey_id', '=', 's.id')
            ->group_by(
                's.id',
                's.title',
                's.description',
                's.status'
            )
            ->order_by('s.id', 'DESC')
            ->execute()
            ->as_array();

        $published = 0;
        $draft = 0;
        $closed = 0;

        foreach ($surveys as $survey)
        {

            switch ($survey['status'])
            {
                case 'published':
                    $published++;
                    break;

                case 'draft':
                    $draft++;
                    break;

                case 'closed':
                    $closed++;
                    break;
            }
        }

        $view = View::factory('dashboard/index');

        $view->surveys = $surveys;
        $view->total_surveys = count($surveys);
        $view->published_surveys = $published;
        $view->draft_surveys = $draft;
        $view->closed_surveys = $closed;

        $this->response->body($view);
    }
}