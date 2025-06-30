<?php

namespace WeDevs\Wpuf\Traits;

use Invisible_Recaptcha;
use WeDevs\Wpuf\Fields\Form_Field_Featured_Image;
use WeDevs\Wpuf\Fields\Form_Field_Post_Content;
use WeDevs\Wpuf\Fields\Form_Field_Post_Excerpt;
use WeDevs\Wpuf\Fields\Form_Field_Post_Tags;
use WeDevs\Wpuf\Fields\Form_Field_Post_Taxonomy;
use WeDevs\Wpuf\Fields\Form_Field_Post_Title;

trait FieldableTrait {
    /**
     * WP post types
     *
     * @var string
     */
    private $wp_post_types = [];

    public static $config_id = '_wpuf_form_id';

    public static $separator = ' | ';

    /**
     * Add post field setting on form builder
     *
     * @since 2.5
     *
     * @param array $field_settings
     */
    public function add_field_settings( $field_settings ) {
        $this->set_wp_post_types();

        if ( class_exists( 'WeDevs\Wpuf\Fields\Field_Contract' ) ) {
            $field_settings['post_title']     = new Form_Field_Post_Title();
            $field_settings['post_content']   = new Form_Field_Post_Content();
            $field_settings['post_excerpt']   = new Form_Field_Post_Excerpt();
            $field_settings['featured_image'] = new Form_Field_Featured_Image();

            $taxonomy_templates = [];

            foreach ( $this->wp_post_types as $post_type => $taxonomies ) {
                if ( ! empty( $taxonomies ) ) {
                    foreach ( $taxonomies as $tax_name => $taxonomy ) {
                        if ( 'post_tag' === $tax_name ) {
                            $taxonomy_templates['post_tag'] = new Form_Field_Post_Tags();
                        } else {
                            $taxonomy_templates[ $tax_name ] = new Form_Field_Post_Taxonomy( $tax_name, $taxonomy );
                            // $taxonomy_templates[ 'taxonomy' ] = new WPUF_Form_Field_Post_Taxonomy($tax_name, $taxonomy);
                        }
                    }
                }
            }

            $field_settings = array_merge( $field_settings, $taxonomy_templates );
        }

        return $field_settings;
    }

    /**
     * Populate available wp post types
     *
     * @since 2.5
     *
     * @return void
     */
    public function set_wp_post_types() {
        $args = [ '_builtin' => true ];
        $wpuf_post_types = wpuf_get_post_types( $args );
        $ignore_taxonomies = apply_filters( 'wpuf-ignore-taxonomies', [
            'post_format',
        ] );
        foreach ( $wpuf_post_types as $post_type ) {
            $this->wp_post_types[ $post_type ] = [];
            $taxonomies = get_object_taxonomies( $post_type, 'object' );
            foreach ( $taxonomies as $tax_name => $taxonomy ) {
                if ( ! in_array( $tax_name, $ignore_taxonomies ) ) {
                    $this->wp_post_types[ $post_type ][ $tax_name ] = [
                        'title'        => $taxonomy->label,
                        'hierarchical' => $taxonomy->hierarchical,
                    ];
                    $this->wp_post_types[ $post_type ][ $tax_name ]['terms'] = get_terms( [
                        'taxonomy'   => $tax_name,
                        'hide_empty' => false,
                    ] );
                }
            }
        }
    }

    public function set_default_taxonomy( $post_id ) {
        $post_taxonomies = get_object_taxonomies( $this->form_settings['post_type'], 'objects' );
        foreach ( $post_taxonomies as $tax ) {
            if ( $tax->hierarchical ) {
                $name = 'default_' . $tax->name;
                if ( isset( $this->form_settings[ $name ] ) && ! empty( $this->form_settings[ $name ] ) ) {
                    $value = $this->form_settings[ $name ];
                    wp_set_post_terms( $post_id, $value, $tax->name );
                }
            }
        }
    }

