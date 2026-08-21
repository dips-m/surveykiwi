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

        var firstNameInput = $('#participant-first-name');
        var lastNameInput = $('#participant-last-name');
        var emailInput = $('#participant-email');

        var firstNameMessage = $('#participant-first-name-message');
        var lastNameMessage = $('#participant-last-name-message');
        var emailMessage = $('#participant-email-message');

        /*
        * Clear previous messages.
        */
        $('.participant-field-message')
            .removeClass('error success')
            .text('');

        var firstName = firstNameInput.val().trim();
        var lastName = lastNameInput.val().trim();
        var email = emailInput.val().trim();

        if (!firstName) {
            firstNameMessage
                .addClass('error')
                .text('First name is required.');

            firstNameInput.focus();
            return false;
        }

        if (!lastName) {
            lastNameMessage
                .addClass('error')
                .text('Last name is required.');

            lastNameInput.focus();
            return false;
        }

        if (!email) {
            emailMessage
                .addClass('error')
                .text('Email is required.');

            emailInput.focus();
            return false;
        }

        /*
        * Basic email validation.
        */
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            emailMessage
                .addClass('error')
                .text('Please enter a valid email address.');

            emailInput.focus();
            return false;
        }

        button.prop('disabled', true);

        $.ajax({
            url: window.participantUrls.add,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',

            success: function (response) {

                if (response.status) {

                    emailMessage
                        .addClass('success')
                        .text(response.message || 'Participant added successfully.');

                    form.reset();

                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);

                } else {

                    emailMessage
                        .addClass('error')
                        .text(
                            response.message ||
                            'Unable to add participant.'
                        );
                }
            },

            error: function (xhr) {

                var response = xhr.responseJSON;

                emailMessage
                    .addClass('error')
                    .text(
                        response && response.message
                            ? response.message
                            : 'Unable to add participant.'
                    );
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

        const firstNameInput = row.find('.participant-first-name-input');
        const lastNameInput = row.find('.participant-last-name-input');
        const emailInput = row.find('.participant-email-input');

        const firstName = firstNameInput.val().trim();
        const lastName = lastNameInput.val().trim();
        const email = emailInput.val().trim();

        // Clear previous messages
        row.find('.participant-field-message')
            .removeClass('error success')
            .text('');

        if (!participantId) {
            row.find('.participant-email-message')
                .addClass('error')
                .text('Participant ID not found.');
            return;
        }

        if (!firstName) {
            firstNameInput.focus();

            row.find('.participant-first-name-message')
                .addClass('error')
                .text('First name is required.');

            return;
        }

        if (!lastName) {
            lastNameInput.focus();

            row.find('.participant-last-name-message')
                .addClass('error')
                .text('Last name is required.');

            return;
        }

        if (!email) {
            emailInput.focus();

            row.find('.participant-email-message')
                .addClass('error')
                .text('Email is required.');

            return;
        }

        /*
        * Basic email validation
        */
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            emailInput.focus();

            row.find('.participant-email-message')
                .addClass('error')
                .text('Please enter a valid email address.');

            return;
        }

        const saveButton = row.find('.participant-save-btn');

        saveButton.prop('disabled', true);

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
                    firstNameInput.addClass('hidden');
                    lastNameInput.addClass('hidden');
                    emailInput.addClass('hidden');

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

                    /*
                    * Show success message
                    */
                    row.find('.participant-email-message')
                        .addClass('success')
                        .text(response.message || 'Participant updated successfully.');

                } else {

                    row.find('.participant-email-message')
                        .addClass('error')
                        .text(response.message || 'Unable to update participant.');
                }
                setTimeout(function () {
                        window.location.reload();
                }, 1000);
            },

            error: function (xhr) {

                let message = 'Unable to update participant.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                row.find('.participant-email-message')
                    .addClass('error')
                    .text(message);
            },

            complete: function () {
                saveButton.prop('disabled', false);
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

        let participantCount = window.participantUrls.participantCount;
        console.log('participantCount:', participantCount);
        


        if (!participantId) {
            alert('Participant ID not found.');
            return;
        }

        if (parseInt(participantCount, 10) === 1) {
            alert('Schedule requires at least one participant.');
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
                    location.reload();

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