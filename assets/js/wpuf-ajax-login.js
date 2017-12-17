function wpuf_ajax_open_login_dialog(href){

    jQuery('#wpuf-ajax-user-modal .modal-dialog').removeClass('registration-complete');

    var modal_dialog = jQuery('#wpuf-ajax-user-modal .modal-dialog');
    modal_dialog.attr('data-active-tab', '');

    switch(href){

        case '#wpuf-ajax-register':
            modal_dialog.attr('data-active-tab', '#wpuf-ajax-register');
            break;

        case '#wpuf-ajax-login':
        default:
            modal_dialog.attr('data-active-tab', '#wpuf-ajax-login');
            break;
    }

    jQuery('#wpuf-ajax-user-modal').modal('show');
}   

function wpuf_ajax_close_login_dialog(){

    jQuery('#wpuf-ajax-user-modal').modal('hide');
}   

jQuery(function($){

    "use strict";
    /***************************
    **  LOGIN / REGISTER DIALOG
    ***************************/

    // Open login/register modal
    $('[href="#wpuf-ajax-login"], [href="#wpuf-ajax-register"]').click(function(e){

        e.preventDefault();

        wpuf_ajax_open_login_dialog( $(this).attr('href') );

    });

    // Switch forms login/register
    $('.modal-footer a, a[href="#wpuf-ajax-reset-password"]').click(function(e){
        e.preventDefault();
        $('#wpuf-ajax-user-modal .modal-dialog').attr('data-active-tab', $(this).attr('href'));
    });


    // Post login form
    $('#wpuf-ajax-login-form').on('submit', function(e){

        e.preventDefault();

        var button = $(this).find('button');
            button.button('loading');

        $.post(wpuf_ajax.ajaxurl, $('#wpuf-ajax-login-form').serialize(), function(data){

            var obj = $.parseJSON(data);

            $('.wpuf-ajax-login .wpuf-ajax-errors').html(obj.message);
            
            if(obj.error == false){
                $('#wpuf-ajax-user-modal .modal-dialog').addClass('loading');
                window.location.reload(true);
                button.hide();
            }

            button.button('reset');
        });

    });


    // Post register form
    $('#wpuf-ajax-reg-form').on('submit', function(e){

        e.preventDefault();

        var button = $(this).find('button');
            button.button('loading');

        $.post(wpuf_ajax.ajaxurl, $('#wpuf-ajax-reg-form').serialize(), function(data){
            
            var obj = $.parseJSON(data);

            $('.wpuf-ajax-register .wpuf-ajax-errors').html(obj.message);
            
            if(obj.error == false){
                $('#wpuf-ajax-user-modal .modal-dialog').addClass('registration-complete');
                // window.location.reload(true);
                button.hide();
            }

            button.button('reset');
            
        });

    });

    // Logout
    $('[href="#logout"]').click(function(e){

        e.preventDefault();

        $.ajax({
            url: wpuf_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpuf_ajax_logout',
            },
            success: function(data) {

                if(data.error == false){
                    $('.wpuf-ajax-logout .wpuf-ajax-errors').html(data.message);
                    window.location.reload(true);
                }
            }
        });

    });


    // Reset Password
    $('#wpuf_ajax_reset_pass_form').on('submit', function(e){

        e.preventDefault();

        var button = $(this).find('button');
            button.button('loading');

        $.post(wpuf_ajax.ajaxurl, $('#wpuf_ajax_reset_pass_form').serialize(), function(data){

            var obj = $.parseJSON(data);

            $('.wpuf-ajax-reset-password .wpuf-ajax-errors').html(obj.message);
            
            // if(obj.error == false){
                // $('#wpuf-ajax-user-modal .modal-dialog').addClass('loading');
                // $('#wpuf-ajax-user-modal').modal('hide');
            // }

            button.button('reset');
        });

    });

    if(window.location.hash == '#login'){
        wpuf_ajax_open_login_dialog('#wpuf-ajax-login');
    }       

});