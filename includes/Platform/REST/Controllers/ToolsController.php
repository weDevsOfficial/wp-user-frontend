<?php
/**
 * Tools REST controller
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Tools\ToolsService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/tools/*`: User Frontend > Tools for the admin app. Same
 * capabilities as the classic page: the site-wide tools (pages, reset,
 * delete, transactions, logout menu) need `manage_options`; export, import
 * and the shortcode list need the WPUF admin role.
 *
 * @since WPUF_SINCE
 */
class ToolsController extends RestController {

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'admin/tools';

    /**
     * Resource name in error codes.
     *
     * @var string
     */
    protected $resource = 'tools';

    /**
     * The tools.
     *
     * @var ToolsService
     */
    private $tools;

    /**
     * Constructor.
     *
     * @since WPUF_SINCE
     *
     * @param ToolsService|null $tools The tools
     */
    public function __construct( $tools = null ) {
        $this->tools = $tools ? $tools : new ToolsService();
    }

    /**
     * Register the routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        $base  = '/' . $this->rest_base;
        $forms = $this->permission( Caps::MANAGE_FORMS );
        $site  = [ $this, 'can_manage_site' ];
        $type  = [
            'type'     => 'string',
            'required' => true,
            'enum'     => ToolsService::FORM_TYPES,
        ];

        $this->route( $base . '/install-pages', WP_REST_Server::CREATABLE, 'install_pages', $site );
        $this->route( $base . '/reset-settings', WP_REST_Server::CREATABLE, 'reset_settings', $site );
        $this->route( $base . '/clear-transactions', WP_REST_Server::CREATABLE, 'clear_transactions', $site );
        $this->route(
            $base . '/delete-forms',
            WP_REST_Server::CREATABLE,
            'delete_forms',
            $site,
            [
                'type' => [
                    'type'     => 'string',
                    'required' => true,
                    'enum'     => ToolsService::DELETABLE,
                ],
            ]
        );
        $this->route(
            $base . '/logout-menu',
            WP_REST_Server::CREATABLE,
            'add_logout_menu',
            $site,
            [
                'menu_id' => [
                    'type'     => 'integer',
                    'required' => true,
                    'minimum'  => 1,
                ],
                'label'   => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ]
        );
        $this->route( $base . '/forms', WP_REST_Server::READABLE, 'get_forms', $forms, [ 'type' => $type ] );
        $this->route(
            $base . '/export',
            WP_REST_Server::READABLE,
            'export',
            $forms,
            [
                'type' => $type,
                'ids'  => [
                    'type'    => 'array',
                    'items'   => [ 'type' => 'integer' ],
                    'default' => [],
                ],
            ]
        );
        // Develop let only `manage_options` upload the JSON file (Admin_Tools::enable_json_upload()),
        // and an import carries form settings such as a registration role.
        $this->route( $base . '/import', WP_REST_Server::CREATABLE, 'import', $site );
        $this->route( $base . '/shortcodes', WP_REST_Server::READABLE, 'get_shortcodes', $forms );
        $this->route( $base . '/onboarding', WP_REST_Server::READABLE, 'get_onboarding', $site );
    }


    /**
     * Who may run the site-wide tools: as the classic page's handlers.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_manage_site() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! Caps::can( Caps::MANAGE_SITE ) ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Install the plugin pages.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function install_pages() {
        $this->tools->install_pages();

        return rest_ensure_response( [ 'message' => __( 'The plugin pages are installed.', 'wp-user-frontend' ) ] );
    }

    /**
     * Delete the plugin settings.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function reset_settings() {
        $this->tools->reset_settings();

        return rest_ensure_response( [ 'message' => __( 'Settings has been cleared!', 'wp-user-frontend' ) ] );
    }

    /**
     * Empty the transactions table.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function clear_transactions() {
        $this->tools->clear_transactions();

        return rest_ensure_response( [ 'message' => __( 'All transactions has been deleted!', 'wp-user-frontend' ) ] );
    }

    /**
     * Delete every post of a type.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function delete_forms( $request ) {
        $deleted = $this->tools->delete_post_type( $request['type'] );

        if ( is_wp_error( $deleted ) ) {
            return $deleted;
        }

        return rest_ensure_response(
            [
                'deleted' => $deleted,
                'message' => sprintf(
                    /* translators: %d: number of items deleted */
                    _n( '%d item has been deleted.', '%d items have been deleted.', $deleted, 'wp-user-frontend' ),
                    $deleted
                ),
            ]
        );
    }

    /**
     * Add a logout link to a menu.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function add_logout_menu( $request ) {
        $item = $this->tools->add_logout_to_menu( $request['menu_id'], $request['label'] );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        return rest_ensure_response(
            [
                'item_id' => $item,
                'message' => __( 'Logout link has been added to the menu successfully!', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Published forms of a type.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function get_forms( $request ) {
        return rest_ensure_response( $this->tools->forms( $request['type'] ) );
    }

    /**
     * The export file's content.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function export( $request ) {
        $export = $this->tools->export( $request['type'], (array) $request['ids'] );

        if ( is_wp_error( $export ) ) {
            return $export;
        }

        if ( empty( $export['forms'] ) ) {
            return new WP_Error( 'wpuf_tools_nothing_to_export', __( 'There are no forms to export.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        return rest_ensure_response( $export );
    }

    /**
     * Import forms from the uploaded `file`.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function import( $request ) {
        $files  = $request->get_file_params();
        $result = $this->tools->import( isset( $files['file'] ) && is_array( $files['file'] ) ? $files['file'] : [] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( [ 'message' => __( 'Forms imported successfully.', 'wp-user-frontend' ) ] );
    }

    /**
     * The onboarding box as it is now (the app's globals are from page load).
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function get_onboarding() {
        return rest_ensure_response( $this->tools->onboarding_entry() );
    }

    /**
     * The shortcode reference.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function get_shortcodes() {
        return rest_ensure_response( $this->tools->shortcodes() );
    }
}
