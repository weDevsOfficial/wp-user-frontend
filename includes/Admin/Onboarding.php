<?php

namespace WeDevs\Wpuf\Admin;

/**
 * Onboarding wizard
 *
 * A guided setup that turns a fresh install into a working frontend site:
 * post form, registration, user directory, payments, the settings that have
 * to be right before anything else works, and the companion plugins.
 *
 * Rendered as a standalone screen using the User Directory design system.
 *
 * @since WPUF_SINCE
 */
class Onboarding {

    /**
     * Page slug of the wizard
     */
    const PAGE_SLUG = 'wpuf-onboarding';

    /**
     * Option holding the wizard progress
     */
    const PROGRESS_OPTION = 'wpuf_onboarding_progress';

    /**
     * Option holding the features the admin picked in the first step
     */
    const FEATURES_OPTION = 'wpuf_onboarding_features';

    /**
     * Option holding plugins the wizard could not install
     */
    const PLUGIN_ERRORS_OPTION = 'wpuf_onboarding_plugin_errors';

    /**
     * Option marking a finished run
     */
    const COMPLETED_OPTION = 'wpuf_onboarding_completed';

    /**
     * Current step key
     *
     * @var string
     */
    protected $step = '';

    /**
     * All the steps of the wizard
     *
     * @var array
     */
    protected $steps = [];

    /**
     * Boot the wizard
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_page' ] );
        add_action( 'admin_head', [ $this, 'hide_menu_link' ] );
        add_action( 'admin_init', [ $this, 'render' ], 1 );

        // Ahead of Setup_Wizard::redirect_to_page(), which runs at 9999, so a
        // first install lands here rather than in the legacy three step wizard.
        add_action( 'admin_init', [ $this, 'maybe_redirect_after_activation' ], 5 );

        // Late, so every menu this might hide is registered by the time it runs.
        add_action( 'admin_menu', [ $this, 'hide_menus_for_unpicked_features' ], 999 );
    }

    /**
     * Mark the label of a control that has to be answered
     *
     * The asterisk is not the only signal: it carries a screen-reader word too, so
     * the requirement is not communicated by colour alone (WCAG 1.4.1).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public static function required_mark() {
        printf(
            '<span class="wpuf-onboarding-required" aria-hidden="true">*</span><span class="screen-reader-text">%s</span>',
            esc_html__( 'required', 'wp-user-frontend' )
        );
    }

    /**
     * Menu slugs belonging to each feature offered in the first step
     *
     * Post forms are deliberately absent: their submenu slug is the plugin's own
     * top level slug, so hiding it would take the whole User Frontend menu with it.
     *
     * @since WPUF_SINCE
     *
     * @return array feature key => list of submenu slugs
     */
    public function get_feature_menus() {
        $menus = [
            // Free and Pro both register the registration forms screen on this slug.
            'registration'   => [
                'wpuf-profile-forms',
            ],
            'user_directory' => [
                'wpuf_userlisting',
            ],
            'payments'       => [
                'wpuf_subscription',
                'wpuf_transaction',
                'wpuf_subscribers',
                'wpuf_coupon',
            ],
        ];

        /**
         * Filter the menus hidden when a feature is switched off during onboarding
         *
         * @since WPUF_SINCE
         *
         * @param array $menus Feature key => list of submenu slugs.
         */
        $menus = apply_filters( 'wpuf_onboarding_feature_menus', $menus );

        return ! empty( $menus ) && is_array( $menus ) ? $menus : [];
    }

