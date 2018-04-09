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

    } else if ( $status == 'private' ) {
        $title = __( 'Private', 'wpuf' );
        $fontcolor = '#bbbbbb';
    }

    $show_status = '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
    echo apply_filters( 'wpuf_show_post_status', $show_status, $status );
}

/**
 * Format the post status for user dashboard
 *
 * @param string $status
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_admin_post_status( $status ) {

    if ( $status == 'publish' ) {

        $title = __( 'Published', 'wpuf' );
        $fontcolor = '#009200';
    } else if ( $status == 'draft' || $status == 'private' ) {

        $title = __( 'Draft', 'wpuf' );
        $fontcolor = '#bbbbbb';
    } else if ( $status == 'pending' ) {

        $title = __( 'Pending', 'wpuf' );
        $fontcolor = '#C00202';
    } else if ( $status == 'future' ) {
        $title = __( 'Scheduled', 'wpuf' );
        $fontcolor = '#bbbbbb';
    }

    echo '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
}

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
 * Get the attachments of a post
 *
 * @param int $post_id
 * @return array attachment list
 */
function wpfu_get_attachments( $post_id ) {
    $att_list = array();

    $args = array(
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_id,
        'order'       => 'ASC',
        'orderby'     => 'menu_order'
    );

    $attachments = get_posts( $args );

    foreach ($attachments as $attachment) {
        $att_list[] = array(
            'id'    => $attachment->ID,
            'title' => $attachment->post_title,
            'url'   => wp_get_attachment_url( $attachment->ID ),
            'mime'  => $attachment->post_mime_type
        );
    }

    return $att_list;
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
function wpuf_get_post_types( $args = array() ) {
    $defaults = array();

    $args = wp_parse_args( $args, $defaults );

    $post_types = get_post_types( $args );

    $ignore_post_types = array(
        'attachment', 'revision', 'nav_menu_item'
    );

    foreach ( $post_types as $key => $val ) {
        if ( in_array( $val, $ignore_post_types ) ) {
            unset( $post_types[$key] );
        }
    }

    return apply_filters( 'wpuf-get-post-types', $post_types );
}

/**
 * Get lists of users from database
 *
 * @return array
 */
function wpuf_list_users() {
    global $wpdb;

    $users = $wpdb->get_results( "SELECT ID, user_login from $wpdb->users" );

    $list = array();

    if ( $users ) {
        foreach ($users as $user) {
            $list[$user->ID] = $user->user_login;
        }
    }

    return $list;
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @return string HTML content, if not displaying.
 */
function wpuf_get_pages( $post_type = 'page' ) {
    global $wpdb;

    $array = array( '' => __( '-- select --', 'wpuf' ) );
    $pages = get_posts( array('post_type' => $post_type, 'numberposts' => -1) );
    if ( $pages ) {
        foreach ($pages as $page) {
            $array[$page->ID] = esc_attr( $page->post_title );
        }
    }

    return $array;
}

/**
 * Edit post link for frontend
 *
 * @since 0.7
 * @param string $url url of the original post edit link
 * @param int $post_id
 * @return string url of the current edit post page
 */
function wpuf_override_admin_edit_link( $url, $post_id ) {
    if ( is_admin() ) {
        return $url;
    }

    $override = wpuf_get_option( 'override_editlink', 'wpuf_general', 'no' );

    if ( $override == 'yes' ) {
        $url = '';

        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
            $edit_page = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
            $url = get_permalink( $edit_page );

            $url = wp_nonce_url( $url . '?pid=' . $post_id, 'wpuf_edit' );
        }
    }

    return apply_filters( 'wpuf_front_post_edit_link', $url );
}

add_filter( 'get_edit_post_link', 'wpuf_override_admin_edit_link', 10, 2 );

/**
 * Create HTML dropdown list of Categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class WPUF_Walker_Category_Multi extends Walker {

    /**
     * @see Walker::$tree_type
     * @var string
     */
    var $tree_type = 'category';

    /**
     * @see Walker::$db_fields
     * @var array
     */
    var $db_fields = array('parent' => 'parent', 'id' => 'term_id');

    /**
     * @see Walker::start_el()
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int $depth Depth of category. Used for padding.
     * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
     */
    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $pad = str_repeat( '&nbsp;', $depth * 3 );

        $cat_name = apply_filters( 'list_cats', $category->name, $category );
        $output .= "\t<option class=\"level-$depth\" value=\"" . $category->term_id . "\"";
        if ( in_array( $category->term_id, $args['selected'] ) )
            $output .= ' selected="selected"';
        $output .= '>';
        $output .= $pad . $cat_name;
        if ( $args['show_count'] )
            $output .= '&nbsp;&nbsp;(' . $category->count . ')';
        $output .= "</option>\n";
    }

}

/**
 * Category checklist walker
 *
 * @since 0.8
 */
class WPUF_Walker_Category_Checklist extends Walker {

    var $tree_type = 'category';
    var $db_fields = array('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent<ul class='children'>\n";
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent</ul>\n";
    }

    function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
        extract( $args );
        if ( empty( $taxonomy ) )
            $taxonomy = 'category';

        if ( $taxonomy == 'category' )
            $name = 'category';
        else
            $name = $taxonomy;

        if ( 'yes' === $show_inline ) {
            $inline_class = 'wpuf-checkbox-inline';
        } else {
            $inline_class = '';
        }

        $class = isset( $args['class'] ) ? $args['class'] : '';
        $output .= "\n<li class='" . $inline_class . "' id='{$taxonomy}-{$category->term_id}'>" . '<label class="selectit"><input class="'. $class . '" value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
    }

    function end_el( &$output, $category, $depth = 0, $args = array() ) {
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
function wpuf_category_checklist( $post_id = 0, $selected_cats = false, $attr = array(), $class = null ) {
    require_once ABSPATH . '/wp-admin/includes/template.php';

    $walker       = new WPUF_Walker_Category_Checklist();

    $exclude_type = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
    $exclude      = $attr['exclude'];
    
    if ( $exclude_type == 'child_of' ) {
      $exclude = $exclude[0];
    }

    $tax          = $attr['name'];
    $current_user = get_current_user_id();

    if ( $post_id ) {
        $args['selected_cats'] = wp_get_object_terms( $post_id, $tax, array('fields' => 'ids') );
    } elseif ( $selected_cats ) {
        $args['selected_cats'] = $selected_cats;
    } else {
        $args['selected_cats'] = array();
    }

    $args['show_inline'] = $attr['show_inline'];

    $args['class'] = $class;

    $tax_args = array(
        'taxonomy' => $tax,
        'hide_empty'  => false,
        $exclude_type => $exclude,
        'orderby'     => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
        'order'       => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
    );
    $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

    $categories = (array) get_terms( $tax_args );

    echo '<ul class="wpuf-category-checklist">';
    printf( '<input type="hidden" name="%s" value="0" />', $tax );
    echo call_user_func_array( array(&$walker, 'walk'), array($categories, 0, $args) );
    echo '</ul>';
}

function wpuf_pre($data) {
    echo '<pre>'; print_r( $data ); echo '</pre>';
}

/**
 * Get all the image sizes
 *
 * @return array image sizes
 */
function wpuf_get_image_sizes() {
    $image_sizes_orig   = get_intermediate_image_sizes();
    $image_sizes_orig[] = 'full';
    $image_sizes        = array();

    foreach ($image_sizes_orig as $size) {
        $image_sizes[$size] = $size;
    }

    return $image_sizes;
}

function wpuf_allowed_extensions() {
    $extesions = array(
        'images' => array('ext' => 'jpg,jpeg,gif,png,bmp', 'label' => __( 'Images', 'wpuf' )),
        'audio'  => array('ext' => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi', 'label' => __( 'Audio', 'wpuf' )),
        'video'  => array('ext' => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe', 'label' => __( 'Videos', 'wpuf' )),
        'pdf'    => array('ext' => 'pdf', 'label' => __( 'PDF', 'wpuf' )),
        'office' => array('ext' => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt', 'label' => __( 'Office Documents', 'wpuf' )),
        'zip'    => array('ext' => 'zip,gz,gzip,rar,7z', 'label' => __( 'Zip Archives', 'wpuf' )),
        'exe'    => array('ext' => 'exe', 'label' => __( 'Executable Files', 'wpuf' )),
        'csv'    => array('ext' => 'csv', 'label' => __( 'CSV', 'wpuf' ))
    );

    return apply_filters( 'wpuf_allowed_extensions', $extesions );
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
    }

    return $text;
}

add_filter( 'wpuf_addpost_notice', 'wpuf_addpost_notice' );


/**
 * Associate attachemnt to a post
 *
 * @since 2.0
 *
 * @param type $attachment_id
 * @param type $post_id
 */
function wpuf_associate_attachment( $attachment_id, $post_id ) {
    $args = array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    );
    wpuf_update_post( $args );
}

/**
 * Update post when hooked to save_post
 *
 * @since 2.5.4
 *
 * @param array args
 */
function wpuf_update_post( $args ) {
    if ( ! wp_is_post_revision( $args['ID'] ) ){
        // unhook this function so it doesn't loop infinitely
        remove_action( 'save_post', array( WPUF_Admin_Posting::init(), 'save_meta' ), 1 );

        // update the post, which calls save_post again
        wp_update_post( $args );

        // re-hook this function
        add_action( 'save_post', array( WPUF_Admin_Posting::init(), 'save_meta' ), 1 );
    }
}

/**
 * Get user role names
 *
 * @since 2.0
 *
 * @global WP_Roles $wp_roles
 * @return array
 */
function wpuf_get_user_roles() {
    global $wp_roles;

    if ( !isset( $wp_roles ) )
        $wp_roles = new WP_Roles();

    return $wp_roles->get_names();
}

/**
 * User avatar wrapper for custom uploaded avatar
 *
 * @since 2.0
 *
 * @param string $avatar
 * @param mixed $id_or_email
 * @param int $size
 * @param string $default
 * @param string $alt
 * @return string image tag of the user avatar
 */
function wpuf_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

    if ( is_numeric( $id_or_email ) ) {
        $user = get_user_by( 'id', $id_or_email );
    } elseif ( is_object( $id_or_email ) ) {
        if ( $id_or_email->user_id != '0' ) {
            $user = get_user_by( 'id', $id_or_email->user_id );
        } else {
            return $avatar;
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( !$user ) {
        return $avatar;
    }

    // see if there is a user_avatar meta field
    $user_avatar = get_user_meta( $user->ID, 'user_avatar', true );
    if ( empty( $user_avatar ) ) {
        return $avatar;
    }

    return sprintf( '<img src="%1$s" alt="%2$s" height="%3$s" width="%3$s" class="avatar">', esc_url( $user_avatar ), $alt, $size );
}

add_filter( 'get_avatar', 'wpuf_get_avatar', 99, 5 );

function wpuf_update_avatar( $user_id, $attachment_id ) {

    $upload_dir = wp_upload_dir();
    $relative_url = wp_get_attachment_url( $attachment_id );

    if ( function_exists( 'wp_get_image_editor' ) ) {
        // try to crop the photo if it's big
        $file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $relative_url );

        // as the image upload process generated a bunch of images
        // try delete the intermediate sizes.
        $ext = strrchr( $file_path, '.' );
        $file_path_w_ext = str_replace( $ext, '', $file_path );
        $small_url = $file_path_w_ext . '-avatar' . $ext;
        $relative_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $small_url );

        $editor = wp_get_image_editor( $file_path );

        if ( !is_wp_error( $editor ) ) {
            $avatar_size    = wpuf_get_option( 'avatar_size', 'wpuf_profile', '100x100' );
            $avatar_size    = explode( 'x', $avatar_size );
            $avatar_width   = $avatar_size[0];
            $avatar_height  = $avatar_size[1];

            $editor->resize( $avatar_width, $avatar_height, true );
            $editor->save( $small_url );

            // if the file creation successfull, delete the original attachment
            if ( file_exists( $small_url ) ) {
                wp_delete_attachment( $attachment_id, true );
            }
        }
    }

    // delete any previous avatar
    $prev_avatar = get_user_meta( $user_id, 'user_avatar', true );

    if ( !empty( $prev_avatar ) ) {
        $prev_avatar_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $prev_avatar );

        if ( file_exists( $prev_avatar_path ) ) {
            unlink( $prev_avatar_path );
        }
    }

    // now update new user avatar
    update_user_meta( $user_id, 'user_avatar', $relative_url );
}

