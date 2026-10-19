<?php
/**
 * Frontend uploads as a service: the body of the upload AJAX action, returning instead of printing
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Forms;

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
     * @var Upload_Handler
     */
    private $ajax;

    /**
     * @since WPUF_SINCE
     *
     * @param Upload_Handler $ajax The legacy upload handler
     */
    public function __construct( Upload_Handler $ajax ) {
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

        // Bind the upload to a real field of the form. The editor's "Insert
        // Photo" (image_only, or type `insert_image` from the React editor)
        // needs a content field with Insert Photo on; every other upload
        // must name a configured upload-capable field.
        $allowed = ( $image_only || 'insert_image' === $field_type )
            ? $this->form_allows_insert_image( $form_id )
            : $this->is_valid_upload_field( $form_id, $field_type );

        if ( ! $allowed ) {
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

            /** This filter is documented in includes/Frontend/Forms/Upload_Handler.php */
            $image_size = apply_filters( 'wpuf_upload_response_image_size', $image_size, $form_id, $field_type );
            /** This filter is documented in includes/Frontend/Forms/Upload_Handler.php */
            $image_type = apply_filters( 'wpuf_upload_response_image_type', $image_type, $form_id, $field_type );

            $html = 'link' === $image_type
                ? wp_get_attachment_link( $attach_id, $image_size )
                : wp_get_attachment_image( $attach_id, $image_size );
        } else {
            $html = Upload_Handler::attach_html( $attach_id, $field_type, $form_id );
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

    /**
     * Whether the requested field is a real upload-capable field of the form.
     *
     * The client-supplied field name must not authorize an upload on its own:
     * it has to name a field that exists in the form and takes files.
     *
     * @since WPUF_SINCE
     *
     * @param int    $form_id    Form id
     * @param string $field_type Client-supplied field name
     *
     * @return bool
     */
    public function is_valid_upload_field( $form_id, $field_type ) {
        if ( '' === (string) $field_type ) {
            return false;
        }

        /**
         * Field input types / templates that may receive uploads.
         *
         * @since WPUF_SINCE
         *
         * @param array  $upload_types
         * @param int    $form_id
         * @param string $field_type
         */
        $upload_types = apply_filters(
            'wpuf_upload_capable_field_types',
            [ 'image_upload', 'file_upload', 'featured_image', 'avatar', 'cover_photo', 'profile_photo' ],
            $form_id,
            $field_type
        );

        $allowed_names = $this->collect_upload_field_names( wpuf_get_form_fields( $form_id ), (array) $upload_types );

        /**
         * Final allow-list of field names that may receive an upload for this form.
         *
         * @since WPUF_SINCE
         *
         * @param array  $allowed_names
         * @param int    $form_id
         * @param string $field_type
         */
        $allowed_names = apply_filters( 'wpuf_upload_allowed_field_names', $allowed_names, $form_id, $field_type );

        return in_array( (string) $field_type, (array) $allowed_names, true );
    }

    /**
     * Whether the form has a field with "Insert Photo" turned on.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return bool
     */
    public function form_allows_insert_image( $form_id ) {
        $allowed = $this->has_insert_image_field( wpuf_get_form_fields( $form_id ) );

        /**
         * Whether an "Insert Photo" upload is allowed for this form.
         *
         * @since WPUF_SINCE
         *
         * @param bool $allowed
         * @param int  $form_id
         */
        return (bool) apply_filters( 'wpuf_upload_allow_insert_image', $allowed, $form_id );
    }

    /**
     * Names of upload-capable fields, including ones inside Column / Repeat fields.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields       Form fields or a nested field list
     * @param array $upload_types Upload-capable input types / templates
     *
     * @return array
     */
    protected function collect_upload_field_names( $fields, $upload_types ) {
        $names = [];

        if ( ! is_array( $fields ) ) {
            return $names;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            // A column bucket (list of fields) has no field keys of its own.
            if ( ! isset( $field['input_type'] ) && ! isset( $field['template'] ) ) {
                $names = array_merge( $names, $this->collect_upload_field_names( $field, $upload_types ) );
                continue;
            }

            $input_type = isset( $field['input_type'] ) ? $field['input_type'] : '';
            $template   = isset( $field['template'] ) ? $field['template'] : '';
            $name       = isset( $field['name'] ) ? $field['name'] : '';

            if ( '' !== $name && ( in_array( $input_type, $upload_types, true ) || in_array( $template, $upload_types, true ) ) ) {
                $names[] = $name;
            }

            if ( ! empty( $field['inner_fields'] ) ) {
                $names = array_merge( $names, $this->collect_upload_field_names( $field['inner_fields'], $upload_types ) );
            }
        }

        return $names;
    }

    /**
     * Find a field with "Insert Photo" turned on, including nested ones.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Form fields or a nested field list
     *
     * @return bool
     */
    protected function has_insert_image_field( $fields ) {
        if ( ! is_array( $fields ) ) {
            return false;
        }

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            if ( isset( $field['insert_image'] ) && 'yes' === $field['insert_image'] ) {
                return true;
            }

            if ( ! empty( $field['inner_fields'] ) ) {
                $nested = $field['inner_fields'];
            } elseif ( ! isset( $field['input_type'] ) && ! isset( $field['template'] ) ) {
                $nested = $field;
            } else {
                $nested = [];
            }

            if ( $nested && $this->has_insert_image_field( $nested ) ) {
                return true;
            }
        }

        return false;
    }
}
