<?php defined('SYSPATH') or die('No direct script access.');


class Controller_Schedule extends Controller_Template
{
    public $template = 'layout/master';


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

            $schedule_model = Model::factory('Schedule');


            $file = isset($_FILES['participants_file']) ? $_FILES['participants_file'] : NULL;

            $has_file = ( $file && isset($file['error']) &&  $file['error'] === UPLOAD_ERR_OK);

            /*
             * Validate schedule data and participant CSV.
             */
            $validation = $schedule_model->validate_save(
                $this->request->post(),
                $id,
                $file,
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
            $result =  $schedule_model->save(
                $id,
                $schedule_id,
                $this->request->post()
            );

            if (!$result['success'])
            {
                return $this->_json_response($result['success'] ,$result['message']);
            }

            /*
             * Import participants only when a new CSV
             * has been uploaded.
             */
            if ($has_file)
            {
                $participant_model = Model::factory('Participant');
                $imported_count = $participant_model->import_csv(
                    $id,
                    $file
                );

                $this->_json_response(
                    'success',
                    'Schedule saved successfully. ' .
                    $imported_count .
                    ' participants imported.'
                );

                return;
            }

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