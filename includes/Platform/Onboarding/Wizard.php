<?php
/**
 * The setup wizard
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

use WeDevs\Wpuf\Admin\Onboarding as Onboarding_Page;
use WeDevs\Wpuf\Admin\Screens\Welcome;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;

/**
 * The setup wizard's flow: which steps and features exist, what was picked,
 * the progress of a run, saving a step (Step_Handlers) and the state the
 * admin app shows (Step_Data). Shared in the container, so the wizard page
 * (Admin\Onboarding, Admin\Screens\Onboarding), the REST controller and
 * Tools see one instance on every kind of request.
 *
 * @since WPUF_SINCE
 */
class Wizard {

    /**
     * Pro's user directory module.
     */
    const PRO_DIRECTORY = 'user-directory/userlisting.php';

    /**
     * Run state (options, activation redirect).
     *
     * @var State
     */
    private $state;

    /**
     * Page lookups.
     *
     * @var Pages
     */
    private $pages;

    /**
     * Companion plugins.
     *
     * @var Plugin_Installer
     */
    private $plugins;

    /**
     * Step savers (built on first use).
     *
     * @var Step_Handlers|null
     */
    private $handlers = null;

    /**
     * Step payloads (built on first use).
     *
     * @var Step_Data|null
     */
    private $data = null;

    /**
     * @since WPUF_SINCE
     *
     * @param State            $state   Run state
     * @param Pages            $pages   Page lookups
     * @param Plugin_Installer $plugins Companion plugins
     */
    public function __construct( State $state, Pages $pages, Plugin_Installer $plugins ) {
        $this->state   = $state;
        $this->pages   = $pages;
        $this->plugins = $plugins;
    }

    /**
     * The run state.
     *
     * @since WPUF_SINCE
     *
     * @return State
     */
    public function state() {
        return $this->state;
    }

    /**
     * Page lookups.
     *
     * @since WPUF_SINCE
     *
     * @return Pages
     */
    public function pages() {
        return $this->pages;
    }

    /**
     * Companion plugins.
     *
     * @since WPUF_SINCE
     *
     * @return Plugin_Installer
     */
    public function plugins() {
        return $this->plugins;
    }

    /**
     * The step savers.
     *
     * @since WPUF_SINCE
     *
     * @return Step_Handlers
     */
    public function handlers() {
        if ( null === $this->handlers ) {
            $this->handlers = new Step_Handlers( $this );
        }

        return $this->handlers;
    }

    /**
     * The step payloads.
     *
     * @since WPUF_SINCE
     *
     * @return Step_Data
     */
    public function data() {
        if ( null === $this->data ) {
            $this->data = new Step_Data( $this );
        }

        return $this->data;
    }

