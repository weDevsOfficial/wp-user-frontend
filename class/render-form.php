<?php

/**
 * Handles form generaton and posting for add/edit post in frontend
 *
 * @package WP User Frontend
 */
class WPUF_Render_Form {

    static $meta_key            = 'wpuf_form';
    static $separator           = '| ';
    static $config_id           = '_wpuf_form_id';
    private $form_condition_key = 'wpuf_cond';
    private static $_instance;
    private $field_count = 0;
    public $multiform_start = 0;

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new WPUF_Render_Form();
        }

        return self::$_instance;
    }

    /**
     * Send json error message
     *
     * @param string $error
     */
    function send_error( $error ) {
        echo json_encode( array(
            'success' => false,
            'error'   => $error
        ) );

        die();
    }

    /**
     * Search on multi dimentional array
     *
     * @param array $array
     * @param string $key name of key
     * @param string $value the value to search
     * @return array
     */
    function search( $array, $key, $value ) {
        $results = array();

        if ( is_array( $array ) ) {
            if ( isset( $array[$key] ) && $array[$key] == $value )
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge( $results, $this->search( $subarray, $key, $value ) );
        }

        return $results;
    }

    /**
     * Really simple captcha validation
     *
     * @return void
     */
    function validate_rs_captcha() {
        $rs_captcha_input = isset( $_POST['rs_captcha'] ) ? $_POST['rs_captcha'] : '';
        $rs_captcha_file  = isset( $_POST['rs_captcha_val'] ) ? $_POST['rs_captcha_val'] : '';

        if ( class_exists( 'ReallySimpleCaptcha' ) ) {
            $captcha_instance = new ReallySimpleCaptcha();

            if ( !$captcha_instance->check( $rs_captcha_file, $rs_captcha_input ) ) {

                $this->send_error( __( 'Really Simple Captcha validation failed', 'wpuf' ) );
            } else {
                // validation success, remove the files
                $captcha_instance->remove( $rs_captcha_file );
            }
        }
    }

    /**
     * reCaptcha Validation
     *
     * @return void
     */
    function validate_re_captcha() {
        $recap_challenge = isset( $_POST['recaptcha_challenge_field'] ) ? $_POST['recaptcha_challenge_field'] : '';
        $recap_response  = isset( $_POST['recaptcha_response_field'] ) ? $_POST['recaptcha_response_field'] : '';
        $private_key     = wpuf_get_option( 'recaptcha_private', 'wpuf_general' );

        $resp            = recaptcha_check_answer( $private_key, $_SERVER["REMOTE_ADDR"], $recap_challenge, $recap_response );

        if ( !$resp->is_valid ) {
            $this->send_error( __( 'reCAPTCHA validation failed', 'wpuf' ) );
        }
    }

    /**
     * Guess a suitable username for registration based on email address
     * @param string $email email address
     * @return string username
     */
    function guess_username( $email ) {
        // username from email address
        $username = sanitize_user( substr( $email, 0, strpos( $email, '@' ) ) );

        if ( !username_exists( $username ) ) {
            return $username;
        }

        // try to add some random number in username
        // and may be we got our username
        $username .= rand( 1, 199 );
        if ( !username_exists( $username ) ) {
            return $username;
        }
    }

    /**
     * Get input meta fields separated as post vars, taxonomy and meta vars
     *
     * @param int $form_id form id
     * @return array
     */
    public static function get_input_fields( $form_id ) {
        $form_vars    = wpuf_get_form_fields( $form_id );

        $ignore_lists = array('section_break', 'html');
        $post_vars    = $meta_vars = $taxonomy_vars = array();

        foreach ($form_vars as $key => $value) {

            // ignore section break and HTML input type
            if ( in_array( $value['input_type'], $ignore_lists ) ) {
                continue;
            }

            //separate the post and custom fields
            if ( isset( $value['is_meta'] ) && $value['is_meta'] == 'yes' ) {
                $meta_vars[] = $value;
                continue;
            }

            if ( $value['input_type'] == 'taxonomy' ) {

                // don't add "category"
                if ( $value['name'] == 'category' ) {
                    continue;
                }

                $taxonomy_vars[] = $value;
            } else {
                $post_vars[] = $value;
            }
        }

        return array($post_vars, $taxonomy_vars, $meta_vars);
    }

    public static function prepare_meta_fields( $meta_vars ) {
        // loop through custom fields
        // skip files, put in a key => value paired array for later executation
        // process repeatable fields separately
        // if the input is array type, implode with separator in a field

        $files          = array();
        $meta_key_value = array();
        $multi_repeated = array(); //multi repeated fields will in sotre duplicated meta key

        foreach ($meta_vars as $key => $value) {

            // put files in a separate array, we'll process it later
            if ( ($value['input_type'] == 'file_upload') || ($value['input_type'] == 'image_upload') ) {
                $files[] = array(
                    'name' => $value['name'],
                    'value' => isset( $_POST['wpuf_files'][$value['name']] ) ? $_POST['wpuf_files'][$value['name']] : array()
                );

                // process repeatable fiels
            } elseif ( $value['input_type'] == 'repeat' ) {

                // if it is a multi column repeat field
                if ( isset( $value['multiple'] ) ) {

                    // if there's any items in the array, process it
                    if ( $_POST[$value['name']] ) {

                        $ref_arr = array();
                        $cols    = count( $value['columns'] );
                        $first   = array_shift( array_values( $_POST[$value['name']] ) ); //first element
                        $rows    = count( $first );

                        // loop through columns
                        for ($i = 0; $i < $rows; $i++) {

                            // loop through the rows and store in a temp array
                            $temp = array();
                            for ($j = 0; $j < $cols; $j++) {

                                $temp[] = $_POST[$value['name']][$j][$i];
                            }

                            // store all fields in a row with self::$separator separated
                            $ref_arr[] = implode( self::$separator, $temp );
                        }

                        // now, if we found anything in $ref_arr, store to $multi_repeated
                        if ( $ref_arr ) {
                            $multi_repeated[$value['name']] = array_slice( $ref_arr, 0, $rows );
                        }
                    }
                } else {
                    $meta_key_value[$value['name']] = implode( self::$separator, $_POST[$value['name']] );
                }

                // process other fields
            } elseif ( $value['input_type'] == 'address' ) {

                if( isset( $_POST[ $value['name'] ] ) && is_array( $_POST[ $value['name'] ] ) ) {
                    foreach ( $_POST[ $value['name'] ] as $address_field => $field_value ) {
                        $meta_key_value[ $value['name'] ][ $address_field ] = $field_value;
                    }
                }

            }
            else {
                // if it's an array, implode with this->separator
                if ( is_array( $_POST[$value['name']] ) ) {

                    if ( $value['input_type'] == 'address' ) {
                        $meta_key_value[$value['name']] = $_POST[$value['name']];
                    } else {
                        $meta_key_value[$value['name']] = implode( self::$separator, $_POST[$value['name']] );
                    }
                } else {
                    $meta_key_value[$value['name']] = trim( $_POST[$value['name']] );
                }
            }
        } //end foreach

        return array($meta_key_value, $multi_repeated, $files);
    }

    function guest_fields( $form_settings ) {
        ?>
        <li class="el-name">
            <div class="wpuf-label">
                <label><?php echo $form_settings['name_label']; ?> <span class="required">*</span></label>
            </div>

            <div class="wpuf-fields">
                <input type="text" required="required" data-required="yes" data-type="text" name="guest_name" value="" size="40">
            </div>
        </li>

        <li class="el-email">
            <div class="wpuf-label">
                <label><?php echo $form_settings['email_label']; ?> <span class="required">*</span></label>
            </div>

            <div class="wpuf-fields">
                <input type="email" required="required" data-required="yes" data-type="email" name="guest_email" value="" size="40">
            </div>
        </li>
        <?php
    }

    /**
     * Handles the add post shortcode
     *
     * @param $atts
     */
    function render_form( $form_id, $post_id = NULL, $preview = false ) {

        $form_vars = wpuf_get_form_fields( $form_id );
        $form_settings = wpuf_get_form_settings( $form_id );

        if ( ! is_user_logged_in() && $form_settings['guest_post'] != 'true' ) {
            echo '<div class="wpuf-message">' . $form_settings['message_restrict'] . '</div>';
            wp_login_form();
            return;
        }

        if ( $form_vars ) {
            ?>

            <?php if ( !$preview ) { ?>
                <form class="wpuf-form-add" action="" method="post">
                <?php } ?>

                <ul class="wpuf-form">

                    <?php
                    if ( !$post_id ) {
                        do_action( 'wpuf_add_post_form_top', $form_id, $form_settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_top', $form_id, $post_id, $form_settings );
                    }

                    if ( !is_user_logged_in() && $form_settings['guest_post'] == 'true' && $form_settings['guest_details'] == 'true' ) {
                        $this->guest_fields( $form_settings );
                    }

                    $this->render_items( $form_vars, $post_id, 'post', $form_id, $form_settings );
                    $this->submit_button( $form_id, $form_settings, $post_id );

                    if ( !$post_id ) {
                        do_action( 'wpuf_add_post_form_bottom', $form_id, $form_settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_bottom', $form_id, $post_id, $form_settings );
                    }
                    ?>

                </ul>

                <?php if ( !$preview ) { ?>
                </form>
            <?php } ?>

            <?php
        } //endif
    }

    function render_item_before( $form_field, $post_id ) {
        $label_exclude = array('section_break', 'html', 'action_hook', 'toc');
        $el_name       = !empty( $form_field['name'] ) ? $form_field['name'] : '';
        $class_name    = !empty( $form_field['css'] ) ? ' ' . $form_field['css'] : '';

        printf( '<li class="wpuf-el %s%s">', $el_name, $class_name );

        if ( isset( $form_field['input_type'] ) && !in_array( $form_field['input_type'], $label_exclude ) ) {
            $this->label( $form_field, $post_id );
        }
    }

    function render_item_after( $form_field ) {
        echo '</li>';
    }

    function conditional_logic( $form_field, $form_id ) {

        $cond_inputs = $form_field['wpuf_cond'];
        $cond_inputs['condition_status'] = isset( $cond_inputs['condition_status'] ) ? $cond_inputs['condition_status'] : '';

        if ( $cond_inputs['condition_status'] == 'yes') {
            $cond_inputs['type']    = $form_field['input_type'];
            $cond_inputs['name']    = $form_field['name'];
            $cond_inputs['form_id'] = $form_id;
            $condition              = json_encode( $cond_inputs );

        } else {
            $condition = '';
        }

        //taxnomy name create unique
        if ( $form_field['input_type'] == 'taxonomy' ) {
            $cond_inputs['name'] = $form_field['name'] . '_' . $form_field['type'] .'_'. $form_field['id'];
            $condition           = json_encode( $cond_inputs );
        }

        //for section break
        if ( $form_field['input_type'] == 'section_break' ) {
            $cond_inputs['name'] = $form_field['name'] .'_'. $form_field['id'];
            $condition           = json_encode( $cond_inputs );
        }


        ?>
        <script type="text/javascript">

            wpuf_conditional_items.push(<?php echo $condition; ?>);

        </script>
        <?php

    }

    /**
     * Render form items
     *
     * @param array $form_vars
     * @param int|null $post_id
     * @param string $type type of the form. post or user
     */
    function render_items( $form_vars, $post_id, $type = 'post', $form_id, $form_settings, $cond_inputs = array() ) {

        $edit_ignore = array('recaptcha', 'really_simple_captcha');
        $hidden_fields = array();
        ?>
        <script type="text/javascript">
            wpuf_conditional_items = [];
        </script>
        <?php

        //through var, we will know if multiform step started already
        //$multiform_start = 0;

        //if multistep form is enabled
        if ( isset( $form_settings['enable_multistep'] ) && $form_settings['enable_multistep'] == 'yes' ) {
            ?>
            <input type="hidden" name="wpuf_multistep_type" value="<?php echo $form_settings['multistep_progressbar_type'] ?>"/>
            <?php
            if ( $form_settings['multistep_progressbar_type'] == 'step_by_step' ){
                ?>
                <!--wpuf-multistep-progressbar-> wpuf_ms_pb-->
                <div class="wpuf-multistep-progressbar">

                </div>
            <?php
            } else {
                ?>
                <div class="wpuf-multistep-progressbar">

                </div>
            <?php

            }

        }

        foreach ($form_vars as $key => $form_field) {
            // don't show captcha in edit page
            if ( $post_id && in_array( $form_field['input_type'], $edit_ignore ) ) {
                continue;
            }

            // igonre the hidden fields
            if ( $form_field['input_type'] == 'hidden' ) {
                $hidden_fields[] = $form_field;
                continue;
            }

            if ( $form_field['input_type'] != 'step_start' && $form_field['input_type'] != 'step_end' ) {
                $this->render_item_before( $form_field, $post_id );
            }

            $this->field_count++;

            switch ($form_field['input_type']) {
                case 'text':
                    $this->text( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'textarea':
                    $this->textarea( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'select':
                    $this->select( $form_field, false, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'multiselect':
                    $this->select( $form_field, true, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'radio':
                    $this->radio( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'checkbox':
                    $this->checkbox( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'url':
                    $this->url( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'email':
                    $this->email( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'password':
                    $this->password( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'taxonomy':

                    $this->taxonomy( $form_field, $post_id, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'section_break':
                    $form_field['name'] = 'section_break';
                    $this->section_break( $form_field, $post_id, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'html':
                    $form_field['name'] = 'custom_html';

                    $this->html( $form_field, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                case 'image_upload':
                    $this->image_upload( $form_field, $post_id, $type, $form_id );
                    $this->conditional_logic( $form_field, $form_id );
                    break;

                default:
                    do_action( 'wpuf_render_form_' . $form_field['input_type'], $form_field, $form_id, $post_id, $form_settings );
                    do_action( 'wpuf_render_pro_' . $form_field['input_type'], $form_field, $post_id, $type, $form_id, $form_settings, 'WPUF_Render_Form', $this, $this->multiform_start, isset( $form_settings['enable_multistep'] )?$form_settings['enable_multistep']:'' );
                    break;
            }


            $this->render_item_after( $form_field );
        } //end foreach

        if ( $hidden_fields ) {
            foreach($hidden_fields as $field) {
                printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $field['name'] ), esc_attr( $field['meta_value'] ) );
                echo "\r\n";
            }
        }
    }

    function submit_button( $form_id, $form_settings, $post_id ) {
        ?>
        <li class="wpuf-submit">
            <div class="wpuf-label">
                &nbsp;
            </div>

            <?php wp_nonce_field( 'wpuf_form_add' ); ?>
            <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
            <input type="hidden" name="page_id" value="<?php echo get_post() ? get_the_ID() : '0'; ?>">
            <input type="hidden" name="action" value="wpuf_submit_post">

            <?php
            if ( $post_id ) {
                $cur_post = get_post( $post_id );
                ?>
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <input type="hidden" name="post_date" value="<?php echo esc_attr( $cur_post->post_date ); ?>">
                <input type="hidden" name="comment_status" value="<?php echo esc_attr( $cur_post->comment_status ); ?>">
                <input type="hidden" name="post_author" value="<?php echo esc_attr( $cur_post->post_author ); ?>">
                <input type="submit" name="submit" value="<?php echo $form_settings['update_text']; ?>" />
            <?php } else { ?>
                <input type="submit" name="submit" value="<?php echo $form_settings['submit_text']; ?>" />
                <input type="hidden" name="wpuf_form_status" value="new">
            <?php } ?>

            <?php if ( isset( $form_settings['draft_post'] ) && $form_settings['draft_post'] == 'true' ) { ?>
                <a href="#" class="btn" id="wpuf-post-draft"><?php _e( 'Save Draft', 'wpuf' ); ?></a>
            <?php } ?>
        </li>
    <?php
    }



    /**
     * Form preview handler
     *
     * @return void
     */
    function preview_form() {
        $form_id = isset( $_GET['form_id'] ) ? intval( $_GET['form_id'] ) : 0;


        if ( $form_id ) {
            ?>

            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Form Preview</title>
                    <link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/frontend-forms.css', dirname( __FILE__ ) ); ?>">

                    <style type="text/css">
                        body {
                            margin: 0;
                            padding: 0;
                            background: #eee;
                        }

                        .container {
                            width: 700px;
                            margin: 0 auto;
                            margin-top: 20px;
                            padding: 20px;
                            background: #fff;
                            border: 1px solid #DFDFDF;
                            -webkit-box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                            box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                        }
                    </style>
                </head>
                <body>
                    <div class="container">

                        <?php $this->render_form( $form_id, null, true ); ?>
                    </div>
                </body>
            </html>

            <?php
        } else {
            wp_die( 'Error generating the form preview' );
        }

        exit;
    }

    /**
     * Prints required field asterisk
     *
     * @param array $attr
     * @return string
     */
    function required_mark( $attr ) {
        if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
            return ' <span class="required">*</span>';
        }
    }

    /**
     * Prints HTML5 required attribute
     *
     * @param array $attr
     * @return string
     */
    function required_html5( $attr ) {
        if ( $attr['required'] == 'yes' ) {
            // echo ' required="required"';
        }
    }

    /**
     * Print required class name
     *
     * @param array $attr
     * @return string
     */
    function required_class( $attr ) {
        return;
        if ( $attr['required'] == 'yes' ) {
            echo ' required';
        }
    }

    /**
     * Prints form input label
     *
     * @param string $attr
     */
    function label( $attr, $post_id = 0 ) {
        if ( $post_id && $attr['input_type'] == 'password') {
            $attr['required'] = 'no';
        }
        ?>
        <div class="wpuf-label">
            <label for="wpuf-<?php echo isset( $attr['name'] ) ? $attr['name'] : 'cls'; ?>"><?php echo $attr['label'] . $this->required_mark( $attr ); ?></label>
        </div>
        <?php
    }

    /**
     * Check if its a meta field
     *
     * @param array $attr
     * @return boolean
     */
    function is_meta( $attr ) {
        if ( isset( $attr['is_meta'] ) && $attr['is_meta'] == 'yes' ) {
            return true;
        }

        return false;
    }

    /**
     * Get a meta value
     *
     * @param int $object_id user_ID or post_ID
     * @param string $meta_key
     * @param string $type post or user
     * @param bool $single
     * @return string
     */
    function get_meta( $object_id, $meta_key, $type = 'post', $single = true ) {
        if ( !$object_id ) {
            return '';
        }

        if ( $type == 'post' ) {
            return get_post_meta( $object_id, $meta_key, $single );
        }

        return get_user_meta( $object_id, $meta_key, $single );
    }

    function get_user_data( $user_id, $field ) {
        return get_user_by( 'id', $user_id )->$field;
    }

    /**
     * Prints a text field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function text( $attr, $post_id, $type = 'post', $form_id = null ) {
        // checking for user profile username
        $username = false;
        $taxonomy = false;

        if ( $post_id ) {

            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type );
            } else {

                // applicable for post tags
                if ( $type == 'post' && $attr['name'] == 'tags' ) {
                    $post_tags = wp_get_post_tags( $post_id );
                    $tagsarray = array();
                    foreach ($post_tags as $tag) {
                        $tagsarray[] = $tag->name;
                    }

                    $value = implode( ', ', $tagsarray );
                    $taxonomy = true;
                } elseif ( $type == 'post' ) {
                    $value = get_post_field( $attr['name'], $post_id );
                } elseif ( $type == 'user' ) {
                    $value = get_user_by( 'id', $post_id )->$attr['name'];

                    if ( $attr['name'] == 'user_login' ) {
                        $username = true;
                    }
                }
            }
        } else {
            $value = $attr['default'];

            if ( $type == 'post' && $attr['name'] == 'tags' ) {
                $taxonomy = true;
            }
        }

        ?>

        <div class="wpuf-fields">
            <input class="textfield<?php echo $this->required_class( $attr );  echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" id="<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" <?php echo $username ? 'disabled' : ''; ?> />
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>

            <?php if ( $taxonomy ) { ?>
            <script type="text/javascript">
                jQuery(function($) {
                    $('li.tags input[name=tags]').suggest( wpuf_frontend.ajaxurl + '?action=ajax-tag-search&tax=post_tag', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
                });
            </script>
            <?php } ?>
        </div>

        <?php
    }


    /**
     * Function to check word restriction
     *
     * @param $word_nums number of words allowed
     */
    function check_word_restriction_func($word_nums, $field_type, $field_name){
        ?>
        <script type="text/javascript">
            var editor_limit = '<?php echo $word_nums; ?>',
                field_type = '<?php echo $field_type; ?>',
                field_name = '<?php echo $field_name; ?>';

            // jQuery ready fires too early, use window.onload instead
            window.onload = function () {

                var word_limit_message = "<?php _e( 'Word Limit Reached !', 'wpuf' ); ?>"
                if ( field_type !== 'no' ) {

                    tinyMCE.activeEditor.onKeyDown.add( function(ed,event) {
                        editor_content = this.getContent().split(' ');
                        editor_limit ? jQuery('.mce-path-item.mce-last').html('Word Limit : '+ editor_content.length +'/'+editor_limit):'';

                        if ( editor_limit && editor_content.length > editor_limit ) {
                            block_typing(event);
                        }
                    });

                    tinyMCE.activeEditor.onPaste.add(function(ed, e) {
                        //console.log(e.clipboardData.getData('text/plain'));
                        //getting cursor current position
                        make_media_embed_code(e.clipboardData.getData('text/plain'),ed);

                    });
                } else {

                    jQuery('textarea[name="'+ field_name +'"]').keydown(function(e){
                        editor_content = jQuery(this).val().split(' ');
                        if ( editor_limit && editor_content.length > editor_limit ) {
                            jQuery(this).closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html( word_limit_message );
                            block_typing(e);
                        } else {
                            jQuery(this).closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');
                        }
                    });
                }

                var block_typing = function (event){
                    // Allow: backspace, delete, tab, escape, minus enter and . backspace = 8,delete=46,tab=9,enter=13,.=190,escape=27, minus = 189
                    if (jQuery.inArray(event.keyCode, [46, 8, 9, 27, 13, 110, 190, 189]) !== -1 ||
                        // Allow: Ctrl+A
                        (event.keyCode == 65 && event.ctrlKey === true) ||
                        // Allow: home, end, left, right, down, up
                        (event.keyCode >= 35 && event.keyCode <= 40)) {
                        // let it happen, don't do anything
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    jQuery('.mce-path-item.mce-last').html( word_limit_message );
                }

                var make_media_embed_code = function(content,editor){
                    jQuery.post( ajaxurl, {
                            action:'make_media_embed_code',
                            content: content
                        },
                        function(data){
                            console.log(data);
                            editor.setContent(editor.getContent() + editor.setContent(data));
                        }
                    )
                }
                jQuery()
            }
        </script>
        <?php

    }

    /**
     * Prints a textarea field
     * @param array $attr
     * @param int|null $post_id
     */
    function textarea( $attr, $post_id, $type, $form_id ) {
        $req_class = ( $attr['required'] == 'yes' ) ? 'required' : 'rich-editor';
        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {

                if ( $type == 'post' ) {
                    $value = get_post_field( $attr['name'], $post_id );
                } else {
                    $value = $this->get_user_data( $post_id, $attr['name'] );
                }
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <?php if ( in_array( $attr['rich'], array( 'yes', 'teeny' ) ) ) { ?>
            <div class="wpuf-fields wpuf-rich-validation <?php printf( 'wpuf_%s_%s', $attr['name'], $form_id ); ?>" data-type="rich" data-required="<?php echo esc_attr( $attr['required'] ); ?>" data-id="<?php echo esc_attr( $attr['name'] ); ?>">
        <?php } else { ?>
            <div class="wpuf-fields">
        <?php } ?>

            <?php if ( isset( $attr['insert_image'] ) && $attr['insert_image'] == 'yes' ) { ?>
                <div id="wpuf-insert-image-container">
                    <a class="wpuf-button" id="wpuf-insert-image" href="#">
                        <span class="wpuf-media-icon"></span>
                        <?php _e( 'Insert Photo', 'wpuf' ); ?>
                    </a>
                </div>
            <?php } ?>

            <?php
            $textarea_id = $attr['name']?$attr['name']:'textarea_'.$this->field_count;
            if ( $attr['rich'] == 'yes' ) {

                wp_editor( $value, $textarea_id, array('textarea_rows' => $attr['rows'], 'quicktags' => false, 'media_buttons' => false, 'editor_class' => $req_class) );

            } elseif( $attr['rich'] == 'teeny' ) {

                wp_editor( $value, $textarea_id, array('textarea_rows' => $attr['rows'], 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => $req_class) );
            } else {
                ?>
                <textarea class="textareafield<?php echo $this->required_class( $attr ); ?> <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" id="<?php echo $attr['name']; ?>" name="<?php echo $attr['name']; ?>" data-required="<?php echo $attr['required'] ?>" data-type="textarea"<?php $this->required_html5( $attr ); ?> placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" rows="<?php echo $attr['rows']; ?>" cols="<?php echo $attr['cols']; ?>"><?php echo esc_textarea( $value ) ?></textarea>
                <span class="wpuf-wordlimit-message wpuf-help"></span>
            <?php } ?>
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>
        <?php

        if ( isset( $attr['word_restriction'] ) ) {
            $this->check_word_restriction_func( $attr['word_restriction'], $attr['rich'], $attr['name'] );
        }
    }


    /**
     * Prints a select or multiselect field
     *
     * @param array $attr
     * @param bool $multiselect
     * @param int|null $post_id
     */
    function select( $attr, $multiselect = false, $post_id, $type, $form_id = null ) {
        if ( $post_id ) {
            $selected = $this->get_meta( $post_id, $attr['name'], $type );
            $selected = $multiselect ? explode( self::$separator, $selected ) : $selected;
        } else {
            $selected = isset( $attr['selected'] ) ? $attr['selected'] : '';
            $selected = $multiselect ? ( is_array( $selected ) ? $selected : array() ) : $selected;
        }

        $multi = $multiselect ? ' multiple="multiple"' : '';
        $data_type = $multiselect ? 'multiselect' : 'select';
        $css = $multiselect ? ' class="multiselect  wpuf_'. $attr['name'] .'_'. $form_id.'"' : '';

        ?>



        <div class="wpuf-fields">
            <select  <?php echo $css; ?> class="<?php echo 'wpuf_'. $attr['name'] .'_'. $form_id; ?>" name="<?php echo $attr['name']; ?>[]"<?php echo $multi; ?> data-required="<?php echo $attr['required'] ?>" data-type="<?php echo $data_type; ?>"<?php $this->required_html5( $attr ); ?>>

                <?php if ( !empty( $attr['first'] ) ) { ?>
                    <option value=""><?php echo $attr['first']; ?></option>
                <?php } ?>

                <?php
                if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
                    foreach ($attr['options'] as $value => $option) {
                        $current_select = $multiselect ? selected( in_array( $value, $selected ), true, false ) : selected( $selected, $value, false );
                        ?>
                        <option value="<?php echo esc_attr( $value ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>
        <?php
    }

    /**
     * Prints a radio field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function radio( $attr, $post_id, $type, $form_id ) {
        $selected = isset( $attr['selected'] ) ? $attr['selected'] : '';

        if ( $post_id ) {
            $selected = $this->get_meta( $post_id, $attr['name'], $type, true );
        }
        ?>

        <div class="wpuf-fields" data-required="<?php echo $attr['required'] ?>" data-type="radio">

            <?php
            if ( $attr['options'] && count( $attr['options'] ) > 0 ) {
                foreach ($attr['options'] as $value => $option) {
                    ?>

                    <label>
                        <input name="<?php echo $attr['name']; ?>" class="<?php echo 'wpuf_'.$attr['name']. '_'. $form_id; ?>" type="radio" value="<?php echo esc_attr( $value ); ?>"<?php checked( $selected, $value ); ?> />
                        <?php echo $option; ?>
                    </label>
                    <?php
                }
            }
            ?>

            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>

        <?php
    }

    /**
     * Prints a checkbox field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function checkbox( $attr, $post_id, $type, $form_id ) {
        $selected = isset( $attr['selected'] ) ? $attr['selected'] : array();

        if ( $post_id ) {
            if ( $value = $this->get_meta( $post_id, $attr['name'], $type, true ) ) {
                $selected = explode( self::$separator, $value );
            }
        }
        ?>

        <div class="wpuf-fields" data-required="<?php echo $attr['required'] ?>" data-type="radio">

            <?php
            if ( $attr['options'] && count( $attr['options'] ) > 0 ) {

                foreach ($attr['options'] as $value => $option) {

                    ?>

                    <label>
                        <input type="checkbox" class="<?php echo 'wpuf_'.$attr['name']. '_'. $form_id; ?>" name="<?php echo $attr['name']; ?>[]" value="<?php echo esc_attr( $value ); ?>"<?php echo in_array( $value, $selected ) ? ' checked="checked"' : ''; ?> />
                        <?php echo $option; ?>
                    </label>
                    <?php
                }
            }
            ?>

            <div class="wpuf-fields">
                <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
            </div>

        </div>

        <?php
    }

    /**
     * Prints a url field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function url( $attr, $post_id, $type, $form_id ) {

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {
                //must be user profile url
                $value = $this->get_user_data( $post_id, $attr['name'] );
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <div class="wpuf-fields">
            <input id="wpuf-<?php echo $attr['name']; ?>" type="url" class="url <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>

        <?php
    }

    /**
     * Prints a email field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function email( $attr, $post_id, $type = 'post', $form_id ) {
        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type, true );
            } else {
                //must be user email
                $value = $this->get_user_data( $post_id, $attr['name'] );
            }
        } else {
            $value = $attr['default'];
        }
        ?>

        <div class="wpuf-fields">
            <input id="wpuf-<?php echo $attr['name']; ?>" type="email" class="email <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" data-required="<?php echo $attr['required'] ?>" data-type="email" <?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>

        <?php
    }

    /**
     * Prints a email field
     *
     * @param array $attr
     */
    function password( $attr, $post_id, $type, $form_id ) {
        if ( $post_id ) {
            $attr['required'] = 'no';
        }
        ?>

        <div class="wpuf-fields">
            <input id="pass1" type="password" class="password <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="pass1" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>

        <?php
        if ( $attr['repeat_pass'] == 'yes' ) {
            echo '</li>';
            echo '<li>';

            $this->label( array('name' => 'pass2', 'label' => $attr['re_pass_label'], 'required' => $post_id ? 'no' : 'yes') );
            ?>

            <div class="wpuf-fields">
                <input id="pass2" type="password" class="password <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="pass2" value="" size="<?php echo esc_attr( $attr['size'] ) ?>" />
            </div>

            <?php
        }

        if ( $attr['repeat_pass'] == 'yes' && $attr['pass_strength'] == 'yes' ) {
            echo '</li>';
            echo '<li>';
            ?>
            <div class="wpuf-label">
                &nbsp;
            </div>

            <div class="wpuf-fields">
                <div id="pass-strength-result" style="display: block"><?php _e( 'Strength indicator' ); ?></div>
                <script src="<?php echo includes_url( 'js/zxcvbn.min.js' ); ?>"></script>
                <script src="<?php echo admin_url( 'js/password-strength-meter.js' ); ?>"></script>
                <script type="text/javascript">
                    var pwsL10n = {
                        empty: "<?php _e( 'Strength indicator', 'wpuf' ); ?>",
                        short: "<?php _e( 'Very weak', 'wpuf' ); ?>",
                        bad: "<?php _e( 'Weak', 'wpuf' ); ?>",
                        good: "<?php _e( 'Medium', 'wpuf' ); ?>",
                        strong: "<?php _e( 'Strong', 'wpuf' ); ?>",
                        mismatch: "<?php _e( 'Mismatch', 'wpuf' ); ?>"
                    };
                    try{convertEntities(pwsL10n);}catch(e){};
                </script>
            </div>
            <?php
        }

    }


    function taxnomy_select( $terms, $attr ) {

        $selected     = $terms ? $terms : '';
        $required     = sprintf( 'data-required="%s" data-type="select"', $attr['required'] );
        $taxonomy     = $attr['name'];
        $class        = ' wpuf_'.$attr['name'].'_'.$selected;
        $exclude_type = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
        $exclude      = $attr['exclude'];

        $select = wp_dropdown_categories( array(

            'show_option_none' => __( '-- Select --', 'wpuf' ),
            'hierarchical'     => 1,
            'hide_empty'       => 0,
            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
            'name'             => $taxonomy . '[]',
            'taxonomy'         => $taxonomy,
            'echo'             => 0,
            'title_li'         => '',
            'class'            => 'cat-ajax '. $taxonomy . $class,
            $exclude_type      => $exclude,
            'selected'         => $selected,
            'depth'            => 1,
            'child_of'         => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : ''
        ) );

        echo str_replace( '<select', '<select ' . $required, $select );
        $attr = array(
            'required'     => $attr['required'],
            'name'         => $attr['name'],
            'exclude_type' => $attr['exclude_type'],
            'exclude'      => $attr['exclude'],
            'orderby'      => $attr['orderby'],
            'order'        => $attr['order'],
            'name'         => $attr['name'],
            //'last_term_id' => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : '',
            //'term_id'      => $selected
        );
        ?>
        <span data-taxonomy=<?php echo json_encode( $attr ); ?>></span>
        <?php
    }

    /**
     * Prints a taxonomy field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function taxonomy( $attr, $post_id, $form_id ) {

        $exclude_type = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
        $exclude      = $attr['exclude'];
        $taxonomy     = $attr['name'];
        $class        = ' wpuf_'.$attr['name'].'_'.$form_id;


        $terms = array();
        if ( $post_id && $attr['type'] == 'text' ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy, array('fields' => 'names') );
        } elseif( $post_id ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy, array('fields' => 'ids') );
        }

        $div_class = 'wpuf_' . $attr['name'] . '_' . $attr['type'] . '_' . $attr['id'] . '_' . $form_id;
        ?>


        <?php if ( $attr['type'] == 'checkbox' ) { ?>
            <div class="wpuf-fields <?php echo $div_class; ?>" data-required="<?php echo esc_attr( $attr['required'] ); ?>" data-type="tax-checkbox">
        <?php } else { ?>
            <div class="wpuf-fields <?php echo $div_class; ?>">
        <?php } ?>

                <?php
                switch ($attr['type']) {
                    case 'ajax':
                        $class = ' wpuf_'.$attr['name'].'_'.$form_id;
                        ?>
                        <div class="category-wrap <?php echo $class; ?>">
                            <?php

                            if ( !count( $terms ) ) {

                                ?>
                                <div id="lvl0" level="0">
                                    <?php $this->taxnomy_select( null, $attr, $form_id ); ?>
                                </div>
                                <?php
                            } else {

                                $level = 0;
                                asort( $terms );
                                $last_term_id = end( $terms );

                                foreach( $terms as $term_id) {
                                    $class = ( $last_term_id != $term_id ) ? 'hasChild' : '';
                                    ?>
                                    <div id="lvl<?php echo $level; ?>" level="<?php echo $level; ?>" >
                                        <?php $this->taxnomy_select( $term_id, $attr ); ?>
                                    </div>
                                <?php
                                    $attr['parent_cat'] = $term_id;
                                    $level++;
                                }
                            }

                        ?>
                        </div>
                        <span class="loading"></span>
                        <?php
                        break;
                    case 'select':

                        $selected = $terms ? $terms[0] : '';
                        $required = sprintf( 'data-required="%s" data-type="select"', $attr['required'] );

                        $select = wp_dropdown_categories( array(
                            'show_option_none' => __( '-- Select --', 'wpuf' ),
                            'hierarchical'     => 1,
                            'hide_empty'       => 0,
                            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
                            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
                            'name'             => $taxonomy . '[]',
                            'taxonomy'         => $taxonomy,
                            'echo'             => 0,
                            'title_li'         => '',
                            'class'            => $taxonomy . $class,
                            $exclude_type      => $exclude,
                            'selected'         => $selected,
                        ) );

                        echo str_replace( '<select', '<select ' . $required, $select );
                        break;

                    case 'multiselect':
                        $selected = $terms ? $terms : array();
                        $required = sprintf( 'data-required="%s" data-type="multiselect"', $attr['required'] );
                        $walker = new WPUF_Walker_Category_Multi();

                        $select = wp_dropdown_categories( array(
                            // 'show_option_none' => __( '-- Select --', 'wpuf' ),
                            'hierarchical'     => 1,
                            'hide_empty'       => 0,
                            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
                            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
                            'name'             => $taxonomy . '[]',
                            'id'               => 'cat-ajax',
                            'taxonomy'         => $taxonomy,
                            'echo'             => 0,
                            'title_li'         => '',
                            'class'            => $taxonomy . ' multiselect' . $class,
                            $exclude_type      => $exclude,
                            'selected'         => $selected,
                            'walker'           => $walker
                        ) );

                        echo str_replace( '<select', '<select multiple="multiple" ' . $required, $select );
                        break;

                    case 'checkbox':
                        wpuf_category_checklist( $post_id, false, $attr, $class );
                        break;

                    case 'text':
                        ?>

                        <input class="textfield<?php echo $this->required_class( $attr ); ?>" id="<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" value="<?php echo esc_attr( implode( ', ', $terms ) ); ?>" size="40" />

                        <script type="text/javascript">
                            jQuery(function($) {
                                $('#<?php echo $attr['name']; ?>').suggest( wpuf_frontend.ajaxurl + '?action=ajax-tag-search&tax=<?php echo $attr['name']; ?>', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
                            });
                        </script>

                        <?php
                        break;

                    default:
                        # code...
                        break;
                }
                ?>
            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>
        </div>


        <?php
    }

    /**
     * Prints a HTML field
     *
     * @param array $attr
     */
    function html( $attr, $form_id ) {
        ?>
        <div class="wpuf-fields <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>">
            <?php echo do_shortcode( $attr['html'] ); ?>
        </div>
        <?php
    }

    /**
     * Prints a image upload field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function image_upload( $attr, $post_id, $type, $form_id ) {

        $has_featured_image = false;
        $has_images = false;
        $has_avatar = false;

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $images = $this->get_meta( $post_id, $attr['name'], $type, false );
                $has_images = true;
            } else {

                if ( $type == 'post' ) {
                    // it's a featured image then
                    $thumb_id = get_post_thumbnail_id( $post_id );

                    if ( $thumb_id ) {
                        $has_featured_image = true;
                        $featured_image = WPUF_Upload::attach_html( $thumb_id, 'featured_image' );
                    }
                } else {
                    // it must be a user avatar
                    $has_avatar = true;
                    $featured_image = get_avatar( $post_id );
                }
            }
        }
        ?>

        <div class="wpuf-fields">
            <div id="wpuf-<?php echo $attr['name']; ?>-upload-container">
                <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="<?php echo $attr['required']; ?>">
                    <a id="wpuf-<?php echo $attr['name']; ?>-pickfiles" class="button file-selector <?php echo ' wpuf_' . $attr['name'] . '_' . $form_id; ?>" href="#"><?php _e( 'Select Image', 'wpuf' ); ?></a>

                    <ul class="wpuf-attachment-list thumbnails">
                        <?php
                        if ( $has_featured_image ) {
                            echo $featured_image;
                        }

                        if ( $has_avatar ) {
                            $avatar = get_user_meta( $post_id, 'user_avatar', true );
                            if ( $avatar ) {
                                echo $featured_image;
                                printf( '<br><a href="#" data-confirm="%s" class="wpuf-button button wpuf-delete-avatar">%s</a>', __( 'Are you sure?', 'wpuf' ), __( 'Delete', 'wpuf' ) );
                            }
                        }

                        if ( $has_images ) {
                            foreach ($images as $attach_id) {
                                echo WPUF_Upload::attach_html( $attach_id, $attr['name'] );
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div><!-- .container -->

            <span class="wpuf-help"><?php echo stripslashes( $attr['help'] ); ?></span>

        </div> <!-- .wpuf-fields -->

        <script type="text/javascript">
            jQuery(function($) {
                new WPUF_Uploader('wpuf-<?php echo $attr['name']; ?>-pickfiles', 'wpuf-<?php echo $attr['name']; ?>-upload-container', <?php echo $attr['count']; ?>, '<?php echo $attr['name']; ?>', 'jpg,jpeg,gif,png,bmp', <?php echo $attr['max_size'] ?>);
            });
        </script>
    <?php

    }

    /**
     * Prints a section break
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function section_break( $attr, $post_id, $form_id ) {
        ?>
        <div class="wpuf-section-wrap <?php echo ' wpuf_'.$attr['name'].'_'.$attr['id'].'_'.$form_id; ?>">
            <h2 class="wpuf-section-title"><?php echo $attr['label']; ?></h2>
            <div class="wpuf-section-details"><?php echo $attr['description']; ?></div>
        </div>
        <?php
    }

}
