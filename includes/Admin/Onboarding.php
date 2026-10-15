<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Platform\Onboarding\State;

use WeDevs\Wpuf\Platform\Onboarding\Pages;
use WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Onboarding wizard
 *
 * A guided setup that turns a fresh install into a working frontend site:
 * post form, registration, user directory, payments, the settings that have
 * to be right before anything else works, and the companion plugins.
 *
 * The wizard itself is the admin app route `#/onboarding/:step`
 * (Admin\Screens\Onboarding, React app src/admin/apps/onboarding); its steps
 * save through wpuf/v1/onboarding (Platform\REST\Controllers\
 * OnboardingController), which runs the step handlers below.
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
    const PROGRESS_OPTION = State::PROGRESS_OPTION;

    /**
     * Option holding the features the admin picked in the first step
     */
    const FEATURES_OPTION = State::FEATURES_OPTION;

    /**
     * Option holding plugins the wizard could not install
     */
    const PLUGIN_ERRORS_OPTION = State::PLUGIN_ERRORS_OPTION;

    /**
     * Option marking a finished run
     */
    const COMPLETED_OPTION = State::COMPLETED_OPTION;

    /**
     * All the steps of the wizard
     *
     * @var array
     */
    protected $steps = [];

    /**
     * Submitted values of the step being saved (slashed, like $_POST)
     *
     * @var array
     */
    protected $input = [];

    /**
     * Page lookups (lazy: tests build the wizard without its constructor).
     *
     * @var Pages|null
     */
    protected $pages = null;

    /**
     * Plugin step service (lazy).
     *
     * @var Plugin_Installer|null
     */
    protected $plugins = null;

    /**
     * The wizard's own state (options, redirect).
     *
     * @since WPUF_SINCE
     *
     * @return State
     */
    protected function state() {
        return wpuf()->platform()->get( State::class );
    }

    /**
     * Boot the wizard
     *
     * @since WPUF_SINCE
     *
     * @param bool $hooks Hook into the admin (false: the REST controller's copy)
     */
    public function __construct( $hooks = true ) {
        if ( ! $hooks ) {
            return;
        }

        add_action( 'admin_menu', [ $this, 'register_page' ] );
        add_action( 'admin_head', [ $this, 'hide_menu_link' ] );

        // Ahead of Setup_Wizard::redirect_to_page(), which runs at 9999, so a
        // first install lands here rather than in the legacy three step wizard.
        add_action( 'admin_init', [ $this, 'maybe_redirect_after_activation' ], 5 );

        // Late, so every menu this might hide is registered by the time it runs.
        add_action( 'admin_menu', [ $this, 'hide_menus_for_unpicked_features' ], 999 );
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
                // Pro's coupons row is the post type's list.
                'edit.php?post_type=wpuf_coupon',
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
     * Tools: the admin app route (no redirect hop through the old page).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function tools_url() {
        return admin_url( 'admin.php?page=wp-user-frontend#/tools' );
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
        $saved = $this->state()->features();

        // Never answered, so nothing is switched off. Leave every menu alone.
        if ( ! is_array( $saved ) ) {
            return;
        }

        $parent = 'wp-user-frontend';

        if ( isset( wpuf()->admin->menu->parent_slug ) ) {
            $parent = wpuf()->admin->menu->parent_slug;
        }

        $saved = $this->get_features();

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
        if ( ! $this->state()->has_redirect() ) {
            return;
        }

        // An AJAX request (heartbeat) or a user who cannot manage the site inside
        // the transient's window must not use up the one-time redirect.
        if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
            return;
        }

        $this->state()->consume_redirect(); // the legacy wizard's transient too

        // The legacy wizard reads its own transient later in this same request.
        // Clearing it here keeps the two from fighting over one activation.

        // Match the legacy wizard: single site only, and never mid bulk activation.
        // Presence of the flag is all that is read, and the transient consumed above
        // is what authorises this, not the request.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        // Give the post form and registration steps something to pick from. Only
        // the pages those two steps offer; the rest wait for the settings step.
        $installer = wpuf()->platform()->get( Admin_Installer::class );

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
        $hook = add_dashboard_page(
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            'manage_options',
            self::PAGE_SLUG,
            '__return_null'
        );

        // The page opens the admin app route (Admin\Screens\Onboarding).
        if ( $hook ) {
            add_action(
                'load-' . $hook,
                function () {
                    wpuf()->platform()->get( Screens\Registry::class )->load( self::PAGE_SLUG );
                }
            );
        }
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
        $saved = $this->state()->features();

        if ( ! is_array( $saved ) ) {
            return array_keys( $this->get_feature_definitions() );
        }

        // A directory that is running counts as picked, wherever it was switched
        // on (Modules screen, an earlier run): the wizard shows it ticked and
        // never switches it off without being told to.
        if ( ! in_array( 'user_directory', $saved, true ) && $this->is_directory_active() ) {
            $saved[] = 'user_directory';
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
     * Key of the step that follows a step
     *
     * @since WPUF_SINCE
     *
     * @param string $step
     *
     * @return string empty when this is the last step
     */
    public function get_next_step_key( $step ) {
        $keys  = array_keys( $this->steps ? $this->steps : $this->get_steps() );
        $index = array_search( $step, $keys, true );

        if ( false === $index || ! isset( $keys[ $index + 1 ] ) ) {
            return '';
        }

        return $keys[ $index + 1 ];
    }



    /**
     * Whether a full run has been finished
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_completed() {
        return $this->state()->is_completed();
    }

    /**
     * Wipe the progress of a finished run so the wizard opens on step one
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function maybe_restart() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( empty( $_GET['restart'] ) ) {
            return;
        }

        check_admin_referer( 'wpuf-onboarding-restart' );

        $this->state()->reset();

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
        $progress = $this->state()->progress();

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

        $this->state()->set_progress( $progress );
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
            // Finishing here counts as finishing setup, so the legacy three step
            // wizard stops claiming the activation redirect and stops nagging.
            $this->state()->mark_completed();
        }

        if ( $progress['last_step'] === $step ) {
            return;
        }

        $progress['last_step'] = $step;

        $this->state()->set_progress( $progress );
    }

    /**
     * Save a step: run its handler with the submitted values, mark it done
     *
     * @since WPUF_SINCE
     *
     * @param string $step   Step key
     * @param array  $values Submitted values (unslashed)
     *
     * @return array|\WP_Error next (step key, '' after the last one) and celebrate
     */
    public function run_step( $step, $values ) {
        $this->steps = $this->get_steps();

        if ( ! isset( $this->steps[ $step ] ) ) {
            return new \WP_Error( 'wpuf_onboarding_unknown_step', __( 'This setup step does not exist.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        // The handlers read slashed values, as they arrived in $_POST before.
        $this->input = wp_slash( is_array( $values ) ? $values : [] );

        if ( ! empty( $this->steps[ $step ]['handler'] ) && is_callable( $this->steps[ $step ]['handler'] ) ) {
            call_user_func( $this->steps[ $step ]['handler'] );
        }

        $this->input = [];

        // A handler can change which steps exist; the features step does.
        $this->steps = $this->get_steps();

        $this->mark_done( $step );

        $next = $this->get_next_step_key( $step );

        return [
            'next'      => $next,
            // Only a finished run earns the confetti, not a click on the rail.
            'celebrate' => 'ready' === $next,
        ];
    }

    /**
     * The admin opened a step: remember it, and a visit to the last step
     * finishes the run
     *
     * @since WPUF_SINCE
     *
     * @param string $step Step key
     *
     * @return void
     */
    public function visit( $step ) {
        if ( ! array_key_exists( $step, $this->get_steps() ) ) {
            return;
        }

        $this->set_last_seen( $step );

        // Whoever runs the wizard has seen what the plugin does: no welcome.
        update_user_meta( get_current_user_id(), Screens\Welcome::SEEN_META, 1 );
    }

    /**
     * Everything the wizard page shows, step by step
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_state() {
        $this->steps = $this->get_steps();

        $steps = [];

        foreach ( $this->steps as $key => $step ) {
            $steps[] = [
                'key'   => $key,
                'label' => isset( $step['label'] ) ? wp_strip_all_tags( $step['label'] ) : $key,
            ];
        }

        $progress = $this->get_progress();

        return [
            'steps'    => $steps,
            'progress' => [
                'completed' => array_values( (array) $progress['completed'] ),
                'last_step' => (string) $progress['last_step'],
            ],
            'is_pro'   => wpuf_is_pro_active(),
            'urls'     => [
                'exit'     => $this->tools_url(),
                'tools'    => $this->tools_url(),
                'settings' => wpuf_admin_app_url( '/settings', [] ),
                'payment'  => wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_payment' ] ),
                'pro'      => \WeDevs\Wpuf\Free\Pro_Prompt::get_pro_url(),
            ],
            'images'   => [
                'logo'     => WPUF_ASSET_URI . '/images/onboarding-logo.svg',
                'icon'     => WPUF_ASSET_URI . '/images/onboarding/icon.svg',
                'proBadge' => WPUF_ASSET_URI . '/images/pro-badge.svg',
            ],
            'data'     => [
                'features'     => $this->features_state(),
                'post_form'    => $this->post_form_state(),
                'registration' => $this->registration_state(),
                'common'       => $this->common_state(),
                'plugins'      => $this->plugins_state(),
                'ready'        => $this->ready_state(),
            ],
        ];
    }

    /**
     * Step 1 data
     *
     * @return array
     */
    private function features_state() {
        $features = [];

        foreach ( $this->get_feature_definitions() as $key => $feature ) {
            $features[] = [
                'key'  => $key,
                'name' => isset( $feature['name'] ) ? wp_strip_all_tags( $feature['name'] ) : $key,
                'desc' => isset( $feature['desc'] ) ? wp_strip_all_tags( $feature['desc'] ) : '',
            ];
        }

        return [
            'definitions'      => $features,
            'picked'           => array_values( $this->get_features() ),
            'directory_in_use' => $this->is_directory_active(),
        ];
    }

    /**
     * Post form step data
     *
     * @return array
     */
    private function post_form_state() {
        $templates = wpuf_get_post_form_templates();
        $templates = is_array( $templates ) ? $templates : [];
        $existing  = absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting', 0 ) );
        $options   = [];

        foreach ( $templates as $key => $template ) {
            $options[] = [
                'value' => (string) $key,
                'label' => wp_strip_all_tags( $template->get_title() ),
                'desc'  => wp_strip_all_tags( $template->get_description() ),
            ];
        }

        $options[] = [
            'value' => 'skip',
            'label' => __( 'Do not create a form', 'wp-user-frontend' ),
            'desc'  => __( 'Keep your current form and build one later.', 'wp-user-frontend' ),
        ];

        // A site that already has a default form opens on "do not create a
        // form" rather than silently offering to replace it; a site with none
        // starts on the standard template, or the first one this build has.
        $selected = $existing ? 'skip' : 'post_form_template_post';

        if ( 'skip' !== $selected && ! array_key_exists( $selected, $templates ) ) {
            $first    = key( $templates );
            $selected = null !== $first ? (string) $first : 'skip';
        }

        return [
            'templates'        => $options,
            'selected'         => $selected,
            'existing'         => $existing ? [
                'title' => wp_strip_all_tags( get_the_title( $existing ) ),
                'url'   => wpuf_admin_app_url( '/post-forms/' . absint( $existing ) . '/edit', [] ),
            ] : null,
            'enable_post_edit' => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) ),
            'enable_post_del'  => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) ),
        ];
    }

    /**
     * Login and registration step data
     *
     * @return array
     */
    private function registration_state() {
        $is_pro  = wpuf_is_pro_active();
        $layouts = wpuf_get_login_layout_options();
        $layouts = ! empty( $layouts ) && is_array( $layouts ) ? $layouts : [];
        $layout  = wpuf_get_option( 'wpuf_login_form_layout', 'wpuf_profile', 'layout1' );

        // A stored layout this build does not ship (Pro gone) falls back to
        // the basic one; free always shows the basic one.
        $first   = key( $layouts );
        $default = array_key_exists( 'layout1', $layouts ) ? 'layout1' : ( null !== $first ? (string) $first : '' );

        if ( ! $is_pro || ! array_key_exists( $layout, $layouts ) ) {
            $layout = $default;
        }

        $layout_options = [];

        foreach ( $layouts as $key => $option ) {
            $layout_options[] = [
                'value' => (string) $key,
                'label' => isset( $option['label'] ) ? wp_strip_all_tags( $option['label'] ) : (string) $key,
                'image' => isset( $option['image'] ) ? esc_url_raw( $option['image'] ) : '',
            ];
        }

        $login = $this->pages()->page_options(
            __( 'Create a new Login page', 'wp-user-frontend' ),
            $this->get_pages_for_shortcode( 'wpuf-login', __( 'UF Login Page', 'wp-user-frontend' ) )
        );

        // Free and Pro register different registration shortcodes.
        $reg = $this->pages()->page_options(
            $is_pro ? __( 'Create a new Registration page and form', 'wp-user-frontend' ) : __( 'Create a new Registration page', 'wp-user-frontend' ),
            $this->get_pages_for_shortcode( $is_pro ? 'wpuf_profile' : 'wpuf-registration', __( 'UF Registration Page', 'wp-user-frontend' ) )
        );

        $account = $this->pages()->page_options(
            __( 'Create a new Account page', 'wp-user-frontend' ),
            $this->get_pages_for_shortcode( 'wpuf_account', __( 'UF Account Page', 'wp-user-frontend' ) )
        );

        $account_page = absint( wpuf_get_option( 'account_page', 'wpuf_my_account', 0 ) );

        // The setting is often empty though the page exists: offer that page
        // rather than a second one.
        if ( ! $account_page ) {
            $account_page = $this->find_page_with_shortcode( 'wpuf_account' );
        }

        return [
            'login_pages'   => $login,
            'login_page'    => $this->pages()->page_choice( wpuf_get_option( 'login_page', 'wpuf_profile', 0 ), $login ),
            'reg_pages'     => $reg,
            'reg_page'      => $this->pages()->page_choice( wpuf_get_option( 'reg_override_page', 'wpuf_profile', 0 ), $reg ),
            'account_pages' => $account,
            'account_page'  => $this->pages()->page_choice( $account_page, $account ),
            'autologin'     => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'autologin_after_registration', 'wpuf_profile', 'on' ) ),
            'layouts'       => $layout_options,
            'layout'        => $layout,
        ];
    }

    /**
     * Settings step data
     *
     * @return array
     */
    private function common_state() {
        $admin_bar   = wpuf_get_option( 'show_admin_bar', 'wpuf_general', [ 'administrator', 'editor', 'author', 'contributor' ] );
        $admin_bar   = is_array( $admin_bar ) ? $admin_bar : [ $admin_bar ];
        $progress    = $this->get_progress();
        $revisiting  = in_array( 'common', $progress['completed'], true );
        $active_ways = wpuf_get_option( 'active_gateways', 'wpuf_payment', [] );
        $active_ways = is_array( $active_ways ) ? $active_ways : [];
        $gateways    = [];

        foreach ( $this->get_gateway_cards() as $id => $card ) {
            $id = (string) $id;

            $gateways[] = [
                'id'           => $id,
                'label'        => ! empty( $card['admin_label'] ) ? wp_strip_all_tags( $card['admin_label'] ) : $id,
                'icon'         => ! empty( $card['icon'] ) ? esc_url_raw( $card['icon'] ) : '',
                'is_pro'       => ! empty( $card['is_pro_preview'] ),
                'needs_module' => ! empty( $card['needs_module'] ),
                'needs_setup'  => ! empty( $card['needs_setup'] ),
                'hint'         => ! empty( $card['hint'] ) ? wp_strip_all_tags( $card['hint'] ) : '',
                // Bank needs no credentials, so a first run lands on it.
                'selected'     => 'bank' === $id
                    ? ( ! $revisiting || in_array( $id, $active_ways, true ) )
                    : in_array( $id, $active_ways, true ),
            ];
        }

        return [
            'hide_admin_bar'     => $revisiting ? [ 'administrator' ] === array_values( $admin_bar ) : true,
            'wants_registration' => $this->wants( 'registration' ),
            'wants_payments'     => $this->wants( 'payments' ),
            'gateways'           => $gateways,
        ];
    }

    /**
     * Plugins step data
     *
     * @return array
     */
    private function plugins_state() {
        $items  = [];
        $errors = $this->state()->plugin_errors();
        $failed = [];

        foreach ( $this->get_pending_plugins() as $slug => $plugin ) {
            $items[] = [
                'slug'      => (string) $slug,
                'name'      => wp_strip_all_tags( $plugin['name'] ),
                'desc'      => isset( $plugin['desc'] ) ? wp_strip_all_tags( $plugin['desc'] ) : '',
                'logo'      => ! empty( $plugin['logo'] ) ? WPUF_ASSET_URI . '/images/' . $plugin['logo'] : '',
                'installed' => ! empty( $plugin['installed'] ),
            ];
        }

        foreach ( $errors as $name => $message ) {
            $failed[] = [
                'name'    => wp_strip_all_tags( (string) $name ),
                'message' => wp_strip_all_tags( (string) $message ),
            ];
        }

        return [
            'items'       => $items,
            'can_install' => current_user_can( 'install_plugins' ),
            'errors'      => $failed,
        ];
    }

    /**
     * Ready step data
     *
     * @return array
     */
    private function ready_state() {
        $progress   = $this->get_progress();
        $revisiting = in_array( 'ready', $progress['completed'], true );
        $share      = $revisiting ? wpuf_get_option( 'share_wpuf_essentials', 'wpuf_general', 'off' ) : 'on';

        if ( $this->wants( 'post_form' ) ) {
            $cta = [ wpuf_admin_app_url( '/post-forms', [] ), __( 'Open my post forms', 'wp-user-frontend' ) ];
        } elseif ( $this->wants( 'registration' ) && wpuf_is_pro_active() ) {
            $cta = [ wpuf_admin_app_url( '/registration-forms', [] ), __( 'Open my registration forms', 'wp-user-frontend' ) ];
        } elseif ( $this->wants( 'registration' ) ) {
            $cta = [ wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_profile' ] ), __( 'Open login & registration settings', 'wp-user-frontend' ) ];
        } elseif ( $this->wants( 'user_directory' ) ) {
            $cta = [ admin_url( 'admin.php?page=wpuf_userlisting' ), __( 'Open my user directories', 'wp-user-frontend' ) ];
        } else {
            $cta = [ wpuf_admin_app_url( '/post-forms', [] ), __( 'Go to User Frontend', 'wp-user-frontend' ) ];
        }

        $checklist = [];

        foreach ( $this->get_checklist() as $item ) {
            $checklist[] = [
                'label' => wp_strip_all_tags( $item['label'] ),
                'done'  => ! empty( $item['done'] ),
                'url'   => esc_url_raw( $item['url'] ),
                'link'  => wp_strip_all_tags( $item['link'] ),
            ];
        }

        return [
            'checklist' => $checklist,
            'share'     => wpuf_is_checkbox_or_toggle_on( $share ),
            'cta'       => [
                'url'   => $cta[0],
                'label' => $cta[1],
            ],
        ];
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
        return ! empty( $this->input[ $key ] );
    }

    /**
     * Value of a submitted field of the current step
     *
     * @since WPUF_SINCE
     *
     * @param string $key
     * @param string $fallback
     *
     * @return string
     */
    protected function posted_value( $key, $fallback = '' ) {
        if ( ! isset( $this->input[ $key ] ) || ! is_scalar( $this->input[ $key ] ) ) {
            return $fallback;
        }

        return sanitize_text_field( wp_unslash( $this->input[ $key ] ) );
    }

    /**
     * A submitted list of keys of the current step
     *
     * @since WPUF_SINCE
     *
     * @param string $key
     *
     * @return string[]
     */
    protected function posted_keys( $key ) {
        if ( empty( $this->input[ $key ] ) ) {
            return [];
        }

        return array_map( 'sanitize_key', wp_unslash( array_filter( (array) $this->input[ $key ], 'is_scalar' ) ) );
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

        $picked = $this->posted_keys( 'features' );

        $picked = array_values( array_intersect( $allowed, $picked ) );

        // Switching off a directory that is in use takes the front end pages of
        // the site with it, so it needs the admin's explicit yes (the confirm
        // box of the step). Without it the directory stays on and stays picked.
        if ( ! in_array( 'user_directory', $picked, true ) && $this->is_directory_active() && ! $this->posted( 'confirm_directory_off' ) ) {
            $picked = array_values( array_intersect( $allowed, array_merge( $picked, [ 'user_directory' ] ) ) );
        }

        $this->state()->set_features( $picked );

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
        $payment = Stores::settings()->read( 'wpuf_payment' );
        $payment = is_array( $payment ) ? $payment : [];

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        Stores::settings()->write_section( 'wpuf_payment', $payment );
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

        $settings = Stores::settings()->read( 'wpuf_frontend_posting' );
        $settings = is_array( $settings ) ? $settings : [];

        if ( $form_id ) {
            $settings['default_post_form'] = $form_id;
        }

        Stores::settings()->write_section( 'wpuf_frontend_posting', $settings );

        // Editing and deleting live under the dashboard section, which is what
        // the frontend actually reads.
        $dashboard = Stores::settings()->read( 'wpuf_dashboard' );
        $dashboard = is_array( $dashboard ) ? $dashboard : [];

        $dashboard['enable_post_edit'] = $this->posted( 'enable_post_edit' ) ? 'yes' : 'no';
        $dashboard['enable_post_del']  = $this->posted( 'enable_post_del' ) ? 'yes' : 'no';

        Stores::settings()->write_section( 'wpuf_dashboard', $dashboard );
    }

    /**
     * Step 2: login and registration
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_registration() {
        $profile = Stores::settings()->read( 'wpuf_profile' );
        $profile = is_array( $profile ) ? $profile : [];

        $installer = wpuf()->platform()->get( Admin_Installer::class );

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
        } elseif ( $this->pages()->is_page( $login_choice ) ) {
            $profile['login_page'] = absint( $login_choice );

            $this->pages()->ensure_page_shortcode( absint( $login_choice ), 'wpuf-login', '[wpuf-login]' );
        }

        $reg_choice = $this->posted_value( 'reg_page' );
        $is_pro     = wpuf_is_pro_active();

        if ( 'create' === $reg_choice ) {
            if ( $is_pro ) {
                // Reuses the registration page the site has (as "create" does
                // for the login page); Pro builds one only when there is none.
                $data    = $installer->install_registration_page( $profile );
                $profile = $data['profile_options'];
            } else {
                // Free ships its own registration form on [wpuf-registration], so a
                // site without Pro still gets a working sign-up page. Only building
                // custom forms on top of it is the Pro part.
                $installer = wpuf()->platform()->get( Admin_Installer::class );
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
        } elseif ( $this->pages()->is_page( $reg_choice ) ) {
            $profile['reg_override_page'] = absint( $reg_choice );

            if ( $is_pro ) {
                $form_id = $this->get_registration_form_id();

                if ( $form_id ) {
                    $this->pages()->ensure_page_shortcode(
                        absint( $reg_choice ),
                        'wpuf_profile',
                        '[wpuf_profile type="registration" id="' . $form_id . '"]'
                    );
                }
            } else {
                $this->pages()->ensure_page_shortcode(
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

        Stores::settings()->write_section( 'wpuf_profile', $profile );

        $this->save_account_page();
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

        $installer = wpuf()->platform()->get( Admin_Installer::class );

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

        $account = Stores::settings()->read( 'wpuf_my_account' );
        $account = is_array( $account ) ? $account : [];

        if ( 'create' === $choice ) {
            $installer = wpuf()->platform()->get( Admin_Installer::class );
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

            // Only an existing page; anything else keeps the stored account page.
            if ( ! $this->pages()->is_page( $page_id ) ) {
                return;
            }

            $this->pages()->ensure_page_shortcode( $page_id, 'wpuf_account', '[wpuf_account]' );
        }

        $account['account_page'] = $page_id;

        Stores::settings()->write_section( 'wpuf_my_account', $account );
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
            $this->plugins()->toggle_pro_directory_module( $enabled );
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
        $payment = Stores::settings()->read( 'wpuf_payment' );
        $payment = is_array( $payment ) ? $payment : [];

        $enabled = $this->posted( 'enable_payment' );

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        if ( ! $enabled ) {
            Stores::settings()->write_section( 'wpuf_payment', $payment );

            return;
        }

        $picked = $this->posted_keys( 'active_gateways' );

        $allowed  = array_keys( wpuf_get_gateways() );
        $gateways = array_values( array_intersect( $allowed, $picked ) );

        // Payments switched on with no way to take money is a dead end, and
        // bank transfer is the one gateway that needs no credentials.
        if ( ! $gateways ) {
            $gateways = [ 'bank' ];
        }

        // Same shape the legacy settings screen stores ({ slug: slug }): checkout
        // looks gateways up by key (Frontend/Payment.php, gateway-functions.php).
        $payment['active_gateways'] = array_combine( $gateways, $gateways );

        Stores::settings()->write_section( 'wpuf_payment', $payment );
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

        $general = Stores::settings()->read( 'wpuf_general' );
        $general = is_array( $general ) ? $general : [];

        $general['show_admin_bar'] = $this->posted( 'hide_admin_bar' )
            ? [ 'administrator' ]
            : [ 'administrator', 'editor', 'author', 'contributor' ];

        // Persist the choice, not just act on it. Admin_Installer::admin_notice()
        // reads this to decide whether to keep nagging about installing the pages.
        $general['install_wpuf_pages'] = $this->posted( 'install_wpuf_pages' ) ? 'on' : 'off';

        Stores::settings()->write_section( 'wpuf_general', $general );

        if ( $this->posted( 'install_wpuf_pages' ) ) {
            $installer = wpuf()->platform()->get( Admin_Installer::class );

            $installer->init_pages();

            // init_pages() only makes the directory page when the Pro module is
            // loaded, so cover the free module here too.
            // A directory page renders as a shortcode on a classic theme and as a
            // block on a block theme, so both spellings have to count as "exists".
            $directory_exists = false;

            foreach ( Admin_Installer::USER_DIRECTORY_MARKERS as $directory_marker ) {
                if ( $this->pages()->page_exists( $directory_marker ) ) {
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
            $installer = wpuf()->platform()->get( Admin_Installer::class );

            $installer->auto_add_logout_to_menu();
        }
    }

    /**
     * Last step: the diagnostics opt-in (the page then opens the screen the
     * admin picked)
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_share() {
        $share   = $this->posted( 'share_essentials' ) ? 'on' : 'off';
        $general = Stores::settings()->read( 'wpuf_general' );
        $general = is_array( $general ) ? $general : [];

        $general['share_wpuf_essentials'] = $share;

        Stores::settings()->write_section( 'wpuf_general', $general );

        if ( wpuf()->tracker && isset( wpuf()->tracker->insights ) ) {
            if ( wpuf_is_checkbox_or_toggle_on( $share ) ) {
                wpuf()->tracker->insights->optin();
            } else {
                wpuf()->tracker->insights->optout();
            }
        }
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
        $form_fields     = $template_object->get_form_fields();

        // Same writes as the template handler, through the form store (task 2.4a).
        $form_id = Stores::forms()->create(
            [
                'post_title'           => $template_object->get_title(),
                'post_type'            => 'wpuf_forms',
                'post_status'          => 'publish',
                'post_author'          => get_current_user_id(),
                'fields'               => $form_fields ? $form_fields : [],
                'unslash_fields'       => false,
                'settings'             => $template_object->get_form_settings(),
                'store_empty_settings' => true,
                'settings_first'       => true,
            ]
        );

        if ( is_wp_error( $form_id ) ) {
            return false;
        }

        return $form_id;
    }

    /**
     * Checklist of what the wizard has configured
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_checklist() {
        $frontend_posting = Stores::settings()->read( 'wpuf_frontend_posting' );
        $profile          = Stores::settings()->read( 'wpuf_profile' );
        $payment          = Stores::settings()->read( 'wpuf_payment' );

        $directory_on = $this->is_directory_active();

        $checklist = [];

        if ( $this->wants( 'post_form' ) ) {
            $checklist[] = [
                'label' => __( 'Post form ready', 'wp-user-frontend' ),
                'done'  => ! empty( $frontend_posting['default_post_form'] ),
                'url'   => wpuf_admin_app_url( '/post-forms', [] ),
                'link'  => __( 'Post Forms', 'wp-user-frontend' ),
            ];
        }

        if ( $this->wants( 'registration' ) ) {
            $checklist[] = [
                'label' => __( 'Sign up and login pages set', 'wp-user-frontend' ),
                'done'  => ! empty( $profile['login_page'] ),
                'url'   => wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_profile' ] ),
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
                'url'   => wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_payment' ] ),
                'link'  => __( 'Payments', 'wp-user-frontend' ),
            ];
        }

        $checklist[] = [
            'label' => __( 'UF pages installed', 'wp-user-frontend' ),
            'done'  => '1' === get_option( '_wpuf_page_created' ),
            'url'   => $this->tools_url(),
            'link'  => __( 'Tools', 'wp-user-frontend' ),
        ];

        return $checklist;
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Pages::find_page_with_shortcode()
     *
     * @since WPUF_SINCE
     */
    public function find_page_with_shortcode( $tag ) {
        return $this->pages()->find_page_with_shortcode( $tag );
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Pages::get_pages_for_shortcode()
     *
     * @since WPUF_SINCE
     */
    public function get_pages_for_shortcode( $tag, $marker = '' ) {
        return $this->pages()->get_pages_for_shortcode( $tag, $marker );
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::get_gateway_cards()
     *
     * @since WPUF_SINCE
     */
    public function get_gateway_cards() {
        return $this->plugins()->get_gateway_cards();
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::get_pending_plugins()
     *
     * @since WPUF_SINCE
     */
    public function get_pending_plugins() {
        return $this->plugins()->get_pending_plugins();
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::install_and_activate()
     *
     * @since WPUF_SINCE
     */
    public function install_and_activate( $slug ) {
        return $this->plugins()->install_and_activate( $slug );
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::get_recommended_plugins()
     *
     * @since WPUF_SINCE
     */
    public function get_recommended_plugins() {
        return $this->plugins()->get_recommended_plugins();
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::is_plugin_installed()
     *
     * @since WPUF_SINCE
     */
    public function is_plugin_installed( $basename ) {
        return $this->plugins()->is_plugin_installed( $basename );
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::get_installed_file()
     *
     * @since WPUF_SINCE
     */
    public function get_installed_file( $basename ) {
        return $this->plugins()->get_installed_file( $basename );
    }

    /**
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::is_directory_active()
     *
     * @since WPUF_SINCE
     */
    public function is_directory_active() {
        return $this->plugins()->is_directory_active();
    }

    /**
     * Plugins step: install and activate the picked plugins.
     *
     * @see \WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer::save()
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function save_plugins() {
        $this->plugins()->save( $this->posted_keys( 'plugins' ) );
    }

    /**
     * Page lookups of the wizard.
     *
     * @since WPUF_SINCE
     *
     * @return Pages
     */
    protected function pages() {
        if ( ! $this->pages ) {
            $this->pages = new Pages();
        }

        return $this->pages;
    }

    /**
     * Plugin step service of the wizard.
     *
     * @since WPUF_SINCE
     *
     * @return Plugin_Installer
     */
    protected function plugins() {
        if ( ! $this->plugins ) {
            $this->plugins = new Plugin_Installer();
        }

        return $this->plugins;
    }
}
