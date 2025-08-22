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

        if ( class_exists( 'WPUF_User_Listing' ) ) {
            $this->create_page( __( 'User Directory', 'wp-user-frontend' ), $this->get_user_directory_page_content() );
        }

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
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        if ( $page != 'wpuf-setup' ) {
            wp_redirect( admin_url( 'admin.php?page=wpuf-settings&wpuf_page_installed=1' ) );
            exit;
        }
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

    /**
     * Get user directory page content
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function get_user_directory_page_content() {
        return '<!-- wp:wpuf-ud/directory {"directory_layout":"roundGrids","hasSelectedLayout":true,"selectedLayout":"roundGrids"} -->
<div class="wp-block-wpuf-ud-directory"><!-- wp:wpuf-ud/directory-item -->
<div class="wp-block-wpuf-ud-directory-item"><!-- wp:group {"className":"is-style-default","style":{"border":{"radius":"8px","color":"#d1d5db","width":"1px"},"spacing":{"margin":{"top":"0","bottom":"0"},"blockGap":"0","padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"0","right":"0"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group is-style-default has-border-color" style="border-color:#d1d5db;border-width:1px;border-radius:8px;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><!-- wp:wpuf-ud/avatar {"avatarSize":"custom","fallbackType":"gravatar","customSize":128} /-->

<!-- wp:wpuf-ud/name {"textAlign":"center","style":{"color":"#0F172A","fontWeight":"bold","typography":{"fontWeight":"600","fontSize":"20px","lineHeight":"2"}}} /-->

<!-- wp:wpuf-ud/contact {"showIcons":false,"iconSize":"small","showLabels":false,"className":"wpuf-user-contact-info wpuf-contact-layout-inline"} /-->

<!-- wp:wpuf-ud/social {"iconSize":"medium"} -->
<div class="wp-block-wpuf-ud-social"><div class="wpuf-social-fields"></div></div>
<!-- /wp:wpuf-ud/social -->

<!-- wp:wpuf-ud/button {"textColor":"base","fontSize":"medium","style":{"color":{"background":"#7c3aed"},"border":{"radius":"6px"}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:wpuf-ud/directory-item --></div>
<!-- /wp:wpuf-ud/directory -->

<!-- wp:wpuf-ud/profile {"block_instance_id":"e111db80-9c50-4642-aaa7-b56a8ebc54b1","userId":1,"userObject":{"id":1,"user_login":"admin101","display_name":"admin101","user_email":"","user_url":"https://wpuf.test","bio":"","avatar":"","first_name":"","last_name":"","nickname":"","name":"admin101","url":"https://wpuf.test","description":"","link":"https://wpuf.test/author/admin101/","slug":"admin101","avatar_urls":{"24":"https://secure.gravatar.com/avatar/74a43f5a2491b706609180d3059d0b4269b25d859801497ec0d248fe75f37ac4?s=24\u0026d=mm\u0026r=g","48":"https://secure.gravatar.com/avatar/74a43f5a2491b706609180d3059d0b4269b25d859801497ec0d248fe75f37ac4?s=48\u0026d=mm\u0026r=g","96":"https://secure.gravatar.com/avatar/74a43f5a2491b706609180d3059d0b4269b25d859801497ec0d248fe75f37ac4?s=96\u0026d=mm\u0026r=g"},"meta":[],"_links":{"self":[{"href":"https://wpuf.test/wp-json/wp/v2/users/1","targetHints":{"allow":["GET","POST","PUT","PATCH","DELETE"]}}],"collection":[{"href":"https://wpuf.test/wp-json/wp/v2/users"}]}},"canEdit":"1","hasSelectedPattern":true} -->
<div class="wp-block-wpuf-ud-profile wpuf-user-profile"><!-- wp:columns {"className":"wpuf-flex wpuf-flex-row wpuf-gap-8 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-8"} -->
<div class="wp-block-columns wpuf-flex wpuf-flex-row wpuf-gap-8 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-8"><!-- wp:column {"width":"33%","className":"wpuf-profile-sidebar","style":{"border":{"width":"0 1px 0 0","style":"solid","color":"#E5E7EB"}}} -->
<div class="wp-block-column wpuf-profile-sidebar has-border-color" style="border-color:#E5E7EB;border-style:solid;border-width:0 1px 0 0;flex-basis:33%"><!-- wp:wpuf-ud/avatar {"avatarSize":"custom","customSize":100} /-->

<!-- wp:wpuf-ud/name {"headingLevel":"h2","showRole":true} /-->

<!-- wp:wpuf-ud/contact {"showFields":["display_name","user_email","user_url"],"layoutStyle":"vertical","showLabels":false,"style":{"spacing":{"margin":{"top":"1rem","bottom":"1rem"}}}} /-->

<!-- wp:group {"className":"wpuf-mt-8","style":{"spacing":{"margin":{"top":"2rem"}}}} -->
<div class="wp-block-group wpuf-mt-8" style="margin-top:2rem"><!-- wp:heading {"level":4,"style":{"spacing":{"margin":{"top":"2rem"}}}} -->
<h4 class="wp-block-heading" style="margin-top:2rem">Bio</h4>
<!-- /wp:heading -->

<!-- wp:wpuf-ud/bio {"characterLimit":100,"style":{"spacing":{"margin":{"top":".75rem"}}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"67%","className":"wpuf-profile-content"} -->
<div class="wp-block-column wpuf-profile-content" style="flex-basis:67%"><!-- wp:wpuf-ud/tabs -->
<div class="wpuf-user-tabs" data-about-content="[]"></div>
<!-- /wp:wpuf-ud/tabs --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:wpuf-ud/profile -->';
    }
}
