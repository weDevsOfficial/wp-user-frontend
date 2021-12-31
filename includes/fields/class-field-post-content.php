<?php

/**
 * Post Title Field class
 */
class WPUF_Form_Field_Post_Content extends WPUF_Field_Contract {

    public function __construct() {
        $this->name       = __( 'Post Content', 'wp-user-frontend' );
        $this->input_type = 'post_content';
        $this->icon       = 'text-width';
    }

    /**
     * Render the PostTitle field
     *
     * @param array  $field_settings
     * @param int    $form_id
     * @param string $type
     * @param int    $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        if ( isset( $post_id ) ) {
            $value = get_post_field( $field_settings['name'], $post_id );
        } else {
            $value = $field_settings['default'];
        }

        $req_class   = ( $field_settings['required'] == 'yes' ) ? 'required' : 'rich-editor';
        $textarea_id = $field_settings['name'] ? $field_settings['name'] . '_' . $form_id : 'textarea_'; ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <?php if ( in_array( $field_settings['rich'], [ 'yes', 'teeny' ] ) ) { ?>
                <div class="wpuf-fields wpuf-rich-validation <?php printf( 'wpuf_%s_%s', esc_attr( $field_settings['name'] ), esc_attr( $form_id ) ); ?>" data-type="rich" data-required="<?php echo esc_attr( $field_settings['required'] ); ?>" data-id="<?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>" data-name="<?php echo esc_attr( $field_settings['name'] ); ?>">
            <?php } else { ?>
                <div class="wpuf-fields">
            <?php } ?>

                <?php

                if ( isset( $field_settings['insert_image'] ) && $field_settings['insert_image'] == 'yes' ) {
                    ?>
                <div id="wpuf-insert-image-container">
                    <a class="wpuf-button wpuf-insert-image" id="wpuf-insert-image_<?php echo esc_attr( $form_id ); ?>" href="#" data-form_id="<?php echo esc_attr( $form_id ); ?>">
                        <span class="wpuf-media-icon"></span>
                            <?php esc_html_e( 'Insert Photo', 'wp-user-frontend' ); ?>
                    </a>
                </div>

                <script type="text/javascript">
                    ;(function($) {
                        $(document).ready( function(){
                            WP_User_Frontend.insertImage('wpuf-insert-image_<?php echo esc_attr( $form_id ); ?>', '<?php echo esc_attr( $form_id ); ?>');
                        });
                    })(jQuery);
                </script>
                    <?php
                }

                $tinymce_settings = wpuf_filter_editor_toolbar( $field_settings );

                if ( $field_settings['rich'] === 'yes' ) {
                    $editor_settings = [
                        // 'textarea_rows' => $field_settings['rows'],
                        'quicktags'     => false,
                        'media_buttons' => false,
                        'editor_class'  => $req_class,
                        'textarea_name' => $field_settings['name'],
                    ];

                    if ( ! empty( $tinymce_settings ) ) {
                        $editor_settings['tinymce'] = $tinymce_settings;
                    }

                    $editor_settings = apply_filters( 'wpuf_textarea_editor_args', $editor_settings );
                    wp_editor( $value, $textarea_id, $editor_settings );
                } elseif ( $field_settings['rich'] === 'teeny' ) {
                    $editor_settings = [
                        'textarea_rows' => $field_settings['rows'],
                        'quicktags'     => false,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'editor_class'  => $req_class,
                        'textarea_name' => $field_settings['name'],
                    ];

                    if ( ! empty( $tinymce_settings ) ) {
                        $editor_settings['tinymce'] = $tinymce_settings;
                    }

                    $editor_settings = apply_filters( 'wpuf_textarea_editor_args', $editor_settings );
                    wp_editor( $value, $textarea_id, $editor_settings );
                } else {
                    ?>
            <textarea
                class="textareafield <?php echo ' wpuf_' . esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                id="<?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                data-type="textarea"
                placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                rows="<?php echo esc_attr( $field_settings['rows'] ); ?>"
                cols="<?php echo esc_attr( $field_settings['cols'] ); ?>"
            ><?php echo esc_textarea( $value ); ?></textarea>
            <span class="wpuf-wordlimit-message wpuf-help"></span>

                    <?php
                }
                ?>

        <?php
        $this->help_text( $field_settings );

        if ( isset( $field_settings['content_restriction'] ) && $field_settings['content_restriction'] ) {
            $this->check_content_restriction_func(
                $field_settings['content_restriction'],
                $field_settings['rich'],
                $field_settings['name'] . '_' . $form_id,
                $field_settings['restriction_type'],
                $field_settings['restriction_to']
            );
        }
        ?>
        </li>
        <?php
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings( false, [ 'dynamic' ] );
        $default_text_options = $this->get_default_textarea_option_settings();

        $settings = [
            [
                'name'          => 'insert_image',
                'title'         => __( 'Enable Image Insertion', 'wp-user-frontend' ),
                'type'          => 'checkbox',
                'options'       => [ 'yes' => __( 'Enable image upload in post area', 'wp-user-frontend' ) ],
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 14,
            ],
        ];

        return array_merge( $default_options, $default_text_options, $settings );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'input_type'       => 'textarea',
            'is_meta'          => 'no',
            'name'             => 'post_content',
            'rows'             => 5,
            'cols'             => 25,
            'rich'             => 'yes',
            'id'               => 0,
            'is_new'           => true,
            'restriction_type' => 'character',
            'restriction_to'   => 'max',
        ];

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
        check_ajax_referer( 'wpuf_form_add' );

        $field = isset( $_POST[ $field['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field['name'] ] ) ) : '';

        return trim( $field );
    }
}
