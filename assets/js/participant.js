$(document).ready(function () {

    /*
     * ADD PARTICIPANT
     */

    $(document).on('submit', '#add-participant-form', function (e) {

        e.preventDefault();
        e.stopPropagation();

        var form = this;
        var formData = new FormData(form);

        var button = $('#add-participant-btn');
        var message = $('#participant-add-message');

        message.text('').css('color', '');

        button.prop('disabled', true);

        $.ajax({
            url: window.participantUrls.add,

            type: 'POST',

            data: formData,

            contentType: false,

            processData: false,

            dataType: 'json',

            success: function (response) {

                if (response.status === 'success') {

                    message
                        .text(response.message)
                        .css('color', '#16a34a');

                    form.reset();

                    setTimeout(function () {
                        window.location.reload();
                    }, 500);

                } else {

                    message
                        .text(
                            response.message ||
                            'Unable to add participant.'
                        )
                        .css('color', '#dc2626');
                }
            },

            error: function (xhr) {

                var response = xhr.responseJSON;

                message
                    .text(
                        response && response.message
                            ? response.message
                            : 'Unable to add participant.'
                    )
                    .css('color', '#dc2626');
            },

            complete: function () {
                button.prop('disabled', false);
            }
        });

        return false;
    });
    

    /*
     * EDIT PARTICIPANT
     */
    $(document).on('click', '.participant-edit-btn', function () {

        const row = $(this).closest('.participant-row');

        // Actual survey_participants.id
        const participantId = row.data('participant-id');

        if (!participantId) {
            alert('Participant ID not found.');
            return;
        }

        /*
         * Hide text values
         */
        row.find('.participant-first-name-text').addClass('hidden');
        row.find('.participant-last-name-text').addClass('hidden');
        row.find('.participant-email-text').addClass('hidden');

        /*
         * Show input fields
         */
        row.find('.participant-first-name-input').removeClass('hidden');
        row.find('.participant-last-name-input').removeClass('hidden');
        row.find('.participant-email-input').removeClass('hidden');

        /*
         * Change action buttons
         */
        row.find('.participant-actions').addClass('hidden');
        row.find('.participant-edit-actions').removeClass('hidden');
    });


    /*
     * CANCEL EDIT
     */
    $(document).on('click', '.participant-cancel-btn', function () {

        const row = $(this).closest('.participant-row');

        /*
         * Restore original values from display text
         */
        row.find('.participant-first-name-input')
            .val(row.find('.participant-first-name-text').text().trim());

        row.find('.participant-last-name-input')
            .val(row.find('.participant-last-name-text').text().trim());

        row.find('.participant-email-input')
            .val(row.find('.participant-email-text').text().trim());

        /*
         * Hide inputs
         */
        row.find('.participant-first-name-input').addClass('hidden');
        row.find('.participant-last-name-input').addClass('hidden');
        row.find('.participant-email-input').addClass('hidden');

        /*
         * Show text
         */
        row.find('.participant-first-name-text').removeClass('hidden');
        row.find('.participant-last-name-text').removeClass('hidden');
        row.find('.participant-email-text').removeClass('hidden');

        /*
         * Restore action buttons
         */
        row.find('.participant-edit-actions').addClass('hidden');
        row.find('.participant-actions').removeClass('hidden');
    });


    /*
     * SAVE PARTICIPANT
     */
    $(document).on('click', '.participant-save-btn', function () {

        const row = $(this).closest('.participant-row');

        // Actual DB ID
        const participantId = row.data('participant-id');
        const surveyId = row.data('survey-id');
       

        const firstName = row
            .find('.participant-first-name-input')
            .val()
            .trim();

        const lastName = row
            .find('.participant-last-name-input')
            .val()
            .trim();

        const email = row
            .find('.participant-email-input')
            .val()
            .trim();


        if (!participantId) {
            alert('Participant ID not found.');
            return;
        }

        if (!firstName) {
            alert('First name is required.');
            return;
        }

        if (!lastName) {
            alert('Last name is required.');
            return;
        }

        if (!email) {
            alert('Email is required.');
            return;
        }


        /*
         * Basic email validation
         */
        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }


        $.ajax({

            url: window.participantUrls.edit,

            type: 'POST',

            data: {
                id: participantId,
                survey_id: surveyId,
                first_name: firstName,
                last_name: lastName,
                email: email
            },

            dataType: 'json',

            success: function (response) {

                if (response.status === 'success') {

                    /*
                     * Update displayed values
                     */
                    row.find('.participant-first-name-text')
                        .text(firstName);

                    row.find('.participant-last-name-text')
                        .text(lastName);

                    row.find('.participant-email-text')
                        .text(email);


                    /*
                     * Hide inputs
                     */
                    row.find('.participant-first-name-input')
                        .addClass('hidden');

                    row.find('.participant-last-name-input')
                        .addClass('hidden');

                    row.find('.participant-email-input')
                        .addClass('hidden');


                    /*
                     * Show text
                     */
                    row.find('.participant-first-name-text')
                        .removeClass('hidden');

                    row.find('.participant-last-name-text')
                        .removeClass('hidden');

                    row.find('.participant-email-text')
                        .removeClass('hidden');


                    /*
                     * Restore action buttons
                     */
                    row.find('.participant-edit-actions')
                        .addClass('hidden');

                    row.find('.participant-actions')
                        .removeClass('hidden');

                } else {

                    alert(response.message || 'Unable to update participant.');
                }
            },

            error: function (xhr) {

                let message = 'Unable to update participant.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            }
        });
    });


    /*
     * DELETE PARTICIPANT
     */
    $(document).on('click', '.participant-delete-btn', function () {

        const button = $(this);

        const row = button.closest('.participant-row');

        // Actual DB ID
        const participantId = row.data('participant-id');


        if (!participantId) {
            alert('Participant ID not found.');
            return;
        }


        if (!confirm('Are you sure you want to delete this participant?')) {
            return;
        }


        $.ajax({

            url: window.participantUrls.delete,

            type: 'POST',

            data: {
                id: participantId
            },

            dataType: 'json',

            success: function (response) {

                if (response.status === 'success') {

                    /*
                     * Remove only this participant row
                     */
                    row.remove();

                } else {

                    alert(response.message || 'Unable to delete participant.');
                }
            },

            error: function (xhr) {

                let message = 'Unable to delete participant.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            }
        });
    });

});