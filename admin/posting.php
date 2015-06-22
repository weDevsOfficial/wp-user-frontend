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

    function __construct() {
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_script') );

        add_action( 'save_post', array($this, 'save_meta'), 1, 2 ); // save the custom fields
    }

    function enqueue_script() {
        global $pagenow;

        if ( !in_array( $pagenow, array('profile.php', 'post-new.php', 'post.php', 'user-edit.php') ) ) {
            return;
        }

        $scheme = is_ssl() ? 'https' : 'http';

        wp_enqueue_style( 'jquery-ui', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker') );
        wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );

        wp_enqueue_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', array('jquery', 'plupload-handlers') );
        wp_localize_script( 'wpuf-upload', 'wpuf_frontend_upload', array(
            'confirmMsg' => __( 'Are you sure?', 'wpuf' ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wpuf_nonce' ),
            'plupload' => array(
                'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf_featured_img' ),
                'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters' => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart' => true,
                'urlstream_upload' => true,
            )
        ) );
    }

    function add_meta_boxes() {
        $post_types = get_post_types( array('public' => true) );

        foreach ($post_types as $post_type) {
            add_meta_box( 'wpuf-custom-fields', __( 'WPUF Custom Fields', 'wpuf' ), array($this, 'render_form'), $post_type, 'normal', 'high' );
        }
    }

    function hide_form() {
        ?>
        <style type="text/css">
            #wpuf-custom-fields { display: none; }
        </style>
        <?php
    }

    function render_form( $form_id, $post_id = null, $preview = false) {
        global $post;

        $form_id = get_post_meta( $post->ID, '_wpuf_form_id', true );
        $form_settings = wpuf_get_form_settings( $form_id );


        // hide the metabox itself if no form ID is set
        if ( !$form_id ) {
            $this->hide_form();
            return;
        }

        list($post_fields, $taxonomy_fields, $custom_fields) = $this->get_input_fields( $form_id );

        if ( empty( $custom_fields ) ) {
            _e( 'No custom fields found.', 'wpuf' );
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

    function render_item_before( $form_field, $post_id = 0 ) {
        echo '<tr>';
        echo '<th><strong>';
        $this->label( $form_field );
        echo '</strong></th>';
        echo '<td>';
    }

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
            ul.wpuf-attachment-list li a.attachment-delete {
                text-decoration: none;
                padding: 3px 12px;
                border: 1px solid #C47272;
                color: #ffffff;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                border-radius: 3px;
                background-color: #da4f49;
                background-image: -moz-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ee5f5b), to(#bd362f));
                background-image: -webkit-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: -o-linear-gradient(top, #ee5f5b, #bd362f);
                background-image: linear-gradient(to bottom, #ee5f5b, #bd362f);
                background-repeat: repeat-x;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffee5f5b', endColorstr='#ffbd362f', GradientType=0);
                border-color: #bd362f #bd362f #802420;
                border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
                *background-color: #bd362f;
                filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
            }
            ul.wpuf-attachment-list li a.attachment-delete:hover,
            ul.wpuf-attachment-list li a.attachment-delete:active {
                color: #ffffff;
                background-color: #bd362f;
                *background-color: #a9302a;
            }

            .wpuf-cf-table table th,
            .wpuf-cf-table table td{
                padding-left: 0 !important;
            }

            .wpuf-cf-table .required { color: red;}
            .wpuf-cf-table textarea { width: 400px; }

        </style>
        <?php
    }

    // Save the Metabox Data
    function save_meta( $post_id, $post ) {
        if ( !isset( $_POST['wpuf_cf_update'] ) ) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_cf_update'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) )
            return $post->ID;

        list( $post_vars, $tax_vars, $meta_vars ) = self::get_input_fields( $_POST['wpuf_cf_form_id'] );

        WPUF_Frontend_Form_Post::update_post_meta( $meta_vars, $post->ID );
    }

}