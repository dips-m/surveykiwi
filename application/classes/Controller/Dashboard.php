<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller_Admin_Template
{
    public function action_index()
    {
        $survey_model = new Model_Survey();

        $surveys = $survey_model->get_dashboard_surveys();

        $counts = $survey_model->get_dashboard_counts(
            $surveys
        );

        $view = View::factory('dashboard/index');

        $view->surveys = $surveys;
        $view->total_surveys = $counts['total'];
        $view->published_surveys = $counts['published'];
        $view->draft_surveys = $counts['draft'];
        $view->closed_surveys = $counts['closed'];

        $this->template->content = $view;
        $this->template->title = 'Dashboard - SurveyKiwi';
    }
}