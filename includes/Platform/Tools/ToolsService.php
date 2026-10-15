<?php
/**
 * Tools service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Tools;

use WeDevs\Wpuf\Admin\Admin_Installer;
use WeDevs\Wpuf\Admin\Admin_Tools;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\SubscriptionStore;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WP_Error;

/**
 * What User Frontend > Tools does, for the admin app's `#/tools` route
 * (Platform\REST\Controllers\ToolsController) and the old links Admin_Tools
 * still answers. Every write goes through the stores: forms, packs,
 * transactions and the settings sections.
 *
 * @since WPUF_SINCE
 */
class ToolsService {

    /**
     * Settings sections "Reset Settings" deletes.
     */
    const SETTINGS_SECTIONS = [ 'wpuf_general', 'wpuf_dashboard', 'wpuf_profile', 'wpuf_payment', '_wpuf_page_created' ];

    /**
     * Post statuses an import may set.
     */
    const IMPORT_STATUSES = [ 'publish', 'draft', 'pending' ];

    /**
     * The form store.
     *
     * @var FormStore
     */
    private $forms;

    /**
     * The subscription store.
     *
     * @var SubscriptionStore
     */
    private $subscriptions;

    /**
     * The transaction store.
     *
     * @var TransactionStore
     */
    private $transactions;

    /**
     * The settings store.
     *
     * @var SettingsStore
     */
    private $settings;

    /**
     * Constructor.
     *
     * @since WPUF_SINCE
     *
     * @param FormStore|null         $forms         The form store (container default)
     * @param SubscriptionStore|null $subscriptions The subscription store (container default)
     * @param TransactionStore|null  $transactions  The transaction store (container default)
     * @param SettingsStore|null     $settings      The settings store (container default)
     */
    public function __construct( $forms = null, $subscriptions = null, $transactions = null, $settings = null ) {
        $this->forms         = $forms instanceof FormStore ? $forms : Stores::forms();
        $this->subscriptions = $subscriptions instanceof SubscriptionStore ? $subscriptions : Stores::subscriptions();
        $this->transactions  = $transactions instanceof TransactionStore ? $transactions : Stores::transactions();
        $this->settings      = $settings instanceof SettingsStore ? $settings : Stores::settings();
    }

