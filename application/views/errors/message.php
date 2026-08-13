<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="container">

    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">

            <div class="panel panel-default text-center">

                <div class="panel-body" style="padding: 50px 30px;">

                    <div
                        style="
                            font-size: 60px;
                            color: #999;
                            margin-bottom: 20px;
                        "
                    >
                        <span class="glyphicon glyphicon-exclamation-sign"></span>
                    </div>

                    <h2>
                        <?php
                        echo HTML::chars(
                            isset($title)
                                ? $title
                                : 'Something Went Wrong'
                        );
                        ?>
                    </h2>

                    <p
                        class="text-muted"
                        style="
                            margin-top: 15px;
                            font-size: 15px;
                        "
                    >
                        <?php
                        echo HTML::chars(
                            isset($message)
                                ? $message
                                : 'The requested page could not be found.'
                        );
                        ?>
                    </p>

                    <div style="margin-top: 30px;">

                        <a
                            href="<?php echo URL::site(); ?>"
                            class="btn btn-primary"
                        >
                            <span class="glyphicon glyphicon-home"></span>
                            Go to Home
                        </a>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>