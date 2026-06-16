<?php
/*
  If you would like to edit this file, copy it to your current theme's directory and edit it there.
  WPUF will always look in your theme's directory first, before using this default template.
 */
?>
<?php
$layout_class = isset( $layout_class ) ? trim( (string) $layout_class ) : '';

// Server-rendered, customizable text. Defaults match the historical English
// strings so non-Pro / unfiltered installs render identically. All values are
// supplied via the `wpuf_login_form_template_args` filter — see Simple_Login.
$form_title           = isset( $form_title ) ? (string) $form_title : '';
$form_subtitle        = isset( $form_subtitle ) ? (string) $form_subtitle : '';
$username_label       = isset( $username_label ) ? (string) $username_label : __( 'Username or Email', 'wp-user-frontend' );
$username_placeholder = isset( $username_placeholder ) ? (string) $username_placeholder : '';
$password_label       = isset( $password_label ) ? (string) $password_label : __( 'Password', 'wp-user-frontend' );
$password_placeholder = isset( $password_placeholder ) ? (string) $password_placeholder : '';
$remember_me_text     = isset( $remember_me_text ) ? (string) $remember_me_text : __( 'Remember Me', 'wp-user-frontend' );
$lost_password_text   = isset( $lost_password_text ) ? (string) $lost_password_text : __( 'Lost Password', 'wp-user-frontend' );
$button_text          = isset( $button_text ) ? (string) $button_text : __( 'Log In', 'wp-user-frontend' );
?>
<div class="login<?php echo $layout_class ? ' ' . esc_attr( $layout_class ) : ''; ?>" id="wpuf-login-form">

    <?php if ( $form_title !== '' || $form_subtitle !== '' ) : ?>
        <div class="wpuf-login-header">
            <?php if ( $form_title !== '' ) : ?>
                <h2 class="wpuf-login-title"><?php echo esc_html( $form_title ); ?></h2>
            <?php endif; ?>
            <?php if ( $form_subtitle !== '' ) : ?>
                <p class="wpuf-login-subtitle"><?php echo esc_html( $form_subtitle ); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php
    $message = apply_filters( 'login_message', '' );

    if ( ! empty( $message ) ) {
        echo wp_kses_post( $message ) . "\n";
    }
    ?>

    <?php
        /**
         * Render login form errors.
         *
         * Default handler: `Simple_Login::show_errors()`. Detach with
         * `remove_action( 'wpuf_login_show_errors', ... )` and supply your
         * own renderer to swap the error UI without overriding this template.
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_login_show_errors' );

        /**
         * Render login form messages.
         *
         * Default handler: `Simple_Login::show_messages()`. See
         * `wpuf_login_show_errors` for replacement pattern.
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_login_show_messages' );
    ?>

    <form name="loginform" class="wpuf-login-form" id="loginform" action="<?php echo esc_attr( $action_url ); ?>" method="post">
        <p>
            <label for="wpuf-user_login"><?php echo esc_html( $username_label ); ?></label>
            <input type="text" name="log" id="wpuf-user_login" class="input" value="" size="20" placeholder="<?php echo esc_attr( $username_placeholder ); ?>" />
        </p>
        <p>
            <label for="wpuf-user_pass"><?php echo esc_html( $password_label ); ?></label>
            <input type="password" name="pwd" id="wpuf-user_pass" class="input" value="" size="20" placeholder="<?php echo esc_attr( $password_placeholder ); ?>" />
        </p>

        <?php
            $recaptcha = wpuf_get_option( 'login_form_recaptcha', 'wpuf_profile', 'off' );
            $turnstile = wpuf_get_option( 'login_form_turnstile', 'wpuf_profile', 'off' );

            $turnstile_site_key = wpuf_get_option( 'turnstile_site_key', 'wpuf_general', '' );
        ?>
        <?php if ( $recaptcha === 'on' ) { ?>
            <p>
                <div class="wpuf-fields">
                    <?php echo wp_kses( recaptcha_get_html( wpuf_get_option( 'recaptcha_public', 'wpuf_general' ), true, null, is_ssl() ),[
                        'div' => [
                            'class' => [],
                            'data-sitekey' => [],
                        ],

                        'script' => [
                            'src' => []
                        ],

                        'noscript' => [],

                        'iframe' => [
                            'src' => [],
                            'height' => [],
                            'width' => [],
                            'frameborder' => [],
                        ],
                        'br' => [],
                        'textarea' => [
                            'name' => [],
                            'rows' => [],
                            'cols' => [],
                        ],
                        'input' => [
                            'type'   => [],
                            'value' => [],
                            'name'   => [],
                        ]
                    ] ); ?>
                </div>
            </p>
        <?php } ?>

        <?php if ( $turnstile === 'on' ) { ?>
            <div
                id="wpuf-turnstile"
                class="wpuf-turnstile"
                data-sitekey="<?php echo esc_attr( $turnstile_site_key ); ?>"
                data-callback="javascriptCallback"
            ></div>
            <script>
                window.onloadTurnstileCallback = function () {
                    turnstile.render("#wpuf-turnstile", {
                        sitekey: "<?php echo esc_js( $turnstile_site_key ); ?>",
                    });
                };
            </script>
        <?php } ?>

        <p class="forgetmenot wpuf-remember-forgot-wrapper">
            <span class="wpuf-remember-me">
                <input name="rememberme" type="checkbox" id="wpuf-rememberme" value="forever" />
                <label for="wpuf-rememberme"><?php echo esc_html( $remember_me_text ); ?></label>
            </span>
            <span class="wpuf-lost-password">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php echo esc_html( $lost_password_text ); ?></a>
            </span>
        </p>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php echo esc_attr( $button_text ); ?>" />
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
            <input type="hidden" name="wpuf_login" value="true" />
            <input type="hidden" name="action" value="login" />
            <?php wp_nonce_field( 'wpuf_login_action','wpuf-login-nonce' ); ?>
        </p>
        <p>
            <?php do_action( 'wpuf_login_form_bottom' ); ?>
        </p>
    </form>

    <div class="wpuf-action-links">
        <?php echo wp_kses_post( wpuf()->frontend->simple_login->get_action_links( [ 'login' => false, 'lostpassword' => false ] ) ); ?>
    </div>
</div>
