jQuery(function($) {
    $('.pre-sub-exp-notify-date').hide();
    $('.post-sub-exp-notify-date').hide();
    $('.pre-sub-exp-sub').hide();
    $('.pre-sub-exp-body').hide();
    $('.post-sub-exp-sub').hide();
    $('.post-sub-exp-body').hide();

    $("#wpuf-wpuf_mails\\[enable_subs_notification\\]").click( function() {
        if( $(this).prop("checked") ) {
            $('.pre-sub-exp-notify-date').show();
            $('.post-sub-exp-notify-date').show();
            $('.pre-sub-exp-sub').show();
            $('.pre-sub-exp-body').show();
            $('.post-sub-exp-sub').show();
            $('.post-sub-exp-body').show();
        } else {
            $('.pre-sub-exp-notify-date').hide();
            $('.post-sub-exp-notify-date').hide();
            $('.pre-sub-exp-sub').hide();
            $('.pre-sub-exp-body').hide();
            $('.post-sub-exp-sub').hide();
            $('.post-sub-exp-body').hide();
        }
    });
});
