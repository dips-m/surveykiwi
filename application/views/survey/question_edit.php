<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1>Edit Question</h1>
            <p class="text-muted">
                <?php echo HTML::chars($survey['title']); ?>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-7">
        <div class="panel panel-default">
            <div class="panel-body">

                <?php if ( ! empty($errors)): ?>

                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo HTML::chars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                <?php endif; ?>

                <form method="post" action="<?php echo URL::site('survey/question_edit/' . $question['id']); ?>">

                    <div class="form-group">
                        <label for="question-text">Question</label>
                        <textarea
                            id="question-text"
                            name="question"
                            class="form-control"
                            rows="2"
                            maxlength="1000"
                        ><?php echo HTML::chars($question['question']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="question-type">Type</label>
                        <select id="question-type" name="type" class="form-control">
                            <?php foreach (Model_Survey_Question::types() as $type): ?>
                                <option
                                    value="<?php echo HTML::chars($type); ?>"
                                    <?php echo $question['type'] === $type ? 'selected' : ''; ?>
                                >
                                    <?php echo HTML::chars(ucfirst(str_replace('_', ' ', $type))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="question-options">
                            Options
                            <small class="text-muted">(one per line — only used for choice-type questions)</small>
                        </label>
                        <textarea
                            id="question-options"
                            name="options"
                            class="form-control"
                            rows="4"
                        ><?php echo HTML::chars($question['options']); ?></textarea>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input
                                type="checkbox"
                                name="required"
                                value="1"
                                <?php echo ! empty($question['required']) ? 'checked' : ''; ?>
                            >
                            Required
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary sk-create-button">
                        Save Changes
                    </button>

                    <a
                        href="<?php echo URL::site('survey/questions/' . $question['survey_id']); ?>"
                        class="btn btn-default"
                    >
                        Cancel
                    </a>

                </form>

            </div>
        </div>
    </div>
</div>