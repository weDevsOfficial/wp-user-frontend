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
 * @since 4.3.10
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

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_page' ] );
        add_action( 'admin_head', [ $this, 'hide_menu_link' ] );
        add_action( 'admin_init', [ $this, 'render' ], 1 );
    }

    /**
     * Register the hidden page that hosts the wizard
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
     * @return void
     */
    public function hide_menu_link() {
        remove_submenu_page( 'index.php', self::PAGE_SLUG );
    }

    /**
     * Get the step definitions
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
         * @since 4.3.10
         *
         * @param array $steps
         */
        return apply_filters( 'wpuf_onboarding_steps', $steps );
    }

    /**
     * The things WPUF can do, as offered in the first step
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
                'name'  => __( 'Registration &amp; login', 'wp-user-frontend' ),
                'desc'  => __( 'Sign-up and login pages that look like your site, not WordPress.', 'wp-user-frontend' ),
            ],
            'user_directory' => [
                'step'  => '',
                'name'  => __( 'User directory', 'wp-user-frontend' ),
                'desc'  => __( 'A browsable list of your members, each with their own profile page.', 'wp-user-frontend' ),
            ],
            'payments'       => [
                'step'  => '',
                'name'  => __( 'Payments &amp; subscriptions', 'wp-user-frontend' ),
                'desc'  => __( 'Charge per post, sell packs, or put your directory behind a payment.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filter the features offered in the onboarding wizard
         *
         * @since 4.3.10
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

            // A handler can change which steps exist — the features step does.
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
     * @return void
     */
    protected function render_header() {
        wp_enqueue_style( 'wpuf-onboarding' );

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
                <img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/onboarding-logo.png' ); ?>" alt="<?php esc_attr_e( 'User Frontend', 'wp-user-frontend' ); ?>" />
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
                                        <?php if ( 'is-done' === $state ) : ?>
                                            <svg width="9" height="7" viewBox="0 0 9 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php else : ?>
                                            <?php echo esc_html( $index + 1 ); ?>
                                        <?php endif; ?>
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
     * @return void
     */
    protected function render_footer() {
        ?>
        </body>
        </html>
        <?php
    }

    /**
     * Render the fixed action bar of a step
     *
     * @param array $args
     *
     * @return void
     */
    public function action_bar( $args = [] ) {
        $args = wp_parse_args(
            $args, [
                'next_label' => __( 'Save &amp; Continue', 'wp-user-frontend' ),
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
     * @return bool
     */
    public function is_completed() {
        return (bool) get_option( self::COMPLETED_OPTION );
    }

    /**
     * Wipe the progress of a finished run so the wizard opens on step one
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
     * @return array url and label
     */
    public function get_entry_point() {
        $progress = $this->get_progress();

        if ( $this->is_completed() || empty( $progress['last_step'] ) || 'features' === $progress['last_step'] ) {
            return [
                'url'   => wp_nonce_url(
                    add_query_arg( 'restart', '1', $this->get_step_link( 'features' ) ),
                    'wpuf-onboarding-restart'
                ),
                'label' => __( 'Start Onboarding', 'wp-user-frontend' ),
            ];
        }

        return [
            'url'   => $this->get_step_link( $progress['last_step'] ),
            'label' => __( 'Resume Onboarding', 'wp-user-frontend' ),
        ];
    }

    /**
     * Stored progress of the wizard
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
     * @param string $step
     *
     * @return void
     */
    protected function set_last_seen( $step ) {
        $progress = $this->get_progress();

        if ( 'ready' === $step ) {
            update_option( self::COMPLETED_OPTION, 1 );
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
    }

    /**
     * Step 2: frontend posting
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
     * @return void
     */
    public function save_registration() {
        $profile = get_option( 'wpuf_profile', [] );
        $profile = is_array( $profile ) ? $profile : [];

        $installer = new Admin_Installer();

        $login_choice = $this->posted_value( 'login_page' );

        if ( 'create' === $login_choice ) {
            $login_page = $installer->create_page( __( 'Login', 'wp-user-frontend' ), '[wpuf-login]' );

            if ( $login_page ) {
                $profile['login_page'] = $login_page;
            }
        } elseif ( absint( $login_choice ) ) {
            $profile['login_page'] = absint( $login_choice );

            $this->ensure_page_shortcode( absint( $login_choice ), 'wpuf-login', '[wpuf-login]' );
        }

        $reg_choice = $this->posted_value( 'reg_page' );

        if ( 'create' === $reg_choice ) {
            $data = apply_filters( 'wpuf_pro_page_install', $profile );

            if ( is_array( $data ) && isset( $data['profile_options'] ) ) {
                $profile = $data['profile_options'];
            }
        } elseif ( absint( $reg_choice ) ) {
            $profile['reg_override_page'] = absint( $reg_choice );

            $form_id = $this->get_registration_form_id();

            if ( $form_id ) {
                $this->ensure_page_shortcode(
                    absint( $reg_choice ),
                    'wpuf_profile',
                    '[wpuf_profile type="registration" id="' . $form_id . '"]'
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
     * has_shortcode() only matches tags registered at the time of the call,
     * and the WPUF shortcodes are not registered on the wizard screen, so the
     * tag is matched directly instead.
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
            $page_id   = $installer->create_page( __( 'Account', 'wp-user-frontend' ), '[wpuf_account]' );

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

        update_option( 'wpuf_general', $general );

        if ( $this->posted( 'install_wpuf_pages' ) ) {
            $installer = new Admin_Installer();

            $installer->init_pages();

            // init_pages() only makes the directory page when the Pro module is
            // loaded, so cover the free module here too.
            if ( $this->wants( 'user_directory' ) && $this->is_directory_active() && ! $this->page_exists( '[wpuf_user_listing' ) ) {
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

        foreach ( $picked as $slug ) {
            if ( ! isset( $plugins[ $slug ] ) ) {
                continue;
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
     * The recommended plugins that are not running yet
     *
     * A plugin that is later deactivated or deleted comes back on the list,
     * and with it the step.
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
     * Last step: the diagnostics opt-in, then off to the dashboard
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
            if ( 'on' === $share ) {
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
                'logo' => 'onboarding/erp.png',
                'name' => __( 'WP ERP', 'wp-user-frontend' ),
                'file' => 'erp/wp-erp.php',
                'desc' => __( 'Turn your members into CRM contacts, with every interaction on one profile.', 'wp-user-frontend' ),
            ],
            'wedocs'              => [
                'logo' => 'onboarding/wedocs.png',
                'name' => __( 'weDocs', 'wp-user-frontend' ),
                'file' => 'wedocs/wedocs.php',
                'desc' => __( 'A docs area members can read instead of opening a ticket.', 'wp-user-frontend' ),
            ],
            'wedevs-project-manager' => [
                'logo' => 'onboarding/wedevs-project-manager.png',
                'name' => __( 'WP Project Manager', 'wp-user-frontend' ),
                'file' => 'wedevs-project-manager/cpm.php',
                'desc' => __( 'Run projects and tasks with the members who sign up.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filter the plugins recommended during onboarding
         *
         * @since 4.3.10
         *
         * @param array $plugins
         */
        return apply_filters( 'wpuf_onboarding_recommended_plugins', $plugins );
    }

    /**
     * Whether a plugin is present on the site
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
                'done'  => ! empty( $payment['enable_payment'] ) && 'on' === $payment['enable_payment'] && ! empty( $payment['active_gateways'] ),
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