function wpuf_admin_role() {
    return apply_filters( 'wpuf_admin_role', 'manage_options' );
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
            $return[$id] = array(
                'label' => $gate['checkout_label'],
                'icon' => isset( $gate['icon'] ) ? $gate['icon'] : ''
            );
        }
    }

    return $return;
}

/**
 * Show custom fields in post content area
 *
 * @global object $post
 * @param string $content
 * @return string
 */
function wpuf_show_custom_fields( $content ) {
    global $post;

    $show_custom  = wpuf_get_option( 'cf_show_front', 'wpuf_frontend_posting' );

    if ( $show_custom != 'on' ) {
        return $content;
    }

    $show_caption  = wpuf_get_option( 'image_caption', 'wpuf_frontend_posting' );
    $form_id       = get_post_meta( $post->ID, '_wpuf_form_id', true );
    $form_settings = wpuf_get_form_settings( $form_id );

    if ( !$form_id ) {
        return $content;
    }

    $html = '<ul class="wpuf_customs">';

    $form_vars = wpuf_get_form_fields( $form_id );
    $meta      = array();

    if ( $form_vars ) {
        foreach ($form_vars as $attr) {
            if ( isset( $attr['show_in_post'] ) && $attr['show_in_post'] == 'yes' ) {
                $meta[] = $attr;
            }
        }

        if ( !$meta ) {
            return $content;
        }

        foreach ($meta as $attr) {

            if ( !isset( $attr['name'] ) ) {
                $attr['name'] = $attr['input_type'];
            }

            $field_value = get_post_meta( $post->ID, $attr['name'] );

            $return_for_no_cond = 0;

            if ( isset ( $attr['wpuf_cond']['condition_status'] ) && $attr['wpuf_cond']['condition_status'] == 'yes' ) {

                foreach ( $attr['wpuf_cond']['cond_field'] as $field_key => $cond_field_name ) {

                    //check if the condintal field is a taxonomuy
                    if ( taxonomy_exists( $cond_field_name ) ) {
                        $post_terms = wp_get_post_terms( $post->ID , $cond_field_name, true );
                        $cond_field_value = array();

                        if ( is_array( $post_terms ) ) {
                            foreach( $post_terms as $term_key => $term_array ) {
                                $cond_field_value[] = $term_array->term_id;
                            }
                        }
                        //$cond_field_value = isset($post_terms[0]) ? $post_terms[0]->term_id : '';
                    } else {
                        $cond_field_value = get_post_meta( $post->ID, $cond_field_name, 'true' );
                    }

                    if ( isset( $attr['wpuf_cond']['cond_option'][$field_key] ) ) {

                        if ( is_array( $cond_field_value ) ) {

                            if ( !in_array( $attr['wpuf_cond']['cond_option'][$field_key], $cond_field_value ) ) {
                                $return_for_no_cond = 1;
                            }

                        } else {

                            if ( $attr['wpuf_cond']['cond_option'][$field_key] != $cond_field_value ) {
                                $return_for_no_cond = 1;
                            }
                        }

                    }
                }
            }

            if ( $return_for_no_cond == 1 ) {
                continue;
            }

            if ( !count( $field_value ) ) {
                continue;
            }

            if ( $attr['input_type'] == 'hidden' ) {
                continue;
            }

            switch ( $attr['input_type'] ) {
                case 'image_upload':
                case 'file_upload':

                    $image_html = '<li><label>' . $attr['label'] . ':</label> ';

                    if ( $field_value ) {

                        foreach ($field_value as $attachment_id) {
                            if ( $attr['input_type'] == 'image_upload' ) {
                                $thumb = wp_get_attachment_image( $attachment_id, 'thumbnail' );
                            } else {
                                $thumb = get_post_field( 'post_title', $attachment_id );
                            }

                            $full_size = wp_get_attachment_url( $attachment_id );

                            if( $thumb ) {
                                $image_html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );

                                if ( $show_caption == 'on' ) {
                                    $post_detail = get_post( $attachment_id );
                                    if( !empty( $post_detail->post_title ) ) {
                                        $image_html .= '<br /><label>' . __( 'Title', 'wpuf' ) . ':</label> <span class="image_title">' . esc_html( $post_detail->post_title ) . '</span>';
                                    }
                                    if( !empty( $post_detail->post_excerpt ) ) {
                                        $image_html .= '<br /><label>' . __( 'Caption', 'wpuf' ) . ':</label> <span class="image_caption">' . esc_html( $post_detail->post_excerpt ) . '</span>';
                                    }
                                    if( !empty( $post_detail->post_content ) ) {
                                        $image_html .= '<br /><label>' . __( 'Description', 'wpuf' ) . ':</label> <span class="image_description">' . esc_html( $post_detail->post_content ) . '</span>';
                                    }
                                }
                            }
                        }
                    }

                    $html .= $image_html . '</li>';
                    break;

                case 'map':
                    ob_start();
                    wpuf_shortcode_map_post($attr['name'], $post->ID);
                    $html .= ob_get_clean();
                    break;

                case 'address':
                    include_once dirname( __FILE__ ) . '/includes/countries.php';

                    $address_html = '';

                    if ( isset( $field_value[0] ) && is_array( $field_value[0] ) ) {

                        foreach ( $field_value[0] as $field_key => $value ) {

                            if ( $field_key == 'country_select' ) {
                                if ( isset ( $countries[$value] ) ) {
                                    $value = $countries[$value];
                                }
                            }
                            $address_html .= '<li><label>' . $attr['address'][$field_key]['label'] . ': </label> ';
                            $address_html .= ' '.$value.'</li>';
                        }

                    }

                    $html .= $address_html;
                    break;

                case 'repeat':
                    $value = get_post_meta( $post->ID, $attr['name'] );
                    $newvalue = array();

                    foreach ($value as $i => $str) {
                        if (preg_match('/[^\|\s]/', $str)) {
                            $newvalue[] = $str;
                        }
                    }

                    $new = implode( ', ', $newvalue );

                    if ( $new ) {
                        $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( $new ) );
                    }
                    break;

                case 'url':
                    $value = get_post_meta( $post->ID, $attr['name'] , true );
                    $open_in = $attr['open_window'] == 'same' ? '' : '_blank';
                    $link = sprintf( "<li><label>%s :</label><a href='%s' target = '%s'>%s</a>", $attr['label'], $value, $open_in, $value);
                    $html.= $link;
                    break;

                case 'date':
                    $value = get_post_meta( $post->ID, $attr['name'], true );
                    $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( $value ) );
                    break;

                default:
                    $value       = get_post_meta( $post->ID, $attr['name'] );
                    $filter_html = apply_filters( 'wpuf_custom_field_render', '', $value, $attr, $form_settings );
                    $separator   = '| ';

                    if ( !empty( $filter_html ) ) {
                        $html .= $filter_html;
                    } elseif ( is_serialized( $value[0] ) ) {
                        $new            = maybe_unserialize( $value[0] );
                        $modified_value = implode( $separator, $new );

                        if ( $modified_value ) {
                           $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( $modified_value ) );
                        }
                    } elseif ( ( $attr['input_type'] == 'checkbox' || $attr['input_type'] == 'multiselect' ) && is_array( $value ) ) {
                        $modified_value = implode( $separator, $value[0] );

                        if ( $modified_value ) {
                           $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( $modified_value ) );
                        }
                    } else {

                        $new = implode( ', ', $value );

                        if ( $new ) {
                            $html .= sprintf( '<li><label>%s</label>: %s</li>', $attr['label'], make_clickable( $new ) );
                        }
                    }

                    break;
            }
        }
    }

    $html .= '</ul>';

    return $content . $html;
}

