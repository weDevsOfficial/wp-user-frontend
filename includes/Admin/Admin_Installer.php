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
        if ( '1' !== $page_created && 'off' === wpuf_get_option( 'install_wpuf_pages', 'wpuf_general', 'on' ) ) {
            ?>
            <div class="updated error">
                <p>
                    <?php
                    esc_html_e(
                        'If you have not created <strong>WP User Frontend</strong> pages yet, you can do this by one click.',
                        'wp-user-frontend'
                    );
					?>
                </p>
                <p class="submit">
                    <a class="button button-primary"
                        href="
                        <?php
						echo esc_url(
                            wp_nonce_url(
                                add_query_arg(
                                    [ 'install_wpuf_pages' => '1' ],
                                    admin_url( 'admin.php?page=wpuf-settings' )
                                ),
                                'wpuf_install_pages'
                            )
                        );
						?>
                                                                ">
                                                                <?php
																esc_html_e(
                                                                    'Install WPUF Pages',
                                                                    'wp-user-frontend'
																);
																?>
                                                                                                                                        </a>
                    <?php esc_html_e( 'or', 'wp-user-frontend' ); ?>
                    <a class="button"
                        href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_hide_page_nag' => '1' ] ), 'wpuf_install_pages' ) ); ?>">
                                        <?php
											esc_html_e(
                                                'Skip Setup',
                                                'wp-user-frontend'
                                            );
										?>
                                                                                                                            </a>
                </p>
            </div>
            <?php
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpuf_page_installed'] ) && '1' === $_GET['wpuf_page_installed'] ) {
            ?>
            <div class="updated">
                <p>
                    <strong>
                        <?php
                        esc_html_e(
                            'Congratulations!',
                            'wp-user-frontend'
                        );
                        ?>
                    </strong>
                    <?php
                        echo wp_kses_post(
                            'Pages for <strong>WP User Frontend</strong> has been successfully installed and saved!',
                            'wp-user-frontend'
                        );
                    ?>
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
        if ( ! isset( $_GET['install_wpuf_pages'] ) && ! isset( $_GET['wpuf_hide_page_nag'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wpuf_install_pages' ) ) {
            return;
        }

        if ( isset( $_GET['install_wpuf_pages'] ) && '1' === $_GET['install_wpuf_pages'] ) {
            $this->init_pages();
        }

        if ( isset( $_GET['wpuf_hide_page_nag'] ) && '1' === $_GET['wpuf_hide_page_nag'] ) {
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
        $frontend_posting = $this->get_option_array( 'wpuf_frontend_posting' );
        $profile_options  = $this->get_option_array( 'wpuf_profile' );
        $payment_options  = $this->get_option_array( 'wpuf_payment' );

        // create a dashboard page
        $dashboard_page = $this->get_or_create_page( __( 'Dashboard', 'wp-user-frontend' ), '[wpuf_dashboard]', 0, '[wpuf_dashboard]' );
        $account_page   = $this->get_or_create_page( __( 'Account', 'wp-user-frontend' ), '[wpuf_account]', 0, '[wpuf_account]' );
        $edit_page      = $this->get_or_create_page(
            __( 'Edit', 'wp-user-frontend' ),
            '[wpuf_edit]',
            isset( $frontend_posting['edit_page_id'] ) ? absint( $frontend_posting['edit_page_id'] ) : 0,
            '[wpuf_edit]'
        );
        // login page
        $login_page = $this->get_or_create_page(
            __( 'Login', 'wp-user-frontend' ),
            '[wpuf-login]',
            isset( $profile_options['login_page'] ) ? absint( $profile_options['login_page'] ) : 0,
            '[wpuf-login]'
        );

        // Keep the form the site already posts with; only build a sample when there is none.
        $post_form = isset( $frontend_posting['default_post_form'] ) ? absint( $frontend_posting['default_post_form'] ) : 0;

        if ( ! $this->post_is_usable( $post_form, 'wpuf_forms' ) ) {
            $post_form = $this->create_form();
        }

        if ( 'on' === wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            // payment page
            $subscr_page  = $this->get_or_create_page(
                __( 'Subscription', 'wp-user-frontend' ),
                __( '[wpuf_sub_pack]', 'wp-user-frontend' ),
                isset( $payment_options['subscription_page'] ) ? absint( $payment_options['subscription_page'] ) : 0,
                '[wpuf_sub_pack]'
            );
            $payment_page = $this->get_or_create_page(
                __( 'Payment', 'wp-user-frontend' ),
                __( 'Please select a gateway for payment', 'wp-user-frontend' ),
                isset( $payment_options['payment_page'] ) ? absint( $payment_options['payment_page'] ) : 0
            );
            $thank_page   = $this->get_or_create_page(
                __( 'Thank You', 'wp-user-frontend' ),
                __(
                    '<h1>Payment is complete</h1><p>Congratulations, your payment has been completed!</p>',
                    'wp-user-frontend'
                ),
                isset( $payment_options['payment_success'] ) ? absint( $payment_options['payment_success'] ) : 0
            );
            $bank_page    = $this->get_or_create_page(
                __( 'Order Received', 'wp-user-frontend' ),
                __(
                    'Hi, we have received your order. We will validate the order and will take necessary steps to move forward.',
                    'wp-user-frontend'
                ),
                isset( $payment_options['bank_success'] ) ? absint( $payment_options['bank_success'] ) : 0
            );
        }

        // save the settings. Every option below is merged into what is already
        // stored, never replaced, so settings this installer does not own are kept.
        if ( $edit_page ) {
            $frontend_posting['edit_page_id']      = $edit_page;
            $frontend_posting['default_post_form'] = $post_form;

            update_option( 'wpuf_frontend_posting', $frontend_posting );
        }

        // profile pages
        $reg_page = false;

        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }

        $data = apply_filters( 'wpuf_pro_page_install', $profile_options );

        if ( is_array( $data ) ) {
            if ( isset( $data['profile_options'] ) && is_array( $data['profile_options'] ) ) {
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

        if ( 'on' === wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            // payment pages
            $payment_options['subscription_page'] = $subscr_page;
            $payment_options['payment_page']      = $payment_page;
            $payment_options['payment_success']   = $thank_page;
            $payment_options['bank_success']      = $bank_page;

            update_option( 'wpuf_payment', $payment_options );
        }

        update_option( '_wpuf_page_created', '1' );

        // User Directory page (Pro only): created when WPUF_User_Listing is available.
        if ( class_exists( 'WPUF_User_Listing' ) ) {
            $this->get_or_create_page(
                __( 'User Directory', 'wp-user-frontend' ),
                $this->get_user_directory_page_content(),
                0,
                '[wpuf_user_listing'
            );
        }

        // Auto-add logout link to the primary menu
        $this->auto_add_logout_to_menu();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        // The setup screens keep control of where the admin goes next.
        if ( ! in_array( $page, [ 'wpuf-setup', Onboarding::PAGE_SLUG ], true ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wpuf-settings&wpuf_page_installed=1' ) );

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
        $navigations = get_posts(
            [
				'post_type'      => 'wp_navigation',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
        );

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

            wp_update_post(
                [
					'ID'           => $navigation->ID,
					'post_content' => $new_content,
				]
            );

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

<!-- wp:wpuf-ud/profile {"hasSelectedPattern":true} -->
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
     * Create only the pages the first two wizard steps need
     *
     * The post form and registration steps both offer a page picker, and an empty
     * picker on a brand new site is a dead end: the admin can only choose "create a
     * new page" for every one of them. Creating this handful up front means those
     * steps open with something already selected.
     *
     * The rest, the dashboard, subscription and payment pages, are left for the
     * settings step, which calls init_pages(). That is idempotent, so it creates
     * only what is still missing and never a second copy of anything made here.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function init_essential_pages() {
        $frontend_posting = $this->get_option_array( 'wpuf_frontend_posting' );
        $profile_options  = $this->get_option_array( 'wpuf_profile' );
        $account_options  = $this->get_option_array( 'wpuf_my_account' );

        $login_page = $this->get_or_create_page(
            __( 'Login', 'wp-user-frontend' ),
            '[wpuf-login]',
            isset( $profile_options['login_page'] ) ? absint( $profile_options['login_page'] ) : 0,
            '[wpuf-login]'
        );

        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }

        $edit_page = $this->get_or_create_page(
            __( 'Edit', 'wp-user-frontend' ),
            '[wpuf_edit]',
            isset( $frontend_posting['edit_page_id'] ) ? absint( $frontend_posting['edit_page_id'] ) : 0,
            '[wpuf_edit]'
        );

        if ( $edit_page ) {
            $frontend_posting['edit_page_id'] = $edit_page;

            update_option( 'wpuf_frontend_posting', $frontend_posting );
        }

        $account_page = $this->get_or_create_page(
            __( 'Account', 'wp-user-frontend' ),
            '[wpuf_account]',
            isset( $account_options['account_page'] ) ? absint( $account_options['account_page'] ) : 0,
            '[wpuf_account]'
        );

        if ( $account_page ) {
            $account_options['account_page'] = $account_page;

            update_option( 'wpuf_my_account', $account_options );
        }

        // Registration is a Pro page, built by whatever answers this filter. Free
        // simply gets nothing back and carries on.
        $data = apply_filters( 'wpuf_pro_page_install', $profile_options );

        if ( is_array( $data ) && isset( $data['profile_options'] ) && is_array( $data['profile_options'] ) ) {
            $profile_options = $data['profile_options'];
        }

        if ( ! empty( $profile_options['reg_override_page'] ) ) {
            $profile_options['register_link_override'] = 'on';
        }

        update_option( 'wpuf_profile', $profile_options );
    }

    /**
     * Read an option that is expected to hold an array of settings
     *
     * Returns an empty array for an unset option, and for a corrupt one that
     * stored a scalar, so the caller can always merge into the result.
     *
     * @since WPUF_SINCE
     *
     * @param string $option_name
     *
     * @return array
     */
    protected function get_option_array( $option_name ) {
        $value = get_option( $option_name, [] );

        return is_array( $value ) ? $value : [];
    }

    /**
     * Whether a post id points at a post that can still be used
     *
     * A setting pointing at a trashed or deleted post is treated as unset, so
     * the caller replaces it rather than keeping a dead reference.
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id
     * @param string $post_type
     *
     * @return bool
     */
    protected function post_is_usable( $post_id, $post_type = 'page' ) {
        $post_id = absint( $post_id );

        if ( ! $post_id ) {
            return false;
        }

        $post = get_post( $post_id );

        return $post instanceof \WP_Post
            && $post_type === $post->post_type
            && ! in_array( $post->post_status, [ 'trash', 'auto-draft' ], true );
    }

    /**
     * The first page whose content already holds a marker
     *
     * Used so a re-run points an unset setting at the page the site is already
     * serving instead of publishing a second copy of it.
     *
     * @since WPUF_SINCE
     *
     * @param string $marker Shortcode or block opening tag to look for.
     *
     * @return int page id, 0 when no page holds it
     */
    protected function find_page_with_marker( $marker ) {
        if ( empty( $marker ) ) {
            return 0;
        }

        $pages = get_posts(
            [
                'post_type'        => 'page',
                'post_status'      => [ 'publish', 'draft', 'private' ],
                'posts_per_page'   => -1,
                'orderby'          => 'ID',
                'order'            => 'ASC',
                'fields'           => 'ids',
                'suppress_filters' => false,
            ]
        );

        foreach ( $pages as $page_id ) {
            $content = get_post_field( 'post_content', $page_id );

            if ( is_string( $content ) && false !== strpos( $content, $marker ) ) {
                return absint( $page_id );
            }
        }

        return 0;
    }

    /**
     * Reuse the page this setting already points at, or publish one
     *
     * Resolution order: the id the setting already holds, then any page whose
     * content carries the marker, then a fresh page. This is what keeps the
     * installer idempotent, so running it twice, or running it on a site that
     * has already been set up, does not leave two of every page behind.
     *
     * @since WPUF_SINCE
     *
     * @param string $page_title
     * @param string $post_content
     * @param int    $existing_id Page id currently stored in the setting, 0 when unset.
     * @param string $marker      Shortcode or block tag identifying the page, optional.
     *
     * @return false|int
     */
    public function get_or_create_page( $page_title, $post_content = '', $existing_id = 0, $marker = '' ) {
        if ( $this->post_is_usable( $existing_id ) ) {
            return absint( $existing_id );
        }

        $found = $this->find_page_with_marker( $marker );

        if ( $found ) {
            return $found;
        }

        return $this->create_page( $page_title, $post_content );
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
        $page_id = wp_insert_post(
            [
				'post_title'     => $page_title,
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'post_content'   => $post_content,
			]
        );
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