    /**
     * Post types the "Delete Forms" tools empty.
     */
    const DELETABLE = [ 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon' ];

    /**
     * Form post types that export and import.
     */
    const FORM_TYPES = [ 'wpuf_forms', 'wpuf_profile' ];

    /**
     * The largest import file read (bytes).
     */
    const MAX_IMPORT_BYTES = 10485760;

    /**
     * Install the plugin's pages (Tools > Page Installation).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function install_pages() {
        // init_pages() does not redirect inside a REST request.
        wpuf()->platform()->get( Admin_Installer::class )->init_pages();
    }

    /**
     * Delete the plugin settings (Tools > Reset Settings).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function reset_settings() {
        foreach ( self::SETTINGS_SECTIONS as $section ) {
            $this->settings->delete_section( $section );
        }
    }

    /**
     * Force-delete every post of a type (Tools > Delete Forms).
     *
     * @since WPUF_SINCE
     *
     * @param string $post_type One of self::DELETABLE
     *
     * @return int|WP_Error Posts deleted
     */
    public function delete_post_type( $post_type ) {
        if ( ! in_array( $post_type, self::DELETABLE, true ) ) {
            return new WP_Error( 'wpuf_tools_invalid_type', __( 'This content cannot be deleted from Tools.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        if ( 'wpuf_subscription' === $post_type ) {
            return $this->subscriptions->delete_all();
        }

        if ( in_array( $post_type, self::FORM_TYPES, true ) ) {
            return $this->forms->delete_all_of_type( $post_type );
        }

        // Types without a free store (coupons: Pro's).
        $ids = get_posts(
            [
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => [ 'publish', 'draft', 'pending', 'trash' ],
                'fields'         => 'ids',
            ]
        );

        foreach ( $ids as $id ) {
            wp_delete_post( $id, true );
        }

        return count( $ids );
    }

    /**
     * Empty the transactions table (Tools > Transactions).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function clear_transactions() {
        $this->transactions->truncate();
    }

    /**
     * The site's navigation menus.
     *
     * @since WPUF_SINCE
     *
     * @return array[] id, name
     */
    public function menus() {
        $menus = [];

        foreach ( (array) wp_get_nav_menus() as $menu ) {
            $menus[] = [
                'id'   => (int) $menu->term_id,
                'name' => $menu->name,
            ];
        }

        return $menus;
    }

    /**
     * Add a logout link to a menu (Tools > Add Logout to Menu).
     *
     * @since WPUF_SINCE
     *
     * @param int    $menu_id Menu term id
     * @param string $label   Link label
     *
     * @return int|WP_Error Menu item id
     */
    public function add_logout_to_menu( $menu_id, $label ) {
        $menu_id = absint( $menu_id );

        if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
            return new WP_Error( 'wpuf_tools_no_menu', __( 'Please select a menu.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        $result = wpuf_add_logout_to_menu( $menu_id, $label ? $label : __( 'Logout', 'wp-user-frontend' ) );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'wpuf_tools_logout_failed', $result->get_error_message(), [ 'status' => 400 ] );
        }

        return (int) $result;
    }

    /**
     * The Tools onboarding box: label, URL (decoded for navigation) and the
     * re-run warning (Platform\Onboarding\Wizard::entry_point()).
     *
     * @since WPUF_SINCE
     *
     * @return array url, label, warning
     */
    public function onboarding_entry() {
        $entry = wpuf()->platform()->get( \WeDevs\Wpuf\Platform\Onboarding\Wizard::class )->entry_point();

        $entry['url'] = html_entity_decode( $entry['url'], ENT_QUOTES, 'UTF-8' );

        return $entry;
    }

    /**
     * Published forms of a type, for the export picker.
     *
     * @since WPUF_SINCE
     *
     * @param string $post_type wpuf_forms or wpuf_profile
     *
     * @return array[] id, title
     */
    public function forms( $post_type ) {
        $forms = [];

        foreach ( $this->forms->query( [ 'post_type' => $post_type, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ] ) as $form ) {
            $forms[] = [
                'id'    => (int) $form->get_id(),
                'title' => $form->get_title() ? $form->get_title() : __( '(no title)', 'wp-user-frontend' ),
            ];
        }

        return $forms;
    }

    /**
     * The export file of forms (Tools > Export): the same JSON shape the
     * classic page downloads, for every published form of the type (the
     * classic page stopped at WordPress's default 10) or the picked ones.
     *
     * @since WPUF_SINCE
     *
     * @param string $post_type wpuf_forms or wpuf_profile
     * @param int[]  $ids       Picked forms (empty: all)
     *
     * @return array|WP_Error filename, forms
     */
    public function export( $post_type, $ids = [] ) {
        if ( ! in_array( $post_type, self::FORM_TYPES, true ) ) {
            return new WP_Error( 'wpuf_tools_invalid_type', __( 'Unknown form type.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        $forms = [];

        foreach ( $this->forms->query( [ 'post_type' => $post_type, 'status' => 'publish', 'include' => $ids ] ) as $form ) {
            $read = $this->forms->read( $form->get_id() );
            $data = get_object_vars( $read['post'] );
            unset( $data['ID'] );

            $forms[] = [
                'post_data' => $data,
                'meta_data' => [
                    'fields'        => $read['fields'],
                    'settings'      => $read['settings'],
                    'notifications' => $read['notifications'],
                ],
            ];
        }

        return [
            'filename' => strtolower( str_replace( ' ', '-', Stores::settings()->wp_option( 'blogname' ) ) ) . '-wpuf-' . $post_type . '-' . gmdate( 'Y-m-d' ) . '.json',
            'forms'    => $forms,
        ];
    }

    /**
     * Import forms from an uploaded JSON file (Tools > Import), with the
     * checks the classic upload makes (Admin_Tools::check_filetype_and_ext).
     * The file is read in place and never added to the Media Library.
     *
     * @since WPUF_SINCE
     *
     * @param array $file An entry of $_FILES (name, tmp_name, size, error)
     *
     * @return true|WP_Error
     */
    public function import( $file ) {
        $invalid = new WP_Error( 'wpuf_tools_invalid_file', __( 'Please upload a valid JSON export file.', 'wp-user-frontend' ), [ 'status' => 415 ] );

        if ( empty( $file['tmp_name'] ) || ! empty( $file['error'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
            return new WP_Error( 'wpuf_tools_no_file', __( 'Please choose a JSON file.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        $name = strtolower( (string) $file['name'] );

        if ( 'json' !== pathinfo( $name, PATHINFO_EXTENSION ) || preg_match( '/\.(php\d?|phtml|phar|exe|sh|bat|cmd)(\.|$)/', $name ) ) {
            return $invalid;
        }

        if ( (int) $file['size'] > self::MAX_IMPORT_BYTES ) {
            return new WP_Error( 'wpuf_tools_file_too_big', __( 'The file is too large.', 'wp-user-frontend' ), [ 'status' => 413 ] );
        }

        $content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $forms   = false === $content ? null : json_decode( $content, true );

        if ( ! is_array( $forms ) || preg_match( '/<\?php|<\?=|<script[^>]*>.*?<\/script>/is', (string) $content ) ) {
            return $invalid;
        }

        foreach ( $forms as $form ) {
            if ( ! is_array( $form ) || ! isset( $form['post_data'], $form['meta_data']['fields'] ) || ! is_array( $form['meta_data']['fields'] ) ) {
                return $invalid;
            }
        }

        $result = $this->import_forms( $forms );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'wpuf_tools_import_failed', $result->get_error_message(), [ 'status' => 422 ] );
        }

        return true;
    }

    /**
     * Create the forms of a decoded export file through the form store: post
     * type and status allowlisted (an imported file cannot set any other), the
     * fields, settings and notifications stored as exported.
     *
     * @since WPUF_SINCE
     *
     * @param array $forms Decoded export: `[ [ 'post_data' => [...], 'meta_data' => [ 'fields', 'settings', 'notifications' ] ], ... ]`
     *
     * @return true|WP_Error Every creation error collected
     */
    public function import_forms( array $forms ) {
        $errors = new WP_Error();

        foreach ( $forms as $form ) {
            $post = isset( $form['post_data'] ) && is_array( $form['post_data'] ) ? $form['post_data'] : [];
            $meta = isset( $form['meta_data'] ) && is_array( $form['meta_data'] ) ? $form['meta_data'] : [];

            $form_id = $this->forms->create(
                [
                    'post_title'           => isset( $post['post_title'] ) ? $post['post_title'] : '',
                    'post_status'          => isset( $post['post_status'] ) && in_array( $post['post_status'], self::IMPORT_STATUSES, true ) ? $post['post_status'] : 'publish',
                    'post_type'            => isset( $post['post_type'] ) && in_array( $post['post_type'], self::FORM_TYPES, true ) ? $post['post_type'] : 'wpuf_forms',
                    'ping_status'          => isset( $post['ping_status'] ) ? $post['ping_status'] : '',
                    'comment_status'       => isset( $post['comment_status'] ) ? $post['comment_status'] : '',
                    'fields'               => isset( $meta['fields'] ) && is_array( $meta['fields'] ) ? $meta['fields'] : [],
                    'settings'             => isset( $meta['settings'] ) ? $meta['settings'] : null,
                    'store_empty_settings' => true,
                    'notifications'        => isset( $meta['notifications'] ) ? $meta['notifications'] : null,
                    'version'              => false,
                ]
            );

            if ( is_wp_error( $form_id ) ) {
                $errors->add( $form_id->get_error_code(), $form_id->get_error_message() );
            }
        }

        return $errors->has_errors() ? $errors : true;
    }

    /**
     * The shortcode reference (Tools > Shortcodes), `wpuf_tools_shortcodes_list` applied.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function shortcodes() {
        $admin = function_exists( 'wpuf' ) ? wpuf()->admin : null;

        // REST requests do not load Admin; skip the constructor so the classic
        // page's upload filters are not added here.
        $tools = is_object( $admin ) && $admin->tools instanceof Admin_Tools
            ? $admin->tools
            : ( new \ReflectionClass( Admin_Tools::class ) )->newInstanceWithoutConstructor();

        return array_values( $tools->get_all_shortcodes() );
    }
}
