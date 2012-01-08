function wpuf_show(o){

    var d=document.getElementById('wpuf_field_values_row');
    switch(o.value){
        case 'select': d.style.display='table-row'; break;
        case 'radio': d.style.display='table-row'; break;
        case 'checkbox': d.style.display='table-row'; break;
        default: d.style.display='none';
    }
}

//show/hide immediately on document load
jQuery(document).ready(function() {
    var n = document.getElementById('wpuf_field_values_row');
    console.log(n);
    if( n ) {
        wpuf_show(n);
    }
});

//tooltip function
jQuery(document).ready(function($) {

	//Select all anchor tag with rel set to tooltip
	$('.wpuf_help').mouseover(function(e) {

		//Grab the title attribute's value and assign it to a variable
		var tip = $(this).attr('title');

		//Remove the title attribute's to avoid the native tooltip from the browser
		$(this).attr('title','');

		//Append the tooltip template and its value
		$(this).append('<div class="tooltip"><div class="tipBody">' + tip + '</div></div>');

		//Show the tooltip with faceIn effect
		$('.tooltip').fadeIn('500');
		$('.tooltip').fadeTo('10',0.8);

	}).mousemove(function(e) {

		//Keep changing the X and Y axis for the tooltip, thus, the tooltip move along with the mouse
		$('.tooltip').css( {'left': e.pageX - 50, 'top': e.pageY + 5} );

	}).mouseout(function() {

		//Put back the title attribute's value
		$(this).attr('title',$('.tipBody').html());

		//Remove the appended tooltip template
		$(this).children('div.tooltip').remove();

	});



        //handle the ajax request
    $('.wpuf_admin').submit(function(){
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
                var top = ( $(window).height() - 300 ) / 2 + $(window).scrollTop() + "px";
                $('#option-saved').html(response).css('top', top).slideDown('fast').delay(1000).fadeOut('slow');
            }
        });

        return false;
    });

});