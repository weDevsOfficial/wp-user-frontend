<?php

/**
 * Add Post form class
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class WPUF_Add_Post {

    function __construct() {
        add_shortcode( 'wpuf_addpost', array($this, 'shortcode') );
    }

    /**
     * Handles the add post shortcode
     *
     * @param $atts
     */
    function shortcode( $atts ) {

        extract( shortcode_atts( array('post_type' => 'post'), $atts ) );

        ob_start();

        if ( is_user_logged_in() ) {
            $this->post_form( $post_type );
        } else {
            printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( get_permalink(), false ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Add posting main form
     *
     * @param $post_type
     */
    function post_form( $post_type ) {
        global $userdata;

        $userdata = get_user_by( 'id', $userdata->ID );

        if ( isset( $_POST['wpuf_post_new_submit'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];
            if ( !wp_verify_nonce( $nonce, 'wpuf-add-post' ) ) {
                wp_die( __( 'Cheating?' ) );
            }

            $this->submit_post();
        }

        $info = __( "Post It!", 'wpuf' );
        $can_post = 'yes';

        $info = apply_filters( 'wpuf_addpost_notice', $info );
        $can_post = apply_filters( 'wpuf_can_post', $can_post );
        $featured_image = wpuf_get_option( 'enable_featured_image' );

        $title = isset( $_POST['wpuf_post_title'] ) ? esc_attr( $_POST['wpuf_post_title'] ) : '';
        $description = isset( $_POST['wpuf_post_content'] ) ? $_POST['wpuf_post_content'] : '';

        if ( $can_post == 'yes' ) {
            ?>
            <div id="wpuf-post-area">
                <form id="wpuf_new_post_form" name="wpuf_new_post_form" action="" enctype="multipart/form-data" method="POST">
                    <?php wp_nonce_field( 'wpuf-add-post' ) ?>

                    <ul class="wpuf-post-form">

                        <?php do_action( 'wpuf_add_post_form_top', $post_type ); //plugin hook   ?>
                        <?php wpuf_build_custom_field_form( 'top' ); ?>

                        <?php if ( $featured_image == 'yes' ) { ?>
                            <?php if ( current_theme_supports( 'post-thumbnails' ) ) { ?>
                                <li>
                                    <label for="post-thumbnail"><?php echo wpuf_get_option( 'ft_image_label' ); ?></label>
                                    <div id="wpuf-ft-upload-container">
                                        <div id="wpuf-ft-upload-filelist"></div>
                                        <a id="wpuf-ft-upload-pickfiles" class="button" href="#"><?php echo wpuf_get_option( 'ft_image_btn_label' ); ?></a>
                                    </div>
                                    <div class="clear"></div>
                                </li>
                            <?php } else { ?>
                                <div class="info"><?php _e( 'Your theme doesn\'t support featured image', 'wpuf' ) ?></div>
                            <?php } ?>
                        <?php } ?>

                        <li>
                            <label for="new-post-title">
                                <?php echo wpuf_get_option( 'title_label' ); ?> <span class="required">*</span>
                            </label>
                            <input class="requiredField" type="text" value="<?php echo $title; ?>" name="wpuf_post_title" id="new-post-title" minlength="2">
                            <div class="clear"></div>
                            <p class="description"><?php echo stripslashes( wpuf_get_option( 'title_help' ) ); ?></p>
                        </li>

                        <?php if ( wpuf_get_option( 'allow_cats' ) == 'on' ) { ?>
                            <li>
                                <label for="new-post-cat">
                                    <?php echo wpuf_get_option( 'cat_label' ); ?> <span class="required">*</span>
                                </label>

                                <div class="category-wrap" style="float:left;">
                                    <div id="lvl0">
                                        <?php
                                        $exclude = wpuf_get_option( 'exclude_cats' );
                                        $cat_type = wpuf_get_option( 'cat_type' );

                                        if ( $cat_type == 'normal' ) {
                                            wp_dropdown_categories( 'show_option_none=' . __( '-- Select --', 'wpuf' ) . '&hierarchical=1&hide_empty=0&orderby=name&name=category[]&id=cat&show_count=0&title_li=&use_desc_for_title=1&class=cat requiredField&exclude=' . $exclude );
                                        } else if ( $cat_type == 'ajax' ) {
                                            wp_dropdown_categories( 'show_option_none=' . __( '-- Select --', 'wpuf' ) . '&hierarchical=1&hide_empty=0&orderby=name&name=category[]&id=cat-ajax&show_count=0&title_li=&use_desc_for_title=1&class=cat requiredField&depth=1&exclude=' . $exclude );
                                        } else {
                                            wpuf_category_checklist();
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="loading"></div>
                                <div class="clear"></div>
                                <p class="description"><?php echo stripslashes( wpuf_get_option( 'cat_help' ) ); ?></p>
                            </li>
                        <?php } ?>

                        <?php do_action( 'wpuf_add_post_form_description', $post_type ); ?>
                        <?php wpuf_build_custom_field_form( 'description' ); ?>

                        <li>
                            <label for="new-post-desc">
                                <?php echo wpuf_get_option( 'desc_label' ); ?> <span class="required">*</span>
                            </label>

                            <?php
                            $editor = wpuf_get_option( 'editor_type' );
                            if ( $editor == 'full' ) {
                                ?>
                                <div style="float:left;">
                                    <?php wp_editor( $description, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8) ); ?>
                                </div>
                            <?php } else if ( $editor == 'rich' ) { ?>
                                <div style="float:left;">
                                    <?php wp_editor( $description, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => true, 'textarea_rows' => 8) ); ?>
                                </div>

                            <?php } else { ?>
                                <textarea name="wpuf_post_content" class="requiredField" id="new-post-desc" cols="60" rows="8"><?php echo esc_textarea( $description ); ?></textarea>
                            <?php } ?>

                            <div class="clear"></div>
                            <p class="description"><?php echo stripslashes( wpuf_get_option( 'desc_help' ) ); ?></p>
                        </li>

                        <?php
                        do_action( 'wpuf_add_post_form_after_description', $post_type );

                        $this->publish_date_form();
                        $this->expiry_date_form();

                        wpuf_build_custom_field_form( 'tag' );

                        if ( wpuf_get_option( 'allow_tags' ) == 'on' ) {
                            ?>
                            <li>
                                <label for="new-post-tags">
                                    <?php echo wpuf_get_option( 'tag_label' ); ?>
                                </label>
                                <input type="text" name="wpuf_post_tags" id="new-post-tags" class="new-post-tags">
                                <p class="description"><?php echo stripslashes( wpuf_get_option( 'tag_help' ) ); ?></p>
                                <div class="clear"></div>
                            </li>
                            <?php
                        }

                        do_action( 'wpuf_add_post_form_tags', $post_type );
                        wpuf_build_custom_field_form( 'bottom' );
                        ?>

                        <li>
                            <label>&nbsp;</label>
                            <input class="wpuf_submit" type="submit" name="wpuf_new_post_submit" value="<?php echo esc_attr( wpuf_get_option( 'submit_label' ) ); ?>">
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

    /**
     * Prints the post publish date on form
     *
     * @return bool|string
     */
    function publish_date_form() {
        $enable_date = wpuf_get_option( 'enable_post_date' );

        if ( $enable_date != 'on' ) {
            return;
        }

        $timezone_format = _x( 'Y-m-d G:i:s', 'timezone date format' );
        $month = date_i18n( 'm' );
        $month_array = array(
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec'
        );
        ?>
        <li>
            <label for="timestamp-wrap">
                <?php _e( 'Publish Time:', 'wpuf' ); ?> <span class="required">*</span>
            </label>
            <div class="timestamp-wrap">
                <select name="mm">
                    <?php
                    foreach ($month_array as $key => $val) {
                        $selected = ( $key == $month ) ? ' selected="selected"' : '';
                        echo '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
                    }
                    ?>
                </select>
                <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n( 'd' ); ?>" name="jj">,
                <input type="text" autocomplete="off" tabindex="4" maxlength="4" size="4" value="<?php echo date_i18n( 'Y' ); ?>" name="aa">
                @ <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n( 'G' ); ?>" name="hh">
                : <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n( 'i' ); ?>" name="mn">
            </div>
            <div class="clear"></div>
            <p class="description"></p>
        </li>
        <?php
    }

    /**
     * Prints post expiration date on the form
     *
     * @return bool|string
     */
    function expiry_date_form() {
        $post_expiry = wpuf_get_option( 'enable_post_expiry' );

        if ( $post_expiry != 'on' ) {
            return;
        }
        ?>
        <li>
            <label for="timestamp-wrap">
                <?php _e( 'Expiration Time:', 'wpuf' ); ?><span class="required">*</span>
            </label>
            <select name="expiration-date">
                <?php
                for ($i = 1; $i <= 90; $i++) {
                    if ( $i % 2 != 0 ) {
                        continue;
                    }

                    printf( '<option value="%1$d">%1$d %2$s</option>', $i, __( 'days', 'wpuf' ) );
                }
                ?>
            </select>
            <div class="clear"></div>
            <p class="description"><?php _e( 'Post expiration time in day after publishing.', 'wpuf' ); ?></p>
        </li>
        <?php
    }

    /**
     * Validate the post submit data
     *
     * @global type $userdata
     * @param type $post_type
     */
    function submit_post() {
        global $userdata;

        $errors = array();

        //if there is some attachement, validate them
        if ( !empty( $_FILES['wpuf_post_attachments'] ) ) {
            $errors = wpuf_check_upload();
        }

        $title = trim( $_POST['wpuf_post_title'] );
        $content = trim( $_POST['wpuf_post_content'] );

        $tags = '';
        if ( isset( $_POST['wpuf_post_tags'] ) ) {
            $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
        }

        //validate title
        if ( empty( $title ) ) {
            $errors[] = __( 'Empty post title', 'wpuf' );
        } else {
            $title = trim( strip_tags( $title ) );
        }

        //validate cat
        if ( wpuf_get_option( 'allow_cats' ) == 'on' ) {
            $cat_type = wpuf_get_option( 'cat_type' );
            if ( !isset( $_POST['category'] ) ) {
                $errors[] = __( 'Please choose a category', 'wpuf' );
            } else if ( $cat_type == 'normal' && $_POST['category'][0] == '-1' ) {
                $errors[] = __( 'Please choose a category', 'wpuf' );
            } else {
                if ( count( $_POST['category'] ) < 1 ) {
                    $errors[] = __( 'Please choose a category', 'wpuf' );
                }
            }
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

        //post attachment
        $attach_id = isset( $_POST['wpuf_featured_img'] ) ? intval( $_POST['wpuf_featured_img'] ) : 0;

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

        $post_date_enable = wpuf_get_option( 'enable_post_date' );
        $post_expiry = wpuf_get_option( 'enable_post_expiry' );

        //check post date
        if ( $post_date_enable == 'on' ) {
            $month = $_POST['mm'];
            $day = $_POST['jj'];
            $year = $_POST['aa'];
            $hour = $_POST['hh'];
            $min = $_POST['mn'];

            if ( !checkdate( $month, $day, $year ) ) {
                $errors[] = __( 'Invalid date', 'wpuf' );
            }
        }

        $errors = apply_filters( 'wpuf_add_post_validation', $errors );


        //if not any errors, proceed
        if ( $errors ) {
            echo wpuf_error_msg( $errors );
            return;
        }

        $post_stat = wpuf_get_option( 'post_status' );
        $post_author = (wpuf_get_option( 'post_author' ) == 'original' ) ? $userdata->ID : wpuf_get_option( 'map_author' );

        //users are allowed to choose category
        if ( wpuf_get_option( 'allow_cats' ) == 'on' ) {
            $post_category = $_POST['category'];
        } else {
            $post_category = array(wpuf_get_option( 'default_cat' ));
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

        if ( $post_date_enable == 'on' ) {
            $month = $_POST['mm'];
            $day = $_POST['jj'];
            $year = $_POST['aa'];
            $hour = $_POST['hh'];
            $min = $_POST['mn'];

            $post_date = mktime( $hour, $min, 59, $month, $day, $year );
            $my_post['post_date'] = date( 'Y-m-d H:i:s', $post_date );
        }

        //plugin API to extend the functionality
        $my_post = apply_filters( 'wpuf_add_post_args', $my_post );

        //var_dump( $_POST, $my_post );die();
        //insert the post
        $post_id = wp_insert_post( $my_post );

        if ( $post_id ) {

            //upload attachment to the post
            wpuf_upload_attachment( $post_id );

            //send mail notification
            if ( wpuf_get_option( 'post_notification' ) == 'yes' ) {
                wpuf_notify_post_mail( $userdata, $post_id );
            }

            //add the custom fields
            if ( $custom_fields ) {
                foreach ($custom_fields as $key => $val) {
                    add_post_meta( $post_id, $key, $val, true );
                }
            }

            //set post thumbnail if has any
            if ( $attach_id ) {
                set_post_thumbnail( $post_id, $attach_id );
            }

            //Set Post expiration date if has any
            if ( !empty( $_POST['expiration-date'] ) && $post_expiry == 'on' ) {
                $post = get_post( $post_id );
                $post_date = strtotime( $post->post_date );
                $expiration = (int) $_POST['expiration-date'];
                $expiration = $post_date + ($expiration * 60 * 60 * 24);

                add_post_meta( $post_id, 'expiration-date', $expiration, true );
            }

            //plugin API to extend the functionality
            do_action( 'wpuf_add_post_after_insert', $post_id );

            //echo '<div class="success">' . __('Post published successfully', 'wpuf') . '</div>';
            if ( $post_id ) {
                $redirect = apply_filters( 'wpuf_after_post_redirect', get_permalink( $post_id ), $post_id );

                wp_redirect( $redirect );
                exit;
            }
        }
    }

}

$wpuf_postform = new WPUF_Add_Post();