<?php defined('SYSPATH') or die('No direct script access.'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?php echo isset($title) ? HTML::chars($title) : 'SurveyKiwi'; ?>
    </title>

    <!-- Bootstrap 3 -->
    <link
        rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
    >

    <!-- SurveyKiwi CSS -->
    <link
        rel="stylesheet"
        href="/assets/css/app.css"
    >
</head>

<body>

<div class="sk-app">

    <?php echo View::factory('partials/header'); ?>

    <div class="sk-body">

        <?php echo View::factory('partials/sidebar'); ?>

        <main class="sk-content">
            <?php echo $content; ?>
        </main>

    </div>

</div>

<!-- jQuery -->
<script
    src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js">
</script>

<!-- Bootstrap 3 -->
<script
    src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js">
</script>

<script src="/assets/js/app.js"></script>

</body>
</html>