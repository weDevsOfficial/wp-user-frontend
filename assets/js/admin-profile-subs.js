jQuery(document).ready(function($) {
	$('.p-exp-time').hide();
	$(".p_exp_enabled").click(function(){

		if($(this).prop("checked")) {
	    	$('.p-exp-time').show();
	  	} else {
	    	$('.p-exp-time').hide();
	  	}
	});
});