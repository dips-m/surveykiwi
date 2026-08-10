<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">

            <div class="sk-page-header">

                <div>
                    <h2>Surveys</h2>

                    <p class="text-muted">
                        Create and manage your surveys.
                    </p>
                </div>

                <div>
                    <a
                        href="<?php echo URL::site('survey/create'); ?>"
                        class="btn btn-primary"
                    >
                        <span class="glyphicon glyphicon-plus"></span>
                        Create Survey
                    </a>
                </div>

            </div>

        </div>
    </div>


    <?php if (empty($surveys)): ?>

        <div class="sk-empty-state text-center">

            <span class="glyphicon glyphicon-list-alt"></span>

            <h3>No surveys yet</h3>

            <p class="text-muted">
                Create your first survey to get started.
            </p>

            <a
                href="<?php echo URL::site('survey/create'); ?>"
                class="btn btn-primary"
            >
                <span class="glyphicon glyphicon-plus"></span>
                Create Survey
            </a>

        </div>

    <?php else: ?>

        <div class="panel panel-default sk-survey-table">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-header">
                        <tr>

                            <th>Survey</th>

                            <th>Questions</th>

                            <th>Frequency</th>

                            <th>Participants</th>

                            <th>Created</th>

                            <th>Status</th>

                            <th class="text-right">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($surveys as $survey): ?>

                            <?php

                            /*
                             * Status
                             */
                            $status_class = 'label-default';

                            if ($survey['status'] === 'published')
                            {
                                $status_class = 'label-success';
                            }
                            elseif ($survey['status'] === 'draft')
                            {
                                $status_class = 'label-warning';
                            }
                            elseif ($survey['status'] === 'closed')
                            {
                                $status_class = 'label-danger';
                            }


                            /*
                             * Frequency
                             */
                            $frequency = 'Not scheduled';

                            if ( ! empty($survey['frequency']))
                            {
                                $frequency = ucfirst(
                                    $survey['frequency']
                                );
                            }


                            /*
                             * Created date
                             */
                            $created = '-';

                            if ( ! empty($survey['created_at']))
                            {
                                $created = date(
                                    'M d, Y',
                                    strtotime($survey['created_at'])
                                );
                            }

                            ?>

                            <tr>

                                <!-- Survey -->

                                <td>

                                    <div class="sk-survey-name">

                                        <span>
                                            <?php
                                            echo HTML::chars(
                                                $survey['title']
                                            );
                                            ?>
                                        </span>

                                    </div>

                                </td>


                                <!-- Questions -->

                                <td>

                                    <span class="sk-table-count">

                                        <?php
                                        echo (int) $survey['questions'];
                                        ?>

                                    </span>

                                </td>


                                <!-- Frequency -->

                                <td>

                                    <?php if ($survey['frequency']): ?>

                                        <span class="sk-frequency">

                                            <span class="glyphicon glyphicon-repeat"></span>

                                            <?php
                                            echo HTML::chars(
                                                $frequency
                                            );
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not scheduled
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Participants -->

                                <td>

                                    <span class="sk-table-count">

                                        <?php
                                        echo (int) $survey['participants'];
                                        ?>

                                    </span>

                                </td>


                                <!-- Created -->

                                <td>

                                    <?php
                                    echo HTML::chars($created);
                                    ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <span
                                        class="label <?php echo $status_class; ?>"
                                    >
                                        <?php
                                        echo HTML::chars(
                                            ucfirst($survey['status'])
                                        );
                                        ?>
                                    </span>

                                </td>


                                <!-- Actions -->

                                <td class="text-right">

                                    <div class="sk-table-actions">

                                        <!-- View -->

                                        <a
                                            href="<?php echo URL::site(
                                                'survey/view/'.$survey['id']
                                            ); ?>"
                                            class="sk-icon-action"
                                            title="View Survey"
                                            aria-label="View Survey"
                                        >
                                            <span class="glyphicon glyphicon-eye-open"></span>
                                        </a>


                                        <!-- Schedule -->

                                        <a
                                            href="<?php echo URL::site(
                                                'survey/schedule/'.$survey['id']
                                            ); ?>"
                                            class="sk-icon-action"
                                            title="Schedule Survey"
                                            aria-label="Schedule Survey"
                                        >
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>

</div>