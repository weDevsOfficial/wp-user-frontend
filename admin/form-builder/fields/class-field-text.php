<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Text extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Text',  'wp-user-frontend' );
        $this->input_type = 'text_field';
        $this->icon       = 'text-width';
    }

    /**
     * Render the text field
     *
     * @param array $attr
     * @param int $post_id
     * @param string $type
     * @param null $form_id
     *
     */
    public function render( $attr, $post_id, $type = 'post', $form_id = null ) {
        // checking for user profile username
        $username = false;
        $taxonomy = false;
        if ( $post_id ) {

            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type );
            } else {

                // applicable for post tags
                if ( $type == 'post' && $attr['name'] == 'tags' ) {
                    $post_tags = wp_get_post_tags( $post_id );
                    $tagsarray = array();
                    foreach ($post_tags as $tag) {
                        $tagsarray[] = $tag->name;
                    }

                    $value = implode( ', ', $tagsarray );
                    $taxonomy = true;
                } elseif ( $type == 'post' ) {
                    $value = get_post_field( $attr['name'], $post_id );
                } elseif ( $type == 'user' ) {
                    $name = $attr['name'];
                    $value = get_user_by( 'id', $post_id )->$name;
                    if ( $attr['name'] == 'user_login' ) {
                        $username = true;
                    }
                }
            }
        } else {
            $value = $attr['default'];

            if ( $type == 'post' && $attr['name'] == 'tags' ) {
                $taxonomy = true;
            }
        }

        ?>

        <div class="wpuf-fields">
            <input class="textfield<?php echo $this->required_class( $attr );  echo ' wpuf_'.$attr['name'].'_'.$form_id; ?>" id="<?php echo $attr['name'].'_'.$form_id; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $attr['size'] ) ?>" <?php echo $username ? 'disabled' : ''; ?> />
            <span class="wpuf-wordlimit-message wpuf-help"></span>
            <?php $this->help_text( $attr ); ?>

            <?php if ( $taxonomy ) { ?>
                <script type="text/javascript">
                    ;(function($) {
                        $(document).ready( function(){
                            $('li.tags input[name=tags]').suggest( wpuf_frontend.ajaxurl + '?action=wpuf-ajax-tag-search&tax=post_tag', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
                        });
                    })(jQuery);
                </script>
            <?php } ?>
        </div>

        <?php
        if ( isset( $attr['word_restriction'] ) && $attr['word_restriction'] ) {
            $this->check_word_restriction_func( $attr['word_restriction'], 'no', $attr['name'] . '_' . $form_id );
        }
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings();
        $default_text_options = $this->get_default_text_option_settings( true );
        $check_duplicate      = array(
            array(
                'name'          => 'duplicate',
                'title'         => 'No Duplicates',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'no'   => __( 'Unique Values Only',  'wp-user-frontend' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Select this option to limit user input to unique values only. This will require that a value entered in a field does not currently exist in the entry database for that field.',  'wp-user-frontend' ),
            )
        );
        return array_merge( $default_options, $default_text_options, $check_duplicate );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'input_type'       => 'text',
            'word_restriction' => '',
            'show_in_post'     => 'yes',
            'width'            => ''
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
       return sanitize_text_field( trim( $_POST[$field['name']] ) );
    }
}
