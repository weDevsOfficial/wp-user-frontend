jQuery(function($) {

    function show_sub_sections() {
        $('.pre-sub-exp-notify-date').show();
        $('.post-sub-exp-notify-date').show();
        $('.pre-sub-exp-sub').show();
        $('.pre-sub-exp-body').show();
        $('.post-sub-exp-sub').show();
        $('.post-sub-exp-body').show()
    }

    function hide_sub_sections() {
        $('.pre-sub-exp-notify-date').hide();
        $('.post-sub-exp-notify-date').hide();
        $('.pre-sub-exp-sub').hide();
        $('.pre-sub-exp-body').hide();
        $('.post-sub-exp-sub').hide();
        $('.post-sub-exp-body').hide();
    }

    if ( $("#wpuf-wpuf_mails\\[enable_subs_notification\\]").attr('checked')) {
        show_sub_sections();
    } else {
        hide_sub_sections();
    }

    $("#wpuf-wpuf_mails\\[enable_subs_notification\\]").click( function() {
        if( $(this).prop("checked") ) {
            show_sub_sections();
        } else {
            hide_sub_sections();
        }
    });

    // Collapsable email settings field
    group = [
        '.email-setting',
        '.guest-email-setting',
        '.reset-email-setting',
        '.confirmation-email-setting',
        '.subscription-setting',
        '.admin-new-user-email',
        '.pending-user-email',
        '.denied-user-email',
        '.approved-user-email'
    ]
    group.forEach(function(header, index) {
        $(header).addClass("heading");
        $(header+"-option").addClass("hide");

        $("#wpuf_mails "+header).click(function() {
            $(header+"-option").toggleClass("hide");
        });
    })

});
