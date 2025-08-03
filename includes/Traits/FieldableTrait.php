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
            if ( 'column_field' === $value['input_type'] ) {
                $inner_fields = $value['inner_fields'];

                foreach ( $inner_fields as $column_key => $column_fields ) {
                    if ( ! empty( $column_fields ) ) {
                        // ignore section break and HTML input type
                        foreach ( $column_fields as $column_field_key => $column_field ) {
                            if ( in_array( $column_field['input_type'], $ignore_lists ) ) {
                                continue;
                            }

                            //separate the post and custom fields
                            if ( isset( $column_field['is_meta'] ) && 'yes' === $column_field['is_meta'] ) {
                                $meta_vars[] = $column_field;
                                continue;
                            }

                            if ( 'taxonomy' === $column_field['input_type'] ) {

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
            if ( isset( $value['is_meta'] ) && 'yes' === $value['is_meta'] ) {
                $meta_vars[] = $value;
                continue;
            }

            if ( 'taxonomy' === $value['input_type'] ) {
                // Handle product categories and other WooCommerce taxonomies
                if ( 'product_cat' === $value['name'] || 'product_category' === $value['name'] ) {
                    $value['name'] = 'product_cat'; // Normalize to WooCommerce's taxonomy name
                    $value['is_woocommerce'] = true;
                }
                
                // Handle product tags
                if ( 'product_tag' === $value['name'] ) {
                    $value['is_woocommerce'] = true;
                }

                // Handle shipping class
                if ( 'product_shipping_class' === $value['name'] ) {
                    $value['is_woocommerce'] = true;
                    $value['hierarchical'] = true;
                    if ( empty( $value['type'] ) ) {
                        $value['type'] = 'select';
                    }
                }

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

                if ( 'enable_no_captcha' === $recaptcha_type ) {
                    $no_captcha        = 1;
                    $invisible_captcha = 0;
                } elseif ( 'invisible_recaptcha' === $recaptcha_type ) {
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

        if ( 1 === $no_captcha && 0 === $invisible ) {
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
        } elseif ( 0 === $no_captcha && 0 === $invisible ) {
            $recap_challenge = isset( $_POST['recaptcha_challenge_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_challenge_field'] ) ) : '';
            $recap_response  = isset( $_POST['recaptcha_response_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response_field'] ) ) : '';

            $resp = recaptcha_check_answer( $private_key, $remote_addr, $recap_challenge, $recap_response );

            if ( ! $resp->is_valid ) {
                $this->send_error( __( 'reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        } elseif ( 0 === $no_captcha && 1 === $invisible ) {
            $response  = null;
            $recaptcha = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
            $object    = new Invisible_Recaptcha( $site_key, $private_key );

            $response = $object->verifyResponse( $recaptcha );

            if ( isset( $response['success'] ) && true !== $response['success'] ) {
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
        
        $woo_attr = [];
        $is_product = 'product' === get_post_type( $post_id );
        
        // Store the form field labels for WooCommerce taxonomies
        if ( $is_product ) {
            $taxonomy_labels = [];
            foreach ( $taxonomy_vars as $taxonomy ) {
                if ( ! empty( $taxonomy['label'] ) && ! empty( $taxonomy['name'] ) ) {
                    $taxonomy_labels[ $taxonomy['name'] ] = $taxonomy['label'];
                }
            }
            
            // Store labels in post meta for use in filter
            if ( ! empty( $taxonomy_labels ) ) {
                update_post_meta( $post_id, '_wpuf_taxonomy_labels', $taxonomy_labels );
            }
        }
        
        foreach ( $taxonomy_vars as $taxonomy ) {
            $taxonomy_name = $taxonomy['name'];
            $posted_terms = isset( $_POST[$taxonomy_name] ) ? wp_unslash( $_POST[$taxonomy_name] ) : null;

            // Use default terms if none posted
            if ( empty( $posted_terms ) && ! empty( $taxonomy['default'] ) ) {
                $posted_terms = $taxonomy['default'];
            }

            if ( empty( $posted_terms ) ) {
                continue;
            }
            
            // Handle WooCommerce taxonomies if product post type
            if ( 'product' === get_post_type( $post_id ) && class_exists( 'WooCommerce' ) ) {
                $this->handle_woocommerce_taxonomy( $post_id, $taxonomy_name, $posted_terms, $taxonomy, $woo_attr );
                continue;
            }


            // Handle regular taxonomies
            $this->handle_regular_taxonomy( $post_id, $taxonomy_name, $posted_terms, $taxonomy );
        }

        // Update WooCommerce attributes if any
        if ( ! empty( $woo_attr ) ) {
            update_post_meta( $post_id, '_product_attributes', $woo_attr );
        }
        
        // Only do WooCommerce-specific processing if this is actually a product
        if ( $is_product && function_exists( 'wc_delete_product_transients' ) ) {
            // Ensure complete synchronization with WooCommerce
            $this->sync_woocommerce_product_data( $post_id );
            
            // Force WooCommerce to recognize the changes
            wc_delete_product_transients( $post_id );
            clean_post_cache( $post_id );
            
            // Verify the final product type was saved correctly
            if ( function_exists( 'wc_get_product' ) ) {
                // Clear the product cache first
                wp_cache_delete( 'product-' . $post_id, 'products' );
                
                // Check what product type is actually saved in the database
                $saved_types = wp_get_object_terms( $post_id, 'product_type', array( 'fields' => 'slugs' ) );
                error_log( 'WPUF Final Product Type Terms in DB: ' . print_r( $saved_types, true ) );
                
                // Also check visibility terms
                $saved_visibility = wp_get_object_terms( $post_id, 'product_visibility', array( 'fields' => 'slugs' ) );
                error_log( 'WPUF Final Product Visibility Terms in DB: ' . print_r( $saved_visibility, true ) );
                
                // Check if we have an intended product type stored
                $intended_type = get_post_meta( $post_id, '_wpuf_intended_product_type', true );
                if ( $intended_type ) {
                    // If the saved type doesn't match the intended type, fix it
                    if ( empty( $saved_types ) || ! in_array( $intended_type, $saved_types ) ) {
                        wp_set_object_terms( $post_id, $intended_type, 'product_type', false );
                        $this->sync_product_type_data( $post_id, $intended_type );
                        error_log( 'WPUF Product Type - Restored intended type: ' . $intended_type );
                        
                        // Clear the caches again
                        wp_cache_delete( 'product-' . $post_id, 'products' );
                        clean_object_term_cache( $post_id, 'product_type' );
                    }
                    // Clean up the meta
                    delete_post_meta( $post_id, '_wpuf_intended_product_type' );
                } elseif ( empty( $saved_types ) ) {
                    // If no product type is set and no intended type, default to simple
                    wp_set_object_terms( $post_id, 'simple', 'product_type', false );
                    $this->sync_product_type_data( $post_id, 'simple' );
                    error_log( 'WPUF Product Type - No type found, defaulted to simple' );
                }
            }
        }

        return $woo_attr;
    }

    /**
     * Handle WooCommerce taxonomy terms
     *
     * @param int    $post_id
     * @param string $taxonomy_name
     * @param mixed  $posted_terms
     * @param array  $taxonomy
     * @param array  &$woo_attr
     */
    protected function handle_woocommerce_taxonomy( $post_id, $taxonomy_name, $posted_terms, $taxonomy, &$woo_attr ) {
        // Map of core WooCommerce taxonomies and their handling methods
        $core_woo_taxonomies = [
            'product_type' => 'handle_product_type',
            'product_cat' => 'handle_hierarchical_terms',
            'product_tag' => 'handle_non_hierarchical_terms',
            'product_shipping_class' => 'handle_shipping_class',
            'product_visibility' => 'handle_product_visibility',
        ];

        // Check if it's a product attribute (starts with pa_)
        if ( strpos( $taxonomy_name, 'pa_' ) === 0 ) {
            $this->handle_product_attribute( $post_id, $taxonomy_name, $posted_terms, $taxonomy, $woo_attr );
            return;
        }

        // Handle known core WooCommerce taxonomies
        if ( isset( $core_woo_taxonomies[$taxonomy_name] ) ) {
            $method = $core_woo_taxonomies[$taxonomy_name];
            $this->$method( $post_id, $taxonomy_name, $posted_terms );
            
            // For system taxonomies, always add to attributes if visibility is enabled
            // This ensures product_type, shipping_class, visibility show in Additional Information
            if ( ! in_array( $taxonomy_name, ['product_cat', 'product_tag'] ) ) {
                // Check if this should be visible in Additional Information
                $should_show = false;
                
                // Always show core WooCommerce taxonomies if they have the woo_attr flag
                if ( isset( $taxonomy['woo_attr'] ) && wpuf_is_checkbox_or_toggle_on( $taxonomy['woo_attr'] ) ) {
                    $should_show = true;
                } elseif ( isset( $taxonomy['woo_attr_vis'] ) && wpuf_is_checkbox_or_toggle_on( $taxonomy['woo_attr_vis'] ) ) {
                    $should_show = true;
                }
                
                if ( $should_show ) {
                    $woo_attr[$taxonomy_name] = $this->build_woo_attribute( $taxonomy_name, $taxonomy );
                }
            }
            return;
        }

        // For any other taxonomy on a product (like custom brands, etc.)
        // Treat as potential WooCommerce attribute  
        if ( taxonomy_exists( $taxonomy_name ) ) {
            $is_hierarchical = is_taxonomy_hierarchical( $taxonomy_name );
            
            if ( $is_hierarchical ) {
                $this->handle_hierarchical_terms( $post_id, $taxonomy_name, $posted_terms );
            } else {
                $this->handle_non_hierarchical_terms( $post_id, $taxonomy_name, $posted_terms );
            }
            
            // Add to WooCommerce attributes if enabled
            if ( isset( $taxonomy['woo_attr'] ) && wpuf_is_checkbox_or_toggle_on( $taxonomy['woo_attr'] ) ) {
                $woo_attr[$taxonomy_name] = $this->build_woo_attribute( $taxonomy_name, $taxonomy );
            }
        }
    }

    /**
     * Handle product type taxonomy
     */
    protected function handle_product_type( $post_id, $taxonomy_name, $posted_terms ) {
        // Log incoming data
        error_log( 'WPUF Product Type - Input: ' . print_r( $posted_terms, true ) );
        
        // First, completely clear ALL existing product types
        $existing_types = wp_get_object_terms( $post_id, 'product_type', array( 'fields' => 'ids' ) );
        if ( ! empty( $existing_types ) ) {
            wp_remove_object_terms( $post_id, $existing_types, 'product_type' );
        }
        
        if ( empty( $posted_terms ) ) {
            wp_set_object_terms( $post_id, 'simple', 'product_type', false );
            $this->sync_product_type_data( $post_id, 'simple' );
            return;
        }

        $product_type = is_array( $posted_terms ) ? reset( $posted_terms ) : $posted_terms;
        
        // Map common term IDs to slugs
        // These are the standard WooCommerce product type term IDs
        $type_map = array(
            '6' => 'simple',
            '7' => 'grouped',
            '8' => 'variable',
            '9' => 'external'
        );
        
        // Check if it's a known term ID
        if ( isset( $type_map[ strval( $product_type ) ] ) ) {
            $product_type = $type_map[ strval( $product_type ) ];
            error_log( 'WPUF Product Type - Mapped from ID to: ' . $product_type );
        } elseif ( is_numeric( $product_type ) ) {
            // Convert any other term ID to slug
            $term = get_term( $product_type, 'product_type' );
            if ( $term && ! is_wp_error( $term ) ) {
                $product_type = $term->slug;
                error_log( 'WPUF Product Type - Got slug from term: ' . $product_type );
            }
        } else {
            // Handle term name (like "Variable" or "variable")
            // First try to get by slug
            $term = get_term_by( 'slug', strtolower( $product_type ), 'product_type' );
            if ( ! $term ) {
                // Try by name
                $term = get_term_by( 'name', $product_type, 'product_type' );
            }
            
            if ( $term && ! is_wp_error( $term ) ) {
                $product_type = $term->slug;
            } else {
                // Sanitize to create slug
                $product_type = strtolower( sanitize_title( $product_type ) );
            }
        }
        
        error_log( 'WPUF Product Type - Final slug: ' . $product_type );
        
        // Remove all existing product types first
        wp_remove_object_terms( $post_id, array( 'simple', 'variable', 'grouped', 'external' ), 'product_type' );
        
        // Set the product type using term ID for better reliability
        $term = get_term_by( 'slug', $product_type, 'product_type' );
        if ( $term && ! is_wp_error( $term ) ) {
            $result = wp_set_object_terms( $post_id, intval( $term->term_id ), 'product_type', false );
            error_log( 'WPUF Product Type - Set using term ID ' . $term->term_id . ', result: ' . print_r( $result, true ) );
        } else {
            // Fallback to slug
            $result = wp_set_object_terms( $post_id, $product_type, 'product_type', false );
            error_log( 'WPUF Product Type - Set using slug, result: ' . print_r( $result, true ) );
        }
        
        // Sync product type specific data with WooCommerce
        $this->sync_product_type_data( $post_id, $product_type );
        
        // Clear caches to ensure WooCommerce recognizes the change
        if ( function_exists( 'wc_get_product' ) ) {
            wp_cache_delete( 'product-' . $post_id, 'products' );
            wp_cache_delete( $post_id, 'post_meta' );
            clean_object_term_cache( $post_id, 'product_type' );
        }
        
        // Clear caches again
        if ( function_exists( 'wc_delete_product_transients' ) ) {
            wc_delete_product_transients( $post_id );
        }
        
        // Final verification
        $saved_types = wp_get_object_terms( $post_id, 'product_type', array( 'fields' => 'slugs' ) );
        error_log( 'WPUF Product Type - Final saved types: ' . print_r( $saved_types, true ) );
        
        // Store the intended product type in meta to preserve it
        update_post_meta( $post_id, '_wpuf_intended_product_type', $product_type );
    }

    /**
     * Handle hierarchical terms (like product categories)
     */
    protected function handle_hierarchical_terms( $post_id, $taxonomy_name, $posted_terms ) {
        // Clear existing terms first
        wp_set_object_terms( $post_id, [], $taxonomy_name );
        
        if ( empty( $posted_terms ) ) {
            return;
        }
        
        $term_ids = $this->process_terms_to_ids( $posted_terms, $taxonomy_name );
        
        if ( ! empty( $term_ids ) ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy_name, false );
        }
    }

    /**
     * Handle non-hierarchical terms (like product tags)
     */
    protected function handle_non_hierarchical_terms( $post_id, $taxonomy_name, $posted_terms ) {
        if ( empty( $posted_terms ) ) {
            wp_set_object_terms( $post_id, [], $taxonomy_name );
            return;
        }

        $terms_array = is_array( $posted_terms ) ? $posted_terms : [ $posted_terms ];
        $term_names = [];

        foreach ( $terms_array as $term ) {
            if ( is_numeric( $term ) ) {
                $term_obj = get_term( $term, $taxonomy_name );
                if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                    $term_names[] = $term_obj->name;
                }
            } else {
                $term_names[] = $term;
            }
        }

        if ( ! empty( $term_names ) ) {
            wp_set_post_terms( $post_id, $term_names, $taxonomy_name, false );
        }
    }

    /**
     * Handle shipping class
     */
    protected function handle_shipping_class( $post_id, $taxonomy_name, $posted_terms ) {
        // Clear existing shipping classes
        wp_set_object_terms( $post_id, [], 'product_shipping_class' );
        
        if ( empty( $posted_terms ) || '0' === $posted_terms || '-1' === $posted_terms ) {
            // No shipping class selected
            return;
        }
        
        // Handle both IDs and slugs
        if ( is_numeric( $posted_terms ) ) {
            $term = get_term( $posted_terms, 'product_shipping_class' );
            if ( $term && ! is_wp_error( $term ) ) {
                wp_set_object_terms( $post_id, $term->slug, 'product_shipping_class', false );
            }
        } else {
            // It's already a slug or name
            wp_set_object_terms( $post_id, $posted_terms, 'product_shipping_class', false );
        }
    }

    /**
     * Ensure complete synchronization with WooCommerce product data
     *
     * @param int $post_id
     */
    protected function sync_woocommerce_product_data( $post_id ) {
        // Get current product type
        $product_types = wp_get_object_terms( $post_id, 'product_type', array( 'fields' => 'slugs' ) );
        $product_type = ! empty( $product_types ) ? $product_types[0] : 'simple';
        
        // Sync product type data
        $this->sync_product_type_data( $post_id, $product_type );
        
        // Get visibility terms
        $visibility_terms = wp_get_object_terms( $post_id, 'product_visibility', array( 'fields' => 'slugs' ) );
        
        // Sync visibility with meta
        $this->sync_visibility_meta( $post_id, $visibility_terms );
        
        // Ensure product catalog visibility is set
        if ( ! metadata_exists( 'post', $post_id, '_visibility' ) ) {
            update_post_meta( $post_id, '_visibility', 'visible' );
        }
        
        // Ensure stock status is set
        if ( ! metadata_exists( 'post', $post_id, '_stock_status' ) ) {
            update_post_meta( $post_id, '_stock_status', 'instock' );
        }
        
        // Ensure manage stock is set
        if ( ! metadata_exists( 'post', $post_id, '_manage_stock' ) ) {
            update_post_meta( $post_id, '_manage_stock', 'no' );
        }
        
        // Ensure SKU exists (even if empty)
        if ( ! metadata_exists( 'post', $post_id, '_sku' ) ) {
            update_post_meta( $post_id, '_sku', '' );
        }
        
        // Set product status if not set
        if ( ! metadata_exists( 'post', $post_id, '_status' ) ) {
            update_post_meta( $post_id, '_status', 'publish' );
        }
    }
    
    /**
     * Sync visibility meta with taxonomy terms
     *
     * @param int $post_id
     * @param array $visibility_terms
     */
    protected function sync_visibility_meta( $post_id, $visibility_terms ) {
        // Handle stock status
        if ( in_array( 'outofstock', $visibility_terms ) ) {
            update_post_meta( $post_id, '_stock_status', 'outofstock' );
        }
        
        // Handle catalog visibility
        if ( in_array( 'exclude-from-catalog', $visibility_terms ) && in_array( 'exclude-from-search', $visibility_terms ) ) {
            update_post_meta( $post_id, '_visibility', 'hidden' );
        } elseif ( in_array( 'exclude-from-catalog', $visibility_terms ) ) {
            update_post_meta( $post_id, '_visibility', 'search' );
        } elseif ( in_array( 'exclude-from-search', $visibility_terms ) ) {
            update_post_meta( $post_id, '_visibility', 'catalog' );
        } elseif ( ! in_array( 'featured', $visibility_terms ) && ! in_array( 'outofstock', $visibility_terms ) ) {
            // Only set to visible if no special visibility terms
            update_post_meta( $post_id, '_visibility', 'visible' );
        }
    }
    
    /**
     * Sync product type specific meta data with WooCommerce
     *
     * @param int $post_id
     * @param string $product_type
     */
    protected function sync_product_type_data( $post_id, $product_type ) {
        // Store the product type to prevent WooCommerce from changing it
        update_post_meta( $post_id, '_wpuf_product_type', $product_type );
        
        // Set product type specific meta based on WooCommerce standards
        switch ( $product_type ) {
            case 'external':
                // External/Affiliate products
                update_post_meta( $post_id, '_virtual', 'yes' );
                update_post_meta( $post_id, '_downloadable', 'no' );
                delete_post_meta( $post_id, '_manage_stock' );
                delete_post_meta( $post_id, '_stock' );
                delete_post_meta( $post_id, '_backorders' );
                // External products need these fields
                if ( ! metadata_exists( 'post', $post_id, '_product_url' ) ) {
                    update_post_meta( $post_id, '_product_url', '' );
                }
                if ( ! metadata_exists( 'post', $post_id, '_button_text' ) ) {
                    update_post_meta( $post_id, '_button_text', '' );
                }
                break;
                
            case 'grouped':
                // Grouped products
                update_post_meta( $post_id, '_virtual', 'no' );
                update_post_meta( $post_id, '_downloadable', 'no' );
                delete_post_meta( $post_id, '_manage_stock' );
                delete_post_meta( $post_id, '_stock' );
                delete_post_meta( $post_id, '_backorders' );
                // Grouped products need children
                if ( ! metadata_exists( 'post', $post_id, '_children' ) ) {
                    update_post_meta( $post_id, '_children', array() );
                }
                break;
                
            case 'variable':
                // Variable products - MUST have specific meta to prevent WooCommerce from resetting
                update_post_meta( $post_id, '_virtual', 'no' );
                update_post_meta( $post_id, '_downloadable', 'no' );
                delete_post_meta( $post_id, '_manage_stock' );
                delete_post_meta( $post_id, '_stock' );
                delete_post_meta( $post_id, '_backorders' );
                
                // Critical: Variable products need these to be recognized
                if ( ! metadata_exists( 'post', $post_id, '_product_attributes' ) ) {
                    // Set empty attributes array to prevent reset
                    update_post_meta( $post_id, '_product_attributes', array() );
                }
                
                // Set default variation data
                if ( ! metadata_exists( 'post', $post_id, '_default_attributes' ) ) {
                    update_post_meta( $post_id, '_default_attributes', array() );
                }
                
                // Variable products need min/max prices
                if ( ! metadata_exists( 'post', $post_id, '_min_variation_price' ) ) {
                    update_post_meta( $post_id, '_min_variation_price', '' );
                    update_post_meta( $post_id, '_max_variation_price', '' );
                    update_post_meta( $post_id, '_min_price_variation_id', '' );
                    update_post_meta( $post_id, '_max_price_variation_id', '' );
                    update_post_meta( $post_id, '_min_variation_regular_price', '' );
                    update_post_meta( $post_id, '_max_variation_regular_price', '' );
                    update_post_meta( $post_id, '_min_regular_price_variation_id', '' );
                    update_post_meta( $post_id, '_max_regular_price_variation_id', '' );
                    update_post_meta( $post_id, '_min_variation_sale_price', '' );
                    update_post_meta( $post_id, '_max_variation_sale_price', '' );
                    update_post_meta( $post_id, '_min_sale_price_variation_id', '' );
                    update_post_meta( $post_id, '_max_sale_price_variation_id', '' );
                }
                break;
                
            case 'simple':
            default:
                // Simple products - don't change existing meta unless necessary
                if ( ! metadata_exists( 'post', $post_id, '_virtual' ) ) {
                    update_post_meta( $post_id, '_virtual', 'no' );
                }
                if ( ! metadata_exists( 'post', $post_id, '_downloadable' ) ) {
                    update_post_meta( $post_id, '_downloadable', 'no' );
                }
                break;
        }
        
        // Ensure basic product meta exists for all types
        if ( ! metadata_exists( 'post', $post_id, '_regular_price' ) ) {
            update_post_meta( $post_id, '_regular_price', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_sale_price' ) ) {
            update_post_meta( $post_id, '_sale_price', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_price' ) ) {
            update_post_meta( $post_id, '_price', '' );
        }
        
        // Ensure tax and shipping data exists
        if ( ! metadata_exists( 'post', $post_id, '_tax_status' ) ) {
            update_post_meta( $post_id, '_tax_status', 'taxable' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_tax_class' ) ) {
            update_post_meta( $post_id, '_tax_class', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_weight' ) ) {
            update_post_meta( $post_id, '_weight', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_length' ) ) {
            update_post_meta( $post_id, '_length', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_width' ) ) {
            update_post_meta( $post_id, '_width', '' );
        }
        if ( ! metadata_exists( 'post', $post_id, '_height' ) ) {
            update_post_meta( $post_id, '_height', '' );
        }
    }
    
    /**
     * Handle product visibility
     */
    protected function handle_product_visibility( $post_id, $taxonomy_name, $posted_terms ) {
        error_log( 'WPUF Product Visibility - Input: ' . print_r( $posted_terms, true ) );
        
        // Clear ALL existing visibility terms first
        $existing_terms = wp_get_object_terms( $post_id, 'product_visibility', array( 'fields' => 'ids' ) );
        if ( ! empty( $existing_terms ) ) {
            wp_remove_object_terms( $post_id, $existing_terms, 'product_visibility' );
        }
        
        if ( empty( $posted_terms ) ) {
            return;
        }
        
        // Build a mapping of all visibility term IDs to slugs
        $visibility_map = array();
        $all_visibility_terms = get_terms( array(
            'taxonomy' => 'product_visibility',
            'hide_empty' => false
        ) );
        
        foreach ( $all_visibility_terms as $term ) {
            $visibility_map[ strval( $term->term_id ) ] = $term->slug;
        }
        
        error_log( 'WPUF Product Visibility - Term map: ' . print_r( $visibility_map, true ) );
        
        // Handle both single and multiple visibility terms
        $terms_to_set = array();
        $visibility_terms = is_array( $posted_terms ) ? $posted_terms : array( $posted_terms );
        
        foreach ( $visibility_terms as $term ) {
            $slug = null;
            
            // Check if it's a known term ID
            if ( isset( $visibility_map[ strval( $term ) ] ) ) {
                $slug = $visibility_map[ strval( $term ) ];
                error_log( 'WPUF Product Visibility - Mapped ID ' . $term . ' to slug: ' . $slug );
            } elseif ( is_numeric( $term ) ) {
                // Try to get term by ID
                $term_obj = get_term( $term, 'product_visibility' );
                if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                    $slug = $term_obj->slug;
                }
            } else {
                // It's already a slug or name
                $term_obj = get_term_by( 'slug', $term, 'product_visibility' );
                if ( ! $term_obj ) {
                    $term_obj = get_term_by( 'name', $term, 'product_visibility' );
                }
                if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                    $slug = $term_obj->slug;
                } else {
                    $slug = sanitize_title( $term );
                }
            }
            
            if ( $slug ) {
                $terms_to_set[] = $slug;
            }
        }
        
        error_log( 'WPUF Product Visibility - Setting terms: ' . print_r( $terms_to_set, true ) );
        
        if ( ! empty( $terms_to_set ) ) {
            $result = wp_set_object_terms( $post_id, $terms_to_set, 'product_visibility', false );
            error_log( 'WPUF Product Visibility - Set result: ' . print_r( $result, true ) );
            
            // Update WooCommerce meta values based on visibility terms
            // Handle stock status
            if ( in_array( 'outofstock', $terms_to_set ) ) {
                update_post_meta( $post_id, '_stock_status', 'outofstock' );
                error_log( 'WPUF Product Visibility - Set stock status to outofstock' );
            } else {
                // Don't change stock status if not explicitly set to outofstock
                // This preserves the existing stock status
            }
            
            // Handle catalog visibility
            // WooCommerce uses _visibility meta with values: visible, catalog, search, hidden
            $current_visibility = get_post_meta( $post_id, '_visibility', true );
            if ( ! $current_visibility ) {
                $current_visibility = 'visible'; // Default
            }
            
            if ( in_array( 'exclude-from-catalog', $terms_to_set ) && in_array( 'exclude-from-search', $terms_to_set ) ) {
                update_post_meta( $post_id, '_visibility', 'hidden' );
                error_log( 'WPUF Product Visibility - Set visibility to hidden' );
            } elseif ( in_array( 'exclude-from-catalog', $terms_to_set ) ) {
                update_post_meta( $post_id, '_visibility', 'search' );
                error_log( 'WPUF Product Visibility - Set visibility to search' );
            } elseif ( in_array( 'exclude-from-search', $terms_to_set ) ) {
                update_post_meta( $post_id, '_visibility', 'catalog' );
                error_log( 'WPUF Product Visibility - Set visibility to catalog' );
            } else {
                // If no exclusion terms, set to visible
                if ( ! in_array( 'featured', $terms_to_set ) && ! in_array( 'outofstock', $terms_to_set ) && 
                     ! preg_grep( '/^rated-/', $terms_to_set ) ) {
                    // Only set to visible if there are no other special visibility terms
                    update_post_meta( $post_id, '_visibility', 'visible' );
                    error_log( 'WPUF Product Visibility - Set visibility to visible' );
                }
            }
        }
        
        // Verify what was saved
        $saved_terms = wp_get_object_terms( $post_id, 'product_visibility', array( 'fields' => 'slugs' ) );
        error_log( 'WPUF Product Visibility - Final saved terms: ' . print_r( $saved_terms, true ) );
    }

    /**
     * Handle product attributes
     */
    protected function handle_product_attribute( $post_id, $taxonomy_name, $posted_terms, $taxonomy, &$woo_attr ) {
        // Clear existing terms first
        wp_set_object_terms( $post_id, [], $taxonomy_name );
        
        if ( ! empty( $posted_terms ) ) {
            // Process terms to IDs for consistent handling
            $term_ids = $this->process_terms_to_ids( $posted_terms, $taxonomy_name );
            
            if ( ! empty( $term_ids ) ) {
                wp_set_object_terms( $post_id, $term_ids, $taxonomy_name, false );
            }
        }
        
        // Always add product attributes to WooCommerce attributes array
        // The visibility checkbox controls if it shows on product page
        $woo_attr[$taxonomy_name] = $this->build_woo_attribute( $taxonomy_name, $taxonomy );
    }

    /**
     * Handle regular (non-WooCommerce) taxonomies
     */
    protected function handle_regular_taxonomy( $post_id, $taxonomy_name, $posted_terms, $taxonomy ) {
        $is_hierarchical = is_taxonomy_hierarchical( $taxonomy_name );
        
        if ( isset( $taxonomy['type'] ) && 'text' === $taxonomy['type'] ) {
            // Handle text input (comma-separated values)
            $this->handle_text_taxonomy( $post_id, $taxonomy_name, $posted_terms );
        } else {
            // Handle select/checkbox/radio inputs
            $term_ids = $this->process_terms_to_ids( $posted_terms, $taxonomy_name );
            
            if ( ! empty( $term_ids ) ) {
                if ( $is_hierarchical ) {
                    wp_set_object_terms( $post_id, $term_ids, $taxonomy_name );
                } else {
                    // Convert IDs to names for non-hierarchical taxonomies
                    $term_names = [];
                    foreach ( $term_ids as $term_id ) {
                        $term = get_term( $term_id, $taxonomy_name );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $term_names[] = $term->name;
                        }
                    }
                    wp_set_post_terms( $post_id, $term_names, $taxonomy_name );
                }
            }
        }
    }

    /**
     * Handle text-based taxonomy input
     */
    protected function handle_text_taxonomy( $post_id, $taxonomy_name, $posted_terms ) {
        $terms = is_array( $posted_terms ) ? $posted_terms : explode( ',', $posted_terms );
        $terms = array_map( 'trim', $terms );
        $terms = array_filter( $terms );
        
        $term_ids = [];
        foreach ( $terms as $term ) {
            $existing = term_exists( $term, $taxonomy_name );
            if ( ! $existing ) {
                $new_term = wp_insert_term( $term, $taxonomy_name );
                if ( ! is_wp_error( $new_term ) ) {
                    $term_ids[] = $new_term['term_id'];
                }
            } else {
                $term_ids[] = is_array( $existing ) ? $existing['term_id'] : $existing;
            }
        }
        
        if ( ! empty( $term_ids ) ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy_name );
        }
    }

    /**
     * Process terms to IDs
     */
    protected function process_terms_to_ids( $posted_terms, $taxonomy_name ) {
        $terms = is_array( $posted_terms ) ? $posted_terms : [ $posted_terms ];
        $term_ids = [];
        
        foreach ( $terms as $term ) {
            if ( empty( $term ) ) {
                continue;
            }
            
            if ( is_numeric( $term ) ) {
                $term_obj = get_term( $term, $taxonomy_name );
                if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                    $term_ids[] = (int) $term;
                }
            } else {
                $existing = term_exists( $term, $taxonomy_name );
                if ( $existing ) {
                    $term_ids[] = is_array( $existing ) ? $existing['term_id'] : $existing;
                } else {
                    // Create new term if it doesn't exist
                    $new_term = wp_insert_term( $term, $taxonomy_name );
                    if ( ! is_wp_error( $new_term ) ) {
                        $term_ids[] = $new_term['term_id'];
                    }
                }
            }
        }
        
        return array_unique( $term_ids );
    }

    /**
     * Build WooCommerce attribute array
     */
    protected function build_woo_attribute( $taxonomy_name, $taxonomy ) {
        // Only exclude product_cat and product_tag from attributes
        // Allow product_type, shipping_class, visibility, brand to show if enabled
        $excluded = [ 'product_cat', 'product_tag', 'product_brand' ];
        if ( in_array( $taxonomy_name, $excluded ) ) {
            return [];
        }
        
        // PRIORITY 1: Always use the form field label if available
        $label = '';
        if ( ! empty( $taxonomy['label'] ) ) {
            $label = $taxonomy['label'];
        } else {
            // If no form label, just use the taxonomy name as-is to match the form
            // This ensures consistency between form and display
            $label = $taxonomy_name;
        }
        
        return [
            'name'         => $taxonomy_name,
            'value'        => '', // WooCommerce will populate this from the actual terms
            'is_visible'   => isset( $taxonomy['woo_attr_vis'] ) && wpuf_is_checkbox_or_toggle_on( $taxonomy['woo_attr_vis'] ) ? 1 : 0,
            'is_variation' => 0,
            'is_taxonomy'  => 1,
            'position'     => 0,
            'label'        => $label,
        ];
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
                    if ( isset( $value['multiple'] ) && 'true' === $value['multiple'] ) {

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

                        if ( 'address' === $value['input_type'] ) {
                            $meta_key_value[ $value['name'] ] = $value_name;
                        } elseif ( ! empty( $acf_compatibility ) && 'yes' === $acf_compatibility ) {
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
        
        // Only exclude main categories and tags from additional information display
        $excluded_taxonomies = [ 'product_cat', 'product_tag', 'product_brand' ];
        if ( in_array( $taxonomy['name'], $excluded_taxonomies ) ) {
            return [];
        }
        
        // Special handling for product_brand - exclude if not specifically marked as visible
        if ( 'product_brand' === $taxonomy['name'] && empty( $taxonomy['woo_attr_vis'] ) ) {
            return [];
        }
        
        $terms = $taxonomy['terms'] ?? [];
        
        if ( ! is_array( $terms ) ) {
            $terms = [ $terms ];
        }
        
        // Convert term IDs to term values for WooCommerce
        $term_values = [];
        foreach ( $terms as $term ) {
            if ( empty( $term ) ) {
                continue;
            }
            
            if ( is_numeric( $term ) ) {
                $term_obj = get_term( $term, $taxonomy['name'] );
                if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                    $term_values[] = $term_obj->name;
                }
            } else {
                // For text inputs, check if term exists or create it
                $existing_term = term_exists( $term, $taxonomy['name'] );
                if ( ! $existing_term ) {
                    $new_term = wp_insert_term( $term, $taxonomy['name'] );
                    if ( ! is_wp_error( $new_term ) ) {
                        $term_values[] = $term;
                    }
                } else {
                    $term_values[] = $term;
                }
            }
        }
        
        // Get proper attribute label - prioritize form field label
        $attribute_label = '';
        
        // First priority: Use the label from the form field configuration
        if ( ! empty( $taxonomy['label'] ) ) {
            $attribute_label = $taxonomy['label'];
        } else {
            // Second priority: Try WooCommerce attribute label
            $attribute_label = wc_attribute_label( $taxonomy['name'] );
            if ( empty( $attribute_label ) ) {
                // Final fallback: format the taxonomy name
                $attribute_label = ucwords( str_replace( [ '_', 'pa_' ], ' ', $taxonomy['name'] ) );
            }
        }
        
        return [
            'name'         => $taxonomy['name'],
            'value'        => implode( ' | ', array_filter( $term_values ) ),
            'is_visible'   => ! empty( $taxonomy['woo_attr_vis'] ) && ( 'yes' === $taxonomy['woo_attr_vis'] ) ? 1 : 0,
            'is_variation' => 0,
            'is_taxonomy'  => 1,
            'position'     => 0,
            'label'        => $attribute_label, // Use form-level label
        ];
    }

    /**
     * Initialize WooCommerce hooks
     * Call this method when using the trait to set up required filters
     *
     * @since 4.0.6
     */
    public function init_woocommerce_hooks() {
        if ( class_exists( 'WooCommerce' ) ) {
            add_filter( 'woocommerce_display_product_attributes', [ $this, 'filter_woocommerce_product_attributes' ], 10, 2 );
        }
    }

    /**
     * Filter WooCommerce product attributes to exclude main taxonomies and show proper labels
     *
     * @param array $product_attributes
     * @param \WC_Product $product
     * @return array
     */
    public function filter_woocommerce_product_attributes( $product_attributes, $product ) {
        // Remove product categories, tags, and brands from additional information
        $unwanted_keys = [ 'product_cat', 'product_tag', 'product_brand' ];
        foreach ( $unwanted_keys as $unwanted ) {
            unset( $product_attributes[ $unwanted ] );
        }

        // For WPUF products, do additional processing
        $form_id = get_post_meta( $product->get_id(), '_wpuf_form_id', true );
        if ( ! $form_id ) {
            return $product_attributes;
        }

        // Get form configuration to access field labels
        $form_fields = get_post_meta( $form_id, 'wpuf_form', true );
        $field_labels = [];
        
        // Extract field labels for taxonomy fields
        if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
            foreach ( $form_fields as $field ) {
                if ( isset( $field['input_type'] ) && $field['input_type'] === 'taxonomy' && ! empty( $field['name'] ) && ! empty( $field['label'] ) ) {
                    $field_labels[ $field['name'] ] = $field['label'];
                }
            }
        }

        // Process remaining attributes to ensure proper labels and values
        foreach ( $product_attributes as $key => &$attribute ) {
            // Skip weight and dimensions as they're handled by WooCommerce
            if ( in_array( $key, [ 'weight', 'dimensions' ] ) ) {
                continue;
            }

            // For attributes with 'attribute_' prefix, extract the actual taxonomy name
            $taxonomy_name = $key;
            if ( strpos( $key, 'attribute_' ) === 0 ) {
                $taxonomy_name = substr( $key, 10 ); // Remove 'attribute_' prefix
            }
            
            // ALWAYS use form-level labels if available for consistency
            if ( ! empty( $field_labels[ $taxonomy_name ] ) ) {
                $attribute['label'] = $field_labels[ $taxonomy_name ];
            }
            // If no form label found, keep the existing label (which should match the taxonomy name)

            // Strip HTML tags from values first (WooCommerce may wrap values in HTML)
            if ( ! empty( $attribute['value'] ) ) {
                $attribute['value'] = wp_strip_all_tags( $attribute['value'] );
            }
            
            // Ensure values are displayed as term names, not IDs
            if ( taxonomy_exists( $taxonomy_name ) ) {
                
                $terms = get_the_terms( $product->get_id(), $taxonomy_name );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $term_names = [];
                    foreach ( $terms as $term ) {
                        // Special handling for certain taxonomies
                        if ( 'product_shipping_class' === $taxonomy_name && '-1' === $term->name ) {
                            $term_names[] = 'No shipping class';
                        } elseif ( 'product_shipping_class' === $taxonomy_name && is_numeric( $term->name ) ) {
                            // If shipping class term name is numeric, it might be referencing another term ID
                            $real_term = get_term( intval( $term->name ), $taxonomy_name );
                            if ( $real_term && ! is_wp_error( $real_term ) ) {
                                $term_names[] = $real_term->name;
                            } else {
                                $term_names[] = $term->name;
                            }
                        } elseif ( 'product_visibility' === $taxonomy_name ) {
                            // Keep visibility terms as-is (don't format them)
                            $term_names[] = $term->name;
                        } elseif ( 'product_type' === $taxonomy_name ) {
                            // For product type, ensure we show the actual saved value
                            $term_names[] = $term->slug; // Use slug for product type
                        } else {
                            $term_names[] = $term->name;
                        }
                    }
                    if ( ! empty( $term_names ) ) {
                        $attribute['value'] = implode( ', ', $term_names );
                    }
                } else {
                    // If no terms found via get_the_terms, but attribute has a value,
                    // try to convert ID values to term names
                    if ( ! empty( $attribute['value'] ) ) {
                        $value_parts = explode( ', ', $attribute['value'] );
                        $converted_values = [];
                        
                        foreach ( $value_parts as $part ) {
                            $part = trim( $part );
                            // Check if it's a numeric ID
                            if ( is_numeric( $part ) ) {
                                $term = get_term( $part, $taxonomy_name );
                                if ( $term && ! is_wp_error( $term ) ) {
                                    if ( 'product_shipping_class' === $taxonomy_name && '-1' === $term->name ) {
                                        $converted_values[] = 'No shipping class';
                                    } elseif ( 'product_visibility' === $taxonomy_name ) {
                                        // Keep visibility as-is
                                        $converted_values[] = $term->name;
                                    } elseif ( 'product_type' === $taxonomy_name ) {
                                        // Use slug for product type
                                        $converted_values[] = $term->slug;
                                    } else {
                                        $converted_values[] = $term->name;
                                    }
                                } else {
                                    $converted_values[] = $part; // Keep original if conversion fails
                                }
                            } else {
                                $converted_values[] = $part; // Keep original if not numeric
                            }
                        }
                        
                        if ( ! empty( $converted_values ) ) {
                            $attribute['value'] = implode( ', ', $converted_values );
                        }
                    }
                }
            }
        }

        return $product_attributes;
    }
}
