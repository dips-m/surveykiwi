<?php defined('SYSPATH') or die('No direct script access.'); ?>

<header class="sk-header">

    <div class="sk-brand">
        <a href="/">
            <span class="sk-logo-mark">S</span>
            <span class="sk-logo-text">SurveyKiwi</span>
        </a>
    </div>

    <div class="sk-header-right">

        <button
            type="button"
            class="btn btn-link sk-mobile-menu"
            data-toggle="collapse"
            data-target="#sk-sidebar"
        >
            <span class="glyphicon glyphicon-menu-hamburger"></span>
        </button>

        <div class="dropdown">

            <a
                href="#"
                class="dropdown-toggle sk-user"
                data-toggle="dropdown"
                role="button"
            >
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
                    <a href="#">
                        <span class="glyphicon glyphicon-log-out"></span>
                        Logout
                    </a>
                </li>

            </ul>

        </div>

    </div>

</header>