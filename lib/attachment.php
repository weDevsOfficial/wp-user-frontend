<?php

/**
 * Attachment Uploader class
 *
 * @since 0.8
 * @package WP User Frontend
 */
class WPUF_Attachment {

    function __construct() {
        $allow_upload = wpuf_get_option( 'allow_attachment' );

        if ( $allow_upload == 'yes' ) {
            add_action( 'wpuf_add_post_form_tags', array($this, 'add_post_fields'), 10, 2 );
            add_action( 'wp_enqueue_scripts', array($this, 'scripts') );

            add_action( 'wp_ajax_wpuf_attach_upload', array($this, 'upload_file') );
            add_action( 'wp_ajax_wpuf_attach_del', array($this, 'delete_file') );

            add_action( 'wpuf_add_post_after_insert', array($this, 'attach_file_to_post') );
            add_action( 'wpuf_edit_post_after_update', array($this, 'attach_file_to_post') );
        }
    }

    function scripts() {
        if ( has_shortcode( 'wpuf_addpost' ) || has_shortcode( 'wpuf_edit' ) || has_shortcode( 'wpuf_dashboard' ) ) {

            $max_file_size = intval( wpuf_get_option( 'attachment_max_size' ) ) * 1024;
            $max_upload = intval( wpuf_get_option( 'attachment_num' ) );
            $attachment_enabled = wpuf_get_option( 'allow_attachment' );

            wp_enqueue_script( 'jquery' );
            if ( has_shortcode( 'wpuf_addpost' ) || has_shortcode( 'wpuf_edit' ) ) {
                wp_enqueue_script( 'plupload-handlers' );
            }
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'wpuf_attachment', plugins_url( 'js/attachment.js', dirname( __FILE__ ) ), array('jquery') );

            wp_localize_script( 'wpuf_attachment', 'wpuf_attachment', array(
                'nonce' => wp_create_nonce( 'wpuf_attachment' ),
                'number' => $max_upload,
                'attachment_enabled' => ($attachment_enabled == 'yes') ? true : false,
                'plupload' => array(
                    'runtimes' => 'html5,silverlight,flash,html4',
                    'browse_button' => 'wpuf-attachment-upload-pickfiles',
                    'container' => 'wpuf-attachment-upload-container',
                    'file_data_name' => 'wpuf_attachment_file',
                    'max_file_size' => $max_file_size . 'b',
                    'url' => admin_url( 'admin-ajax.php' ) . '?action=wpuf_attach_upload&nonce=' . wp_create_nonce( 'wpuf_audio_track' ),
                    'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
                    'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
                    'filters' => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                    'multipart' => true,
                    'urlstream_upload' => true,
                )
            ) );
        }
    }

    function add_post_fields( $post_type, $post_obj = null ) {
        //var_dump($post_type, $post_obj);
        $attachments = array();
        if ( $post_obj ) {
            $attachments = wpfu_get_attachments( $post_obj->ID );
        }
        ?>
        <li>
            <label><?php echo wpuf_get_option( 'attachment_label' ) ?></label>
            <div class="clear"></div>
        </li>
        <li>
            <div id="wpuf-attachment-upload-container">
                <div id="wpuf-attachment-upload-filelist">
                    <ul class="wpuf-attachment-list">
                        <script>window.wpufFileCount = 0;</script>
                        <?php
                        if ( $attachments ) {
                            foreach ($attachments as $attach) {
                                echo $this->attach_html( $attach['id'] );
                                echo '<script>window.wpufFileCount += 1;</script>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <a id="wpuf-attachment-upload-pickfiles" class="button" href="#"><?php echo wpuf_get_option( 'attachment_btn_label' ); ?></a>
            </div>
            <div class="clear"></div>
        </li>
        <?php
    }

    function upload_file() {
        check_ajax_referer( 'wpuf_audio_track', 'nonce' );

        $upload = array(
            'name' => $_FILES['wpuf_attachment_file']['name'],
            'type' => $_FILES['wpuf_attachment_file']['type'],
            'tmp_name' => $_FILES['wpuf_attachment_file']['tmp_name'],
            'error' => $_FILES['wpuf_attachment_file']['error'],
            'size' => $_FILES['wpuf_attachment_file']['size']
        );

        $attach_id = wpuf_upload_file( $upload );

        if ( $attach_id ) {
            $html = $this->attach_html( $attach_id );

            $response = array(
                'success' => true,
                'html' => $html,
            );

            echo json_encode( $response );
            exit;
        }


        $response = array('success' => false);
        echo json_encode( $response );
        exit;
    }

    function attach_html( $attach_id ) {

        $attachment = get_post( $attach_id );

        $html = '';
        $html .= '<li class="wpuf-attachment">';
        $html .= '<span class="handle">Move</span>';
        $html .= '<span class="attachment-title">';
        $html .= sprintf( '<input type="text" name="wpuf_attach_title[]" value="%s" placeholder="%s" />', esc_attr( $attachment->post_title ), esc_attr__( 'Insert Song Title', 'wpuf' ) );
        $html .= '</span>';
        $html .= sprintf( '<span class="attachment-name">%s</span>', esc_attr( $attachment->post_title ) );
        $html .= sprintf( '<span class="attachment-actions"><a href="#" class="track-delete button" data-attach_id="%d">%s</a></span>', $attach_id, __( 'Delete', 'wpuf' ) );
        $html .= sprintf( '<input type="hidden" name="wpuf_attach_id[]" value="%d" />', $attach_id );
        $html .= '</li>';

        return $html;
    }

    function delete_file() {
        check_ajax_referer( 'wpuf_attachment', 'nonce' );

        $attach_id = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
        $attachment = get_post( $attach_id );

        //post author or editor role
        if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
            wp_delete_attachment( $attach_id, true );
            echo 'success';
        }

        exit;
    }

    function attach_file_to_post( $post_id ) {
        $posted = $_POST;

        if ( isset( $posted['wpuf_attach_id'] ) ) {
            foreach ($posted['wpuf_attach_id'] as $index => $attach_id) {
                $postarr = array(
                    'ID' => $attach_id,
                    'post_title' => $posted['wpuf_attach_title'][$index],
                    'post_parent' => $post_id,
                    'menu_order' => $index
                );

                wp_update_post( $postarr );
            }
        }
    }

}

$wpuf_audio = new WPUF_Attachment();