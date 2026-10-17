<?php
/**
 * What each wizard step shows
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The payload of each step of the wizard app (`state.data.{step}`): the
 * choices on offer and the values stored now, plain strings only (labels
 * stripped, URLs raw: the app escapes on output).
 *
 * @since WPUF_SINCE
 */
class Step_Data {

    /**
     * The wizard.
     *
     * @var Wizard
     */
    private $wizard;

    /**
     * @since WPUF_SINCE
     *
     * @param Wizard $wizard The wizard
     */
    public function __construct( Wizard $wizard ) {
        $this->wizard = $wizard;
    }

    /**
     * Step 1: the features on offer and the picks.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function features() {
        $features = [];

        foreach ( $this->wizard->get_feature_definitions() as $key => $feature ) {
            $features[] = [
                'key'  => (string) $key,
                'name' => isset( $feature['name'] ) ? wp_strip_all_tags( $feature['name'] ) : (string) $key,
                'desc' => isset( $feature['desc'] ) ? wp_strip_all_tags( $feature['desc'] ) : '',
            ];
        }

        return [
            'definitions'      => $features,
            'picked'           => array_values( $this->wizard->get_features() ),
            'directory_in_use' => $this->wizard->directory_active(),
        ];
    }

    /**
     * Step 2: the templates and the post edit / delete switches.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function post_form() {
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

        // A site with a default form opens on "do not create a form" rather than
        // offering to replace it; one without starts on the standard template.
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
                'url'   => wpuf_admin_app_url( '/post-forms/' . $existing . '/edit', [] ),
            ] : null,
            'enable_post_edit' => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) ),
            'enable_post_del'  => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) ),
        ];
    }

    /**
     * Step 3: page choices, auto login and the login layouts.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function registration() {
        $is_pro  = wpuf_is_pro_active();
        $pages   = $this->wizard->pages();
        $layouts = wpuf_get_login_layout_options();
        $layouts = ! empty( $layouts ) && is_array( $layouts ) ? $layouts : [];
        $layout  = wpuf_get_option( 'wpuf_login_form_layout', 'wpuf_profile', 'layout1' );

        // A stored layout this build does not ship (Pro gone) falls back to the
        // basic one; free always shows the basic one.
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

        $login = $pages->page_options(
            __( 'Create a new Login page', 'wp-user-frontend' ),
            $pages->get_pages_for_shortcode( 'wpuf-login', __( 'UF Login Page', 'wp-user-frontend' ) )
        );

        // Free and Pro register different registration shortcodes.
        $reg = $pages->page_options(
            $is_pro ? __( 'Create a new Registration page and form', 'wp-user-frontend' ) : __( 'Create a new Registration page', 'wp-user-frontend' ),
            $pages->get_pages_for_shortcode( $is_pro ? 'wpuf_profile' : 'wpuf-registration', __( 'UF Registration Page', 'wp-user-frontend' ) )
        );

        $account = $pages->page_options(
            __( 'Create a new Account page', 'wp-user-frontend' ),
            $pages->get_pages_for_shortcode( 'wpuf_account', __( 'UF Account Page', 'wp-user-frontend' ) )
        );

        // The setting is often empty though the page exists: offer that page.
        $account_page = absint( wpuf_get_option( 'account_page', 'wpuf_my_account', 0 ) );

        if ( ! $account_page ) {
            $account_page = $pages->find_page_with_shortcode( 'wpuf_account' );
        }

        return [
            'login_pages'   => $login,
            'login_page'    => $pages->page_choice( wpuf_get_option( 'login_page', 'wpuf_profile', 0 ), $login ),
            'reg_pages'     => $reg,
            'reg_page'      => $pages->page_choice( wpuf_get_option( 'reg_override_page', 'wpuf_profile', 0 ), $reg ),
            'account_pages' => $account,
            'account_page'  => $pages->page_choice( $account_page, $account ),
            'autologin'     => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'autologin_after_registration', 'wpuf_profile', 'on' ) ),
            // WordPress registration must be open for sign ups (same check as
            // the registration form), with where to open it.
            'registration_open' => is_multisite()
                ? in_array( Stores::settings()->wp_option( 'registration', false, true ), [ 'user', 'all' ], true )
                : 1 === (int) Stores::settings()->wp_option( 'users_can_register' ),
            'registration_url'  => is_multisite() ? network_admin_url( 'settings.php#registration' ) : admin_url( 'options-general.php#users_can_register' ),
            'layouts'       => $layout_options,
            'layout'        => $layout,
        ];
    }

    /**
     * Step 4: admin bar and the gateway cards.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function common() {
        $admin_bar   = wpuf_get_option( 'show_admin_bar', 'wpuf_general', [ 'administrator', 'editor', 'author', 'contributor' ] );
        $admin_bar   = is_array( $admin_bar ) ? $admin_bar : [ $admin_bar ];
        $revisiting  = $this->wizard->is_revisit( 'common' );
        $active_ways = wpuf_get_option( 'active_gateways', 'wpuf_payment', [] );
        $active_ways = is_array( $active_ways ) ? $active_ways : [];
        $gateways    = [];

        foreach ( $this->gateway_cards() as $id => $card ) {
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
            'wants_registration' => $this->wizard->wants( 'registration' ),
            'wants_payments'     => $this->wizard->wants( 'payments' ),
            'gateways'           => $gateways,
        ];
    }

    /**
     * Step 5: the companion plugins not running yet, and last run's failures.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function plugins() {
        $items  = [];
        $failed = [];

        foreach ( $this->wizard->plugins()->get_pending_plugins() as $slug => $plugin ) {
            $items[] = [
                'slug'      => (string) $slug,
                'name'      => wp_strip_all_tags( $plugin['name'] ),
                'desc'      => isset( $plugin['desc'] ) ? wp_strip_all_tags( $plugin['desc'] ) : '',
                'logo'      => ! empty( $plugin['logo'] ) ? WPUF_ASSET_URI . '/images/' . $plugin['logo'] : '',
                'installed' => ! empty( $plugin['installed'] ),
            ];
        }

        foreach ( $this->wizard->state()->plugin_errors() as $name => $message ) {
            $failed[] = [
                'name'    => wp_strip_all_tags( (string) $name ),
                'message' => wp_strip_all_tags( (string) $message ),
            ];
        }

        return [
            'items'       => $items,
            'can_install' => $this->wizard->plugins()->can_install(),
            'errors'      => $failed,
        ];
    }

    /**
     * Last step: the checklist, the opt-in and where to go next.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function ready() {
        $share = $this->wizard->is_revisit( 'ready' ) ? wpuf_get_option( 'share_wpuf_essentials', 'wpuf_general', 'off' ) : 'on';

        if ( $this->wizard->wants( 'post_form' ) ) {
            $cta = [ wpuf_admin_app_url( '/post-forms', [] ), __( 'Open my post forms', 'wp-user-frontend' ) ];
        } elseif ( $this->wizard->wants( 'registration' ) && wpuf_is_pro_active() ) {
            $cta = [ wpuf_admin_app_url( '/registration-forms', [] ), __( 'Open my registration forms', 'wp-user-frontend' ) ];
        } elseif ( $this->wizard->wants( 'registration' ) ) {
            $cta = [ wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_profile' ] ), __( 'Open login & registration settings', 'wp-user-frontend' ) ];
        } elseif ( $this->wizard->wants( 'user_directory' ) ) {
            $cta = [ admin_url( 'admin.php?page=wpuf_userlisting' ), __( 'Open my user directories', 'wp-user-frontend' ) ];
        } else {
            $cta = [ wpuf_admin_app_url( '/post-forms', [] ), __( 'Go to User Frontend', 'wp-user-frontend' ) ];
        }

        return [
            'checklist' => $this->checklist(),
            'share'     => wpuf_is_checkbox_or_toggle_on( $share ),
            'cta'       => [
                'url'   => $cta[0],
                'label' => $cta[1],
            ],
        ];
    }

    /**
     * What the wizard has set up, one row per picked feature, each with the
     * screen that handles it.
     *
     * @since WPUF_SINCE
     *
     * @return array[] label, done, url, link
     */
    public function checklist() {
        $posting = Stores::settings()->read( 'wpuf_frontend_posting' );
        $profile = Stores::settings()->read( 'wpuf_profile' );
        $payment = Stores::settings()->read( 'wpuf_payment' );
        $rows    = [];

        if ( $this->wizard->wants( 'post_form' ) ) {
            $rows[] = [ __( 'Post form ready', 'wp-user-frontend' ), ! empty( $posting['default_post_form'] ), wpuf_admin_app_url( '/post-forms', [] ), __( 'Post Forms', 'wp-user-frontend' ) ];
        }

        if ( $this->wizard->wants( 'registration' ) ) {
            $rows[] = [ __( 'Sign up and login pages set', 'wp-user-frontend' ), ! empty( $profile['login_page'] ), wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_profile' ] ), __( 'Login / Registration', 'wp-user-frontend' ) ];
        }

        if ( $this->wizard->wants( 'user_directory' ) ) {
            $rows[] = [ __( 'User directory on', 'wp-user-frontend' ), $this->wizard->directory_active(), admin_url( 'admin.php?page=wpuf_userlisting' ), __( 'User Directories', 'wp-user-frontend' ) ];
        }

        if ( $this->wizard->wants( 'payments' ) ) {
            $ready  = ! empty( $payment['enable_payment'] ) && wpuf_is_checkbox_or_toggle_on( $payment['enable_payment'] ) && $this->has_ready_gateway( $payment );
            $rows[] = [ __( 'Payments ready', 'wp-user-frontend' ), $ready, wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_payment' ] ), __( 'Payments', 'wp-user-frontend' ) ];
        }

        $rows[] = [ __( 'UF pages installed', 'wp-user-frontend' ), '1' === get_option( '_wpuf_page_created' ), wpuf_admin_app_url( '/tools', [] ), __( 'Tools', 'wp-user-frontend' ) ];

        return array_map(
            function ( $row ) {
                return [
                    'label' => wp_strip_all_tags( $row[0] ),
                    'done'  => (bool) $row[1],
                    'url'   => esc_url_raw( $row[2] ),
                    'link'  => wp_strip_all_tags( $row[3] ),
                ];
            },
            $rows
        );
    }

    /**
     * Whether an active gateway can take money now: Bank Payment always,
     * PayPal and Stripe once their credentials are in (a ticked PayPal with
     * no keys is not "ready"); another plugin's gateway counts as ready.
     *
     * @since WPUF_SINCE
     *
     * @param array $payment The `wpuf_payment` settings.
     *
     * @return bool
     */
    private function has_ready_gateway( array $payment ) {
        $active = isset( $payment['active_gateways'] ) && is_array( $payment['active_gateways'] ) ? array_keys( array_filter( $payment['active_gateways'] ) ) : [];
        $filled = function ( $key ) use ( $payment ) {
            return isset( $payment[ $key ] ) && '' !== trim( (string) $payment[ $key ] );
        };

        foreach ( $active as $id ) {
            if ( 'paypal' === $id && ! $filled( 'paypal_email' ) && ! $filled( 'paypal_client_id' ) ) {
                continue;
            }

            if ( 'stripe' === $id && ! $filled( 'stripe_api_secret' ) ) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * The gateways offered on the settings step, with what each still needs.
     *
     * Built from the settings screen's own list. Bank transfer is the only one
     * that works on the spot. Stripe comes back when nothing registered it, so
     * a Pro site with the module off reads "turn the module on" rather than
     * "no Stripe".
     *
     * @since WPUF_SINCE
     *
     * @return array gateway id => admin_label, icon, is_pro_preview, needs_module, needs_setup, hint
     */
    public function gateway_cards() {
        $gateways = wpuf_get_gateways( 'gateway_selector' );
        $gateways = is_array( $gateways ) ? $gateways : [];

        if ( ! isset( $gateways['stripe'] ) ) {
            $gateways['stripe'] = [
                'admin_label'    => __( 'Credit Card', 'wp-user-frontend' ),
                'icon'           => '',
                'is_pro_preview' => ! wpuf_is_pro_active(),
                // Pro is here, so the gateway exists; its module is off.
                'needs_module'   => wpuf_is_pro_active(),
            ];
        }

        // Nothing taken until the admin enters credentials.
        $needs_credentials = [ 'paypal', 'stripe' ];

        foreach ( $gateways as $id => $gateway ) {
            $is_pro_preview = ! empty( $gateway['is_pro_preview'] );
            $needs_module   = ! empty( $gateway['needs_module'] );
            $needs_setup    = ! $is_pro_preview && ! $needs_module && in_array( $id, $needs_credentials, true );

            if ( $needs_module ) {
                $hint = __( 'Turn the Stripe module on in Modules, then add your keys.', 'wp-user-frontend' );
            } elseif ( $is_pro_preview ) {
                $hint = __( 'Comes with Pro.', 'wp-user-frontend' );
            } elseif ( $needs_setup ) {
                $hint = __( 'Needs your API keys in Settings.', 'wp-user-frontend' );
            } else {
                $hint = __( 'Works right away.', 'wp-user-frontend' );
            }

            $gateways[ $id ]['is_pro_preview'] = $is_pro_preview;
            $gateways[ $id ]['needs_setup']    = $needs_setup;
            $gateways[ $id ]['hint']           = $hint;
        }

        /**
         * Filter the gateway cards shown on the onboarding settings step.
         *
         * @since WPUF_SINCE
         *
         * @param array $gateways Gateway id => card data.
         */
        $gateways = apply_filters( 'wpuf_onboarding_gateway_cards', $gateways );

        return is_array( $gateways ) ? $gateways : [];
    }
}
