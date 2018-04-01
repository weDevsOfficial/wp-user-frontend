<?php

/**
 * Acf integration class
 */
class WPUF_ACF_Compatibility {

    public $id      = 'acf';
    public $title   = 'Advanced Custom Fields';

	function __construct() {
        add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );

        add_action( 'wp_ajax_wpuf_dismiss_notice_' . $this->id, array( $this, 'dismiss_notice' ) );
        add_action( 'wp_ajax_wpuf_compatibility_' . $this->id, array( $this, 'maybe_compatible' ) );
        add_action( 'wp_ajax_wpuf_migrate_' . $this->id, array( $this, 'migrate_cf_data' ) );
	}

    /**
     * See if ACF plugin exists
     *
     * @return boolean
     */
	public function plugin_exists() {
		return class_exists( 'acf' );
	}

    /**
     * If the prompt is dismissed
     *
     * @return boolean
     */
    public function is_dismissed() {
        return 'yes' == get_option( 'wpuf_dismiss_notice_' . $this->id );
    }

    /**
     * Check if 
     *
     * @return boolean
     */
    public function is_compatible() {
        return 'yes' == wpuf_get_option( 'wpuf_compatibility_' . $this->id, 'wpuf_general', 'no' );
    }

    /**
     * Check if
     *
     * @return boolean
     */
    public function is_migrated() {
        return 'yes' == get_option( 'wpuf_migrate_' . $this->id );
    }

    /**
     * Dismiss the prompt
     *
     * @return void
     */
    public function dismiss_prompt() {
        update_option( 'wpuf_dismiss_notice_' . $this->id, 'yes' );
    }

    /**
     * Update option
     *
     *@return void
     */
    public function maybe_compatible() {
        wpuf_update_option( 'wpuf_compatibility_' . $this->id, 'wpuf_general', 'yes' );

        wp_send_json_success();
    }

    /**
     * Update existing custom fields data
     *
     *@return void
     */
    public function migrate_cf_data() {
        $forms = $this->get_post_forms();

        if ( !empty( $forms ) ) {
            foreach ( $forms as $form ) {
                $form_id        = $form->ID;
                $form_vars      = wpuf_get_form_fields( $form_id );
                $form_settings  = wpuf_get_form_settings( $form_id );
                $post_type      = $form_settings['post_type'];

                foreach ( $form_vars as $attr ) {
                    $field_type = $attr['input_type'];
                    $meta       = $attr['is_meta'];

                    if ( $meta == 'yes' && ( $field_type == 'checkbox' || $field_type == 'multiselect' ) ) {
                        $meta_key = $attr['name'];
                        
                        $args = array(
                            'post_type'   => $post_type,
                            'meta_key'    => '_wpuf_form_id',
                        );
                        $posts = get_posts( $args ); 

                        if ( !empty( $posts ) ) {
                            foreach ($posts as $post) {
                                $post_id    = $post->ID;
                                $separator  = '| ';
                                $meta_value = get_post_meta( $post_id, $meta_key );

                                if ( !empty( $meta_value ) ) {
                                    $new_value = explode( $separator, $meta_value[0] );
                                    $new_value = maybe_serialize( $new_value );

                                    update_post_meta( $post_id, $meta_key, $new_value );
                                }
                            }
                        }
                    }
                }
            }
        }

        update_option( 'wpuf_migrate_' . $this->id, 'yes' );
        wpuf_update_option(  'wpuf_compatibility_' . $this->id, 'wpuf_general', 'yes' );

        wp_send_json_success();
    }

    /**
     * Get all post form 
     *
     *@return array
     */
    public function get_post_forms() {
        $args = array(
            'post_type'   => 'wpuf_forms',
            'post_status' => 'publish',
        );
        return $form_posts = get_posts( $args ); 
    }

    /**
     * Dismiss the notice
     *
     * @return void
     */
    public function dismiss_notice() {
        $this->dismiss_prompt();

        wp_send_json_success();
    }

	/**
     * Show notice if the plugin found
     *
     * @return void
     */
    public function maybe_show_notice() {
        if ( ! $this->plugin_exists() ) {
            return;
        }

        if ( $this->is_dismissed() || $this->is_compatible() || $this->is_migrated() || !current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="notice notice-info">
            <p><strong><?php printf( __( '%s Detected', 'wpuf' ), $this->title ); ?></strong></p>
            <p><?php printf( __( 'Hey, looks like you have <strong>%s</strong> installed. What do you want to do with WPUF?', 'wpuf' ), $this->title ); ?></p>
            <p><i><strong style="color:#46b450;">Compatible: </strong><?php printf( __( 'It will update compatibility option only, so existing custom fields data format will not change.', 'wpuf' ) ); ?></i></p>
            <p><i><strong style="color:#46b450;">Compatible & Migrate: </strong><?php printf( __( 'It will update existing custom fields data to ACF format and update compatibility option too.', 'wpuf' ) ); ?></i></p>

            <p>
                <a href="#" class="button button-primary" id="wpuf-compatible-<?php echo $this->id ;?>"><?php _e( 'Compatible', 'wpuf' ); ?></a>
                <a href="#" class="button button-primary" id="wpuf-migrate-<?php echo $this->id ;?>"><?php _e( 'Compatible & Migrate', 'wpuf' ); ?></a>
                <a href="#" class="button" id="wpuf-dismiss-<?php echo $this->id ;?>"><?php _e( 'No Thanks', 'wpuf' ); ?></a>
            </p>
        </div>

        <script type="text/javascript">
            jQuery(function($) {
                $('.notice').on('click', 'a#wpuf-compatible-<?php echo $this->id ;?>', function(e) {
                    e.preventDefault();

                    var self = $(this);
                    self.addClass('updating-message');
                    wp.ajax.send('wpuf_compatibility_<?php echo $this->id ;?>', {
                        success: function() {
                            var html = '<p><strong>Compatible Option Updated</strong></p>';

                            self.closest('.notice').removeClass('notice-info').addClass('notice-success').html( html );
                        },

                        error: function() {
                            var html = '<p><strong>Something went wrong.</strong></p>';

                            self.closest('.notice').removeClass('notice-info').addClass('notice-error').html( html );
                        },

                        complete: function() {
                            self.removeClass('updating-message');
                        }                         
                    });
                });

                $('.notice').on('click', 'a#wpuf-migrate-<?php echo $this->id ;?>', function(e) {
                    e.preventDefault();

                    var self = $(this);
                    self.addClass('updating-message');
                    wp.ajax.send('wpuf_migrate_<?php echo $this->id ;?>', {
                        success: function() {
                            var html  = '<p><strong>Compatible option and existing custom fields data updated</strong></p>';

                            self.closest('.notice').removeClass('notice-info').addClass('notice-success').html( html );
                        },

                        error: function() {
                            var html = '<p><strong>Something went wrong.</strong></p>';

                            self.closest('.notice').removeClass('notice-info').addClass('notice-error').html( html );
                        },

                        complete: function() {
                            self.removeClass('updating-message');
                        }                        
                    });
                });

                $('.notice').on('click', '#wpuf-dismiss-<?php echo $this->id ;?>', function(e) {
                    e.preventDefault();

                    $(this).closest('.notice').remove();
                    wp.ajax.send('wpuf_dismiss_notice_<?php echo $this->id ;?>');
                });

            });
        </script>
        
        <?php
    }
}