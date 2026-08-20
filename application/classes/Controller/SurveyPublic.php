<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_SurveyPublic extends Controller
{
    /**
     * Public survey page.
     *
     * Accessible with or without login.
     */
    public function action_public()
{
    $survey_id = (int) $this->request->param('id');

    if ($survey_id <= 0)
    {
        throw HTTP_Exception_404::factory(
            'Survey not found.'
        );
    }

    $survey_model = new Model_Survey();

    /*
     * Only published surveys are publicly accessible.
     */
    $survey = $survey_model->get_published_survey(
        $survey_id
    );

    if ( ! $survey)
    {
        throw HTTP_Exception_404::factory(
            'Survey not found.'
        );
    }

    /*
     * Get questions configured for this survey.
     */
    $questions = $survey_model->get_survey_questions(
        $survey_id
    );

    /*
     * Options are currently stored as a space-separated
     * string in the DB (not JSON). Split on whitespace
     * and drop any empty entries.
     */
    foreach ($questions as &$question)
    {
        if ( ! empty($question['options']))
        {
            $parts = preg_split(
                '/\s+/',
                trim($question['options'])
            );

            $question['options'] = array_values(
                array_filter($parts, 'strlen')
            );
        }
        else
        {
            $question['options'] = array();
        }
    }
    unset($question);

    $view = View::factory('survey/public');

    $view->survey = $survey;
    $view->questions = $questions;

    $this->response->body($view);
}
}