    /**
     * The steps of this run: features the admin did not pick lose their step,
     * and the plugins step goes once every companion plugin runs.
     *
     * @since WPUF_SINCE
     *
     * @param bool $saving For a step save: the plugins step stays while its own
     *                     installs finish the list, so its save still runs.
     *
     * @return array key => label, handler (called with a Step_Input)
     */
    public function get_steps( $saving = false ) {
        $handlers = $this->handlers();
        $steps    = [
            'features'     => [
                'label'   => __( 'What you need', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_features' ],
            ],
            'post_form'    => [
                'label'   => __( 'Post Form', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_post_form' ],
            ],
            'registration' => [
                'label'   => __( 'Registration', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_registration' ],
            ],
            'common'       => [
                'label'   => __( 'Settings', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_common' ],
            ],
            'plugins'      => [
                'label'   => __( 'Plugins', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_plugins' ],
            ],
            'ready'        => [
                'label'   => __( 'Ready', 'wp-user-frontend' ),
                'handler' => [ $handlers, 'save_share' ],
            ],
        ];

        if ( ! $saving && ! $this->plugins->get_pending_plugins() ) {
            unset( $steps['plugins'] );
        }

        $features = $this->get_features();

        foreach ( $this->get_feature_definitions() as $feature => $definition ) {
            if ( ! empty( $definition['step'] ) && ! in_array( $feature, $features, true ) ) {
                unset( $steps[ $definition['step'] ] );
            }
        }

        /**
         * Filter the onboarding wizard steps.
         *
         * A step's handler is called with a Step_Input of the values the step sent.
         *
         * @since WPUF_SINCE
         *
         * @param array $steps key => label, handler
         */
        $steps = apply_filters( 'wpuf_onboarding_steps', $steps );

        return is_array( $steps ) ? $steps : [];
    }

    /**
     * The things WPUF can do, as offered in the first step.
     *
     * @since WPUF_SINCE
     *
     * @return array key => step (its own step, '' for none), name, desc
     */
    public function get_feature_definitions() {
        $features = [
            'post_form'      => [
                'step' => 'post_form',
                'name' => __( 'Frontend post submission', 'wp-user-frontend' ),
                'desc' => __( 'People write and publish posts on your site. They never see wp-admin.', 'wp-user-frontend' ),
            ],
            'registration'   => [
                'step' => 'registration',
                'name' => __( 'Registration & login', 'wp-user-frontend' ),
                'desc' => __( 'Sign-up and login pages that look like your site, not WordPress.', 'wp-user-frontend' ),
            ],
            'user_directory' => [
                'step' => '',
                'name' => __( 'User directory', 'wp-user-frontend' ),
                'desc' => __( 'A browsable list of your members, each with their own profile page.', 'wp-user-frontend' ),
            ],
            'payments'       => [
                'step' => '',
                'name' => __( 'Payments & subscriptions', 'wp-user-frontend' ),
                'desc' => __( 'Charge per post, sell packs, or put your directory behind a payment.', 'wp-user-frontend' ),
            ],
        ];

        // Free carries its own directory module; with Pro the directory is the
        // Pro module, offered only when the license plan includes it (pack.json).
        if ( ! $this->directory_available() ) {
            unset( $features['user_directory'] );
        }

        /**
         * Filter the features offered in the onboarding wizard.
         *
         * @since WPUF_SINCE
         *
         * @param array $features key => step, name, desc
         */
        $features = apply_filters( 'wpuf_onboarding_features', $features );

        return is_array( $features ) ? $features : [];
    }

    /**
     * Menu slugs of each feature, hidden while the feature is not picked.
     *
     * Post forms are absent: their submenu slug is the plugin's own top level
     * slug, so hiding it would take the whole menu with it.
     *
     * @since WPUF_SINCE
     *
     * @return array feature key => submenu slugs
     */
    public function get_feature_menus() {
        $menus = [
            // Free and Pro both register the registration forms screen on this slug.
            'registration'   => [ 'wpuf-profile-forms' ],
            'user_directory' => [ 'wpuf_userlisting' ],
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
         * Filter the menus hidden when a feature is switched off during onboarding.
         *
         * @since WPUF_SINCE
         *
         * @param array $menus Feature key => list of submenu slugs.
         */
        $menus = apply_filters( 'wpuf_onboarding_feature_menus', $menus );

        return ! empty( $menus ) && is_array( $menus ) ? $menus : [];
    }

    /**
     * The features the admin picked: all of them until the first step is
     * answered, and a running directory always counts as picked.
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    public function get_features() {
        $saved = $this->state->features();

        if ( ! is_array( $saved ) ) {
            return array_keys( $this->get_feature_definitions() );
        }

        // Wherever the directory was switched on (Modules screen, an earlier
        // run), the wizard shows it ticked and never switches it off unasked.
        if ( ! in_array( 'user_directory', $saved, true ) && $this->directory_active() ) {
            $saved[] = 'user_directory';
        }

        return $saved;
    }

    /**
     * Whether this site may run a user directory: always on free (its own
     * module), on Pro when the license plan carries the module.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function directory_available() {
        if ( ! wpuf_is_pro_active() || ! function_exists( 'wpuf_pro_is_module_allowed' ) ) {
            return true;
        }

        return wpuf_pro_is_module_allowed( self::PRO_DIRECTORY );
    }

    /**
     * Whether a user directory runs on this site (the Pro module or free's).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function directory_active() {
        if ( function_exists( 'wpuf_pro_is_module_active' ) && wpuf_pro_is_module_active( self::PRO_DIRECTORY ) ) {
            return true;
        }

        return function_exists( 'wpuf_free_is_module_active' ) && wpuf_free_is_module_active( 'user_directory' );
    }

    /**
     * Whether a feature was picked.
     *
     * @since WPUF_SINCE
     *
     * @param string $feature Feature key
     *
     * @return bool
     */
    public function wants( $feature ) {
        return in_array( $feature, $this->get_features(), true );
    }

    /**
     * Stored progress of the wizard.
     *
     * @since WPUF_SINCE
     *
     * @return array completed (step keys), last_step
     */
    public function get_progress() {
        $progress = $this->state->progress();

        return wp_parse_args(
            is_array( $progress ) ? $progress : [],
            [
                'completed' => [],
                'last_step' => '',
            ]
        );
    }

    /**
     * Whether the admin finished a step before (a second run).
     *
     * @since WPUF_SINCE
     *
     * @param string $step Step key
     *
     * @return bool
     */
    public function is_revisit( $step ) {
        return in_array( $step, (array) $this->get_progress()['completed'], true );
    }

    /**
     * Save a step: run its handler with the submitted values, mark it done.
     *
     * @since WPUF_SINCE
     *
     * @param string $step   Step key
     * @param array  $values Submitted values (unslashed)
     *
     * @return array|WP_Error next (step key, '' after the last one), celebrate
     */
    public function run_step( $step, array $values ) {
        $steps = $this->get_steps( true );

        if ( ! isset( $steps[ $step ] ) ) {
            return new WP_Error( 'wpuf_onboarding_unknown_step', __( 'This setup step does not exist.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( ! empty( $steps[ $step ]['handler'] ) && is_callable( $steps[ $step ]['handler'] ) ) {
            call_user_func( $steps[ $step ]['handler'], new Step_Input( $values ) );
        }

        $this->mark_done( $step );

        // A handler can change which steps exist; the features step does.
        $next = $this->next_step( $step, $this->get_steps( true ) );

        return [
            'next'      => $next,
            // Only a finished run earns the confetti, not a click on the rail.
            'celebrate' => 'ready' === $next,
        ];
    }

    /**
     * The admin opened a step: remember it; opening the last step finishes
     * the run, and whoever runs the wizard skips the one-time welcome.
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

        if ( 'ready' === $step ) {
            // Finishing here is finishing setup: the activation redirect and the
            // page notice stop claiming the admin.
            $this->state->mark_completed();
        }

        $progress = $this->get_progress();

        if ( $progress['last_step'] !== $step ) {
            $progress['last_step'] = $step;

            $this->state->set_progress( $progress );
        }

        Stores::users()->set_meta( get_current_user_id(), Welcome::SEEN_META, 1 );
    }

    /**
     * Wipe the progress of a run so the wizard opens on step one.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function restart() {
        $this->state->reset();
    }

    /**
     * Whether a full run has been finished.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_completed() {
        return $this->state->is_completed();
    }

    /**
     * The old page address of a step (opens the app route).
     *
     * @since WPUF_SINCE
     *
     * @param string $step Step key
     *
     * @return string
     */
    public function step_link( $step ) {
        return add_query_arg(
            [
                'page' => Onboarding_Page::PAGE_SLUG,
                'step' => $step,
            ],
            admin_url( 'index.php' )
        );
    }

    /**
     * Where the Tools button sends the admin, what it says, and the warning
     * before a second run.
     *
     * @since WPUF_SINCE
     *
     * @return array url, label, warning
     */
    public function entry_point() {
        $progress  = $this->get_progress();
        $completed = $this->is_completed();

        if ( $completed || empty( $progress['last_step'] ) || 'features' === $progress['last_step'] ) {
            return [
                'url'     => wp_nonce_url( add_query_arg( 'restart', '1', $this->step_link( 'features' ) ), 'wpuf-onboarding-restart' ),
                'label'   => $completed
                    ? __( 'Run Onboarding Again', 'wp-user-frontend' )
                    : __( 'Start Onboarding', 'wp-user-frontend' ),
                // Only a site that has been through the wizard has choices to overwrite.
                'warning' => $completed
                    ? __( 'You have already completed onboarding. Running it again walks through the same steps and saves over the choices you made last time. Existing pages and forms are reused rather than duplicated, and nothing changes until you save a step.', 'wp-user-frontend' )
                    : '',
            ];
        }

        return [
            'url'     => $this->step_link( $progress['last_step'] ),
            'label'   => __( 'Resume Onboarding', 'wp-user-frontend' ),
            'warning' => '',
        ];
    }

    /**
     * Everything the wizard app shows.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_state() {
        $steps = [];

        foreach ( $this->get_steps() as $key => $step ) {
            $steps[] = [
                'key'   => (string) $key,
                'label' => isset( $step['label'] ) ? wp_strip_all_tags( $step['label'] ) : (string) $key,
            ];
        }

        $progress = $this->get_progress();
        $data     = $this->data();
        $tools    = wpuf_admin_app_url( '/tools', [] );

        return [
            'steps'    => $steps,
            'progress' => [
                'completed' => array_values( (array) $progress['completed'] ),
                'last_step' => (string) $progress['last_step'],
            ],
            'is_pro'   => wpuf_is_pro_active(),
            'urls'     => [
                'exit'     => $tools,
                'tools'    => $tools,
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
                'features'     => $data->features(),
                'post_form'    => $data->post_form(),
                'registration' => $data->registration(),
                'common'       => $data->common(),
                'plugins'      => $data->plugins(),
                'ready'        => $data->ready(),
            ],
        ];
    }

    /**
     * Mark a step as completed.
     *
     * @param string $step Step key
     *
     * @return void
     */
    private function mark_done( $step ) {
        $progress = $this->get_progress();

        if ( ! in_array( $step, $progress['completed'], true ) ) {
            $progress['completed'][] = $step;
        }

        $this->state->set_progress( $progress );
    }

    /**
     * The step after a step.
     *
     * @param string $step  Step key
     * @param array  $steps Steps
     *
     * @return string '' after the last one
     */
    private function next_step( $step, array $steps ) {
        $keys  = array_keys( $steps );
        $index = array_search( $step, $keys, true );

        return false !== $index && isset( $keys[ $index + 1 ] ) ? (string) $keys[ $index + 1 ] : '';
    }
}
