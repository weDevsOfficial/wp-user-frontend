 <?php

/**
 * Textarea Field Class
 */
class WPUF_Form_Field_Textarea extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Textarea', 'wp-user-frontend' );
        $this->input_type = 'textarea_field';
        $this->icon       = 'paragraph';
    }

    /**
     * Render the Textarea field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null) {


        if ( isset( $post_id ) &&  $post_id != '0' ) {
            if ( $this->is_meta( $field_settings ) ) {
                $value = $this->get_meta( $post_id, $field_settings['name'], $type );
            }
        } else {
            $value = $field_settings['default'];
        }

        $req_class   = ( $field_settings['required'] == 'yes' ) ? 'required' : 'rich-editor';
        $textarea_id = $field_settings['name'] ? $field_settings['name'] . '_' . $form_id : 'textarea_';


        $this->field_print_label($field_settings, $form_id );

    ?>

            <?php if ( in_array( $field_settings['rich'], array( 'yes', 'teeny' ) ) ) { ?>
                <div class="wpuf-fields wpuf-rich-validation <?php printf( 'wpuf_%s_%s', $field_settings['name'], $form_id ); ?>" data-type="rich" data-required="<?php echo esc_attr( $field_settings['required'] ); ?>" data-id="<?php echo esc_attr( $field_settings['name'] ) . '_' . $form_id; ?>" data-name="<?php echo esc_attr( $field_settings['name'] ); ?>">
            <?php } else { ?>
                <div class="wpuf-fields">
            <?php } ?>

                <?php

                if ( $field_settings['rich'] == 'yes' ) {
                    $editor_settings = array(
                        'textarea_rows' => $field_settings['rows'],
                        'quicktags'     => false,
                        'media_buttons' => false,
                        'editor_class'  => $req_class,
                        'textarea_name' => $field_settings['name']
                    );

                    $editor_settings = apply_filters( 'wpuf_textarea_editor_args' , $editor_settings );
                    wp_editor( $value, $textarea_id, $editor_settings );

                } elseif( $field_settings['rich'] == 'teeny' ) {

                    $editor_settings = array(
                        'textarea_rows' => $field_settings['rows'],
                        'quicktags'     => false,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'editor_class'  => $req_class,
                        'textarea_name' => $field_settings['name']
                    );

                    $editor_settings = apply_filters( 'wpuf_textarea_editor_args' , $editor_settings );
                    wp_editor( $value, $textarea_id, $editor_settings );

                } else {
                    ?>
                    <textarea
                        class="textareafield <?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>"
                        id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                        name="<?php echo $field_settings['name']; ?>"
                        data-required="<?php echo $field_settings['required'] ?>"
                        data-type="textarea"
                        placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                        rows="<?php echo $field_settings['rows']; ?>"
                        cols="<?php echo $field_settings['cols']; ?>"
                    ><?php echo esc_textarea( $value ) ?></textarea>
                    <span class="wpuf-wordlimit-message wpuf-help"></span>

                <?php } ?>

                <?php
                $this->help_text( $field_settings );
                if ( isset( $field_settings['word_restriction'] ) && $field_settings['word_restriction'] ) {
                    $this->check_word_restriction_func(
                        $field_settings['word_restriction'],
                        $field_settings['rich'],
                        $field_settings['name'] . '_' . $form_id
                    );
                }

                ?>

        <?php $this->after_field_print_label();
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
            'label'            => __( 'Textarea', 'wp-user-frontend' ),
            'is_meta'          => 'yes',
            'rows'             => 5,
            'cols'             => 25,
            'rich'             => 'no',
            'id'               => 0,
            'is_new'           => true,
            'show_in_post'     => 'yes',
            'hide_field_label'  => 'no',
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
       return trim( $_POST[$field['name']] );
    }
}
