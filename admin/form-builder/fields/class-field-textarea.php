<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Textarea extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Textarea', 'wp-user-frontend' );
        $this->input_type = 'textarea_field';
        $this->icon       = 'paragraph';
    }

    /**
     * Render textarea field
     * @param array $attr
     * @param int $post_id
     * @param $type
     * @param $form_id
     */
    public function render( $attr, $post_id, $type = 'post', $form_id = null ) {

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
            <div class="wpuf-fields wpuf-rich-validation <?php printf( 'wpuf_%s_%s', $attr['name'], $form_id ); ?>" data-type="rich" data-required="<?php echo esc_attr( $attr['required'] ); ?>" data-id="<?php echo esc_attr( $attr['name'] ) . '_' . $form_id; ?>" data-name="<?php echo esc_attr( $attr['name'] ); ?>">
        <?php } else { ?>
            <div class="wpuf-fields">
        <?php } ?>

        <?php if ( isset( $attr['insert_image'] ) && $attr['insert_image'] == 'yes' ) { ?>
            <div id="wpuf-insert-image-container">
                <a class="wpuf-button wpuf-insert-image" id="wpuf-insert-image_<?php echo $form_id; ?>" href="#" data-form_id="<?php echo $form_id; ?>">
                    <span class="wpuf-media-icon"></span>
                    <?php _e( 'Insert Photo', 'wp-user-frontend' ); ?>
                </a>
            </div>

            <script type="text/javascript">
                ;(function($) {
                    $(document).ready( function(){
                        WP_User_Frontend.insertImage('wpuf-insert-image_<?php echo $form_id; ?>', '<?php echo $form_id; ?>');
                    });
                })(jQuery);
            </script>
        <?php } ?>

        <?php
        $form_settings = wpuf_get_form_settings( $form_id );
        $layout        = isset( $form_settings['form_layout'] ) ? $form_settings['form_layout'] : 'layout1';
        $textarea_id   = $attr['name'] ? $attr['name'] . '_' . $form_id : 'textarea_' . $this->field_count;
        $content_css   = includes_url()."js/tinymce/skins/wordpress/wp-content.css";

        if ( $attr['rich'] == 'yes' ) {
            $editor_settings = array(
                'textarea_rows' => $attr['rows'],
                'quicktags'     => false,
                'media_buttons' => false,
                'editor_class'  => $req_class,
                'textarea_name' => $attr['name'],
                'tinymce'       => array(
                    'content_css'   => $content_css.", ". WPUF_ASSET_URI . '/css/frontend-form/' . $layout . '.css'
                )
            );

            $editor_settings = apply_filters( 'wpuf_textarea_editor_args' , $editor_settings );
            wp_editor( $value, $textarea_id, $editor_settings );

        } elseif( $attr['rich'] == 'teeny' ) {

            $editor_settings = array(
                'textarea_rows' => $attr['rows'],
                'quicktags'     => false,
                'media_buttons' => false,
                'teeny'         => true,
                'editor_class'  => $req_class,
                'textarea_name' => $attr['name'],
                'tinymce'       => array(
                    'content_css'   => $content_css.", ". WPUF_ASSET_URI . '/css/frontend-form/' . $layout . '.css'
                )
            );

            $editor_settings = apply_filters( 'wpuf_textarea_editor_args' , $editor_settings );
            wp_editor( $value, $textarea_id, $editor_settings );

        } else {
            ?>
            <textarea class="textareafield<?php echo $this->required_class( $attr ); ?> <?php echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" id="<?php echo $attr['name'] . '_' . $form_id; ?>" name="<?php echo $attr['name']; ?>" data-required="<?php echo $attr['required'] ?>" data-type="textarea"<?php $this->required_html5( $attr ); ?> placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" rows="<?php echo $attr['rows']; ?>" cols="<?php echo $attr['cols']; ?>"><?php echo esc_textarea( $value ) ?></textarea>
            <span class="wpuf-wordlimit-message wpuf-help"></span>
        <?php } ?>
        <?php $this->help_text( $attr ); ?>
        </div>
        <?php

        if ( isset( $attr['word_restriction'] ) && $attr['word_restriction'] ) {
            $this->check_word_restriction_func( $attr['word_restriction'], $attr['rich'], $attr['name'] . '_' . $form_id );
        }
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings();
        $default_text_options = $this->get_default_textarea_option_settings();

        return array_merge( $default_options, $default_text_options );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'input_type'       => 'textarea',
            'word_restriction' => '',
            'rows'             => 5,
            'cols'             => 25,
            'rich'             => 'no',
        );

        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {
       return wp_kses_post( $_POST[$field['name']] );
    }
}
