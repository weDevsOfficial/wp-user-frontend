<?php
/**
 * REST: frontend form uploads
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Frontend\Forms\Upload_Service;
use WeDevs\Wpuf\Platform\REST\Rate_Limit;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `POST wpuf/v1/uploads` (multipart, one file as `wpuf_file`) and
 * `DELETE wpuf/v1/uploads/{id}`. The same rules as the admin-ajax upload:
 * the form must allow the visitor to upload, the upload nonce the schema
 * handed out must match, and deleting needs ownership (or the guest cookie
 * token for a guest's own upload).
 *
 * @since WPUF_SINCE
 */
class UploadsController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'upload';

    /**
     * Uploads.
     *
     * @var Upload_Service
     */
    private $uploads;

    /**
     * Rate limiter (not applied yet: the per-IP limits come back with a later enhancement).
     *
     * @var Rate_Limit
     */
    private $limiter;

    /**
     * @since WPUF_SINCE
     *
     * @param Upload_Service $uploads Uploads
     * @param Rate_Limit     $limiter Rate limiter
     */
    public function __construct( Upload_Service $uploads, Rate_Limit $limiter ) {
        $this->uploads = $uploads;
        $this->limiter = $limiter;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route(
            '/uploads', WP_REST_Server::CREATABLE, 'create', [ $this, 'can_upload' ], [
				'form_id'    => [
					'type'     => 'integer',
					'required' => true,
					'minimum'  => 1,
				],
				'type'       => [
					'type'    => 'string',
					'default' => '',
				],
				'image_only' => [
					'type'    => 'boolean',
					'default' => false,
				],
				'wpuf_nonce' => [
					'type'     => 'string',
					'required' => true,
				],
			]
        );
        $this->route(
            '/uploads/(?P<id>\d+)', WP_REST_Server::DELETABLE, 'delete', [ $this, 'can_delete' ], [
				'id'         => [
					'type'     => 'integer',
					'required' => true,
					'minimum'  => 1,
				],
				'wpuf_nonce' => [
					'type'    => 'string',
					'default' => '',
				],
			]
        );
    }

    /**
     * The form allows this visitor to upload and the upload nonce matches.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_upload( WP_REST_Request $request ) {
        if ( ! wp_verify_nonce( sanitize_key( (string) $request['wpuf_nonce'] ), 'wpuf-upload-nonce' ) ) {
            return $this->error( 'expired', __( 'Your session has expired. Please reload the page and try again.', 'wp-user-frontend' ), 403 );
        }

        if ( ! $this->uploads->can_upload( (int) $request['form_id'] ) ) {
            return is_user_logged_in()
                ? $this->error( 'forbidden', __( 'You are not allowed to upload files to this form.', 'wp-user-frontend' ), 403 )
                : new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        return true;
    }

    /**
     * Deleting: a logged-in user (ownership is checked by the service), or a
     * guest with the delete nonce (ownership through the guest cookie token).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_delete( WP_REST_Request $request ) {
        if ( is_user_logged_in() ) {
            return true;
        }

        if ( wp_verify_nonce( sanitize_key( (string) $request['wpuf_nonce'] ), 'wpuf_nonce' ) ) {
            return true;
        }

        return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
    }

    /**
     * POST /uploads
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function create( WP_REST_Request $request ) {
        $files = $request->get_file_params();
        $file  = isset( $files['wpuf_file'] ) ? (array) $files['wpuf_file'] : [];

        if ( ! $file ) {
            return $this->error( 'no_file', __( 'No file was uploaded.', 'wp-user-frontend' ), 400 );
        }

        $result = $this->uploads->upload( $file, (int) $request['form_id'], sanitize_text_field( (string) $request['type'] ), $this->cast_bool( $request['image_only'] ) );

        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    /**
     * DELETE /uploads/{id}
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function delete( WP_REST_Request $request ) {
        $result = $this->uploads->delete( (int) $request['id'] );

        return is_wp_error( $result ) ? $result : rest_ensure_response( [ 'deleted' => true ] );
    }
}
