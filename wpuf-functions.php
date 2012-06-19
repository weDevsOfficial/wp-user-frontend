<?php

/**
 * Start output buffering
 *
 * This is needed for redirecting to post when a new post has made
 *
 * @since 0.8
 */
function wpuf_buffer_start() {
    ob_start();
}

add_action( 'init', 'wpuf_buffer_start' );

/**
 * If the user isn't logged in, redirect
 * to the login page
 *
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_auth_redirect_login() {
    $user = wp_get_current_user();

    if ( $user->ID == 0 ) {
        nocache_headers();
        wp_redirect( get_option( 'siteurl' ) . '/wp-login.php?redirect_to=' . urlencode( $_SERVER['REQUEST_URI'] ) );
        exit();
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
    $subject = sprintf( __( '[%s] New Post Submission' ), $blogname );

    $msg = sprintf( __( 'A new post has been submitted on %s' ), $blogname ) . "\r\n\r\n";
    $msg .= sprintf( __( 'Author : %s' ), $user->display_name ) . "\r\n";
    $msg .= sprintf( __( 'Author Email : %s' ), $user->user_email ) . "\r\n";
    $msg .= sprintf( __( 'Title : %s' ), get_the_title( $post_id ) ) . "\r\n";
    $msg .= sprintf( __( 'Permalink : %s' ), $permalink ) . "\r\n";
    $msg .= sprintf( __( 'Edit Link : %s' ), admin_url( 'post.php?action=edit&post=' . $post_id ) ) . "\r\n";

    //plugin api
    $to = apply_filters( 'wpuf_notify_to', $to );
    $subject = apply_filters( 'wpuf_notify_subject', $subject );
    $msg = apply_filters( 'wpuf_notify_message', $msg );

    wp_mail( $to, $subject, $msg, $headers );
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
    if ( !isset( $_FILES['wpuf_post_attachments'] ) ) {
        return false;
    }

    $fields = (int) wpuf_get_option( 'attachment_num' );

    for ($i = 0; $i < $fields; $i++) {
        $file_name = basename( $_FILES['wpuf_post_attachments']['name'][$i] );

        if ( $file_name ) {
            if ( $file_name ) {
                $upload = array(
                    'name' => $_FILES['wpuf_post_attachments']['name'][$i],
                    'type' => $_FILES['wpuf_post_attachments']['type'][$i],
                    'tmp_name' => $_FILES['wpuf_post_attachments']['tmp_name'][$i],
                    'error' => $_FILES['wpuf_post_attachments']['error'][$i],
                    'size' => $_FILES['wpuf_post_attachments']['size'][$i]
                );

                wpuf_upload_file( $upload );
            }//file exists
        }// end for
    }
}

/**
 * Generic function to upload a file
 *
 * @since 0.8
 * @param string $field_name file input field name
 * @return bool|int attachment id on success, bool false instead
 */
function wpuf_upload_file( $upload_data ) {

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

        return $attach_id;
    }

    return false;
}

/**
 * Checks the submitted files if has any errors
 *
 * @return array error list
 */
