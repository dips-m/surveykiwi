<?php defined('SYSPATH') or die('No direct script access.'); 
    $errors = Session::instance()->get_once('form_errors', array());
    $old = Session::instance()->get_once('form_data', array());

    $freq_val = isset($old['frequency']) ? $old['frequency'] : (isset($schedule['frequency']) ? $schedule['frequency'] : '');
    $start_val = isset($old['start_date']) ? $old['start_date'] : (isset($schedule['start_date']) ? date('Y-m-d\TH:i', strtotime($schedule['start_date'])) : '');
    $end_val = isset($old['end_date']) ? $old['end_date'] : (isset($schedule['end_date']) ? date('Y-m-d\TH:i', strtotime($schedule['end_date'])) : '');
    $active_val = isset($old['is_active']) ? $old['is_active'] : (!isset($schedule['is_active']) || $schedule['is_active'] == 1);
    $remind_val = isset($old['reminders_enabled']) ? $old['reminders_enabled'] : (isset($schedule['reminders_enabled']) && $schedule['reminders_enabled'] == 1);

?>


<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1>Survey Schedule</h1>
            <p class="text-muted">Manage your schedules.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">

        <!-- Flash Error / Success Messages -->
        <?php if ($success = Session::instance()->get_once('flash_success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo HTML::chars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error = Session::instance()->get_once('flash_error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo HTML::chars($error); ?>
            </div>
        <?php endif; ?>

        <div id="alert-container"></div>

        <!-- PANEL 1: Schedule Configuration & Participants Form -->
        <div class="panel panel-default" style="border-radius: 8px; border-color: #e2e8f0;">
            <div class="panel-body" style="padding: 25px 20px;">
                <p class="text-muted" style="margin-bottom: 20px;">Survey: <strong><?php echo HTML::chars($survey['title']); ?></strong></p>
                <hr/>

                <!-- Direct Non-AJAX Schedule Form -->
                <form id="scheduleForm" action="<?php echo URL::site('schedule/save/' . $survey['id']); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start;">
                    <input type="hidden" id="schedule_id" name="schedule_id" value="<?php echo !empty($schedule['id']) ? (int) $schedule['id'] : 0; ?>">
                        <!-- Frequency Selection -->
                        <div id="group-frequency" class="col-md-4" style="padding-right: 10px; padding-left: 10px;">
                            <label for="frequency" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" id="frequency" class="form-control" style="border-radius: 6px;">
                                <option value="">Select</option>
                                <option value="once" <?php echo ($freq_val === 'once') ? 'selected' : ''; ?>>Once (Non-recurring)</option>
                                <option value="daily" <?php echo ($freq_val === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo ($freq_val === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo ($freq_val === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                <option value="quarterly" <?php echo ($freq_val === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                            </select>
                            <span id="err-frequency" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none; width: 100%;"></span>
                        </div>

                        <!-- Start Date Configuration -->
                        <div id="group-start-date" class="col-md-4" style="padding-right: 10px; padding-left: 10px;">
                            <label for="start_date" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Start date <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="form-control" style="border-radius: 6px;" 
                                value="<?php echo HTML::chars($start_val); ?>" placeholder="Select Date">
                            <span id="err-start-date" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none; width: 100%;"></span>
                        </div>

                        <!-- End Date Configuration -->
                        <div id="group-end-date" class="col-md-4" style="padding-right: 10px; padding-left: 10px;">
                            <label for="end_date" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">End date <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" id="end_date" class="form-control" style="border-radius: 6px;" 
                                value="<?php echo HTML::chars($end_val); ?>" placeholder="Select Date">
                            <span id="err-end-date" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none; width: 100%;"></span>
                        </div>

                    </div>

                    <!-- Participants / Status / Reminders -->
                    <div class="row"
                        style="display: flex; flex-wrap: wrap; align-items: flex-start; margin-top: 15px;">

                        <!-- Participants -->
                        <div id="group-participants-file"
                            class="col-md-4"
                            style="padding-right: 10px; padding-left: 10px;">

                            <label for="participants_file"
                                class="control-label"
                                style="
                                    font-size: 13px;
                                    font-weight: 500;
                                    color: #475569;
                                    margin-bottom: 6px;
                                ">

                                Participants
                                <span class="text-muted"
                                    style="font-weight: 400;">
                                    (CSV)
                                </span>

                            </label>

                            <div style="
                                display: flex;
                                align-items: center;
                                width: 100%;
                            ">

                                <label class="btn btn-default"
                                    style="
                                        background-color: #ffffff;
                                        border-color: #cbd5e1;
                                        font-weight: 500;
                                        color: #334155;
                                        border-radius: 6px;
                                        margin-bottom: 0;
                                        cursor: pointer;
                                        white-space: nowrap;
                                    ">

                                    <i class="glyphicon glyphicon-folder-open"></i>
                                    Choose CSV

                                    <input type="file"
                                        id="participants_file"
                                        name="participants_file"
                                        accept=".csv"
                                        style="display: none;"
                                        onchange="
                                            document.getElementById('file-chosen-name').textContent =
                                            this.files[0] ? this.files[0].name : 'No file chosen';
                                        ">

                                </label>

                                <span id="file-chosen-name"
                                    class="text-muted"
                                    style="
                                        font-size: 12px;
                                        margin-left: 8px;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                    ">
                                    No file chosen
                                </span>

                            </div>

                            <?php if (count($participants) > 0): ?>
                                <span class="text-warning"
                                    style="font-size:12px; display:block; margin-top:6px;">
                                    <i class="glyphicon glyphicon-warning-sign"></i>
                                    Note: Uploading a new CSV will delete the existing
                                    <?php echo count($participants); ?> participants.
                                </span>
                            <?php endif; ?>

                            <span id="err-participants-file"
                                class="help-block text-danger"
                                style="
                                    font-size: 12px;
                                    margin-top: 4px;
                                    margin-bottom: 0;
                                    display: none;
                                ">
                            </span>

                        </div>


                        <!-- Status -->
                        <div class="col-md-4"
                            style="
                                padding-right: 10px;
                                padding-left: 10px;
                                padding-top: 5px;
                            ">

                            <label class="control-label"
                                style="
                                    font-size: 13px;
                                    font-weight: 500;
                                    color: #475569;
                                    margin-bottom: 6px;
                                    display: block;
                                ">
                                Status
                            </label>

                            <div class="checkbox form-control"
                                style="margin-top: 0;">

                                <label style="
                                    font-weight: 500;
                                    color: #1e293b;
                                    font-size: 13px;
                                ">

                                    <input type="checkbox"
                                        name="is_active"
                                        value="1"
                                        <?php echo $active_val ? 'checked' : ''; ?>>

                                    Active schedule

                                </label>

                            </div>

                        </div>


                        <!-- Reminders -->
                        <div class="col-md-4"
                            style="
                                padding-right: 10px;
                                padding-left: 10px;
                                padding-top: 5px;
                            ">

                            <label class="control-label"
                                style="
                                    font-size: 13px;
                                    font-weight: 500;
                                    color: #475569;
                                    margin-bottom: 6px;
                                    display: block;
                                ">
                                Reminders
                            </label>

                            <div class="checkbox form-control"
                                style="
                                    margin-top: 0;
                                    margin-bottom: 6px;
                                ">

                                <label style="
                                    font-weight: 500;
                                    color: #1e293b;
                                    font-size: 13px;
                                ">

                                    <input type="checkbox"
                                        name="reminders_enabled"
                                        value="1"
                                        <?php echo $remind_val ? 'checked' : ''; ?>>

                                    Alert creator 1 hour before survey

                                </label>

                            </div>

                        </div>

                    </div>

                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start; margin-top: 15px;">
                        <!-- Submit Button -->
                        <div class="col-md-12 text-right" style="padding-top: 28px;">
                            <button type="submit" class="btn btn-primary" style="background-color: #26a69a; border-color: #26a69a; padding: 8px 24px; font-weight: 500; border-radius: 6px; ">
                                Save Schedule
                            </button>
                        </div>

                    </div>
                </form>

                <hr style="margin-top: 30px; margin-bottom: 20px;">
            </div>
        </div>

    </div>
</div>

<!-- Schedule & Participants -->
<div class="row">

    <!-- =========================
         UPCOMING SCHEDULES
    ========================== -->
    <div class="col-md-6">

        <div class="panel panel-default"
             style="border-radius: 8px; border-color: #e2e8f0; box-shadow: none; height: auto; min-height: 655px;">

            <!-- Header -->
            <div class="panel-heading"
                 style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0; padding: 14px 16px;">

                <div style="display: flex; align-items: center; justify-content: space-between;">

                    <div>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #334155;">
                            <i class="glyphicon glyphicon-time"
                               style="margin-right: 6px; color: #26a69a;"></i>
                            Schedules
                        </h4>

                        <small class="text-muted">
                            <?php echo count($scheduleDates); ?> scheduled occurrence(s)
                        </small>
                    </div>

                    <div>
                        <label for="schedule-filter" style="margin: 0 6px 0 0; font-size: 12px; color: #64748b;">Show:</label>
                        <select id="schedule-filter" class="form-control input-sm" style="display: inline-block; width: 100px;">
                            <option value="all" selected>All</option>
                            <option value="future">Future</option>
                            <option value="past">Past</option>
                        </select>
                    </div>


                </div>

            </div>

            <!-- Body -->
            <div class="panel-body" style="padding: 0;">

                <?php if (!empty($scheduleDates)): ?>

                    <div class="table-responsive">
                        <table class="table table-hover"
                               style="margin-bottom: 0; font-size: 13px;">

                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="width: 80px; padding: 10px 15px; color: #64748b;">
                                        #
                                    </th>

                                    <th style="padding: 10px 15px; color: #64748b;">
                                        Start Date
                                    </th>

                                    <th style="padding: 10px 15px; color: #64748b;">
                                        Start Time
                                    </th>

                                    <th style="padding: 10px 15px; color: #64748b;">
                                        End Time
                                    </th>
                                    <th style="padding: 10px 15px; color: #64748b;">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="schedule-list">

                                <?php foreach ($scheduleDates as $index => $scheduleValue): ?>

                                    <tr class="schedule-row" data-status="<?php echo HTML::chars($scheduleValue['status']); ?>">

                                        <td class="schedule-index" style="padding: 11px 15px; color: #64748b;"></td>

                                        <td style="padding: 11px 15px;">
                                            <strong style="color: #334155;">
                                                <?php
                                                echo date(
                                                    'd M Y',
                                                    strtotime($scheduleValue['start_date'])
                                                );
                                                ?>
                                            </strong>
                                        </td>

                                        <td style="padding: 11px 15px;">
                                            <?php
                                            echo date(
                                                'h:i A',
                                                strtotime($scheduleValue['start_date'])
                                            );
                                            ?>
                                        </td>

                                        <td style="padding: 11px 15px;">
                                            <?php
                                            echo date(
                                                'h:i A',
                                                strtotime($scheduleValue['end_date'])
                                            );
                                            ?>
                                        </td>
                                        <td style="padding: 11px 15px;">
                                            <?php if ($scheduleValue['status'] === 'future'): ?>
                                                <span class="label label-info">Future</span>
                                            <?php else: ?>
                                                <span class="label label-default">Past</span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>
                    </div>

                    <!-- Schedule Pagination -->
                    <div id="schedule-pagination"
                         style="text-align: center; padding: 10px 15px; border-top: 1px solid #e2e8f0;">
                    </div>

                <?php else: ?>

                    <div style="padding: 100px 20px; text-align: center;">

                        <i class="glyphicon glyphicon-calendar"
                           style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px;"></i>

                        <p style="margin: 0; color: #64748b; font-size: 13px;">
                            No upcoming schedules.
                        </p>

                        <small class="text-muted">
                            Save a valid schedule configuration to generate execution dates.
                        </small>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <!-- =========================
         PARTICIPANTS
    ========================== -->
    <div class="col-md-6">
        <div class="panel panel-default"
             style="border-radius: 8px; border-color: #e2e8f0; box-shadow: none; height: auto; min-height: 655px;">
            <!-- Header -->
            <div class="panel-heading"
                 style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 8px 8px 0 0; padding: 14px 16px;">

                <div style="display: flex; align-items: center; justify-content: space-between;">

                    <div>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #334155;">
                            <i class="glyphicon glyphicon-user"
                               style="margin-right: 6px; color: #26a69a;"></i>
                            Participants
                        </h4>

                        <small class="text-muted">
                            Uploaded participants
                        </small>
                    </div>

                    <span class="label label-success"
                          style="font-size: 11px; padding: 5px 8px;">
                        <span id="total-participants-count">
                            <?php echo count($participants); ?>
                        </span>
                        Total
                    </span>

                </div>


            </div>
            <!-- Body -->
            <div class="panel-body" style="padding: 0;">
                    <!-- Add Participant -->
                <div style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">

                    <form id="add-participant-form"
                        class="form-inline"
                        style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">

                            <input
                                type="hidden"
                                name="survey_id"
                                value="<?= (int) $survey['id'] ?>"
                            >

                        <input
                            type="text"
                            name="first_name"
                            id="participant-first-name"
                            class="form-control input-field"
                            placeholder="First Name"
                            required
                            style="width: 160px;"
                        >

                        <input
                            type="text"
                            name="last_name"
                            id="participant-last-name"
                            class="form-control input-field"
                            placeholder="Last Name"
                            required
                            style="width: 160px;"
                        >

                        <input
                            type="email"
                            name="email"
                            id="participant-email"
                            class="form-control input-field"
                            placeholder="Email"
                            required
                            style="width: 240px;"
                        >

                        <button
                            type="submit"
                            id="add-participant-btn"
                            class="dt-button"
                            title="Add Participant"
                            style="display: inline-flex; align-items: center; justify-content: center;"
                        >
                            Add
                        </button>

                    </form>

                    <div
                        id="participant-add-message"
                        style="margin-top: 6px; font-size: 12px;"
                    ></div>
                </div>

                <?php if (!empty($participants)): ?>

                    <div class="table-responsive">
                        <table class="table table-hover"
                               style="margin-bottom: 0; font-size: 13px;">

                            <thead>
                                <tr style="background: #f8fafc;">

                                    <th style="width: 55px; padding: 10px 12px; color: #64748b;">
                                        #
                                    </th>

                                    <th style="padding: 10px 12px; color: #64748b;">
                                        First Name
                                    </th>

                                    <th style="padding: 10px 12px; color: #64748b;">
                                        Last Name
                                    </th>

                                    <th style="padding: 10px 12px; color: #64748b;">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-slate-600">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="participants-list">

                                <?php foreach ($participants as $index => $p): ?>

                                    <tr
                                        class="participant-row"
                                        data-participant-id="<?php echo (int) $p['id']; ?>"
                                        data-survey-id="<?php echo (int)  $survey['id']; ?>"
                                    >

                                        <td style="padding: 10px 12px; color: #94a3b8;">
                                            <?php echo ($index + 1); ?>
                                        </td>

                                        <!-- First Name -->
                                        <td style="padding: 10px 12px; color: #334155;">

                                            <span class="participant-first-name-text">
                                                <?php echo HTML::chars($p['first_name']); ?>
                                            </span>

                                            <input
                                                type="text"
                                                class="form-control participant-first-name-input hidden"
                                                value="<?php echo HTML::chars($p['first_name']); ?>"
                                            >

                                        </td>

                                        <!-- Last Name -->
                                        <td style="padding: 10px 12px; color: #334155;">

                                            <span class="participant-last-name-text">
                                                <?php echo HTML::chars($p['last_name']); ?>
                                            </span>

                                            <input
                                                type="text"
                                                class="form-control participant-last-name-input hidden"
                                                value="<?php echo HTML::chars($p['last_name']); ?>"
                                            >

                                        </td>

                                        <!-- Email -->
                                        <td style="padding: 10px 12px; color: #64748b;">

                                            <span class="participant-email-text">
                                                <?php echo HTML::chars($p['email']); ?>
                                            </span>

                                            <input
                                                type="email"
                                                class="form-control participant-email-input hidden"
                                                value="<?php echo HTML::chars($p['email']); ?>"
                                            >

                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3 text-right whitespace-nowrap">

                                            <!-- Normal actions -->
                                            <div class="participant-actions">

                                                <button
                                                    type="button"
                                                    class="participant-edit-btn"
                                                >
                                                    <i class="glyphicon glyphicon-pencil"></i>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="participant-delete-btn dt-button"
                                                >
                                                    <i class="glyphicon glyphicon-trash"></i>
                                                </button>

                                            </div>

                                            <!-- Edit actions -->
                                            <div class="participant-edit-actions hidden">

                                                <button
                                                    type="button"
                                                    class="participant-save-btn dt-button"
                                                >
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="participant-cancel-btn dt-button"
                                                >
                                                    <i class="glyphicon glyphicon-remove"></i>
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                    <!-- Participants Pagination -->
                    <div id="participants-pagination"
                         style="text-align: center; padding: 10px 15px; border-top: 1px solid #e2e8f0;">
                    </div>

                <?php else: ?>

                    <div style="padding: 40px 20px; text-align: center;">

                        <i class="glyphicon glyphicon-user"
                           style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px;"></i>

                        <p style="margin: 0; color: #64748b; font-size: 13px;">
                            No participants uploaded yet.
                        </p>

                        <small class="text-muted">
                            Upload a CSV file above to add participants.
                        </small>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<!-- =========== Script ============== -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Load the external JavaScript file -->
<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/schedule-preview.js"></script>

<script>
    window.participantUrls = {
        add: '<?= URL::site('participant/add') ?>',
        edit: '<?= URL::site('participant/edit') ?>',
        delete: '<?= URL::site('participant/delete') ?>'
    };
</script>

<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/participant.js"></script>

<!-- =========================
     PAGINATION
========================== -->
<script>
    $(document).ready(function() {

        var itemsPerPage = 10;


        /**
         * Generic pagination function
         */
        function setupPagination(rowSelector, paginationSelector) {

            var $rows = $(rowSelector);
            var $pagination = $(paginationSelector);

            var totalItems = $rows.length;
            var totalPages = Math.ceil(totalItems / itemsPerPage);

            if (totalPages <= 1) {
                $pagination.hide();
                $rows.show();
                return;
            }

            var currentPage = 1;


            function renderPage(page) {

                currentPage = page;

                var start = (page - 1) * itemsPerPage;
                var end = start + itemsPerPage;

                $rows.hide();

                $rows.slice(start, end).show();

                renderPagination();
            }


            function renderPagination() {

                var html = '';

                html += '<ul class="pagination pagination-sm" style="margin: 0;">';


                // Previous
                if (currentPage === 1) {

                    html += '<li class="disabled">';
                    html += '<a href="javascript:void(0);">&laquo;</a>';
                    html += '</li>';

                } else {

                    html += '<li>';
                    html += '<a href="javascript:void(0);" data-page="' + (currentPage - 1) + '">&laquo;</a>';
                    html += '</li>';

                }


                // Page numbers
                for (var i = 1; i <= totalPages; i++) {

                    if (i === currentPage) {

                        html += '<li class="active">';
                        html += '<a href="javascript:void(0);">' + i + '</a>';
                        html += '</li>';

                    } else {

                        html += '<li>';
                        html += '<a href="javascript:void(0);" data-page="' + i + '">' + i + '</a>';
                        html += '</li>';

                    }

                }


                // Next
                if (currentPage === totalPages) {

                    html += '<li class="disabled">';
                    html += '<a href="javascript:void(0);">&raquo;</a>';
                    html += '</li>';

                } else {

                    html += '<li>';
                    html += '<a href="javascript:void(0);" data-page="' + (currentPage + 1) + '">&raquo;</a>';
                    html += '</li>';

                }

                html += '</ul>';

                $pagination.html(html);
            }


            $pagination.on('click', 'a[data-page]', function(e) {

                e.preventDefault();

                var page = parseInt($(this).attr('data-page'), 10);

                if (page >= 1 && page <= totalPages) {
                    renderPage(page);
                }

            });


            renderPage(1);
        }


        // Schedule pagination
        setupPagination(
            '#schedule-list .schedule-row',
            '#schedule-pagination'
        );


        // Participants pagination
        setupPagination(
            '#participants-list .participant-row',
            '#participants-pagination'
        );

    });
</script>