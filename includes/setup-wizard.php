<?php
/**
 * Setup wizard class
 *
 * Walkthrough to the basic setup upon installation
 */

/**
 * The class
 */
class WPUF_Setup_Wizard {
    /** @var string Currenct Step */
    protected $step   = '';

    /** @var array Steps for the setup wizard */
    protected $steps  = array();

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        add_action( 'admin_init', array( $this, 'setup_wizard' ), 99 );
        add_action( 'admin_init', array( $this, 'redirect_to_page' ), 9999 );
    }

    /**
     * Enqueue scripts & styles from wpuf plugin.
     *
     * @return void
     */
    public function enqueue_scripts() {
        $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_script( 'jquery-blockui', WPUF_ASSET_URI . '/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
        wp_register_script( 'selectWPUF', WPUF_ASSET_URI . '/js/selectWPUF/selectWPUF.full' . $suffix . '.js', array( 'jquery' ), '1.0.1' );
        wp_register_script( 'wpuf-enhanced-select', WPUF_ASSET_URI . '/js/admin/wpuf-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWPUF' ) );
        wp_localize_script( 'wpuf-enhanced-select', 'wpuf_enhanced_select_params', array(
            'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'wpuf' ),
            'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'wpuf' ),
            'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wpuf' ),
            'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wpuf' ),
            'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wpuf' ),
            'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wpuf' ),
            'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wpuf' ),
            'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wpuf' ),
            'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'wpuf' ),
            'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'wpuf' ),
            'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wpuf' ),
            'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wpuf' ),
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
        ) );

        wp_enqueue_style( 'wpuf_admin_styles', WPUF_ASSET_URI . '/css/admin/admin.css', array() );
        wp_enqueue_style( 'wpuf-setup', WPUF_ASSET_URI . '/css/admin/wpuf-setup.css', array( 'dashicons', 'install' ) );

        wp_register_script( 'wpuf-setup', WPUF_ASSET_URI . '/js/admin/wpuf-setup' . $suffix . '.js', array( 'jquery', 'wpuf-enhanced-select', 'jquery-blockui' ) );
        wp_localize_script( 'wpuf-setup', 'wpuf_setup_params', array() );
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'wpuf-setup', '' );
    }

    /**
     * Redirect to the welcome page once the plugin is installed
     *
     * @return void
     */
    public function redirect_to_page() {
        if ( ! get_transient( 'wpuf_activation_redirect' ) ) {
            return;
        }

        delete_transient( 'wpuf_activation_redirect' );

        // Only do this for single site installs.
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'index.php?page=wpuf-setup' ) );
        exit;
    }

    /**
     * Show the setup wizard.
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'wpuf-setup' !== $_GET['page'] ) {
            return;
        }
        $this->steps = array(
            'introduction' => array(
                'name'    =>  __( 'Introduction', 'wpuf' ),
                'view'    => array( $this, 'wpuf_setup_introduction' ),
                'handler' => ''
            ),
            'basic' => array(
                'name'    =>  __( 'Settings', 'wpuf' ),
                'view'    => array( $this, 'wpuf_setup_basic' ),
                'handler' => array( $this, 'wpuf_setup_basic_save' ),
            ),
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'wpuf' ),
                'view'    => array( $this, 'wpuf_setup_ready' ),
                'handler' => ''
            )
        );
        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

        $this->enqueue_scripts();

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) { // WPCS: CSRF ok.
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );

        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
    }

    /**
     * Setup Wizard Header.
     */
    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WPUF &rsaquo; Setup Wizard', 'wpuf' ); ?></title>
            <?php wp_print_scripts( 'wpuf-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
            <?php do_action( 'wpuf_setup_wizard_styles' ); ?>
            <style type="text/css">
                .wpuf-setup-steps {
                    justify-content: center;
                }
                .wpuf-setup-content a {
                    color: #7dc443;
                }
                .wpuf-setup-steps li.active:before {
                    border-color: #7dc443;
                }
                .wpuf-setup-steps li.active {
                    border-color: #7dc443;
                    color: #7dc443;
                }
                .wpuf-setup-steps li.done:before {
                    border-color: #7dc443;
                }
                .wpuf-setup-steps li.done {
                    border-color: #7dc443;
                    color: #7dc443;
                }
                .wpuf-setup .wpuf-setup-actions .button-primary, .wpuf-setup .wpuf-setup-actions .button-primary, .wpuf-setup .wpuf-setup-actions .button-primary {
                    background: #7dc443 !important;
                }
                .wpuf-setup .wpuf-setup-actions .button-primary:active, .wpuf-setup .wpuf-setup-actions .button-primary:focus, .wpuf-setup .wpuf-setup-actions .button-primary:hover {
                    background: #63bf17 !important;
                    border-color: #63bf17 !important;
                }
                .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a, .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a, .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a {
                    background: #7dc443 !important;
                    box-shadow: inset 0 1px 0 rgba(255,255,255,.25),0 1px 0 #7dc443;
                }
                .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a:active, .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a:focus, .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a:hover {
                    background: #19ca4f !important;
                    border-color: #19ca4f !important;
                    box-shadow: inset 0 1px 0 rgba(255,255,255,.25),0 1px 0 #19ca4f;
                }
                .wpuf-setup .wpuf-setup-actions .button-primary {
                    border-color: #7dc443 !important;
                }
                .wpuf-setup-content .wpuf-setup-next-steps ul .setup-product a {
                    border-color: #7dc443 !important;
                }
                ul.wpuf-wizard-payment-gateways li.wpuf-wizard-gateway .wpuf-wizard-gateway-enable input:checked+label:before {
                    background: #7dc443 !important;
                    border-color: #7dc443 !important;
                }
            </style>
        </head>
        <body class="wpuf-setup wp-core-ui">
            <h1 id="wpuf-logo"><a href="https://wedevs.com/wp-user-frontend-pro/"><img src="<?php echo WPUF_ASSET_URI . '/images/icon-128x128.png'; ?>" alt="WPUF" /></a></h1>
        <?php
    }

    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() {
        ?>
            <?php if ( 'next_steps' === $this->step ) : ?>
                <a class="wpuf-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'wpuf' ); ?></a>
            <?php endif; ?>
            </body>
        </html>
        <?php
    }

    /**
     * Output the steps.
     */
    public function setup_wizard_steps() {
        $ouput_steps = $this->steps;
        array_shift( $ouput_steps );
        ?>
        <ol class="wpuf-setup-steps">
            <?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                ?>"><?php echo esc_html( $step['name'] ); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step.
     */
    public function setup_wizard_content() {
        echo '<div class="wpuf-setup-content">';
        call_user_func( $this->steps[ $this->step ]['view'] );
        echo '</div>';
    }

    /**
     * Introduction step.
     */
    public function wpuf_setup_introduction() {
        ?>
        <h1><?php _e( 'Welcome to the world of WPUF!', 'wpuf' ); ?></h1>
        <p><?php _e( 'Thank you for choosing WPUF to power your websites frontend! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than a minute.</strong>', 'wpuf' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'wpuf' ); ?></p>
        <p class="wpuf-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'wpuf' ); ?></a>
            <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'wpuf' ); ?></a>
        </p>
        <?php
    }

    /**
     * Selling step.
     */
    public function wpuf_setup_basic() {
        $enable_payment = wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' );
        $install_wpuf_pages       = wpuf_get_option( 'install_wpuf_pages', 'wpuf_general', 'on' );
        ?>
        <h1><?php _e( 'Basic Setting', 'wpuf' ); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="enable_payment"><?php _e( 'Enable Payments', 'wpuf' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="enable_payment" id="enable_payment" class="input-checkbox" value="1" <?php echo ( $enable_payment == 'on' ) ? 'checked="checked"' : ''; ?>/>
                        <label for="enable_payment"><?php _e( 'Make payment enable for user to add posts on frontend.', 'wpuf' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="install_wpuf_pages"><?php _e( 'Install WPUF Pages', 'wpuf' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="install_wpuf_pages" id="install_wpuf_pages" class="input-checkbox" value="1" <?php echo ( $install_wpuf_pages == 'on' ) ? 'checked="checked"' : ''; ?>/>
                        <label for="install_wpuf_pages"><?php _e( 'Install neccessery pages on your site frontend.', 'wpuf' ); ?></label>
                    </td>
                </tr>
            </table>
            <p class="wpuf-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'wpuf' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'wpuf' ); ?></a>
                <?php wp_nonce_field( 'wpuf-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save selling options.
     */
    public function wpuf_setup_basic_save() {
        check_admin_referer( 'wpuf-setup' );
        $payment_options = get_option( 'wpuf_payment' );
        $general_options = get_option( 'wpuf_general' );
        $payment_options['enable_payment'] = isset( $_POST['enable_payment'] ) ? 'on' : 'off';
        $general_options['install_wpuf_pages']       = isset( $_POST['install_wpuf_pages'] ) ? 'on' : 'off';

        update_option( 'wpuf_payment', $payment_options );
        update_option( 'wpuf_general', $general_options );

        if ( 'on' == $general_options['install_wpuf_pages'] ) {
            $installer = new WPUF_Admin_Installer();
            $installer->init_pages();
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Final step.
     */
    public function wpuf_setup_ready() {
        ?>
        <h1><?php _e( 'Thank you!', 'wpuf' ); ?></h1>

        <div class="wpuf-setup-next-steps">
            <div class="wpuf-setup-next-steps-first">
                <ul>
                    <li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-welcome' ) ); ?>"><?php _e( 'Welcome to Awesomeness!', 'wpuf' ); ?></a></li>
                </ul>
            </div>
            <div class="wpuf-setup-next-steps-last">
                <h2><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-settings' ) ); ?>"><?php _e( 'Go to Full Settings', 'wpuf' ); ?></a></h2>
            </div>
        </div>
        <?php
    }
}

new WPUF_Setup_Wizard();
