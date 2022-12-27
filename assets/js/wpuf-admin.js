jQuery(function($) {

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
        '.approved-user-email',
        '.approved-post-email',
        '.account-activated-user-email'
    ]
    group.forEach(function(header, index) {
        $(header).addClass("heading");
        $(header+"-option").addClass("hide");

        $("#wpuf_mails "+header).click(function() {
            $(header+"-option").toggleClass("hide");
        });
    })

    // Checked layout radio input field after clicking image
    $(".wpuf-form-layouts li").click(function() {
        $(this.children[0]).attr("checked", "checked");
        $(".wpuf-form-layouts li").removeClass('active');
        $(this).toggleClass('active');
    });

    // Clear schedule lock
    $('#wpuf_clear_schedule_lock').on('click', function(e) {
        e.preventDefault();
        var post_id = $(this).attr('data');

        $.ajax({
            url: wpuf_admin_script.ajaxurl,
            type: 'POST',
            data: {
                'action'    : 'wpuf_clear_schedule_lock',
                'nonce'     : wpuf_admin_script.nonce,
                'post_id'   : post_id
            },
            success:function(data) {
                Swal.fire({
                    icon: 'success',
                    title: wpuf_admin_script.cleared_schedule_lock,
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
        $(this).closest("p").hide();
    });

    // override settings tab preventDefault action on lib/class.settings-api.php for tooltip link
    $('.button-upgrade-to-pro').click(function(e) {
        e.stopPropagation();
    });

    // show tooltips on crown icons
    $('th span.pro-icon, td label span.pro-icon-title, th label span.pro-icon-title, a span.pro-icon-title').on('mouseover', function() {
        let tooltip = $( '.wpuf-pro-field-tooltip' );
        let windowWidth = $( window ).width();
        let windowHeight = $( window ).height();
        let iconBounding = $( this )[0].getBoundingClientRect();
        let spaceTop = iconBounding.y;
        let iconBoundingRight = iconBounding.right;
        let iconBoundingBottom = iconBounding.bottom;
        let spaceRight = windowWidth - iconBoundingRight;
        let spaceBottom = windowHeight - iconBoundingBottom;
        let tooltipHeight = tooltip.outerHeight();
        let tooltipWidth = tooltip.outerWidth();

        if ( spaceTop > tooltipHeight ) {
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '50%' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '100%' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'initial' );
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '50%' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '0' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '-1em' );
            $( 'span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'top', '-1em' );
            $( 'tr.wpuf-subscription-recurring span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'top', '-.5em' );
        } else if ( spaceTop < tooltipHeight && spaceRight > tooltipWidth ) {
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '-5px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '22px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'rotate(90deg)' );
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '185px' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '310px' );
            $( 'a span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'left', '175px' );
            $( 'a span.pro-icon-title .wpuf-pro-field-tooltip i' ).css( 'top', '15px' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'left', '175px' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '295px' );
        } else if ( spaceBottom > tooltipHeight ) {
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '10px' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '360px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '-10px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '150px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'rotate(180deg)' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'left', '0' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '0' );
        }

        tooltip.appendTo( this );
    });
});
