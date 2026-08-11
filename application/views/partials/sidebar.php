<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php
$current_controller = strtolower(
    Request::current()->controller()
);
?>

<aside
    id="sk-sidebar"
    class="sk-sidebar collapse in"
>

    <nav>

        <div class="sk-nav-section">
            Workspace
        </div>

        <ul class="nav sk-nav">

            <li class="<?php echo ($current_controller === 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo URL::site('dashboard'); ?>">
                    <span class="glyphicon glyphicon-dashboard"></span>
                    Dashboard
                </a>
            </li>

            <li class="<?php echo ($current_controller === 'survey') ? 'active' : ''; ?>">
                <a href="<?php echo URL::site('survey'); ?>">
                    <span class="glyphicon glyphicon-list-alt"></span>
                    Surveys
                </a>
            </li>

        </ul>

    </nav>

    <div class="sk-sidebar-footer">

        <span class="glyphicon glyphicon-info-sign"></span>

        <span>
            SurveyKiwi
        </span>

        <small>
            Assessment Edition
        </small>

    </div>

</aside>