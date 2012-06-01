<?php

/**
 * Handles the add post shortcode
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 * @param $atts
 */
function wpuf_add_post_shorcode( $atts ) {

    extract( shortcode_atts( array('post_type' => 'post'), $atts ) );

    ob_start();

    if ( is_user_logged_in() ) {
        wpuf_add_post( $post_type );
    } else {
        printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( get_permalink(), false ) );
    }

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

add_shortcode( 'wpuf_addpost', 'wpuf_add_post_shorcode' );

/**
 * Add posting main form
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 *
 * @param $post_type
 */
function wpuf_add_post( $post_type ) {
    global $userdata;
    $userdata = get_userdata( $userdata->ID );

    $info = __( "Post It!", 'wpuf' );
    $can_post = 'yes';

    $info = apply_filters( 'wpuf_addpost_notice', $info );
    $can_post = apply_filters( 'wpuf_can_post', $can_post );

    if ( $can_post == 'yes' ) {
        ?>
        <div id="wpuf-post-area">
            <form id="wpuf_new_post_form" name="wpuf_new_post_form" action="" enctype="multipart/form-data" method="POST">
                <?php wp_nonce_field( 'wpuf-add-post' ) ?>

                <ul class="wpuf-post-form">

                    <?php do_action( 'wpuf_add_post_form_top', $post_type ); //plugin hook  ?>
                    <?php wpuf_build_custom_field_form( 'top' ); ?>

                    <li>
                        <label for="new-post-title">
                            <?php echo get_option( 'wpuf_title_label' ); ?> <span class="required">*</span>
                        </label>
                        <input class="requiredField" type="text" name="wpuf_post_title" id="new-post-title" minlength="2">
                        <div class="clear"></div>
                        <p class="description"><?php echo stripslashes( get_option( 'wpuf_title_help' ) ); ?></p>
                    </li>

                    <?php if ( get_option( 'wpuf_allow_choose_cat' ) == 'yes' ) { ?>
                        <li>
                            <label for="new-post-cat">
                                <?php echo get_option( 'wpuf_cat_label' ); ?> <span class="required">*</span>
                            </label>
                            <div style="float:left;">
                                <div id="catlvl0">
                                    <?php $exclude = get_option( 'wpuf_exclude_cat' ); ?>
                                    <?php wp_dropdown_categories( 'show_option_none=' . __( '-- Select --', 'wpuf' ) . '&hierarchical=1&hide_empty=0&orderby=name&name=category[]&id=cat&show_count=0&title_li=&use_desc_for_title=1&class=cat requiredField&depth=1&exclude=' . $exclude ) ?>
                                </div>
                            </div>
                            <div id="categories-footer" style="float:left;"></div>
                            <div class="clear"></div>
                            <p class="description"><?php echo stripslashes( get_option( 'wpuf_cat_help' ) ); ?></p>
                        </li>
                    <?php } ?>

                    <?php do_action( 'wpuf_add_post_form_description', $post_type ); ?>
                    <?php wpuf_build_custom_field_form( 'description' ); ?>

                    <li>
                        <label for="new-post-desc">
                            <?php echo get_option( 'wpuf_desc_label' ); ?> <span class="required">*</span>
                        </label>
                        <div style="float:left;">
                            <?php
                            $editor = get_option( 'wpuf_editor_type' );
                            if ( $editor == 'full' ) {
                                ?>
                                <?php wp_editor( '', 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'teeny' => false, 'textarea_rows' => 8) ); ?>
                            <?php } else if ( $editor == 'rich' ) { ?>
                                <?php wp_editor( '', 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'teeny' => true, 'textarea_rows' => 8) ); ?>
                            <?php } else { ?>
                                <textarea name="wpuf_post_content" id="new-post-desc" cols="60" rows="8"></textarea>
                            <?php } ?>
                        </div>
                        <div class="clear"></div>
                        <p class="description"><?php echo stripslashes( get_option( 'wpuf_desc_help' ) ); ?></p>
                    </li>

                    <?php do_action( 'wpuf_add_post_form_after_description', $post_type ); ?>
                    <?php wpuf_build_custom_field_form( 'tag' ); ?>

                    <?php if ( get_option( 'wpuf_allow_tags' ) == 'yes' ) { ?>
                        <li>
                            <label for="new-post-tags">
                                <?php echo get_option( 'wpuf_tag_label' ); ?>
                            </label>
                            <input type="text" name="wpuf_post_tags" id="new-post-tags" class="new-post-tags">
                            <p class="description"><?php echo stripslashes( get_option( 'wpuf_tag_help' ) ); ?></p>
                            <div class="clear"></div>
                        </li>
                    <?php } ?>

                    <?php do_action( 'wpuf_add_post_form_tags', $post_type ); ?>

                    <?php wpuf_attachment_fields(); ?>

                    <?php wpuf_build_custom_field_form( 'bottom' ); ?>

                    <li>
                        <label>&nbsp;</label>
                        <input class="wpuf_submit" type="submit" name="wpuf_new_post_submit" value="<?php echo stripslashes( get_option( 'wpuf_post_submit_label' ) ); ?>">
                        <input type="hidden" name="wpuf_post_type" value="<?php echo $post_type; ?>" />
                        <input type="hidden" name="wpuf_post_new_submit" value="yes" />
                    </li>

                    <?php do_action( 'wpuf_add_post_form_bottom', $post_type ); ?>

                </ul>
            </form>
        </div>
        <?php
    } else {
        echo '<div class="info">' . $info . '</div>';
    }
}

