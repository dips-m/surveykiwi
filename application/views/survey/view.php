<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1>
                <?php echo HTML::chars($survey['title']); ?>
            </h1>
            <?php if ( ! empty($survey['description'])): ?>

                <p class="text-muted">
                    <?php
                    echo HTML::chars(
                        $survey['description']
                    );
                    ?>
                </p>

            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">

                <strong class="pull-left">
                    Survey Details
                </strong>

                <a
                    href="<?php
                    echo URL::site(
                        'survey/schedule/'
                        . (int) $survey['id']
                    );
                    ?>"
                    class="btn btn-default btn-sm pull-right"
                >
                    <span class="glyphicon glyphicon-calendar"></span>
                    View Schedule
                </a>

            </div>
            <div class="panel-body">
                <p>
                    <strong>Status:</strong>

                    <?php
                    echo HTML::chars(
                        ucfirst($survey['status'])
                    );
                    ?>
                </p>
                <p>
                    <strong>Participants:</strong>

                    <?php
                    echo (int) $participant_count;
                    ?>
                </p>
                <p>
                    <strong>Questions:</strong>

                    <?php
                    echo count($questions);
                    ?>
                </p>
                <p>
                    <strong>Created:</strong>

                    <?php
                    if ( ! empty($survey['created_at']))
                    {
                        echo HTML::chars(
                            date(
                                'M d, Y',
                                strtotime(
                                    $survey['created_at']
                                )
                            )
                        );
                    }
                    else
                    {
                        echo '-';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="panel panel-default">

            <div class="panel-heading">
                <strong>
                    Questions
                </strong>
            </div>

            <?php if (empty($questions)): ?>

                <div class="panel-body">
                    <p class="text-muted">
                        No questions have been added to this survey.
                    </p>
                </div>

            <?php else: ?>

                <div class="list-group">

                    <?php foreach ($questions as $index => $question): ?>

                        <div class="list-group-item">

                            <div class="question-text">
                                <?php echo ($index + 1); ?>.
                                <?php
                                echo HTML::chars(
                                    $question['question']
                                );
                                ?>
                            </div>

                            <div class="question-type text-muted">
                                Type:
                                <?php
                                echo HTML::chars(
                                    ucfirst(
                                        $question['type']
                                    )
                                );
                                ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </div>
</div>