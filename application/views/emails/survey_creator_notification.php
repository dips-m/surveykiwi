<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>
        Survey Starting Soon
    </title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
">

    <div style="
        max-width: 600px;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    ">

        <div style="
            background-color: #337ab7;
            color: #ffffff;
            padding: 20px 30px;
        ">

            <h2 style="
                margin: 0;
                font-size: 22px;
            ">
                SurveyKiwi
            </h2>

        </div>

        <div style="
            padding: 30px;
            color: #333333;
        ">

            <h3 style="
                margin-top: 0;
                color: #333333;
            ">
                Your survey is starting soon
            </h3>

            <p>
                Your survey is scheduled to start within the
                next hour.
            </p>

            <div style="
                background-color: #f8f9fa;
                border-left: 4px solid #337ab7;
                padding: 15px 20px;
                margin: 20px 0;
            ">

                <p style="margin: 0 0 8px 0;">
                    <strong>Survey:</strong>
                    <?php
                    echo HTML::chars(
                        $survey['title']
                    );
                    ?>
                </p>

                <p style="margin: 0;">
                    <strong>Start time:</strong>
                    <?php
                    echo HTML::chars(
                        $survey['start_date']
                    );
                    ?>
                </p>

            </div>

            <?php if ( ! empty($survey['description'])): ?>

                <p>
                    <?php
                    echo HTML::chars(
                        $survey['description']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <p>
                The scheduled survey will be available
                to participants at the scheduled start time.
            </p>

            <p style="margin-top: 30px;">
                Thank you,<br>
                <strong>SurveyKiwi</strong>
            </p>

        </div>

        <div style="
            padding: 15px 30px;
            background-color: #f8f9fa;
            color: #777777;
            font-size: 12px;
        ">

            This is an automated notification from SurveyKiwi.

        </div>

    </div>

</body>

</html>