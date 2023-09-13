<?php

/**
 * Plugin Upgrade Routine
 *
 * @since 2.2
 */
class WPUF_Upgrades {

    /**
     * The upgrades
     *
     * @var array
     */
    private static $upgrades = [
        '2.1.9' => 'upgrades/upgrade-2.1.9.php',
        '2.6.0' => 'upgrades/upgrade-2.6.0.php',
        '2.7.0' => 'upgrades/upgrade-2.7.0.php',
        '2.8.0' => 'upgrades/upgrade-2.8.0.php',
        '2.8.2' => 'upgrades/upgrade-2.8.2.php',
        '2.8.5' => 'upgrades/upgrade-2.8.5.php',
        '2.9.2' => 'upgrades/upgrade-2.9.2.php',
        '3.6.0' => 'upgrades/upgrade-3.6.0.php',
    ];

    /**
     * The class constructor
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'show_upgrade_notice' ] );
        add_action( 'admin_init', [ $this, 'perform_updates' ] );
    }

    /**
     * Get the plugin version
     *
     * @return string
     */
    public function get_version() {
        return get_option( 'wpuf_version' );
    }

    /**
     * Check if the plugin needs any update
     *
     * @return bool
     */
    public function needs_update() {

        // may be it's the first install
        if ( !$this->get_version() ) {
            return false;
        }
        //check if current version is greater then installed version and any update key is available
        if ( version_compare( $this->get_version(), WPUF_VERSION, '<' ) && in_array( WPUF_VERSION, array_keys( self::$upgrades ) ) ) {
            return true;
        }

        return false;
    }

    /**
     * Perform all the necessary upgrade routines
     *
     * @return void
     */
    public function perform_updates() {
        if ( empty( $_GET['wpuf_do_update'] ) ) {
            return;
        }

        if ( ! sanitize_text_field( wp_unslash( $_GET['wpuf_do_update'] ) ) ) {
            return;
        }

        $installed_version = $this->get_version();
        $path              = trailingslashit( __DIR__ );

        foreach ( self::$upgrades as $version => $file ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                include $path . $file;
                update_option( 'wpuf_version', $version );
            }
        }

        update_option( 'wpuf_version', WPUF_VERSION );
    }

    /**
     * Show upgrade notice.
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function show_upgrade_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->needs_update() ) {
            return;
        }

        if ( $this->needs_update() ) {
            $url = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
            ?>
            <div id="message" class="updated">
                <p><?php printf( '<strong>%s</strong>', esc_attr__( 'WPUF Data Update Required', 'wp-user-frontend' ) ); ?></p>
                <p class="submit"><a href="<?php echo esc_url( add_query_arg( [ 'wpuf_do_update' => true ], $url ) ); ?>" class="wpuf-update-btn button-primary"><?php esc_attr_e( 'Run the updater', 'wp-user-frontend' ); ?></a></p>
            </div>

            <script type="text/javascript">
                jQuery('.wpuf-update-btn').click('click', function() {
                    return confirm( '<?php esc_attr_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wp-user-frontend' ); ?>' );
                });
            </script>
            <?php
        }
    }
}
