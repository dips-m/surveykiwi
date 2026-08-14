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

                                    Alert creator for survey

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


<!-- =========== Script ============== -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script type="text/javascript" src="<?php echo URL::base(); ?>assets/js/schedule-preview.js"></script>
