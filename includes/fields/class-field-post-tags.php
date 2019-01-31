<?php
//Post Tags Class
class WPUF_Form_Field_Post_Tags extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Tags', 'wp-user-frontend' );
        $this->input_type = 'post_tags';
        $this->icon       = 'text-width';
    }

    /**
     * Render the Post Tags field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {

        if ( isset( $post_id ) ) {
            $post_tags = wp_get_post_tags( $post_id );
            $tagsarray = array();
            foreach ($post_tags as $tag) {
                $tagsarray[] = $tag->name;
            }
            $value = implode( ', ', $tagsarray );
        } else {
            $value = $field_settings['default'];
        }

    ?>


        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <input
                    class="textfield <?php echo 'wpuf_' . $field_settings['name'] . '_' . $form_id; ?>"
                    id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                    type="text"
                    data-required="<?php echo $field_settings['required'] ?>"
                    data-type="text" name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ) ?>"
                    size="<?php echo esc_attr( $field_settings['size'] ) ?>"
                />

                <span class="wpuf-wordlimit-message wpuf-help"></span>
                <?php  $this->help_text( $field_settings ); ?>
            </div>

              <script type="text/javascript">
                ;(function($) {
                    $(document).ready( function(){
                        $('li.tags input[name=tags]').suggest( wpuf_frontend.ajaxurl + '?action=wpuf-ajax-tag-search&tax=post_tag', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
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
        $default_options      = $this->get_default_option_settings(false,array('dynamic'));
        $settings = $this->get_default_text_option_settings();
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
            'input_type'        => 'text',
            'is_meta'           => 'no',
            'width'             => 'large',
            'size'              => 40,
            'id'                => 0,
            'is_new'            => true,
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
       return sanitize_text_field(trim($_POST[$field['name']]));
    }

}
