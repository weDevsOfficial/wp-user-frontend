<?php
/**
 * REST: the account page of the current user
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Frontend\Account\Account_Service;
use WeDevs\Wpuf\Frontend\Forms\Upload_Service;
use WeDevs\Wpuf\Platform\Http\Submission_Context;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/account/...`: everything is about the logged-in user and nothing
 * accepts a user id. Visitors get 401 on every route.
 *
 * @since WPUF_SINCE
 */
class AccountController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'account';

    /**
     * The account data and actions.
     *
     * @var Account_Service
     */
    private $account;

    /**
     * Uploads (the profile photo).
     *
     * @var Upload_Service
     */
    private $uploads;

    /**
     * @since WPUF_SINCE
     *
     * @param Account_Service $account Account service
     * @param Upload_Service  $uploads Uploads
     */
    public function __construct( Account_Service $account, Upload_Service $uploads ) {
        $this->account = $account;
        $this->uploads = $uploads;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $logged_in = [ $this, 'logged_in' ];

        $this->route( '/account', WP_REST_Server::READABLE, 'get_account', $logged_in );
        $this->route(
            '/account/posts', WP_REST_Server::READABLE, 'get_posts', $logged_in, [
				'type'     => [
					'type'    => 'string',
					'default' => 'post',
				],
				'page'     => [
					'type'    => 'integer',
					'default' => 1,
					'minimum' => 1,
				],
				'per_page' => [
					'type'    => 'integer',
					'default' => 0,
					'minimum' => 0,
					'maximum' => 100,
				],
			]
        );
        $this->route(
            '/account/posts/(?P<id>\d+)', WP_REST_Server::DELETABLE, 'delete_post', $logged_in, [
				'id' => [
					'type'     => 'integer',
					'required' => true,
					'minimum'  => 1,
				],
			]
        );
        $this->route(
            '/account/sections/(?P<slug>[a-zA-Z0-9_-]+)', WP_REST_Server::READABLE, 'get_section', $logged_in, [
				'slug'    => [
					'type'     => 'string',
					'required' => true,
				],
				'pagenum' => [
					'type'    => 'integer',
					'default' => 1,
					'minimum' => 1,
				],
			]
        );
        $this->route( '/account/profile', WP_REST_Server::EDITABLE, 'update_profile', $logged_in );
        $this->route( '/account/password', WP_REST_Server::EDITABLE, 'update_password', $logged_in );
        $this->route( '/account/avatar', WP_REST_Server::CREATABLE, 'update_avatar', $logged_in );
        $this->route( '/account/avatar', WP_REST_Server::DELETABLE, 'delete_avatar', $logged_in );
        $this->route(
            '/account/billing-address', WP_REST_Server::EDITABLE, 'update_billing_address', $logged_in, [
                'billing_country'   => [
					'type' => 'string',
					'default' => '',
				],
                'billing_state'     => [
					'type' => 'string',
					'default' => '',
				],
                'billing_add_line1' => [
					'type' => 'string',
					'default' => '',
				],
                'billing_add_line2' => [
					'type' => 'string',
					'default' => '',
				],
                'billing_city'      => [
					'type' => 'string',
					'default' => '',
				],
                'billing_zip'       => [
					'type' => 'string',
					'default' => '',
				],
                'type'              => [
					'type' => 'string',
					'default' => '',
				],
                'id'                => [
					'type' => 'string',
					'default' => '',
				],
            ]
        );
        $this->route(
            '/account/billing-address/states', WP_REST_Server::READABLE, 'get_billing_states', $logged_in, [
                'country'    => [
					'type' => 'string',
					'default' => '',
				],
                'field_name' => [
					'type' => 'string',
					'default' => '',
				],
            ]
        );
    }

    /**
     * Logged in, else 401.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function logged_in() {
        if ( is_user_logged_in() ) {
            return true;
        }

        return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
    }

    /**
     * GET /account: profile, sections, stats, settings.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function get_account() {
        return rest_ensure_response(
            [
                'profile'  => $this->account->profile(),
                'sections' => $this->account->sections(),
                'stats'    => $this->account->stats(),
                'settings' => [
                    'page_url'    => $this->account->page_url(),
                    'default_tab' => wpuf_get_option( 'account_page_active_tab', 'wpuf_my_account', 'dashboard' ),
                    'per_page'    => (int) wpuf_get_option( 'per_page', 'wpuf_dashboard', 5 ),
                    'post_types'  => $this->account->allowed_post_types(),
                ],
                'nonces'   => [
                    'profile'  => wp_create_nonce( 'wpuf-account-update-profile' ),
                    'password' => wp_create_nonce( 'wpuf-account-change-password' ),
                ],
            ]
        );
    }

    /**
     * GET /account/posts
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_posts( WP_REST_Request $request ) {
        $result = $this->account->posts( (string) $request['type'], (int) $request['page'], (int) $request['per_page'] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $response = $this->paginate( $result['items'], $result['total'], $result['per_page'] );
        $response->set_data( $result );

        return $response;
    }

    /**
     * DELETE /account/posts/{id}
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function delete_post( WP_REST_Request $request ) {
        $result = $this->account->delete_post( (int) $request['id'] );

        return is_wp_error( $result ) ? $result : rest_ensure_response( [ 'deleted' => true ] );
    }

    /**
     * GET /account/sections/{slug}: the PHP output of a section.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_section( WP_REST_Request $request ) {
        $scripts = wp_scripts()->queue;
        $styles  = wp_styles()->queue;
        $html    = $this->account->section_html( (string) $request['slug'], (int) $request['pagenum'] );

        return is_wp_error( $html ) ? $html : rest_ensure_response(
            [
                'slug'   => sanitize_text_field( (string) $request['slug'] ),
                'html'   => $html,
                'assets' => $this->account->assets_since( $scripts, $styles ),
            ]
        );
    }

    /**
     * PUT /account/profile
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function update_profile( WP_REST_Request $request ) {
        $body = $this->body( $request );
        // The profile hooks see the request the classic account form posted
        // (the `wp_rest` cookie nonce is this route's CSRF check).
        $result = Submission_Context::run( $body, fn() => $this->account->update_profile( $body ), [ 'nonce' => false ] );

        return is_wp_error( $result ) ? $result : rest_ensure_response(
            [
                'updated' => true,
                'profile' => $this->account->profile(),
            ]
        );
    }

    /**
     * PUT /account/password
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function update_password( WP_REST_Request $request ) {
        $result = $this->account->change_password( $this->body( $request ) );

        return is_wp_error( $result ) ? $result : rest_ensure_response(
            [
                'updated' => true,
                'message' => __( 'Password updated successfully!', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * POST /account/avatar (multipart `wpuf_file`): the profile photo.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function update_avatar( WP_REST_Request $request ) {
        $files = $request->get_file_params();
        $file  = isset( $files['wpuf_file'] ) ? (array) $files['wpuf_file'] : [];

        if ( ! $file ) {
            return $this->error( 'no_file', __( 'No file was uploaded.', 'wp-user-frontend' ), 400 );
        }

        /** This filter is documented in includes/functions/users.php */
        $extensions = apply_filters( 'wpuf_field_profile_photo_allowed_extensions', [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ] );
        $extension  = strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $extension, (array) $extensions, true ) ) {
            return $this->error( 'bad_type', __( 'This file type is not allowed for a profile photo.', 'wp-user-frontend' ), 400 );
        }

        $limit   = function () {
            return 2 * MB_IN_BYTES;
        };
        $size_ok = (int) $file['size'] <= (int) apply_filters( 'wpuf_profile_photo_max_size', $limit() );

        if ( ! $size_ok ) {
            return $this->error( 'too_large', __( 'The profile photo must be 2 MB or smaller.', 'wp-user-frontend' ), 400 );
        }

        $user_id = get_current_user_id();
        $form_id = (int) wpuf_get_option( 'edit_profile_form', 'wpuf_my_account', 0 );
        $result  = $this->uploads->upload( $file, $form_id ? $form_id : $this->any_form_id(), 'profile_photo' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $previous = (int) get_user_meta( $user_id, 'wpuf_profile_photo', true );

        update_user_meta( $user_id, 'wpuf_profile_photo', (int) $result['attach_id'] );
        wp_update_post(
            [
                'ID'          => (int) $result['attach_id'],
                'post_author' => $user_id,
            ]
        );

        if ( $previous && $previous !== (int) $result['attach_id'] && (int) get_post_field( 'post_author', $previous ) === $user_id ) {
            wp_delete_attachment( $previous, true );
        }

        return rest_ensure_response( [ 'profile' => $this->account->profile() ] );
    }

    /**
     * DELETE /account/avatar: back to Gravatar or initials.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function delete_avatar() {
        $user_id  = get_current_user_id();
        $previous = (int) get_user_meta( $user_id, 'wpuf_profile_photo', true );

        delete_user_meta( $user_id, 'wpuf_profile_photo' );

        if ( $previous && (int) get_post_field( 'post_author', $previous ) === $user_id ) {
            wp_delete_attachment( $previous, true );
        }

        return rest_ensure_response( [ 'profile' => $this->account->profile() ] );
    }

    /**
     * PUT /account/billing-address: save the billing address; on the pay page
     * (`type` + `id` of the item) also the tax rate and total for it.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function update_billing_address( WP_REST_Request $request ) {
        $keys  = [ 'billing_add_line1', 'billing_add_line2', 'billing_city', 'billing_state', 'billing_zip', 'billing_country', 'type', 'id' ];
        $input = [];

        foreach ( $keys as $key ) {
            $input[ $key ] = (string) $request->get_param( $key );
        }

        $tax = wpuf_save_billing_address( get_current_user_id(), $input );

        if ( null === $tax && wpuf()->is_pro() ) {
            // An older Pro answers only through this action: it prints the JSON
            // response and exits, which is the response of this route then.
            do_action(
                'wpuf_calculate_tax',
                [
                    'type'            => sanitize_text_field( $input['type'] ),
                    'id'              => sanitize_text_field( $input['id'] ),
                    'billing_country' => sanitize_text_field( $input['billing_country'] ),
                    'billing_state'   => sanitize_text_field( $input['billing_state'] ),
                ]
            );
        }

        return rest_ensure_response(
            null === $tax ? [ 'saved' => true ] : array_merge( [ 'saved' => true ], $tax )
        );
    }

    /**
     * GET /account/billing-address/states: the state field of a country
     * (`html`, empty with `states: false` when the country has none).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function get_billing_states( WP_REST_Request $request ) {
        $html = wpuf_get_states_field_html(
            sanitize_text_field( (string) $request->get_param( 'country' ) ),
            sanitize_text_field( (string) $request->get_param( 'field_name' ) )
        );

        return rest_ensure_response(
            'nostates' === $html
                ? [
					'states' => false,
					'html' => '',
				]
                : [
					'states' => true,
					'html' => $html,
				]
        );
    }

    /**
     * A form id the upload service accepts when no profile form is set
     * (the upload rules only need an existing form for a logged-in user).
     *
     * @since WPUF_SINCE
     *
     * @return int
     */
    private function any_form_id() {
        $forms = get_posts(
            [
                'post_type'      => 'wpuf_forms',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]
        );

        return $forms ? (int) $forms[0] : 0;
    }

    /**
     * The request body as an unslashed array.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return array
     */
    private function body( WP_REST_Request $request ) {
        $body = (array) $request->get_body_params();

        return $body ? $body : (array) $request->get_json_params();
    }
}
