<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-9">
            <h1>
                <?php echo HTML::chars($survey['title']); ?>
            </h1>
            <p class="text-muted">Manage Questions</p>
        </div>
        <div class="col-sm-3 text-right">
            <a
                href="<?php echo URL::site('survey/view/' . $survey['id']); ?>"
                class="btn btn-default"
            >
                Back to Survey
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Questions</strong>
            </div>

            <?php if (empty($questions)): ?>

                <div class="panel-body sk-empty-question">
                    <p class="text-muted">
                        No questions have been added to this survey yet.
                    </p>
                </div>

            <?php else: ?>

                <div class="sk-question-list list-group">

                    <?php foreach ($questions as $index => $question): ?>

                        <div class="sk-question-item">

                            <div class="sk-question-number">
                                <?php echo ($index + 1); ?>
                            </div>

                            <div class="sk-question-content">
                                <div class="sk-question-text">
                                    <?php echo HTML::chars($question['question']); ?>
                                </div>

                                <div class="sk-question-type">
                                    <span class="label label-default">
                                        <?php echo HTML::chars(ucfirst(str_replace('_', ' ', $question['type']))); ?>
                                    </span>
                                    <?php if ( ! empty($question['settings']['required'])): ?>
                                        <span class="label label-warning">Required</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="participant-actions">
                                <a
                                    href="<?php echo URL::site('survey/question_edit/' . $question['id']); ?>"
                                    class="sk-icon-action"
                                    title="Edit question"
                                >
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </a>

                                <form
                                    method="post"
                                    action="<?php echo URL::site('survey/question_delete/' . $question['id']); ?>"
                                    onsubmit="return confirm('Delete this question?');"
                                    style="display:inline;"
                                >
                                    <button type="submit" class="sk-icon-action" title="Delete question">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                </form>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <div class="col-sm-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Add Question</strong>
            </div>
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

                <form method="post" action="<?php echo URL::site('survey/questions/' . $survey['id']); ?>">

                    <div class="form-group">
                        <label for="question-text">Question</label>
                        <textarea
                            id="question-text"
                            name="question"
                            class="form-control"
                            rows="2"
                            maxlength="1000"
                        ><?php echo HTML::chars($input['question']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="question-type">Type</label>
                        <select id="question-type" name="type" class="form-control">
                            <?php foreach (Model_Survey_Question::types() as $type): ?>
                                <option
                                    value="<?php echo HTML::chars($type); ?>"
                                    <?php echo $input['type'] === $type ? 'selected' : ''; ?>
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
                        ><?php echo HTML::chars($input['options']); ?></textarea>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input
                                type="checkbox"
                                name="required"
                                value="1"
                                <?php echo ! empty($input['required']) ? 'checked' : ''; ?>
                            >
                            Required
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary sk-create-button">
                        Add Question
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>