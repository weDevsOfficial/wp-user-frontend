<?php

namespace WeDevs\Wpuf\Admin;

/**
 * Page installer
 *
 * @since 2.3
 */
class Admin_Installer {

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );
        add_action( 'admin_init', [ $this, 'handle_request' ] );
    }

    /**
     * Print admin notices
     *
     * @return void
     */
    public function admin_notice() {
        $page_created = get_option( '_wpuf_page_created' );
        if ( $page_created != '1' && 'off' == wpuf_get_option( 'install_wpuf_pages', 'wpuf_general', 'on' ) ) {
            ?>
            <div class="updated error">
                <p>
                    <?php esc_html_e( 'If you have not created <strong>WP User Frontend</strong> pages yet, you can do this by one click.',
                                      'wp-user-frontend' ); ?>
                </p>
                <p class="submit">
                    <a class="button button-primary"
                       href="<?php echo esc_url( add_query_arg( [ 'install_wpuf_pages' => true ],
                                                                admin_url( 'admin.php?page=wpuf-settings' ) ) ); ?>"><?php esc_html_e( 'Install WPUF Pages',
                                                                                                                                       'wp-user-frontend' ); ?></a>
                    <?php esc_html_e( 'or', 'wp-user-frontend' ); ?>
                    <a class="button"
                       href="<?php echo esc_url( add_query_arg( [ 'wpuf_hide_page_nag' => true ] ) ); ?>"><?php esc_html_e( 'Skip Setup',
                                                                                                                            'wp-user-frontend' ); ?></a>
                </p>
            </div>
            <?php
        }
        if ( isset( $_GET['wpuf_page_installed'] ) && $_GET['wpuf_page_installed'] == '1' ) {
            ?>
            <div class="updated">
                <p>
                    <strong><?php esc_html_e( 'Congratulations!',
                                              'wp-user-frontend' ); ?></strong> <?php echo wp_kses_post( 'Pages for <strong>WP User Frontend</strong> has been successfully installed and saved!',
                                                                                                         'wp-user-frontend' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Handle the page creation button requests
     *
     * @return void
     */
    public function handle_request() {
        $nonce = isset( $_REQUEST['wpuf_steup'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf_steup'] ) ) : '';
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( 'wpuf_steup' ) ) {
        }
        if ( isset( $_GET['install_wpuf_pages'] ) && $_GET['install_wpuf_pages'] == '1' ) {
            $this->init_pages();
        }
        // if ( isset( $_POST['install_wpuf_pages'] ) && $_POST['install_wpuf_pages'] == '1' ) {
        //     $this->init_pages();
        // }
        if ( isset( $_GET['wpuf_hide_page_nag'] ) && $_GET['wpuf_hide_page_nag'] == '1' ) {
            update_option( '_wpuf_page_created', '1' );
        }
    }

    /**
     * Initialize the plugin with some default page/settings
     *
     * @since 2.2
     *
     * @return void
     */
    public function init_pages() {
        // create a dashboard page
        $dashboard_page = $this->create_page( __( 'Dashboard', 'wp-user-frontend' ), '[wpuf_dashboard]' );
        $account_page   = $this->create_page( __( 'Account', 'wp-user-frontend' ), '[wpuf_account]' );
        $edit_page      = $this->create_page( __( 'Edit', 'wp-user-frontend' ), '[wpuf_edit]' );
        // login page
        $login_page = $this->create_page( __( 'Login', 'wp-user-frontend' ), '[wpuf-login]' );
        $post_form = $this->create_form();
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            // payment page
            $subscr_page  = $this->create_page( __( 'Subscription', 'wp-user-frontend' ),
                                                __( '[wpuf_sub_pack]', 'wp-user-frontend' ) );
            $payment_page = $this->create_page( __( 'Payment', 'wp-user-frontend' ),
                                                __( 'Please select a gateway for payment', 'wp-user-frontend' ) );
            $thank_page   = $this->create_page( __( 'Thank You', 'wp-user-frontend' ),
                                                __( '<h1>Payment is complete</h1><p>Congratulations, your payment has been completed!</p>',
                                                    'wp-user-frontend' ) );
            $bank_page    = $this->create_page( __( 'Order Received', 'wp-user-frontend' ),
                                                __( 'Hi, we have received your order. We will validate the order and will take necessary steps to move forward.',
                                                    'wp-user-frontend' ) );
        }
        // save the settings
        if ( $edit_page ) {
            update_option( 'wpuf_frontend_posting', [
                'edit_page_id'      => $edit_page,
                'default_post_form' => $post_form,
            ] );
        }
        // profile pages
        $profile_options = [];
        $reg_page        = false;
        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }
        $data = apply_filters( 'wpuf_pro_page_install', $profile_options );
        if ( is_array( $data ) ) {
            if ( isset( $data['profile_options'] ) ) {
                $profile_options = $data['profile_options'];
            }
            if ( isset( $data['reg_page'] ) ) {
                $reg_page = $data['reg_page'];
            }
        }
        if ( $login_page && $reg_page ) {
            $profile_options['register_link_override'] = 'on';
        }
        update_option( 'wpuf_profile', $profile_options );
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            // payment pages
            update_option( 'wpuf_payment', [
                'subscription_page' => $subscr_page,
                'payment_page'      => $payment_page,
                'payment_success'   => $thank_page,
                'bank_success'      => $bank_page,
            ] );
        }
        update_option( '_wpuf_page_created', '1' );

        // User Directory page (Pro only): created when WPUF_User_Listing is available.
        if ( class_exists( 'WPUF_User_Listing' ) ) {
            $this->create_page(
                __( 'User Directory', 'wp-user-frontend' ),
                $this->get_user_directory_page_content()
            );
        }

        // Auto-add logout link to the primary menu
        $this->auto_add_logout_to_menu();

        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        if ( $page != 'wpuf-setup' ) {
            wp_redirect( admin_url( 'admin.php?page=wpuf-settings&wpuf_page_installed=1' ) );
            exit;
        }
    }

    /**
     * Automatically add logout link to the primary navigation menu
     *
     * @since 4.2.10
     *
     * @return void
     */
    public function auto_add_logout_to_menu() {
        // Check if it's a block theme (FSE)
        if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
            // For FSE themes, try to add to wp_navigation post type
            $this->add_logout_to_fse_navigation();
            return;
        }

        // For classic themes, find the primary menu and add logout
        $this->add_logout_to_classic_menu();
    }

    /**
     * Add logout link to classic theme menu
     *
     * @since 4.2.10
     *
     * @return bool
     */
    private function add_logout_to_classic_menu() {
        // Get all registered menu locations
        $locations = get_nav_menu_locations();

        // Priority order for menu locations to add logout
        $priority_locations = [ 'primary', 'main', 'main-menu', 'primary-menu', 'header', 'header-menu', 'top', 'top-menu' ];

        $menu_id = 0;

        // Try to find a menu in priority order
        foreach ( $priority_locations as $location ) {
            if ( ! empty( $locations[ $location ] ) ) {
                $menu_id = $locations[ $location ];
                break;
            }
        }

        // If no priority location found, try the first available menu location
        if ( ! $menu_id && ! empty( $locations ) ) {
            $menu_id = reset( $locations );
        }

        // If still no menu, try to get any existing menu
        if ( ! $menu_id ) {
            $menus = wp_get_nav_menus();
            if ( ! empty( $menus ) ) {
                $menu_id = $menus[0]->term_id;
            }
        }

        if ( ! $menu_id ) {
            return false;
        }

        // Check if logout already exists in this menu
        $menu_items = wp_get_nav_menu_items( $menu_id );
        if ( $menu_items ) {
            foreach ( $menu_items as $item ) {
                if ( strpos( $item->url, 'action=logout' ) !== false ) {
                    // Logout already exists
                    return true;
                }
            }
        }

        // Add logout to menu
        if ( function_exists( 'wpuf_add_logout_to_menu' ) ) {
            $result = wpuf_add_logout_to_menu( $menu_id, __( 'Logout', 'wp-user-frontend' ) );
            return ! is_wp_error( $result );
        }

        return false;
    }

    /**
     * Add logout link to FSE navigation
     *
     * @since 4.2.10
     *
     * @return bool
     */
    private function add_logout_to_fse_navigation() {
        // Get all wp_navigation posts
        $navigations = get_posts( [
            'post_type'      => 'wp_navigation',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ] );

        if ( empty( $navigations ) ) {
            return false;
        }

        $logout_url   = function_exists( 'wpuf_get_logout_url' ) ? wpuf_get_logout_url() : wp_logout_url();
        $logout_label = __( 'Logout', 'wp-user-frontend' );
        $logout_block = sprintf(
            '<!-- wp:navigation-link {"label":"%s","url":"%s","kind":"custom","isTopLevelLink":true} /-->',
            esc_attr( $logout_label ),
            esc_url( $logout_url )
        );

        $updated = false;

        foreach ( $navigations as $navigation ) {
            // Check if logout already exists
            if ( strpos( $navigation->post_content, 'action=logout' ) !== false || strpos( $navigation->post_content, 'Logout' ) !== false ) {
                continue;
            }

            // Append logout link to the navigation content
            $new_content = $navigation->post_content . "\n" . $logout_block;

            wp_update_post( [
                'ID'           => $navigation->ID,
                'post_content' => $new_content,
            ] );

            $updated = true;
        }

        return $updated;
    }

    /**
     * Get the post content for the User Directory page.
     *
     * Returns the wpuf-ud/directory Gutenberg block markup for block themes and
     * the [wpuf_user_listing] shortcode for classic themes. Wrapped in a filter
     * so integrations can customize the generated content.
     *
     * @since 4.3.2
     *
     * @return string
     */
    public function get_user_directory_page_content() {
        $is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

        if ( $is_block_theme ) {
            $content = $this->get_user_directory_block_content();
        } else {
            $content = '[wpuf_user_listing]';
        }

        /**
         * Filter the User Directory page content generated during page installation.
         *
         * @since 4.3.2
         *
         * @param string $content        Rendered page content (block markup or shortcode).
         * @param bool   $is_block_theme Whether the active theme is a block (FSE) theme.
         */
        return apply_filters( 'wpuf_user_directory_page_content', $content, $is_block_theme );
    }

    /**
     * Get the default Gutenberg block markup for the User Directory page.
     *
     * @since 4.3.2
     *
     * @return string
     */
    private function get_user_directory_block_content() {
        return <<<'HTML'
<!-- wp:wpuf-ud/directory {"directory_layout":"roundGrids","hasSelectedLayout":true,"selectedLayout":"roundGrids"} -->
<div class="wp-block-wpuf-ud-directory"><!-- wp:wpuf-ud/directory-item -->
<div class="wp-block-wpuf-ud-directory-item"><!-- wp:group {"className":"is-style-default","style":{"border":{"radius":"8px","color":"#d1d5db","width":"1px"},"spacing":{"margin":{"top":"0","bottom":"0"},"blockGap":"0","padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"0","right":"0"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group is-style-default has-border-color" style="border-color:#d1d5db;border-width:1px;border-radius:8px;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><!-- wp:wpuf-ud/avatar {"avatarSize":"custom","fallbackType":"gravatar","customSize":128,"style":{"spacing":{"padding":{"bottom":"15px"},"margin":{"top":"3px"}}}} /-->

<!-- wp:wpuf-ud/name {"style":{"color":"#0F172A","fontWeight":"bold","typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"20px","lineHeight":"2"},"spacing":{"margin":{"bottom":"2px"}}}} /-->

<!-- wp:wpuf-ud/contact {"showIcons":false,"iconSize":"small","showLabels":false,"className":"wpuf-user-contact-info wpuf-contact-layout-inline","style":{"color":{"text":"#64748B"},"typography":{"lineHeight":"1","textAlign":"center","fontSize":"14px"},"spacing":{"margin":{"bottom":"5px"}}}} /-->

<!-- wp:wpuf-ud/social {"iconSize":"medium","style":{"spacing":{"padding":{"top":"5px","bottom":"5px"},"margin":{"top":"5px","bottom":"5px"}}}} /-->

<!-- wp:wpuf-ud/profile-button {"textColor":"base","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"9px","right":"17px","bottom":"9px","left":"17px"},"margin":{"top":"14px","bottom":"8px"}},"marginTop":"16px","typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"14px"},"color":{"background":"#7c3aed"}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:wpuf-ud/directory-item --></div>
<!-- /wp:wpuf-ud/directory -->

<!-- wp:wpuf-ud/profile {"block_instance_id":"b59d61da-fb6c-4cec-8e65-64e7c236a593","userId":1,"userObject":{"id":1,"user_login":"admin101","display_name":"John Doe","user_email":"mail@mail.com","user_url":"https://wpuf.test","bio":"Curabitur euismod neque vel accumsan aliquet. Donec quis massa ac sem congue ullamcorper. Morbi at sodales ligula. Sed laoreet in odio a cursus. Integer quis nisi sit amet tortor aliquam ornare luctus non orci. Pellentesque porttitor nunc sed augue luctus auctor. Phasellus feugiat quis dui sit amet lacinia. Nullam et venenatis ligula, id iaculis ligula. Maecenas cursus ex vitae neque elementum, convallis condimentum ex aliquet. Maecenas eu orci ac nunc interdum tristique. Nunc id odio massa. Praesent et diam nec ipsum congue lobortis. Aliquam erat volutpat.","avatar":"https://secure.gravatar.com/avatar/74a43f5a2491b706609180d3059d0b4269b25d859801497ec0d248fe75f37ac4?s=96\u0026d=mm\u0026r=g","first_name":"John","last_name":"Doe","nickname":"user nickname","user_registered":"2025-08-19 09:33:34","roles":["administrator"],"class_list":"","username":"admin101","name":"John Doe"},"canEdit":"1","hasSelectedPattern":true} -->
<div class="wp-block-wpuf-ud-profile wpuf-user-profile"><!-- wp:columns {"className":"wpuf-flex wpuf-flex-row wpuf-gap-8 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-8"} -->
<div class="wp-block-columns wpuf-flex wpuf-flex-row wpuf-gap-8 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-8"><!-- wp:column {"width":"35%","className":"wpuf-profile-sidebar","style":{"border":{"style":"none","width":"0px"},"spacing":{"padding":{"right":"var:preset|spacing|40","top":"0","bottom":"0","left":"0"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"75%"}} -->
<div class="wp-block-column wpuf-profile-sidebar" style="border-style:none;border-width:0px;padding-top:0;padding-right:var(--wp--preset--spacing--40);padding-bottom:0;padding-left:0;flex-basis:35%"><!-- wp:wpuf-ud/avatar {"avatarSize":"custom","fallbackType":"gravatar","customSize":100,"style":{"spacing":{"margin":{"bottom":"10px"}}}} /-->

<!-- wp:wpuf-ud/name {"nameFormat":"first_last","headingLevel":"h2","fontFamily":"manrope","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} /-->

<!-- wp:wpuf-ud/contact {"showFields":["email","website"],"layoutStyle":"vertical","showLabels":false,"iconColor":"#707070","style":{"typography":{"fontSize":"14px","lineHeight":"1.7"},"spacing":{"margin":{"bottom":"20px","top":"var:preset|spacing|20"}}}} /-->

<!-- wp:heading {"level":4,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","right":"0"}},"color":{"text":"#707070"},"elements":{"link":{"color":{"text":"#707070"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"fontSize":"small"} -->
<h4 class="wp-block-heading has-text-color has-link-color has-small-font-size" style="color:#707070;margin-top:var(--wp--preset--spacing--40);margin-right:0;font-style:normal;font-weight:700">SOCIAL</h4>
<!-- /wp:heading -->

<!-- wp:wpuf-ud/social {"layoutStyle":"layout-2","style":{"spacing":{"margin":{"right":"0","top":"var:preset|spacing|20"},"padding":{"right":"var:preset|spacing|20","left":"0","top":"0"}}}} /-->

<!-- wp:heading {"level":4,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}},"color":{"text":"#707070"},"elements":{"link":{"color":{"text":"#707070"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"fontSize":"small"} -->
<h4 class="wp-block-heading has-text-color has-link-color has-small-font-size" style="color:#707070;margin-top:var(--wp--preset--spacing--40);font-style:normal;font-weight:700">BIO</h4>
<!-- /wp:heading -->

<!-- wp:wpuf-ud/bio {"characterLimit":100,"style":{"spacing":{"margin":{"top":"var:preset|spacing|20"},"padding":{"right":"0"}},"typography":{"fontSize":"14px"}}} /-->

<!-- wp:wpuf-ud/unmatched-blocks /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"65%","className":"wpuf-profile-content","layout":{"type":"default"}} -->
<div class="wp-block-column wpuf-profile-content" style="flex-basis:65%"><!-- wp:wpuf-ud/tabs {"style":{"spacing":{"margin":{"top":"100px"}}}} -->
<div class="wpuf-user-tabs" data-about-content="[]"><!-- wp:heading {"level":4,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}},"color":{"text":"#707070"},"elements":{"link":{"color":{"text":"#707070"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"fontSize":"small"} -->
<h4 class="wp-block-heading has-text-color has-link-color has-small-font-size" style="color:#707070;margin-top:var(--wp--preset--spacing--40);font-style:normal;font-weight:700">BIO</h4>
<!-- /wp:heading -->

<!-- wp:wpuf-ud/bio {"characterLimit":100,"style":{"spacing":{"margin":{"top":"var:preset|spacing|20"},"padding":{"right":"0"}},"typography":{"fontSize":"14px"}}} /--></div>
<!-- /wp:wpuf-ud/tabs --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:wpuf-ud/profile -->
HTML;
    }

    /**
     * Create a page with title and content
     *
     * @param string $page_title
     * @param string $post_content
     *
     * @return false|int
     */
    public function create_page( $page_title, $post_content = '', $post_type = 'page' ) {
        $page_id = wp_insert_post( [
                                       'post_title'     => $page_title,
                                       'post_type'      => $post_type,
                                       'post_status'    => 'publish',
                                       'comment_status' => 'closed',
                                       'post_content'   => $post_content,
                                   ] );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            return $page_id;
        }

        return false;
    }

    /**
     * Create a basic registration form by default
     *
     * @return int|bool
     */
    public function create_reg_form() {
        return wpuf_create_sample_form( __( 'Registration', 'wp-user-frontend' ), 'wpuf_profile' );
    }

    /**
     * Create a post form
     *
     * @return void
     */
    public function create_form() {
        return wpuf_create_sample_form( __( 'Sample Form', 'wp-user-frontend' ), 'wpuf_forms' );
    }
}
