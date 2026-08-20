<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="container-fluid">

    <div class="row">

        <div class="col-sm-12">

            <div class="survey-not-found-panel">

                <div class="text-center">

                    <span class="glyphicon glyphicon-warning-sign survey-not-found-icon"></span>

                    <h3>Survey Not Found</h3>

                    <p class="text-muted">
                        The survey you're trying to edit doesn't exist or may have been deleted.
                    </p>

                    
                    <a href="<?php echo URL::site('survey'); ?>"
                        class="btn btn-primary"
                    >
                        <span class="glyphicon glyphicon-arrow-left"></span>
                        Back to Surveys
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<style>
    .survey-not-found-panel {
        max-width: 480px;
        margin: 60px auto;
        padding: 40px 30px;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .survey-not-found-icon {
        font-size: 42px;
        color: #d9534f;
        margin-bottom: 15px;
        display: inline-block;
    }

    .survey-not-found-panel h3 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #333;
    }

    .survey-not-found-panel p {
        margin-bottom: 25px;
    }

    .survey-not-found-panel .btn .glyphicon {
        margin-right: 5px;
    }
</style>