function wpuf_init_posting_check() {
    if ( has_shortcode( 'wpuf_addpost' ) ) {
        if ( isset( $_POST['wpuf_post_new_submit'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];
            if ( !wp_verify_nonce( $nonce, 'wpuf-add-post' ) ) {
                wp_die( __( 'Cheating?' ) );
            }

            wpuf_validate_post_submit();
        }
    }
}

add_action( 'template_redirect', 'wpuf_init_posting_check' );

/**
 * Validate the post submit data
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 *
 * @global type $userdata
 * @param type $post_type
 */
function wpuf_validate_post_submit() {
    global $userdata;

    $errors = array();

    //if there is some attachement, validate them
    if ( !empty( $_FILES['wpuf_post_attachments'] ) ) {
        $errors = wpuf_check_upload();
    }

    $title = trim( $_POST['wpuf_post_title'] );
    $content = trim( $_POST['wpuf_post_content'] );
    $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
    $cat = $_POST['category'];

    //validate title
    if ( empty( $title ) ) {
        $errors[] = __( 'Empty post title', 'wpuf' );
    } else {
        $title = trim( strip_tags( $title ) );
    }

    //validate cat
    if ( $cat == '-1' ) {
        $errors[] = __( 'Please choose a category', 'wpuf' );
    }

    //validate post content
    if ( empty( $content ) ) {
        $errors[] = __( 'Empty post content', 'wpuf' );
    } else {
        $content = trim( $content );
    }

    //process tags
    if ( !empty( $tags ) ) {
        $tags = explode( ',', $tags );
    }

    //post type
    $post_type = trim( strip_tags( $_POST['wpuf_post_type'] ) );

    //process the custom fields
    $custom_fields = array();

    $fields = wpuf_get_custom_fields();
    if ( is_array( $fields ) ) {

        foreach ($fields as $cf) {
            if ( array_key_exists( $cf['field'], $_POST ) ) {

                $temp = trim( strip_tags( $_POST[$cf['field']] ) );
                //var_dump($temp, $cf);

                if ( ( $cf['type'] == 'yes' ) && !$temp ) {
                    $errors[] = sprintf( __( '%s is missing', 'wpuf' ), $cf['label'] );
                } else {
                    $custom_fields[$cf['field']] = $temp;
                }
            } //array_key_exists
        } //foreach
    } //is_array

    $errors = apply_filters( 'wpuf_add_post_validation', $errors );


    //if not any errors, proceed
    if ( !$errors ) {
        $post_stat = ( get_option( 'wpuf_post_status' ) ) ? get_option( 'wpuf_post_status' ) : 'publish';
        $post_author = ( get_option( 'wpuf_post_author' ) == 'original' ) ? $userdata->ID : get_option( 'wpuf_map_author' );

        //users are allowed to choose category
        if ( get_option( 'wpuf_allow_choose_cat' ) == 'yes' ) {
            $post_category = $cat;
        } else {
            $post_category = array(get_option( 'wpuf_default_cat' ));
        }

        $my_post = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $post_stat,
            'post_author' => $post_author,
            'post_category' => $post_category,
            'post_type' => $post_type,
            'tags_input' => $tags
        );

        //plugin API to extend the functionality
        $my_post = apply_filters( 'wpuf_add_post_args', $my_post );

        //insert the post
        $post_id = wp_insert_post( $my_post );

        if ( $post_id ) {

            //upload attachment to the post
            wpuf_upload_attachment( $post_id );

            //send mail notification
            if ( get_option( 'wpuf_notify' ) == 'yes' ) {
                wpuf_notify_post_mail( $userdata, $post_id );
            }

            //add the custom fields
            if ( $custom_fields ) {
                foreach ($custom_fields as $key => $val) {
                    add_post_meta( $post_id, $key, $val, true );
                }
            }

            //plugin API to extend the functionality
            do_action( 'wpuf_add_post_after_insert', $post_id );

            //echo '<div class="success">' . __('Post published successfully', 'wpuf') . '</div>';
            if ( $post_id ) {
                $redirect = get_permalink( $post_id );
                $redirect = apply_filters( 'wpuf_after_post_redirect', $redirect, $post_id );
                wp_redirect( $redirect );
            }
        }
    } else {
        //echo wpuf_error_msg( $errors );
    }
}
