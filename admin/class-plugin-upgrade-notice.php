<?php
/**
 * In WordPress Dashboard plugins page, show notice if needed
 * The message is coming from wpuf-util
 *
 * @since 3.6.6
 */

class Plugin_Upgrade_Notice {

    /**
     * The notice transient key
     *
     * @var string
     */
    const NOTICE_KEY = 'wpuf_upgrade_notice';

    /**
     * The plugin path as it is within the plugins directory, ie
     * "some-plugin/main-file.php".
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin upgrade notice (empty if none are available).
     *
     * @var string
     */
    protected $upgrade_notice = '';

    /**
     * The class constructor
     *
     * @since 3.6.6
     */
    public function __construct() {
        $plugin_file       = WPUF_FILE;
        $this->plugin_path = trailingslashit( dirname( $plugin_file ) );
        $plugin_dir        = trailingslashit( basename( $this->plugin_path ) );
        $file              = $plugin_dir . basename( $plugin_file );

        // WordPress dynamic hook
        add_action( 'in_plugin_update_message-' . $file, [ $this, 'maybe_display_message' ] );
    }

    /**
     * Display notice upon checking
     *
     * @since 3.6.6
     *
     * @return void
     */
    public function maybe_display_message() {
        $this->check_for_notice();

        if ( $this->upgrade_notice ) {
            $this->render_notice();
        }
    }

    /**
     * Tests to see if an upgrade notice is available.
     *
     * @since 3.6.6
     *
     * @return void
     */
    protected function check_for_notice() {
        $notice_url = 'https://raw.githubusercontent.com/weDevsOfficial/wpuf-util/master/upgrade-notice.json';
        $response   = wp_remote_get( $notice_url, [ 'timeout' => 15 ] );
        $notice     = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || ( 200 !== $response['response']['code'] ) ) {
            $notice = '[]';
        }

        $notice = json_decode( $notice, true );

        if ( empty( $notice ) ) {
            return;
        }

        $min_version = ! empty( $notice['min-version'] ) ? $notice['min-version'] : '';

        if ( version_compare( WPUF_VERSION, $min_version,  '>=' ) ) {
            return;
        }

        $this->upgrade_notice = ! empty( $notice['message'] ) ? $notice['message'] : '';
    }

    /**
     * Render the notice under new version available message
     *
     * @since 3.6.6
     *
     * @return void
     */
    public function render_notice() {
        $notice = wp_kses_post( $this->upgrade_notice );
        echo "<div class='wpuf-update-message' style='color: #fff; background: #f54a20; padding: 10px;'> $notice </div>";
    }
}
