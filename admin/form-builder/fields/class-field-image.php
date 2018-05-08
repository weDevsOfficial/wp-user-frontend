<?php

/**
 * Text Field Class
 */
class WeForms_Form_Field_Image extends WeForms_Field_Contract {

    function __construct() {
        $this->name       = __( 'Image Upload', 'weforms' );
        $this->input_type = 'image_upload';
        $this->icon       = 'file-image-o';
    }

    /**
     * Render the text field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id ) {
        $unique_id = sprintf( '%s-%d', $field_settings['name'], $form_id );
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <div id="wpuf-<?php echo $unique_id; ?>-upload-container">
                    <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="<?php echo $field_settings['required']; ?>">
                        <a id="wpuf-<?php echo $unique_id; ?>-pickfiles" data-form_id="<?php echo $form_id; ?>" class="button file-selector <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>" href="#"><?php _e( 'Select Image', 'weforms' ); ?></a>

                        <ul class="wpuf-attachment-list thumbnails"></ul>
                    </div>
                </div><!-- .container -->

                <?php $this->help_text( $field_settings ); ?>

            </div> <!-- .wpuf-fields -->

            <script type="text/javascript">
                ;(function($) {
                    $(document).ready( function(){
                        var uploader = new WPUF_Uploader('wpuf-<?php echo $unique_id; ?>-pickfiles', 'wpuf-<?php echo $unique_id; ?>-upload-container', <?php echo $field_settings['count']; ?>, '<?php echo $field_settings['name']; ?>', 'jpg,jpeg,gif,png,bmp', <?php echo $field_settings['max_size'] ?>);
                        wpuf_plupload_items.push(uploader);
                    });
                })(jQuery);
            </script>

        </li>
        <?php
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings(true, array('dynamic', 'width') ); // exclude dynamic

        $settings = array(
            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'weforms' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'weforms' ),
            ),

            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'weforms' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'weforms' ),
            ),
        );

        return array_merge( $default_options, $settings );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'max_size' => '1024',
            'count'    => '1',
        );

        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return @return mixed
     */
    public function prepare_entry( $field ) {
       return isset( $_POST['wpuf_files'][$field['name']] ) ? $_POST['wpuf_files'][$field['name']] : array();
    }
}