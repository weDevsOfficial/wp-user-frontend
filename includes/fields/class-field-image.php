<?php

/**
 * Image Field Class
 */
class WPUF_Form_Field_Image extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Image Upload', 'wp-user-frontend' );
        $this->input_type = 'image_upload';
        $this->icon       = 'file-image-o';
    }

    /**
     * Render the Image field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null) {

        $has_images         = false;

        if ( isset($post_id) &&  $post_id != '0' ) {

            if ( $this->is_meta( $field_settings ) ) {

                $images = $this->get_meta( $post_id, $field_settings['name'], $type, false );

                if ( $images ) {

                    if( is_serialized( $images[0] ) ) {
                        $images = maybe_unserialize( $images[0] );
                    }

                    if ( is_array( $images[0] ) ) {
                        $images = $images[0];
                    }
                }
                $has_images         = true;
            }

        }

        $unique_id = sprintf( '%s-%d', $field_settings['name'], $form_id );


        $this->field_print_label($field_settings, $form_id );

    ?>

            <div class="wpuf-fields">
                <div id="wpuf-<?php echo $unique_id; ?>-upload-container">
                    <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="<?php echo $field_settings['required']; ?>">
                        <a id="wpuf-<?php echo $unique_id; ?>-pickfiles" data-form_id="<?php echo $form_id; ?>" class="button file-selector <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>" href="#"><?php echo $field_settings['button_label']; ?></a>

                        <ul class="wpuf-attachment-list thumbnails">

                            <?php
                                    if ( $has_images ) {
                                        foreach ($images as $attach_id) {
                                            echo WPUF_Upload::attach_html( $attach_id, $field_settings['name'] );
                                        }
                                    }
                            ?>
                        </ul>
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


        <?php $this->after_field_print_label();

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
                'title'         => __( 'Max. file size', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wp-user-frontend' ),
            ),

            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wp-user-frontend' ),
            ),
            array(
                'name'          => 'button_label',
                'title'         => __( 'Button Label', 'wp-user-frontend' ),
                'type'          => 'text',
                'default'       => __( 'Select Image', 'wp-user-frontend' ),
                'section'       => 'basic',
                'priority'      => 22,
                'help_text'     => __( 'Enter a label for the Select button', 'wp-user-frontend' ),
            )
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
            'input_type'        => 'image_upload',
            'max_size'          => '1024',
            'count'             => '1',
            'button_label'      => __( 'Select Image', 'wp-user-frontend' ),
            'is_meta'           => 'yes',
            'max_size'          => '1024',
            'count'             => '1',
            'id'                => 0,
            'is_new'            => true,
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
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
