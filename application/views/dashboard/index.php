<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php
echo View::factory('dashboard/index_content')
    ->set('surveys', $surveys)
    ->set('total_surveys', $total_surveys)
    ->set('published_surveys', $published_surveys)
    ->set('draft_surveys', $draft_surveys)
    ->set('closed_surveys', $closed_surveys);
?>