add_filter( 'the_content', 'wpuf_show_custom_fields' );

/**
 * Map display shortcode
 *
 * @param string $meta_key
 * @param int $post_id
 * @param array $args
 */
function wpuf_shortcode_map( $location, $post_id = null, $args = array(), $meta_key = '' ) {
    if ( !wpuf()->is_pro() || !$location ) {
        return;
    }
    global $post;

    // compatibility
    if ( $post_id ) {
        wpuf_shortcode_map_post( $location, $post_id, $args );
        return;
    }

    $default = array('width' => 450, 'height' => 250, 'zoom' => 12);
    $args = wp_parse_args( $args, $default );

    list( $def_lat, $def_long ) = explode( ',', $location );
    $def_lat = $def_lat ? $def_lat : 0;
    $def_long = $def_long ? $def_long : 0;
    ?>

    <div class="google-map" style="margin: 10px 0; height: <?php echo $args['height']; ?>px; width: <?php echo $args['width']; ?>px;" id="wpuf-map-<?php echo $meta_key . $post->ID; ?>"></div>

    <script type="text/javascript">
        jQuery(function($){
            var curpoint = new google.maps.LatLng(<?php echo $def_lat; ?>, <?php echo $def_long; ?>);

            var gmap = new google.maps.Map( $('#wpuf-map-<?php echo $meta_key . $post->ID; ?>')[0], {
                center: curpoint,
                zoom: <?php echo $args['zoom']; ?>,
                mapTypeId: window.google.maps.MapTypeId.ROADMAP
            });

            var marker = new window.google.maps.Marker({
                position: curpoint,
                map: gmap,
                draggable: true
            });

        });
    </script>
    <?php
}

/**
 * Map shortcode for users
 *
 * @param string $meta_key
 * @param int $user_id
 * @param array $args
 */
function wpuf_shortcode_map_user( $meta_key, $user_id = null, $args = array() ) {
    $location = get_user_meta( $user_id, $meta_key, true );
    wpuf_shortcode_map( $location, null, $args, $meta_key );
}

/**
 * Map shortcode post posts
 *
 * @global object $post
 * @param string $meta_key
 * @param int $post_id
 * @param array $args
 */
function wpuf_shortcode_map_post( $meta_key, $post_id = null, $args = array() ) {
    global $post;

    if ( !$post_id ) {
        $post_id = $post->ID;
    }

    $location = get_post_meta( $post_id, $meta_key, true );
    wpuf_shortcode_map( $location, null, $args, $meta_key );
}

function wpuf_meta_shortcode( $atts ) {
    global $post;

    extract( shortcode_atts( array(
        'name' => '',
        'type' => 'normal',
        'size' => 'thumbnail',
        'height' => 250,
        'width' => 450,
        'zoom' => 12
    ), $atts ) );

    if ( empty( $name ) ) {
        return;
    }

    if ( $type == 'image' || $type == 'file' ) {
        $images = get_post_meta( $post->ID, $name );

        if ( $images ) {
            $html = '';
            foreach ($images as $attachment_id) {

                if ( $type == 'image' ) {
                    $thumb = wp_get_attachment_image( $attachment_id, $size );
                } else {
                    $thumb = get_post_field( 'post_title', $attachment_id );
                }

                $full_size = wp_get_attachment_url( $attachment_id );
                $html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
            }

            return $html;
        }

    } elseif ( $type == 'map' ) {
        ob_start();
        wpuf_shortcode_map( $name, $post->ID, array('width' => $width, 'height' => $height, 'zoom' => $zoom ) );
        return ob_get_clean();

    } elseif ( $type == 'repeat' ) {
        return implode( '; ', get_post_meta( $post->ID, $name ) );
    } elseif ( $type == 'normal' ) {
        return implode( ', ', get_post_meta( $post->ID, $name ) );
    } else {
        return make_clickable( implode( ', ', get_post_meta( $post->ID, $name ) ) );
    }
}

add_shortcode( 'wpuf-meta', 'wpuf_meta_shortcode' );


/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function wpuf_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

/**
 * check the current post for the existence of a short code
 *
 * @link http://wp.tutsplus.com/articles/quick-tip-improving-shortcodes-with-the-has_shortcode-function/
 * @param string $shortcode
 * @return boolean
 */
function wpuf_has_shortcode( $shortcode = '', $post_id = false ) {

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
 * Get attachment ID from a URL
 *
 * @since 2.1.8
 *
 * @link http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/ Original Implementation
 *
 * @global type $wpdb
 * @param type $attachment_url
 * @return type
 */
function wpuf_get_attachment_id_from_url( $attachment_url = '' ) {

    global $wpdb;
    $attachment_id = false;

    // If there is no url, return.
    if ( '' == $attachment_url )
        return;

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
    }

    return $attachment_id;
}

/**
 * Non logged in users tag autocomplete
 *
 * @since 2.1.9
 * @global object $wpdb
 */
function wpufe_ajax_tag_search() {
    global $wpdb;

    $taxonomy = sanitize_key( $_GET['tax'] );
    $tax = get_taxonomy( $taxonomy );
    if ( !$tax ) {
        wp_die( 0 );
    }

    $s = wp_unslash( $_GET['q'] );

    $comma = _x( ',', 'tag delimiter', 'wpuf' );
    if ( ',' !== $comma )
        $s = str_replace( $comma, ',', $s );
    if ( false !== strpos( $s, ',' ) ) {
        $s = explode( ',', $s );
        $s = $s[count( $s ) - 1];
    }

    $s = trim( $s );
    if ( strlen( $s ) < 2 )
        wp_die(); // require 2 chars for matching

    $results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . like_escape( $s ) . '%' ) );

    echo join( $results, "\n" );
    wp_die();
}

add_action( 'wp_ajax_wpuf-ajax-tag-search', 'wpufe_ajax_tag_search' );
add_action( 'wp_ajax_nopriv_wpuf-ajax-tag-search', 'wpufe_ajax_tag_search' );

/**
 * Option dropdown helper
 *
 * @param array $options
 * @param string $selected
 * @return string
 */
function wpuf_dropdown_helper( $options, $selected = '' ) {
    $string = '';

    foreach ($options as $key => $label) {
        $string .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $selected, $key, false ), $label );
    }

    return $string;
}

/**
 * Include a template file
 *
 * Looks up first on the theme directory, if not found
 * lods from plugins folder
 *
 * @since 2.2
 *
 * @param string $file file name or path to file
 */
function wpuf_load_template( $file, $args = array() ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }

    $child_theme_dir = get_stylesheet_directory() . '/wpuf/';
    $parent_theme_dir = get_template_directory() . '/wpuf/';
    $wpuf_dir = WPUF_ROOT . '/templates/';

    if ( file_exists( $child_theme_dir . $file ) ) {

        include $child_theme_dir . $file;

    } else if ( file_exists( $parent_theme_dir . $file ) ) {

        include $parent_theme_dir . $file;

    } else {
        include $wpuf_dir . $file;
    }
}

/**
 * Helper function for formatting date field
 *
 * @since 0.1
 * @param string $date
 * @param bool $show_time
 * @return string
 */
function wpuf_get_date( $date, $show_time = false, $format = false ) {
    if ( empty( $date ) ) {
        return $date;
    }

    $timestamp = strtotime( $date );
    if ( $format ) {
        $dateobj = DateTime::createFromFormat( $format, $date );
        if ( $dateobj ) {
            $timestamp = $dateobj->getTimestamp();
        }
    }

    $format = get_option( 'date_format' );

    if ( $show_time ) {
        $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    }

    return date_i18n( $format, $timestamp );
}

