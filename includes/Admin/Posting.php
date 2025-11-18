<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Traits\FieldableTrait;

/**
 * Admin side posting handler
 *
 * Builds custom fields UI for post add/edit screen
 * and handles value saving.
 */
class Posting {
    use FieldableTrait;

    private static $_instance;

    /**
     * Tribe events custom fields
     *
     * @var array
     */
    protected $tribe_events_custom_fields = [];

    public function __construct() {
        $this->tribe_events_custom_fields = [
            '_EventAllDay',
            '_EventStartDate',
            '_EventEndDate',
            '_EventStartDateUTC',
            '_EventEndDateUTC',
            '_EventDuration',
            'venue',
            '_EventVenueID',
            'venue_name',
            'venue_website',
            'venue_phone',
            'venue_address',
            'organizer',
            '_EventOrganizerID',
            'organizer_name',
            'organizer_website',
            'organizer_email',
            'organizer_phone',
            '_EventShowMapLink',
            '_EventShowMap',
            '_EventCurrencySymbol',
            '_EventCurrencyCode',
            '_EventCurrencyPosition',
            '_EventCost',
            '_EventCostMin',
            '_EventCostMax',
            '_EventURL',
            '_EventOrganizerID',
            '_EventPhone',
            '_EventHideFromUpcoming',
            '_EventTimezone',
            '_EventTimezoneAbbr',
            '_tribe_events_errors',
            '_EventOrigin',
            '_tribe_featured',
        ];

        // meta boxes
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes'] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box_form_select'] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box_post_lock'] );
        // Remove global CSS loading to prevent leaks - only load on WPUF pages via hook
        // add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script'] );
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_script' ] );
        // add_action( 'admin_enqueue_scripts', [ $this, 'dequeue_assets' ] );
        add_action( 'wpuf_load_registration_forms', [ $this, 'enqueue_script' ] );
        add_action( 'save_post', [ $this, 'save_meta'], 100, 2 ); // save the custom fields
        add_action( 'save_post', [ $this, 'form_selection_metabox_save' ], 1, 2 ); // save edit form id
        add_action( 'save_post', [ $this, 'post_lock_metabox_save' ], 1, 2 ); // save post lock option
    }

    /**
     * Dequeue assets to avoid conflict
     *
     * @since 4.1.0
     *
     * @return void
     */
    public function dequeue_assets() {
        $post_form_page = 'wpuf-post-forms';

        if ( strpos( get_current_screen()->id, $post_form_page ) === false ) {
            return;
        }

        wp_dequeue_style( 'wpuf-form-builder' );
        if ( defined( 'WPUF_PRO_VERSION' ) && version_compare( WPUF_PRO_VERSION, '4.1', '<' ) ) {
            wp_dequeue_style( 'wpuf-form-builder-pro' );
        }
    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function enqueue_script() {
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        wp_enqueue_style( 'wpuf-admin-form-builder' );

        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-slider' );

        if( ! class_exists('ACF') ) {
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', ['jquery-ui-datepicker'] );
        }

        if ( !empty( $api_key ) ) {
            wp_enqueue_script( 'wpuf-google-maps' );
        } else {
            add_action( 'admin_head', 'wpuf_hide_google_map_button' );
        }

        wp_enqueue_style( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.css', [], '11.4.8' );
        wp_enqueue_script( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.js', [], '11.4.8', true );
        // wp_enqueue_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', ['jquery', 'plupload-handlers'] );
        wp_enqueue_script( 'wpuf-upload' );
        wp_localize_script( 'wpuf-upload', 'wpuf_upload', [
            'confirmMsg' => __( 'Are you sure?', 'wp-user-frontend' ),
            'delete_it'  => __( 'Yes, delete it', 'wp-user-frontend' ),
            'cancel_it'  => __( 'No, cancel it', 'wp-user-frontend' ),
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
            'plupload'   => [
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => [['title' => __( 'Allowed Files', 'wp-user-frontend' ), 'extensions' => '*']],
                'multipart'        => true,
                'urlstream_upload' => true,
                'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wp-user-frontend' ),
                'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend' ),
            ],
        ] );

        // Enqueue field initialization script for admin metabox

        // Enqueue Selectize for country fields
        wp_enqueue_style( 'wpuf-selectize' );
        wp_enqueue_script( 'wpuf-selectize' );

        // Enqueue international telephone input for phone fields
        wp_enqueue_style( 'wpuf-intlTelInput' );
        wp_enqueue_script( 'wpuf-intlTelInput' );

        // Try to load the field initialization script using the registered handle
        wp_enqueue_script( 'wpuf-field-initialization' );

        // Localize script with asset URI
        wp_localize_script( 'wpuf-field-initialization', 'wpuf_field_initializer', [
            'asset_uri' => defined( 'WPUF_PRO_ASSET_URI' ) ? WPUF_PRO_ASSET_URI : '',
        ] );


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
    public function add_meta_box_form_select() {
        $post_types = get_post_types( ['public' => true] );

        foreach ( $post_types as $post_type ) {
            add_meta_box( 'wpuf-select-form', __( 'WPUF Form', 'wp-user-frontend' ), [$this, 'form_selection_metabox'], $post_type, 'side', 'high' );
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
    public function form_selection_metabox() {
        global $post;

        $forms    = get_posts( ['post_type' => 'wpuf_forms', 'numberposts' => '-1'] );
        $selected = get_post_meta( $post->ID, '_wpuf_form_id', true ); ?>

        <!-- <input type="hidden" name="wpuf_form_select_nonce" value="<?php // echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" /> -->
        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wpuf_form_select_nonce' ); ?>
        <select name="wpuf_form_select">
            <option value="">--</option>
            <?php foreach ( $forms as $form ) { ?>
                <option value="<?php echo esc_attr( $form->ID ); ?>"<?php selected( $selected, $form->ID ); ?>><?php echo esc_html( $form->post_title ); ?></option>
            <?php } ?>
        </select>
        <div>
            <p><a href="https://wedevs.com/docs/wp-user-frontend-pro/tutorials/purpose-of-the-wpuf-form-metabox/" target="_blank"><?php esc_html_e( 'Learn more', 'wp-user-frontend' ); ?></a></p>
        </div>
        <?php
    }

    /**
     * Saves the form ID from form selection meta box
     *
     * @since 2.5.2
     *
     * @param int    $post_id
     * @param object $post
     *
     * @return int|void
     */
    public function form_selection_metabox_save( $post_id, $post ) {
        if ( !isset( $_POST['wpuf_form_select'] ) ) {
            return $post->ID;
        }
        $nonce = isset( $_POST['wpuf_form_select_nonce'] ) ? sanitize_key( wp_unslash( $_POST['wpuf_form_select_nonce'] ) ) : '';
        if ( isset( $nonce ) && !wp_verify_nonce( $nonce , plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }
        $wpuf_form_select = isset( $_POST['wpuf_form_select'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_form_select'] ) ) : '';

        update_post_meta( $post->ID, '_wpuf_form_id', $wpuf_form_select );
    }

    /**
     * Meta box for post lock
     *
     * Registers a meta box in public post types to select the desired WPUF
     * form select box to assign a form id.
     *
     * @since 3.0.2
     *
     * @return void
     */
    public function add_meta_box_post_lock() {
        $post_types = get_post_types( ['public' => true] );

        foreach ( $post_types as $post_type ) {
            add_meta_box( 'wpuf-post-lock', __( 'WPUF Lock User', 'wp-user-frontend' ), [$this, 'post_lock_metabox'], $post_type, 'side', 'high' );
        }
    }

    /**
     * Post lock meta box in post types
     *
     * Registered via $this->add_meta_box_post_lock()
     *
     * @since 3.0.2
     *
     * @global object $post
     */
    public function post_lock_metabox() {
        global $post;

        $msg                 = '';
        $edit_post_lock      = get_post_meta( $post->ID, '_wpuf_lock_editing_post', true );
        $edit_post_lock_time = get_post_meta( $post->ID, '_wpuf_lock_user_editing_post_time', true );

        if ( empty( $edit_post_lock_time ) ) {
            $is_locked = false;
        }

        if ( ( !empty( $edit_post_lock_time ) && $edit_post_lock_time < time() ) || $edit_post_lock == 'yes' ) {
            $is_locked = true;
            $msg       = sprintf(
                    // translators: %s is the post ID.
                    __( 'Post is locked, to allow user to edit this post <a id="wpuf_clear_schedule_lock" data="%s" href="#">Click here</a>', 'wp-user-frontend' ),
                    $post->ID
                );
        }

        if ( !empty( $edit_post_lock_time ) && $edit_post_lock_time > time() ) {
            $is_locked    = false;
            $time         = date( 'Y-m-d H:i:s', $edit_post_lock_time ); // phpcs:ignore
            $local_time   = get_date_from_gmt( $time, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
            $msg          = sprintf(
                                // translators: %1$s The time when the post edit access will be locked and %2$s The post ID.
                                __( 'Frontend edit access for this post will be automatically locked after %1$s, <a id="wpuf_clear_schedule_lock" data="%2$s" href="#">Clear Lock</a> Or,', 'wp-user-frontend' ),
                                $local_time, $post->ID
                            );
        } ?>

        <!-- <input type="hidden" name="wpuf_lock_editing_post_nonce" value="<?php // echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" /> -->
        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wpuf_lock_editing_post_nonce' ); ?>
        <p>
            <?php
                echo wp_kses( $msg, [
                    'a' => [
                            'href' => [],
                            'id'   => [],
                            'data' => [],
                        ]
                    ] );
            ?>
        </p>

        <label>
            <?php if ( !$is_locked ) { ?>
                <input type="hidden" name="wpuf_lock_post" value="no">
                <input type="checkbox" name="wpuf_lock_post" value="yes" <?php checked( $edit_post_lock, 'yes' ); ?>>
                <?php esc_html_e( 'Lock Post Permanently', 'wp-user-frontend' ); ?>
            <?php } ?>
        </label>

        <?php if ( !$is_locked ) { ?>
            <p style="margin-top: 10px"><?php esc_html_e( 'Lock user from editing this post from the frontend dashboard', 'wp-user-frontend' ); ?></p>
        <?php } ?>

        <?php
    }

    /**
     * Save the lock post option
     *
     * @since 3.0.2
     *
     * @param int    $post_id
     * @param object $post
     *
     * @return int|void
     */
    public function post_lock_metabox_save( $post_id, $post ) {
        $edit_post_lock_time = isset( $_POST['_wpuf_lock_user_editing_post_time'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpuf_lock_user_editing_post_time'] ) ) : '';

        if ( !isset( $_POST['wpuf_lock_post'] ) ) {
            return $post->ID;
        }

        $nonce = isset( $_POST['wpuf_lock_editing_post_nonce'] ) ? sanitize_key( wp_unslash( $_POST['wpuf_lock_editing_post_nonce'] ) ) : '';

        if ( isset( $nonce ) && !wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }
        $wpuf_lock_post = isset( $_POST['wpuf_lock_post'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_lock_post'] ) ) : '';

        update_post_meta( $post->ID, '_wpuf_lock_editing_post', $wpuf_lock_post );
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
    public function add_meta_boxes() {
        $post_types = get_post_types( ['public' => true] );

        foreach ( $post_types as $post_type ) {
            add_meta_box( 'wpuf-custom-fields', __( 'WPUF Custom Fields', 'wp-user-frontend' ), [$this, 'render_form'], $post_type, 'normal', 'high' );
        }
    }

    /**
     * function to hide form custom field
     *
     * @since 2.5
     *
     * @return void
     */
    public function hide_form() {
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
    public function render_form( $form_id, $post_id = null ) {
        global $post;

        $form_id       = get_post_meta( $post->ID, '_wpuf_form_id', true );
        $form_settings = wpuf_get_form_settings( $form_id );

        /**
         * There may be incompatibilities with WPUF metabox display when Advanced Custom Fields
         * is active. By default WPUF metaboxes will be hidden when ACF is detected. However,
         * you can override that by using the following filter.
         */
        $hide_with_acf = class_exists( 'acf' ) ? apply_filters( 'wpuf_hide_meta_when_acf_active', true ) : false;
        $acf_enable    = wpuf_get_option( 'wpuf_compatibility_acf', 'wpuf_general', 'yes' );

        if ( 'yes' === $acf_enable && $hide_with_acf ) {
            $hide_with_acf = false;
        }

        // hide the metabox itself if no form ID is set
        if ( ! $form_id || $hide_with_acf ) {
            $this->hide_form();

            return;
        }

        list( $post_fields, $taxonomy_fields, $custom_fields ) = $this->get_input_fields( $form_id );

        // check if this is an event post type
        if ( 'tribe_events' === $post->post_type ) {
            // remove the custom fields that are in the tribe_events_custom_fields array
            $custom_fields = array_filter( $custom_fields, function( $field ) {
                return ! in_array( $field['name'], $this->tribe_events_custom_fields );
            } );
        }

        if ( empty( $custom_fields ) ) {
            esc_html_e( 'No custom fields found.', 'wp-user-frontend' );

            return;
        } ?>

        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wpuf_cf_update' ); ?>
        <!-- <input type="hidden" name="wpuf_cf_update" value="<?php // echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" /> -->
        <input type="hidden" name="wpuf_cf_form_id" value="<?php echo esc_attr( $form_id ); ?>" />

        <table class="form-table wpuf-cf-table">
            <tbody>

                    <script type="text/javascript">
            if ( typeof wpuf_conditional_items === 'undefined' ) {
                wpuf_conditional_items = [];
            }

            if ( typeof wpuf_plupload_items === 'undefined' ) {
                wpuf_plupload_items = [];
            }

            if ( typeof wpuf_map_items === 'undefined' ) {
                wpuf_map_items = [];
            }

        </script>

            <?php
            $atts = [];

            // Render all fields including repeat fields
            if ( ! empty( $custom_fields ) ) {
                foreach ( $custom_fields as $field ) {
                    // Check if this is a repeat field
                    if ( isset( $field['template'] ) && $field['template'] === 'repeat_field' &&
                         isset( $field['input_type'] ) && $field['input_type'] === 'repeat' ) {
                        // Use special admin metabox rendering for repeat fields
                        if ( class_exists( 'WeDevs\Wpuf\Pro\Fields\Field_Repeat' ) ) {
                            $repeat_field_obj = new \WeDevs\Wpuf\Pro\Fields\Field_Repeat();
                            $repeat_field_obj->render_admin_metabox( $field, $form_id, 'post', $post->ID );
                        }
                    } else {
                        // Render other fields normally
                        if ( $field_object = wpuf()->fields->field_exists( $field['template'] ) ) {
                            if ( wpuf()->fields->check_field_visibility( $field ) ) {
                                if ( is_object( $field_object ) ) {
                                    $field_object->render( $field, $form_id, 'post', $post->ID );
                                    $field_object->conditional_logic( $field, $form_id );
                                }
                            }
                        }
                    }
                }
            }
            // wp_nonce_field( 'wpuf_form_add' ); ?>
            </tbody>
        </table>
        <style>
            .wpuf-add-repeat.button {
                margin-right: 5px;
            }
        </style>

        <?php
        $this->scripts_styles();
    }

    public function scripts_styles() {
        ?>
        <script type="text/javascript">
            jQuery(function($){
                var wpuf = {
                    init: function() {
                        $('.wpuf-cf-table').on('click', 'img.wpuf-clone-field', this.cloneField);
                        $('.wpuf-cf-table').on('click', 'img.wpuf-remove-field', this.removeField);
                        $('.wpuf-cf-table').on('click', 'a.wpuf-delete-avatar', this.deleteAvatar);
                        this.initRepeatField();
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
                            // _wpnonce: '<?php // echo wp_create_nonce( 'wpuf_nonce' ); ?>'
                            _wpnonce: wpuf_admin_script.nonce
                        };

                        if ( confirm( $(this).data('confirm') ) ) {
                            $.post(ajaxurl, data, function() {
                                window.location.reload();
                            });
                        }
                    },

                    initRepeatField: function() {
                        $('.wpuf-repeat-container').each(function() {
                            var $container = $(this);
                            var fieldName = $container.data('field-name');
                            var maxRepeats = parseInt($container.data('max-repeats')) || -1;


                            wpuf.updateRepeatButtons($container);

                            $container.on('click', '.wpuf-add-repeat', function() {
                                var $instance = $(this).closest('.wpuf-repeat-instance');
                                var instanceIndex = $instance.attr('data-instance');
                                wpuf.addRepeatInstance($container, fieldName, maxRepeats);
                            });

                            $container.on('click', '.wpuf-remove-repeat', function() {
                                var $instance = $(this).closest('.wpuf-repeat-instance');
                                var instanceIndex = $instance.attr('data-instance');
                                wpuf.removeRepeatInstance($instance, $container);
                            });
                        });
                    },

                    addRepeatInstance: function($container, fieldName, maxRepeats) {
                        var $instances = $container.find('.wpuf-repeat-instance');
                        var currentInstances = $instances.length;


                        if (maxRepeats !== -1 && currentInstances >= maxRepeats) {
                            return;
                        }

                        var $firstInstance = $instances.first();
                        var $newInstance = $firstInstance.clone();
                        var newInstanceIndex = currentInstances;

                        $newInstance.attr('data-instance', newInstanceIndex);

                        // Clear all input/textarea/select values in the new instance
                        $newInstance.find('input, textarea, select').each(function() {
                            var $input = $(this);
                            if ($input.is(':checkbox') || $input.is(':radio')) {
                                $input.prop('checked', false);
                            } else {
                                $input.val('');
                            }
                        });
                        // Also clear any textareas' inner text (for browsers that don't use .val() for <textarea>)
                        $newInstance.find('textarea').text('');

                        // Update names and ids for the new instance
                        $newInstance.find('[name], [id], [for]').each(function() {
                            var $element = $(this);
                            var originalName = $element.attr('name');
                            var originalId = $element.attr('id');
                            var originalFor = $element.attr('for');

                            if (originalName && originalName.includes('[')) {
                                var newName = originalName.replace(/\[\d+\]/, '[' + newInstanceIndex + ']');
                                $element.attr('name', newName);
                            }
                            if (originalId && originalId.includes('[')) {
                                var newId = originalId.replace(/\[\d+\]/, '[' + newInstanceIndex + ']');
                                $element.attr('id', newId);
                            }
                            if (originalFor && originalFor.includes('[')) {
                                var newFor = originalFor.replace(/\[\d+\]/, '[' + newInstanceIndex + ']');
                                $element.attr('for', newFor);
                            }
                        });

                        $container.append($newInstance);
                        wpuf.reindexInstances($container, fieldName);
                        wpuf.updateRepeatButtons($container);

                        // Set up MutationObserver for new buttons
                        if (window.MutationObserver && typeof observer !== 'undefined') {
                            $newInstance.find('.wpuf-add-repeat, .wpuf-remove-repeat').each(function() {
                                observer.observe(this, { attributes: true, attributeFilter: ['style', 'class'] });
                            });
                        }

                        // Initialize fields in the new instance
                        if (typeof WPUF_Field_Initializer !== 'undefined') {
                            WPUF_Field_Initializer.init();

                            // Re-apply button visibility after field initializer runs on new instance
                            setTimeout(function() {
                                wpuf.updateRepeatButtons($container);
                            }, 100);
                        }
                    },

                    removeRepeatInstance: function($instance, $container) {
                        var fieldName = $container.data('field-name');
                        var instanceIndex = $instance.attr('data-instance');
                        $instance.remove();
                        wpuf.reindexInstances($container, fieldName);
                        wpuf.updateRepeatButtons($container);
                    },

                    reindexInstances: function($container, fieldName) {
                        $container.find('.wpuf-repeat-instance').each(function(index) {
                            var $instance = $(this);
                            var oldIndex = $instance.attr('data-instance');
                            $instance.attr('data-instance', index);

                            $instance.find('[name], [id], [for]').each(function() {
                                var $element = $(this);
                                var originalName = $element.attr('name');
                                var originalId = $element.attr('id');
                                var originalFor = $element.attr('for');

                                if (originalName && originalName.includes('[')) {
                                    var newName = originalName.replace(/\[\d+\]/, '[' + index + ']');
                                    $element.attr('name', newName);
                                }

                                if (originalId && originalId.includes('[')) {
                                    var newId = originalId.replace(/\[\d+\]/, '[' + index + ']');
                                    $element.attr('id', newId);
                                }

                                if (originalFor && originalFor.includes('[')) {
                                    var newFor = originalFor.replace(/\[\d+\]/, '[' + index + ']');
                                    $element.attr('for', newFor);
                                }
                            });
                        });
                    },

                    updateRepeatButtons: function($container) {
                        var $instances = $container.find('.wpuf-repeat-instance');
                        var count = $instances.length;


                        // Prevent rapid successive calls
                        if ($container.data('updating-buttons')) {
                            return;
                        }
                        $container.data('updating-buttons', true);


                        $instances.each(function(i) {
                            var $instance = $(this);
                            var $controls = $instance.find('.wpuf-repeat-controls');
                            var isLast = i === count - 1;
                            var isOnlyInstance = count === 1;

                            // Clear existing buttons first
                            $controls.empty();

                            // Create and add buttons based on logic
                            var addButtonHtml = '<button type="button" class="wpuf-add-repeat button" data-instance="' + i + '">+</button>';
                            var removeButtonHtml = '<button type="button" class="wpuf-remove-repeat button" data-instance="' + i + '">-</button>';

                            // Add button: show only on last instance
                            if (isLast) {
                                $controls.append(addButtonHtml);
                            }

                            // Remove button: show on all instances EXCEPT when there's only 1 instance
                            if (!isOnlyInstance) {
                                $controls.append(removeButtonHtml);
                            }
                        });

                        // Clear the flag after a short delay
                        setTimeout(function() {
                            $container.removeData('updating-buttons');
                        }, 100);

                    }
                };

                wpuf.init();

                // Set up MutationObserver to watch for button visibility changes
                if (window.MutationObserver) {
                    var observer = new MutationObserver(function(mutations) {
                        var shouldUpdate = false;
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' &&
                                (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                                var $target = $(mutation.target);
                                if ($target.hasClass('wpuf-add-repeat') || $target.hasClass('wpuf-remove-repeat')) {
                                    shouldUpdate = true;
                                }
                            }
                        });

                        if (shouldUpdate) {
                            setTimeout(function() {
                                $('.wpuf-repeat-container').each(function() {
                                    var $container = $(this);
                                    wpuf.updateRepeatButtons($container);
                                });
                            }, 50);
                        }
                    });

                    // Start observing
                    $('.wpuf-repeat-container').each(function() {
                        var $container = $(this);
                        $container.find('.wpuf-add-repeat, .wpuf-remove-repeat').each(function() {
                            observer.observe(this, { attributes: true, attributeFilter: ['style', 'class'] });
                        });
                    });
                }

                // Initialize fields after the form is rendered with a delay to ensure DOM is ready
                setTimeout(function() {
                    // First, render all repeat field buttons
                    $('.wpuf-repeat-container').each(function() {
                        var $container = $(this);
                        wpuf.updateRepeatButtons($container);
                    });

                    if (typeof WPUF_Field_Initializer !== 'undefined') {
                        WPUF_Field_Initializer.init();
                    } else {
                        // Re-apply button visibility after field initializer runs
                        setTimeout(function() {
                            $('.wpuf-repeat-container').each(function() {
                                var $container = $(this);
                                wpuf.updateRepeatButtons($container);
                            });
                        }, 100);

                        // Also re-apply after a longer delay to catch any async field initialization
                        setTimeout(function() {
                            $('.wpuf-repeat-container').each(function() {
                                var $container = $(this);
                                wpuf.updateRepeatButtons($container);
                            });
                        }, 1000);
                    }

                }, 500);
            });

        </script>
        <style>
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

            /* Repeat field styles for admin metabox */
            .wpuf-repeat-container {
                margin: 10px 0;
            }

            .wpuf-repeat-instance {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 10px;
                background: #f9f9f9;
                border-radius: 4px;
                position: relative;
            }

            .wpuf-repeat-controls {
                position: absolute;
                top: 10px;
                right: 10px;
            }

            .wpuf-repeat-controls button {
                border: 1px solid #ccc !important;
                background: #fff;
                padding: 2px 8px;
                margin-left: 5px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
                line-height: 1.4;
            }

            .wpuf-repeat-controls button:hover {
                background: #f0f0f0;
            }

            .wpuf-repeat-controls .wpuf-add-repeat {
                color: #0073aa;
            }

            .wpuf-repeat-controls .wpuf-remove-repeat {
                color: #dc3232;
            }

            /* Field initialization styles for admin metabox */
            .wpuf-cf-table .wpuf-date-field,
            .wpuf-cf-table .wpuf-ratings,
            .wpuf-cf-table select[data-countries] {
                width: 100%;
                max-width: 400px;
            }

            .wpuf-cf-table .wpuf-repeat-instance .wpuf-date-field,
            .wpuf-cf-table .wpuf-repeat-instance .wpuf-ratings,
            .wpuf-cf-table .wpuf-repeat-instance select[data-countries] {
                width: 100%;
                max-width: 100%;
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
    public function save_meta( $post_id, $post = null ) {
        $wpuf_cf_update = isset( $_POST['wpuf_cf_update'] ) ? sanitize_key( wp_unslash( $_POST['wpuf_cf_update'] ) ) : '';
        $wpuf_cf_form_id  = isset( $_POST['wpuf_cf_form_id'] ) ? intval( $_POST['wpuf_cf_form_id'] ) : 0;

        if ( !isset( $post_id ) ) {
            return;
        }

        if ( empty( $wpuf_cf_update ) ) {
            return $post_id;
        }

        if ( isset( $wpuf_cf_update ) && !wp_verify_nonce( $wpuf_cf_update, plugin_basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        list( $post_vars, $tax_vars, $meta_vars ) = ( new Posting )->get_input_fields( $wpuf_cf_form_id );

        $meta_vars = array_filter( $meta_vars, function( $field ) {
            return !in_array( $field['name'], $this->tribe_events_custom_fields );
        } );

        // WPUF_Frontend_Form_Post::update_post_meta( $meta_vars, $post_id );
        $this->update_post_meta( $meta_vars, $post_id );
    }

    /**
     * Clear Schedule lock
     *
     * @since 3.0.2
     */
    public function clear_schedule_lock() {
        check_ajax_referer( 'wpuf_nonce', 'nonce' );

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : '';

        if ( !empty( $post_id ) ) {
            update_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', '' );
            update_post_meta( $post_id, '_wpuf_lock_editing_post', 'no' );
        }
        exit;
    }

    /**
     * Get input meta fields separated as post vars, taxonomy and meta vars
     *
     * @param int $form_id form id
     *
     * @return array
     */
    public static function get_input_fields( $form_id ) {
        $form_vars    = wpuf_get_form_fields( $form_id );

        $ignore_lists = ['section_break', 'html'];
        $post_vars    = $meta_vars = $taxonomy_vars = [];

        foreach ( $form_vars as $key => $value ) {
            // get column field input fields
            if ( $value['input_type'] == 'column_field' ) {
                $inner_fields = $value['inner_fields'];

                foreach ( $inner_fields as $column_key => $column_fields ) {
                    if ( !empty( $column_fields ) ) {
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
                                if ( $column_field['name'] == 'category' ) {
                                    continue;
                                }

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
                if ( $value['name'] == 'category' ) {
                    continue;
                }

                $taxonomy_vars[] = $value;
            } else {
                $post_vars[] = $value;
            }
        }

        return [$post_vars, $taxonomy_vars, $meta_vars];
    }
}
