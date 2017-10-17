<?php

/**
 * wpuf tinyMce Shortcode Button class
 *
 * @since 2.5.2
 */
class WPUF_Shortcodes_Button {

    /**
     * Constructor for shortcode class
     */
    public function __construct() {

        add_filter( 'mce_external_plugins',  array( $this, 'enqueue_plugin_scripts' ) );
        add_filter( 'mce_buttons',  array( $this, 'register_buttons_editor' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'localize_shortcodes' ) , 90  );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts'), 80 );

        add_action( 'media_buttons', array( $this, 'add_media_button' ), 20 );
        add_action( 'admin_footer', array( $this, 'media_thickbox_content' ) );
    }


    /**
     * Enqueue scripts and styles for form builder
     *
     * @global string $pagenow
     * @return void
     */
    function enqueue_scripts() {
        global $pagenow;

        if ( !in_array( $pagenow, array( 'post.php', 'post-new.php') ) ) {
            return;
        }

        wp_enqueue_script( 'wpuf-shortcode', WPUF_ASSET_URI . '/js/admin-shortcode.js', array('jquery') );
    }

    /**
     * Adds a media button (for inserting a form) to the Post Editor
     *
     * @param  int  $editor_id The editor ID
     * @return void
     */
    function add_media_button( $editor_id ) {
        ?>
            <a href="#TB_inline?width=480&amp;inlineId=wpuf-media-dialog" class="button thickbox insert-form" data-editor="<?php echo esc_attr( $editor_id ); ?>" title="<?php _e( 'Add a Form', 'wpuf' ); ?>">
                <?php echo '<span class="wp-media-buttons-icon dashicons dashicons-welcome-widgets-menus"></span>' . __( ' Add Form', 'wpuf' ); ?>
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

        if ( !in_array( $pagenow, array( 'post.php', 'post-new.php') ) ) {
            return;
        }

        include dirname( __FILE__ ) . '/shortcode-builder.php';
    }

    /**
     * Generate shortcode array
     *
     * @since 2.5.2
     *
     */
    function localize_shortcodes() {

        $shortcodes = apply_filters( 'wpuf_page_shortcodes', array(
            'wpuf-dashboard'=> array(
                'title'   => __( 'Dashboard', 'wpuf' ),
                'content' => '[wpuf_dashboard]'
            ),
            'wpuf-account'  => array(
                'title'   => __( 'Account', 'wpuf' ),
                'content' => '[wpuf_account]'
            ),
            'wpuf-edit'     => array(
                'title'   => __( 'Edit', 'wpuf' ),
                'content' => '[wpuf_edit]'
            ),
            'wpuf-login'    => array(
                'title'   => __( 'Login', 'wpuf' ),
                'content' => '[wpuf-login]'
            ),
            'wpuf-registration'    => array(
                'title'   => __( 'Registration', 'wpuf' ),
                'content' => '[wpuf-registration]'
            ),
            'wpuf-sub-pack' => array(
                'title'   => __( 'Subscription', 'wpuf' ),
                'content' => '[wpuf_sub_pack]'
            )
        ) );

        $assets_url = WPUF_ASSET_URI;

        wp_localize_script( 'wpuf-subscriptions', 'wpuf_shortcodes', apply_filters( 'wpuf_button_shortcodes', $shortcodes ) );
        wp_localize_script( 'wpuf-subscriptions', 'wpuf_assets_url', $assets_url );
    }

    /**
     * * Singleton object
     *
     * @staticvar boolean $instance
     *
     * @return \self
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new WPUF_Shortcodes_Button();
        }

        return $instance;
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
    function enqueue_plugin_scripts( $plugin_array ) {
        //enqueue TinyMCE plugin script with its ID.
        $plugin_array["wpuf_button"] =  WPUF_ASSET_URI . "/js/wpuf-tmc-button.js";

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
    function register_buttons_editor( $buttons ) {
        //register buttons with their id.
        array_push( $buttons, "wpuf_button" );

        return $buttons;
    }

}

WPUF_Shortcodes_Button::init();