function wpuf_check_upload() {
    $errors = array();
    $mime = get_allowed_mime_types();

    $size_limit = (int) (wpuf_get_option( 'attachment_max_size' ) * 1024);
    $fields = (int) wpuf_get_option( 'attachment_num' );

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
        'orderby' => 'menu_order'
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
 * Attachments preview on edit page
 *
 * @param int $post_id
 */
function wpuf_edit_attachment( $post_id ) {
    $attach = wpfu_get_attachments( $post_id );

    if ( $attach ) {
        $count = 1;
        foreach ($attach as $a) {

            echo 'Attachment ' . $count . ': <a href="' . $a['url'] . '">' . $a['title'] . '</a>';
            echo "<form name=\"wpuf_edit_attachment\" id=\"wpuf_edit_attachment_{$post_id}\" action=\"\" method=\"POST\">";
            echo "<input type=\"hidden\" name=\"attach_id\" value=\"{$a['id']}\" />";
            echo "<input type=\"hidden\" name=\"action\" value=\"del\" />";
            wp_nonce_field( 'wpuf_attach_del' );
            echo '<input class="wpuf_attachment_delete" type="submit" name="wpuf_attachment_delete" value="delete" onclick="return confirm(\'Are you sure to delete this attachment?\');">';
            echo "</form>";
            echo "<br>";
            $count++;
        }
    }
}

function wpuf_attachment_fields( $edit = false, $post_id = false ) {
    if ( wpuf_get_option( 'allow_attachment' ) == 'yes' ) {
        $fields = (int) wpuf_get_option( 'attachment_num' );

        if ( $edit && $post_id ) {
            $fields = abs( $fields - count( wpfu_get_attachments( $post_id ) ) );
        }

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

    return $post_types;
}

function wpuf_get_cats() {
    $cats = get_categories( array('hide_empty' => false) );

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

    if ( !$post ) {
        return false;
    }

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

    return $found;
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @return string HTML content, if not displaying.
 */
function wpuf_get_pages() {
    global $wpdb;

    $array = array();
    $pages = get_pages();
    if ( $pages ) {
        foreach ($pages as $page) {
            $array[$page->ID] = $page->post_title;
        }
    }

    return $array;
}

/**
 * Get all the payment gateways
 *
 * @return array
 */
function wpuf_get_gateways( $context = 'admin' ) {
    $gateways = WPUF_Payment::get_payment_gateways();
    $return = array();

    foreach ($gateways as $id => $gate) {
        if ( $context == 'admin' ) {
            $return[$id] = $gate['admin_label'];
        } else {
            $return[$id] = $gate['checkout_label'];
        }
    }

    return $return;
}

/**
 * Edit post link for frontend
 *
 * @since 0.7
 * @param string $url url of the original post edit link
 * @param int $post_id
 * @return string url of the current edit post page
 */
function wpuf_edit_post_link( $url, $post_id ) {
    if ( is_admin() ) {
        return $url;
    }

    $override = wpuf_get_option( 'override_editlink', 'yes' );
    if ( $override == 'yes' ) {
        $url = '';
        if ( wpuf_get_option( 'enable_post_edit' ) == 'yes' ) {
            $edit_page = (int) wpuf_get_option( 'edit_page_id' );
            $url = get_permalink( $edit_page );

            $url = wp_nonce_url( $url . '?pid=' . $post_id, 'wpuf_edit' );
        }
    }

    return $url;
}

add_filter( 'get_edit_post_link', 'wpuf_edit_post_link', 10, 2 );

/**
 * Shows the custom field data and attachments to the post
 *
 * @since 0.7
 *
 * @global object $wpdb
 * @global object $post
 * @param string $content
 * @return string
 */
function wpuf_show_meta_front( $content ) {
    global $wpdb, $post;

    //check, if custom field is enabled
    $enabled = wpuf_get_option( 'enable_custom_field' );
    $show_custom = wpuf_get_option( 'cf_show_front' );
    $show_attachment = wpuf_get_option( 'att_show_front' );

    if ( $enabled == 'on' && $show_custom == 'on' ) {
        $extra = '';
        $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_customfields ORDER BY `region` DESC", OBJECT );
        if ( $wpdb->num_rows > 0 ) {
            $extra .= '<ul class="wpuf_customs">';
            foreach ($fields as $field) {
                $meta = get_post_meta( $post->ID, $field->field, true );
                if ( $meta ) {
                    $extra .= sprintf( '<li><label>%s</label> : %s</li>', $field->label, make_clickable( $meta ) );
                }
            }
            $extra .= '<ul>';

            $content .= $extra;
        }
    }

    if ( $show_attachment == 'on' ) {
        $attach = '';
        $attachments = wpfu_get_attachments( $post->ID );

        if ( $attachments ) {
            $attach = '<ul class="wpuf-attachments">';

            foreach ($attachments as $file) {

                //if the attachment is image, show the image. else show the link
                if ( wpuf_is_file_image( $file['url'], $file['mime'] ) ) {
                    $thumb = wp_get_attachment_image_src( $file['id'] );
                    $attach .= sprintf( '<li><a href="%s"><img src="%s" alt="%s" /></a></li>', $file['url'], $thumb[0], esc_attr( $file['title'] ) );
                } else {
                    $attach .= sprintf( '<li><a href="%s" title="%s">%s</a></li>', $file['url'], esc_attr( $file['title'] ), $file['title'] );
                }
            }

            $attach .= '</ul>';
        }

        if ( $attach ) {
            $content .= $attach;
        }
    }

    return $content;
}

add_filter( 'the_content', 'wpuf_show_meta_front' );

/**
 * Check if the file is a image
 *
 * @since 0.7
 *
 * @param string $file url of the file to check
 * @param string $mime mime type of the file
 * @return bool
 */
function wpuf_is_file_image( $file, $mime ) {
    $ext = preg_match( '/\.([^.]+)$/', $file, $matches ) ? strtolower( $matches[1] ) : false;

    $image_exts = array('jpg', 'jpeg', 'gif', 'png');

    if ( 'image/' == substr( $mime, 0, 6 ) || $ext && 'import' == $mime && in_array( $ext, $image_exts ) ) {
        return true;
    }

    return false;
}

/**
 * Displays attachment information upon upload as featured image
 *
 * @since 0.8
 * @param int $attach_id attachment id
 * @return string
 */
function wpuf_feat_img_html( $attach_id ) {
    $image = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
    $post = get_post( $attach_id );

    $html = sprintf( '<div class="wpuf-item" id="attachment-%d">', $attach_id );
    $html .= sprintf( '<img src="%s" alt="%s" />', $image[0], esc_attr( $post->post_title ) );
    $html .= sprintf( '<a class="wpuf-del-ft-image button" href="#" data-id="%d">%s</a> ', $attach_id, __( 'Remove Image', 'wpuf' ) );
    $html .= sprintf( '<input type="hidden" name="wpuf_featured_img" value="%d" />', $attach_id );
    $html .= '</div>';

    return $html;
}

/**
 * Category checklist walker
 *
 * @since 0.8
 */
class WPUF_Walker_Category_Checklist extends Walker {

    var $tree_type = 'category';
    var $db_fields = array('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

    function start_lvl( &$output, $depth, $args ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent<ul class='children'>\n";
    }

    function end_lvl( &$output, $depth, $args ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent</ul>\n";
    }

    function start_el( &$output, $category, $depth, $args ) {
        extract( $args );
        if ( empty( $taxonomy ) )
            $taxonomy = 'category';

        if ( $taxonomy == 'category' )
            $name = 'category';
        else
            $name = 'tax_input[' . $taxonomy . ']';

        $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
        $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
    }

    function end_el( &$output, $category, $depth, $args ) {
        $output .= "</li>\n";
    }

}

/**
 * Displays checklist of a taxonomy
 *
 * @since 0.8
 * @param int $post_id
 * @param array $selected_cats
 */
function wpuf_category_checklist( $post_id = 0, $selected_cats = false, $tax = 'category' ) {
    require_once ABSPATH . '/wp-admin/includes/template.php';

    $walker = new WPUF_Walker_Category_Checklist();

    echo '<ul class="wpuf-category-checklist">';
    wp_terms_checklist( $post_id, array(
        'taxonomy' => $tax,
        'descendants_and_self' => 0,
        'selected_cats' => $selected_cats,
        'popular_cats' => false,
        'walker' => $walker,
        'checked_ontop' => false
    ) );
    echo '</ul>';
}

// display msg if permalinks aren't setup correctly
function wpuf_permalink_nag() {

    if ( current_user_can( 'manage_options' ) )
        $msg = sprintf( __( 'You need to set your <a href="%1$s">permalink custom structure</a> to at least contain <b>/&#37;postname&#37;/</b> before WP User Frontend will work properly.', 'wpuf' ), 'options-permalink.php' );

    echo "<div class='error fade'><p>$msg</p></div>";
}

//if not found %postname%, shows a error msg at admin panel
if ( !stristr( get_option( 'permalink_structure' ), '%postname%' ) ) {
    add_action( 'admin_notices', 'wpuf_permalink_nag', 3 );
}

function wpuf_option_values() {
    global $custom_fields;

    wpuf_value_travarse( $custom_fields );
}

function wpuf_value_travarse( $param ) {
    foreach ($param as $key => $value) {
        if ( $value['name'] ) {
            echo '"' . $value['name'] . '" => "' . get_option( $value['name'] ) . '"<br>';
        }
    }
}

//wpuf_option_values();

function wpuf_get_custom_fields() {
    global $wpdb;

    $data = array();

    $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_customfields", OBJECT );
    if ( $wpdb->num_rows > 0 ) {
        foreach ($fields as $f) {
            $data[] = array(
                'label' => $f->label,
                'field' => $f->field,
                'type' => $f->required
            );
        }

        return $data;
    }

    return false;
}

/**
 * Adds notices on add post form if any
 *
 * @param string $text
 * @return string
 */
function wpuf_addpost_notice( $text ) {
    $user = wp_get_current_user();

    if ( is_user_logged_in() ) {
        $lock = ( $user->wpuf_postlock == 'yes' ) ? 'yes' : 'no';

        if ( $lock == 'yes' ) {
            return $user->wpuf_lock_cause;
        }

        $force_pack = wpuf_get_option( 'force_pack' );
        $post_count = (isset( $user->wpuf_sub_pcount )) ? intval( $user->wpuf_sub_pcount ) : 0;

        if ( $force_pack == 'yes' && $post_count == 0 ) {
            return __( 'You must purchase a pack before posting', 'wpuf' );
        }
    }

    return $text;
}

add_filter( 'wpuf_addpost_notice', 'wpuf_addpost_notice' );

/**
 * Adds the filter to the add post form if the user can post or not
 *
 * @param string $perm permission type. "yes" or "no"
 * @return string permission type. "yes" or "no"
 */
function wpuf_can_post( $perm ) {
    $user = wp_get_current_user();

    if ( is_user_logged_in() ) {
        $lock = ( $user->wpuf_postlock == 'yes' ) ? 'yes' : 'no';

        if ( $lock == 'yes' ) {
            return 'no';
        }

        $force_pack = wpuf_get_option( 'force_pack' );
        $post_count = (isset( $user->wpuf_sub_pcount )) ? intval( $user->wpuf_sub_pcount ) : 0;

        if ( $force_pack == 'yes' && $post_count == 0 ) {
            return 'no';
        }
    }

    return $perm;
}

add_filter( 'wpuf_can_post', 'wpuf_can_post' );

function wpuf_header_css() {
    $css = wpuf_get_option( 'custom_css' );
    ?>
    <style type="text/css">
        ul.wpuf-attachments{ list-style: none; overflow: hidden;}
        ul.wpuf-attachments li {float: left; margin: 0 10px 10px 0;}
        <?php echo $css; ?>
    </style>
    <?php
}

add_action( 'wp_head', 'wpuf_header_css' );

/**
 * Get all the image sizes
 *
 * @return array image sizes
 */
function wpuf_get_image_sizes() {
    $image_sizes_orig = get_intermediate_image_sizes();
    $image_sizes_orig[] = 'full';
    $image_sizes = array();

    foreach ($image_sizes_orig as $size) {
        $image_sizes[$size] = $size;
    }

    return $image_sizes;
}