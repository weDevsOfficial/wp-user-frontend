function wpuf_show(el){

    var d = jQuery('#wpuf_field_values_row');
    if(jQuery(el).val() == 'select') {
        d.show();
    } else {
        d.hide();
    }
}

//tooltip function
jQuery(document).ready(function($) {

    //handle the ajax request
    $('form.wpuf_admin').submit(function(){
        data = $(this).serialize();
        //alert(data);
        $(this).append('<div class="wpuf_loading">Saving...</div>');

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,
            cache: false,
            success: function(response){
                $('.wpuf_loading').remove();
                var top = ( $(window).height() - 300 ) / 2 + $(window).scrollTop() + "px",
                    left = ( $(window).width() - 550 ) / 2;
                $('#option-saved').html(response).css({'top': top, 'left': left}).slideDown('fast').delay(1000).fadeOut('slow');
            }
        });

        return false;
    });

    // Switches option sections
    $('.group').hide();
    var activetab = '';
    if (typeof(localStorage) != 'undefined' ) {
        activetab = localStorage.getItem("activetab");
    }
    if (activetab != '' && $(activetab).length ) {
        $(activetab).fadeIn();
    } else {
        $('.group:first').fadeIn();
    }
    $('.group .collapsed').each(function(){
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function(){
                if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
            });
    });

    if (activetab != '' && $(activetab + '-tab').length ) {
        $(activetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }
    $('.nav-tab-wrapper a').click(function(evt) {
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", $(this).attr('href'));
        }
        $('.group').hide();
        $(clicked_group).fadeIn();
        evt.preventDefault();
    });

    $('.wpuf-admin #type').change();

});