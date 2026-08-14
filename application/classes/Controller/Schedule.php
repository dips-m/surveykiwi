<?php defined('SYSPATH') or die('No direct script access.');


class Controller_Schedule extends Controller_Template
{
    public $template = 'layout/master';


    public function action_index()
    {
        $id = (int) $this->request->param('id');

        $survey = $this->_get_survey_or_404($id);

        $schedule_model = Model::factory('Schedule');
        $schedule = $schedule_model->get_by_survey_id($id);


        $this->template->content = View::factory('survey/schedule')
            ->set('survey', $survey)
            ->set('schedule', $schedule);
    }


    /**
     * Handle main schedule form saving.
     * Import is intentionally handled only from this save action.
     */
    public function action_save()
    {
        if ($this->request->method() !== HTTP_Request::POST)
        {
            $this->_json_response('error', 'Invalid request method.');
            return;
        }

        $id = (int) $this->request->param('id');
        $schedule_id = (int) $this->request->post('schedule_id');

        try
        {
            $this->_get_survey_or_404($id);

            $schedule_model = Model::factory('Schedule');

            /*
             * Validate schedule data
             */
            $validation = $schedule_model->validate_save(
                $this->request->post(),
                $id,
                $schedule_id

            );

            if (!$validation['valid'])
            {
                $this->_json_response(
                    'error',
                    $validation['message']
                );

                return;
            }

            /*
             * Save schedule.
             */
            $schedule_model->save(
                $id,
                $schedule_id,
                $this->request->post()
            );


            /*
             * Existing participants remain unchanged
             * when no CSV is uploaded.
             */
            $this->_json_response(
                'success',
                'Schedule saved successfully.'
            );

            return;
        }
        catch (Exception $e)
        {
            Kohana::$log->add(
                Log::ERROR,
                $e->getMessage()
            );

            $this->_json_response(
                'error',
                $e->getMessage()
            );

            return;
        }
    }



    /**
     * Get survey or throw 404.
     * @param integer $id
     * @return array
     */
    private function _get_survey_or_404($id)
    {
        if ($id <= 0)
        {
            throw HTTP_Exception::factory(404,'Survey not found.');
        }

        $survey = DB::select()
            ->from('surveys')
            ->where('id', '=', $id)
            ->execute()
            ->current();

        if (!$survey)
        {
            throw HTTP_Exception::factory(404,'Survey not found.');
        }

        return $survey;
    }

        /**
     * Return AJAX JSON response.
     *
     * @param string  $status
     * @param string  $message
     * @param integer $http_status
     *
     * @return void
     */
    private function _json_response($status,$message,$http_status = 200)
    {
        $this->auto_render = FALSE;
        $this->response->status($http_status);
        $this->response->headers('Content-Type','application/json');
        $this->response->body(
            json_encode(array(
                'status' => $status,
                'message' => $message
            ))
        );
    }
    
}