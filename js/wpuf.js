jQuery(document).ready(function($) {
    //process the contact us form
    $('#wpuf_new_post_form').submit(function() {

        var form = $(this);
        
        $(this).find('.requiredField').each(function() {
            if( $(this).hasClass('invalid') ) {
                $(this).removeClass('invalid');
            }
        });

        var hasError = false;

        $(this).find('.requiredField').each(function() {
            var labelText = $(this).prev('label').text();
            
            if(jQuery.trim($(this).val()) == '') {
                $(this).addClass('invalid');
                hasError = true;
            } else if($(this).hasClass('email')) {
                var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                if(!emailReg.test(jQuery.trim($(this).val()))) {
                    $(this).addClass('invalid');
                    hasError = true;
                }
            } else if($(this).hasClass('cat')) {
				var cat = document.getElementById('cat');
				console.log( cat.options[cat.selectedIndex].value );
				if( cat.options[cat.selectedIndex].value == '-1' ) {
					$(this).addClass('invalid');
                    hasError = true;
				}
			}
        });

        if( ! hasError ) {
            $(this).find('input[type=submit]').attr({
                'value': 'Please wait, I am posting...',
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
