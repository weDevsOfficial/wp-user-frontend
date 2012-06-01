jQuery(document).ready(function($) {
    //process the contact us form
    $('#wpuf_new_post_form, #wpuf_edit_post_form').submit(function() {

        var form = $(this);

        form.find('.requiredField').each(function() {
            if( $(this).hasClass('invalid') ) {
                $(this).removeClass('invalid');
            }
        });

        var hasError = false;

        $(this).find('.requiredField').each(function() {
            var el = $(this),
                labelText = el.prev('label').text();

            if(jQuery.trim(el.val()) == '') {
                el.addClass('invalid');
                hasError = true;
            } else if(el.hasClass('email')) {
                var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                if(!emailReg.test($.trim(el.val()))) {
                    el.addClass('invalid');
                    hasError = true;
                }
            } else if(el.hasClass('cat')) {
                if( el.val() == '-1' ) {
                    el.addClass('invalid');
                    hasError = true;
                }
            }
        });

        if( ! hasError ) {
            $(this).find('input[type=submit]').attr({
                'value': wpuf.postingMsg,
                'disabled': true
            });

            return true;
        }

        return false;
    });

    function check_pass_strength() {
        var pass1 = $('#pass1').val(), user = $('#user_login1').val(), pass2 = $('#pass2').val(), strength;

        $('#pass-strength-result').removeClass('short bad good strong');
        if ( ! pass1 ) {
            $('#pass-strength-result').html( pwsL10n.empty );
            return;
        }

        strength = passwordStrength(pass1, user, pass2);

        switch ( strength ) {
            case 2:
                $('#pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
                break;
            case 3:
                $('#pass-strength-result').addClass('good').html( pwsL10n['good'] );
                break;
            case 4:
                $('#pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
                break;
            case 5:
                $('#pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
                break;
            default:
                $('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
        }
    }

    //editprofile password strength
    $('#pass1').val('').keyup( check_pass_strength );
    $('#pass2').val('').keyup( check_pass_strength );
    $('#pass-strength-result').show();
});


jQuery(document).ready(function() {
    //if on page load the parent category is already selected, load up the child categories
    jQuery('#catlvl0').attr('level', 0);
    if (jQuery('#catlvl0 #cat').val() > 0) {
        wpuf_getChildrenCategories(jQuery(this),'catlvl-', 1, 'yes');
    }
    //bind the ajax lookup event to #cat object
    jQuery('#wpuf_new_post_form #cat').live('change', function(){
        currentLevel = parseInt(jQuery(this).parent().attr('level'));
        wpuf_getChildrenCategories(jQuery(this), 'catlvl', currentLevel+1, 'yes');

        //rebuild the entire set of dropdowns based on which dropdown was changed
        jQuery.each(jQuery(this).parent().parent().children(), function(childLevel, childElement) {
            if(currentLevel+1 < childLevel) jQuery(childElement).remove();
            if(currentLevel+1 == childLevel) jQuery(childElement).removeClass('hasChild');
        //console.log(childElement);
        });

        //find the deepest selected category and assign the value to the "chosenCateory" field
        if(jQuery(this).val() > 0) jQuery('#chosenCategory input:first').val(jQuery(this).val());
        else if(jQuery('#catlvl'+(currentLevel-1)+' select').val() > 0) jQuery('#chosenCategory input:first').val(jQuery('#catlvl'+(currentLevel-1)+' select').val());
        else jQuery('#chosenCategory input:first').val('-1');
    });
});

function wpuf_getChildrenCategories(dropdown, results_div_id, level, allow_parent_posting) {
    parent_dropdown = jQuery(dropdown).parent();
    category_ID = jQuery(dropdown).val();
    results_div = results_div_id+level;
    if(!jQuery(parent_dropdown).hasClass('hasChild'))
        jQuery(parent_dropdown).addClass('hasChild').parent().append('<div id="'+results_div+'" level="'+level+'" class="childCategory"></div>')

    jQuery.ajax({
        type: "post",
        url: wpuf.ajaxurl,
        data: {
            action: 'wpuf_get_child_cats',
            //_ajax_nonce: '<?php //echo $nonce; ?>',
            catID : category_ID
        },
        beforeSend: function() {
            jQuery('#getcat').hide();
            jQuery('#categories-footer').addClass('wpuf_loading').slideDown("fast");
        }, //show loading just when dropdown changed
        complete: function() {
            jQuery('#categories-footer').removeClass('wpuf_loading');
        }, //stop showing loading when the process is complete
        success: function(html){ //so, if data is retrieved, store it in html
            //if no categories are found
            if(html == "") {
                jQuery('#'+results_div).slideUp("fast");
                whenEmpty = true;
            }
            //child categories found so build and display them
            else {
                jQuery('#'+results_div).html(html).slideDown("fast"); //build html from ajax post
                /* FANCY SELECT BOX ACTIVATOR - UNCOMMENT ONCE ITS READY
                jQuery('#'+results_div+" #cat").selectBox({ menuTransition: 'fade', menuSpeed: 'fast' });
                */
                jQuery('#'+results_div+" a").fadeIn(); //fade in the new dropdown (selectBox converts to <a>
                whenEmpty = false;
            }

            //always check if go button should be on or off, jQuery parent is used for traveling backup the category heirarchy
            if( (allow_parent_posting == 'yes' &&  jQuery('#chosenCategory input:first').val() > 0) ){
                jQuery('#getcat').fadeIn();
            }
            //check for empty category option
            else if(whenEmpty && allow_parent_posting == 'whenEmpty' && jQuery('#chosenCategory input:first').val() > 0) {
                jQuery('#getcat').fadeIn();
            }
            //if child category exists, is set, and allow_parent_posting not set to "when empty"
            else if(jQuery('#'+results_div_id+(level-1)).hasClass('childCategory') && jQuery(dropdown).val() > -1 && allow_parent_posting == 'no') {
                jQuery('#getcat').fadeIn();
            }
            else {
                jQuery('#getcat').fadeOut();
            }

        }
    }); //close jQuery.ajax(
} // end of JavaScript function js_cp_getChildrenCategories
