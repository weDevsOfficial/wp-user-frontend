<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Image extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Image Upload', 'wp-user-frontend' );
        $this->input_type = 'image_upload';
        $this->icon       = 'file-image-o';
    }

    /**
     * Prints a image upload field
     *
     * @param array $attr
     * @param int|null $post_id
     */
    function render( $attr, $post_id, $type = '', $form_id = null ) {

        $has_featured_image = false;
        $has_images         = false;
        $has_avatar         = false;
        $unique_id          = sprintf( '%s-%d', $attr['name'], $form_id );

        if ( $post_id ) {
            if ( $this->is_meta( $attr ) ) {
                $images = $this->get_meta( $post_id, $attr['name'], $type, false );
                $has_images = true;
            } else {

                if ( $type == 'post' ) {
                    // it's a featured image then
                    $thumb_id = get_post_thumbnail_id( $post_id );

                    if ( $thumb_id ) {
                        $has_featured_image = true;
                        $featured_image = WPUF_Upload::attach_html( $thumb_id, 'featured_image' );
                    }
                } else {
                    // it must be a user avatar
                    $has_avatar = true;
                    $featured_image = get_avatar( $post_id );
                }
            }
        }
        $button_label = empty( $attr['button_label'] ) ? __( 'Select Image', 'wp-user-frontend' ) : $attr['button_label'];
        ?>

        <div class="wpuf-fields">
            <div id="wpuf-<?php echo $unique_id; ?>-upload-container">
                <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="<?php echo $attr['required']; ?>">
                    <a id="wpuf-<?php echo $unique_id; ?>-pickfiles" data-form_id="<?php echo $form_id; ?>" class="button file-selector <?php echo ' wpuf_' . $attr['name'] . '_' . $form_id; ?>" href="#"><?php echo $button_label ?></a>

                    <ul class="wpuf-attachment-list thumbnails">
                        <?php
                        if ( $has_featured_image ) {
                            echo $featured_image;
                        }

                        if ( $has_avatar ) {
                            $avatar = get_user_meta( $post_id, 'user_avatar', true );
                            if ( $avatar ) {
                                echo '<li>'.$featured_image;
                                printf( '<br><a href="#" data-confirm="%s" class="btn btn-danger btn-small wpuf-button button wpuf-delete-avatar">%s</a>', __( 'Are you sure?', 'wp-user-frontend' ), __( 'Delete', 'wp-user-frontend' ) );
                                echo '</li>';
                            }
                        }

                        if ( $has_images ) {
                            foreach ($images as $attach_id) {
                                echo WPUF_Upload::attach_html( $attach_id, $attr['name'] );
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div><!-- .container -->

            <?php $this->help_text( $attr ); ?>

        </div> <!-- .wpuf-fields -->

        <script type="text/javascript">
            ;(function($) {
                $(document).ready( function(){
                    var uploader = new WPUF_Uploader('wpuf-<?php echo $unique_id; ?>-pickfiles', 'wpuf-<?php echo $unique_id; ?>-upload-container', <?php echo $attr['count']; ?>, '<?php echo $attr['name']; ?>', 'jpg,jpeg,gif,png,bmp', <?php echo $attr['max_size'] ?>);
                    wpuf_plupload_items.push(uploader);
                });
            })(jQuery);
        </script>
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
            'button_label'      => __( 'Select Image', 'wp-user-frontend' ),
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
