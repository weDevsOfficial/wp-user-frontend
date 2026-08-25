<?php

namespace WeDevs\Wpuf\Ajax;

/**
 * Attachment Uploader class
 *
 * @since 1.0
 */
class Upload_Ajax {

    /**
     * Validate if it's coming from WordPress with a valid nonce
     *
     * @return void
     */
    public function validate_nonce() {
        $nonce = isset( $_GET['nonce'] ) ? sanitize_key( wp_unslash( $_GET['nonce'] ) ) : '';
        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-upload-nonce' ) ) {
            return;
        }
    }

    public function upload_file( $image_only = false ) {
        $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ) : '';
        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-upload-nonce' ) ) {
            return;
        }
        // a valid request will have a form ID
        $form_id = isset( $_POST['form_id'] ) ? intval( wp_unslash( $_POST['form_id'] ) ) : false;
        if ( ! $form_id ) {
            die( 'error' );
        }
        $field_type = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
        /**
         * Hook fires before begining upload process
         *
         * @since 3.3.0
         *
         * @param int    $form_id
         * @param string $field_type
         */
        do_action( 'wpuf_upload_file_init', $form_id, $field_type );
        // check if guest post enabled for guests
        if ( ! is_user_logged_in() ) {
            $guest_post    = false;
            $form_settings = wpuf_get_form_settings( $form_id );
            if ( isset( $form_settings['post_permission'] ) && 'guest_post' === $form_settings['post_permission'] ) {
                $guest_post = true;
            }
            // check if the request coming from weForms & allow users to upload when require login option is disabled
            if ( isset( $form_settings['require_login'] ) && $form_settings['require_login'] == 'false' ) {
                $guest_post = true;
            }
            //if it is registration form, let the user upload the file
            if ( get_post_type( $form_id ) == 'wpuf_profile' ) {
                $guest_post = true;
            }
            if ( ! $guest_post ) {
                die( 'error' );
            }
        }
        $wpuf_file = isset( $_FILES['wpuf_file'] ) ? $_FILES['wpuf_file'] : []; // WPCS: sanitization ok.
        $file_name      = pathinfo( $wpuf_file['name'], PATHINFO_FILENAME );
        $file_extension = pathinfo( $wpuf_file['name'], PATHINFO_EXTENSION );
        $upload = [
            'name'     => $file_name . '.' . $file_extension,
            'type'     => $wpuf_file['type'],
            'tmp_name' => $wpuf_file['tmp_name'],
            'error'    => $wpuf_file['error'],
            'size'     => $wpuf_file['size'],
        ];
        header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        $attach = $this->handle_upload( $upload );
        if ( $attach['success'] ) {
            $response = [ 'success' => true ];
            if ( $image_only ) {
                $image_size = wpuf_get_option( 'insert_photo_size', 'wpuf_frontend_posting', 'thumbnail' );
                $image_type = wpuf_get_option( 'insert_photo_type', 'wpuf_frontend_posting', 'link' );
                /**
                 * Filter upload image size for response
                 *
                 * @since 3.3.0
                 *
                 * @param string $image_size
                 * @param int    $form_id
                 * @param string $field_type
                 */
                $image_size = apply_filters( 'wpuf_upload_response_image_size', $image_size, $form_id, $field_type );
                /**
                 * Filter upload image type for response
                 *
                 * @since 3.3.0
                 *
                 * @param string $image_size
                 * @param int    $form_id
                 * @param string $field_type
                 */
                $image_type = apply_filters( 'wpuf_upload_response_image_type', $image_type, $form_id, $field_type );
                if ( $image_type == 'link' ) {
                    $response['html'] = wp_get_attachment_link( $attach['attach_id'], $image_size );
                } else {
                    $response['html'] = wp_get_attachment_image( $attach['attach_id'], $image_size );
                }
            } else {
                $response['html'] = self::attach_html( $attach['attach_id'], $field_type, $form_id );
            }
            echo wp_kses(
                $response['html'], [
                    'li'       => [
                        'class' => [],
                    ],
                    'div'      => [
                        'class' => [],
                    ],
                    'img'      => [
                        'src'   => [],
                        'alt'   => [],
                        'class' => [],
                    ],
                    'input'    => [
                        'type'        => [],
                        'name'        => [],
                        'value'       => [],
                        'placeholder' => [],
                    ],
                    'textarea' => [
                        'name'        => [],
                        'placeholder' => [],
                    ],
                    'a'        => [
                        'href'           => [],
                        'class'          => [],
                        'data-attach-id' => [],
                    ],
                    'span'     => [
                        'class' => [],
                    ],
                ]
            );
        } else {
            wp_send_json_error( $attach['error'], 200 );
        }
        exit;
    }

    /**
     * Generic function to upload a file
     *
     * @param array $upload_data file upload data
     *
     * @return array attachment result with success status and attach_id
     */
    public function handle_upload( $upload_data ) {
        // Allow the .jfif image extension (a JPEG variant) for WPUF uploads only,
        // then unhook so the site-wide allowed types are left unchanged.
        add_filter( 'upload_mimes', [ $this, 'allow_jfif_mime_type' ] );
        add_filter( 'wp_check_filetype_and_ext', [ $this, 'resolve_jfif_filetype' ], 10, 4 );

        $uploaded_file = wp_handle_upload( $upload_data, [ 'test_form' => false ] );

        remove_filter( 'upload_mimes', [ $this, 'allow_jfif_mime_type' ] );
        remove_filter( 'wp_check_filetype_and_ext', [ $this, 'resolve_jfif_filetype' ], 10 );
        // If the wp_handle_upload call returned a local path for the image
        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc    = $uploaded_file['file'];
            $file_name   = basename( $uploaded_file['file'] );
            $file_type   = wp_check_filetype( $file_name );
            $attachment = [
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];
            $attach_id   = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            // Mark the attachment as a WPUF upload so it can be safely
            // identified and removed later through the wpuf_file_del endpoint.
            update_post_meta( $attach_id, '_wpuf_attachment', 1 );

            // Guests have no author id, so bind the upload to a per-session
            // token. This prevents one visitor from deleting another visitor's
            // (or any author-less) attachment via the public delete handler.
            if ( ! is_user_logged_in() ) {
                update_post_meta( $attach_id, '_wpuf_guest_token', $this->get_guest_upload_token() );
            }

            return [
                'success'   => true,
                'attach_id' => $attach_id,
            ];
        }

        return [
            'success' => false,
            'error'   => $uploaded_file['error'],
        ];
    }

    /**
     * Allow the .jfif image extension (a JPEG variant) during a WPUF upload.
     *
     * WordPress does not permit .jfif by default, so a valid JFIF/JPEG image
     * uploaded through a frontend field would otherwise be rejected.
     *
     * @since 4.3.11
     *
     * @param array $mimes Allowed mime types keyed by extension pattern.
     *
     * @return array
     */
    public function allow_jfif_mime_type( $mimes ) {
        $mimes['jfif'] = 'image/jpeg';

        return $mimes;
    }

    /**
     * Resolve a .jfif file to the image/jpeg type so WordPress' real-mime check
     * accepts it instead of flagging an extension/mime mismatch.
     *
     * @since 4.3.11
     *
     * @param array  $data     File data array with keys ext, type, proper_filename.
     * @param string $file     Full path to the file.
     * @param string $filename The name of the file.
     * @param array  $mimes    Allowed mime types.
     *
     * @return array
     */
    public function resolve_jfif_filetype( $data, $file, $filename, $mimes ) {
        if ( empty( $data['ext'] ) && preg_match( '/\.jfif$/i', $filename ) ) {
            $data['ext']  = 'jfif';
            $data['type'] = 'image/jpeg';
        }

        return $data;
    }

    public static function attach_html( $attach_id, $type = NULL, $form_id = NULL ) {
        if ( ! $type ) {
            $type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'image';
        }
        $attachment = get_post( $attach_id );
        if ( ! $attachment ) {
            return;
        }
        if ( wp_attachment_is_image( $attach_id ) ) {
            /**
             * Filter upload image size for response
             *
             * @since 3.3.0
             *
             * @param string $image_size
             * @param int    $form_id
             * @param string $field_type
             */
            $image_size = apply_filters( 'wpuf_upload_response_image_size', 'thumbnail', $form_id, $type );
            $image = wp_get_attachment_image_src( $attach_id, $image_size );
            $image = $image[0];
        } else {
            $image = wp_mime_type_icon( $attach_id );
        }
        /**
         * Filter uploaded image class names for the reponse
         *
         * @since 3.3.0
         *
         * @param array $class_names
         */
        $attachment_class_names = apply_filters(
            'wpuf_upload_response_image_class_names', [ 'wpuf-attachment-image' ]
        );
        $attachment_class_names = implode( ' ', $attachment_class_names );
        $html = '<li class="ui-state-default wpuf-image-wrap thumbnail">';
        $html .= sprintf(
            '<div class="attachment-name"><img src="%s" alt="%s" class="%s" /></div>', $image,
            esc_attr( $attachment->post_title ), esc_attr( $attachment_class_names )
        );
        $html .= sprintf(
            '<span class="wpuf-attach-title">%s</span>', esc_html( $attachment->post_title )
        );
        if ( wpuf_get_option( 'image_caption', 'wpuf_frontend_posting', 'off' ) == 'on' ) {
            $html .= '<div class="wpuf-file-input-wrap">';
            $html .= sprintf(
                '<input type="text" name="wpuf_files_data[%d][title]" value="%s" placeholder="%s">', $attach_id,
                esc_attr( $attachment->post_title ), __( 'Title', 'wp-user-frontend' )
            );
            $html .= sprintf(
                '<textarea name="wpuf_files_data[%d][caption]" placeholder="%s">%s</textarea>', $attach_id,
                __( 'Caption', 'wp-user-frontend' ), esc_textarea( $attachment->post_excerpt )
            );
            $html .= sprintf(
                '<textarea name="wpuf_files_data[%d][desc]" placeholder="%s">%s</textarea>', $attach_id,
                __( 'Description', 'wp-user-frontend' ), esc_textarea( $attachment->post_content )
            );
            $html .= '</div>';
        }
        $html .= sprintf( '<input type="hidden" name="wpuf_files[%s][]" value="%d">', $type, $attach_id );
        $html .= '<div class="caption">';
        $html .= sprintf(
            '<a href="#" class="attachment-delete" data-attach-id="%d"> <img src="%s" /></a>', $attach_id,
            WPUF_ASSET_URI . '/images/del-img.png'
        );
        $html .= sprintf(
            '<span class="wpuf-drag-file"> <img src="%s" /></span>', WPUF_ASSET_URI . '/images/move-img.png'
        );
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    public function delete_file() {
        check_ajax_referer( 'wpuf_nonce', 'nonce' );
        $attachment_id = isset( $_POST['attach_id'] ) ? absint( wp_unslash( $_POST['attach_id'] ) ) : 0;
        if ( empty( $attachment_id ) ) {
            wp_send_json_error( [ 'message' => __( 'attach_id is required.', 'wp-user-frontend' ) ], 422 );
        }
        $attachment = get_post( $attachment_id );

        if ( empty( $attachment ) || 'attachment' !== $attachment->post_type ) {
            wp_send_json_error( [ 'message' => __( 'attachment not found.', 'wp-user-frontend' ) ] );
        }

        if ( ! $this->can_delete_attachment( $attachment ) ) {
            wp_send_json_error( [ 'message' => __( 'You are not allowed to delete this attachment.', 'wp-user-frontend' ) ], 403 );
        }

        $deleted = wp_delete_attachment( $attachment_id, true );
        if ( $deleted ) {
            wp_send_json_success( [ 'message' => __( 'Attachment deleted successfully.', 'wp-user-frontend' ) ] );
        }
        wp_send_json_error( [ 'message' => __( 'Could not delete the attachment', 'wp-user-frontend' ) ], 422 );
    }

    /**
     * Check whether the current request is allowed to delete an attachment
     *
     * Closes the unauthenticated arbitrary attachment deletion hole where any
     * author-less attachment ( post_author = 0 ) could be removed because of a
     * loose 0 == 0 comparison. Logged-in users may only remove their own
     * uploads, guests may only remove uploads bound to their own session token,
     * and users with the editor capability keep full control.
     *
     * @since 4.3.9
     *
     * @param \WP_Post $attachment Attachment post object.
     *
     * @return bool
     */
    protected function can_delete_attachment( $attachment ) {
        // Users able to manage others' content (editors, admins) keep full control.
        if ( current_user_can( 'delete_private_pages' ) ) {
            return true;
        }

        $author_id = (int) $attachment->post_author;

        // Logged-in users can only delete attachments they own. The author of a
        // real user upload is never 0, so the previous 0 == 0 bypass is gone.
        if ( is_user_logged_in() ) {
            return $author_id > 0 && (int) get_current_user_id() === $author_id;
        }

        // Guests may only remove author-less WPUF uploads bound to their session.
        if ( 0 !== $author_id ) {
            return false;
        }

        // Only WPUF-created uploads carry the marker; arbitrary site media does not.
        if ( ! get_post_meta( $attachment->ID, '_wpuf_attachment', true ) ) {
            return false;
        }

        $stored_token = (string) get_post_meta( $attachment->ID, '_wpuf_guest_token', true );
        $cookie_token = isset( $_COOKIE['wpuf_guest_upload'] )
            ? sanitize_text_field( wp_unslash( $_COOKIE['wpuf_guest_upload'] ) )
            : '';

        return '' !== $stored_token && '' !== $cookie_token && hash_equals( $stored_token, $cookie_token );
    }

    /**
     * Get ( or create ) the per-session token used to bind guest uploads
     *
     * The token is stored in an http-only cookie and mirrored into attachment
     * meta on upload so the public delete handler can verify ownership without
     * relying on the author id, which is always 0 for guests.
     *
     * @since 4.3.9
     *
     * @return string
     */
    protected function get_guest_upload_token() {
        $token = isset( $_COOKIE['wpuf_guest_upload'] )
            ? sanitize_text_field( wp_unslash( $_COOKIE['wpuf_guest_upload'] ) )
            : '';

        if ( empty( $token ) ) {
            $token = wp_generate_password( 32, false );

            // SameSite=Lax keeps the token from being sent on cross-site
            // requests, adding defense in depth to the delete handler.
            setcookie(
                'wpuf_guest_upload',
                $token,
                [
                    'expires'  => time() + DAY_IN_SECONDS,
                    'path'     => COOKIEPATH ? COOKIEPATH : '/',
                    'domain'   => COOKIE_DOMAIN,
                    'secure'   => is_ssl(),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );

            // Make the token available within the current request as well.
            $_COOKIE['wpuf_guest_upload'] = $token;
        }

        return $token;
    }

    public function associate_file( $attach_id, $post_id ) {
        wp_update_post(
            [
                'ID'          => $attach_id,
                'post_parent' => $post_id,
            ]
        );
    }

    public function insert_image() {
        $this->upload_file( true );
    }
    
}
