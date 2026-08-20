<?php defined('SYSPATH') or die('No direct script access.');
    $errors = Session::instance()->get_once('form_errors', array());
    $old = Session::instance()->get_once('form_data', array());

    $freq_val = isset($old['frequency']) ? $old['frequency'] : (isset($schedule['frequency']) ? $schedule['frequency'] : '');
    $start_date_val = isset($old['start_date']) ? $old['start_date'] : (isset($schedule['start_date']) ? date('Y-m-d', strtotime($schedule['start_date'])) : '');
    $end_date_val = isset($old['end_date']) ? $old['end_date'] : (isset($schedule['end_date']) ? date('Y-m-d', strtotime($schedule['end_date'])) : '');
    $start_time_val = isset($old['start_time']) ? $old['start_time'] : (isset($schedule['start_time']) ? date('H:i', strtotime($schedule['start_time'])) : '');
    $end_time_val = isset($old['end_time']) ? $old['end_time'] : (isset($schedule['end_time']) ? date('H:i', strtotime($schedule['end_time'])) : '');
    $recurrence_rule_val = isset($old['recurrence_rule_id']) ? $old['recurrence_rule_id'] : (isset($schedule['recurrence_rule_id']) ? $schedule['recurrence_rule_id'] : '');
    $weekday_val = isset($old['weekday']) ? $old['weekday'] : (isset($schedule['weekday']) ? $schedule['weekday'] : '');
    $month_day_val = isset($old['month_day']) ? $old['month_day'] : (isset($schedule['month_day']) ? $schedule['month_day'] : '');
    $quarter_month_val = isset($old['quarter_month']) ? $old['quarter_month'] : (isset($schedule['quarter_month']) ? $schedule['quarter_month'] : '');
    $active_val = isset($old['is_active']) ? $old['is_active'] : (!isset($schedule['is_active']) || $schedule['is_active'] == 1);
    $remind_val = isset($old['reminders_enabled']) ? $old['reminders_enabled'] : (isset($schedule['reminders_enabled']) && $schedule['reminders_enabled'] == 1);

    $start_date_past = !empty($start_date_val) && strtotime($start_date_val) <= strtotime(date('Y-m-d'));
    $start_datetime_past = !empty($start_date_val) && !empty($start_time_val) && strtotime($start_date_val . ' ' . $start_time_val) < time();

    $disabled_freq = $start_date_past ? 'disabled' : '';
?>

<div class="sk-page-header">
    <div class="row">
        <div class="col-sm-12">
            <h1><?php echo $is_edit ? 'Edit Survey' : 'Create Survey'; ?></h1>
        </div>
    </div>
</div>

