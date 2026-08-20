<?php defined('SYSPATH') or die('No direct script access.');


class Controller_Participant extends Controller
{
    /**
     * add single participant.
     *
     * AJAX POST only.
     */
    public function action_add()
    {
        if ($this->request->method() !== HTTP_Request::POST)
        {
            $this->_json_response(
                'error',
                'Invalid request method.'
            );

            return;
        }

        try
        {
            $survey_id = (int) $this->request->post('survey_id');

            $first_name = trim($this->request->post('first_name'));
            $last_name = trim($this->request->post('last_name'));
            $email = trim($this->request->post('email'));

            if ($survey_id <= 0)
            {
                $this->_json_response('error', 'Invalid survey.');
                return;
            }

            $participant_model = Model::factory('Participant');

            if ($participant_model->email_exists($survey_id, $email))
            {
                $this->_json_response('error', 'This email already exists for this survey.');
                return;
            }

            /*
            * Make sure the survey exists.
            */
            $survey = DB::select('id')
                ->from('surveys')
                ->where('id', '=', $survey_id)
                ->execute()
                ->current();

            if (!$survey)
            {
                $this->_json_response('error','Survey not found.');
                return;
            }

            $participant = $participant_model->create(
                $survey_id,
                $first_name,
                $last_name,
                $email
            );

            $this->_json_response($participant['success'], $participant['message']);
            return;
        }
        catch (Exception $e)
        {
            Kohana::$log->add(Kohana::ERROR, $e->getMessage());
            $this->_json_response('error', $e->getMessage());
            return;
        }
    }

    /**
     * Update a participant.
     *
     * AJAX POST only.
     */
    public function action_edit()
    {
        if ($this->request->method() !== HTTP_Request::POST)
        {
            $this->_json_response('error','Invalid request method.');
            return;
        }

        $id = (int) $this->request->post('id');

        if ($id <= 0)
        {
            $this->_json_response('error','Invalid participant.');
            return;
        }

        $survey_id = (int) $this->request->post('survey_id');

        $first_name = trim($this->request->post('first_name'));
        $last_name = trim($this->request->post('last_name'));
        $email = trim($this->request->post('email'));

        /*
         * Validate required fields.
         */
        if ($first_name === '' || $last_name === '' || $email === '')
        {
            $this->_json_response('error','All participant fields are required.');
            return;
        }


        /*
         * Validate email.
         */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $this->_json_response('error', 'Please enter a valid email address.');
            return;
        }

        if ($survey_id <= 0)
        {
            $this->_json_response(
                'error',
                'Invalid survey.'
            );

            return;
        }

        $participant_model = Model::factory('Participant');

        if ($participant_model->email_exists($survey_id, $email, $id))
            {
                $this->_json_response('error', 'This email already exists for this survey.');
                return;
            }

        try
        {
            /*
             * Update participant.
             */
            $updated = $participant_model->update(
                $id,
                array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'email'      => $email
                )
            );


            if (!$updated)
            {
                $this->_json_response(
                    'error',
                    'Participant not found.'
                );

                return;
            }


            $this->_json_response(
                'success',
                'Participant updated successfully.'
            );
        }
        catch (Exception $e)
        {
            Kohana::$log->add(
                Kohana::ERROR,
                $e->getMessage()
            );


            $this->_json_response(
                'error',
                $e->getMessage()
            );
        }
    }


    /**
     * Delete a participant.
     *
     * AJAX POST only.
     */
    public function action_delete()
    {
        if ($this->request->method() !== HTTP_Request::POST)
        {
            $this->_json_response(
                'error',
                'Invalid request method.'
            );

            return;
        }


        $id = (int) $this->request->post('id');


        if ($id <= 0)
        {
            $this->_json_response(
                'error',
                'Invalid participant.'
            );

            return;
        }


        try
        {
            $participant_model = Model::factory('Participant');


            /*
             * Delete participant.
             */
            $deleted = $participant_model->delete($id);


            if (!$deleted)
            {
                $this->_json_response(
                    'error',
                    'Participant not found.'
                );

                return;
            }


            $this->_json_response(
                'success',
                'Participant deleted successfully.'
            );
        }
        catch (Exception $e)
        {
            Kohana::$log->add(
                Kohana::ERROR,
                $e->getMessage()
            );


            $this->_json_response(
                'error',
                $e->getMessage()
            );
        }
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
    private function _json_response(
        $status,
        $message,
        $http_status = 200
    )
    {
        $this->auto_render = FALSE;


        $this->response->status($http_status);


        $this->response->headers(
            'Content-Type',
            'application/json'
        );


        $this->response->body(
            json_encode(array(
                'status'  => $status,
                'message' => $message
            ))
        );
    }
}