/**
 * Helper function for converting a normal date string to unix date/time string
 *
 * @since 0.1
 * @param string $date
 * @param int $gmt
 * @return string
 */
function wpuf_date2mysql( $date, $gmt = 0 ) {
    if (empty( $date ) ) {
        return;
    }
    $time = strtotime( $date );

    return ( $gmt ) ? gmdate( 'Y-m-d H:i:s', $time ) : gmdate( 'Y-m-d H:i:s', ( $time + ( intval( get_option( 'timezone_string' ) ) * 3600 ) ) );
}

/**
 * Get form fields from a form
 *
 * @param  int $form_id
 * @return array
 */
function wpuf_get_form_fields( $form_id ) {
    $fields = get_children(array(
        'post_parent' => $form_id,
        'post_status' => 'publish',
        'post_type'   => 'wpuf_input',
        'numberposts' => '-1',
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
    ));

    $form_fields = array();

    foreach ( $fields as $key => $content ) {

        $field = maybe_unserialize( $content->post_content );

        $field['id'] = $content->ID;

        // Add inline property for radio and checkbox fields
        $inline_supported_fields = array( 'radio', 'checkbox' );
        if ( in_array( $field['input_type'] , $inline_supported_fields ) ) {
            if ( ! isset( $field['inline'] ) ) {
                $field['inline'] = 'no';
            }
        }

        // Add 'selected' property
        $option_based_fields = array( 'select', 'multiselect', 'radio', 'checkbox' );
        if ( in_array( $field['input_type'] , $option_based_fields ) ) {
            if ( ! isset( $field['selected'] ) ) {

                if ( 'select' === $field['input_type'] || 'radio' === $field['input_type'] ) {
                    $field['selected'] = '';
                } else {
                    $field['selected'] = array();
                }

            }
        }

        // Add 'multiple' key for input_type:repeat
        if ( 'repeat' === $field['input_type'] && ! isset( $field['multiple'] ) ) {
            $field['multiple'] = '';
        }

        if ( 'recaptcha' === $field['input_type'] ) {
            $field['name'] = 'recaptcha';
            $field['enable_no_captcha'] = isset( $field['enable_no_captcha'] ) ? $field['enable_no_captcha'] : '';

        }

        $form_fields[] = apply_filters( 'wpuf-get-form-fields', $field );
    }

    return $form_fields;
}

add_action( 'wp_ajax_wpuf_get_child_cat', 'wpuf_get_child_cats' );
add_action( 'wp_ajax_nopriv_wpuf_get_child_cat', 'wpuf_get_child_cats' );

/**
 * Returns child category dropdown on ajax request
 */
function wpuf_get_child_cats() {

    $parentCat = $_POST['catID'];
    $field_attr = $_POST['field_attr'];
    $taxonomy = $_POST['field_attr']['name'];

    $terms = null;
    $result = '';

    if ( $parentCat < 1 )
        die( $result );

    if ( $terms = get_categories( 'taxonomy='.$taxonomy.'&child_of=' . $parentCat . '&hide_empty=0' ) ) {
        $field_attr['parent_cat'] = $parentCat;
        if( is_array($terms) ){
            foreach( $terms as $key => $term ){
                $terms[$key] = (array)$term;
            }
        }
        $result .= WPUF_Render_Form::init()->taxnomy_select( '', $field_attr );
    } else {
        die( '' );
    }

    die( $result );
}

/**
 * Returns form setting value
 *
 * @param  init $form_id
 * @param  boolen $status
 *
 * @return array
 */
function wpuf_get_form_settings( $form_id, $status = true ) {
    return get_post_meta( $form_id, 'wpuf_form_settings', $status );
}

/**
 * Get form notifications
 *
 * @since 2.5.2
 *
 * @param  int $form_id
 *
 * @return array
 */
function wpuf_get_form_notifications( $form_id ) {
    $notifications =  get_post_meta( $form_id, 'notifications', true );

    if ( ! $notifications ) {
        return array();
    }

    return $notifications;
}

/**
 * Get form integration settings
 *
 * @since 2.5.4
 *
 * @param  int $form_id
 *
 * @return array
 */
function wpuf_get_form_integrations( $form_id ) {
    $integrations =  get_post_meta( $form_id, 'integrations', true );

    if ( ! $integrations ) {
        return array();
    }

    return $integrations;
}

/**
 * Check if an integration is active
 *
 * @since 2.5.4
 *
 * @param  int $form_id
 * @param  string $integration_id
 *
 * @return boolean
 */
function wpuf_is_integration_active( $form_id, $integration_id ) {
    $integrations = wpuf_get_form_integrations( $form_id );

    if ( ! $integrations ) {
        return false;
    }

    foreach ($integrations as $id => $integration) {
        if ( $integration_id == $id && $integration->enabled == true ) {
            return $integration;
        }
    }

    return false;
}

/**
 * Get the subscription page url
 *
 * @return string
 */
function wpuf_get_subscription_page_url() {
    $page_id = wpuf_get_option( 'subscription_page', 'wpuf_payment' );

    return get_permalink( $page_id );
}

/**
 * Clear the buffer
 *
 * prevents ajax breakage and endless loading icon. A LIFE SAVER!!!
 *
 * @return void
 */
function wpuf_clear_buffer() {
    ob_clean();
}

/**
 * Check if the license has been expired
 *
 * @since 2.3.13
 *
 * @return boolean
 */
function wpuf_is_license_expired() {
    if ( in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) {
        return false;
    }

    $license_status = get_option( 'wpuf_license_status' );

    // seems like this wasn't activated at all
    if ( ! isset( $license_status->update ) ) {
        return false;
    }

    // if license has expired more than 15 days ago
    $update    = strtotime( $license_status->update );
    $threshold = strtotime( '+15 days', $update );

    // printf( 'Validity: %s, Threshold: %s', date( 'd-m-Y', $update), date( 'd-m-Y', $threshold ) );

    if ( time() >= $threshold ) {
        return true;
    }

    return false;
}

/**
 * Get post form templates
 *
 * @since 2.4
 *
 * @return array
 */
function wpuf_get_post_form_templates() {
    require_once WPUF_ROOT . '/class/post-form-templates/post.php';

    $integrations = array();
    $integrations['WPUF_Post_Form_Template_Post'] = new WPUF_Post_Form_Template_Post();

    return apply_filters( 'wpuf_get_post_form_templates', $integrations );
}

/**
 * Get countries
 *
 * @since 2.4.1
 *
 * @param  string $type (optional)
 *
 * @return array|string
 */
function wpuf_get_countries( $type = 'array' ) {
    $countries = include dirname( __FILE__ ) . '/includes/countries-formated.php';

    if ( $type == 'json' ) {
        $countries = json_encode( $countries );
    }

    return $countries;
}

