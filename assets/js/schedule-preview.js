function showFieldError(fieldId, message) {
    var $group = $('#group-' + fieldId);
    var $errSpan = $('#err-' + fieldId);

    $group.addClass('has-error');

    if ($errSpan.length === 0) {
        $('#' + fieldId).after('<span id="err-' + fieldId + '" class="help-block text-danger" style="font-size:12px; margin-top:4px; display:block; width:100%;">' + message + '</span>');
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

    var $frequency = $('#frequency');
    var $recurrenceRule = $('#recurrence_rule_id');
    var $weekday = $('#weekday');
    var $monthDay = $('#month_day');
    var $quarterMonth = $('#quarter_month');

    var savedRuleId = window.scheduleConfig ? window.scheduleConfig.recurrenceRuleId || '' : '';
    var savedWeekday = window.scheduleConfig ? window.scheduleConfig.weekday || '' : '';
    var savedMonthDay = window.scheduleConfig ? window.scheduleConfig.monthDay || '' : '';
    var savedQuarterMonth = window.scheduleConfig ? window.scheduleConfig.quarterMonth || '' : '';

    var startDateInput = $('#start_date');
    var endDateInput = $('#end_date');
    var savedStartDate = startDateInput.val();

    var recurrenceRules = {
        daily: [
            {id: 1, name: 'Every day'},
            {id: 2, name: 'Weekdays only'}
        ],
        monthly: [
            {id: 3, name: 'Same day of month'},
            {id: 4, name: 'Last day of month'}
        ],
        quarterly: [
            {id: 5, name: 'Same day of quarter'},
            {id: 6, name: 'Last day of quarter'}
        ]
    };

    function updateMonthDayField() {
        var frequency = $frequency.val();
        var ruleId = parseInt($recurrenceRule.val(), 10);

        $('#group-month-day').hide();
        $('#group-quarter-month').hide();

        $monthDay.prop('required', false);
        $quarterMonth.prop('required', false);

        clearFieldError('month-day');
        clearFieldError('quarter-month');

        // Monthly - Same day of month
        if (frequency === 'monthly' && ruleId === 3) {
            $('#group-month-day').show();
            $monthDay.prop('required', true);

            if (savedMonthDay) {
                $monthDay.val(savedMonthDay);
            }

            return;
        }

        // Monthly - Last day of month
        if (frequency === 'monthly' && ruleId === 4) {
            $monthDay.val('');
            $quarterMonth.val('');
            return;
        }

        // Quarterly - Same day of quarter
        if (frequency === 'quarterly' && ruleId === 5) {
            $('#group-month-day').show();
            $('#group-quarter-month').show();

            $monthDay.prop('required', true);
            $quarterMonth.prop('required', true);

            if (savedMonthDay) {
                $monthDay.val(savedMonthDay);
            }

            if (savedQuarterMonth) {
                $quarterMonth.val(savedQuarterMonth);
            } else if (!$quarterMonth.val()) {
                $quarterMonth.val($quarterMonth.find('option:first').val());
            }

            return;
        }

        // Quarterly - Last day of quarter
        if (frequency === 'quarterly' && ruleId === 6) {
            $monthDay.val('');
            $quarterMonth.val('');
            return;
        }

        $monthDay.val('');
        $quarterMonth.val('');
    }

    function updateRecurrenceFields() {
        var frequency = $frequency.val();
        var rules = recurrenceRules[frequency] || [];

        $('#group-recurrence-rule').hide();
        $('#group-weekday').hide();
        $('#group-month-day').hide();
        $('#group-quarter-month').hide();

        $recurrenceRule.prop('required', false);
        $weekday.prop('required', false);
        $monthDay.prop('required', false);
        $quarterMonth.prop('required', false);

        clearFieldError('recurrence-rule');
        clearFieldError('weekday');
        clearFieldError('month-day');
        clearFieldError('quarter-month');

        if (frequency === 'once' || !frequency) {
            $recurrenceRule.val('');
            $weekday.val('');
            $monthDay.val('');
            $quarterMonth.val('');
            return;
        }

        // Weekly does not have a recurrence rule.
        if (frequency === 'weekly') {
            $recurrenceRule.val('');

            $('#group-weekday').show();
            $weekday.prop('required', true);

            if (savedWeekday && $weekday.find('option[value="' + savedWeekday + '"]').length) {
                $weekday.val(savedWeekday);
            } else {
                $weekday.val($weekday.find('option:first').val());
            }

            clearFieldError('recurrence-rule');
            clearFieldError('weekday');

            return;
        }

        if (!rules.length) {
            return;
        }

        $recurrenceRule.empty();

        $.each(rules, function(index, rule) {
            $recurrenceRule.append(
                $('<option>', {
                    value: rule.id,
                    text: rule.name
                })
            );
        });

        if (savedRuleId && $recurrenceRule.find('option[value="' + savedRuleId + '"]').length) {
            $recurrenceRule.val(savedRuleId);
        } else {
            $recurrenceRule.val(rules[0].id);
        }

        $('#group-recurrence-rule').show();
        $recurrenceRule.prop('required', true);

        clearFieldError('recurrence-rule');

        updateMonthDayField();
    }

    $frequency.on('change', function() {
        clearFieldError('frequency');
        clearFieldError('recurrence-rule');
        clearFieldError('weekday');
        clearFieldError('month-day');
        clearFieldError('quarter-month');

        savedRuleId = '';
        savedWeekday = '';
        savedMonthDay = '';
        savedQuarterMonth = '';

        updateRecurrenceFields();
    });

    $recurrenceRule.on('change', function() {
        clearFieldError('recurrence-rule');
        updateMonthDayField();
    });

    $weekday.on('change', function() {
        clearFieldError('weekday');
    });

    $monthDay.on('change', function() {
        clearFieldError('month-day');
    });

    $quarterMonth.on('change', function() {
        clearFieldError('quarter-month');
    });

    function getTomorrow() {
        var tomorrow = new Date();
        tomorrow.setHours(0, 0, 0, 0);
        tomorrow.setDate(tomorrow.getDate() + 1);
        return tomorrow;
    }

    var defaultStartDate = savedStartDate ? savedStartDate.replace(' ', 'T') : null;
    var tomorrow = getTomorrow();

    var savedStartTimestamp = savedStartDate ? new Date(defaultStartDate).getTime() : null;
    var startDateIsPast = savedStartTimestamp && savedStartTimestamp < Date.now();

    var startPicker = flatpickr('#start_date', {
        enableTime: false,
        noCalendar: false,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: true,
        minDate: tomorrow,
        defaultDate: defaultStartDate,

        onReady: function(selectedDates, dateStr, instance) {
            if (startDateIsPast) {
                instance.setDate(defaultStartDate, false);
                instance.altInput.disabled = true;
                instance.altInput.style.cursor = 'not-allowed';
                instance.altInput.style.backgroundColor = '#f5f5f5';

                if (selectedDates[0]) {
                    startDateInput.val(flatpickr.formatDate(selectedDates[0], 'Y-m-d'));
                }
            }
        },

        onChange: function(selectedDates) {
            if (selectedDates.length && !startDateIsPast) {
                startDateInput.val(flatpickr.formatDate(selectedDates[0], 'Y-m-d'));
                clearFieldError('start-date');
                updateEndDateMin(selectedDates[0]);
            }
        }
    });

    if (startDateIsPast && defaultStartDate) {
        startPicker.setDate(defaultStartDate, false);
        startDateInput.val(savedStartDate);
        startPicker.altInput.value = flatpickr.formatDate(new Date(defaultStartDate), 'd M Y');
        startPicker.altInput.disabled = true;
        startPicker.altInput.style.cursor = 'not-allowed';
        startPicker.altInput.style.backgroundColor = '#f5f5f5';
    }

    var endPicker = flatpickr('#end_date', {
        enableTime: false,
        noCalendar: false,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: true,

        onChange: function() {
            clearFieldError('end-date');
        }
    });

    var startTimePicker = flatpickr('#start_time', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        altInput: true,
        altFormat: 'H:i',
        allowInput: true,
        time_24hr: true,

        onChange: function() {
            clearFieldError('start-time');
        }
    });

    var endTimePicker = flatpickr('#end_time', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        altInput: true,
        altFormat: 'H:i',
        allowInput: true,
        time_24hr: true,

        onChange: function() {
            clearFieldError('end-time');
        }
    });

    function updateEndDateMin(startDate) {
        if (!startDate) {
            return;
        }

        var frequency = $frequency.val();

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

        clearFieldError('end-date');
    }

    $frequency.on('change', function() {
        var startDate = startPicker.selectedDates[0];

        if (startDate) {
            updateEndDateMin(startDate);
        }
    });

    startDateInput.on('input change', function() {
        clearFieldError('start-date');
    });

    endDateInput.on('input change', function() {
        clearFieldError('end-date');
    });

    $('#start_time, #end_time').on('change', function() {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();

        if (startTime && endTime && endTime <= startTime) {
            showFieldError('end-time', 'End time must be later than start time.');
        } else {
            clearFieldError('end-time');
        }
    });

    var existingStartDate = startPicker.selectedDates[0];

    if (existingStartDate) {
        updateEndDateMin(existingStartDate);
    }

    updateRecurrenceFields();

    $('#schedule-filter').on('change', function() {
        filterSchedules();
    });

    function filterSchedules() {
        var filter = $('#schedule-filter').val();
        var visibleCount = 0;

        $('.schedule-row').each(function() {
            var status = $(this).data('status');
            var show = filter === 'all' || status === filter;

            $(this).toggle(show);

            if (show) {
                visibleCount++;
                $(this).find('.schedule-index').text(visibleCount);
            }
        });
    }

    filterSchedules();

    $('#participants_file').on('change', function() {
        clearFieldError('participants-file');

        if (!this.files || this.files.length === 0) {
            $('#file-chosen-name').text('No file chosen');
            return;
        }

        var fileName = this.files[0].name;
        var lowerFileName = fileName.toLowerCase();

        if (lowerFileName.slice(-4) !== '.csv') {
            showFieldError('participants-file', 'Please upload a valid CSV file.');
            $(this).val('');
            $('#file-chosen-name').text('No file chosen');
            return;
        }

        $('#file-chosen-name').text(fileName);
    });

    $('#scheduleForm').submit(function(e) {
        e.preventDefault();

        var form = this;
        var scheduleId = parseInt($('#schedule_id').val(), 10) || 0;
        var isEdit = scheduleId > 0;
        var frequencyVal = $frequency.val();
        var startDateVal = startDateInput.val();
        var endDateVal = endDateInput.val();
        var fileInput = $('#participants_file')[0];

        var currentDate = new Date();
        var startDate = startDateVal ? new Date(startDateVal.replace(' ', 'T')) : null;
        var endDate = endDateVal ? new Date(endDateVal.replace(' ', 'T')) : null;
        var startTimeVal = $('#start_time').val();
        var endTimeVal = $('#end_time').val();

        var existingParticipantsCount = parseInt($('#total-participants-count').text(), 10) || 0;
        var hasNewFile = fileInput && fileInput.files && fileInput.files.length > 0;
        var hasError = false;
        var validationErrors = [];

        var recurrenceRuleVal = $recurrenceRule.val();
        var weekdayVal = $weekday.val();
        var monthDayVal = $monthDay.val();
        var quarterMonthVal = $quarterMonth.val();

        $('#alert-container').html('');

        if (!frequencyVal) {
            showFieldError('frequency', 'Please select a launch frequency.');
            validationErrors.push('frequency');
            hasError = true;
        } else {
            clearFieldError('frequency');
        }

        if (!startDateVal) {
            showFieldError('start-date', 'Start date is required.');
            validationErrors.push('start-date');
            hasError = true;
        } else if (!isEdit && startDate < currentDate) {
            showFieldError('start-date', 'Start date cannot be in the past.');
            validationErrors.push('start-date');
            hasError = true;
        } else {
            clearFieldError('start-date');
        }

        if (!endDateVal) {
            showFieldError('end-date', 'End date is required.');
            validationErrors.push('end-date');
            hasError = true;
        } else if (frequencyVal !== 'once' && endDate <= startDate) {
            showFieldError('end-date', 'End date must be strictly greater than start date.');
            validationErrors.push('end-date');
            hasError = true;
        } else {
            clearFieldError('end-date');
        }

        if (!startTimeVal) {
            showFieldError('start-time', 'Start time is required.');
            validationErrors.push('start-time');
            hasError = true;
        } else {
            clearFieldError('start-time');
        }

        if (!endTimeVal) {
            showFieldError('end-time', 'End time is required.');
            validationErrors.push('end-time');
            hasError = true;
        } else {
            clearFieldError('end-time');
        }

        if (startTimeVal && endTimeVal && endTimeVal <= startTimeVal) {
            showFieldError('end-time', 'End time must be later than start time.');
            validationErrors.push('end-time');
            hasError = true;
        } else if (endTimeVal) {
            clearFieldError('end-time');
        }

        /*
         * Recurrence validation
         *
         * once:
         *   no recurrence_rule_id
         *
         * daily:
         *   1 = Every day
         *   2 = Weekdays only
         *
         * weekly:
         *   no recurrence_rule_id
         *   weekday is required
         *
         * monthly:
         *   3 = Same day of month
         *   4 = Last day of month
         *
         * quarterly:
         *   5 = Same day of quarter
         *   6 = Last day of quarter
         */
        if (frequencyVal === 'once') {
            $recurrenceRule.val('');
            $weekday.val('');
            $monthDay.val('');
            $quarterMonth.val('');
        }

        if (frequencyVal === 'weekly') {
            // Weekly must never submit a recurrence rule.
            $recurrenceRule.val('');

            if (!weekdayVal) {
                showFieldError('weekday', 'Please select a weekday.');
                validationErrors.push('weekday');
                hasError = true;
            } else {
                clearFieldError('weekday');
            }
        }

        if (frequencyVal === 'daily') {
            if (!recurrenceRuleVal || (parseInt(recurrenceRuleVal, 10) !== 1 && parseInt(recurrenceRuleVal, 10) !== 2)) {
                showFieldError('recurrence-rule', 'Please select a recurrence option.');
                validationErrors.push('recurrence-rule');
                hasError = true;
            } else {
                clearFieldError('recurrence-rule');
            }

            $weekday.val('');
            $monthDay.val('');
            $quarterMonth.val('');
        }

        if (frequencyVal === 'monthly') {
            var monthlyRuleId = parseInt(recurrenceRuleVal, 10);

            if (monthlyRuleId !== 3 && monthlyRuleId !== 4) {
                showFieldError('recurrence-rule', 'Please select a recurrence option.');
                validationErrors.push('recurrence-rule');
                hasError = true;
            } else {
                clearFieldError('recurrence-rule');
            }

            if (monthlyRuleId === 3) {
                if (!monthDayVal || parseInt(monthDayVal, 10) < 1 || parseInt(monthDayVal, 10) > 30) {
                    showFieldError('month-day', 'Please select a valid day.');
                    validationErrors.push('month-day');
                    hasError = true;
                } else {
                    clearFieldError('month-day');
                }
            } else {
                $monthDay.val('');
                clearFieldError('month-day');
            }

            $weekday.val('');
            $quarterMonth.val('');
        }

        if (frequencyVal === 'quarterly') {
            var quarterlyRuleId = parseInt(recurrenceRuleVal, 10);

            if (quarterlyRuleId !== 5 && quarterlyRuleId !== 6) {
                showFieldError('recurrence-rule', 'Please select a recurrence option.');
                validationErrors.push('recurrence-rule');
                hasError = true;
            } else {
                clearFieldError('recurrence-rule');
            }

            // Same day of quarter
            if (quarterlyRuleId === 5) {
                if (!monthDayVal || parseInt(monthDayVal, 10) < 1 || parseInt(monthDayVal, 10) > 30) {
                    showFieldError('month-day', 'Please select a valid day.');
                    validationErrors.push('month-day');
                    hasError = true;
                } else {
                    clearFieldError('month-day');
                }

                if (!quarterMonthVal || parseInt(quarterMonthVal, 10) < 1 || parseInt(quarterMonthVal, 10) > 3) {
                    showFieldError('quarter-month', 'Please select the quarter month.');
                    validationErrors.push('quarter-month');
                    hasError = true;
                } else {
                    clearFieldError('quarter-month');
                }
            } else {
                // Last day of quarter does not need month/day selection.
                $monthDay.val('');
                $quarterMonth.val('');

                clearFieldError('month-day');
                clearFieldError('quarter-month');
            }

            $weekday.val('');
        }

        if (!hasNewFile) {
            if (existingParticipantsCount === 0) {
                showFieldError('participants-file', 'Participants are required. Please upload a CSV file.');
                validationErrors.push('participants-file');
                hasError = true;
            } else {
                clearFieldError('participants-file');
            }
        } else {
            var fileName = fileInput.files[0].name.toLowerCase();

            if (fileName.slice(-4) !== '.csv') {
                showFieldError('participants-file', 'Please upload a valid CSV file.');
                validationErrors.push('participants-file');
                hasError = true;
            } else {
                clearFieldError('participants-file');
            }
        }

        console.log('Schedule validation:', {
            frequency: frequencyVal,
            recurrenceRuleId: $recurrenceRule.val(),
            weekday: $weekday.val(),
            monthDay: $monthDay.val(),
            quarterMonth: $quarterMonth.val(),
            validationErrors: validationErrors,
            hasError: hasError
        });

        if (hasError) {
            console.log('Form submission stopped because of:', validationErrors);
            return false;
        }

        var $btn = $('#submit-schedule-btn');

        $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh"></i> Saving...');

        var formData = new FormData(form);

        console.log('Submitting schedule:', {
            frequency: formData.get('frequency'),
            recurrence_rule_id: formData.get('recurrence_rule_id'),
            weekday: formData.get('weekday'),
            month_day: formData.get('month_day'),
            quarter_month: formData.get('quarter_month')
        });

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',

            success: function(response) {
                if (response.status === 'success') {
                    $('#alert-container').html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + response.message + '</div>');

                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#alert-container').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + response.message + '</div>');

                    $btn.prop('disabled', false).html('<i class="glyphicon glyphicon-floppy-disk"></i> Save Schedule');
                }
            },

            error: function(xhr) {
                var message = 'An unexpected server error occurred.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $('#alert-container').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>');

                $btn.prop('disabled', false).html('<i class="glyphicon glyphicon-floppy-disk"></i> Save Schedule');
            }
        });

        return false;
    });
});