<?php

/**
 * What's New Class
 *
 * @since 2.7.0
 */
class WPUF_Whats_New {

    /**
     * Initialize the actions
     */
    function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );

        add_action( 'wp_ajax_wpuf_whats_new_dismiss', array( $this, 'dismiss_notice' ) );
    }

    /**
     * Check if a changelog is unread
     *
     * @return boolean
     */
    public function has_new() {
        $options = $this->get_option();

        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        if ( array_key_exists( WPUF_VERSION, $options ) && $options[WPUF_VERSION] === true ) {
            return false;
        }

        return true;
    }

    /**
     * Mark the current plugin changelog as read
     *
     * @return void
     */
    public function mark_read() {
        $options = $this->get_option();

        $options[WPUF_VERSION] = true;

        update_option( 'wpuf_whats_new', $options );
    }

    /**
     * Get the changelog history
     *
     * @return array
     */
    public function get_option() {
        return get_option( 'wpuf_whats_new', array() );
    }

    /**
     * Register the menu page
     *
     * @return void
     */
    public function register_menu() {
        add_submenu_page( null, __( 'Whats New', 'wpuf' ), __( 'Whats New', 'wpuf' ), 'manage_options', 'whats-new-wpuf', array( $this, 'menu_page' ) );
    }

    /**
     * Render the menu page
     *
     * @return void
     */
    public function menu_page() {

        $this->mark_read();

        include_once WPUF_ROOT . '/admin/html/whats-new.php';
    }

    /**
     * Show the admin notice if applicable
     *
     * @return void
     */
    public function admin_notice() {

        if ( ! $this->has_new() ) {
            return;
        }
        ?>
        <div class="notice notice-success wpuf-whats-new-notice free">

            <div class="wpuf-whats-new-icon">
                <img src="<?php echo WPUF_ASSET_URI . '/images/icon-128x128.png'; ?>" alt="WPUF Icon">
            </div>

            <div class="wpuf-whats-new-text">
                <p><strong><?php printf( __( 'WP User Frontend - Version %s', 'wpuf' ), WPUF_VERSION ); ?></strong></p>
                <p><?php printf( __( 'Welcome to the new version of WP User Frontend. See what\'s been changed in the <strong>%s</strong> version.', 'wpuf' ), WPUF_VERSION ); ?></strong></p>
            </div>

            <div class="wpuf-whats-new-actions">
                <a href="<?php echo admin_url( 'index.php?page=whats-new-wpuf' ); ?>" class="button button-primary"><?php _e( 'What\'s New?', 'wpuf' ); ?></a>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpuf' ); ?></span></button>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(function($) {

                var wrap = $('.wpuf-whats-new-notice.free');

                wrap.on('click', 'button.notice-dismiss', function(event) {
                    event.preventDefault();

                    wp.ajax.send( 'wpuf_whats_new_dismiss' );
                    wrap.remove();
                });
            });
        </script>
        <?php
    }

    /**
     * Mark the notice as dimissed via ajax
     *
     * @return void
     */
    public function dismiss_notice() {
        $this->mark_read();

        wp_send_json_success();
    }
}