    /**
     * get Input fields
     *
     * @param array $form_vars
     *
     * @return array
     */
    public function get_input_fields( $form_vars ) {
        $ignore_lists = [ 'section_break', 'html' ];
        $post_vars    = $meta_vars = $taxonomy_vars = [];

        foreach ( $form_vars as $key => $value ) {
            // get column field input fields
            if ( $value['input_type'] == 'column_field' ) {
                $inner_fields = $value['inner_fields'];

                foreach ( $inner_fields as $column_key => $column_fields ) {
                    if ( ! empty( $column_fields ) ) {
                        // ignore section break and HTML input type
                        foreach ( $column_fields as $column_field_key => $column_field ) {
                            if ( in_array( $column_field['input_type'], $ignore_lists ) ) {
                                continue;
                            }

                            //separate the post and custom fields
                            if ( isset( $column_field['is_meta'] ) && $column_field['is_meta'] == 'yes' ) {
                                $meta_vars[] = $column_field;
                                continue;
                            }

                            if ( $column_field['input_type'] == 'taxonomy' ) {

                                // don't add "category"
                                // if ( $column_field['name'] == 'category' ) {
                                //     continue;
                                // }

                                $taxonomy_vars[] = $column_field;
                            } else {
                                $post_vars[] = $column_field;
                            }
                        }
                    }
                }
                continue;
            }

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
                // if ( $value['name'] == 'category' ) {
                //     continue;
                // }

                $taxonomy_vars[] = $value;
            } else {
                $post_vars[] = $value;
            }
        }

