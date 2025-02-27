<?php

namespace WeDevs\Wpuf\Admin;

/**
 * wpuf tinyMce Shortcode Button class
 *
 * @since 2.5.2
 */
class Shortcodes_Button {
    /**
     * Constructor for shortcode class
     */
    public function __construct() {
        add_filter( 'mce_external_plugins', [ $this, 'enqueue_plugin_scripts' ] );
        add_filter( 'mce_buttons', [ $this, 'register_buttons_editor' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'localize_shortcodes' ], 90 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 80 );
        add_action( 'media_buttons', [ $this, 'add_media_button' ], 20 );
        add_action( 'admin_footer', [ $this, 'media_thickbox_content' ] );
    }

    /**
     * Enqueue scripts and styles for form builder
     *
     * @return void
     * @global string $pagenow
     *
     */
    public function enqueue_scripts() {
        global $pagenow;

        if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
            return;
        }

        wp_enqueue_script( 'wpuf-admin-shortcode' );
    }

    /**
     * Adds a media button (for inserting a form) to the Post Editor
     *
     * @param int $editor_id The editor ID
     *
     * @return void
     */
    public function add_media_button( $editor_id ) {
        ?>
        <a href="#TB_inline?width=480&amp;inlineId=wpuf-media-dialog" class="button thickbox insert-form"
           data-editor="<?php echo esc_attr( $editor_id ); ?>"
           title="<?php esc_html_e( 'Add a Form', 'wp-user-frontend' ); ?>">
            <?php echo wp_kses_post(
                '<span class="wp-media-buttons-icon dashicons dashicons-welcome-widgets-menus"></span>' . __(
                    ' Add Form', 'wp-user-frontend'
                )
            ); ?>
        </a>
        <?php
    }

    /**
     * Prints the thickbox popup content
     *
     * @return void
     */
    public function media_thickbox_content() {
        global $pagenow;

        if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
            return;
        }

        wpuf_include_once( WPUF_INCLUDES . '/Admin/views/shortcode-builder.php' );
    }

    /**
     * Generate shortcode array
     *
     * @since 2.5.2
     */
    public function localize_shortcodes() {
        $shortcodes = apply_filters(
            'wpuf_page_shortcodes', [
                'wpuf-dashboard'    => [
                    'title'   => __( 'Dashboard', 'wp-user-frontend' ),
                    'content' => '[wpuf_dashboard]',
                ],
                'wpuf-account'      => [
                    'title'   => __( 'Account', 'wp-user-frontend' ),
                    'content' => '[wpuf_account]',
                ],
                'wpuf-edit'         => [
                    'title'   => __( 'Edit', 'wp-user-frontend' ),
                    'content' => '[wpuf_edit]',
                ],
                'wpuf-login'        => [
                    'title'   => __( 'Login', 'wp-user-frontend' ),
                    'content' => '[wpuf-login]',
                ],
                'wpuf-registration' => [
                    'title'   => __( 'Registration', 'wp-user-frontend' ),
                    'content' => '[wpuf-registration]',
                ],
                'wpuf-sub-pack'     => [
                    'title'   => __( 'Subscription', 'wp-user-frontend' ),
                    'content' => '[wpuf_sub_pack]',
                ],
            ]
        );
        $assets_url = WPUF_ASSET_URI;
        wp_localize_script( 'wpuf-subscriptions', 'wpuf_shortcodes', apply_filters( 'wpuf_button_shortcodes', $shortcodes ) );
        wp_localize_script( 'wpuf-subscriptions', 'wpuf_assets_url', [ 'url' => $assets_url ] );
    }

    /**
     * Add button on Post Editor
     *
     * @since 2.5.2
     *
     * @param array $plugin_array
     *
     * @return array
     */
    public function enqueue_plugin_scripts( $plugin_array ) {
        global $pagenow;

        if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
            return $plugin_array;
        }

        // enqueue TinyMCE plugin script with its ID.
        $plugin_array['wpuf_button'] = WPUF_ASSET_URI . '/js/wpuf-tmc-button.js';

        return $plugin_array;
    }

    /**
     * Register tinyMce button
     *
     * @since 2.5.2
     *
     * @param array $buttons
     *
     * @return array
     */
    public function register_buttons_editor( $buttons ) {
        //register buttons with their id.
        array_push( $buttons, 'wpuf_button' );

        return $buttons;
    }
}
