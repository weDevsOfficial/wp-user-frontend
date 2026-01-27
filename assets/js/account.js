 // Account page CSS entry point
import '../css/frontend/account.css';

// Account page JavaScript functionality
// Note: Form submission is handled by frontend-form.js
(function($) {
    'use strict';

    $(document).ready(function() {

        // Password toggle functionality
        $('.wpuf-password-toggle').on('click', function() {
            const input = $(this).siblings('input[type="password"], input[type="text"]');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).addClass('active');
            } else {
                input.attr('type', 'password');
                $(this).removeClass('active');
            }
        });

    });

})(jQuery);