<div id="survey-form" data-is-edit="<?php echo $is_edit ? '1' : '0'; ?>">

    <div class="section-alert" data-role="alert"></div>

    <div id="survey-form-toolbar">
        <button type="button" class="btn btn-link btn-xs" id="expand-all-btn">Expand all</button>
        <button type="button" class="btn btn-link btn-xs" id="collapse-all-btn">Collapse all</button>
    </div>

    <div class="row">
        <!-- 1. Survey Details & Questions (merged, full width) -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-target="#section-details-questions">
                    <div class="panel-heading-row">
                        <strong>Survey Details &amp; Questions</strong>
                        <span class="glyphicon glyphicon-chevron-down"></span>
                    </div>
                </div>
                <div id="section-details-questions" class="panel-collapse">
                    <div class="panel-body">

                        <form id="survey-details-questions-form" method="post" action="<?php echo $save_details_and_questions_url; ?>" novalidate>

                            

                            <input type="hidden" name="csrf_token" value="<?php echo HTML::chars($csrf_token); ?>">
                            <input type="hidden" name="id" value="<?php echo HTML::chars($survey['id']); ?>">

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="survey-title">Title</label>
                                        <input
                                            type="text"
                                            id="survey-title"
                                            name="title"
                                            class="form-control"
                                            maxlength="255"
                                            value="<?php echo HTML::chars($survey['title']); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="survey-status">Status</label>
                                        <select id="survey-status" name="status" class="form-control">
                                            <?php foreach (Model_Survey::statuses() as $status): ?>
                                                <option
                                                    value="<?php echo HTML::chars($status); ?>"
                                                    <?php echo $survey['status'] === $status ? 'selected' : ''; ?>
                                                >
                                                    <?php echo HTML::chars(ucfirst($status)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="survey-description">Description</label>
                                        <textarea
                                            id="survey-description"
                                            name="description"
                                            class="form-control"
                                            rows="4"
                                            maxlength="2000"
                                        ><?php echo HTML::chars($survey['description']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-divider">

                            <div id="question-rows">
                                <?php foreach ($questions as $index => $question): ?>
                                    <?php echo View::factory('survey/_question_row')
                                        ->set('index', $index)
                                        ->set('question', $question)
                                        ->set('question_types', $question_types); ?>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" id="add-question-btn" class="btn btn-default btn-sm">
                                <span class="glyphicon glyphicon-plus"></span>
                                Add Question
                            </button>

                            <div class="section-footer">
                                <button type="submit" class="btn btn-primary btn-sm" style="background-color: #2e5490; border-color: #2e5490; padding: 8px 24px; font-weight: 500; border-radius: 6px;">
                                    <?php echo $is_edit ? 'Save Survey' : 'Create Survey'; ?>
                                </button>
                                <span class="text-muted small" data-role="saving-indicator" style="display:none;">Saving&hellip;</span>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-schedule">

        <!-- 3. Survey Schedule (full width) -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-target="#section-schedule">
                    <div class="panel-heading-row">
                        <strong>Survey Schedule</strong>
                        <span class="glyphicon glyphicon-chevron-down"></span>
                    </div>
                </div>
                <div id="section-schedule" class="panel-collapse">

                <div class="row">
    <div class="col-md-12">
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

        <div class="panel panel-default" style="border-radius: 8px; border-color: #e2e8f0;">
            <div class="panel-body" style="padding: 25px 20px;">
                <form id="scheduleForm" action="<?php echo URL::site('schedule/save/' . $survey['id']); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" id="schedule_id" name="schedule_id" value="<?php echo !empty($schedule['id']) ? (int) $schedule['id'] : 0; ?>">

                    <!-- Schedule Configuration -->
                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start;">
                        <div id="group-frequency" class="col-md-3" style="padding-right: 10px; padding-left: 10px;">
                            <label for="frequency" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Frequency <span class="text-danger">*</span></label>
                            <?php if( !empty($freq_val) ): ?>
                                <select name="frequency" id="frequency" class="form-control" style="border-radius: 6px;" <?php echo $disabled_freq ?>>
                                    <option value="">Select</option>
                                    <option value="once" <?php echo ($freq_val === 'once') ? 'selected' : ''; ?>>Once</option>
                                    <option value="daily" <?php echo ($freq_val === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo ($freq_val === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo ($freq_val === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?php echo ($freq_val === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                </select>
                                <input type="hidden" name="frequency" value="<?php echo htmlspecialchars($freq_val); ?>">
                            <?php else: ?>
                                <select name="frequency" id="frequency" class="form-control" style="border-radius: 6px;">
                                    <option value="">Select</option>
                                    <option value="once" <?php echo ($freq_val === 'once') ? 'selected' : ''; ?>>Once</option>
                                    <option value="daily" <?php echo ($freq_val === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo ($freq_val === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo ($freq_val === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?php echo ($freq_val === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                </select>
                            <?php endif; ?>


                            <span id="err-frequency" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-recurrence-rule" class="col-md-3" style="padding-right: 10px; padding-left: 10px; display: none;">
                            <label for="recurrence_rule_id" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Recurrence <span class="text-danger">*</span></label>
                            <select name="recurrence_rule_id" id="recurrence_rule_id" class="form-control" style="border-radius: 6px;"></select>
                            <span id="err-recurrence-rule" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-weekday" class="col-md-3" style="padding-right: 10px; padding-left: 10px; display: none;">
                            <label for="weekday" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Weekday <span class="text-danger">*</span></label>
                            <select name="weekday" id="weekday" class="form-control" style="border-radius: 6px;">
                                <option value="">Select weekday</option>
                                <option value="1" <?php echo ((string) $weekday_val === '1') ? 'selected' : ''; ?>>Monday</option>
                                <option value="2" <?php echo ((string) $weekday_val === '2') ? 'selected' : ''; ?>>Tuesday</option>
                                <option value="3" <?php echo ((string) $weekday_val === '3') ? 'selected' : ''; ?>>Wednesday</option>
                                <option value="4" <?php echo ((string) $weekday_val === '4') ? 'selected' : ''; ?>>Thursday</option>
                                <option value="5" <?php echo ((string) $weekday_val === '5') ? 'selected' : ''; ?>>Friday</option>
                                <option value="6" <?php echo ((string) $weekday_val === '6') ? 'selected' : ''; ?>>Saturday</option>
                                <option value="7" <?php echo ((string) $weekday_val === '7') ? 'selected' : ''; ?>>Sunday</option>
                            </select>
                            <span id="err-weekday" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-quarter-month" class="col-md-3" style="padding-right: 10px; padding-left: 10px; display: none;">
                            <label for="quarter_month" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Quarter Month <span class="text-danger">*</span></label>
                            <select name="quarter_month" id="quarter_month" class="form-control" style="border-radius: 6px;">
                                <option value="">Select month</option>
                                <option value="1" <?php echo ((string) $quarter_month_val === '1') ? 'selected' : ''; ?>>January, April, July, October (Month 1)</option>
                                <option value="2" <?php echo ((string) $quarter_month_val === '2') ? 'selected' : ''; ?>>February, May, August, November (Month 2)</option>
                                <option value="3" <?php echo ((string) $quarter_month_val === '3') ? 'selected' : ''; ?>>March, June, September, December (Month 3)</option>
                            </select>
                            <span id="err-quarter-month" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-month-day" class="col-md-3" style="padding-right: 10px; padding-left: 10px; display: none;">
                            <label for="month_day" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Month Day <span class="text-danger">*</span></label>
                            <select name="month_day" id="month_day" class="form-control" style="border-radius: 6px;">
                                <option value="">Select day</option>
                                <?php for ($day = 1; $day <= 30; $day++): ?>
                                    <option value="<?php echo $day; ?>" <?php echo ((string) $month_day_val === (string) $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span id="err-month-day" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>


                    </div>

                    <!-- Start / End Date & Time -->
                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start; margin-top: 15px;">
                        <div id="group-start-date" class="col-md-3" style="padding-right: 10px; padding-left: 10px;">
                            <label for="start_date" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Start Date <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="form-control" value="<?php echo HTML::chars($start_date_val); ?>" placeholder="Select Date" style="border-radius: 6px;">
                            <span id="err-start-date" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-end-date" class="col-md-3" style="padding-right: 10px; padding-left: 10px;">
                            <label for="end_date" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">End Date <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" id="end_date" class="form-control" value="<?php echo HTML::chars($end_date_val); ?>" placeholder="Select Date" style="border-radius: 6px;">
                            <span id="err-end-date" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-start-time" class="col-md-3" style="padding-right: 10px; padding-left: 10px;">
                            <label for="start_time" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Start Time <span class="text-danger">*</span></label>
                            <input type="text" name="start_time" id="start_time" class="form-control" value="<?php echo HTML::chars($start_time_val); ?>" placeholder="Select Time" style="border-radius: 6px;">
                            <span id="err-start-time" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>

                        <div id="group-end-time" class="col-md-3" style="padding-right: 10px; padding-left: 10px;">
                            <label for="end_time" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">End Time <span class="text-danger">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="form-control" value="<?php echo HTML::chars($end_time_val); ?>" placeholder="Select Time" style="border-radius: 6px;">
                            <span id="err-end-time" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; display: none;"></span>
                        </div>
                    </div>

                    <!-- Participants / Status / Reminders -->
                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start; margin-top: 15px;">

                        <div class="col-md-3" id="group-participants-file" style="padding-right: 10px; padding-left: 10px;">
                            <label for="participants_file" class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Participants <span class="text-muted" style="font-weight: 400;">(CSV)</span></label>

                            <div style="display: flex; align-items: center; width: 100%;">
                                <label class="btn btn-default" style="background-color: #ffffff; border-color: #cbd5e1; font-weight: 500; color: #334155; border-radius: 6px; margin-bottom: 0; cursor: pointer; white-space: nowrap;">
                                    <i class="glyphicon glyphicon-folder-open"></i>
                                    Choose CSV
                                    <input type="file" id="participants_file" name="participants_file" accept=".csv" style="display: none;">
                                </label>

                                <span id="file-chosen-name" class="text-muted" style="font-size: 12px; margin-left: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">No file chosen</span>
                            </div>

                            <?php if (!empty($participants) && count($participants) > 0): ?>
                                <span class="text-warning" style="font-size:12px; display:block; margin-top:6px;">
                                    <i class="glyphicon glyphicon-warning-sign"></i>
                                    Note: Uploading a new CSV will delete the existing <?php echo count($participants); ?> participants.
                                </span>
                            <?php endif; ?>

                            <span id="err-participants-file" class="help-block text-danger" style="font-size: 12px; margin-top: 4px; margin-bottom: 0; display: none;"></span>

                            <div style="margin-top: 6px;">
                                <a href="<?= URL::base(); ?>database/sample/survey_participants-2026.csv" download style="font-size: 12px; color: #2e5490; text-decoration: none;">
                                    <i class="glyphicon glyphicon-download-alt" style="margin-right: 4px;"></i>
                                    Download Sample Participant CSV
                                </a>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3" style="padding-right: 10px; padding-left: 10px; padding-top: 5px;">
                            <label class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; display: block;">Status</label>

                            <div class="checkbox form-control" style="margin-top: 0;">
                                <label style="font-weight: 500; color: #1e293b; font-size: 13px;">
                                    <input type="checkbox" name="is_active" value="1" <?php echo $active_val ? 'checked' : ''; ?>>
                                    Active schedule
                                </label>
                            </div>
                        </div>

                        <!-- Reminders -->
                        <div class="col-md-3" style="padding-right: 10px; padding-left: 10px; padding-top: 5px;">
                            <label class="control-label" style="font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; display: block;">Reminders</label>

                            <div class="checkbox form-control" style="margin-top: 0; margin-bottom: 6px;">
                                <label style="font-weight: 500; color: #1e293b; font-size: 13px;">
                                    <input type="checkbox" name="reminders_enabled" value="1" <?php echo $remind_val ? 'checked' : ''; ?>>
                                    Alert creator for survey
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: flex-start; margin-top: 15px;">
                        <div class="col-md-12 text-right" style="padding-top: 28px;">
                            <button type="submit" id="submit-schedule-btn" class="btn btn-primary" style="background-color: #2e5490; border-color: #2e5490; padding: 8px 24px; font-weight: 500; border-radius: 6px;">
                                Save Schedule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Schedule & Participant List -->
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
                                style="margin-right: 6px; color: #2e5490;"></i>
                            Schedules
                        </h4>

                        <small class="text-muted">
                            <?php echo !empty($scheduleDates) ? count($scheduleDates) : 0; ?> scheduled occurrence(s)
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
                                    <th style="width: 55px; padding: 10px 15px; color: #64748b;">
                                        #
                                    </th>

                                    <th style="min-width: 130px; padding: 10px 15px; color: #64748b;">
                                        Start Date
                                    </th>

                                    <th style="min-width: 110px; padding: 10px 15px; color: #64748b;">
                                        Start Date
                                    </th>

                                    <th style="min-width: 110px; padding: 10px 15px; color: #64748b;">
                                        Start Time
                                    </th>

                                    <th style="min-width: 110px; padding: 10px 15px; color: #64748b;">
                                        End Time
                                    </th>
                                    <th style="min-width: 100px; padding: 10px 15px; color: #64748b;">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="schedule-list">

                                <?php foreach ($scheduleDates as $index => $scheduleValue): ?>

                                    <tr class="schedule-row" data-status="<?php echo HTML::chars($scheduleValue['status']); ?>">

                                        <td class="schedule-index" style="padding: 11px 15px; color: #64748b;"><?php echo $index + 1; ?></td>

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
                                            <strong style="color: #334155;">
                                                <?php
                                                echo date(
                                                    'l',
                                                    strtotime($scheduleValue['start_date'])
                                                );
                                                ?>
                                            </strong>
                                        </td>

                                        <td style="padding: 11px 15px;">
                                            <?php
                                            echo date(
                                                'H:i',
                                                strtotime($scheduleValue['start_date'])
                                            );
                                            ?>
                                        </td>

                                        <td style="padding: 11px 15px;">
                                            <?php
                                            echo date(
                                                'H:i',
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
                                style="margin-right: 6px; color: #2e5490;"></i>
                            Participants
                        </h4>

                        <small class="text-muted">Uploaded participants</small>
                    </div>

                    <span class="label label-success" style="font-size: 11px; padding: 5px 8px; color: #2e5490; background-color: #e8edf6">
                        <span id="total-participants-count"><?php echo !empty($participants) ? count($participants) : 0; ?></span> Total
                    </span>

                </div>


            </div>
            <!-- Body -->
            <div class="panel-body" style="padding: 0;">
                <!-- Add Participant -->
                <div style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">

                    <form id="add-participant-form"
                        style="display: flex; align-items: center; gap: 8px; width: 100%;">

                        <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">

                        <input type="text" name="first_name" id="participant-first-name" class="form-control input-field" placeholder="First Name" required
                            style="flex: 1; min-width: 0;">

                        <input type="text" name="last_name" id="participant-last-name" class="form-control input-field" placeholder="Last Name" required
                            style="flex: 1; min-width: 0;">

                        <input type="email" name="email" id="participant-email" class="form-control input-field" placeholder="Email" required
                            style="flex: 1.5; min-width: 0;">

                        <button type="submit" id="add-participant-btn" class="btn btn-primary btn-sm" title="Add Participant"
                            style="background-color: #2e5490; border-color: #2e5490; padding: 8px 20px; font-weight: 500; border-radius: 6px; flex: 0 0 auto; white-space: nowrap;">
                            Add
                        </button>

                    </form>

                    <div id="participant-add-message"
                        style="margin-top: 6px; font-size: 12px;">
                    </div>

                </div>

                <?php if (!empty($participants)): ?>

                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0; font-size: 13px;">

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
                                        data-survey-id="<?php echo (int)  $survey['id']; ?>">

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
                                                value="<?php echo HTML::chars($p['first_name']); ?>">

                                        </td>

                                        <!-- Last Name -->
                                        <td style="padding: 10px 12px; color: #334155;">

                                            <span class="participant-last-name-text">
                                                <?php echo HTML::chars($p['last_name']); ?>
                                            </span>

                                            <input
                                                type="text"
                                                class="form-control participant-last-name-input hidden"
                                                value="<?php echo HTML::chars($p['last_name']); ?>">

                                        </td>

                                        <!-- Email -->
                                        <td style="padding: 10px 12px; color: #64748b;">

                                            <span class="participant-email-text">
                                                <?php echo HTML::chars($p['email']); ?>
                                            </span>

                                            <input
                                                type="email"
                                                class="form-control participant-email-input hidden"
                                                value="<?php echo HTML::chars($p['email']); ?>">

                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3 text-right whitespace-nowrap">

                                            <!-- Normal actions -->
                                            <div class="participant-actions">
                                                <button type="button" class="participant-edit-btn">
                                                    <i class="glyphicon glyphicon-pencil"></i>
                                                </button>

                                                <button type="button" class="participant-delete-btn dt-button">
                                                    <i class="glyphicon glyphicon-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Edit actions -->
                                            <div class="participant-edit-actions hidden">

                                                <button type="button" class="participant-save-btn dt-button">
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                </button>

                                                <button type="button" class="participant-cancel-btn dt-button">
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
                </div>
            </div>
        </div>

    </div>

    <div class="section-footer" style="margin-top: 16px;">
        <a href="<?php echo URL::site('survey'); ?>" class="btn btn-default">
            <?php echo $is_edit ? 'Done' : 'Cancel'; ?>
        </a>
    </div>

</div>

<!-- Template for a blank question row, cloned by JS on "Add Question" -->
<script type="text/template" id="question-row-template">
    <?php echo View::factory('survey/_question_row')
        ->set('index', '__INDEX__')
        ->set('question', array('question' => '', 'type' => Model_SurveyQuestion::TYPE_TEXT, 'options' => '', 'required' => FALSE))
        ->set('question_types', $question_types); ?>
</script>

<!-- Template for a blank option row, cloned by JS on "Add Option" -->
<script type="text/template" id="option-row-template">
    <div class="option-row">
        <input type="text" name="__NAME__" class="form-control option-row-input" maxlength="500" placeholder="Option text" value="">
        <button type="button" class="js-remove-option option-row-remove" title="Remove option">
            <span class="glyphicon glyphicon-remove"></span>
        </button>
    </div>
</script>

<script>
(function () {
    'use strict';

    var root = document.getElementById('survey-form');

    // Kept in sync with Model_SurveyQuestion::requires_options() on the server.
    var CHOICE_TYPES = ['radio', 'checkbox', 'select'];

    /* ---------------------------------------------------------------
     * Panel expand/collapse — independent per panel (not an accordion),
     * so all sections can be open at once. All start expanded.
     * ------------------------------------------------------------- */
    var headings = root.querySelectorAll('.panel-heading');

    Array.prototype.forEach.call(headings, function (heading) {
        var target = document.querySelector(heading.getAttribute('data-target'));

        heading.addEventListener('click', function () {
            var isHidden = target.style.display === 'none';
            target.style.display = isHidden ? '' : 'none';
            heading.classList.toggle('collapsed', ! isHidden);
        });
    });

    document.getElementById('expand-all-btn').addEventListener('click', function () {
        root.querySelectorAll('.panel-collapse').forEach(function (el) { el.style.display = ''; });
        root.querySelectorAll('.panel-heading').forEach(function (el) { el.classList.remove('collapsed'); });
    });

    document.getElementById('collapse-all-btn').addEventListener('click', function () {
        root.querySelectorAll('.panel-collapse').forEach(function (el) { el.style.display = 'none'; });
        root.querySelectorAll('.panel-heading').forEach(function (el) { el.classList.add('collapsed'); });
    });

    /* ---------------------------------------------------------------
     * Question rows — add / remove
     * ------------------------------------------------------------- */
    var container = document.getElementById('question-rows');
    var addButton = document.getElementById('add-question-btn');
    var rowTemplate = document.getElementById('question-row-template').innerHTML;
    var optionTemplate = document.getElementById('option-row-template').innerHTML;
    var nextIndex = <?php echo count($questions); ?>;

    function toggleOptionsVisibility(row) {
        var select = row.querySelector('.js-question-type');
        var repeater = row.querySelector('.question-options-repeater');

        if ( ! select || ! repeater) { return; }

        repeater.style.display = CHOICE_TYPES.indexOf(select.value) === -1 ? 'none' : '';
    }

    addButton.addEventListener('click', function () {
        var html    = rowTemplate.split('__INDEX__').join(nextIndex);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();

        var row = wrapper.firstChild;
        container.appendChild(row);
        toggleOptionsVisibility(row);

        nextIndex += 1;
    });

    container.addEventListener('click', function (event) {
        var removeQuestionBtn = event.target.closest('.js-remove-question');

        if (removeQuestionBtn)
        {
            var row = removeQuestionBtn.closest('.question-row');

            if (row)
            {
                row.parentNode.removeChild(row);
            }

            return;
        }

        var addOptionBtn = event.target.closest('.js-add-option');

        if (addOptionBtn)
        {
            var questionRow = addOptionBtn.closest('.question-row');
            var textInput   = questionRow.querySelector('.question-row-text');
            var optionsName = textInput.getAttribute('name').replace('[question]', '[options][]');

            var html    = optionTemplate.split('__NAME__').join(optionsName);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();

            questionRow.querySelector('.option-rows').appendChild(wrapper.firstChild);

            return;
        }

        var removeOptionBtn = event.target.closest('.js-remove-option');

        if (removeOptionBtn)
        {
            var optionRows = removeOptionBtn.closest('.option-rows');

            // Keep at least two options — matches the server-side minimum.
            if (optionRows.querySelectorAll('.option-row').length <= 2)
            {
                return;
            }

            var optionRow = removeOptionBtn.closest('.option-row');
            optionRow.parentNode.removeChild(optionRow);
        }
    });

    container.addEventListener('change', function (event) {
        if (event.target.classList.contains('js-question-type'))
        {
            toggleOptionsVisibility(event.target.closest('.question-row'));
        }
    });

    // Set correct initial visibility for rows rendered server-side (edit mode).
    container.querySelectorAll('.question-row').forEach(function (row) {
        toggleOptionsVisibility(row);
    });

    /* ---------------------------------------------------------------
     * AJAX submit — each section saves independently.
     * On the very first save of a brand-new survey (Details form, no
     * id yet), the server returns a redirect to the edit URL so the
     * Questions/Schedule sections load already unlocked.
     * ------------------------------------------------------------- */
    function showAlert(form, type, messages) {
        var alertBox = root.querySelector('[data-role="alert"]');

        if (!alertBox) {
            return;
        }

        alertBox.className = 'section-alert section-alert-' + type;

        if (Array.isArray(messages))
        {
            var list = document.createElement('ul');

            messages.forEach(function (msg) {
                var li = document.createElement('li');
                li.textContent = msg;
                list.appendChild(li);
            });

            alertBox.innerHTML = '';
            alertBox.appendChild(list);
        }
        else
        {
            alertBox.textContent = messages;
        }
    }

    function bindSectionForm(form, onSuccess) {
        if ( ! form) { return; }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Client-side guard: at least one question row must have text.
            // Server re-validates this regardless; this just avoids a round-trip.
            if (form.id === 'survey-details-questions-form')
            {
                var hasQuestion = false;

                form.querySelectorAll('.question-row-text').forEach(function (textarea) {
                    if (textarea.value.trim() !== '') { hasQuestion = true; }
                });

                if ( ! hasQuestion)
                {
                    showAlert(form, 'error', ['Add at least one question.']);
                    return;
                }
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            var indicator = form.querySelector('[data-role="saving-indicator"]');

            submitBtn.disabled = true;
            if (indicator) { indicator.style.display = ''; }

            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    var data = result.data;

                    if (data.success)
                    {
                        showAlert(form, 'success', data.message || 'Saved.');

                        if (data.survey_id)
                        {
                            root.querySelectorAll('input[name="id"], input[name="survey_id"]').forEach(function (input) {
                                input.value = data.survey_id;
                            });
                        }

                        if (typeof onSuccess === 'function')
                        {
                            onSuccess(data);
                        }
                    }
                    else
                    {
                        showAlert(form, 'error', data.errors && data.errors.length ? data.errors : ['Could not save. Please try again.']);
                    }
                })
                .catch(function () {
                    showAlert(form, 'error', ['Network error. Please try again.']);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    if (indicator) { indicator.style.display = 'none'; }
                });
        });
    }

    bindSectionForm(document.getElementById('survey-details-questions-form'), function () {
        var detailsHeading  = root.querySelector('[data-target="#section-details-questions"]');
        var detailsPanel    = document.getElementById('section-details-questions');
        var scheduleHeading = root.querySelector('[data-target="#section-schedule"]');
        var schedulePanel   = document.getElementById('section-schedule');

        detailsPanel.style.display = 'none';
        detailsHeading.classList.add('collapsed');

        schedulePanel.style.display = '';
        scheduleHeading.classList.remove('collapsed');

        scheduleHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    bindSectionForm(document.getElementById('survey-schedule-form'));

})();
</script>

<!-- =========== Script ============== -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    window.scheduleConfig = {
        recurrenceRuleId: '<?php echo HTML::chars($recurrence_rule_val); ?>',
        weekday: '<?php echo HTML::chars($weekday_val); ?>',
        monthDay: '<?php echo HTML::chars($month_day_val); ?>',
        quarterMonth: '<?php echo HTML::chars($quarter_month_val); ?>'
    };

    window.participantUrls = {
        add: '<?= URL::site('participant/add') ?>',
        edit: '<?= URL::site('participant/edit') ?>',
        delete: '<?= URL::site('participant/delete') ?>'
    };
</script>

<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/participant.js"></script>
<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/schedule-preview.js"></script>
<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/pagination.js"></script>

