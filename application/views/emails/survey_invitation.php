<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo HTML::chars($survey['title']); ?>
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f4f7fb;
        font-family: Arial, Helvetica, sans-serif;
        color: #333333;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #f4f7fb;
        padding: 40px 15px;
    "
>
    <tr>
        <td align="center">

            <!-- Email Container -->
            <table
                width="600"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 600px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    border: 1px solid #e5e7eb;
                "
            >

                <!-- Header -->
                <tr>
                    <td
                        style="
                            background-color: #337ab7;
                            padding: 25px 30px;
                            text-align: center;
                        "
                    >
                        <div
                            style="
                                font-size: 26px;
                                font-weight: bold;
                                color: #ffffff;
                                letter-spacing: 0.5px;
                            "
                        >
                            SurveyKiwi
                        </div>

                        <div
                            style="
                                margin-top: 6px;
                                font-size: 13px;
                                color: #eaf3fb;
                            "
                        >
                            Your feedback matters
                        </div>
                    </td>
                </tr>


                <!-- Content -->
                <tr>
                    <td
                        style="
                            padding: 35px 40px;
                        "
                    >

                        <p
                            style="
                                margin: 0 0 20px 0;
                                font-size: 16px;
                                line-height: 1.6;
                                color: #333333;
                            "
                        >
                            Hello
                            <strong>
                                <?php
                                echo HTML::chars(
                                    $participant_name
                                );
                                ?>
                            </strong>,
                        </p>


                        <p
                            style="
                                margin: 0 0 20px 0;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            You have been invited to participate
                            in the following survey:
                        </p>


                        <!-- Survey Box -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin: 25px 0;
                                background-color: #f8fafc;
                                border-left: 4px solid #337ab7;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 18px 20px;
                                    "
                                >

                                    <div
                                        style="
                                            font-size: 19px;
                                            font-weight: bold;
                                            color: #333333;
                                            line-height: 1.4;
                                        "
                                    >
                                        <?php
                                        echo HTML::chars(
                                            $survey['title']
                                        );
                                        ?>
                                    </div>

                                    <?php if (
                                        ! empty(
                                            $survey['description']
                                        )
                                    ): ?>

                                        <div
                                            style="
                                                margin-top: 10px;
                                                font-size: 14px;
                                                line-height: 1.6;
                                                color: #666666;
                                            "
                                        >
                                            <?php
                                            echo HTML::chars(
                                                $survey['description']
                                            );
                                            ?>
                                        </div>

                                    <?php endif; ?>

                                </td>
                            </tr>
                        </table>


                        <p
                            style="
                                margin: 0 0 25px 0;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            We would appreciate your feedback.
                            Please click the button below to
                            complete the survey.
                        </p>


                        <!-- CTA Button -->
                        <table
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            align="center"
                            style="margin: 30px auto;"
                        >
                            <tr>
                                <td
                                    align="center"
                                    style="
                                        background-color: #337ab7;
                                        border-radius: 5px;
                                    "
                                >
                                    <a
                                        href="<?php
                                        echo HTML::chars(
                                            $survey_url
                                        );
                                        ?>"
                                        style="
                                            display: inline-block;
                                            padding: 13px 28px;
                                            font-size: 15px;
                                            font-weight: bold;
                                            color: #ffffff;
                                            text-decoration: none;
                                            border-radius: 5px;
                                        "
                                    >
                                        Take Survey
                                    </a>
                                </td>
                            </tr>
                        </table>


                        <p
                            style="
                                margin: 25px 0 0 0;
                                font-size: 14px;
                                line-height: 1.6;
                                color: #777777;
                            "
                        >
                            Thank you for taking the time to
                            share your feedback.
                        </p>


                        <p
                            style="
                                margin: 20px 0 0 0;
                                font-size: 14px;
                                line-height: 1.6;
                                color: #555555;
                            "
                        >
                            Regards,<br>
                            <strong>SurveyKiwi</strong>
                        </p>

                    </td>
                </tr>


                <!-- Footer -->
                <tr>
                    <td
                        style="
                            padding: 20px 30px;
                            background-color: #f8fafc;
                            border-top: 1px solid #e5e7eb;
                            text-align: center;
                        "
                    >

                        <p
                            style="
                                margin: 0;
                                font-size: 12px;
                                line-height: 1.5;
                                color: #999999;
                            "
                        >
                            This email was sent by SurveyKiwi
                            regarding a survey invitation.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>

</html>