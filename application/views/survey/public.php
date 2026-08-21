<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?php echo HTML::chars($survey['title']); ?>
    </title>

    <link
        rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo URL::base(); ?>assets/css/app.css"
    >

</head>

<body>

<div class="container">

    <div class="row">

        <div class="col-sm-8 col-sm-offset-2">

            <div class="survey-public">

                <div class="page-header">

                    <h1>
                        <?php
                        echo HTML::chars(
                            $survey['title']
                        );
                        ?>
                    </h1>

                    <?php if ( ! empty($survey['description'])): ?>

                        <p class="text-muted">
                            <?php
                            echo nl2br(
                                HTML::chars(
                                    $survey['description']
                                )
                            );
                            ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div
                    class="section-alert"
                    data-role="alert"
                    style="display:none;"
                ></div>


                <form
                    method="post"
                    action="<?php
                    echo URL::site(
                        'survey/' .
                        (int) $survey['id'] .
                        '/submit'
                    );
                    ?>"
                >

                    <?php
                    foreach ($questions as $index => $question):
                    ?>

                        <div class="form-group">

                            <label>

                                <?php
                                echo ($index + 1) . '. ';
                                ?>

                                <?php
                                echo HTML::chars(
                                    $question['question']
                                );
                                ?>

                                <?php
                                if (
                                    ! empty(
                                        $question['is_required']
                                    )
                                ):
                                ?>

                                    <span class="text-danger">
                                        *
                                    </span>

                                <?php endif; ?>

                            </label>


                            <?php
                                switch ($question['type']) {
                                    case 'text':
                                        ?>
                                        <input
                                            type="text"
                                            name="answers[<?php echo (int) $question['id']; ?>]"
                                            class="form-control"
                                            <?php echo !empty($question['is_required']) ? 'required' : ''; ?>
                                        >
                                        <?php
                                        break;

                                    case 'textarea':
                                        ?>
                                        <textarea
                                            name="answers[<?php echo (int) $question['id']; ?>]"
                                            class="form-control"
                                            rows="5"
                                            <?php echo !empty($question['is_required']) ? 'required' : ''; ?>
                                        ></textarea>
                                        <?php
                                        break;

                                    case 'radio':
                                        foreach ($question['options'] as $option):
                                        ?>
                                            <div class="radio">
                                                <label>
                                                    <input
                                                        type="radio"
                                                        name="answers[<?php echo (int) $question['id']; ?>]"
                                                        value="<?php echo HTML::chars($option); ?>"
                                                        <?php echo !empty($question['is_required']) ? 'required' : ''; ?>
                                                    >
                                                    <?php echo HTML::chars($option); ?>
                                                </label>
                                            </div>
                                        <?php
                                        endforeach;
                                        break;

                                    case 'checkbox':
                                        foreach ($question['options'] as $option):
                                        ?>
                                            <div class="checkbox">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="answers[<?php echo (int) $question['id']; ?>][]"
                                                        value="<?php echo HTML::chars($option); ?>"
                                                    >
                                                    <?php echo HTML::chars($option); ?>
                                                </label>
                                            </div>
                                        <?php
                                        endforeach;
                                        break;

                                        case 'select':
                                            ?>
                                                <select
                                                    name="answers[<?php echo (int) $question['id']; ?>]"
                                                    class="form-control"
                                                    <?php echo !empty($question['is_required']) ? 'required' : ''; ?>
                                                >
                                                    <option value="">-- Select an option --</option>
                                        
                                                    <?php foreach ($question['options'] as $option): ?>
                                                        <option value="<?php echo HTML::chars($option); ?>">
                                                            <?php echo HTML::chars($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                        
                                                </select>
                                            <?php
                                            break;

                                    default:
                                        ?>
                                        <p class="text-muted">Unsupported question type.</p>
                                        <?php
                                        break;
                                }
                                ?>

                        </div>

                    <?php endforeach; ?>


                    <hr>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg"
                        disabled="disabled"
                    >
                        Submit Survey
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>