/**
 * Get account dashboard's sections
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_account_sections() {
    $account_sections = array(
        array( 'slug' => 'dashboard', 'label' => __( 'Dashboard', 'wpuf' ) ),
        array( 'slug' => 'posts', 'label' => __( 'Posts', 'wpuf' ) ),
        array( 'slug' => 'edit-profile', 'label' => __( 'Edit Profile', 'wpuf' ) ),
        array( 'slug' => 'subscription', 'label' => __( 'Subscription', 'wpuf' ) ),
        array( 'slug' => 'billing-address', 'label' => __( 'Billing Address', 'wpuf' ) ),
    );

    return apply_filters( 'wpuf_account_sections', $account_sections );
}

/**
 * Get all transactions
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_transactions( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    );

    $args = wp_parse_args( $args, $defaults );

    if ( $args['count'] ) {
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpuf_transaction" );
    }

    $result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_transaction ORDER BY `{$args['orderby']}` {$args['order']} LIMIT {$args['offset']}, {$args['number']}", OBJECT );

    return $result;
}

/**
 * Get all pending transactions
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_pending_transactions( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    );

    $args = wp_parse_args( $args, $defaults );

    $pending_args = array(
        'post_type'      => 'wpuf_order',
        'post_status'    => array( 'publish', 'pending' ),
        'posts_per_page' => $args['number'],
        'offset'         => $args['offset'],
        'orderby'        => $args['orderby'],
        'order'          => $args['order'],
    );

    $wpuf_order_query = new WP_Query( $pending_args );

    if ( $args['count'] ) {
        return $wpuf_order_query->found_posts;
    }

    $transactions = $wpuf_order_query->get_posts();

    $items = array();
    foreach ( $transactions as $transaction ) {
        $info = get_post_meta( $transaction->ID, '_data', true );

        $items[] = (object) array(
            'id'               => $transaction->ID,
            'user_id'          => $info['user_info']['id'],
            'status'           => 'pending',
            'cost'             => $info['price'],
            'tax'              => isset( $info['tax'] ) ? $info['tax'] : 0,
            'post_id'          => ( $info['type'] == 'post' ) ? $info['item_number'] : 0,
            'pack_id'          => ( $info['type'] == 'pack' ) ? $info['item_number'] : 0,
            'payer_first_name' => $info['user_info']['first_name'],
            'payer_last_name'  => $info['user_info']['last_name'],
            'payer_email'      => $info['user_info']['email'],
            'payment_type'     => ( $info['post_data']['wpuf_payment_method'] == 'bank' ) ? 'Bank/Manual' : ucwords( $info['post_data']['wpuf_payment_method'] ),
            'transaction_id'   => 0,
            'created'          => $info['date'],
        );
    }

    return $items;
}

/**
 * Get full list of currency codes.
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_currencies() {
    $currencies = array(
        array( 'currency' => 'AED', 'label' => __( 'United Arab Emirates Dirham', 'wpuf' ), 'symbol' => 'د.إ' ),
        array( 'currency' => 'AUD', 'label' => __( 'Australian Dollars', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'AZD', 'label' => __( 'Argentine Peso', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'BDT', 'label' => __( 'Bangladeshi Taka', 'wpuf' ), 'symbol' => '&#2547;' ),
        array( 'currency' => 'BRL', 'label' => __( 'Brazilian Real', 'wpuf' ), 'symbol' => '&#82;&#36;' ),
        array( 'currency' => 'BGN', 'label' => __( 'Bulgarian Lev', 'wpuf' ), 'symbol' => '&#1083;&#1074;.' ),
        array( 'currency' => 'CAD', 'label' => __( 'Canadian Dollars', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'CLP', 'label' => __( 'Chilean Peso', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'CNY', 'label' => __( 'Chinese Yuan', 'wpuf' ), 'symbol' => '&yen;' ),
        array( 'currency' => 'COP', 'label' => __( 'Colombian Peso', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'CZK', 'label' => __( 'Czech Koruna', 'wpuf' ), 'symbol' => '&#75;&#269;' ),
        array( 'currency' => 'DKK', 'label' => __( 'Danish Krone', 'wpuf' ), 'symbol' => 'kr.' ),
        array( 'currency' => 'DOP', 'label' => __( 'Dominican Peso', 'wpuf' ), 'symbol' => 'RD&#36;' ),
        array( 'currency' => 'DZD', 'label' => __( 'Algerian Dinar', 'wpuf' ), 'symbol' => 'DA;' ),
        array( 'currency' => 'EUR', 'label' => __( 'Euros', 'wpuf' ), 'symbol' => '&euro;' ),
        array( 'currency' => 'HKD', 'label' => __( 'Hong Kong Dollar', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'HRK', 'label' => __( 'Croatia kuna', 'wpuf' ), 'symbol' => 'Kn' ),
        array( 'currency' => 'HUF', 'label' => __( 'Hungarian Forint', 'wpuf' ), 'symbol' => '&#70;&#116;' ),
        array( 'currency' => 'ISK', 'label' => __( 'Icelandic krona', 'wpuf' ), 'symbol' => 'Kr.' ),
        array( 'currency' => 'IDR', 'label' => __( 'Indonesia Rupiah', 'wpuf' ), 'symbol' => 'Rp' ),
        array( 'currency' => 'INR', 'label' => __( 'Indian Rupee', 'wpuf' ), 'symbol' => '&#8377;' ),
        array( 'currency' => 'NPR', 'label' => __( 'Nepali Rupee', 'wpuf' ), 'symbol' => 'Rs.' ),
        array( 'currency' => 'ILS', 'label' => __( 'Israeli Shekel', 'wpuf' ), 'symbol' => '&#8362;' ),
        array( 'currency' => 'JPY', 'label' => __( 'Japanese Yen', 'wpuf' ), 'symbol' => '&yen;' ),
        array( 'currency' => 'KIP', 'label' => __( 'Lao Kip', 'wpuf' ), 'symbol' => '&#8365;' ),
        array( 'currency' => 'KRW', 'label' => __( 'South Korean Won', 'wpuf' ), 'symbol' => '&#8361;' ),
        array( 'currency' => 'MYR', 'label' => __( 'Malaysian Ringgits', 'wpuf' ), 'symbol' => '&#82;&#77;' ),
        array( 'currency' => 'MXN', 'label' => __( 'Mexican Peso', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'NGN', 'label' => __( 'Nigerian Naira', 'wpuf' ), 'symbol' => '&#8358;' ),
        array( 'currency' => 'NOK', 'label' => __( 'Norwegian Krone', 'wpuf' ), 'symbol' => '&#107;&#114;' ),
        array( 'currency' => 'NZD', 'label' => __( 'New Zealand Dollar', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'OMR', 'label' => __( 'Omani Rial', 'wpuf' ), 'symbol' => 'ر.ع.' ),
        array( 'currency' => 'IRR', 'label' => __( 'Iranian Rial', 'wpuf' ), 'symbol' => '﷼' ),
        array( 'currency' => 'PKR', 'label' => __( 'Pakistani Rupee', 'wpuf' ), 'symbol' => 'Rs' ),
        array( 'currency' => 'PYG', 'label' => __( 'Paraguayan Guaraní', 'wpuf' ), 'symbol' => '&#8370;' ),
        array( 'currency' => 'PHP', 'label' => __( 'Philippine Pesos', 'wpuf' ), 'symbol' => '&#8369;' ),
        array( 'currency' => 'PLN', 'label' => __( 'Polish Zloty', 'wpuf' ), 'symbol' => '&#122;&#322;' ),
        array( 'currency' => 'GBP', 'label' => __( 'Pounds Sterling', 'wpuf' ), 'symbol' => '&pound;' ),
        array( 'currency' => 'RON', 'label' => __( 'Romanian Leu', 'wpuf' ), 'symbol' => 'lei' ),
        array( 'currency' => 'RUB', 'label' => __( 'Russian Ruble', 'wpuf' ), 'symbol' => '&#1088;&#1091;&#1073;.' ),
        array( 'currency' => 'SR', 'label'  => __( 'Saudi Riyal', 'wpuf'), 'symbol' => 'SR' ),
        array( 'currency' => 'SGD', 'label' => __( 'Singapore Dollar', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'ZAR', 'label' => __( 'South African rand', 'wpuf' ), 'symbol' => '&#82;' ),
        array( 'currency' => 'SEK', 'label' => __( 'Swedish Krona', 'wpuf' ), 'symbol' => '&#107;&#114;' ),
        array( 'currency' => 'CHF', 'label' => __( 'Swiss Franc', 'wpuf' ), 'symbol' => '&#67;&#72;&#70;' ),
        array( 'currency' => 'TWD', 'label' => __( 'Taiwan New Dollars', 'wpuf' ), 'symbol' => '&#78;&#84;&#36;' ),
        array( 'currency' => 'THB', 'label' => __( 'Thai Baht', 'wpuf' ), 'symbol' => '&#3647;' ),
        array( 'currency' => 'TRY', 'label' => __( 'Turkish Lira', 'wpuf' ), 'symbol' => '&#8378;' ),
        array( 'currency' => 'USD', 'label' => __( 'US Dollar', 'wpuf' ), 'symbol' => '&#36;' ),
        array( 'currency' => 'VND', 'label' => __( 'Vietnamese Dong', 'wpuf' ), 'symbol' => '&#8363;' ),
        array( 'currency' => 'EGP', 'label' => __( 'Egyptian Pound', 'wpuf' ), 'symbol' => 'EGP' ),
    );

    return apply_filters( 'wpuf_currencies', $currencies );
}

/**
 * Get global currency
 *
 * @since 2.4.2
 *
 * @param  string $type
 *
 * @return mixed
 */
function wpuf_get_currency( $type = '' ) {
    $currency_code = wpuf_get_option( 'currency', 'wpuf_payment', 'USD' );

    if ( $type == 'code' ) {
        return $currency_code;
    }

    $currencies = wpuf_get_currencies();
    $index      = array_search( $currency_code, array_column( $currencies, 'currency' ) );
    $currency   = $currencies[ $index ];

    if ( $type == 'symbol' ) {
        return $currency['symbol'];
    }

    return $currency;
}


/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function get_wpuf_price_format() {
    $currency_pos = wpuf_get_option( 'currency_position', 'wpuf_payment', 'left' );
    $format = '%1$s%2$s';

    switch ( $currency_pos ) {
        case 'left' :
            $format = '%1$s%2$s';
        break;
        case 'right' :
            $format = '%2$s%1$s';
        break;
        case 'left_space' :
            $format = '%1$s&nbsp;%2$s';
        break;
        case 'right_space' :
            $format = '%2$s&nbsp;%1$s';
        break;
    }

    return apply_filters( 'wpuf_price_format', $format, $currency_pos );
}

/**
 * Return the thousand separator for prices.
 * @since  2.4.4
 * @return string
 */
function wpuf_get_price_thousand_separator() {
    $separator = stripslashes( wpuf_get_option( 'wpuf_price_thousand_sep', 'wpuf_payment', ',' ) );
    return $separator;
}

/**
 * Return the decimal separator for prices.
 * @since  2.4.4
 * @return string
 */
function wpuf_get_price_decimal_separator() {
    $separator = stripslashes( wpuf_get_option( 'wpuf_price_decimal_sep', 'wpuf_payment', '.' ) );
    return $separator;
}

/**
 * Return the number of decimals after the decimal point.
 * @since  2.4.4
 * @return int
 */
function wpuf_get_price_decimals() {
    return absint( wpuf_get_option( 'wpuf_price_num_decimals', 'wpuf_payment', 2 ) );
}

/**
 * Trim trailing zeros off prices.
 *
 * @param mixed $price
 * @return string
 */
