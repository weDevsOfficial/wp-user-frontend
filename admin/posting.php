<?php

/**
 * Admin side posting handler
 *
 * Builds custom fields UI for post add/edit screen
 * and handles value saving.
 *
 * @package WP User Frontend
 */
class WPUF_Admin_Posting extends WPUF_Render_Form {

    private static $_instance;

    function __construct() {
        // meta boxes
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes') );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box_form_select') );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script') );

        add_action( 'save_post', array( $this, 'save_meta'), 1, 2 ); // save the custom fields
        add_action( 'save_post', array( $this, 'form_selection_metabox_save' ), 1, 2 ); // save edit form id
    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function enqueue_script() {
        global $pagenow;

        if ( !in_array( $pagenow, array( 'profile.php', 'post-new.php', 'post.php', 'user-edit.php' ) ) ) {
            return;
        }

        $scheme = is_ssl() ? 'https' : 'http';
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        wp_enqueue_style( 'jquery-ui', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker') );

        if ( !empty( $api_key ) ) {
            wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key='.$api_key, array(), null );
        } else {
            add_action('admin_head', 'wpuf_hide_google_map_button');

            function wpuf_hide_google_map_button() {
              echo "<style>
                button.button[data-name='custom_map'] {
                    display: none;
                }
              </style>";
            }
        }

        wp_enqueue_style( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.css', array(), WPUF_VERSION );
        wp_enqueue_script( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.js', array(), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', array('jquery', 'plupload-handlers') );
        wp_localize_script( 'wpuf-upload', 'wpuf_frontend_upload', array(
            'confirmMsg' => __( 'Are you sure?', 'wp-user-frontend' ),
            'delete_it'  => __( 'Yes, delete it', 'wp-user-frontend' ),
            'cancel_it'  => __( 'No, cancel it', 'wp-user-frontend' ),
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
            'plupload'   => array(
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => array(array('title' => __( 'Allowed Files', 'wp-user-frontend' ), 'extensions' => '*')),
                'multipart'        => true,
                'urlstream_upload' => true,
                'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wp-user-frontend' ),
                'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend' )
            )
        ) );
    }

    /**
     * Meta box for all Post form selection
     *
     * Registers a meta box in public post types to select the desired WPUF
     * form select box to assign a form id.
     *
     * @since 2.5.2
     *
     * @return void
     */
    function add_meta_box_form_select() {

        $post_types = get_post_types( array('public' => true) );
        foreach ($post_types as $post_type) {
            add_meta_box( 'wpuf-select-form', __('WPUF Form', 'wp-user-frontend'), array($this, 'form_selection_metabox'), $post_type, 'side', 'high' );
        }
    }


    /**
     * Form selection meta box in post types
     *
     * Registered via $this->add_meta_box_form_select()
     *
     * @since 2.5.2
     *
     * @global object $post
     */
    function form_selection_metabox() {
        global $post;

        $forms = get_posts( array('post_type' => 'wpuf_forms', 'numberposts' => '-1') );
        $selected = get_post_meta( $post->ID, '_wpuf_form_id', true );
        ?>

        <input type="hidden" name="wpuf_form_select_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

        <select name="wpuf_form_select">
            <option value="">--</option>
            <?php foreach ($forms as $form) { ?>
            <option value="<?php echo $form->ID; ?>"<?php selected($selected, $form->ID); ?>><?php echo $form->post_title; ?></option>
            <?php } ?>
        </select>
        <div>
            <p><a href="https://wedevs.com/docs/wp-user-frontend-pro/tutorials/purpose-of-the-wpuf-form-metabox/" target="_blank"><?php _e( 'Purpose of this metabox', 'wp-user-frontend' ); ?></a></p>
        </div>
        <?php
    }

    /**
     * Saves the form ID from form selection meta box
     *
     * @since 2.5.2
     *
     * @param int $post_id
     * @param object $post
     * @return int|void
     */
    function form_selection_metabox_save( $post_id, $post ) {
        if ( !isset($_POST['wpuf_form_select'])) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_form_select_nonce'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        update_post_meta( $post->ID, '_wpuf_form_id', $_POST['wpuf_form_select'] );
    }

    /**
     * Meta box to show WPUF Custom Fields
     *
     * Registers a meta box in public post types to show WPUF Custom Fields
     *
     * @since 2.5
     *
     * @return void
     */
    function add_meta_boxes() {
        $post_types = get_post_types( array('public' => true) );

        foreach ($post_types as $post_type) {
            add_meta_box( 'wpuf-custom-fields', __( 'WPUF Custom Fields', 'wp-user-frontend' ), array($this, 'render_form'), $post_type, 'normal', 'high' );
        }
    }

    /**
     * function to hide form custom field
     *
     * @since 2.5
     *
     * @return void
     */
    function hide_form() {
        ?>
        <style type="text/css">
            #wpuf-custom-fields { display: none; }
        </style>
        <?php
    }

    /**
     * generate frontend form field
     *
     * @since 2.5
     *
     * @param int $form_id
     * @param int $post_id
     *
     * @return void
     */
    function render_form( $form_id, $post_id = null ) {
        global $post;

        $form_id = get_post_meta( $post->ID, '_wpuf_form_id', true );
        $form_settings = wpuf_get_form_settings( $form_id );

        /**
         * There may be incompatibilities with WPUF metabox display when Advanced Custom Fields
         * is active. By default WPUF metaboxes will be hidden when ACF is detected. However,
         * you can override that by using the following filter.
         */
        $hide_with_acf = class_exists( 'acf' ) ? apply_filters( 'wpuf_hide_meta_when_acf_active', true ) : false;

        // hide the metabox itself if no form ID is set
        if ( !$form_id || $hide_with_acf ) {
            $this->hide_form();
            return;
        }

        list($post_fields, $taxonomy_fields, $custom_fields) = $this->get_input_fields( $form_id );

        if ( empty( $custom_fields ) ) {
            _e( 'No custom fields found.', 'wp-user-frontend' );
            return;
        }
        ?>

        <input type="hidden" name="wpuf_cf_update" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <input type="hidden" name="wpuf_cf_form_id" value="<?php echo $form_id; ?>" />

        <table class="form-table wpuf-cf-table">
            <tbody>
                <?php
                $this->render_items( $custom_fields, $post->ID, 'post', $form_id, $form_settings );
                ?>
            </tbody>
        </table>
        <?php
        $this->scripts_styles();
    }

    /**
     * Prints form input label
     *
     * @param string $attr
     */
    function label( $attr, $post_id = 0 ) {
        ?>
        <?php echo $attr['label'] . $this->required_mark( $attr ); ?>
        <?php
    }

    /**
     * generate table header of frontend form field
     *
     * @since 2.5
     *
     * @param array $form_field
     * @param int $post_id
     *
     * @return void
     */
    function render_item_before( $form_field, $post_id = 0 ) {
        echo '<tr>';
        echo '<th><strong>';
        $this->label( $form_field );
        echo '</strong></th>';
        echo '<td>';
    }

    /**
     * generate table bottom of frontend form field
     *
     * @since 2.5
     *
     * @param array $form_field
     *
     * @return void
     */
    function render_item_after( $form_field ) {
        echo '</td>';
        echo '</tr>';
    }

    function scripts_styles() {
        ?>
        <script type="text/javascript">
            jQuery(function($){
                var wpuf = {
                    init: function() {
                        $('.wpuf-cf-table').on('click', 'img.wpuf-clone-field', this.cloneField);
                        $('.wpuf-cf-table').on('click', 'img.wpuf-remove-field', this.removeField);
                        $('.wpuf-cf-table').on('click', 'a.wpuf-delete-avatar', this.deleteAvatar);
                    },
                    cloneField: function(e) {
                        e.preventDefault();

                        var $div = $(this).closest('tr');
                        var $clone = $div.clone();
                        // console.log($clone);

                        //clear the inputs
                        $clone.find('input').val('');
                        $clone.find(':checked').attr('checked', '');
                        $div.after($clone);
                    },

                    removeField: function() {
                        //check if it's the only item
                        var $parent = $(this).closest('tr');
                        var items = $parent.siblings().andSelf().length;

                        if( items > 1 ) {
                            $parent.remove();
                        }
                    },

                    deleteAvatar: function(e) {
                        e.preventDefault();

                        var data = {
                            action: 'wpuf_delete_avatar',
                            user_id : $('#profile-page').find('#user_id').val(),
                            _wpnonce: '<?php echo wp_create_nonce( 'wpuf_nonce' ); ?>'
                        };

                        if ( confirm( $(this).data('confirm') ) ) {
                            $.post(ajaxurl, data, function() {
                                window.location.reload();
                            });
                        }
                    }
                };

                wpuf.init();
            });

        </script>
        <style type="text/css">
            ul.wpuf-attachment-list li {
                display: inline-block;
                border: 1px solid #dfdfdf;
                padding: 5px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                margin-right: 5px;
            }
            .wpuf-cf-table table th,
            .wpuf-cf-table table td{
                padding-left: 0 !important;
            }

            .wpuf-cf-table .required { color: red;}
            .wpuf-cf-table textarea { width: 400px; }

            .wpuf-field-google-map {
                height: 300px;
                width: 100%;
            }
            .wpuf-form-google-map {
                height: 300px;
                width: 100%;
            }
            input[type="text"].wpuf-google-map-search {
                margin-top: 10px !important;
                border: 1px solid transparent !important;
                border-radius: 2px 0 0 2px !important;
                box-sizing: border-box !important;
                -moz-box-sizing: border-box !important;
                height: 32px !important;
                outline: none !important;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
                background-color: #fff !important;
                text-overflow: ellipsis !important;
                width: 170px !important;
                font-family: Roboto !important;
                font-size: 15px !important;
                font-weight: 300 !important;
                padding: 0 11px 0 13px !important;
                display: none;
            }
            .gm-style input[type="text"].wpuf-google-map-search {
                display: block;
            }
            .wpuf-form-google-map-container input[type="text"].wpuf-google-map-search {
                width: 230px !important;
            }
            .wpuf-form-google-map-container.hide-search-box .gm-style input[type="text"].wpuf-google-map-search {
                display: none;
            }

        </style>
        <?php
    }

    /**
     * save post meta
     *
     * @since 2.5
     *
     * @param object $post
     *
     * @return void
     */
    // Save the Metabox Data
    function save_meta( $post_id, $post = null ) {

        if ( !isset( $post_id ) ) {
            return;
        }

        if ( !isset( $_POST['wpuf_cf_update'] ) ) {
            return $post_id;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_cf_update'], plugin_basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        list( $post_vars, $tax_vars, $meta_vars ) = self::get_input_fields( $_POST['wpuf_cf_form_id'] );

        WPUF_Frontend_Form_Post::update_post_meta( $meta_vars, $post_id );
    }

}
