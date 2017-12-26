jQuery(function($) {
    $('.wpuf-notice-before-date').hide();
    $(".wpuf-sub-end-notice-enabled").click(function(){
        if($(this).prop("checked")) {
            $('.wpuf-notice-before-date').show();
        } else {
            $('.wpuf-notice-before-date').hide();
        }
    });
});
