<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Login - SurveyKiwi</title>

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

    <div
        class="row"
        style="margin-top: 100px;"
    >

        <div
            class="col-sm-6 col-sm-offset-3
                   col-md-4 col-md-offset-4"
        >

            <div class="panel panel-default">

                <div class="panel-heading text-center">

                    <h3 style="margin: 10px 0;">
                        SurveyKiwi
                    </h3>

                    <p class="text-muted">
                        Admin Login
                    </p>

                </div>

                <div class="panel-body">

                    <?php if ( ! empty($errors['login'])): ?>

                        <div class="alert alert-danger">

                            <?php
                            echo HTML::chars(
                                $errors['login']
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                    <form
                        method="post"
                        action="<?php
                        echo URL::site('login');
                        ?>"
                    >

                        <div class="form-group">

                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?php
                                echo HTML::chars($email);
                                ?>"
                                autocomplete="username"
                                required
                            >

                            <?php
                            if (
                                ! empty(
                                    $errors['email']
                                )
                            ):
                            ?>

                                <span class="help-block">
                                    <?php
                                    echo HTML::chars(
                                        $errors['email']
                                    );
                                    ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="form-group">

                            <label for="password">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                autocomplete="current-password"
                                required
                            >

                            <?php
                            if (
                                ! empty(
                                    $errors['password']
                                )
                            ):
                            ?>

                                <span class="help-block">
                                    <?php
                                    echo HTML::chars(
                                        $errors['password']
                                    );
                                    ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary btn-block"
                        >
                            Login
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>