    /**
     * Hide the menus for the features the admin said they do not need
     *
     * This only takes effect once the picker has actually been answered. A site
     * that has never run the wizard has no stored picks, so every menu stays where
     * it is and an existing install sees no change at all.
     *
     * The menu is hidden, not the feature. Everything keeps working and the screens
     * stay reachable by URL, which is what keeps this reversible: re-running the
     * wizard and ticking the feature brings its menu straight back.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hide_menus_for_unpicked_features() {
        $saved = get_option( self::FEATURES_OPTION, null );

        // Never answered, so nothing is switched off. Leave every menu alone.
        if ( ! is_array( $saved ) ) {
            return;
        }

        $parent = 'wp-user-frontend';

        if ( isset( wpuf()->admin->menu->parent_slug ) ) {
            $parent = wpuf()->admin->menu->parent_slug;
        }

        foreach ( $this->get_feature_menus() as $feature => $slugs ) {
            if ( in_array( $feature, $saved, true ) || ! is_array( $slugs ) ) {
                continue;
            }

            foreach ( $slugs as $slug ) {
                remove_submenu_page( $parent, $slug );

                // Subscribers hangs off the subscription CPT, not the plugin menu.
                remove_submenu_page( 'edit.php?post_type=wpuf_subscription', $slug );
                remove_submenu_page( 'edit.php?post_type=wpuf_coupon', $slug );
            }
        }
    }

    /**
     * Send a brand new install to the wizard, once
     *
     * Only fires for a site installing WPUF for the first time: Installer::install()
     * sets the transient this reads exclusively when wpuf_installed was previously
     * unset. An existing site is never redirected, whatever state its legacy setup
     * wizard was left in, so updating or reactivating the plugin on a site already
     * in use changes nothing.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function maybe_redirect_after_activation() {
        if ( ! get_transient( 'wpuf_onboarding_redirect' ) ) {
            return;
        }

        delete_transient( 'wpuf_onboarding_redirect' );

        // The legacy wizard reads its own transient later in this same request.
        // Clearing it here keeps the two from fighting over one activation.
        delete_transient( 'wpuf_activation_redirect' );

        // Match the legacy wizard: single site only, and never mid bulk activation.
        // Presence of the flag is all that is read, and the transient consumed above
        // is what authorises this, not the request.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
            return;
        }

        // Give the post form and registration steps something to pick from. Only
        // the pages those two steps offer; the rest wait for the settings step.
        $installer = new Admin_Installer();

        $installer->init_essential_pages();

        wp_safe_redirect( admin_url( 'index.php?page=' . self::PAGE_SLUG ) );

        exit;
    }

    /**
     * Register the hidden page that hosts the wizard
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_page() {
        add_dashboard_page(
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            'manage_options',
            self::PAGE_SLUG,
            '__return_null'
        );
    }

    /**
     * Keep the hosting page out of the Dashboard submenu
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hide_menu_link() {
        remove_submenu_page( 'index.php', self::PAGE_SLUG );
    }

    /**
     * Get the step definitions
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_steps() {
        $steps = [
            'features'       => [
                'label'   => __( 'What you need', 'wp-user-frontend' ),
                'view'    => 'features',
                'handler' => [ $this, 'save_features' ],
            ],
            'post_form'      => [
                'label'   => __( 'Post Form', 'wp-user-frontend' ),
                'view'    => 'post-form',
                'handler' => [ $this, 'save_post_form' ],
            ],
            'registration'   => [
                'label'   => __( 'Registration', 'wp-user-frontend' ),
                'view'    => 'registration',
                'handler' => [ $this, 'save_registration' ],
            ],
            'common'         => [
                'label'   => __( 'Settings', 'wp-user-frontend' ),
                'view'    => 'common',
                'handler' => [ $this, 'save_common' ],
            ],
            'plugins'        => [
                'label'   => __( 'Plugins', 'wp-user-frontend' ),
                'view'    => 'plugins',
                'handler' => [ $this, 'save_plugins' ],
            ],
            'ready'          => [
                'label'   => __( 'Ready', 'wp-user-frontend' ),
                'view'    => 'ready',
                'handler' => [ $this, 'save_share' ],
            ],
        ];

        // Nothing to offer once every companion plugin is running.
        if ( ! $this->get_pending_plugins() ) {
            unset( $steps['plugins'] );
        }

        // Drop the steps for the features the admin said they do not need.
        $features = $this->get_features();

        foreach ( $this->get_feature_definitions() as $feature => $definition ) {
            if ( empty( $definition['step'] ) ) {
                continue;
            }

            if ( ! in_array( $feature, $features, true ) ) {
                unset( $steps[ $definition['step'] ] );
            }
        }

        /**
         * Filter the onboarding wizard steps
         *
         * @since WPUF_SINCE
         *
         * @param array $steps
         */
        return apply_filters( 'wpuf_onboarding_steps', $steps );
    }

    /**
     * The things WPUF can do, as offered in the first step
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_feature_definitions() {
        $features = [
            'post_form'      => [
                'step'  => 'post_form',
                'name'  => __( 'Frontend post submission', 'wp-user-frontend' ),
                'desc'  => __( 'People write and publish posts on your site. They never see wp-admin.', 'wp-user-frontend' ),
            ],
            'registration'   => [
                'step'  => 'registration',
                'name'  => __( 'Registration & login', 'wp-user-frontend' ),
                'desc'  => __( 'Sign-up and login pages that look like your site, not WordPress.', 'wp-user-frontend' ),
            ],
            'user_directory' => [
                'step'  => '',
                'name'  => __( 'User directory', 'wp-user-frontend' ),
                'desc'  => __( 'A browsable list of your members, each with their own profile page.', 'wp-user-frontend' ),
            ],
            'payments'       => [
                'step'  => '',
                'name'  => __( 'Payments & subscriptions', 'wp-user-frontend' ),
                'desc'  => __( 'Charge per post, sell packs, or put your directory behind a payment.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filter the features offered in the onboarding wizard
         *
         * @since WPUF_SINCE
         *
         * @param array $features
         */
        return apply_filters( 'wpuf_onboarding_features', $features );
    }

    /**
     * The features the admin picked
     *
     * Everything is on until the choice is made, so the wizard is complete
     * for anyone who walks straight past the first step.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_features() {
        $saved = get_option( self::FEATURES_OPTION, null );

        if ( ! is_array( $saved ) ) {
            return array_keys( $this->get_feature_definitions() );
        }

        return $saved;
    }

    /**
     * Whether a feature was picked
     *
     * @since WPUF_SINCE
     *
     * @param string $feature
     *
     * @return bool
     */
    public function wants( $feature ) {
        return in_array( $feature, $this->get_features(), true );
    }

    /**
     * Render the wizard and stop WordPress from rendering the admin screen
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        if ( self::PAGE_SLUG !== $page ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to run the setup.', 'wp-user-frontend' ) );
        }

        $this->maybe_restart();

        $this->steps = $this->get_steps();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $requested  = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : '';
        $this->step = isset( $this->steps[ $requested ] ) ? $requested : current( array_keys( $this->steps ) );

        if ( ! empty( $_POST['wpuf_onboarding_save'] ) && ! empty( $this->steps[ $this->step ]['handler'] ) ) {
            check_admin_referer( 'wpuf-onboarding' );

            call_user_func( $this->steps[ $this->step ]['handler'] );

            // A handler can change which steps exist; the features step does.
            $this->steps = $this->get_steps();

            $this->mark_done( $this->step );

            $next = $this->get_next_step_link();

            // Only a finished run earns the confetti, not a click on the rail.
            if ( 'ready' === $this->get_next_step_key() ) {
                $next = add_query_arg( 'celebrate', '1', $next );
            }

            wp_safe_redirect( $next );
            exit;
        }

        $this->set_last_seen( $this->step );

        remove_action( 'admin_print_styles', 'print_emoji_styles' );

        $this->render_header();
        $this->render_content();
        $this->render_footer();

        exit;
    }

    /**
     * Wizard document head, logo and step rail
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function render_header() {
        wp_enqueue_style( 'wpuf-onboarding' );
        wp_enqueue_script( 'wpuf-onboarding' );

        // The confetti needs a URL, and the wizard's markup carries no inline
        // script to put one in, so it arrives as data instead.
        wp_localize_script(
            'wpuf-onboarding',
            'wpufOnboarding',
            [
                'confettiIcon' => esc_url_raw( WPUF_ASSET_URI . '/images/onboarding/icon.svg' ),
            ]
        );

        $steps        = $this->steps;
        $current      = $this->step;
        $current_idx  = array_search( $current, array_keys( $steps ), true );
        $step_keys    = array_keys( $steps );
        $progress     = $this->get_progress();
        $exit_url     = admin_url( 'admin.php?page=wpuf_tools&tab=tools' );
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e( 'User Frontend &rsaquo; Onboarding', 'wp-user-frontend' ); ?></title>
            <?php wp_print_styles( 'wpuf-onboarding' ); ?>
        </head>
        <body class="wpuf-onboarding-body">
            <div class="wpuf-onboarding-topbar">
                <img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/onboarding-logo.svg' ); ?>" alt="<?php esc_attr_e( 'User Frontend', 'wp-user-frontend' ); ?>" />
                <a class="wpuf-onboarding-exit" href="<?php echo esc_url( $exit_url ); ?>">
                    <?php esc_html_e( 'Exit setup', 'wp-user-frontend' ); ?>
                </a>
            </div>

            <div class="wpuf-onboarding-head">
                <h1><?php esc_html_e( 'Set up User Frontend', 'wp-user-frontend' ); ?></h1>

                    <nav aria-label="<?php esc_attr_e( 'Progress', 'wp-user-frontend' ); ?>">
                        <ol class="wpuf-onboarding-steps">
                            <?php
                            $rail = $steps;

                            // The picker is answered once and then out of the way.
                            if ( 'features' !== $current ) {
                                unset( $rail['features'] );
                            }

                            $rail_keys  = array_keys( $rail );
                            $rail_last  = count( $rail_keys ) - 1;

                            foreach ( $rail_keys as $index => $key ) :
                                $step_idx = array_search( $key, $step_keys, true );
                                $state    = '';

                                if ( $key === $current ) {
                                    $state = 'is-active';
                                } elseif ( $step_idx < $current_idx || in_array( $key, $progress['completed'], true ) ) {
                                    $state = 'is-done';
                                }
                                ?>
                                <li class="<?php echo esc_attr( $state ); ?>">
                                    <a class="wpuf-step-marker" href="<?php echo esc_url( $this->get_step_link( $key ) ); ?>">
                                        <?php
                                        // Every marker carries the same tick, as the User Directory
                                        // wizard does. A step not yet reached draws it in white on
                                        // white, so it reads as an empty circle; a step reached fills
                                        // emerald and the tick shows. No digits, so the rail cannot
                                        // renumber itself when a step drops out of the flow.
                                        ?>
                                        <svg width="9" height="7" viewBox="0 0 9 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <span class="wpuf-step-label"><?php echo esc_html( $rail[ $key ]['label'] ); ?></span>
                                    <?php if ( $index !== $rail_last ) : ?>
                                        <span class="wpuf-step-line"></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
            </div>
        <?php
    }

    /**
     * Render the current step view
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function render_content() {
        $view = WPUF_INCLUDES . '/Admin/views/onboarding/' . $this->steps[ $this->step ]['view'] . '.php';

        echo '<div class="wpuf-onboarding-content">';

        if ( file_exists( $view ) ) {
            include $view;
        }

        echo '</div>';
    }

    /**
     * Close the document
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function render_footer() {
        // The wizard builds its own document, so there is no wp_footer() to hang
        // enqueued scripts off. They are printed explicitly instead.
        wp_print_scripts( 'wpuf-onboarding' );
        ?>
        </body>
        </html>
        <?php
    }

    /**
     * Render the fixed action bar of a step
     *
     * @since WPUF_SINCE
     *
     * @param array $args
     *
     * @return void
     */
    public function action_bar( $args = [] ) {
        $args = wp_parse_args(
            $args, [
                'next_label' => __( 'Save & Continue', 'wp-user-frontend' ),
                'show_prev'  => true,
                'show_skip'  => true,
            ]
        );
        ?>
        <div class="wpuf-onboarding-footer">
            <div class="wpuf-onboarding-footer-inner">
                <div class="wpuf-onboarding-footer-left">
                    <?php if ( $args['show_prev'] && $this->get_prev_step_link() ) : ?>
                        <a class="wpuf-onboarding-btn-white" href="<?php echo esc_url( $this->get_prev_step_link() ); ?>">
                            <?php esc_html_e( 'Previous', 'wp-user-frontend' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="wpuf-onboarding-footer-right">
                    <?php if ( $args['show_skip'] ) : ?>
                        <a class="wpuf-onboarding-skip" href="<?php echo esc_url( $this->get_next_step_link() ); ?>">
                            <?php esc_html_e( 'Skip this step', 'wp-user-frontend' ); ?>
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="wpuf_onboarding_save" value="1" class="wpuf-onboarding-btn-primary">
                        <?php echo esc_html( $args['next_label'] ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Link of a given step
     *
     * @since WPUF_SINCE
     *
     * @param string $step
     *
     * @return string
     */
    public function get_step_link( $step ) {
        return add_query_arg(
            [
                'page' => self::PAGE_SLUG,
                'step' => $step,
            ], admin_url( 'index.php' )
        );
    }

    /**
     * Key of the step that follows the current one
     *
     * @since WPUF_SINCE
     *
     * @return string empty when this is the last step
     */
    public function get_next_step_key() {
        $keys  = array_keys( $this->steps );
        $index = array_search( $this->step, $keys, true );

        if ( false === $index || ! isset( $keys[ $index + 1 ] ) ) {
            return '';
        }

        return $keys[ $index + 1 ];
    }

    /**
     * Link of the next step
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_next_step_link() {
        $keys  = array_keys( $this->steps );
        $index = array_search( $this->step, $keys, true );

        if ( false === $index || ! isset( $keys[ $index + 1 ] ) ) {
            return admin_url( 'admin.php?page=wp-user-frontend' );
        }

        return $this->get_step_link( $keys[ $index + 1 ] );
    }

    /**
     * Link of the previous step
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_prev_step_link() {
        $keys  = array_keys( $this->steps );
        $index = array_search( $this->step, $keys, true );

        if ( false === $index || ! isset( $keys[ $index - 1 ] ) ) {
            return '';
        }

        return $this->get_step_link( $keys[ $index - 1 ] );
    }

    /**
     * Whether a full run has been finished
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_completed() {
        return (bool) get_option( self::COMPLETED_OPTION );
    }

    /**
     * Wipe the progress of a finished run so the wizard opens on step one
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function maybe_restart() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( empty( $_GET['restart'] ) ) {
            return;
        }

        check_admin_referer( 'wpuf-onboarding-restart' );

        delete_option( self::PROGRESS_OPTION );
        delete_option( self::COMPLETED_OPTION );

        wp_safe_redirect( $this->get_step_link( 'features' ) );
        exit;
    }

    /**
     * Where the Tools button should send the admin, and what it should say
     *
     * @since WPUF_SINCE
     *
     * @return array url and label
     */
    public function get_entry_point() {
        $progress  = $this->get_progress();
        $completed = $this->is_completed();

        if ( $completed || empty( $progress['last_step'] ) || 'features' === $progress['last_step'] ) {
            return [
                'url'     => wp_nonce_url(
                    add_query_arg( 'restart', '1', $this->get_step_link( 'features' ) ),
                    'wpuf-onboarding-restart'
                ),
                'label'   => $completed
                    ? __( 'Run Onboarding Again', 'wp-user-frontend' )
                    : __( 'Start Onboarding', 'wp-user-frontend' ),
                // Only a site that has already been through the wizard has settings
                // worth warning about overwriting.
                'warning' => $completed
                    ? __( 'You have already completed onboarding. Running it again walks through the same steps and saves over the choices you made last time. Existing pages and forms are reused rather than duplicated, and nothing changes until you save a step.', 'wp-user-frontend' )
                    : '',
            ];
        }

        return [
            'url'     => $this->get_step_link( $progress['last_step'] ),
            'label'   => __( 'Resume Onboarding', 'wp-user-frontend' ),
            'warning' => '',
        ];
    }

    /**
     * Stored progress of the wizard
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_progress() {
        $progress = get_option(
            self::PROGRESS_OPTION, [
                'completed' => [],
                'last_step' => '',
            ]
        );

        if ( ! is_array( $progress ) ) {
            $progress = [];
        }

        return wp_parse_args(
            $progress, [
                'completed' => [],
                'last_step' => '',
            ]
        );
    }

    /**
     * Mark a step as completed
     *
     * @since WPUF_SINCE
     *
     * @param string $step
     *
     * @return void
     */
    protected function mark_done( $step ) {
        $progress = $this->get_progress();

        if ( ! in_array( $step, $progress['completed'], true ) ) {
            $progress['completed'][] = $step;
        }

        update_option( self::PROGRESS_OPTION, $progress );
    }

    /**
     * Remember where the admin left off
     *
     * @since WPUF_SINCE
     *
     * @param string $step
     *
     * @return void
     */
    protected function set_last_seen( $step ) {
        $progress = $this->get_progress();

        if ( 'ready' === $step ) {
            update_option( self::COMPLETED_OPTION, 1 );

            // Finishing here counts as finishing setup, so the legacy three step
            // wizard stops claiming the activation redirect and stops nagging.
            update_option( 'wpuf_setup_wizard', 1 );
        }

        if ( $progress['last_step'] === $step ) {
            return;
        }

        $progress['last_step'] = $step;

        update_option( self::PROGRESS_OPTION, $progress );
    }

    /**
     * Whether a checkbox of the current step was submitted
     *
     * @since WPUF_SINCE
     *
     * @param string $key
     *
     * @return bool
     */
    protected function posted( $key ) {
        // Nonce is verified in render() before any handler runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        return ! empty( $_POST[ $key ] );
    }

    /**
     * Value of a submitted field of the current step
     *
     * @since WPUF_SINCE
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    protected function posted_value( $key, $default = '' ) {
        // Nonce is verified in render() before any handler runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! isset( $_POST[ $key ] ) ) {
            return $default;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
    }

    /**
     * Step 1: what the site is for
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_features() {
        $allowed = array_keys( $this->get_feature_definitions() );

        // Nonce is verified in render() before any handler runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $picked = isset( $_POST['features'] ) ? array_map( 'sanitize_key', wp_unslash( (array) $_POST['features'] ) ) : [];

        $picked = array_values( array_intersect( $allowed, $picked ) );

        update_option( self::FEATURES_OPTION, $picked );

        // The directory has no step of its own, so the choice made here is
        // what switches the module on or off.
        $this->toggle_directory( in_array( 'user_directory', $picked, true ) );

        // Payments has no step of its own either. Unticking it has to switch the
        // setting off, not merely hide the menus, or the gateways carry on working
        // while the admin believes they turned the whole thing off.
        $this->toggle_payments( in_array( 'payments', $picked, true ) );
    }

    /**
     * Switch the payment setting to match the feature pick
     *
     * Switching payments off writes the setting the rest of the plugin actually
     * reads. Switching it back on only lifts that block; which gateways are active
     * stays with the settings step, so a site turning payments on again does not
     * silently get a gateway it never chose.
     *
     * @since WPUF_SINCE
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function toggle_payments( $enabled ) {
        $payment = get_option( 'wpuf_payment', [] );
        $payment = is_array( $payment ) ? $payment : [];

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        update_option( 'wpuf_payment', $payment );
    }

    /**
     * Step 2: frontend posting
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_post_form() {
        $template = $this->posted_value( 'post_form_template' );
        $form_id  = absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting', 0 ) );

        if ( $template && 'skip' !== $template ) {
            $new_form = $this->create_form_from_template( $template );

            if ( $new_form ) {
                $form_id = $new_form;
            }
        }

        $settings = get_option( 'wpuf_frontend_posting', [] );
        $settings = is_array( $settings ) ? $settings : [];

        if ( $form_id ) {
            $settings['default_post_form'] = $form_id;
        }

        update_option( 'wpuf_frontend_posting', $settings );

        // Editing and deleting live under the dashboard section, which is what
        // the frontend actually reads.
        $dashboard = get_option( 'wpuf_dashboard', [] );
        $dashboard = is_array( $dashboard ) ? $dashboard : [];

        $dashboard['enable_post_edit'] = $this->posted( 'enable_post_edit' ) ? 'yes' : 'no';
        $dashboard['enable_post_del']  = $this->posted( 'enable_post_del' ) ? 'yes' : 'no';

        update_option( 'wpuf_dashboard', $dashboard );
    }

    /**
     * Step 2: login and registration
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_registration() {
        $profile = get_option( 'wpuf_profile', [] );
        $profile = is_array( $profile ) ? $profile : [];

        $installer = new Admin_Installer();

        $login_choice = $this->posted_value( 'login_page' );

        if ( 'create' === $login_choice ) {
            // Reuse a page that already carries the shortcode. Choosing "create" on a
            // second run should still land on the existing page rather than publish a
            // duplicate of it.
            $login_page = $installer->get_or_create_page(
                __( 'Login', 'wp-user-frontend' ),
                '[wpuf-login]',
                isset( $profile['login_page'] ) ? absint( $profile['login_page'] ) : 0,
                '[wpuf-login]'
            );

            if ( $login_page ) {
                $profile['login_page'] = $login_page;
            }
        } elseif ( absint( $login_choice ) ) {
            $profile['login_page'] = absint( $login_choice );

            $this->ensure_page_shortcode( absint( $login_choice ), 'wpuf-login', '[wpuf-login]' );
        }

        $reg_choice = $this->posted_value( 'reg_page' );
        $is_pro     = wpuf_is_pro_active();

        if ( 'create' === $reg_choice ) {
            if ( $is_pro ) {
                $data = apply_filters( 'wpuf_pro_page_install', $profile );

                if ( is_array( $data ) && isset( $data['profile_options'] ) && is_array( $data['profile_options'] ) ) {
                    $profile = $data['profile_options'];
                }
            } else {
                // Free ships its own registration form on [wpuf-registration], so a
                // site without Pro still gets a working sign-up page. Only building
                // custom forms on top of it is the Pro part.
                $installer = new Admin_Installer();
                $reg_page  = $installer->get_or_create_page(
                    __( 'Registration', 'wp-user-frontend' ),
                    '[wpuf-registration]',
                    0,
                    '[wpuf-registration]'
                );

                if ( $reg_page ) {
                    $profile['reg_override_page'] = $reg_page;
                }
            }
        } elseif ( absint( $reg_choice ) ) {
            $profile['reg_override_page'] = absint( $reg_choice );

            if ( $is_pro ) {
                $form_id = $this->get_registration_form_id();

                if ( $form_id ) {
                    $this->ensure_page_shortcode(
                        absint( $reg_choice ),
                        'wpuf_profile',
                        '[wpuf_profile type="registration" id="' . $form_id . '"]'
                    );
                }
            } else {
                $this->ensure_page_shortcode(
                    absint( $reg_choice ),
                    'wpuf-registration',
                    '[wpuf-registration]'
                );
            }
        }

        // Only send WordPress register links at a page that exists.
        $profile['register_link_override'] = ! empty( $profile['reg_override_page'] ) ? 'on' : 'off';

        $profile['autologin_after_registration'] = $this->posted( 'autologin_after_registration' ) ? 'on' : 'off';

        // Layouts are a Pro feature; the free preview posts nothing.
        $layout = $this->posted_value( 'wpuf_login_form_layout' );

        if ( $layout && array_key_exists( $layout, wpuf_get_login_layout_options() ) ) {
            $profile['wpuf_login_form_layout'] = $layout;
        }

        update_option( 'wpuf_profile', $profile );

        $this->save_account_page();
    }

    /**
     * The first page already holding a shortcode
     *
     * Used so an unset page setting falls back to a page that exists rather
     * than offering to create another one.
     *
     * @since WPUF_SINCE
     *
     * @param string $tag
     *
     * @return int page id, 0 when none holds it
     */
    public function find_page_with_shortcode( $tag ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ]
        );

        foreach ( $pages as $page ) {
            if ( $this->content_has_shortcode( $page->post_content, $tag ) ) {
                return $page->ID;
            }
        }

        return 0;
    }

    /**
     * Pages for a wizard dropdown, flagging the ones already holding a shortcode
     *
     * @since WPUF_SINCE
     *
     * @param string $tag    shortcode tag to look for
     * @param string $marker  what to show in brackets on a page that has it
     *
     * @return array page id => label
     */
    public function get_pages_for_shortcode( $tag, $marker = '' ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        $list = [];

        foreach ( $pages as $page ) {
            $label = $page->post_title ? $page->post_title : __( '(no title)', 'wp-user-frontend' );

            if ( $marker && $this->content_has_shortcode( $page->post_content, $tag ) ) {
                $label .= ' (' . $marker . ')';
            }

            $list[ $page->ID ] = $label;
        }

        return $list;
    }

    /**
     * Whether content holds a shortcode
     *
     * The has_shortcode() helper only matches tags registered at the time of the
     * call, and the WPUF shortcodes are not registered on the wizard screen, so
     * the tag is matched directly instead.
     *
     * @since WPUF_SINCE
     *
     * @param string $content
     * @param string $tag
     *
     * @return bool
     */
    protected function content_has_shortcode( $content, $tag ) {
        if ( false === strpos( $content, '[' ) ) {
            return false;
        }

        return (bool) preg_match( '/\[' . preg_quote( $tag, '/' ) . '[\s\]\/]/', $content );
    }

    /**
     * Put a WPUF shortcode on a page that does not have it yet
     *
     * Picking an existing page from the wizard is only useful if the page
     * actually renders the form, so the shortcode is appended when missing.
     *
     * @since WPUF_SINCE
     *
     * @param int    $page_id
     * @param string $tag       shortcode tag to look for
     * @param string $shortcode full shortcode to append
     *
     * @return bool whether the page was changed
     */
    protected function ensure_page_shortcode( $page_id, $tag, $shortcode ) {
        $page = get_post( $page_id );

        if ( ! $page || 'page' !== $page->post_type ) {
            return false;
        }

        if ( $this->content_has_shortcode( $page->post_content, $tag ) ) {
            return false;
        }

        $content = trim( $page->post_content );
        $content = $content ? $content . "\n\n" . $shortcode : $shortcode;

        wp_update_post(
            [
                'ID'           => $page_id,
                'post_content' => $content,
            ]
        );

        return true;
    }

    /**
     * A registration form to point a page at, creating one if needed
     *
     * @since WPUF_SINCE
     *
     * @return int form id, 0 when none could be made
     */
    protected function get_registration_form_id() {
        $forms = get_posts(
            [
                'post_type'      => 'wpuf_profile',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ]
        );

        if ( ! empty( $forms[0] ) ) {
            return absint( $forms[0] );
        }

        $installer = new Admin_Installer();

        return absint( $installer->create_reg_form() );
    }

    /**
     * Point the account page setting at a real page
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function save_account_page() {
        $choice = $this->posted_value( 'account_page' );

        if ( ! $choice ) {
            return;
        }

        $account = get_option( 'wpuf_my_account', [] );
        $account = is_array( $account ) ? $account : [];

        if ( 'create' === $choice ) {
            $installer = new Admin_Installer();
            $page_id   = $installer->get_or_create_page(
                __( 'Account', 'wp-user-frontend' ),
                '[wpuf_account]',
                isset( $account['account_page'] ) ? absint( $account['account_page'] ) : 0,
                '[wpuf_account]'
            );

            if ( ! $page_id ) {
                return;
            }
        } else {
            $page_id = absint( $choice );

            if ( ! $page_id ) {
                return;
            }

            $this->ensure_page_shortcode( $page_id, 'wpuf_account', '[wpuf_account]' );
        }

        $account['account_page'] = $page_id;

        update_option( 'wpuf_my_account', $account );
    }

    /**
     * Switch the user directory module on or off
     *
     * @since WPUF_SINCE
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function toggle_directory( $enabled ) {
        // Pro serves the directory from its own module list, but that list is
        // plan gated. Keep the free list in step either way, so a plan that
        // does not carry the Pro module still gets a working directory.
        if ( wpuf_is_pro_active() ) {
            $this->toggle_pro_directory_module( $enabled );
        }

        $active = wpuf_free_get_active_modules();
        $active = is_array( $active ) ? $active : [];

        if ( $enabled && ! in_array( 'user_directory', $active, true ) ) {
            $active[] = 'user_directory';
        }

        if ( ! $enabled ) {
            $active = array_values( array_diff( $active, [ 'user_directory' ] ) );
        }

        update_option( wpuf_free_active_module_key(), $active );
    }

    /**
     * Payments, saved as part of the settings step
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_payment_settings() {
        $payment = get_option( 'wpuf_payment', [] );
        $payment = is_array( $payment ) ? $payment : [];

        $enabled = $this->posted( 'enable_payment' );

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        if ( ! $enabled ) {
            update_option( 'wpuf_payment', $payment );

            return;
        }

        // Nonce is verified in render() before any handler runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $picked = isset( $_POST['active_gateways'] ) ? array_map( 'sanitize_key', wp_unslash( (array) $_POST['active_gateways'] ) ) : [];

        $allowed  = array_keys( wpuf_get_gateways() );
        $gateways = array_values( array_intersect( $allowed, $picked ) );

        // Payments switched on with no way to take money is a dead end, and
        // bank transfer is the one gateway that needs no credentials.
        if ( ! $gateways ) {
            $gateways = [ 'bank' ];
        }

        $payment['active_gateways'] = $gateways;

        update_option( 'wpuf_payment', $payment );
    }

    /**
     * Step 5: the settings that have to be right first
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_common() {
        if ( $this->wants( 'payments' ) ) {
            $this->save_payment_settings();
        }

        $general = get_option( 'wpuf_general', [] );
        $general = is_array( $general ) ? $general : [];

        $general['show_admin_bar'] = $this->posted( 'hide_admin_bar' )
            ? [ 'administrator' ]
            : [ 'administrator', 'editor', 'author', 'contributor' ];

        // Persist the choice, not just act on it. Admin_Installer::admin_notice()
        // reads this to decide whether to keep nagging about installing the pages.
        $general['install_wpuf_pages'] = $this->posted( 'install_wpuf_pages' ) ? 'on' : 'off';

        update_option( 'wpuf_general', $general );

        if ( $this->posted( 'install_wpuf_pages' ) ) {
            $installer = new Admin_Installer();

            $installer->init_pages();

            // init_pages() only makes the directory page when the Pro module is
            // loaded, so cover the free module here too.
            // A directory page renders as a shortcode on a classic theme and as a
            // block on a block theme, so both spellings have to count as "exists".
            $directory_exists = false;

            foreach ( Admin_Installer::USER_DIRECTORY_MARKERS as $directory_marker ) {
                if ( $this->page_exists( $directory_marker ) ) {
                    $directory_exists = true;

                    break;
                }
            }

            if ( $this->wants( 'user_directory' ) && $this->is_directory_active() && ! $directory_exists ) {
                $installer->create_page(
                    __( 'User Directory', 'wp-user-frontend' ),
                    $installer->get_user_directory_page_content()
                );
            }
        }

        if ( $this->posted( 'add_logout_menu' ) ) {
            $installer = new Admin_Installer();

            $installer->auto_add_logout_to_menu();
        }
    }

    /**
     * Step 6: companion plugins
     *
     * Plugins are installed over ajax from the step itself, so nothing is
     * saved here beyond marking the step as visited.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_plugins() {
        $plugins = $this->get_recommended_plugins();
        $errors  = [];

        // Nonce is verified in render() before any handler runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $picked = isset( $_POST['plugins'] ) ? array_map( 'sanitize_key', wp_unslash( (array) $_POST['plugins'] ) ) : [];

        if ( ! $picked || ! current_user_can( 'install_plugins' ) ) {
            delete_option( self::PLUGIN_ERRORS_OPTION );

            return;
        }

        // Four plugins downloaded one after another in a single request is the
        // slowest thing the wizard does, and on a host with a short
        // max_execution_time it is what times out. Ask for room, then keep an eye
        // on the clock and stop cleanly rather than being killed mid-install.
        wp_raise_memory_limit( 'admin' );

        $budget = $this->get_install_time_budget();
        $started = microtime( true );

        foreach ( $picked as $slug ) {
            if ( ! isset( $plugins[ $slug ] ) ) {
                continue;
            }

            // Stop while there is still time to render a page. Whatever is left is
            // reported rather than silently dropped, and the step reappears with
            // those plugins still on it, so a second click finishes the job.
            if ( $budget > 0 && ( microtime( true ) - $started ) > $budget ) {
                $errors[ $plugins[ $slug ]['name'] ] = __( 'Not installed yet: the wizard ran out of time on this request. Select it again to finish.', 'wp-user-frontend' );

                continue;
            }

            // Each plugin gets its own slice of the clock where the host allows it.
            if ( function_exists( 'set_time_limit' ) ) {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- disabled on some hosts.
                @set_time_limit( 120 );
            }

            $result = $this->install_and_activate( $slug );

            if ( is_wp_error( $result ) ) {
                $errors[ $plugins[ $slug ]['name'] ] = $result->get_error_message();
            }
        }

        if ( $errors ) {
            update_option( self::PLUGIN_ERRORS_OPTION, $errors );
        } else {
            delete_option( self::PLUGIN_ERRORS_OPTION );
        }
    }

    /**
     * How long this request may spend installing before it stops
     *
     * Leaves roughly a quarter of the host's execution time to render the next
     * screen, so a run that cannot finish reports what is left instead of dying
     * half way. Returns 0 when the host sets no limit, in which case there is
     * nothing to budget against.
     *
     * @since WPUF_SINCE
     *
     * @return float seconds, 0 when the host imposes no limit
     */
    protected function get_install_time_budget() {
        $limit = (int) ini_get( 'max_execution_time' );

        if ( $limit <= 0 ) {
            return 0;
        }

        return max( 5, $limit * 0.75 );
    }

    /**
     * The gateways offered on the settings step, with what each one still needs
     *
     * Built from the same source the settings screen renders, then annotated so a
     * card can say whether it is ready to take money or still wants credentials.
     * Bank transfer is the only one that works on the spot, which is why it is the
     * default; the rest send the admin to Settings afterwards.
     *
     * Stripe is added back when nothing registered it. Without this the card
     * simply vanishes on a Pro site whose Stripe module is switched off, which
     * reads as "we do not support Stripe" rather than "turn the module on".
     *
     * @since WPUF_SINCE
     *
     * @return array gateway id => card data
     */
    public function get_gateway_cards() {
        $gateways = wpuf_get_gateways( 'gateway_selector' );
        $gateways = is_array( $gateways ) ? $gateways : [];

        if ( ! isset( $gateways['stripe'] ) ) {
            $gateways['stripe'] = [
                'admin_label'    => __( 'Credit Card', 'wp-user-frontend' ),
                'icon'           => '',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ];

            // Pro is here, so the gateway is available; its module is just off.
            if ( wpuf_is_pro_active() ) {
                $gateways['stripe']['needs_module'] = true;
            }
        }

        // Anything that cannot take a payment until the admin enters credentials.
        $needs_credentials = [ 'paypal', 'stripe' ];

        foreach ( $gateways as $id => $gateway ) {
            $is_pro_preview = ! empty( $gateway['is_pro_preview'] );

            $gateways[ $id ]['is_pro_preview'] = $is_pro_preview;
            $gateways[ $id ]['needs_setup']    = ! $is_pro_preview && in_array( $id, $needs_credentials, true );

            if ( ! empty( $gateway['needs_module'] ) ) {
                $gateways[ $id ]['hint'] = __( 'Turn the Stripe module on in Modules, then add your keys.', 'wp-user-frontend' );
            } elseif ( $is_pro_preview ) {
                $gateways[ $id ]['hint'] = __( 'Comes with Pro.', 'wp-user-frontend' );
            } elseif ( $gateways[ $id ]['needs_setup'] ) {
                $gateways[ $id ]['hint'] = __( 'Needs your API keys in Settings.', 'wp-user-frontend' );
            } else {
                $gateways[ $id ]['hint'] = __( 'Works right away.', 'wp-user-frontend' );
            }
        }

        /**
         * Filter the gateway cards shown on the onboarding settings step
         *
         * @since WPUF_SINCE
         *
         * @param array $gateways Gateway id => card data.
         */
        $gateways = apply_filters( 'wpuf_onboarding_gateway_cards', $gateways );

        return is_array( $gateways ) ? $gateways : [];
    }

    /**
     * The recommended plugins that are not running yet
     *
     * A plugin that is later deactivated or deleted comes back on the list,
     * and with it the step.
     *
     * @since WPUF_SINCE
     *
     * @return array slug => plugin
     */
    public function get_pending_plugins() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pending = [];

        foreach ( $this->get_recommended_plugins() as $slug => $plugin ) {
            $file = $this->get_installed_file( $plugin['file'] );

            if ( $file && is_plugin_active( $file ) ) {
                continue;
            }

            $plugin['installed'] = (bool) $file;

            $pending[ $slug ] = $plugin;
        }

        return $pending;
    }

    /**
     * Install a recommended plugin from wordpress.org and switch it on
     *
     * @since WPUF_SINCE
     *
     * @param string $slug
     *
     * @return true|\WP_Error
     */
    public function install_and_activate( $slug ) {
        $plugins = $this->get_recommended_plugins();

        if ( ! isset( $plugins[ $slug ] ) ) {
            return new \WP_Error( 'unknown_plugin', __( 'Unknown plugin.', 'wp-user-frontend' ) );
        }

        $basename = $plugins[ $slug ]['file'];

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        if ( ! $this->is_plugin_installed( $basename ) ) {
            $api = plugins_api(
                'plugin_information', [
                    'slug'   => $slug,
                    'fields' => [ 'sections' => false ],
                ]
            );

            if ( is_wp_error( $api ) ) {
                return $api;
            }

            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
            $result   = $upgrader->install( $api->download_link );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            if ( ! $result ) {
                return new \WP_Error( 'install_failed', __( 'Could not install the plugin.', 'wp-user-frontend' ) );
            }
        }

        $installed_file = $this->get_installed_file( $basename );

        if ( ! $installed_file ) {
            return new \WP_Error( 'not_found', __( 'The plugin was not found after installing.', 'wp-user-frontend' ) );
        }

        $activated = activate_plugin( $installed_file );

        if ( is_wp_error( $activated ) ) {
            return $activated;
        }

        $this->clear_activation_redirects();

        return true;
    }

    /**
     * Drop the "just activated" redirects the companion plugins set for themselves
     *
     * Activating a plugin from inside the wizard makes that plugin queue its own
     * welcome or setup redirect, which fires on the next admin screen and throws the
     * admin out of onboarding half way through. Clearing the flags keeps the admin
     * in the wizard; the plugin's own setup screens stay reachable from its menu.
     *
     * Best effort by design. Deleting a transient that was never set is a no-op, and
     * the list is filterable so a plugin using a key not covered here can add it
     * rather than needing a change in this file.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function clear_activation_redirects() {
        $default_transients = [
            // WPUF's own, so finishing an install here does not bounce the admin
            // into the legacy setup wizard.
            'wpuf_activation_redirect',
            'wpuf_onboarding_redirect',
            // The companion plugins offered by this step.
            'wemail_activation_redirect',
            'erp_activation_redirect',
            '_erp_setup_page_redirect',
            'wedocs_activation_redirect',
            'pm_activation_redirect',
            'cpm_activation_redirect',
        ];

        /**
         * Filter the activation redirect flags cleared after a companion plugin installs
         *
         * @since WPUF_SINCE
         *
         * @param array $transients Transient names to delete.
         */
        $transients = apply_filters( 'wpuf_onboarding_activation_redirects', $default_transients );
        $transients = ! empty( $transients ) && is_array( $transients ) ? $transients : $default_transients;

        foreach ( $transients as $transient ) {
            if ( is_string( $transient ) && '' !== $transient ) {
                delete_transient( $transient );
            }
        }
    }

    /**
     * Last step: the diagnostics opt-in, then off to the dashboard
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_share() {
        $share   = $this->posted( 'share_essentials' ) ? 'on' : 'off';
        $general = get_option( 'wpuf_general', [] );
        $general = is_array( $general ) ? $general : [];

        $general['share_wpuf_essentials'] = $share;

        update_option( 'wpuf_general', $general );

        if ( wpuf()->tracker && isset( wpuf()->tracker->insights ) ) {
            if ( wpuf_is_checkbox_or_toggle_on( $share ) ) {
                wpuf()->tracker->insights->optin();
            } else {
                wpuf()->tracker->insights->optout();
            }
        }

        $redirect = $this->posted_value( 'redirect_to' );

        if ( $redirect ) {
            wp_safe_redirect( $redirect );
            exit;
        }
    }

    /**
     * The companion plugins offered in the wizard
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_recommended_plugins() {
        $plugins = [
            'wemail'              => [
                'logo' => 'onboarding/wemail.svg',
                'name' => __( 'weMail', 'wp-user-frontend' ),
                'file' => 'wemail/wemail.php',
                'desc' => __( 'Welcome mails, newsletters and campaigns to the people who sign up.', 'wp-user-frontend' ),
            ],
            'erp'                 => [
                'logo' => 'onboarding/erp.svg',
                'name' => __( 'WP ERP', 'wp-user-frontend' ),
                'file' => 'erp/wp-erp.php',
                'desc' => __( 'Turn your members into CRM contacts, with every interaction on one profile.', 'wp-user-frontend' ),
            ],
            'wedocs'              => [
                'logo' => 'onboarding/wedocs.svg',
                'name' => __( 'weDocs', 'wp-user-frontend' ),
                'file' => 'wedocs/wedocs.php',
                'desc' => __( 'A docs area members can read instead of opening a ticket.', 'wp-user-frontend' ),
            ],
            'wedevs-project-manager' => [
                'logo' => 'onboarding/wedevs-project-manager.svg',
                'name' => __( 'WP Project Manager', 'wp-user-frontend' ),
                'file' => 'wedevs-project-manager/cpm.php',
                'desc' => __( 'Run projects and tasks with the members who sign up.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filter the plugins recommended during onboarding
         *
         * @since WPUF_SINCE
         *
         * @param array $plugins
         */
        return apply_filters( 'wpuf_onboarding_recommended_plugins', $plugins );
    }

    /**
     * Whether a plugin is present on the site
     *
     * @since WPUF_SINCE
     *
     * @param string $basename
     *
     * @return bool
     */
    public function is_plugin_installed( $basename ) {
        return (bool) $this->get_installed_file( $basename );
    }

    /**
     * The installed plugin file for a basename
     *
     * Plugins are matched by their folder, so a main file we did not guess
     * exactly still resolves to what is really on disk.
     *
     * @since WPUF_SINCE
     *
     * @param string $basename
     *
     * @return string empty when the plugin is not installed
     */
    public function get_installed_file( $basename ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed = get_plugins();

        if ( array_key_exists( $basename, $installed ) ) {
            return $basename;
        }

        $folder = dirname( $basename );

        foreach ( array_keys( $installed ) as $file ) {
            if ( dirname( $file ) === $folder ) {
                return $file;
            }
        }

        return '';
    }

    /**
     * Turn the Pro user directory module on or off
     *
     * @since WPUF_SINCE
     *
     * @param bool $enable
     *
     * @return void
     */
    protected function toggle_pro_directory_module( $enable ) {
        $module = 'user-directory/userlisting.php';

        if ( ! function_exists( 'wpuf_pro_activate_module' ) ) {
            return;
        }

        if ( ! $enable ) {
            wpuf_pro_deactivate_module( $module );

            return;
        }

        if ( function_exists( 'wpuf_pro_is_module_allowed' ) && ! wpuf_pro_is_module_allowed( $module ) ) {
            return;
        }

        wpuf_pro_activate_module( $module );
    }

    /**
     * Whether the user directory is available on this site
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_directory_active() {
        if ( function_exists( 'wpuf_pro_is_module_active' ) && wpuf_pro_is_module_active( 'user-directory/userlisting.php' ) ) {
            return true;
        }

        return function_exists( 'wpuf_free_is_module_active' ) && wpuf_free_is_module_active( 'user_directory' );
    }

    /**
     * Create a post form from a template without leaving the wizard
     *
     * @since WPUF_SINCE
     *
     * @param string $template
     *
     * @return int|false form id
     */
    protected function create_form_from_template( $template ) {
        $registry = wpuf_get_post_form_templates();

        if ( ! isset( $registry[ $template ] ) ) {
            return false;
        }

        $template_object = $registry[ $template ];

        $form_id = wp_insert_post(
            [
                'post_title'  => $template_object->get_title(),
                'post_type'   => 'wpuf_forms',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ]
        );

        if ( is_wp_error( $form_id ) ) {
            return false;
        }

        update_post_meta( $form_id, 'wpuf_form_settings', $template_object->get_form_settings() );
        update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );

        $form_fields = $template_object->get_form_fields();

        if ( $form_fields ) {
            foreach ( $form_fields as $menu_order => $field ) {
                wp_insert_post(
                    [
                        'post_type'    => 'wpuf_input',
                        'post_status'  => 'publish',
                        'post_content' => maybe_serialize( $field ),
                        'post_parent'  => $form_id,
                        'menu_order'   => $menu_order,
                    ]
                );
            }
        }

        return $form_id;
    }

    /**
     * Whether a page holding the given shortcode already exists
     *
     * @since WPUF_SINCE
     *
     * @param string $needle
     *
     * @return bool
     */
    protected function page_exists( $needle ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft' ],
                'posts_per_page' => -1,
                'fields'         => 'ids',
                's'              => $needle,
            ]
        );

        return ! empty( $pages );
    }

    /**
     * Checklist of what the wizard has configured
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_checklist() {
        $frontend_posting = get_option( 'wpuf_frontend_posting', [] );
        $profile          = get_option( 'wpuf_profile', [] );
        $payment          = get_option( 'wpuf_payment', [] );

        $directory_on = $this->is_directory_active();

        $checklist = [];

        if ( $this->wants( 'post_form' ) ) {
            $checklist[] = [
                'label' => __( 'Post form ready', 'wp-user-frontend' ),
                'done'  => ! empty( $frontend_posting['default_post_form'] ),
                'url'   => admin_url( 'admin.php?page=wpuf-post-forms' ),
                'link'  => __( 'Post Forms', 'wp-user-frontend' ),
            ];
        }

        if ( $this->wants( 'registration' ) ) {
            $checklist[] = [
                'label' => __( 'Sign up and login pages set', 'wp-user-frontend' ),
                'done'  => ! empty( $profile['login_page'] ),
                'url'   => admin_url( 'admin.php?page=wpuf-settings#wpuf_profile' ),
                'link'  => __( 'Login / Registration', 'wp-user-frontend' ),
            ];
        }

        if ( $this->wants( 'user_directory' ) ) {
            $checklist[] = [
                'label' => __( 'User directory on', 'wp-user-frontend' ),
                'done'  => $directory_on,
                'url'   => admin_url( 'admin.php?page=wpuf_userlisting' ),
                'link'  => __( 'User Directories', 'wp-user-frontend' ),
            ];
        }

        if ( $this->wants( 'payments' ) ) {
            $checklist[] = [
                'label' => __( 'Payments ready', 'wp-user-frontend' ),
                'done'  => ! empty( $payment['enable_payment'] )
                    && wpuf_is_checkbox_or_toggle_on( $payment['enable_payment'] )
                    && ! empty( $payment['active_gateways'] ),
                'url'   => admin_url( 'admin.php?page=wpuf-settings#wpuf_payment' ),
                'link'  => __( 'Payments', 'wp-user-frontend' ),
            ];
        }

        $checklist[] = [
            'label' => __( 'UF pages installed', 'wp-user-frontend' ),
            'done'  => '1' === get_option( '_wpuf_page_created' ),
            'url'   => admin_url( 'admin.php?page=wpuf_tools&tab=tools' ),
            'link'  => __( 'Tools', 'wp-user-frontend' ),
        ];

        return $checklist;
    }
}
