(function ($) {

    'use strict';

    $(document).ready(function () {
        /*
         * Close the mobile sidebar after selecting a menu item.
         */
        $('.sk-nav a').on('click', function () {

            if ($(window).width() < 768) {
                $('#sk-sidebar').collapse('hide');
            }

        });
    });
    
})(jQuery);