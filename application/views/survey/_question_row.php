<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php
    // Normalize options into an array so the repeater always has something to render.
    // Supports both a legacy newline-separated string and an already-array value.
    $option_values = is_array($question['options'])
        ? $question['options']
        : array_filter(array_map('trim', explode("\n", (string) $question['options'])), 'strlen');

    $option_values = array_values($option_values);

    // Keep at least two option rows, matching the JS minimum.
    while (count($option_values) < 2)
    {
        $option_values[] = '';
    }
?>
<div class="question-row">

    <div class="question-row-head">
        <span class="question-row-number"><?php echo is_numeric($index) ? ((int) $index + 1) : '__ROW_NUMBER__'; ?></span>

        <textarea
            name="questions[<?php echo $index; ?>][question]"
            class="form-control question-row-text"
            rows="1"
            maxlength="1000"
            placeholder="Enter question text"
        ><?php echo HTML::chars($question['question']); ?></textarea>

        <select name="questions[<?php echo $index; ?>][type]" class="form-control js-question-type question-row-type">
            <?php foreach ($question_types as $type): ?>
                <option
                    value="<?php echo HTML::chars($type); ?>"
                    <?php echo $question['type'] === $type ? 'selected' : ''; ?>
                >
                    <?php echo HTML::chars(ucfirst(str_replace('_', ' ', $type))); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="button" class="js-remove-question question-row-remove" title="Remove question">
            <span class="glyphicon glyphicon-trash"></span>
        </button>
    </div>

    <div class="question-row-fields">

        <label class="question-row-required">
            <input
                type="checkbox"
                name="questions[<?php echo $index; ?>][required]"
                value="1"
                <?php echo ! empty($question['required']) ? 'checked' : ''; ?>
            >
            Required
        </label>

    </div>

    <div class="question-options-repeater">
        <span class="question-options-label">Options</span>

        <div class="option-rows">
            <?php foreach ($option_values as $option_value): ?>
                <div class="option-row">
                    <input
                        type="text"
                        name="questions[<?php echo $index; ?>][options][]"
                        class="form-control option-row-input"
                        maxlength="500"
                        placeholder="Option text"
                        value="<?php echo HTML::chars($option_value); ?>"
                    >
                    <button type="button" class="js-remove-option option-row-remove" title="Remove option">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="js-add-option btn btn-link btn-xs">
            <span class="glyphicon glyphicon-plus"></span>
            Add Option
        </button>
    </div>

</div>
