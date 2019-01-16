<?php

/**
 * Section Break Field Class
 */
class WPUF_Form_Field_SectionBreak extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Section Break', 'wp-user-frontend' );
        $this->input_type = 'section_break';
        $this->icon       = 'columns';
    }

    /**
     * Render the Section Break field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null) {

        $field_settings['description'] = isset( $field_settings['description'] ) ? $field_settings['description'] : '';
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <div class="wpuf-section-wrap wpuf-fields <?php echo 'section_' . $form_id; ?><?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>">
                <h2 class="wpuf-section-title"><?php echo $field_settings['label']; ?></h2>
                <div class="wpuf-section-details"><?php echo $field_settings['description']; ?></div>
            </div>

        </li>
        <?php
    }

    /**
     * It's a full width block
     *
     * @return boolean
     */
    public function is_full_width() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $options = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Title', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Title of the section', 'wp-user-frontend' ),
            ),
            array(
                'name'      => 'description',
                'title'     => __( 'Description', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 12,
                'help_text' => __( 'Some details text about the section', 'wp-user-frontend' ),
            ),
        );

        return $options;
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $props = array(
            'input_type'  => 'section_break',
            'template'    => $this->get_type(),
            'label'       => $this->get_name(),
            'description' => __( 'Some description about this section', 'wp-user-frontend'  ),
            'id'          => 0,
            'is_new'      => true,
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
            'wpuf_cond'         => null
        );

        return $props;
    }
}
