/**
 * WPUF Two-Factor Authentication — Account Security tab
 *
 * Drives enrollment, confirmation, code-issuance, and self-disable flows
 * on the [wpuf_account] Security tab. Generic across methods: every
 * card has a data-method-id attribute and the four AJAX actions take
 * method_id as a parameter. Method-specific extras (TOTP QR code,
 * Email OTP masked destination, etc.) come from the props returned by
 * Method_Interface::start_enrollment() and are rendered client-side
 * via the renderEnrollmentExtras() dispatcher below.
 *
 * Methods that need a fresh code before self-disable (Email OTP, SMS
 * OTP) are detected via the presence of `.wpuf-2fa-issue-disable-code`
 * being unhidden by the renderer; TOTP keeps it hidden because the
 * code is always available in the user's authenticator app.
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
            $msg.text(message || '');
            $msg.removeClass('wpuf-text-red-600 wpuf-text-green-700');
            $msg.addClass(isError ? 'wpuf-text-red-600' : 'wpuf-text-green-700');
        }

        function methodIdFor($card) {
            return $card.data('method-id') || $card.attr('data-method-id') || '';
        }

        // --- Enrollment extras renderers ----------------------------------
        // Per-method renderers fill `.wpuf-2fa-enrollment-extras` based on
        // whatever start_enrollment() returned. Each method gets one
        // function; default falls back to a generic instructions blurb.
        var extrasRenderers = {
            totp: function ($extras, props) {
                var html = '';
                html += '<div class="wpuf-flex wpuf-flex-wrap wpuf-gap-6 wpuf-items-start">';
                html +=   '<div class="wpuf-2fa-qr-target">' + (props.qr_svg || '') + '</div>';
                html +=   '<div class="wpuf-flex-1 wpuf-min-w-0">';
                html +=     '<label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">';
                html +=       'Manual entry key';
                html +=     '</label>';
                html +=     '<input type="text" readonly value="' + escapeAttr(props.secret || '') + '"';
                html +=       ' class="wpuf-font-mono wpuf-text-sm wpuf-w-full wpuf-max-w-xs wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-gray-50 wpuf-text-gray-700 wpuf-px-3 wpuf-py-2" />';
                html +=   '</div>';
                html += '</div>';
                $extras.html(html);
            },
            // Default for any method we don't have a custom renderer for.
            // The textual `note` is rendered separately into
            // `.wpuf-2fa-enrollment-instructions` by the calling code, so
            // we leave `$extras` empty here. Methods that need dynamic
            // UI (QR codes, masked-destination fields) provide their
            // own renderer keyed by method id.
            _default: function ($extras /*, props */) {
                $extras.empty();
            }
        };

        function renderEnrollmentExtras(methodId, $extras, props) {
            var renderer = extrasRenderers[methodId] || extrasRenderers._default;
            renderer($extras, props || {});
        }

        function escapeHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttr(s) {
            return escapeHtml(s);
        }

        // --- Enrollment: start --------------------------------------------
        $root.on('click', '.wpuf-2fa-method-start', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            var $card     = $btn.closest('.wpuf-2fa-method-card');
            var methodId  = methodIdFor($card);
            var $enroll   = $card.find('.wpuf-2fa-method-enrollment');

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_method_start',
                _wpnonce: nonce,
                method_id: methodId
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = (response && response.data && response.data.message)
                        ? response.data.message
                        : 'Could not start setup.';
                    showMessage($enroll, msg, true);
                    $btn.prop('disabled', false);
                    return;
                }

                var $extras = $enroll.find('.wpuf-2fa-enrollment-extras');
                renderEnrollmentExtras(methodId, $extras, response.data);

                if (response.data && response.data.note) {
                    $enroll.find('.wpuf-2fa-enrollment-instructions').text(response.data.note);
                } else {
                    $enroll.find('.wpuf-2fa-enrollment-instructions').empty();
                }

                $enroll.removeClass('wpuf-hidden');
                $enroll.find('.wpuf-2fa-method-code').focus();
            }).fail(function () {
                showMessage($enroll, 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });

        // --- Enrollment: confirm ------------------------------------------
        $root.on('click', '.wpuf-2fa-method-confirm', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var $card    = $btn.closest('.wpuf-2fa-method-card');
            var methodId = methodIdFor($card);
            var $enroll  = $card.find('.wpuf-2fa-method-enrollment');
            var code     = $enroll.find('.wpuf-2fa-method-code').val();

            if (!code) {
                showMessage($enroll, 'Enter the verification code.', true);
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_method_confirm',
                _wpnonce: nonce,
                method_id: methodId,
                code: code
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = (response && response.data && response.data.message)
                        ? response.data.message
                        : 'Could not verify the code.';
                    showMessage($enroll, msg, true);
                    $btn.prop('disabled', false);

                    if (response && response.data && response.data.expired) {
                        // Pending state expired — bounce back to the start.
                        $enroll.addClass('wpuf-hidden');
                        $card.find('.wpuf-2fa-method-start').prop('disabled', false);
                    }
                    return;
                }

                showMessage($enroll, response.data.message, false);
                window.setTimeout(function () { window.location.reload(); }, 800);
            }).fail(function () {
                showMessage($enroll, 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });

        // --- Issue a disable code (Email OTP, SMS OTP) --------------------
        $root.on('click', '.wpuf-2fa-issue-disable-code', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var $card    = $btn.closest('.wpuf-2fa-method-card');
            var methodId = methodIdFor($card);
            var $form    = $card.find('.wpuf-2fa-disable-form');

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_method_issue',
                _wpnonce: nonce,
                method_id: methodId
            }).done(function (response) {
                $btn.prop('disabled', false);
                if (!response || !response.success) {
                    var msg = (response && response.data && response.data.message)
                        ? response.data.message
                        : 'Could not send the code.';
                    showMessage($form, msg, true);
                    return;
                }
                showMessage($form, response.data.message, false);
            }).fail(function () {
                showMessage($form, 'Network error. Please try again.', true);
                $btn.prop('disabled', false);
            });
        });

        // --- Disable -------------------------------------------------------
        $root.on('click', '.wpuf-2fa-method-disable', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            var $card     = $btn.closest('.wpuf-2fa-method-card');
            var methodId  = methodIdFor($card);
            var $form     = $card.find('.wpuf-2fa-disable-form');
            var password  = $form.find('.wpuf-2fa-disable-password').val();
            var code      = $form.find('.wpuf-2fa-disable-code').val();

            if (!password) {
                showMessage($form, 'Enter your current password.', true);
                return;
            }
            if (!code) {
                showMessage($form, 'Enter the verification code.', true);
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'wpuf_2fa_method_disable',
                _wpnonce: nonce,
                method_id: methodId,
                password: password,
                code: code
            }).done(function (response) {
                if (!response || !response.success) {
                    var msg = (response && response.data && response.data.message)
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
