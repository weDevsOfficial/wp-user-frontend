<?php

/**
 * If the user isn't logged in, redirect
 * to the login page
 *
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_auth_redirect_login() {
    $user = wp_get_current_user();

    if ( $user->id == 0 ) {
        nocache_headers();
        wp_redirect( get_option( 'siteurl' ) . '/wp-login.php?redirect_to=' . urlencode( $_SERVER['REQUEST_URI'] ) );
        exit();
    }
}

/**
 * Utility function for debugging
 *
 * @since version 0.1
 * @author Tareq Hasan
 */
if ( !function_exists( 'd' ) ) {

    function d( $param ) {
        echo "<pre>";
        print_r( $param );
        echo "</pre>";
    }

}

/**
 * Format the post status for user dashboard
 *
 * @param string $status
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_show_post_status( $status ) {

    if ( $status == 'publish' ) {

        $title = __( 'Live', 'wpuf' );
        $fontcolor = '#33CC33';
    } else if ( $status == 'draft' ) {

        $title = __( 'Offline', 'wpuf' );
        $fontcolor = '#bbbbbb';
    } else if ( $status == 'pending' ) {

        $title = __( 'Awaiting Approval', 'wpuf' );
        $fontcolor = '#C00202';
    } else if ( $status == 'future' ) {
        $title = __( 'Scheduled', 'wpuf' );
        $fontcolor = '#bbbbbb';
    }

    echo '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
}

/**
 * Enqueues Styles and Scripts when the shortcodes are used only
 *
 * @uses has_shortcode()
 * @since 0.2
 */
