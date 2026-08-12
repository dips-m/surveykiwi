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

        if ($survey_id <= 0)
        {
            throw new Exception('Invalid survey.');
        }

        if (
            empty($file) ||
            !isset($file['error']) ||
            $file['error'] !== UPLOAD_ERR_OK
        )
        {
            throw new Exception('Unable to upload CSV file.');
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'csv')
        {
            throw new Exception('Please upload a valid CSV file.');
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
        {
            throw new Exception('Invalid CSV file.');
        }

        $handle = fopen($file['tmp_name'], 'r');

        if (!$handle)
        {
            throw new Exception('Unable to open CSV file.');
        }

        $participants = array();
        $row_number = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
        {
            $row_number++;

            // Skip CSV header
            if ($row_number === 1)
            {
                continue;
            }

            // Skip empty rows
            if (empty($data))
            {
                continue;
            }

            if (count($data) < 3)
            {
                fclose($handle);

                throw new Exception(
                    'Invalid CSV format on row ' . $row_number . '.'
                );
            }

            $first_name = trim($data[0]);
            $last_name  = trim($data[1]);
            $email      = trim($data[2]);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
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

        if (empty($participants))
        {
            throw new Exception(
                'The CSV file does not contain any valid participants.'
            );
        }

        /*
         * Replace existing participants with the
         * newly imported participants.
         */
        DB::delete('survey_participants')
            ->where('survey_id', '=', $survey_id)
            ->execute();

        foreach ($participants as $participant)
        {
            DB::insert('survey_participants', array(
                'survey_id',
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at'
            ))
                ->values(array(
                    $participant['survey_id'],
                    $participant['first_name'],
                    $participant['last_name'],
                    $participant['email'],
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ))
                ->execute();
        }

        return count($participants);
    }
}