        return [ $post_vars, $taxonomy_vars, $meta_vars ];
    }

    /**
     * Checking recaptcha
     *
     * @param [type] $post_vars [description]
     *
     * @return void
     */
    public function on_edit_no_check_recaptcha( $post_vars ) {
        check_ajax_referer( 'wpuf_form_add' );
        // search if rs captcha is there
        if ( $this->search( $post_vars, 'input_type', 'really_simple_captcha' ) ) {
            $this->validate_rs_captcha();
        }
        $no_captcha = '';
        $invisible_captcha = '';
        $recaptcha_type = '';
        $check_recaptcha = $this->search( $post_vars, 'input_type', 'recaptcha' );

        if ( ! empty( $check_recaptcha ) ) {
            $recaptcha_type = $check_recaptcha[0]['recaptcha_type'];
        }
        // check recaptcha
        if ( $check_recaptcha ) {
            if ( isset( $_POST['g-recaptcha-response'] ) ) {
                if ( empty( $_POST['g-recaptcha-response'] ) && $check_recaptcha[0]['recaptcha_type'] !== 'invisible_recaptcha' ) {
                    wpuf()->ajax->send_error( __( 'Empty reCaptcha Field', 'wp-user-frontend' ) );
                }

                if ( $recaptcha_type == 'enable_no_captcha' ) {
                    $no_captcha        = 1;
                    $invisible_captcha = 0;
                } elseif ( $recaptcha_type == 'invisible_recaptcha' ) {
                    $invisible_captcha = 1;
                    $no_captcha        = 0;
                } else {
                    $invisible_captcha = 0;
                    $no_captcha        = 0;
                }
            }
            $this->validate_re_captcha( $no_captcha, $invisible_captcha );
        }
    }

    /**
     * Really simple captcha validation
     *
     * @return void
     */
    public function validate_rs_captcha() {
        $nonce = isset( $_REQUEST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf-login-nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
            return;
        }

        $rs_captcha_input = isset( $_POST['rs_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['rs_captcha'] ) ) : '';
        $rs_captcha_file  = isset( $_POST['rs_captcha_val'] ) ? sanitize_text_field( wp_unslash( $_POST['rs_captcha_val'] ) ) : '';

        if ( class_exists( 'ReallySimpleCaptcha' ) ) {
            $captcha_instance = new \ReallySimpleCaptcha();

            if ( ! $captcha_instance->check( $rs_captcha_file, $rs_captcha_input ) ) {
                wpuf()->ajax->send_error( __( 'Really Simple Captcha validation failed', 'wp-user-frontend' ) );
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
    public function validate_re_captcha( $no_captcha = '', $invisible = '' ) {
        // need to check if invisible reCaptcha need library or we can do it here.
        // ref: https://shareurcodes.com/blog/google%20invisible%20recaptcha%20integration%20with%20php
        check_ajax_referer( 'wpuf_form_add' );

        $site_key             = wpuf_get_option( 'recaptcha_public', 'wpuf_general' );
        $private_key          = wpuf_get_option( 'recaptcha_private', 'wpuf_general' );
        $remote_addr          = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $g_recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

        if ( $no_captcha == 1 && 0 == $invisible ) {
            if ( ! class_exists( 'WPUF_ReCaptcha' ) ) {
                require_once WPUF_ROOT . '/Lib/recaptchalib_noCaptcha.php';
            }

            $response  = null;
            $reCaptcha = new \WPUF_ReCaptcha( $private_key );

            $resp = $reCaptcha->verifyResponse(
                $remote_addr,
                $g_recaptcha_response
            );

            if ( ! $resp->success ) {
                $this->send_error( __( 'noCaptcha reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        } elseif ( $no_captcha == 0 && 0 == $invisible ) {
            $recap_challenge = isset( $_POST['recaptcha_challenge_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_challenge_field'] ) ) : '';
            $recap_response  = isset( $_POST['recaptcha_response_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response_field'] ) ) : '';

            $resp = recaptcha_check_answer( $private_key, $remote_addr, $recap_challenge, $recap_response );

            if ( ! $resp->is_valid ) {
                $this->send_error( __( 'reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        } elseif ( $no_captcha == 0 && 1 == $invisible ) {
            $response  = null;
            $recaptcha = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
            $object    = new Invisible_Recaptcha( $site_key, $private_key );

            $response = $object->verifyResponse( $recaptcha );

            if ( isset( $response['success'] ) and $response['success'] != true ) {
                $this->send_error( __( 'Invisible reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        }
    }

    /**
     * Adjust thumbnail image id if given
     *
     * @param $postarr
     *
     * @return array
     */
    private function adjust_thumbnail_id( $postarr ) {
        $wpuf_files = ! empty( $_POST['wpuf_files'] ) ? wp_unslash( $_POST['wpuf_files'] ) : [];

        if ( ! empty( $wpuf_files['featured_image'] ) ) {
            $attachment_id            = reset( $wpuf_files['featured_image'] );
            $postarr['_thumbnail_id'] = $attachment_id;
        }

        return $postarr;
    }

    public function update_post_meta( $meta_vars, $post_id ) {
        // check_ajax_referer( 'wpuf_form_add' );
        // prepare the meta vars
        [ $meta_key_value, $multi_repeated, $files ] = self::prepare_meta_fields( $meta_vars );
        // set featured image if there's any

        /**
         * Fires before updating post meta fields
         *
         * @param int $post_id
         * @param array $meta_key_value
         * @param array $multi_repeated
         * @param array $files
         */
        do_action( 'wpuf_before_updating_post_meta_fields', $post_id, $meta_key_value, $multi_repeated, $files );

        // @codingStandardsIgnoreStart
        $wpuf_files = isset( $_POST['wpuf_files'] ) ? $_POST['wpuf_files'] : [];

        if ( isset( $wpuf_files['featured_image'] ) ) {
            $attachment_id = $wpuf_files['featured_image'][0];

            wpuf_associate_attachment( $attachment_id, $post_id );
            set_post_thumbnail( $post_id, $attachment_id );

            $file_data = isset( $_POST['wpuf_files_data'][ $attachment_id ] ) ? $_POST['wpuf_files_data'][ $attachment_id ] : false;

            // @codingStandardsIgnoreEnd
            if ( $file_data ) {
                $args = [
                    'ID'           => $attachment_id,
                    'post_title'   => $file_data['title'],
                    'post_content' => $file_data['desc'],
                    'post_excerpt' => $file_data['caption'],
                ];
                wpuf_update_post( $args );

                update_post_meta( $attachment_id, '_wp_attachment_image_alt', $file_data['title'] );
            }
        }

        // save all custom fields
        foreach ( $meta_key_value as $meta_key => $meta_value ) {
            update_post_meta( $post_id, $meta_key, $meta_value );
        }

        // save any multicolumn repeatable fields
        foreach ( $multi_repeated as $repeat_key => $repeat_value ) {
            // first, delete any previous repeatable fields
            delete_post_meta( $post_id, $repeat_key );

            // now add them
            foreach ( $repeat_value as $repeat_field ) {
                add_post_meta( $post_id, $repeat_key, $repeat_field );
            }
        }

        // save any files attached
        foreach ( $files as $file_input ) {
            // delete any previous value
            delete_post_meta( $post_id, $file_input['name'] );

            $image_ids = '';

            if ( count( $file_input['value'] ) > 1 ) {
                $image_ids = $file_input['value'];
            }

            if ( count( $file_input['value'] ) === 1 ) {
                $image_ids = $file_input['value'][0];
            }

            if ( ! empty( $image_ids ) ) {
                add_post_meta( $post_id, $file_input['name'], $image_ids );
            }

            //to track how many files are being uploaded
            $file_numbers = 0;

            foreach ( $file_input['value'] as $attachment_id ) {

                //if file numbers are greated than allowed number, prevent it from being uploaded
                if ( $file_numbers >= $file_input['count'] ) {
                    wp_delete_attachment( $attachment_id );
                    continue;
                }

                wpuf_associate_attachment( $attachment_id, $post_id );
                //add_post_meta( $post_id, $file_input['name'], $attachment_id );

                // file title, caption, desc update

                // @codingStandardsIgnoreStart
                $file_data = isset( $_POST['wpuf_files_data'][ $attachment_id ] ) ? wp_unslash( $_POST['wpuf_files_data'][ $attachment_id ] ) : false;

                // @codingStandardsIgnoreEnd
                if ( $file_data ) {
                    $args = [
                        'ID'           => $attachment_id,
                        'post_title'   => $file_data['title'],
                        'post_content' => $file_data['desc'],
                        'post_excerpt' => $file_data['caption'],
                    ];
                    wpuf_update_post( $args );

                    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $file_data['title'] );
                }
                $file_numbers++;
            }
        }
    }

    /**
     * set custom taxonomy
     *
     * @param int   $post_id
     * @param array $taxonomy_vars
     */
    public function set_custom_taxonomy( $post_id, $taxonomy_vars ) {
        check_ajax_referer( 'wpuf_form_add' );
        // save any custom taxonomies
        $woo_attr = [];

        foreach ( $taxonomy_vars as $taxonomy ) {
            if ( isset( $_POST[ $taxonomy['name'] ] ) && is_array( $_POST[ $taxonomy['name'] ] ) ) {
                $taxonomy_name = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $taxonomy['name'] ] ) );
            }

            if ( isset( $_POST[ $taxonomy['name'] ] ) && ! is_array( $_POST[ $taxonomy['name'] ] ) ) {
                $taxonomy_name = sanitize_text_field( wp_unslash( $_POST[ $taxonomy['name'] ] ) );

                if ( 'text' === $taxonomy['type'] ) {
                    $terms = explode( ',', $taxonomy_name );
                    $terms = array_map(
                        function ( $term_name ) use ( $taxonomy ) {
                            $term = get_term_by( 'name', $term_name, $taxonomy['name'] );

                            if ( empty( $term_name ) ) {
                                return null;
                            }

                            if ( $term instanceof \WP_Term ) {
                                return $term->term_id;
                            }

                            $new_term = wp_insert_term( $term_name, $taxonomy['name'] );

                            return $new_term['term_id'];
                        }, $terms
                    );

                    $taxonomy_name = array_filter( $terms );
                }
            }

            // At this point $taxonomy_name should be a single id or array of ids
            if ( isset( $taxonomy_name ) && $taxonomy_name != 0 && $taxonomy_name != -1 ) {
                if ( is_object_in_taxonomy( $this->form_settings['post_type'], $taxonomy['name'] ) ) {
                    $tax = $taxonomy_name;
                    // if it's not an array, make it one
                    if ( ! is_array( $tax ) ) {
                        $tax = [ $tax ];
                    }

                    if ( $taxonomy['type'] == 'text' ) {
                        wp_set_object_terms( $post_id, $taxonomy_name, $taxonomy['name'] );

                        // woocommerce check
                        if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && ! empty( $taxonomy_name ) ) {
                            $woo_attr[ $taxonomy['name'] ] = $this->woo_attribute( $taxonomy );
                        }
                    } else {
                        if ( is_taxonomy_hierarchical( $taxonomy['name'] ) ) {
                            wp_set_post_terms( $post_id, $taxonomy_name, $taxonomy['name'] );

                            // woocommerce check
                            if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && ! empty( $taxonomy_name ) ) {
                                $woo_attr[ $taxonomy['name'] ] = $this->woo_attribute( $taxonomy );
                            }
                        } else {
                            if ( $tax ) {
                                $non_hierarchical = [];

                                foreach ( $tax as $value ) {
                                    $term = get_term_by( 'id', $value, $taxonomy['name'] );

                                    if ( $term && ! is_wp_error( $term ) ) {
                                        $non_hierarchical[] = $term->name;
                                    }
                                }

                                wp_set_post_terms( $post_id, $non_hierarchical, $taxonomy['name'] );

                                // woocommerce check
                                if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && ! empty( $_POST[ $taxonomy['name'] ] ) ) {
                                    $woo_attr[ $taxonomy['name'] ] = $this->woo_attribute( $taxonomy );
                                }
                            }
                        } // hierarchical
                    } // is text
                } // is object tax
            } // isset tax

            else {
                if ( isset( $taxonomy_name ) && 0 === absint( $taxonomy_name ) ) {
                    wp_set_post_terms( $post_id, $taxonomy_name, $taxonomy['name'] );
                }

                if ( ! isset( $taxonomy['woo_attr'] ) ) {
                    $this->set_default_taxonomy( $post_id );
                }
            }
        }

        // if a woocommerce attribute
        if ( $woo_attr ) {
            update_post_meta( $post_id, '_product_attributes', $woo_attr );
        }

        return $woo_attr;
    }

    /**
     * prepare meta fields
     *
     * @param array $meta_vars
     *
     * @return array
     */
    public static function prepare_meta_fields( $meta_vars ) {
        // loop through custom fields
        // skip files, put in a key => value paired array for later executation
        // process repeatable fields separately
        // if the input is array type, implode with separator in a field
        // /check_ajax_referer( 'wpuf_form_add' );
        $post_data = wp_unslash( $_POST ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        $files          = [];
        $meta_key_value = [];
        $multi_repeated = []; //multi repeated fields will in sotre duplicated meta key

        foreach ( $meta_vars as $key => $value ) {
            $wpuf_field = wpuf()->fields->get_field( $value['template'] );
            $posted_field_data = isset( $post_data[ $value['name'] ] ) ? $post_data[ $value['name'] ] : null;

            if ( isset( $posted_field_data ) && method_exists( $wpuf_field, 'sanitize_field_data' ) ) {
                $meta_key_value[ $value['name'] ] = $wpuf_field->sanitize_field_data( $posted_field_data, $value );
                continue;
            } elseif ( isset( $post_data[ $value['name'] ] ) && is_array( $post_data[ $value['name'] ] ) ) {
                $value_name = isset( $post_data[ $value['name'] ] ) ? array_map( 'sanitize_text_field', wp_unslash( $post_data[ $value['name'] ] ) ) : '';
            } else {
                $value_name = isset( $post_data[ $value['name'] ] ) ? sanitize_text_field( wp_unslash( $post_data[ $value['name'] ] ) ) : '';
            }

            if ( isset( $post_data['wpuf_files'][ $value['name'] ] ) ) {
                $wpuf_files = isset( $post_data['wpuf_files'] ) ? array_map( 'sanitize_text_field', wp_unslash( $post_data['wpuf_files'][ $value['name'] ] ) ) : [];
            } else {
                $wpuf_files = [];
            }

            if ( '_downloadable' === $value['name'] && 'on' === $value_name ) {
                $value_name = 'yes';
            }

            switch ( $value['input_type'] ) {

                // put files in a separate array, we'll process it later
                case 'file_upload':
                case 'image_upload':
                    $files[] = [
                        'name'  => $value['name'],
                        // 'value' => $wpuf_files[$value['name']],
                        'value' => isset( $wpuf_files ) ? $wpuf_files : [],
                        'count' => $value['count'],
                    ];
                    break;

                case 'repeat':
                    $repeater_value = wp_unslash( $_POST[ $value['name'] ] ); // WPCS: sanitization ok.

                    // if it is a multi column repeat field
                    if ( isset( $value['multiple'] ) && $value['multiple'] == 'true' ) {

                        // if there's any items in the array, process it
                        if ( $repeater_value ) {
                            $ref_arr = array();
                            $cols    = count( $value['columns'] );
                            $values  = array_values( $repeater_value );
                            $first   = array_shift( $values ); //first element
                            $rows    = count( $first );

                            // loop through columns
                            for ( $i = 0; $i < $rows; $i++ ) {

                                // loop through the rows and store in a temp array
                                $temp = array();
                                for ( $j = 0; $j < $cols; $j++ ) {
                                    $temp[] = $repeater_value[ $j ][ $i ];
                                }

                                // store all fields in a row with self::$separator separated
                                $ref_arr[] = implode( self::$separator, $temp );
                            }

                            // now, if we found anything in $ref_arr, store to $multi_repeated
                            if ( $ref_arr ) {
                                $multi_repeated[ $value['name'] ] = array_slice( $ref_arr, 0, $rows );
                            }
                        }
                    } else {
                        $meta_key_value[ $value['name'] ] = implode( self::$separator, $repeater_value );
                    }

                    break;

                case 'address':
                    if ( is_array( $value_name ) ) {
                        foreach ( $value_name as $address_field => $field_value ) {
                            $meta_key_value[ $value['name'] ][ $address_field ] = sanitize_text_field( $field_value );
                        }
                    }

                    break;

                case 'text':
                case 'email':
                case 'number':
                case 'date':
                    $meta_key_value[ $value['name'] ] = $value_name;

                    break;

                case 'textarea':
                    $allowed_tags = wp_kses_allowed_html( 'post' );
                    $meta_key_value[ $value['name'] ] = wp_kses( $value_name, $allowed_tags );

                    break;

                case 'map':
                    $data           = [];
                    $map_field_data = $value_name;

                    if ( ! empty( $map_field_data ) ) {
                        if ( stripos( $map_field_data, '||' ) !== false ) {
                            list( $data['address'], $data['lat'], $data['lng'] ) = explode( ' || ', $map_field_data );
                            $meta_key_value[ $value['name'] ]  = $data;
                        } else {
                            $meta_key_value[ $value['name'] ] = json_decode( $map_field_data, true );
                        }
                    }
                    break;

                case 'checkbox':
                    if ( is_array( $value_name ) && ! empty( $value_name ) ) {
                        $meta_key_value[ $value['name'] ] = implode( self::$separator, $value_name );
                    } else {
                        $meta_key_value[ $value['name'] ] = isset( $value_name[0] ) ? $value_name[0] : '';
                    }
                    break;

                default:
                    // if it's an array, implode with this->separator
                    if ( ! empty( $value_name ) && is_array( $value_name ) ) {
                        $acf_compatibility = wpuf_get_option( 'wpuf_compatibility_acf', 'wpuf_general', 'no' );

                        if ( $value['input_type'] == 'address' ) {
                            $meta_key_value[ $value['name'] ] = $value_name;
                        } elseif ( ! empty( $acf_compatibility ) && $acf_compatibility == 'yes' ) {
                            $meta_key_value[ $value['name'] ] = $value_name;
                        } else {
                            $meta_key_value[ $value['name'] ] = implode( self::$separator, $value_name );
                        }
                    } elseif ( ! empty( $value_name ) ) {
                        $meta_key_value[ $value['name'] ] = trim( $value_name );
                    } else {
                        $meta_key_value[ $value['name'] ] = trim( $value_name );
                    }

                    break;
            }
        } //end foreach
        return [ $meta_key_value, $multi_repeated, $files ];
    }

    /**
     * Search on multi dimensional array
     *
     * @since 4.0.0 moved from Render_Form.php to FieldableTrait.php
     *
     * @param array  $the_array
     * @param string $key   name of key
     * @param string $value the value to search
     *
     * @return array
     */
    public function search( $the_array, $key, $value ) {
        $results = [];

        if ( is_array( $the_array ) ) {
            if ( isset( $the_array[ $key ] ) && $the_array[ $key ] === $value ) {
                $results[] = $the_array;
            }

            foreach ( $the_array as $subarray ) {
                $results = array_merge( $results, $this->search( $subarray, $key, $value ) );
            }
        }

        return $results;
    }

    /**
     * Get WooCommerce attributres
     *
     * @since 4.0.6 moved from Render_Form.php to FieldableTrait.php
     *
     * @param array $taxonomy
     *
     * @return array
     */
    public function woo_attribute( $taxonomy ) {
        check_ajax_referer( 'wpuf_form_add' );
        $taxonomy_name = isset( $_POST[ $taxonomy['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $taxonomy['name'] ] ) ) : '';

        return [
            'name'         => ! empty( $taxonomy['name'] ) ? $taxonomy['name'] : '',
            'value'        => $taxonomy_name,
            'is_visible'   => ! empty( $taxonomy['woo_attr_vis'] ) && ( 'yes' === $taxonomy['woo_attr_vis'] ) ? 1 : 0,
            'is_variation' => 0,
            'is_taxonomy'  => 1,
        ];
    }
}
