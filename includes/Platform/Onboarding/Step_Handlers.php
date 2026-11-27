<?php
/**
 * What each wizard step saves
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

use WeDevs\Wpuf\Admin\Admin_Installer;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The step savers of the setup wizard. Every settings write goes through
 * the settings store (so `wpuf_settings_saved` fires as on the settings
 * screen), forms through the form store, pages through Admin_Installer.
 *
 * @since WPUF_SINCE
 */
class Step_Handlers {

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
     * Step 1: what the site needs. The directory and payments have no step of
     * their own, so the pick here switches them on or off.
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_features( Step_Input $input ) {
        $allowed   = array_keys( $this->wizard->get_feature_definitions() );
        $picked    = array_values( array_intersect( $allowed, $input->keys( 'features' ) ) );
        $directory = $this->wizard->directory_active();

        // Switching off a directory in use takes its front end pages with it, so
        // it needs the step's explicit yes; without it the directory stays on.
        if ( ! in_array( 'user_directory', $picked, true ) && $directory && ! $input->flag( 'confirm_directory_off' ) ) {
            $picked = array_values( array_intersect( $allowed, array_merge( $picked, [ 'user_directory' ] ) ) );
        }

        $this->wizard->state()->set_features( $picked );

        $this->toggle_directory( in_array( 'user_directory', $picked, true ) );

        // Unticking payments switches the setting off, not only its menus, or the
        // gateways keep working while the admin believes they turned it off.
        $this->toggle_payments( in_array( 'payments', $picked, true ) );
    }

