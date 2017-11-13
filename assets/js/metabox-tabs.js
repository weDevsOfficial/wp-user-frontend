jQuery(document).ready(function() {
	// tab between them
	jQuery('.metabox-tabs li a').each(function(i) {
		var thisTab = jQuery(this).parent().attr('class').replace(/active /, '');

		if ( 'active' != jQuery(this).attr('class') )
			jQuery('div.' + thisTab).hide();
		
		jQuery('div.' + thisTab).addClass('tab-content');
 
		jQuery(this).click(function(){
			// hide all child content
			jQuery(this).parent().parent().parent().children('div').hide();
 
			// remove all active tabs
			jQuery(this).parent().parent('ul').find('li.active').removeClass('active');
 
			// show selected content
			jQuery(this).parent().parent().parent().find('div.'+thisTab).show();
			jQuery(this).parent().parent().parent().find('li.'+thisTab).addClass('active');
		});
	});

	jQuery('.heading').hide();
	jQuery('.metabox-tabs').show();
});