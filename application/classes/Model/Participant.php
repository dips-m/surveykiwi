<?php defined('SYSPATH') or die('No direct script access.');


class Model_Participant
{
    /**
     * Get participants for a survey.
     *
     * @param integer $survey_id
     *
     * @return array
     */
    public function get_by_survey($survey_id)
    {
        return DB::select()
            ->from('survey_participants')
            ->where('survey_id', '=', (int) $survey_id)
            ->order_by('id', 'ASC')
            ->execute()
            ->as_array();
    }


    /**
     * Get participant count for a survey.
     *
     * @param integer $survey_id
     *
     * @return integer
     */
    public function count_by_survey($survey_id)
    {
        return (int) DB::select(
            array(DB::expr('COUNT(*)'), 'total')
        )
            ->from('survey_participants')
            ->where('survey_id', '=', (int) $survey_id)
            ->execute()
            ->get('total');
    }

    /**
     * Check whether an email already exists for a survey.
     *
     * @param integer      $survey_id
     * @param string       $email
     * @param integer|null $exclude_id
     *
     * @return boolean
     */
    public function email_exists($survey_id, $email, $exclude_id = NULL)
    {
        $query = DB::select(
                array(DB::expr('COUNT(*)'), 'total')
            )
            ->from('survey_participants')
            ->where('survey_id', '=', (int) $survey_id)
            ->where('email', '=', trim($email));

        // During edit, exclude the current participant.
        if ($exclude_id !== NULL  && (int) $exclude_id > 0)
        {
            $query->where('id', '!=', (int) $exclude_id);
        }

        return (int) $query->execute()->get('total') > 0;
    }


    /**
     * Import participants from CSV.
     *
     * Existing participants are replaced only after
     * the CSV has been successfully validated.
     *
     * @param integer $survey_id
     * @param array   $file
     *
     * @return integer
     *
     * @throws Exception
     */
    public function import_csv($survey_id, $file)
    {
        $survey_id = (int) $survey_id;

        if ($survey_id <= 0) {
            throw new Exception('Invalid survey.');
        }

        if (
            empty($file) ||
            !isset($file['error']) ||
            $file['error'] !== UPLOAD_ERR_OK
        ) {
            throw new Exception('Unable to upload CSV file.');
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'csv') {
            throw new Exception('Please upload a valid CSV file.');
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid CSV file.');
        }

        $handle = fopen($file['tmp_name'], 'r');

        if (!$handle) {
            throw new Exception('Unable to open CSV file.');
        }

        $participants = array();
        $row_number = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row_number++;

            // Skip CSV header
            if ($row_number === 1) {
                continue;
            }

            // Skip empty rows
            if (empty($data)) {
                continue;
            }

            if (count($data) < 3) {
                fclose($handle);

                throw new Exception(
                    'Invalid CSV format on row ' . $row_number . '.'
                );
            }

            $first_name = trim($data[0]);
            $last_name  = trim($data[1]);
            $email      = trim($data[2]);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                fclose($handle);

                throw new Exception(
                    'Invalid email address on CSV row ' . $row_number . '.'
                );
            }

            $participants[] = array(
                'survey_id'  => $survey_id,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email
            );
        }

        fclose($handle);

        if (empty($participants)) {
            throw new Exception(
                'The CSV file does not contain any valid participants.'
            );
        }

        /*
        * Replace existing participants.
        *
        * survey_invitations are related to survey_participants
        * with ON DELETE CASCADE, so deleting participants
        * automatically removes their invitations.
        */
        DB::query(NULL, 'START TRANSACTION')->execute();

        try
        {
            DB::delete('survey_participants')
                ->where('survey_id', '=', $survey_id)
                ->execute();

            $insert = DB::insert('survey_participants', array(
                'survey_id',
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at'
            ));

            $now = date('Y-m-d H:i:s');

            foreach ($participants as $participant)
            {
                $insert->values(array(
                    $participant['survey_id'],
                    $participant['first_name'],
                    $participant['last_name'],
                    $participant['email'],
                    $now,
                    $now
                ));
            }

            $insert->execute();
            DB::query(NULL, 'COMMIT')->execute();
        }
        catch (Exception $e)
        {
            DB::query(NULL, 'ROLLBACK')->execute();
            throw $e;
        }

        return count($participants);
    }

    public function create($survey_id, $first_name, $last_name, $email)
    {
        $survey_id = (int) $survey_id;

        if ($survey_id <= 0)
        {
            return array('success' => FALSE, 'message' => 'Invalid survey.');
        }

        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $email = trim($email);

        if ($first_name === '')
        {
            return array('success' => FALSE, 'message' => 'First name is required.');
        }

        if ($last_name === '')
        {
            return array('success' => FALSE, 'message' => 'Last name is required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return array('success' => FALSE, 'message' => 'Please enter a valid email address.');
        }

        list($participant_id, $rows) = DB::insert(
            'survey_participants',
            array(
                'survey_id',
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at'
            )
        )
            ->values(array(
                $survey_id,
                $first_name,
                $last_name,
                $email,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ))
            ->execute();

        return array('success' => True, 'message' => 'Participant added successfully.');
    }

    /**
     * Update a participant.
     *
     * @param integer $id
     * @param array   $data
     *
     * @return integer
     */
    public function update($id, $data)
    {
        return DB::update('survey_participants')
            ->set(array(
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'updated_at' => date('Y-m-d H:i:s')
            ))
            ->where('id', '=', (int) $id)
            ->execute();
    }


    /**
     * Delete a participant.
     *
     * @param integer $id
     *
     * @return integer
     */
    public function delete($id)
    {
        return DB::delete('survey_participants')
            ->where('id', '=', (int) $id)
            ->execute();
    }
}
