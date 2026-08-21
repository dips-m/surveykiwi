<?php defined('SYSPATH') or die('No direct script access.'); ?>

<header class="sk-header">
    <div class="sk-brand">
        <a href="/">
            <span class="sk-logo-mark">S</span>
            <span class="sk-logo-text">SurveyKiwi</span>
        </a>
    </div>

    <button type="button" class="btn btn-link sk-mobile-menu" data-toggle="collapse" data-target="#sk-sidebar">
        <span class="glyphicon glyphicon-menu-hamburger"></span>
    </button>

    <div class="sk-header-right">
        <div class="sk-header-action">
            <a href="<?php echo URL::site('survey/create'); ?>" class="btn btn-primary btn-sm">
                <span class="glyphicon glyphicon-plus sk-create-icon"></span>
                <span class="sk-create-text">Create Survey</span>
            </a>
        </div>

        <div class="dropdown">
            <a href="#" class="dropdown-toggle sk-user" data-toggle="dropdown" role="button">
                <span class="sk-avatar">A</span>
                <span class="sk-user-name">
                    Admin
                </span>
                <span class="caret"></span>
            </a>

            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a href="#">
                        <span class="glyphicon glyphicon-user"></span>
                        Profile
                    </a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="<?php echo URL::site('logout'); ?>">
                        <span class="glyphicon glyphicon-log-out"></span>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
