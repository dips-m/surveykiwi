<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php
$content = View::factory('dashboard/index_content');

$content->surveys = $surveys;
$content->total_surveys = $total_surveys;
$content->published_surveys = $published_surveys;
$content->draft_surveys = $draft_surveys;
$content->closed_surveys = $closed_surveys;

$layout = View::factory('layout/master');

$layout->title = 'Dashboard - SurveyKiwi';
$layout->content = $content;

echo $layout;
?>