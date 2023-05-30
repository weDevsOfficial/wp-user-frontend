<?php

namespace Wp\User\Frontend\Admin;

use Wp\User\Frontend\Free\WPUF_Pro_Prompt;
use Wp\User\Frontend\Lib\WeDevs_Settings_API;

class Menu {
    private $all_submenu_hooks = [];

    public $parent_slug = 'wp-user-frontend';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );

        add_filter( 'parent_file', [ $this, 'fix_parent_menu' ] );
        add_filter( 'submenu_file', [ $this, 'fix_submenu_file' ] );
    }

    public function admin_menu() {
        global $_registered_pages;

        $capability = wpuf_admin_role();
        $wpuf_icon  = 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' );

        add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', 'wp-user-frontend' ), $capability, $this->parent_slug, [ $this, '\wpuf_post_forms_page' ], $wpuf_icon, '54.2' );
        $post_forms_hook = add_submenu_page(
            $this->parent_slug,
            __( 'Post Forms', 'wp-user-frontend' ),
            __( 'Post Forms', 'wp-user-frontend' ),
            $capability,
            'wpuf-post-forms',
            [ $this, 'wpuf_post_forms_page' ]
        );
        $this->all_submenu_hooks['post_forms'] = $post_forms_hook;

        // remove the toplevel menu item
        remove_submenu_page( 'wp-user-frontend', 'wp-user-frontend' );

        /*
         * @since 2.3
         */
        do_action( 'wpuf_admin_menu_top' );

        if ( 'on' === wpuf_get_option( 'enable_payment', 'Wp\User\Frontend\WPUF_Payment', 'on' ) ) {
            $subscription_hook = add_submenu_page( $this->parent_slug, __( 'Subscriptions', 'wp-user-frontend' ), __( 'Subscriptions', 'wp-user-frontend' ), $capability, 'edit.php?post_type=wpuf_subscription' );

            $this->all_submenu_hooks['subscription_hook'] = 'load-' . $subscription_hook;

            $coupons_hook = add_submenu_page( $this->parent_slug, __( 'Coupons', 'wp-user-frontend' ), __( 'Coupons', 'wp-user-frontend' ), $capability, 'wpuf_coupon', [ $this, 'admin_coupon_page' ] );

            $this->all_submenu_hooks['coupons_hook'] = 'load-' . $coupons_hook;

            $transactions_page = add_submenu_page( $this->parent_slug, __( 'Transactions', 'wp-user-frontend' ), __( 'Transactions', 'wp-user-frontend' ), $capability, 'wpuf_transaction', [ $this, 'transactions_page' ] );

            add_action( 'load-' . $transactions_page, [ $this, 'transactions_screen_option' ] );
        }

        $tools_hook = add_submenu_page( $this->parent_slug, __( 'Tools', 'wp-user-frontend' ), __( 'Tools', 'wp-user-frontend' ), $capability, 'wpuf_tools', [ $this, 'tools_page' ] );
        $this->all_submenu_hooks['tools'] = 'load-' . $tools_hook;

        add_action( 'load-' . $tools_hook, [ $this, 'enqueue_tools_script' ] );

        do_action( 'wpuf_admin_menu' );

        add_action( 'load-' . $post_forms_hook, [ $this, 'post_form_menu_action' ] );

        do_action( 'wpuf_admin_menu_bottom' );

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $premium_hook = add_submenu_page( 'wp-user-frontend', __( 'Premium', 'wp-user-frontend' ), __( 'Premium', 'wp-user-frontend' ), $capability, 'wpuf_premium', [ $this, 'premium_page' ] );

            $this->all_submenu_hooks['premium'] = 'load-' . $premium_hook;
        }

        $help_hook = add_submenu_page( 'wp-user-frontend', __( 'Help', 'wp-user-frontend' ), sprintf( '<span style="color:#f18500">%s</span>', __( 'Help', 'wp-user-frontend' ) ), $capability, 'wpuf-support', [ $this, 'support_page' ] );
        $this->all_submenu_hooks['help'] = 'load-' . $help_hook;

        add_action( 'load-' . $help_hook, [ $this, 'enqueue_help_script' ] );

        $subscribers_page_hook = add_submenu_page( 'edit.php?post_type=wpuf_subscription', __( 'Subscribers', 'wp-user-frontend' ), __( 'Subscribers', 'wp-user-frontend' ), $capability, 'wpuf_subscribers', [ $this, 'subscribers_page' ] );
        //phpcs:ignore
        $_registered_pages['user-frontend_page_wpuf_subscribers'] = true; // hack to work the nested subscribers page. WPUF > Subscriptions > Subscribers

        $this->all_submenu_hooks['subscribers_hook'] = 'load-' . $subscribers_page_hook;

        $settings_page_hook = add_submenu_page( 'wp-user-frontend', __( 'Settings', 'wp-user-frontend' ), __( 'Settings', 'wp-user-frontend' ), $capability, 'wpuf-settings', [ $this, 'plugin_settings_page' ] );

        $this->all_submenu_hooks['settings_hook'] = 'load-' . $settings_page_hook;

        add_action( 'load-' . $settings_page_hook, [ $this, 'enqueue_settings_page_scripts' ] );
    }

    /**
     * The content of the Post Form page.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
        // phpcs:ignore WordPress.Security.NonceVerification
        $action           = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' );

        switch ( $action ) {
            case 'edit':
            case 'add-new':
                require_once WPUF_INCLUDES . '/Admin/views/post-form.php';
                break;

            default:
                require_once WPUF_INCLUDES . '/Admin/views/post-forms-list-table-view.php';
                break;
        }
    }

    /**
     * The action to run just after the menu is created.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function post_form_menu_action() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_post_forms' );
    }

    /**
     * Screen options.
     *
     * @return void
     */
    public function transactions_screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => __( 'Number of items per page:', 'wp-user-frontend' ),
            'default' => 20,
            'option'  => 'transactions_per_page',
        ];

        add_screen_option( $option, $args );

        if ( ! class_exists( 'Wp\User\Frontend\Admin\WPUF_Transactions_List_Table' ) ) {
            require_once WPUF_ROOT . '/class/transactions-list-table.php';
        }

        $this->transactions_list_table_obj = new WPUF_Transactions_List_Table();
    }

    public function transactions_page() {
        require_once WPUF_INCLUDES . '/Admin/views/transactions-list-table-view.php';
    }

    /**
     * The subscribers page content
     *
     * @param $post_ID
     *
     * @return void
     */
    public function subscribers_page( $post_ID ) {
        include dirname( WPUF_INCLUDES ) . '/admin/subscribers.php';
    }

    public function get_all_submenu_hooks() {
        return $this->all_submenu_hooks;
    }

    public function add_submenu_hooks( $key, $hook ) {
        $this->all_submenu_hooks[ $key ] = $hook;
    }

    /**
     * Highlight the proper top level menu
     *
     * @see http://wordpress.org/support/topic/moving-taxonomy-ui-to-another-main-menu?replies=5#post-2432769
     *
     * @global $current_screen
     *
     * @param string $parent_file
     *
     * @return string
     */
    public function fix_parent_menu( $parent_file ) {
        $current_screen = get_current_screen();

        $post_types = [ 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon' ];

        if ( in_array( $current_screen->post_type, $post_types, true ) ) {
            $parent_file = 'wp-user-frontend';
        }

        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_the-slug' ) {
            $parent_file = 'wp-user-frontend';
        }

        return $parent_file;
    }

    /**
     * Fix the submenu class in admin menu
     *
     * @since 2.6.0
     *
     * @param string $submenu_file
     *
     * @return string
     */
    public function fix_submenu_file( $submenu_file ) {
        $current_screen = get_current_screen();

        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_wpuf_subscribers' ) {
            $submenu_file = 'edit.php?post_type=wpuf_subscription';
        }

        return $submenu_file;
    }

    public function admin_coupon_page() {
        ?>
        <h2><?php esc_html_e( 'Coupons', 'wp-user-frontend' ); ?></h2>

        <div class="wpuf-notice" style="padding: 20px; background: #fff; border: 1px solid #ddd;">
            <p>
                <?php esc_html_e( 'Use Coupon codes for subscription for discounts.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <?php esc_html_e( 'This feature is only available in the Pro Version.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <a href="<?php echo esc_url( WPUF_Pro_Prompt::get_pro_url() ); ?>" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                <a href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/coupons/" target="_blank" class="button"><?php esc_html_e( 'Learn more about Coupons', 'wp-user-frontend' ); ?></a>
            </p>
        </div>

        <?php
    }

    /**
     * Enqueue scripts required for tools page
     *
     * @return void
     */
    public function enqueue_tools_script() {
        wp_enqueue_media(); // for uploading JSON

        wp_enqueue_script( 'wpuf-vue' );
        wp_enqueue_script( 'wpuf-admin-tools' );

        wp_localize_script(
            'wpuf-admin-tools',
            'wpuf_admin_tools',
            [
                'url'   => [
                    'ajax' => admin_url( 'admin-ajax.php' ),
                ],
                'nonce' => wp_create_nonce( 'wpuf_admin_tools' ),
                'i18n'  => [
                    'wpuf_import_forms'      => __( 'WPUF Import Forms', 'wp-user-frontend' ),
                    'add_json_file'          => __( 'Add JSON file', 'wp-user-frontend' ),
                    'could_not_import_forms' => __( 'Could not import forms.', 'wp-user-frontend' ),
                ],
            ]
        );
    }

    /**
     * The User Frontend > Tools page content
     *
     * @return void
     */
    public function tools_page() {
        include WPUF_INCLUDES . '/Admin/views/tools.php';
    }

    /**
     * Load necessary scripts for User Frontend > Settings page
     *
     * @return void
     */
    public function enqueue_settings_page_scripts() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-subscriptions' );
    }

    /**
     * The User Frontend > Settings page content
     *
     * @return void
     */
    public function plugin_settings_page() {
        ?>
        <div class="wrap">

            <h2 style="margin-bottom: 15px;"><?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?></h2>
            <div class="wpuf-settings-wrap">
                <?php
                settings_errors();

                wpuf()->settings->get_settings_api()->show_navigation();
                wpuf()->settings->get_settings_api()->show_forms();
                ?>
            </div>
            <script>
                (function () {
                    document.addEventListener('DOMContentLoaded',function () {
                        var tabs    = document.querySelector('.wpuf-settings-wrap').querySelectorAll('h2 a');
                        var content = document.querySelectorAll('.wpuf-settings-wrap .metabox-holder th');
                        var close   = document.querySelector('#wpuf-search-section span');

                        var search_input = document.querySelector('#wpuf-settings-search');

                        search_input.addEventListener('keyup', function (e) {
                            var search_value = e.target.value.toLowerCase();
                            var value_tab  = [];

                            if ( search_value.length ) {
                                close.style.display = 'flex'
                                content.forEach(function (row, index) {

                                    var content_id = row.closest('div').getAttribute('id');
                                    var tab_id     = content_id + '-tab';
                                    var found_value = row.innerText.toLowerCase().includes( search_value );

                                    if ( found_value ){
                                        row.closest('tr').style.display = 'table-row';
                                    }else {
                                        row.closest('tr').style.display = 'none';
                                    }

                                    if ( 'wpuf_mails' === content_id ){
                                        row.closest('tbody').querySelectorAll('tr').forEach(function (tr) {
                                            tr.style.display = '';
                                        });
                                    }

                                    if ( found_value === true && ! value_tab.includes( tab_id ) ) {
                                        value_tab.push(tab_id);
                                    }
                                })

                                if ( value_tab.length ) {
                                    document.getElementById(value_tab[0]).click();
                                }

                                tabs.forEach(function (tab) {
                                    var tab_id = tab.getAttribute('id');
                                    if ( ! value_tab.includes( tab_id ) ){
                                        document.getElementById(tab_id).style.display = 'none';
                                    }else {
                                        document.getElementById(tab_id).style.display = 'block';
                                    }
                                })

                            }else {
                                wpuf_search_reset();
                            }
                        })

                        close.addEventListener('click',function (event) {
                            wpuf_search_reset();
                            search_input.value = '';
                            close.style.display = 'none';
                        })

                        function wpuf_search_reset() {
                            content.forEach(function (row, index) {
                                var content_id = row.closest('div').getAttribute('id');
                                var tab_id     = content_id + '-tab';
                                document.getElementById(content_id).style.display = '';
                                document.getElementById(tab_id).style.display = '';
                                document.getElementById('wpuf_general-tab').click();
                            })
                            document.querySelector('.wpuf-settings-wrap .metabox-holder').querySelectorAll('tr').forEach(function (row) {
                                row.style.display = '';
                            });

                        }
                    });
                })();
            </script>
        </div>
        <?php
    }

    /**
     * The User Frontend > Premium page content
     *
     * @return void
     */
    public function premium_page() {
        require_once WPUF_INCLUDES . '/Admin/views/premium.php';
    }

    /**
     * The User Frontend > Support page content
     *
     * @return void
     */
    public function support_page() {
        require_once WPUF_INCLUDES . '/Admin/views/support.php';
    }

    /**
     * The User Frontend > Help page scripts
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_help_script() {
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-admin' );
    }
}