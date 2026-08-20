<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1><?php echo $is_edit ? 'Edit Survey' : 'Create Survey'; ?></h1>
        </div>
    </div>
</div>

<div id="survey-form" data-is-edit="<?php echo $is_edit ? '1' : '0'; ?>">

    <div class="section-alert" data-role="alert"></div>

    <div id="survey-form-toolbar">
        <button type="button" class="btn btn-link btn-xs" id="expand-all-btn">Expand all</button>
        <button type="button" class="btn btn-link btn-xs" id="collapse-all-btn">Collapse all</button>
    </div>

    <div class="row">
        <!-- 1. Survey Details & Questions (merged, full width) -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-target="#section-details-questions">
                    <div class="panel-heading-row">
                        <strong>1. Survey Details &amp; Questions</strong>
                        <span class="glyphicon glyphicon-chevron-down"></span>
                    </div>
                </div>
                <div id="section-details-questions" class="panel-collapse">
                    <div class="panel-body">

                        <form id="survey-details-questions-form" method="post" action="<?php echo $save_details_and_questions_url; ?>" novalidate>

                            

                            <input type="hidden" name="csrf_token" value="<?php echo HTML::chars($csrf_token); ?>">
                            <input type="hidden" name="id" value="<?php echo HTML::chars($survey['id']); ?>">

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="survey-title">Title</label>
                                        <input
                                            type="text"
                                            id="survey-title"
                                            name="title"
                                            class="form-control"
                                            maxlength="255"
                                            value="<?php echo HTML::chars($survey['title']); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="survey-status">Status</label>
                                        <select id="survey-status" name="status" class="form-control">
                                            <?php foreach (Model_Survey::statuses() as $status): ?>
                                                <option
                                                    value="<?php echo HTML::chars($status); ?>"
                                                    <?php echo $survey['status'] === $status ? 'selected' : ''; ?>
                                                >
                                                    <?php echo HTML::chars(ucfirst($status)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="survey-description">Description</label>
                                        <textarea
                                            id="survey-description"
                                            name="description"
                                            class="form-control"
                                            rows="4"
                                            maxlength="2000"
                                        ><?php echo HTML::chars($survey['description']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-divider">

                            <div id="question-rows">
                                <?php foreach ($questions as $index => $question): ?>
                                    <?php echo View::factory('survey/_question_row')
                                        ->set('index', $index)
                                        ->set('question', $question)
                                        ->set('question_types', $question_types); ?>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" id="add-question-btn" class="btn btn-default btn-sm">
                                <span class="glyphicon glyphicon-plus"></span>
                                Add Question
                            </button>

                            <div class="section-footer">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?php echo $is_edit ? 'Save Survey' : 'Create Survey'; ?>
                                </button>
                                <span class="text-muted small" data-role="saving-indicator" style="display:none;">Saving&hellip;</span>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-schedule">

        <!-- 3. Survey Schedule (full width) -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-target="#section-schedule">
                    <div class="panel-heading-row">
                        <strong>3. Survey Schedule</strong>
                        <span class="glyphicon glyphicon-chevron-down"></span>
                    </div>
                </div>
                <div id="section-schedule" class="panel-collapse">
                    <div class="panel-body">

                        <?php if ( ! $is_edit): ?>
                            <div class="section-locked-note">
                                Save the survey details first — the schedule can be set once the survey exists.
                            </div>
                        <?php endif; ?>

                        <form id="survey-schedule-form" method="post" action="<?php echo $save_schedule_url; ?>" novalidate>

                            <fieldset <?php echo $is_edit ? '' : 'disabled'; ?>>

                                <input type="hidden" name="csrf_token" value="<?php echo HTML::chars($csrf_token); ?>">
                                <input type="hidden" name="survey_id" value="<?php echo HTML::chars($survey['id']); ?>">

                                <div class="schedule-fields">
                                    <div class="form-group">
                                        <label for="survey-starts-at">Opens</label>
                                        <input
                                            type="datetime-local"
                                            id="survey-starts-at"
                                            name="starts_at"
                                            class="form-control"
                                            value="<?php //echo HTML::chars($survey['starts_at']); ?>"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label for="survey-ends-at">Closes</label>
                                        <input
                                            type="datetime-local"
                                            id="survey-ends-at"
                                            name="ends_at"
                                            class="form-control"
                                            value="<?php //echo HTML::chars($survey['ends_at']); ?>"
                                        >
                                    </div>
                                </div>

                                <div class="section-footer">
                                    <button type="submit" class="btn btn-primary btn-sm">Save Schedule</button>
                                    <span class="text-muted small" data-role="saving-indicator" style="display:none;">Saving&hellip;</span>
                                </div>

                            </fieldset>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="section-footer" style="margin-top: 16px;">
        <a href="<?php echo URL::site('survey'); ?>" class="btn btn-default">
            <?php echo $is_edit ? 'Done' : 'Cancel'; ?>
        </a>
    </div>

</div>

<!-- Template for a blank question row, cloned by JS on "Add Question" -->
<script type="text/template" id="question-row-template">
    <?php echo View::factory('survey/_question_row')
        ->set('index', '__INDEX__')
        ->set('question', array('question' => '', 'type' => Model_SurveyQuestion::TYPE_TEXT, 'options' => '', 'required' => FALSE))
        ->set('question_types', $question_types); ?>
</script>

<!-- Template for a blank option row, cloned by JS on "Add Option" -->
<script type="text/template" id="option-row-template">
    <div class="option-row">
        <input type="text" name="__NAME__" class="form-control option-row-input" maxlength="500" placeholder="Option text" value="">
        <button type="button" class="js-remove-option option-row-remove" title="Remove option">
            <span class="glyphicon glyphicon-remove"></span>
        </button>
    </div>
</script>

<script>
(function () {
    'use strict';

    var root = document.getElementById('survey-form');

    // Kept in sync with Model_SurveyQuestion::requires_options() on the server.
    var CHOICE_TYPES = ['radio', 'checkbox', 'select'];

    /* ---------------------------------------------------------------
     * Panel expand/collapse — independent per panel (not an accordion),
     * so all sections can be open at once. All start expanded.
     * ------------------------------------------------------------- */
    var headings = root.querySelectorAll('.panel-heading');

    Array.prototype.forEach.call(headings, function (heading) {
        var target = document.querySelector(heading.getAttribute('data-target'));

        heading.addEventListener('click', function () {
            var isHidden = target.style.display === 'none';
            target.style.display = isHidden ? '' : 'none';
            heading.classList.toggle('collapsed', ! isHidden);
        });
    });

    document.getElementById('expand-all-btn').addEventListener('click', function () {
        root.querySelectorAll('.panel-collapse').forEach(function (el) { el.style.display = ''; });
        root.querySelectorAll('.panel-heading').forEach(function (el) { el.classList.remove('collapsed'); });
    });

    document.getElementById('collapse-all-btn').addEventListener('click', function () {
        root.querySelectorAll('.panel-collapse').forEach(function (el) { el.style.display = 'none'; });
        root.querySelectorAll('.panel-heading').forEach(function (el) { el.classList.add('collapsed'); });
    });

    /* ---------------------------------------------------------------
     * Question rows — add / remove
     * ------------------------------------------------------------- */
    var container = document.getElementById('question-rows');
    var addButton = document.getElementById('add-question-btn');
    var rowTemplate = document.getElementById('question-row-template').innerHTML;
    var optionTemplate = document.getElementById('option-row-template').innerHTML;
    var nextIndex = <?php echo count($questions); ?>;

    function toggleOptionsVisibility(row) {
        var select = row.querySelector('.js-question-type');
        var repeater = row.querySelector('.question-options-repeater');

        if ( ! select || ! repeater) { return; }

        repeater.style.display = CHOICE_TYPES.indexOf(select.value) === -1 ? 'none' : '';
    }

    addButton.addEventListener('click', function () {
        var html    = rowTemplate.split('__INDEX__').join(nextIndex);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();

        var row = wrapper.firstChild;
        container.appendChild(row);
        toggleOptionsVisibility(row);

        nextIndex += 1;
    });

    container.addEventListener('click', function (event) {
        var removeQuestionBtn = event.target.closest('.js-remove-question');

        if (removeQuestionBtn)
        {
            var row = removeQuestionBtn.closest('.question-row');

            if (row)
            {
                row.parentNode.removeChild(row);
            }

            return;
        }

        var addOptionBtn = event.target.closest('.js-add-option');

        if (addOptionBtn)
        {
            var questionRow = addOptionBtn.closest('.question-row');
            var textInput   = questionRow.querySelector('.question-row-text');
            var optionsName = textInput.getAttribute('name').replace('[question]', '[options][]');

            var html    = optionTemplate.split('__NAME__').join(optionsName);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();

            questionRow.querySelector('.option-rows').appendChild(wrapper.firstChild);

            return;
        }

        var removeOptionBtn = event.target.closest('.js-remove-option');

        if (removeOptionBtn)
        {
            var optionRows = removeOptionBtn.closest('.option-rows');

            // Keep at least two options — matches the server-side minimum.
            if (optionRows.querySelectorAll('.option-row').length <= 2)
            {
                return;
            }

            var optionRow = removeOptionBtn.closest('.option-row');
            optionRow.parentNode.removeChild(optionRow);
        }
    });

    container.addEventListener('change', function (event) {
        if (event.target.classList.contains('js-question-type'))
        {
            toggleOptionsVisibility(event.target.closest('.question-row'));
        }
    });

    // Set correct initial visibility for rows rendered server-side (edit mode).
    container.querySelectorAll('.question-row').forEach(function (row) {
        toggleOptionsVisibility(row);
    });

    /* ---------------------------------------------------------------
     * AJAX submit — each section saves independently.
     * On the very first save of a brand-new survey (Details form, no
     * id yet), the server returns a redirect to the edit URL so the
     * Questions/Schedule sections load already unlocked.
     * ------------------------------------------------------------- */
    function showAlert(form, type, messages) {
        var alertBox = root.querySelector('[data-role="alert"]');

        if (!alertBox) {
            return;
        }

        alertBox.className = 'section-alert section-alert-' + type;

        if (Array.isArray(messages))
        {
            var list = document.createElement('ul');

            messages.forEach(function (msg) {
                var li = document.createElement('li');
                li.textContent = msg;
                list.appendChild(li);
            });

            alertBox.innerHTML = '';
            alertBox.appendChild(list);
        }
        else
        {
            alertBox.textContent = messages;
        }
    }

    function bindSectionForm(form, onSuccess) {
        if ( ! form) { return; }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Client-side guard: at least one question row must have text.
            // Server re-validates this regardless; this just avoids a round-trip.
            if (form.id === 'survey-details-questions-form')
            {
                var hasQuestion = false;

                form.querySelectorAll('.question-row-text').forEach(function (textarea) {
                    if (textarea.value.trim() !== '') { hasQuestion = true; }
                });

                if ( ! hasQuestion)
                {
                    showAlert(form, 'error', ['Add at least one question.']);
                    return;
                }
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            var indicator = form.querySelector('[data-role="saving-indicator"]');

            submitBtn.disabled = true;
            if (indicator) { indicator.style.display = ''; }

            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    var data = result.data;

                    if (data.success)
                    {
                        showAlert(form, 'success', data.message || 'Saved.');

                        if (data.survey_id)
                        {
                            root.querySelectorAll('input[name="id"], input[name="survey_id"]').forEach(function (input) {
                                input.value = data.survey_id;
                            });
                        }

                        if (typeof onSuccess === 'function')
                        {
                            onSuccess(data);
                        }
                    }
                    else
                    {
                        showAlert(form, 'error', data.errors && data.errors.length ? data.errors : ['Could not save. Please try again.']);
                    }
                })
                .catch(function () {
                    showAlert(form, 'error', ['Network error. Please try again.']);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    if (indicator) { indicator.style.display = 'none'; }
                });
        });
    }

    bindSectionForm(document.getElementById('survey-details-questions-form'), function () {
        var detailsHeading  = root.querySelector('[data-target="#section-details-questions"]');
        var detailsPanel    = document.getElementById('section-details-questions');
        var scheduleHeading = root.querySelector('[data-target="#section-schedule"]');
        var schedulePanel   = document.getElementById('section-schedule');

        detailsPanel.style.display = 'none';
        detailsHeading.classList.add('collapsed');

        schedulePanel.style.display = '';
        scheduleHeading.classList.remove('collapsed');

        scheduleHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    bindSectionForm(document.getElementById('survey-schedule-form'));

})();
</script>