<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="sk-page-header">
    <div>
        <h1>Dashboard</h1>
    </div>
</div>

<!-- Statistics -->
<div class="row sk-stat-row">
    <div class="col-sm-6 col-md-3">
        <div class="sk-stat-card">
            <div class="sk-stat-icon">
                <span class="glyphicon glyphicon-list-alt"></span>
            </div>

            <div class="sk-stat-content">
                <span class="sk-stat-label">
                    Total Surveys
                </span>
                <strong>
                    <?php echo (int) $total_surveys; ?>
                </strong>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
        <div class="sk-stat-card">
            <div class="sk-stat-icon">
                <span class="glyphicon glyphicon-ok-circle"></span>
            </div>
            <div class="sk-stat-content">
                <span class="sk-stat-label">
                    Published
                </span>
                <strong>
                    <?php echo (int) $published_surveys; ?>
                </strong>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
        <div class="sk-stat-card">
            <div class="sk-stat-icon">
                <span class="glyphicon glyphicon-edit"></span>
            </div>
            <div class="sk-stat-content">
                <span class="sk-stat-label">
                    Drafts
                </span>
                <strong>
                    <?php echo (int) $draft_surveys; ?>
                </strong>
            </div>
        </div>
    </div>
</div>

<!-- Surveys -->
<div class="sk-section-header">
    <div>
        <h2>My Surveys</h2>
        <p>
            Manage your surveys.
        </p>
    </div>
</div>

<div class="row">

    <?php foreach ($surveys as $survey): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="sk-survey-card">
                <div class="sk-survey-card-header">
                    <span class="sk-survey-icon">
                        <span class="glyphicon glyphicon-list-alt"></span>
                    </span>
                    <?php
                    $status_class = 'sk-status-' . $survey['status'];
                    ?>
                    <span class="sk-status <?php echo HTML::chars($status_class); ?>">
                        <?php echo HTML::chars(ucfirst($survey['status'])); ?>
                    </span>
                </div>


                <div class="sk-survey-card-body">
                    <h3>
                        <?php echo HTML::chars($survey['title']); ?>
                    </h3>
                    <p class="sk-survey-description">
                        <?php echo HTML::chars($survey['description']); ?>
                    </p>
                    <div class="sk-survey-card-meta">
                        <div class="sk-survey-stats">
                            <span>
                                <strong><?php echo (int) $survey['questions']; ?></strong>
                                Questions
                            </span>
                        </div>
                        <div class="sk-survey-actions">
                            <!-- View -->
                            <a
                                href="/survey/view/<?php echo (int) $survey['id']; ?>"
                                class="sk-icon-action"
                                title="View Survey"
                                aria-label="View Survey"
                            >
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>

                            <!-- Edit -->
                            <a
                                href="/survey/edit/<?php echo (int) $survey['id']; ?>"
                                class="sk-icon-action"
                                title="Edit Survey"
                                aria-label="Edit Survey"
                            >
                                <span class="glyphicon glyphicon-pencil"></span>
                            </a>

                            <!-- Public View -->
                            <a href="<?php echo 'survey/' . (int) $survey['id']; ?>"
                                class="sk-icon-action"
                                title="Public Survey Link"
                                aria-label="Public Survey Link"
                                target="_blank"
                            >
                                <span class="glyphicon glyphicon-share"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>