<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Survey question access.
 *
 * Assumed schema:
 *   survey_questions(id PK, survey_id FK, question, type, options TEXT,
 *                     settings TEXT NULL, sort_order INT,
 *                     created_at, updated_at)
 *
 * `settings` is a JSON-encoded object (e.g. {"required":true}) rather than
 * a dedicated `required` column, so new per-question options can be added
 * later without another migration. Callers still read/write a plain
 * boolean `required` key on the question array — the JSON encode/decode
 * happens only here, at the model boundary.
 *
 * Questions are saved as a full replace-for-survey operation rather than
 * per-row upserts: the form always posts the complete set for a survey, so
 * delete+reinsert inside a transaction is simpler and avoids reconciling
 * client-side row identity with server-side ids.
 */
class Model_SurveyQuestion {

    const TYPE_TEXT     = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_RADIO    = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_SELECT   = 'select';

    protected static $_choice_types = array(
        self::TYPE_RADIO,
        self::TYPE_CHECKBOX,
        self::TYPE_SELECT,
    );

    public static function types()
    {
        return array(
            self::TYPE_TEXT,
            self::TYPE_TEXTAREA,
            self::TYPE_RADIO,
            self::TYPE_CHECKBOX,
            self::TYPE_SELECT,
        );
    }

    /**
     * Whether this question type needs a non-empty options list.
     */
    public static function requires_options($type)
    {
        return in_array($type, self::$_choice_types);
    }

    public static function find_by_survey($survey_id)
    {
        $rows = DB::select('*')
            ->from('survey_questions')
            ->where('survey_id', '=', (int) $survey_id)
            ->order_by('sort_order', 'ASC')
            ->execute()
            ->as_array();

        foreach ($rows as & $row)
        {
            $settings = $row['settings'] ? json_decode($row['settings'], TRUE) : array();
            $row['settings'] = is_array($settings) ? $settings : array();

            // Kept for callers (the view, mainly) that just want a plain
            // boolean without knowing about the settings column.
            $row['required'] = ! empty($row['settings']['required']);
        }

        return $rows;
    }

    /**
     * Replace every question belonging to $survey_id with $questions,
     * in the order given. Runs inside a transaction so a mid-way failure
     * can't leave the survey with a partial question set.
     *
     * @param int   $survey_id
     * @param array $questions each: array('question','type','options','required')
     */
    public static function replace_for_survey($survey_id, array $questions, $manage_transaction = TRUE)
    {
        $db = Database::instance();

        if ($manage_transaction)
        {
            $db->begin();
        }

        try
        {
            DB::delete('survey_questions')
                ->where('survey_id', '=', (int) $survey_id)
                ->execute();

            $sort_order = 0;
            $now        = date('Y-m-d H:i:s');

            foreach ($questions as $question)
            {
                $settings = array(
                    'required' => ! empty($question['required']),
                );

                DB::insert('survey_questions', array(
                        'survey_id', 'question', 'type', 'options',
                        'settings', 'sort_order', 'created_at', 'updated_at',
                    ))
                    ->values(array(
                        (int) $survey_id,
                        $question['question'],
                        $question['type'],
                        $question['options'],
                        json_encode($settings),
                        $sort_order,
                        $now,
                        $now,
                    ))
                    ->execute();

                $sort_order++;
            }

            if ($manage_transaction)
            {
                $db->commit();
            }
        }
        catch (Exception $e)
        {
            if ($manage_transaction)
            {
                $db->rollback();
            }

            throw $e;
        }

        return TRUE;
    }

}