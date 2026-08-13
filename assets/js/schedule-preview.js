function showFieldError(fieldId, message) {
    var $group = $('#group-' + fieldId);
    var $errSpan = $('#err-' + fieldId);

    $group.addClass('has-error');

    if ($errSpan.length === 0) {
        $('#' + fieldId).after(
            '<span id="err-' + fieldId + '" class="help-block text-danger" ' +
            'style="font-size:12px; margin-top:4px; display:block; width:100%;">' +
            message +
            '</span>'
        );
    } else {
        $errSpan.text(message).show();
    }
}

function clearFieldError(fieldId) {
    $('#group-' + fieldId).removeClass('has-error');
    $('#err-' + fieldId).hide().text('');
}



$(document).ready(function() {


    var scheduleId = parseInt($('#schedule_id').val(), 10) || 0;
    var isEdit = scheduleId > 0;

    var startDateValue = $('#start_date').val();
    var endDateValue = $('#end_date').val();

    /*
     * Start Date
     */


    function formatDateTime(date) {
        var pad = function (n) {
            return n < 10 ? '0' + n : n;
        };

        return date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + ' ' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes()) + ':00';
    }

    function getTomorrow() {
        var tomorrow = new Date();

        tomorrow.setHours(0, 0, 0, 0);
        tomorrow.setDate(tomorrow.getDate() + 1);

        return tomorrow;
    }

    var startInput = $('#start_date');

    /*
     * Get the value already saved in the database.
     *
     * Example:
     * 2026-08-31 09:00:00
     */
    var savedStartDate = startInput.val();

    /*
     * Convert database datetime to a format Flatpickr understands.
     */
    var defaultStartDate = null;

    if (savedStartDate) {
        defaultStartDate = savedStartDate.replace(' ', 'T');
    }

    var tomorrow = getTomorrow();

    /*
     * Determine whether this is an existing schedule
     * whose start date is already in the past.
     */
    var savedStartTimestamp = savedStartDate
        ? new Date(savedStartDate.replace(' ', 'T')).getTime()
        : null;

    var todayTimestamp = new Date().getTime();

    var startDateIsPast = (
        savedStartTimestamp &&
        savedStartTimestamp < todayTimestamp
    );

    /*
     * Initialize Flatpickr.
     */
    var startPicker = flatpickr('#start_date', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i:S',
        altInput: true,
        altFormat: 'd M Y, h:i K',
        allowInput: true,
        minDate: tomorrow,
        defaultDate: defaultStartDate,

        onReady: function (selectedDates, dateStr, instance) {

            /*
             * Existing saved start date is in the past.
             *
             * Show the saved value but do not allow editing.
             */
            if (startDateIsPast) {

                instance.setDate(
                    defaultStartDate,
                    false
                );

                instance.altInput.disabled = true;

                instance.altInput.style.cursor = 'not-allowed';

                instance.altInput.style.backgroundColor = '#f5f5f5';

                /*
                 * Keep hidden value unchanged.
                 */
                startInput.val(savedStartDate);
            }
        },

        onChange: function (selectedDates, dateStr, instance) {

            if (selectedDates.length) {

                var selectedDate = selectedDates[0];

                startInput.val(
                    formatDateTime(selectedDate)
                );
            }
        }
    });


    /*
     * Important:
     *
     * If the saved date is in the past, Flatpickr's minDate
     * would normally reject it.
     *
     * We therefore explicitly restore the saved value after
     * initialization and disable only the visible input.
     */
    if (startDateIsPast && defaultStartDate) {

        startPicker.setDate(
            defaultStartDate,
            false
        );

        startInput.val(savedStartDate);

        startPicker.altInput.value =
            flatpickr.formatDate(
                new Date(defaultStartDate),
                'd M Y, h:i K'
            );

        startPicker.altInput.disabled = true;

        startPicker.altInput.style.cursor = 'not-allowed';

        startPicker.altInput.style.backgroundColor = '#f5f5f5';
    }

    /*
     * End Date
     */
    var endPicker = flatpickr('#end_date', {
        enableTime: true,
        dateFormat: 'Y-m-d\\TH:i',
        altInput: true,
        altFormat: 'd M Y, h:i K',
        allowInput: true,
        onChange: function() {
            clearFieldError('end-date');
        }
    });

    function updateEndDateMin(startDate) {

        if (!startDate) {
            return;
        }

        var frequency = $('#frequency').val();

        /*
         * Once:
         * End date must be on the same date as start date.
         */
        if (frequency === 'once') {

            var startDateOnly = new Date(startDate);
            startDateOnly.setHours(0, 0, 0, 0);

            var endDate = new Date(startDate);
            endDate.setHours(23, 59, 0, 0);

            endPicker.set('minDate', startDateOnly);
            endPicker.set('maxDate', endDate);

            var currentEnd = endPicker.selectedDates[0];

            if (!currentEnd || currentEnd.toDateString() !== startDate.toDateString()) {
                var newEnd = new Date(startDate);
                newEnd.setHours(23, 59, 0, 0);

                endPicker.setDate(newEnd, false);
            }

        } else {

            /*
             * Daily / Weekly / Monthly / Quarterly:
             * End date must be at least one calendar day
             * after start date.
             */
            var minEndDate = new Date(startDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            minEndDate.setHours(0, 0, 0, 0);

            endPicker.set('minDate', minEndDate);
            endPicker.set('maxDate', null);

            var currentEnd = endPicker.selectedDates[0];

            if (currentEnd && currentEnd < minEndDate) {
                endPicker.clear();
            }
        }
    }

    /*
     * Frequency change
     */
    $('#frequency').on('change', function() {

        clearFieldError('frequency');

        var startDate = startPicker.selectedDates[0];

        if (startDate) {
            updateEndDateMin(startDate);
        }
    });

    /*
     * Initial date setup
     */
    var existingStartDate = startPicker.selectedDates[0];

    if (existingStartDate) {
        updateEndDateMin(existingStartDate);
    }

    // Clear errors
    $('#frequency').on('change', function() {
        clearFieldError('frequency');
    });

    $('#start_date').on('input change', function() {
        clearFieldError('start-date');
    });


    $('#end_date').on('input change', function() {
        clearFieldError($(this).attr('id'));
    });


    // Participant file change
    $('#participants_file').on('change', function() {

        clearFieldError('participants-file');

        if (!this.files || this.files.length === 0) {
            $('#file-chosen-name').text('No file chosen');
            return;
        }

        var fileName = this.files[0].name;
        var lowerFileName = fileName.toLowerCase();

        if (lowerFileName.slice(-4) !== '.csv') {

            showFieldError(
                'participants-file',
                'Please upload a valid CSV file.'
            );

            $(this).val('');
            $('#file-chosen-name').text('No file chosen');

            return;
        }

        $('#file-chosen-name').text(fileName);
    });


    // Schedule form submit
    $('#scheduleForm').submit(function(e) {

        e.preventDefault();

        var form = this;

        var frequencyVal = $('#frequency').val();
        var startDateVal = $('#start_date').val();
        var endDateVal = $('#end_date').val();

        var fileInput = $('#participants_file')[0];

        var currentDate = new Date();
        var startDate = startDateVal ? new Date(startDateVal) : null;
        var endDate = endDateVal ? new Date(endDateVal) : null;

        // Existing participant count
        var existingParticipantsCount =
            parseInt($('#total-participants-count').text(), 10) || 0;

        var hasNewFile =
            fileInput &&
            fileInput.files &&
            fileInput.files.length > 0;

        var hasError = false;


        // Clear global alerts
        $('#alert-container').html('');


        // -----------------------------------------
        // 1. Frequency
        // -----------------------------------------
        if (!frequencyVal) {

            showFieldError(
                'frequency',
                'Please select a launch frequency.'
            );

            hasError = true;

        } else {

            clearFieldError('frequency');
        }


        // -----------------------------------------
        // 2. Start Date
        // -----------------------------------------
        if (!startDateVal) {

            showFieldError(
                'start-date',
                'Start date and time are required.'
            );

            hasError = true;

        } else if (!isEdit && startDate < currentDate) {

            showFieldError(
                'start-date',
                'Start date and time cannot be in the past.'
            );

            hasError = true;

        } else {

            clearFieldError('start-date');
        }


        // -----------------------------------------
        // 3. End Date
        // -----------------------------------------
        if (!endDateVal) {

            showFieldError(
                'end-date',
                'End date and time are required.'
            );

            hasError = true;

        } else if (endDate <= startDate) {

            showFieldError(
                'end-date',
                'End date and time must be strictly greater than start date and time.'
            );

            hasError = true;

        } else {

            clearFieldError('end-date');
        }


        // -----------------------------------------
        // 4. Participants
        //
        // Create:
        //     Existing count = 0
        //     File required
        //
        // Edit:
        //     Existing count > 0
        //     File optional
        //
        // If a file is selected, always validate it.
        // -----------------------------------------

        if (!hasNewFile) {

            // No new file selected.
            // Only show error when there are no existing participants.

            if (existingParticipantsCount === 0) {

                showFieldError(
                    'participants-file',
                    'Participants are required. Please upload a CSV file.'
                );

                hasError = true;

            } else {

                // Existing participants are available,
                // therefore CSV is optional during edit.

                clearFieldError('participants-file');
            }

        } else {

            // New file selected.
            // Validate it regardless of existing participants.

            var fileName =
                fileInput.files[0].name.toLowerCase();

            if (fileName.slice(-4) !== '.csv') {

                showFieldError(
                    'participants-file',
                    'Please upload a valid CSV file.'
                );

                hasError = true;

            } else {

                clearFieldError('participants-file');
            }
        }


        // -----------------------------------------
        // Stop if validation failed
        // -----------------------------------------
        if (hasError) {
            return false;
        }


        // -----------------------------------------
        // Submit schedule + optional CSV
        // -----------------------------------------

        var $btn = $('#submit-schedule-btn');

        $btn
            .prop('disabled', true)
            .html(
                '<i class="glyphicon glyphicon-refresh"></i> Saving...'
            );


        // IMPORTANT:
        // serialize() does NOT send file inputs.
        // Use FormData.
        var formData = new FormData(form);


        $.ajax({

            url: $(form).attr('action'),

            type: 'POST',

            data: formData,

            contentType: false,

            processData: false,

            dataType: 'json',

            success: function(response) {
                console.log('response: ', response);
                

                if (response.status === 'success') {

                    $('#alert-container').html(
                        '<div class="alert alert-success alert-dismissible">' +
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                            response.message +
                        '</div>'
                    );

                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                } else {

                    $('#alert-container').html(
                        '<div class="alert alert-danger alert-dismissible">' +
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                            response.message +
                        '</div>'
                    );

                    $btn
                        .prop('disabled', false)
                        .html(
                            '<i class="glyphicon glyphicon-floppy-disk"></i> Save Schedule'
                        );
                }
            },

            error: function(xhr) {

                var message =
                    'An unexpected server error occurred.';

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {
                    message = xhr.responseJSON.message;
                }

                $('#alert-container').html(
                    '<div class="alert alert-danger alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        message +
                        '</div>'
                );

                $btn
                    .prop('disabled', false)
                    .html(
                        '<i class="glyphicon glyphicon-floppy-disk"></i> Save Schedule'
                    );
            }

        });

        return false;
    });

});