function wpuf_enqueue_scripts() {
    $path = plugins_url( 'wp-user-frontend' );

    if ( has_shortcode( 'wpuf_addpost' ) || has_shortcode( 'wpuf_edit' ) ) {
        wp_enqueue_style( 'wpuf', $path . '/css/wpuf.css' );
        wp_enqueue_style( 'wpuf-pagination', $path . '/css/pagination.css' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wpuf', $path . '/js/wpuf.js' );
    }
}

add_action( 'wp_enqueue_scripts', 'wpuf_enqueue_scripts' );

/**
 * Format error message
 *
 * @param array $error_msg
 * @return string
 */
function wpuf_error_msg( $error_msg ) {
    $msg_string = '';
    foreach ($error_msg as $value) {
        if ( !empty( $value ) ) {
            $msg_string = $msg_string . '<div class="error">' . $msg_string = $value . '</div>';
        }
    }
    return $msg_string;
}

// for the price field to make only numbers, periods, and commas
function wpuf_clean_tags( $string ) {
    $string = preg_replace( '/\s*,\s*/', ',', rtrim( trim( $string ), ' ,' ) );
    return $string;
}

/**
 * Validates any integer variable and sanitize
 *
 * @param int $int
 * @return intger
 */
function wpuf_is_valid_int( $int ) {
    $int = isset( $int ) ? intval( $int ) : 0;
    return $int;
}

/**
 * Notify the admin for new post
 *
 * @param object $userdata
 * @param int $post_id
 */
function wpuf_notify_post_mail( $user, $post_id ) {
    $blogname = get_bloginfo( 'name' );
    $to = get_bloginfo( 'admin_email' );
    $permalink = get_permalink( $post_id );

    $headers = sprintf( "From: %s <%s>\r\n", $blogname, $to );
    $subject = "[$blogname]New Post Submission";
    $msg = "There is a new post ($permalink) submitted in $blogname by '{$user->display_name}'. Visit " . admin_url( 'edit.php' ) . " to take action";

    wp_mail( $to, $subject, $msg, $headers );
    //var_dump($headers, $subject, $msg, $receiver);
}

/**
 * Adds/Removes mime types to wordpress
 *
 * @param array $mime original mime types
 * @return array modified mime types
 */
function wpuf_mime( $mime ) {
    $unset = array('exe', 'swf', 'tsv', 'wp|wpd', 'onetoc|onetoc2|onetmp|onepkg', 'class', 'htm|html', 'mdb', 'mpp');

    foreach ($unset as $val) {
        unset( $mime[$val] );
    }

    return $mime;
}

add_filter( 'upload_mimes', 'wpuf_mime' );

/**
 * Upload the files to the post as attachemnt
 *
 * @param <type> $post_id
 */
function wpuf_upload_attachment( $post_id ) {
    include_once (ABSPATH . 'wp-admin/includes/file.php');
    include_once (ABSPATH . 'wp-admin/includes/image.php');

    $override = array('test_form' => false);

    $fields = (int) get_option( 'wpuf_attachment_num' );
    for ($i = 0; $i < $fields; $i++) {
        $file_name = basename( $_FILES['wpuf_post_attachments']['name'][$i] );
        $file_type = wp_check_filetype( $file_name );

        if ( $file_name ) {
            $upload = array(
                'name' => $_FILES['wpuf_post_attachments']['name'][$i],
                'type' => $_FILES['wpuf_post_attachments']['type'][$i],
                'tmp_name' => $_FILES['wpuf_post_attachments']['tmp_name'][$i],
                'error' => $_FILES['wpuf_post_attachments']['error'][$i],
                'size' => $_FILES['wpuf_post_attachments']['size'][$i]
            );

            $uploaded_file = wp_handle_upload( $upload, $override );

            // If the wp_handle_upload call returned a local path for the image
            if ( isset( $uploaded_file['file'] ) ) {
                $file_loc = $uploaded_file['file'];

                $attachment = array(
                    'post_mime_type' => $file_type['type'],
                    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment( $attachment, $file_loc, $post_id );

                $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                set_post_thumbnail( $post_id, $attach_id );
            }//end file upload
        }//file exists
    }// end for
}

/**
 * Checks the submitted files if has any errors
 *
 * @return array error list
 */
function wpuf_check_upload() {
    $errors = array();
    $mime = get_allowed_mime_types();

    $size_limit = (int) (get_option( 'wpuf_attachment_max_size' ) * 1024);
    $fields = (int) get_option( 'wpuf_attachment_num' );

    for ($i = 0; $i < $fields; $i++) {
        $tmp_name = basename( $_FILES['wpuf_post_attachments']['tmp_name'][$i] );
        $file_name = basename( $_FILES['wpuf_post_attachments']['name'][$i] );

        //if file is uploaded
        if ( $file_name ) {
            $attach_type = wp_check_filetype( $file_name );
            $attach_size = $_FILES['wpuf_post_attachments']['size'][$i];

            //check file size
            if ( $attach_size > $size_limit ) {
                $errors[] = __( "Attachment file is too big" );
            }

            //check file type
            if ( !in_array( $attach_type['type'], $mime ) ) {
                $errors[] = __( "Invalid attachment file type" );
            }
        } // if $filename
    }// endfor

    return $errors;
}

/**
 * Get the attachments of a post
 *
 * @param int $post_id
 * @return array attachment list
 */
function wpfu_get_attachments( $post_id ) {
    $att_list = array();

    $args = array(
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_id,
        'order' => 'ASC',
        'orderby' => 'ID'
    );

    $attachments = get_posts( $args );

    foreach ($attachments as $attachment) {
        $att_list[] = array(
            'id' => $attachment->ID,
            'title' => $attachment->post_title,
            'url' => wp_get_attachment_url( $attachment->ID ),
            'mime' => $attachment->post_mime_type
        );
    }

    return $att_list;
}

/**
 * Prints the attachment files to the post content
 *
 * @global <type> $post
 * @param string $content original post content
 * @return string modified post content
 */
function wpuf_add_attachment_to_post( $content ) {
    global $post;

    $attach = wpfu_get_attachments( $post->ID );
    //var_dump( $attach );

    if ( $attach ) {
        $count = 1;
        foreach ($attach as $a) {
            $text .= 'Attachment ' . $count . ': <a href="' . $a['url'] . '">' . $a['title'] . '</a><br>';
            $count++;
        }
        return $content . $text;
    }

    return $content;
}

//add_filter('the_content', 'wpuf_add_attachment_to_post');


function wpuf_edit_attachment( $post_id ) {
    $attach = wpfu_get_attachments( $post_id );

    if ( $attach ) {
        $count = 1;
        foreach ($attach as $a) {
            $param = "?pid={$post_id}&attach_id={$a['id']}&action=del";
            $text .= 'Attachment ' . $count . ': <a href="' . $a['url'] . '">' . $a['title'] . '</a>';
            $text .= ' - <a href="' . wp_nonce_url( $param, 'wpuf_attach_del' ) . '" onclick="return confirm(\'Are you sure to delete this attachment?\');">Delete</a><br>';
            $count++;
        }
        echo $text;
    }
}

function wpuf_attachment_fields() {
    if ( get_option( 'wpuf_allow_attachments' ) == 'yes' ) {
        $fields = (int) get_option( 'wpuf_attachment_num' );

        for ($i = 0; $i < $fields; $i++) {
            ?>

            <li>
                <label for="wpuf_post_attachments">
                    Attachment <?php echo $i + 1; ?>:
                </label>
                <input type="file" name="wpuf_post_attachments[]">
                <div class="clear"></div>
            </li>

            <?php
        }
    }
}

/**
 * Let the subscribers to upload files from the admin
 *
 * @package WP User Frontend
 * @author Tareq Hasan
 */
function wpuf_let_upload() {
    if ( !current_user_can( 'edit_posts' ) ) {
        $subs = get_role( 'subscriber' );
        //$subs->add_cap( 'upload_files' );
    }
}

/**
 * Remove the mdedia upload tabs from subscribers
 *
 * @package WP User Frontend
 * @author Tareq Hasan
 */
function wpuf_unset_media_tab( $list ) {
    if ( !current_user_can( 'edit_posts' ) ) {
        unset( $list['library'] );
        unset( $list['gallery'] );
    }

    return $list;
}

add_filter( 'media_upload_tabs', 'wpuf_unset_media_tab' );

//add_action( 'init', 'wpuf_let_upload' );

function wpuf_performance() {

    $stat = sprintf( '%d queries in %.3f seconds, using %.2fMB memory', get_num_queries(), timer_stop( 0, 3 ), memory_get_peak_usage() / 1024 / 1024 );

    echo $stat;
}

//add_action( 'wp_footer', 'wpuf_performance');

/**
 * Get the registered post types
 *
 * @return array
 */
function wpuf_get_post_types() {
    $post_types = get_post_types();

    foreach ($post_types as $key => $val) {
        if ( $val == 'attachment' || $val == 'revision' || $val == 'nav_menu_item' ) {
            unset( $post_types[$key] );
        }
    }

    //insert the custom post types
    $cus_post_type = get_option( 'wpuf_post_types' );
    if ( $cus_post_type ) {
        $cus_post_type = explode( ',', $cus_post_type );

        foreach ($cus_post_type as $cus_type) {
            $post_types[$cus_type] = $cus_type;
        }
    }

    return $post_types;
}

function wpuf_get_cats() {
    $cats = get_categories();

    $list = array();

    if ( $cats ) {
        foreach ($cats as $cat) {
            $list[$cat->cat_ID] = $cat->name;
        }
    }

    return $list;
}

/**
 * Get lists of users from database
 *
 * @return array
 */
function wpuf_list_users() {
    if ( function_exists( 'get_users' ) ) {
        $users = get_users();
    } else {
        ////wp 3.1 fallback
        $users = get_users_of_blog();
    }

    $list = array();

    if ( $users ) {
        foreach ($users as $user) {
            $list[$user->ID] = $user->display_name;
        }
    }

    return $list;
}

/**
 * Find the string that starts with defined word
 *
 * @param string $string
 * @param string $starts
 * @return boolean
 */
function wpuf_starts_with( $string, $starts ) {

    $flag = strncmp( $string, $starts, strlen( $starts ) );

    if ( $flag == 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * check the current post for the existence of a short code
 *
 * @link http://wp.tutsplus.com/articles/quick-tip-improving-shortcodes-with-the-has_shortcode-function/
 * @param string $shortcode
 * @return boolean
 */
function has_shortcode( $shortcode = '', $post_id = false ) {
    global $post;

    $post_to_check = ( $post_id == false ) ? get_post( get_the_ID() ) : get_post( $post_id );

    if ( !$post_to_check ) {
        return false;
    }

    // false because we have to search through the post content first
    $found = false;

    // if no short code was provided, return false
    if ( !$shortcode ) {
        return $found;
    }
    // check the post content for the short code
    if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
        // we have found the short code
        $found = true;
    }

    // return our final results
    return $found;
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
function wpuf_dropdown_page( $args = '' ) {
    global $wpdb;

    $array = array();
    $pages = get_pages();
    if ( $pages ) {
        foreach ($pages as $page) {
            //var_dump( $page );
            $array[$page->ID] = $page->post_title;
        }
    }

    return $array;
}