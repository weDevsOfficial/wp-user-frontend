<?php
/**
 * Frontend uploads as a service: the body of the upload AJAX action, returning instead of printing
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Forms;

use WeDevs\Wpuf\Ajax\Upload_Ajax;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;

/**
 * Both transports (admin-ajax `wpuf_file_upload` / `wpuf_insert_image` and
 * REST `POST /uploads`) call `upload()`, so the `wpuf_upload_file_init`
 * action, the guest rules, the JFIF handling and the `wpuf_upload_response_*`
 * filters stay in one place.
 *
 * @since WPUF_SINCE
 */
class Upload_Service {

    /**
     * The legacy upload handler (keeps `handle_upload()` and `attach_html()`).
     *
     * @var Upload_Ajax
     */
    private $ajax;

    /**
     * @since WPUF_SINCE
     *
     * @param Upload_Ajax $ajax The legacy upload handler
     */
    public function __construct( Upload_Ajax $ajax ) {
        $this->ajax = $ajax;
    }

    /**
     * Whether the current visitor may upload for this form (the rule of the
     * old `upload_file()`: logged in, or a guest form, or a profile form).
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return bool
     */
    public function can_upload( $form_id ) {
        $form_id = (int) $form_id;

        if ( ! $form_id ) {
            return false;
        }

        if ( is_user_logged_in() ) {
            return true;
        }

        $form_settings = wpuf_get_form_settings( $form_id );

        if ( isset( $form_settings['post_permission'] ) && 'guest_post' === $form_settings['post_permission'] ) {
            return true;
        }

        if ( isset( $form_settings['require_login'] ) && 'false' === $form_settings['require_login'] ) {
            return true;
        }

        return 'wpuf_profile' === get_post_type( $form_id );
    }

    /**
     * Store an uploaded file as an attachment.
     *
     * @since WPUF_SINCE
     *
     * @param array  $file       One `$_FILES` entry (name, type, tmp_name, error, size)
     * @param int    $form_id    Form id
     * @param string $field_type Field name the upload belongs to
     * @param bool   $image_only The editor's "insert image" upload (answers an image tag / link)
     *
     * @return array|WP_Error `attach_id`, `html`, `url`, `thumb`, `title`, `mime`
     */
    public function upload( array $file, $form_id, $field_type = '', $image_only = false ) {
        $form_id = (int) $form_id;

        if ( ! $form_id ) {
            return new WP_Error( 'wpuf_upload_invalid_form', __( 'Invalid form.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        /**
         * Fires before a frontend upload is handled.
         *
         * @since 4.0.0
         *
         * @param int    $form_id    Form id
         * @param string $field_type Field name
         */
        do_action( 'wpuf_upload_file_init', $form_id, $field_type );

        if ( ! $this->can_upload( $form_id ) ) {
            return new WP_Error( 'wpuf_upload_forbidden', __( 'You are not allowed to upload files to this form.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        $file = wp_parse_args(
            $file,
            [
                'name'     => '',
                'type'     => '',
                'tmp_name' => '',
                'error'    => UPLOAD_ERR_NO_FILE,
                'size'     => 0,
            ]
        );

        $file_name      = pathinfo( $file['name'], PATHINFO_FILENAME );
        $file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

        // wp_handle_upload() and wp_generate_attachment_metadata() live in the
        // admin includes, which a REST request does not load (admin-ajax does).
        foreach ( [ 'file', 'image', 'media' ] as $include ) {
            if ( ! function_exists( 'file' === $include ? 'wp_handle_upload' : ( 'image' === $include ? 'wp_generate_attachment_metadata' : 'media_handle_upload' ) ) ) {
                require_once ABSPATH . 'wp-admin/includes/' . $include . '.php';
            }
        }

        $attach = $this->ajax->handle_upload(
            [
                'name'     => $file_name . '.' . $file_extension,
                'type'     => $file['type'],
                'tmp_name' => $file['tmp_name'],
                'error'    => $file['error'],
                'size'     => $file['size'],
            ]
        );

        if ( empty( $attach['success'] ) ) {
            return new WP_Error( 'wpuf_upload_failed', isset( $attach['error'] ) ? (string) $attach['error'] : __( 'The upload failed.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        $attach_id = (int) $attach['attach_id'];

        if ( $image_only ) {
            $image_size = Stores::settings()->get( 'wpuf_frontend_posting', 'insert_photo_size', 'thumbnail' );
            $image_type = Stores::settings()->get( 'wpuf_frontend_posting', 'insert_photo_type', 'link' );

            /** This filter is documented in includes/Ajax/Upload_Ajax.php */
            $image_size = apply_filters( 'wpuf_upload_response_image_size', $image_size, $form_id, $field_type );
            /** This filter is documented in includes/Ajax/Upload_Ajax.php */
            $image_type = apply_filters( 'wpuf_upload_response_image_type', $image_type, $form_id, $field_type );

            $html = 'link' === $image_type
                ? wp_get_attachment_link( $attach_id, $image_size )
                : wp_get_attachment_image( $attach_id, $image_size );
        } else {
            $html = Upload_Ajax::attach_html( $attach_id, $field_type, $form_id );
        }

        $thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );

        return [
            'attach_id' => $attach_id,
            'html'      => $html,
            'url'       => (string) wp_get_attachment_url( $attach_id ),
            'thumb'     => $thumb ? $thumb[0] : '',
            'title'     => get_the_title( $attach_id ),
            'mime'      => (string) get_post_mime_type( $attach_id ),
        ];
    }

    /**
     * Delete an attachment the current visitor owns.
     *
     * @since WPUF_SINCE
     *
     * @param int $attachment_id Attachment id
     *
     * @return true|WP_Error
     */
    public function delete( $attachment_id ) {
        $attachment_id = absint( $attachment_id );

        if ( ! $attachment_id ) {
            return new WP_Error( 'wpuf_upload_missing_id', __( 'attach_id is required.', 'wp-user-frontend' ), [ 'status' => 422 ] );
        }

        $attachment = get_post( $attachment_id );

        if ( empty( $attachment ) || 'attachment' !== $attachment->post_type ) {
            return new WP_Error( 'wpuf_upload_not_found', __( 'attachment not found.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( ! $this->ajax->can_delete_attachment( $attachment ) ) {
            return new WP_Error( 'wpuf_upload_forbidden', __( 'You are not allowed to delete this attachment.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        if ( ! wp_delete_attachment( $attachment_id, true ) ) {
            return new WP_Error( 'wpuf_upload_delete_failed', __( 'Could not delete the attachment', 'wp-user-frontend' ), [ 'status' => 422 ] );
        }

        return true;
    }
}
