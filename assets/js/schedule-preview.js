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
    // Format datetime-local value
    function formatLocalDateTime(date) {
        var pad = function(n) {
            return n < 10 ? '0' + n : n;
        };

        return date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + 'T' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes());
    }
    
    // Prevent selecting past date/time
    var now = new Date();
    var minDateTimeString = formatLocalDateTime(now);
   
    function updateEndDateMin() {
        var startDateVal = $('#start_date').val();
        var frequencyVal = $('#frequency').val();

        if (startDateVal) {
            var startDate = new Date(startDateVal);

            if (frequencyVal === 'once') {

                // Once: end date must be the same date as start date
                var startDateOnly = startDateVal.substring(0, 10);

                // Keep current end time if already selected
                var currentEndVal = $('#end_date').val();
                var endTime = '00:00';

                if (currentEndVal) {
                    endTime = currentEndVal.substring(11, 16);
                }

                var sameDayEndDate = startDateOnly + 'T' + endTime;

                $('#end_date')
                    .attr('min', startDateOnly + 'T00:00')
                    .attr('max', startDateOnly + 'T23:59');

                // If existing end date is different, move it to start date
                if (!currentEndVal || currentEndVal.substring(0, 10) !== startDateOnly) {
                    $('#end_date').val(sameDayEndDate);
                }

            } else {

                // Other frequencies:
                // Minimum end date = next calendar day at 00:00
                startDate.setDate(startDate.getDate() + 1);
                startDate.setHours(0, 0, 0, 0);

                var minEndDate = formatLocalDateTime(startDate);

                $('#end_date')
                    .attr('min', minEndDate)
                    .removeAttr('max');
            }

        } else {

            $('#end_date')
                .attr('min', minDateTimeString)
                .removeAttr('max');
        }
    }

    var scheduleId = parseInt($('#schedule_id').val(), 10) || 0;
    var isEdit = scheduleId > 0;

    $('#start_date').attr('min', minDateTimeString);
    $('#end_date').attr('min', minDateTimeString);

    updateEndDateMin();

    // Clear errors
    $('#frequency').on('change', function() {
        clearFieldError('frequency');
        updateEndDateMin();
    });

    $('#start_date').on('input change', function() {
        clearFieldError('start-date');
        updateEndDateMin();
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

                    // setTimeout(function() {
                        location.reload();
                    // }, 1000);

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