function wpuf_trim_zeros( $price ) {
    return preg_replace( '/' . preg_quote( wc_get_price_decimal_separator(), '/' ) . '0++$/', '', $price );
}

/**
 * Format the pricing number
 *
 * @since 2.4.2
 *
 * @param  number $number
 * @param  array
 *
 * @return mixed
 */
function wpuf_format_price( $price, $formated = true, $args = array() ) {

    extract( apply_filters( 'wpuf_price_args', wp_parse_args( $args, array(
        'currency'           => $formated ? wpuf_get_currency( 'symbol' ) : '',
        'decimal_separator'  => wpuf_get_price_decimal_separator(),
        'thousand_separator' => $formated ? wpuf_get_price_thousand_separator() : '',
        'decimals'           => wpuf_get_price_decimals(),
        'price_format'       => get_wpuf_price_format()
    ) ) ) );

    $negative        = $price < 0;
    $price           = apply_filters( 'wpuf_raw_price', floatval( $negative ? $price * -1 : $price ) );
    $price           = apply_filters( 'wpuf_formatted_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

    if ( apply_filters( 'wpuf_price_trim_zeros', false ) && $decimals > 0 ) {
        $price = wpuf_trim_zeros( $price );
    }

    $formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, $currency, $price );

    return apply_filters( 'wpuf_format_price', $formatted_price, $price, $args );

}

/**
 * Polyfill of array_column function
 *
 * @since 2.4.3
 */
if ( ! function_exists( 'array_column' ) ) {
    function array_column( $input, $column_key, $index_key = null ) {
        $result = array();

        foreach ( $input as $k => $v ) {
            $result[ $index_key ? $v[ $index_key ] : $k ] = $v[ $column_key ];
        }

        return $result;
    }
}

/**
 * API to duplicate a form
 *
 * @since 2.5
 *
 * @param int $post_id
 *
 * @return int New duplicated form id
 */
function wpuf_duplicate_form( $post_id ) {
    $post = get_post( $post_id );

    if ( !$post ) {
        return;
    }

    $contents = wpuf_get_form_fields( $post_id );

    $new_form = array(
        'post_title'  => $post->post_title,
        'post_type'   => $post->post_type,
        'post_status' => 'draft'
    );

    $form_id = wp_insert_post( $new_form );

    foreach ( $contents as $content ) {
        wpuf_insert_form_field( $form_id, $content );
    }

    // update the post title to remove confusion
    wp_update_post( array(
        'ID'         => $form_id,
        'post_title' => $post->post_title . ' (#' . $form_id . ')'
    ) );

    if ( $form_id ) {
        $form_settings = wpuf_get_form_settings( $post_id );
        $notifications = wpuf_get_form_notifications( $post_id );

        update_post_meta( $form_id, 'wpuf_form_settings', $form_settings );
        update_post_meta( $form_id, 'notifications', $notifications );

        return $form_id;
    }

    return 0;
}

/**
 * Save form fields
 *
 * @since 2.5
 *
 * @param int $form_id
 * @param array $field
 * @param int $field_id
 * @param int $order
 *
 * @return int ID of updated or inserted post
 */
function wpuf_insert_form_field( $form_id, $field = array(), $field_id = null, $order = 0 ) {

    $args = array(
        'post_type'    => 'wpuf_input',
        'post_parent'  => $form_id,
        'post_status'  => 'publish',
        'post_content' => maybe_serialize( wp_unslash( $field ) ),
        'menu_order'   => $order
    );

    if ( $field_id ) {
        $args['ID'] = $field_id;
    }

    if ( $field_id ) {
        return wp_update_post( $args );
    } else {
        return wp_insert_post( $args );
    }
}

/**
 * Create a sample / base form
 *
 * @since  2.5
 * @param  string  $post_title (optional)
 * @param  string  $post_type  (optional)
 * @param  boolean $blank  (optional)
 *
 * @return int
 */
function wpuf_create_sample_form( $post_title = 'Sample Form', $post_type = 'wpuf_forms', $blank = false ) {

    $form_id = wp_insert_post( array(
        'post_title'     => $post_title,
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'comment_status' => 'closed',
        'post_content'   => ''
    ) );

    if ( ! $form_id ) {
        return false;
    }

    $form_fields = array();
    $settings    = array();

    // Post form
    if ( 'wpuf_forms' === $post_type ) {
        $form_fields = array(
            array(
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => 'Post Title',
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => array( )
            ),
            array(
                'input_type'   => 'textarea',
                'template'     => 'post_content',
                'required'     => 'yes',
                'label'        => 'Post Content',
                'name'         => 'post_content',
                'is_meta'      => 'no',
                'help'         => '',
                'css'          => '',
                'rows'         => '5',
                'cols'         => '25',
                'placeholder'  => '',
                'default'      => '',
                'rich'         => 'teeny',
                'insert_image' => 'yes',
                'wpuf_cond'    => array( )
            )
        );

        $settings = array(
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'post_format'      => '0',
            'default_cat'      => '-1',
            'guest_post'       => 'false',
            'guest_details'    => 'true',
            'name_label'       => 'Name',
            'email_label'      => 'Email',
            'message_restrict' => 'This page is restricted. Please Log in / Register to view this page.',
            'redirect_to'      => 'post',
            'message'          => 'Post saved',
            'page_id'          => '',
            'url'              => '',
            'comment_status'   => 'open',
            'submit_text'      => 'Submit',
            'draft_post'       => 'false',
            'edit_post_status' => 'publish',
            'edit_redirect_to' => 'same',
            'update_message'   => 'Post updated successfully',
            'edit_page_id'     => '',
            'edit_url'         => '',
            'subscription'     => '- Select -',
            'update_text'      => 'Update',
            'notification'     => array(
                'new'          => 'on',
                'new_to'       => get_option( 'admin_email' ),
                'new_subject'  => 'New post created',
                'new_body'     => "Hi Admin, \r\n\r\nA new post has been created in your site %sitename% (%siteurl%). \r\n\r\nHere is the details: \r\nPost Title: %post_title% \r\nContent: %post_content% \r\nAuthor: %author% \r\nPost URL: %permalink% \r\nEdit URL: %editlink%",
                'edit'         => 'off',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => 'A post has been edited',
                'edit_body'    => "Hi Admin, \r\n\r\nThe post \"%post_title%\" has been updated. \r\n\r\nHere is the details: \r\nPost Title: %post_title% \r\nContent: %post_content% \r\nAuthor: %author% \r\nPost URL: %permalink% \r\nEdit URL: %editlink%",
            ),
        );
    }

    // Profile form
    if ( 'wpuf_profile' === $post_type ) {
        $form_fields = array(
            array(
                'input_type'  => 'email',
                'template'    => 'user_email',
                'required'    => 'yes',
                'label'       => 'Email',
                'name'        => 'user_email',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => NULL,
            ),
            array(
                'input_type'    => 'password',
                'template'      => 'password',
                'required'      => 'yes',
                'label'         => 'Password',
                'name'          => 'password',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => '40',
                'min_length'    => '5',
                'repeat_pass'   => 'yes',
                're_pass_label' => 'Confirm Password',
                'pass_strength' => 'yes',
                'wpuf_cond'     => NULL
            )
        );

        $settings = array(
            'role'           => 'subscriber',
            'redirect_to'    => 'same',
            'message'        => 'Registration successful',
            'update_message' => 'Profile updated successfully',
            'page_id'        => '0',
            'url'            => '',
            'submit_text'    => 'Register',
            'update_text'    => 'Update Profile'
        );
    }

    if ( ! empty( $form_fields ) && ! $blank ) {
        foreach ( $form_fields as $order => $field ) {
            wpuf_insert_form_field( $form_id, $field, false, $order );
        }
    }

    if ( ! empty( $settings ) ) {
        update_post_meta( $form_id, 'wpuf_form_settings', $settings );
    }

    //set form Version
    update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );

    return $form_id;
}

/**
 * Get the client IP address
 *
 * @since 2.5.2
 *
 * @return string
 */
