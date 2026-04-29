/**
 * WPUF Two-Factor Authentication — Account Security tab
 *
 * Drives the enrollment, confirmation, and self-disable flows on the
 * [wpuf_account] Security tab. Talks to the AJAX endpoints registered
 * by Enrollment_Controller.
 */
(function ($) {
    'use strict';

    $(function () {
        var $root = $('#wpuf-2fa-security');

        if (!$root.length) {
            return;
        }

        var ajaxUrl = $root.data('ajax-url');
        var nonce   = $root.data('nonce');

        function showMessage($container, message, isError) {
            var $msg = $container.find('.wpuf-2fa-message').first();
            $msg.text(message);
            $msg.removeClass('wpuf-text-red-600 wpuf-text-green-700');
            $msg.addClass(isError ? 'wpuf-text-red-600' : 'wpuf-text-green-700');
        }

        // --- Enrollment: start ---------------------------------------------
        $root.on('click', '.wpuf-2fa-totp-start', function (e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_totp_start',
                _wpnonce: nonce
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = response && response.data && response.data.message
                        ? response.data.message
                        : 'Could not start setup.';
                    showMessage($root.find('.wpuf-2fa-totp-enrollment'), msg, true);
                    $btn.prop('disabled', false);
                    return;
                }

                var data       = response.data;
                var $enroll    = $root.find('.wpuf-2fa-totp-enrollment');
                var $qr        = $enroll.find('.wpuf-2fa-qr-target');
                var $secretIn  = $enroll.find('.wpuf-2fa-secret-display');

                $qr.html(data.qr_svg || '');
                $secretIn.val(data.secret || '');
                $enroll.removeClass('wpuf-hidden');
                $btn.prop('disabled', true).text(
                    $btn.data('done-label') || 'Set up in progress…'
                );
                $enroll.find('.wpuf-2fa-totp-code').focus();
            }).fail(function () {
                showMessage($root.find('.wpuf-2fa-totp-enrollment'), 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });

        // --- Enrollment: confirm -------------------------------------------
        $root.on('click', '.wpuf-2fa-totp-confirm', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $enroll = $root.find('.wpuf-2fa-totp-enrollment');
            var code = $enroll.find('.wpuf-2fa-totp-code').val();

            if (!code || code.length !== 6) {
                showMessage($enroll, 'Enter the 6-digit code from your app.', true);
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_totp_confirm',
                _wpnonce: nonce,
                code: code
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = response && response.data && response.data.message
                        ? response.data.message
                        : 'Could not verify the code.';
                    showMessage($enroll, msg, true);
                    $btn.prop('disabled', false);

                    if (response && response.data && response.data.expired) {
                        // Pending transient gone — make the user start over.
                        $enroll.addClass('wpuf-hidden');
                        $root.find('.wpuf-2fa-totp-start').prop('disabled', false);
                    }
                    return;
                }

                showMessage($enroll, response.data.message, false);
                // Reload so the tab re-renders into the enrolled state.
                window.setTimeout(function () { window.location.reload(); }, 800);
            }).fail(function () {
                showMessage($enroll, 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });

        // --- Disable -------------------------------------------------------
        $root.on('click', '.wpuf-2fa-disable-submit', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $form = $root.find('.wpuf-2fa-disable-form');
            var password = $form.find('.wpuf-2fa-disable-password').val();
            var code     = $form.find('.wpuf-2fa-disable-code').val();

            if (!password) {
                showMessage($form, 'Enter your current password.', true);
                return;
            }
            if (!code || code.length !== 6) {
                showMessage($form, 'Enter the 6-digit code from your app.', true);
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_totp_disable',
                _wpnonce: nonce,
                password: password,
                code: code
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = response && response.data && response.data.message
                        ? response.data.message
                        : 'Could not disable 2FA.';
                    showMessage($form, msg, true);
                    $btn.prop('disabled', false);
                    return;
                }

                showMessage($form, response.data.message, false);
                window.setTimeout(function () { window.location.reload(); }, 800);
            }).fail(function () {
                showMessage($form, 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });
    });

})(jQuery);
