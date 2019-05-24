<?php

/**
 * Attachment Uploader class
 *
 * @since 1.0
 * @package wpuf
 */
class WPUF_Upload {

    function __construct() {

        add_action( 'wp_ajax_wpuf_upload_file', array($this, 'upload_file') );
        add_action( 'wp_ajax_nopriv_wpuf_upload_file', array($this, 'upload_file') );

        add_action( 'wp_ajax_wpuf_file_del', array($this, 'delete_file') );
        add_action( 'wp_ajax_nopriv_wpuf_file_del', array($this, 'delete_file') );

        add_action( 'wp_ajax_wpuf_insert_image', array( $this, 'insert_image' ) );
        add_action( 'wp_ajax_nopriv_wpuf_insert_image', array( $this, 'insert_image' ) );
    }

    /**
     * Validate if it's coming from WordPress with a valid nonce
     *
     * @return void
     */
    function validate_nonce() {
        $nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf-upload-nonce' ) ) {
            die( 'error' );
        }
    }

    function upload_file( $image_only = false ) {
        $this->validate_nonce();

        // a valid request will have a form ID
        $form_id = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : false;

        if ( ! $form_id ) {
            die( 'error' );
        }

        // check if guest post enabled for guests
        if ( ! is_user_logged_in() ) {

            $guest_post    = false;
            $form_settings = wpuf_get_form_settings( $form_id );

            if ( isset( $form_settings['guest_post'] ) && $form_settings['guest_post'] == 'true' ) {
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

        $upload = array(
            'name'     => $_FILES['wpuf_file']['name'],
            'type'     => $_FILES['wpuf_file']['type'],
            'tmp_name' => $_FILES['wpuf_file']['tmp_name'],
            'error'    => $_FILES['wpuf_file']['error'],
            'size'     => $_FILES['wpuf_file']['size']
        );

        header('Content-Type: text/html; charset=' . get_option('blog_charset'));

        $attach = $this->handle_upload( $upload );

        if ( $attach['success'] ) {

            $response = array( 'success' => true );

            if ($image_only) {
                $image_size = wpuf_get_option( 'insert_photo_size', 'wpuf_frontend_posting', 'thumbnail' );
                $image_type = wpuf_get_option( 'insert_photo_type', 'wpuf_frontend_posting', 'link' );

                if ( $image_type == 'link' ) {
                    $response['html'] = wp_get_attachment_link( $attach['attach_id'], $image_size );
                } else {
                    $response['html'] = wp_get_attachment_image( $attach['attach_id'], $image_size );
                }

            } else {
                $response['html'] = $this->attach_html( $attach['attach_id'] );
            }

            echo $response['html'];
        } else {
            echo $attach['error'];
        }


        // $response = array('success' => false, 'message' => $attach['error']);
        // echo json_encode( $response );
        exit;
    }

    /**
     * Generic function to upload a file
     *
     * @param string $field_name file input field name
     * @return bool|int attachment id on success, bool false instead
     */
    function handle_upload( $upload_data ) {

        $uploaded_file = wp_handle_upload( $upload_data, array('test_form' => false) );

        // If the wp_handle_upload call returned a local path for the image
        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc = $uploaded_file['file'];
            $file_name = basename( $upload_data['name'] );
            $file_type = wp_check_filetype( $file_name );

            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return array('success' => true, 'attach_id' => $attach_id);
        }

        return array('success' => false, 'error' => $uploaded_file['error']);
    }

    public static function attach_html( $attach_id, $type = NULL ) {
        if ( !$type ) {
            $type = isset( $_GET['type'] ) ? $_GET['type'] : 'image';
        }

        $attachment = get_post( $attach_id );

        if ( ! $attachment ) {
            return;
        }

        if (wp_attachment_is_image( $attach_id)) {
            $image = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
            $image = $image[0];
        } else {
            $image = wp_mime_type_icon( $attach_id );
        }

        $html = '<li class="ui-state-default wpuf-image-wrap thumbnail">';
        $html .= sprintf( '<div class="attachment-name"><img src="%s" alt="%s" /></div>', $image, esc_attr( $attachment->post_title ) );

        if ( wpuf_get_option( 'image_caption', 'wpuf_frontend_posting', 'off' ) == 'on' ) {
            $html .= '<div class="wpuf-file-input-wrap">';
            $html .= sprintf( '<input type="text" name="wpuf_files_data[%d][title]" value="%s" placeholder="%s">', $attach_id, esc_attr( $attachment->post_title ), __( 'Title', 'wp-user-frontend' ) );
            $html .= sprintf( '<textarea name="wpuf_files_data[%d][caption]" placeholder="%s">%s</textarea>', $attach_id, __( 'Caption', 'wp-user-frontend' ), esc_textarea( $attachment->post_excerpt ) );
            $html .= sprintf( '<textarea name="wpuf_files_data[%d][desc]" placeholder="%s">%s</textarea>', $attach_id, __( 'Description', 'wp-user-frontend' ), esc_textarea( $attachment->post_content ) );
            $html .= '</div>';
        }

        $html .= sprintf( '<input type="hidden" name="wpuf_files[%s][]" value="%d">', $type, $attach_id );
        $html .= '<div class="caption">';
        $html .= sprintf( '<a href="#" class="attachment-delete" data-attach_id="%d"> <img src="%s" /></a>', $attach_id, WPUF_ASSET_URI . '/images/del-img.png' );
        $html .= sprintf( '<span class="wpuf-drag-file"> <img src="%s" /></span>', WPUF_ASSET_URI . '/images/move-img.png' );
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    function delete_file() {
        check_ajax_referer( 'wpuf_nonce', 'nonce' );

        $attach_id = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
        $attachment = get_post( $attach_id );

        //post author or editor role
        if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
            echo 'success';
        }

        exit;
    }

    function associate_file( $attach_id, $post_id ) {
        wp_update_post( array(
            'ID' => $attach_id,
            'post_parent' => $post_id
        ) );
    }

    function insert_image() {
        $this->upload_file( true );
    }

}
