<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Survey extends Controller_Admin_Template
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
     * GET /survey/create
     */
    public function action_create()
    {
         $this->_render_form(Model_Survey::blank(), array(), FALSE, array(), array(), array());
    }
 
    /**
     * GET /survey/edit/<id>
     *
     * Kohana does not autowire route params into action method arguments —
     * Controller::execute() calls action_edit() with no arguments — so the
     * id must be pulled from the request explicitly rather than declared
     * as a method parameter.
     */
    public function action_edit()
    {
        $id = $this->request->param('id');
 
        $survey = Model_Survey::find($id);
 
        if ( ! $survey)
        {
            throw HTTP_Exception::factory(404, 'Survey not found')
                ->request($this->request);
        }

        $schedule_model = Model::factory('Schedule');
        $schedule = $schedule_model->get_by_survey_id($id);

        // Calculate future dates if schedule exists
        $scheduleEntries = array();

        if ($schedule)
        {
            $scheduleEntries = $schedule_model->get_entries_by_schedule_id($schedule['id']);
        }

        $participant_model = Model::factory('Participant');
        $participants = $participant_model->get_by_survey($id);
 
        $questions = Model_SurveyQuestion::find_by_survey($id);
 
        $this->_render_form($survey, $questions, TRUE, $schedule, $scheduleEntries, $participants);
    }
 
    public function action_save_details_and_questions()
    {
        $this->_expect_post();

        $post = $this->request->post();

        if ($error = $this->_csrf_error($post))
        {
            return $this->_json(array('success' => FALSE, 'errors' => array($error)), 400);
        }

        // ---- Validate details ----
        $title       = trim(Arr::get($post, 'title', ''));
        $description = trim(Arr::get($post, 'description', ''));
        $status      = Arr::get($post, 'status', '');

        $errors = array();

        if ($title === '')
        {
            $errors[] = 'Title is required.';
        }

        if ( ! in_array($status, Model_Survey::statuses()))
        {
            $errors[] = 'Invalid status.';
        }

        // ---- Validate questions ----
        $rows          = Arr::get($post, 'questions', array());
        $allowed_types = Model_SurveyQuestion::types();
        $clean         = array();

        foreach ($rows as $i => $row)
        {
            $number   = $i + 1;
            $question = trim(Arr::get($row, 'question', ''));
            $type     = Arr::get($row, 'type', '');
            $options  = Arr::get($row, 'options', array());
            $required = ! empty($row['required']);

            if ($question === '')
            {
                $errors[] = "Question {$number}: text is required.";
                continue;
            }

            if ( ! in_array($type, $allowed_types))
            {
                $errors[] = "Question {$number}: invalid question type.";
                continue;
            }

            if (Model_SurveyQuestion::requires_options($type))
            {
                $option_lines = array_filter(array_map('trim', (array) $options), 'strlen');
                $option_lines = array_values($option_lines);

                if (count($option_lines) < 2)
                {
                    $errors[] = "Question {$number}: provide at least two options.";
                    continue;
                }

                $options = implode(",", $option_lines);
            }
            else
            {
                $options = '';
            }

            $clean[] = array(
                'question' => $question,
                'type'     => $type,
                'options'  => $options,
                'required' => $required,
            );
        }

        if (empty($clean))
        {
            $errors[] = 'Add at least one question.';
        }

        if ( ! empty($errors))
        {
            return $this->_json(array('success' => FALSE, 'errors' => $errors), 422);
        }

        // ---- Save both, single transaction owned here ----
        $id = (int) Arr::get($post, 'id');
        $db = Database::instance();

        $db->begin();

        try
        {
            if ($id AND Model_Survey::exists($id))
            {
                Model_Survey::save_details($id, array(
                    'title'       => $title,
                    'description' => $description,
                    'status'      => $status,
                ));

                $survey_id = $id;
            }
            else
            {
                $survey_id = Model_Survey::create(array(
                    'title'       => $title,
                    'description' => $description,
                    'status'      => $status,
                ));
            }

            // FALSE = don't open a nested transaction, this method already owns one.
            Model_SurveyQuestion::replace_for_survey($survey_id, $clean, FALSE);

            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();

            return $this->_json(array('success' => FALSE, 'errors' => array(
                'Could not save survey. Please try again.',
            )), 500);
        }

        return $this->_json(array(
            'success'   => TRUE,
            'message'   => 'Survey saved.',
            'survey_id' => $survey_id,
        ));
    }
 
    /**
     * POST /survey/save_schedule
     */
    public function action_save_schedule()
    {
        $this->_expect_post();
 
        $post = $this->request->post();
 
        if ($error = $this->_csrf_error($post))
        {
            return $this->_json(array('success' => FALSE, 'errors' => array($error)), 400);
        }
 
        $survey_id = (int) Arr::get($post, 'survey_id');
 
        if ( ! $survey_id OR ! Model_Survey::exists($survey_id))
        {
            return $this->_json(array('success' => FALSE, 'errors' => array(
                'Save the survey details before scheduling it.',
            )), 422);
        }
 
        $starts_at = trim(Arr::get($post, 'starts_at', ''));
        $ends_at   = trim(Arr::get($post, 'ends_at', ''));
 
        if ($starts_at AND $ends_at AND strtotime($ends_at) <= strtotime($starts_at))
        {
            return $this->_json(array('success' => FALSE, 'errors' => array(
                'End date must be after the start date.',
            )), 422);
        }
 
        Model_Survey::save_schedule($survey_id, array(
            'starts_at' => $starts_at,
            'ends_at'   => $ends_at,
        ));
 
        return $this->_json(array('success' => TRUE, 'message' => 'Schedule saved.'));
    }
 
    /**
     * Shared render for the create and edit pages.
     *
     * Assigns into the admin layout's content region rather than writing
     * to $this->response directly, so Controller_Template::after() can do
     * its normal job of rendering $this->template (the layout) around it.
     */
    protected function _render_form(array $survey, array $questions, $is_edit, $schedule, $scheduleEntries, $participants)
    {
        $view = View::factory('survey/form')
            ->set('is_edit', $is_edit)
            ->set('survey', $survey)
            ->set('questions', $questions)
            ->set('question_types', Model_SurveyQuestion::types())
            ->set('csrf_token', Security::token())
            ->set('save_details_and_questions_url', URL::site('survey/save_details_and_questions'))
            ->set('save_schedule_url', URL::site('survey/save_schedule'))
            ->set('schedule', $schedule)
            ->set('scheduleDates', $scheduleEntries)
            ->set('participants', $participants);
 
        // Adjust these two lines if Controller_Admin_Template uses
        // different property names for the page title / content region.
        $this->template->title   = $is_edit ? 'Edit Survey' : 'Create Survey';
        $this->template->content = $view;
    }
 
    protected function _expect_post()
    {
        // Belt-and-suspenders: these are AJAX-only endpoints, so turn the
        // layout off immediately rather than waiting until _json() runs.
        $this->auto_render = FALSE;
 
        if ($this->request->method() !== HTTP_Request::POST)
        {
            throw HTTP_Exception::factory(405, 'Method not allowed')
                ->request($this->request);
        }
    }
 
    /**
     * @return string|NULL error message, or NULL if the token is valid
     */
    protected function _csrf_error($post)
    {
        if ( ! Security::check(Arr::get($post, 'csrf_token')))
        {
            return 'Your session has expired. Please reload the page and try again.';
        }
 
        return NULL;
    }
 
    protected function _json(array $payload, $status = 200)
    {
        // Stop Controller_Template::after() from wrapping this JSON body
        // in the admin layout (which would also throw the same "Undefined
        // variable: content" notice, since $this->template->content is
        // never set on these AJAX-only actions).
        $this->auto_render = FALSE;

        // Kohana's Response::status() validates against Response::$messages,
        // which doesn't include 422 (and other newer codes) by default in
        // older Kohana versions. Register it on first use rather than
        // requiring a bootstrap change.
        if ( ! isset(Response::$messages[$status]))
        {
            Response::$messages[$status] = 'Unprocessable Entity';
        }

        $this->response->status($status);
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($payload));

        return $this->response;
    }
 
    /**
     * Turns Validation errors into plain human-readable strings without
     * requiring a message file (messages/survey.php).
     */
    protected function _flatten_errors(Validation $validation)
    {
        $friendly = array(
            'not_empty'  => 'This field is required.',
            'max_length' => 'This field is too long.',
            'in_array'   => 'This value is not valid.',
        );
 
        $errors = array();
 
        foreach ($validation->errors(NULL, FALSE) as $field => $rules)
        {
            foreach ($rules as $rule => $params)
            {
                $errors[] = ucfirst($field) . ': ' . Arr::get($friendly, $rule, 'Invalid value.');
            }
        }
 
        return $errors;
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