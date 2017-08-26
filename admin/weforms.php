<div class="wrap about-wrap">
    <h1><?php _e( 'weForms', 'wpuf' ); ?></h1>

    <p class="about-text"><?php _e( 'The Easiest &amp; Fastest Contact Form Plugin on WordPress', 'wpuf' ) ?></p>

    <hr>
    <p><?php _e( 'Quickly create rich contact forms to generate leads, taking feedbacks, onboarding visitors and flourishing <br /> your imagination! Comes with the best frontend post submission plugin for WordPress, WP User Frontend.', 'wpuf' ) ?>


    <div class="install" id="wpuf-weforms-installer-notice" style="padding: 1em 0; position: relative;">
        <p>
            <button id="wpuf-weforms-installer" class="button button-primary"><?php _e( 'Install Now', 'weforms' ); ?></button>
        </p>
    </div>

    <figure class="we-gif" style="width: 944px;">
        <img class="img-responsive inline-block image-gif shadow" src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2017/08/weforms-final-promo-video.gif" >
    </figure>
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

<style>
    .widget-wrap{
        width: 100%;
        text-align: center;
        align-content: center;
    }

</style>
