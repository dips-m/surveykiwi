<?php defined('SYSPATH') or die('No direct script access.'); ?>

<aside
    id="sk-sidebar"
    class="sk-sidebar collapse in"
>

    <nav>

        <div class="sk-nav-section">
            Workspace
        </div>

        <ul class="nav sk-nav">

            <li class="active">
                <a href="/">
                    <span class="glyphicon glyphicon-dashboard"></span>
                    Dashboard
                </a>
            </li>

            <li>
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