function wpuf_get_client_ip() {
    $ipaddress = '';

    if ( isset($_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

/**
 * Delete a form with it's field and meta
 *
 * @since 2.5.2
 *
 * @param  int  $form_id
 * @param  boolean $force
 *
 * @return void
 */
function wpuf_delete_form( $form_id, $force = true ) {
    global $wpdb;

    wp_delete_post( $form_id, $force );

    // delete form inputs as WP doesn't know the relationship
    $wpdb->delete( $wpdb->posts,
        array(
            'post_parent' => $form_id,
            'post_type'   => 'wpuf_input'
        )
    );
}

/**
 * Check save draft post status based on subscription
 *
 * @since 2.5.2
 *
 * @param  array  $form_settings
 *
 * @return string $post_status
 */
function wpuf_get_draft_post_status( $form_settings ) {
    $post_status = 'draft';
    $current_user       = wpuf_get_user();
    $charging_enabled   = $current_user->subscription()->current_pack_id();
    $user_wpuf_subscription_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );

    if ( $charging_enabled && ! isset( $_POST['post_id'] ) ) {
        if ( !empty( $user_wpuf_subscription_pack ) ) {
            if ( $current_user->subscription()->expired() ) {
                $post_status = 'pending';
            }
        }
    }
    return $post_status;
}

/**
 * Show helper texts to understand the type of page in admin page listing
 *
 * @since 2.6.0
 *
 * @param  array $state
 * @param  \WP_Post $post
 *
 * @return array
 */
function wpuf_admin_page_states( $state, $post) {

    if ( 'page' != $post->post_type ) {
        return $state;
    }

    $pattern = '/\[(wpuf[\w\-\_]+).+\]/';

    preg_match_all ( $pattern , $post->post_content, $matches);
    $matches = array_unique( $matches[0] );

    if ( !empty( $matches ) ) {

        $page      = '';
        $shortcode = $matches[0];

        if ( '[wpuf_account]' == $shortcode ) {
            $page = 'WPUF Account Page';
        } elseif ( '[wpuf_edit]' == $shortcode ) {
            $page = 'WPUF Post Edit Page';
        } elseif ( '[wpuf-login]' == $shortcode ) {
            $page = 'WPUF Login Page';
        } elseif ( '[wpuf_sub_pack]' == $shortcode ) {
            $page = 'WPUF Subscription Page';
        } elseif ( '[wpuf_editprofile]' == $shortcode ) {
            $page = 'WPUF Profile Edit Page';
        } elseif ( stristr( $shortcode, '[wpuf_dashboard') ) {
            $page = 'WPUF Dashboard Page';
        } elseif ( stristr( $shortcode, '[wpuf_profile type="registration"') ) {
            $page = 'WPUF Registration Page';
        } elseif ( stristr( $shortcode, '[wpuf_profile type="profile"') ) {
            $page = 'WPUF Profile Edit Page';
        } elseif ( stristr( $shortcode, '[wpuf_form') ) {
            $page = 'WPUF Form Page';
        }

        if ( ! empty( $page )) {
            $state['wpuf'] = $page;
        }
    }

    return $state;
}

add_filter( 'display_post_states', 'wpuf_admin_page_states', 10, 2 );

/**
 * Encryption function for various usage
 *
 * @since 2.5.8
 *
 * @param  string  $id
 *
 * @return string $encoded_id
 */
function wpuf_encryption ( $id ) {

    $secret_key     = AUTH_KEY;
    $secret_iv      = AUTH_SALT;

    $encrypt_method = "AES-256-CBC";
    $key            = hash( 'sha256', $secret_key );
    $iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    $encoded_id     = base64_encode( openssl_encrypt( $id, $encrypt_method, $key, 0, $iv ) );

    return $encoded_id;
}

/**
 * Decryption function for various usage
 *
 * @since 2.5.8
 *
 * @param  string  $id
 *
 * @return string $encoded_id
 */
function wpuf_decryption ( $id ) {

    $secret_key     = AUTH_KEY;
    $secret_iv      = AUTH_SALT;

    $encrypt_method = "AES-256-CBC";
    $key            = hash( 'sha256', $secret_key );
    $iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    $decoded_id     = openssl_decrypt( base64_decode( $id ), $encrypt_method, $key, 0, $iv );

    return $decoded_id;
}

/**
 * Send guest verification mail
 *
 * @since 2.5.8
 *
 * @param  string  $post_id_encoded, $form_id_encoded, $charging_enabled, $flag
 *
 * @return void
 */
function wpuf_send_mail_to_guest ( $post_id_encoded, $form_id_encoded, $charging_enabled, $flag ) {

    if ( $charging_enabled )  {
        $encoded_guest_url = add_query_arg(
            array(
                'p_id' => $post_id_encoded,
                'f_id' => $form_id_encoded,
                'post_msg' => 'verified',
                'f' => 2,
            ), get_home_url()
        );
    } else {
        $encoded_guest_url = add_query_arg(
            array(
                'p_id' => $post_id_encoded,
                'f_id' => $form_id_encoded,
                'post_msg' => 'verified',
                'f' => 1,
            ), get_home_url()
        );
    }

    $default_body     = 'Hey There,' . '<br>' . '<br>' . 'We just received your guest post and now we want you to confirm your email so that we can verify the content and move on to the publishing process.' .  '<br>' . '<br>' . 'Please click the link below to verify:' . '<br>' . '<br>' . '<a href="'.$encoded_guest_url.'">Publish Post</a>' . '<br>' . '<br>' . 'Regards,' . '<br>' . '<br>' . bloginfo('name');
    $to               = trim( $_POST['guest_email'] );
    $guest_email_sub  = wpuf_get_option( 'guest_email_subject', 'wpuf_mails', 'Please Confirm Your Email to Get the Post Published!' );
    $subject          = $guest_email_sub;
    $guest_email_body = wpuf_get_option( 'guest_email_body', 'wpuf_mails',  $default_body );

    if ( !empty( $guest_email_body ) ) {
        $blogname     = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $field_search = array( '{activation_link}', '{sitename}' );

        $field_replace = array(
            '<a href="'.$encoded_guest_url.'">Publish Post</a>',
            $blogname
        );

        $body = str_replace( $field_search, $field_replace, $guest_email_body );
    } else {
        $body = $default_body;
    }

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $body    = get_formatted_mail_body( $body, $subject);

    wp_mail( $to, $subject, $body, $headers );
}


/**
 * Check if it's post form builder
 *
 * @since 2.6
 *
 * @return boolean
 */
function is_wpuf_post_form_builder() {
    return isset( $_GET['page'] ) && $_GET['page'] == 'wpuf-post-forms' ? true : false;
}

/**
 * Check if it's profile form builder
 *
 * @since 2.6
 *
 * @return boolean
 */
function is_wpuf_profile_form_builder() {
    return isset( $_GET['page'] ) && $_GET['page'] == 'wpuf-profile-forms' ? true : false;
}

/**
 * Get a WP User
 *
 * @since 2.6.0
 *
 * @param  integer|WP_User $user_id
 *
 * @return \WPUF_User
 */
function wpuf_get_user( $user = null ) {

    if ( ! $user ) {
        $user = wp_get_current_user();
    }

    return new WPUF_User( $user );
}

/**
 * Add all terms as allowed terms
 *
 * @since 2.7.0
 *
 * @return void
 */
function wpuf_set_all_terms_as_allowed() {

    if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
        $subscriptions  = WPUF_Subscription::init()->get_subscriptions();
        $allowed_term = array();

        foreach ( $subscriptions as $pack ) {
            if ( ! metadata_exists( 'post', $pack->ID , '_sub_allowed_term_ids' ) ) {
                $cts = get_taxonomies(array('_builtin'=>true), 'objects'); ?>
                <?php foreach ($cts as $ct) {
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms ( array(
                            'taxonomy' => $ct->name,
                            'hide_empty' => false,
                        ) );
                        foreach ($tax_terms as $tax_term) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                $cts = get_taxonomies(array('_builtin'=>false), 'objects'); ?>
                <?php foreach ($cts as $ct) {
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms ( array(
                            'taxonomy' => $ct->name,
                            'hide_empty' => false,
                        ) );
                        foreach ($tax_terms as $tax_term) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                update_post_meta( $pack->ID, '_sub_allowed_term_ids', $allowed_term );
            }
        }
    }
}

/**
 * post submitted by form
 *
 * @since 2.8
 *
 * @param  int $form_id
 *
 * @return List of WP_Post objects.
 */
function wpuf_posts_submitted_by( $form_id ) {
    $settings     = wpuf_get_form_settings( $form_id );
    $settings['post_type'];
    $args = array(
        'meta_key'         => '_wpuf_form_id',
        'meta_value'       => $form_id,
        'post_type'        => $settings['post_type'],
        'post_status'      => 'publish',
    );
    $posts_array = get_posts( $args );
    return $posts_array;
}

/**
 * count post submitted by form
 *
 * @since 2.8
 *
 * @param  int $form_id
 *
 * @return int
 */
function wpuf_form_posts_count( $form_id ) {
    return count( wpuf_posts_submitted_by( $form_id ) );
}

/**
 * Get formatted email body
 *
 * @since  2.9
 *
 * @param  string $message
 *
 * @return string
 */
function get_formatted_mail_body( $message, $subject ) {

    if ( wpuf()->is_pro() && wpuf_pro_is_module_active( 'email-templates/email-templates.php' ) ) {
        $css    = '';
        $header = apply_filters( 'wpuf_email_header', '' );
        $footer = apply_filters( 'wpuf_email_footer', '' );

        if ( empty( $header ) ) {
            ob_start();
            include WPUF_PRO_INCLUDES . '/templates/email/header.php';
            $header = ob_get_clean();
        }

        if ( empty( $footer ) ) {
            ob_start();
            include WPUF_PRO_INCLUDES . '/templates/email/footer.php';
            $footer = ob_get_clean();
        }

        ob_start();
        include WPUF_PRO_INCLUDES . '/templates/email/style.php';
        $css = apply_filters( 'wpuf_email_style', ob_get_clean() );

        $content = $header . $message . $footer;

        if ( ! class_exists( 'Emogrifier' ) ) {
            require_once WPUF_PRO_INCLUDES . '/libs/Emogrifier.php';
        }

        try {

            // apply CSS styles inline for picky email clients
            $emogrifier = new Emogrifier( $content, $css );
            $content = $emogrifier->emogrify();

        } catch ( Exception $e ) {

            echo $e->getMessage();
        }

        return $content;
    }

    return $message;
}

/**
 * Renders an HTML Dropdown
 *
 * @param array $args
 *
 * @return string
 */

function wpuf_select( $args = array() ) {
    $defaults = array(
        'options'          => array(),
        'name'             => null,
        'class'            => '',
        'id'               => '',
        'selected'         => array(),
        'chosen'           => false,
        'placeholder'      => null,
        'multiple'         => false,
        'show_option_all'  => __( 'All', 'all dropdown items', 'wpuf' ),
        'show_option_none' => __( 'None', 'no dropdown items', 'wpuf' ),
        'data'             => array(),
        'readonly'         => false,
        'disabled'         => false,
    );

    $args = wp_parse_args( $args, $defaults );

    $data_elements = ''; $selected = '';
    foreach ( $args['data'] as $key => $value ) {
        $data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }

    if ( $args['multiple'] ) {
        $multiple = ' MULTIPLE';
    } else {
        $multiple = '';
    }

    if ( $args['chosen'] ) {
        $args['class'] .= ' wpuf-select-chosen';
        if ( is_rtl() ) {
            $args['class'] .= ' chosen-rtl';
        }
    }

    if ( $args['placeholder'] ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }

    if ( isset( $args['readonly'] ) && $args['readonly'] ) {
        $readonly = ' readonly="readonly"';
    } else {
        $readonly = '';
    }

    if ( isset( $args['disabled'] ) && $args['disabled'] ) {
        $disabled = ' disabled="disabled"';
    } else {
        $disabled = '';
    }

    $class  = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
    $output = '<select' . $disabled . $readonly . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( str_replace( '-', '_', $args['id'] ) ) . '" class="wpuf-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"'. $data_elements . '>';

    if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
        $selected = "";
    }

    if ( $args['show_option_all'] ) {
        if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
            $selected = selected( true, in_array( 0, $args['selected'] ), false );
        } else {
            $selected = selected( $args['selected'], 0, false );
        }
        $output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
    }

    if ( ! empty( $args['options'] ) ) {
        if ( $args['show_option_none'] ) {
            if ( $args['multiple'] ) {
                $selected = selected( true, in_array( -1, $args['selected'] ), false );
            } elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
                $selected = selected( $args['selected'], -1, false );
            }
            $output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
        }

        foreach ( $args['options'] as $key => $option ) {
            if ( $args['multiple'] && is_array( $args['selected'] ) ) {
                $selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
            } elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
                $selected = selected( $args['selected'], $key, false );
            }

            $output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
        }
    }

    $output .= '</select>';

    return $output;
}

