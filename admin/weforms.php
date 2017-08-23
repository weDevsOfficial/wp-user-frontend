<div class="wrap">
    <h2><?php _e( 'weForms', '' ); ?></h2>
    <div class="widget-wrap">
        <div class="widget-heading">
            <h2 class="heading-title"><?php _e( 'The Easiest &amp; Fastest Contact Form
Plugin on WordPress', 'wpuf' ) ?></h2>
        </div>
        <div class="widget-container">
            <p><?php _e( 'Quickly create rich contact forms to generate leads, taking feedbacks, onboarding visitors and flourishing <br /> your imagination! Comes with the best frontend post submission plugin for WordPress, WP User Frontend.', 'wpuf' ) ?>
            </p>
        </div>
        
        <div class="install" id="wpuf-weforms-installer-notice" style="padding: 1em; position: relative;">
            <p>
                <button id="wpuf-weforms-installer" class="btn btn-primary"><?php _e( 'Install Now', 'weforms' ); ?></button>
            </p>
        </div>

        <script type="text/javascript">
            (function ($) {
                var wrapper = $('#wpuf-weforms-installer-notice');

                wrapper.on('click', '#wpuf-weforms-installer', function (e) {
                    var self = $(this);

                    e.preventDefault();
                    self.addClass('install-now updating-message');
                    self.text('<?php echo esc_js( 'Installing...', 'weforms' ); ?>');
                    var data = {
                        action: 'wpuf_weforms_install',
                        _wpnonce: '<?php echo wp_create_nonce('wpuf-weforms-installer-nonce'); ?>'
                    };

                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            self.attr('disabled', 'disabled');
                            self.removeClass('install-now updating-message');
                            self.text('<?php echo esc_js( 'Installed', 'weforms' ); ?>');

                            window.location.href = '<?php echo admin_url( 'admin.php?page=weforms' ); ?>';
                        }
                    });
                });
            })(jQuery);
        </script>

        <div class="widget-container">
            <div class="elementor-text-editor elementor-clearfix">
                <figure class="we-gif" style="width: 944px;">
                    <img class="img-responsive inline-block image-gif shadow" src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2017/08/weforms-final-promo-video.gif" >
                </figure>
            </div>
        </div>
    </div>
</div>


<style>
    .widget-wrap{
        width: 100%;
        text-align: center;
        align-content: center;
    }
    .btn-primary{
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
    }
    .btn-primary:hover {
        color: #fff;
        background-color: #286090;
        border-color: #204d74;
    }
    .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
    }

</style>