    /**
     * Step 2: the default post form and what authors may do afterwards.
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_post_form( Step_Input $input ) {
        $template = $input->text( 'post_form_template' );
        $form_id  = absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting', 0 ) );

        if ( $template && 'skip' !== $template ) {
            $new_form = $this->create_form_from_template( $template );

            if ( $new_form ) {
                $form_id = $new_form;
            }
        }

        $settings = $this->section( 'wpuf_frontend_posting' );

        if ( $form_id ) {
            $settings['default_post_form'] = $form_id;
        }

        Stores::settings()->write_section( 'wpuf_frontend_posting', $settings );

        // Editing and deleting live in the dashboard section, which the frontend reads.
        $dashboard = $this->section( 'wpuf_dashboard' );

        $dashboard['enable_post_edit'] = $input->flag( 'enable_post_edit' ) ? 'yes' : 'no';
        $dashboard['enable_post_del']  = $input->flag( 'enable_post_del' ) ? 'yes' : 'no';

        Stores::settings()->write_section( 'wpuf_dashboard', $dashboard );
    }

    /**
     * Step 3: the login, registration and account pages, auto login and the
     * login layout.
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_registration( Step_Input $input ) {
        $profile   = $this->section( 'wpuf_profile' );
        $installer = $this->installer();
        $pages     = $this->wizard->pages();
        $is_pro    = wpuf_is_pro_active();

        $login_choice = $input->text( 'login_page' );

        if ( 'create' === $login_choice ) {
            // A page that already carries the shortcode is reused, so "create" on
            // a second run does not publish a duplicate.
            $login_page = $installer->get_or_create_page(
                __( 'Login', 'wp-user-frontend' ),
                '[wpuf-login]',
                isset( $profile['login_page'] ) ? absint( $profile['login_page'] ) : 0,
                '[wpuf-login]'
            );

            if ( $login_page ) {
                $profile['login_page'] = $login_page;
            }
        } elseif ( $pages->is_page( $login_choice ) ) {
            $profile['login_page'] = absint( $login_choice );

            $pages->ensure_page_shortcode( absint( $login_choice ), 'wpuf-login', '[wpuf-login]' );
        }

        $reg_choice = $input->text( 'reg_page' );

        if ( 'create' === $reg_choice ) {
            if ( $is_pro ) {
                // Reuses the site's registration page; Pro builds one only when there is none.
                $data    = $installer->install_registration_page( $profile );
                $profile = $data['profile_options'];
            } else {
                // Free's own form on [wpuf-registration]: a working sign-up page without Pro.
                $reg_page = $installer->get_or_create_page(
                    __( 'Registration', 'wp-user-frontend' ),
                    '[wpuf-registration]',
                    0,
                    '[wpuf-registration]'
                );

                if ( $reg_page ) {
                    $profile['reg_override_page'] = $reg_page;
                }
            }
        } elseif ( $pages->is_page( $reg_choice ) ) {
            $profile['reg_override_page'] = absint( $reg_choice );

            if ( $is_pro ) {
                $form_id = $this->registration_form_id();

                if ( $form_id ) {
                    $pages->ensure_page_shortcode( absint( $reg_choice ), 'wpuf_profile', '[wpuf_profile type="registration" id="' . $form_id . '"]' );
                }
            } else {
                $pages->ensure_page_shortcode( absint( $reg_choice ), 'wpuf-registration', '[wpuf-registration]' );
            }
        }

        // Only send WordPress register links at a page that exists.
        $profile['register_link_override'] = ! empty( $profile['reg_override_page'] ) ? 'on' : 'off';

        $profile['autologin_after_registration'] = $input->flag( 'autologin_after_registration' ) ? 'on' : 'off';

        // Layouts are a Pro feature; the free preview sends nothing.
        $layout = $input->text( 'wpuf_login_form_layout' );

        if ( $layout && array_key_exists( $layout, wpuf_get_login_layout_options() ) ) {
            $profile['wpuf_login_form_layout'] = $layout;
        }

        Stores::settings()->write_section( 'wpuf_profile', $profile );

        $this->save_account_page( $input->text( 'account_page' ) );
    }

    /**
     * Step 4: pages, admin bar, logout link, and payments when picked.
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_common( Step_Input $input ) {
        if ( $this->wizard->wants( 'payments' ) ) {
            $this->save_payment_settings( $input );
        }

        $general       = $this->section( 'wpuf_general' );
        $install_pages = $input->flag( 'install_wpuf_pages' );

        $general['show_admin_bar'] = $input->flag( 'hide_admin_bar' )
            ? [ 'administrator' ]
            : [ 'administrator', 'editor', 'author', 'contributor' ];

        // Stored, not only acted on: Admin_Installer::admin_notice() reads it.
        $general['install_wpuf_pages'] = $install_pages ? 'on' : 'off';

        Stores::settings()->write_section( 'wpuf_general', $general );

        if ( $install_pages ) {
            $this->install_pages();
        }

        if ( $input->flag( 'add_logout_menu' ) ) {
            $this->installer()->auto_add_logout_to_menu();
        }
    }

    /**
     * Step 5: install and activate the picked companion plugins still
     * missing (the step installs them one by one first, so this only runs
     * what the app did not).
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_plugins( Step_Input $input ) {
        $this->wizard->plugins()->save( $input->keys( 'plugins' ) );
    }

    /**
     * Last step: the diagnostics opt-in.
     *
     * @since WPUF_SINCE
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    public function save_share( Step_Input $input ) {
        $share   = $input->flag( 'share_essentials' ) ? 'on' : 'off';
        $general = $this->section( 'wpuf_general' );

        $general['share_wpuf_essentials'] = $share;

        Stores::settings()->write_section( 'wpuf_general', $general );

        if ( wpuf()->tracker && isset( wpuf()->tracker->insights ) ) {
            if ( 'on' === $share ) {
                wpuf()->tracker->insights->optin();
            } else {
                wpuf()->tracker->insights->optout();
            }
        }
    }

    /**
     * Switch the user directory module on or off.
     *
     * @since WPUF_SINCE
     *
     * @param bool $enabled On
     *
     * @return void
     */
    public function toggle_directory( $enabled ) {
        // Pro serves the directory from its plan-gated module list (free's module
        // loads only without Pro); the free list is kept in step for a site
        // that later runs without Pro.
        if ( function_exists( 'wpuf_pro_activate_module' ) ) {
            if ( ! $enabled ) {
                wpuf_pro_deactivate_module( Wizard::PRO_DIRECTORY );
            } elseif ( $this->wizard->directory_available() ) {
                wpuf_pro_activate_module( Wizard::PRO_DIRECTORY );
            }
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
     * Switch the payment setting to the feature pick. Switching back on only
     * lifts the block: which gateways run stays with the settings step.
     *
     * @since WPUF_SINCE
     *
     * @param bool $enabled On
     *
     * @return void
     */
    public function toggle_payments( $enabled ) {
        $payment = $this->section( 'wpuf_payment' );

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        Stores::settings()->write_section( 'wpuf_payment', $payment );
    }

    /**
     * Create a post form from a template, through the form store.
     *
     * @since WPUF_SINCE
     *
     * @param string $template Template key
     *
     * @return int|false Form id
     */
    public function create_form_from_template( $template ) {
        $registry = wpuf_get_post_form_templates();

        if ( ! isset( $registry[ $template ] ) ) {
            return false;
        }

        $template_object = $registry[ $template ];
        $form_fields     = $template_object->get_form_fields();

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

        return is_wp_error( $form_id ) ? false : $form_id;
    }

    /**
     * Payments of the settings step: on or off, and the active gateways.
     *
     * @param Step_Input $input Submitted values
     *
     * @return void
     */
    private function save_payment_settings( Step_Input $input ) {
        $payment = $this->section( 'wpuf_payment' );
        $enabled = $input->flag( 'enable_payment' );

        $payment['enable_payment'] = $enabled ? 'on' : 'off';

        if ( $enabled ) {
            $gateways = array_values( array_intersect( array_keys( wpuf_get_gateways() ), $input->keys( 'active_gateways' ) ) );

            // Payments on with no way to take money is a dead end; bank transfer
            // needs no credentials.
            if ( ! $gateways ) {
                $gateways = [ 'bank' ];
            }

            // The settings screen's shape ({ slug: slug }): checkout looks gateways up by key.
            $payment['active_gateways'] = array_combine( $gateways, $gateways );
        }

        Stores::settings()->write_section( 'wpuf_payment', $payment );
    }

    /**
     * Point the account page setting at a real page.
     *
     * @param string $choice "create" or a page id
     *
     * @return void
     */
    private function save_account_page( $choice ) {
        if ( ! $choice ) {
            return;
        }

        $account = $this->section( 'wpuf_my_account' );
        $pages   = $this->wizard->pages();

        if ( 'create' === $choice ) {
            $page_id = $this->installer()->get_or_create_page(
                __( 'Account', 'wp-user-frontend' ),
                '[wpuf_account]',
                isset( $account['account_page'] ) ? absint( $account['account_page'] ) : 0,
                '[wpuf_account]'
            );
        } else {
            $page_id = absint( $choice );

            // Only an existing page; anything else keeps the stored account page.
            if ( ! $pages->is_page( $page_id ) ) {
                return;
            }

            $pages->ensure_page_shortcode( $page_id, 'wpuf_account', '[wpuf_account]' );
        }

        if ( ! $page_id ) {
            return;
        }

        $account['account_page'] = $page_id;

        Stores::settings()->write_section( 'wpuf_my_account', $account );
    }

    /**
     * The remaining UF pages, and the directory page for the free module.
     *
     * @return void
     */
    private function install_pages() {
        $installer = $this->installer();

        $installer->init_pages();

        // init_pages() makes the directory page only when the Pro module is
        // loaded. A directory page is a shortcode on a classic theme and a block
        // on a block theme, so either spelling counts as "exists".
        $exists = false;

        foreach ( Admin_Installer::USER_DIRECTORY_MARKERS as $marker ) {
            if ( $this->wizard->pages()->page_exists( $marker ) ) {
                $exists = true;

                break;
            }
        }

        if ( ! $exists && $this->wizard->wants( 'user_directory' ) && $this->wizard->directory_active() ) {
            $installer->create_page( __( 'User Directory', 'wp-user-frontend' ), $installer->get_user_directory_page_content() );
        }
    }

    /**
     * A registration form to point a page at, creating one if needed.
     *
     * @return int Form id, 0 when none could be made
     */
    private function registration_form_id() {
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

        return absint( $this->installer()->create_reg_form() );
    }

    /**
     * A settings section as stored (an array).
     *
     * @param string $section Option name
     *
     * @return array
     */
    private function section( $section ) {
        $values = Stores::settings()->read( $section );

        return is_array( $values ) ? $values : [];
    }

    /**
     * The page installer.
     *
     * @return Admin_Installer
     */
    private function installer() {
        return wpuf()->platform()->get( Admin_Installer::class );
    }
}
