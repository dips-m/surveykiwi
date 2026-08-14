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
    var startInput = $('#start_date');
    var savedStartDate = startInput.val();

    function formatDateTime(date) {
        var pad = function(n) {
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

    var defaultStartDate = savedStartDate ? savedStartDate.replace(' ', 'T') : null;
    var tomorrow = getTomorrow();

    var savedStartTimestamp = savedStartDate
        ? new Date(defaultStartDate).getTime()
        : null;

    var startDateIsPast = savedStartTimestamp && savedStartTimestamp < Date.now();

    var startPicker = flatpickr('#start_date', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i:S',
        altInput: true,
        altFormat: 'd M Y, h:i K',
        allowInput: true,
        minDate: tomorrow,
        defaultDate: defaultStartDate,

        onReady: function(selectedDates, dateStr, instance) {
            if (startDateIsPast) {
                instance.setDate(defaultStartDate, false);
                instance.altInput.disabled = true;
                instance.altInput.style.cursor = 'not-allowed';
                instance.altInput.style.backgroundColor = '#f5f5f5';
                startInput.val(savedStartDate);
            }
        },

        onChange: function(selectedDates) {
            if (selectedDates.length && !startDateIsPast) {
                startInput.val(formatDateTime(selectedDates[0]));
                clearFieldError('start-date');
                updateEndDateMin(selectedDates[0]);
            }
        }
    });

    if (startDateIsPast && defaultStartDate) {
        startPicker.setDate(defaultStartDate, false);
        startInput.val(savedStartDate);
        startPicker.altInput.value = flatpickr.formatDate(
            new Date(defaultStartDate),
            'd M Y, h:i K'
        );
        startPicker.altInput.disabled = true;
        startPicker.altInput.style.cursor = 'not-allowed';
        startPicker.altInput.style.backgroundColor = '#f5f5f5';
    }

    var endPicker = flatpickr('#end_date', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i:S',
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

        if (frequency === 'once') {
            var startDay = new Date(startDate);
            startDay.setHours(0, 0, 0, 0);

            var endDay = new Date(startDate);
            endDay.setHours(23, 59, 59, 0);

            endPicker.set('minDate', startDay);
            endPicker.set('maxDate', endDay);

            var currentEnd = endPicker.selectedDates[0];

            if (!currentEnd || currentEnd.toDateString() !== startDate.toDateString()) {
                endPicker.setDate(endDay, false);
            }
        } else {
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

    $('#frequency').on('change', function() {
        clearFieldError('frequency');

        var startDate = startPicker.selectedDates[0];

        if (startDate) {
            updateEndDateMin(startDate);
        }
    });

    var existingStartDate = startPicker.selectedDates[0];

    if (existingStartDate) {
        updateEndDateMin(existingStartDate);
    }

    $('#start_date').on('input change', function() {
        clearFieldError('start-date');
    });

    $('#end_date').on('input change', function() {
        clearFieldError('end-date');
    });


    // Schedule form submit
    $('#scheduleForm').submit(function(e) {

        e.preventDefault();

        var form = this;

        var scheduleId = parseInt($('#schedule_id').val(), 10) || 0;
        var isEdit = scheduleId > 0;

        var frequencyVal = $('#frequency').val();
        var startDateVal = $('#start_date').val();
        var endDateVal = $('#end_date').val();

        var fileInput = $('#participants_file')[0];

        var currentDate = new Date();
        var startDate = startDateVal ? new Date(startDateVal) : null;
        var endDate = endDateVal ? new Date(endDateVal) : null;


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