/**
 * Renders a Text field in settings field
 *
 * @param array $args Arguments for the text field
 *
 * @return string Text field
 */

function wpuf_text( $args = array() ) {
    $defaults = array(
        'id'           => '',
        'name'         => isset( $name )  ? $name  : 'text',
        'value'        => isset( $value ) ? $value : null,
        'label'        => isset( $label ) ? $label : null,
        'desc'         => isset( $desc )  ? $desc  : null,
        'placeholder'  => '',
        'class'        => 'regular-text',
        'disabled'     => false,
        'autocomplete' => '',
        'data'         => false
    );

    $args = wp_parse_args( $args, $defaults );

    $class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
    $disabled = '';
    if ( $args['disabled'] ) {
        $disabled = ' disabled="disabled"';
    }

    $data = '';
    if ( ! empty( $args['data'] ) ) {
        foreach ( $args['data'] as $key => $value ) {
            $data .= 'data-' . $key . '="' . esc_attr( $value ) . '" ';
        }
    }

    $output = '<span id="wpuf-' . $args['name'] . '-wrap">';
    if ( ! empty( $args['label'] ) ) {
        $output .= '<label class="wpuf-label" for="' . $args['id'] . '">' . esc_html( $args['label'] ) . '</label>';
    }

    if ( ! empty( $args['desc'] ) ) {
        $output .= '<span class="wpuf-description">' . esc_html( $args['desc'] ) . '</span>';
    }

    $output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" autocomplete="' . esc_attr( $args['autocomplete'] )  . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

    $output .= '</span>';

    return $output;
}

/**
 * Descriptive text callback
 *
 * @param array $args Arguments passed by the setting
 * @return void
 */

function wpuf_descriptive_text( $args ) {
    $html = wp_kses_post( $args['desc'] );

    echo $html;
}

/**
 * Update the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $value the value to be set
 * @return mixed
 */

function wpuf_update_option( $option, $section, $value ) {
    $options = get_option( $section );

    $options[$option] = $value;

    update_option( $section, $options );
}

/**
 * Get terms of related taxonomy
 *
 * @since  2.8.5
 *
 * @param  string $taxonomy
 *
 * @return array
 */
function wpuf_get_terms( $taxonomy = 'category' ) {
    $items = array();

    $terms = get_terms(  array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false
        )
    );

    foreach ($terms as $key => $term) {
        $items[$term->term_id] = $term->name;
    }

    return $items;
}

/**
 * Retrieve a states drop down
 *
 * @return void
 */
function wpuf_ajax_get_states_field() {
    $cs = new CountryState();
    $countries = $cs->countries();
    $states    = $cs->getStates( $countries[$_POST['country']] );

    if( ! empty( $states ) ) {
        $args = array(
            'name'    => isset ( $_POST['field_name'] ) ? $_POST['field_name'] : '',
            'id'      => isset ( $_POST['field_name'] ) ? $_POST['field_name'] : '',
            'class'   => isset ( $_POST['field_name'] ) ? $_POST['field_name'] : '',
            'options' => $states,
            'show_option_all'  => false,
            'show_option_none' => false
        );

        $response = wpuf_select( $args );

    } else {
        $response = 'nostates';
    }

    echo $response;

    wp_die();
}
add_action( 'wp_ajax_wpuf_get_shop_states', 'wpuf_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_wpuf_get_shop_states', 'wpuf_ajax_get_states_field' );

/**
 * Performs tax calculations and updates billing address
 *
 * @return void
 */

function wpuf_update_billing_address() {
    ob_start();

    $user_id = get_current_user_id();
    $address_fields = array(
        'add_line_1'    => $_POST['billing_add_line1'],
        'add_line_2'    => $_POST['billing_add_line2'],
        'city'          => $_POST['billing_city'],
        'state'         => $_POST['billing_state'],
        'zip_code'      => $_POST['billing_zip'],
        'country'       => $_POST['billing_country']
    );

    update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );

    $post_data['type']            = $_POST['type'];
    $post_data['id']              = $_POST['id'];
    $post_data['billing_country'] = $_POST['billing_country'];
    $post_data['billing_state']   = $_POST['billing_state'];

    $is_pro = wpuf()->is_pro();
    if ( $is_pro ) {
        do_action( 'wpuf_calculate_tax', $post_data );
    } else {
        die();
    }
}
add_action( 'wp_ajax_wpuf_update_billing_address', 'wpuf_update_billing_address' );
add_action( 'wp_ajax_nopriv_wpuf_update_billing_address', 'wpuf_update_billing_address' );

/**
 * Retrieve user address
 *
 * @return void
 */

function wpuf_get_user_address() {
    $user_id = get_current_user_id();
    $address_fields = array();

    if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields') ) {
        $address_fields = get_user_meta( $user_id, 'wpuf_address_fields', true );
    } else {
        $address_fields = array_fill_keys( array( 'add_line_1', 'add_line_2', 'city', 'state', 'zip_code', 'country' ), '' );

        if ( class_exists( 'WooCommerce' ) ) {
            $customer_id = get_current_user_id();
            $woo_address = array();
            $customer    = new WC_Customer( $customer_id );

            $woo_address = $customer->get_billing();
            unset( $woo_address['email'], $woo_address['tel'], $woo_address['phone'], $woo_address['company'] );

            $countries_obj = new WC_Countries();
            $countries_array = $countries_obj->get_countries();
            $country_states_array = $countries_obj->get_states();
            $woo_address['state'] = $country_states_array[$woo_address['country']][$woo_address['state']];
            $woo_address['state'] = strtolower( str_replace( ' ', '', $woo_address['state'] ) );

            if ( !empty( $woo_address ) ) {
                $address_fields = array(
                    'add_line_1'    => $woo_address['address_1'],
                    'add_line_2'    => $woo_address['address_2'],
                    'city'          => $woo_address['city'],
                    'state'         => $woo_address['state'],
                    'zip_code'      => $woo_address['postcode'],
                    'country'       => $woo_address['country']
                );
            }
        }
    }

    return $address_fields;
}

/**
 * Displays a multi select dropdown for a settings field
 *
 * @param array   $args settings field args
 */
function wpuf_settings_multiselect( $args ) {

    $settings = new WeDevs_Settings_API();
    $value = $settings->get_option( $args['id'], $args['section'], $args['std'] );
    $value = is_array($value) ? (array)$value : array();
    $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
    $html  = sprintf( '<select multiple="multiple" class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

    foreach ( $args['options'] as $key => $label ) {
        $checked = in_array($key, $value) ? $key : '0';
        $html   .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $checked, $key, false ), $label );
    }

    $html .= sprintf( '</select>' );
    $html .= $settings->get_field_description( $args );

    echo $html;
}
