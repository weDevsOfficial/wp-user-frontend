<?php

namespace Wp\User\Frontend\Admin;

class Menu {
    private $all_submenu_hooks = [];

    public $parent_slug = 'wp-user-frontend';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    public function admin_menu() {
        $capability = wpuf_admin_role();
        $wpuf_icon  = 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' );

        add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', 'wp-user-frontend' ), $capability, $this->parent_slug, [ $this, 'wpuf_post_forms_page' ], $wpuf_icon, '54.2' );
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

        add_action( "load-$post_forms_hook", [ $this, 'post_form_menu_action' ] );
    }

    /**
     * The content of the Post Form page.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
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

    public function get_all_submenu_hooks() {
        return $this->all_submenu_hooks;
    }

    public function add_submenu_hooks( $key, $hook ) {
        $this->all_submenu_hooks[ $key ] = $hook;
    }
}
