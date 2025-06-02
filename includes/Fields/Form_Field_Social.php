<?php declare(strict_types=1); 

namespace WeDevs\Wpuf\Fields;

/**
 * Base Social Field Class
 * 
 * This abstract class provides common functionality for all social media fields
 * like Twitter, Facebook, LinkedIn, Instagram, etc.
 * 
 * @since 4.1.0
 */
abstract class Form_Field_Social extends Form_Field_URL {

    /**
     * Social platform name (e.g., 'twitter', 'facebook')
     * @var string
     */
    protected $platform = '';

    /**
     * Social platform display name (e.g., 'Twitter', 'Facebook')
     * @var string
     */
    protected $platform_name = '';

    /**
     * SVG icon markup for the social platform
     * @var string
     */
    protected $icon_svg = '';

    /**
     * Base URL for the social platform (e.g., 'https://twitter.com/')
     * @var string
     */
    protected $base_url = '';

    /**
     * Username pattern regex for validation
     * @var string
     */
    protected $username_pattern = '';

    /**
     * URL pattern regex for validation
     * @var string
     */
    protected $url_pattern = '';

    /**
     * Maximum username length
     * @var int
     */
    protected $max_username_length = 15;

    /**
     * Example username for placeholder
     * @var string
     */
    protected $example_username = '';

    /**
     * Get the common social field options
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();
        $settings        = $this->get_default_text_option_settings( false ); // word_restriction = false

        $settings[] = [
            'name'      => 'show_icon',
            'title'     => sprintf( __( 'Show %s Icon', 'wp-user-frontend' ), $this->platform_name ),
            'type'      => 'radio',
            'options'   => [
                'yes'   => __( 'Yes', 'wp-user-frontend' ),
                'no'    => __( 'No', 'wp-user-frontend' ),
            ],
            'section'   => 'basic',
            'default'   => 'no',
            'inline'    => true,
            'priority'  => 30,
            'help_text' => sprintf( __( 'Show %s icon beside the field label', 'wp-user-frontend' ), $this->platform_name ),
        ];

        $settings[] = [
            'name'      => 'open_window',
            'title'     => __( 'Open in : ', 'wp-user-frontend' ),
            'type'      => 'radio',
            'options'   => [
                'same'   => __( 'Same Window', 'wp-user-frontend' ),
                'new'    => __( 'New Window', 'wp-user-frontend' ),
            ],
            'section'   => 'basic',
            'default'   => 'new',
            'inline'    => true,
            'priority'  => 32,
            'help_text' => __( 'Choose whether the link will open in new tab or same window', 'wp-user-frontend' ),
        ];

        $settings[] = [
            'name'      => 'username_validation',
            'title'     => __( 'Username Validation', 'wp-user-frontend' ),
            'type'      => 'radio',
            'options'   => [
                'yes'   => sprintf( __( 'Strict (%s username format)', 'wp-user-frontend' ), $this->platform_name ),
                'no'    => __( 'Allow full URLs', 'wp-user-frontend' ),
            ],
            'section'   => 'basic',
            'default'   => 'yes',
            'inline'    => true,
            'priority'  => 34,
            'help_text' => sprintf( __( 'Enforce %s username format or allow full URLs', 'wp-user-frontend' ), $this->platform_name ),
        ];

        return array_merge( $default_options, $settings );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'input_type'           => $this->platform,
            'is_meta'              => 'yes',
            'width'                => 'large',
            'show_icon'            => 'no',
            'open_window'          => 'new',
            'username_validation'  => 'yes',
            'size'                 => 40,
            'id'                   => 0,
            'is_new'               => true,
            'show_in_post'         => 'yes',
            'hide_field_label'     => 'no',
            'placeholder'          => $this->example_username,
        ];

        return array_merge( $defaults, $props );
    }

    /**
     * Render the social field
     *
     * @param array  $field_settings
     * @param int    $form_id
     * @param string $type
     * @param int    $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        if ( isset( $post_id ) && $post_id !== '0' ) {
            if ( $this->is_meta( $field_settings ) ) {
                $value = $this->get_meta( $post_id, $field_settings['name'], $type );
                // Convert full URL back to username for display in form
                $value = $this->extract_username_from_url( $value );
            }
        } else {
            $value = $field_settings['default'];
        }

        // Custom label rendering with icon support
        if ( ! empty( $field_settings['show_icon'] ) && $field_settings['show_icon'] === 'yes' ) {
            $this->field_print_label_with_icon( $field_settings, $form_id ); 
        } else {
            $this->field_print_label( $field_settings, $form_id );
        }
        ?>
            <div class="wpuf-fields">
                <input
                    id="<?php echo esc_attr( $field_settings['name'] . '_' . $form_id ); ?>"
                    type="text" 
                    pattern="<?php echo esc_attr( $this->username_pattern ); ?>"
                    class="textfield wpuf-<?php echo esc_attr( $this->platform ); ?>-field <?php echo esc_attr( ' wpuf_' . $field_settings['name'] . '_' . $form_id ); ?>"
                    data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                    data-type="<?php echo esc_attr( $this->input_type ); ?>"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ); ?>" 
                    size="<?php echo esc_attr( $field_settings['size'] ); ?>"
                    autocomplete="url"
                />
                
                <?php $this->help_text( $field_settings ); ?>
            </div>

        <?php
        $this->after_field_print_label();
    }

    /**
     * Print label with icon support
     */
    protected function field_print_label_with_icon( $field, $form_id = 0 ) {
        if ( is_admin() ) { ?>
            <tr <?php $this->print_list_attributes( $field ); ?>> <th><strong> 
                <?php echo wp_kses_post( $field['label'] . $this->required_mark( $field ) ); ?> 
                <?php if ( ! empty( $field['show_icon'] ) && $field['show_icon'] === 'yes' ) : ?>
                    <svg class="wpuf-twitter-svg" style="display: inline-block; vertical-align: middle; margin-left: 8px; width: 20px; height: 20px;" width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#16A34A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </strong></th> <td>
        <?php } else { ?>
            <li <?php $this->print_list_attributes( $field ); ?>>
            <div class="wpuf-label">
                <label for="<?php echo isset( $field['name'] ) ? esc_attr( $field['name'] ) . '_' . esc_attr( $form_id ) : 'cls'; ?>">
                    <?php echo wp_kses_post( $field['label'] . $this->required_mark( $field ) ); ?>
                    <?php if ( ! empty( $field['show_icon'] ) && $field['show_icon'] === 'yes' ) : ?>
                        <svg class="wpuf-twitter-svg" style="display: inline-block; vertical-align: middle; margin-left: 8px; width: 20px; height: 20px;" width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#16A34A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    <?php endif; ?>
                </label>
            </div>
        <?php
        }
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

        $value = isset( $_POST[ $field['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field['name'] ] ) ) : '';
        
        if ( empty( $value ) ) {
            return '';
        }

        // Convert username to full URL
        $social_url = $this->convert_to_social_url( $value );
        
        return esc_url( trim( $social_url ) );
    }

    /**
     * Convert username or partial URL to full social platform URL
     *
     * @param string $input
     * @return string
     */
    protected function convert_to_social_url( $input ) {
        $input = trim( $input );

        if ( empty( $input ) ) {
            return '';
        }

        // If it's already a full URL, validate and return
        if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
            return $this->validate_platform_url( $input ) ? $input : '';
        }

        // Remove @ if present
        $username = ltrim( $input, '@' );
        
        // Validate username format
        if ( ! $this->validate_username( $username ) ) {
            return $input; // Return original if invalid
        }

        return $this->base_url . $username;
    }

    /**
     * Extract username from platform URL
     *
     * @param string $url
     * @return string
     */
    protected function extract_username_from_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }

        // If it's not a URL, return as is
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return $url;
        }

        // Extract username from platform URL using the URL pattern
        if ( preg_match( $this->url_pattern, $url, $matches ) ) {
            return '@' . $matches[1];
        }

        return $url;
    }

    /**
     * Validate username format
     *
     * @param string $username
     * @return bool
     */
    protected function validate_username( $username ) {
        return preg_match( $this->username_pattern, $username ) && 
               strlen( $username ) <= $this->max_username_length;
    }

    /**
     * Validate platform URL
     *
     * @param string $url
     * @return bool
     */
    protected function validate_platform_url( $url ) {
        return preg_match( $this->url_pattern, $url );
    }

    /**
     * Render field data
     *
     * @since 3.3.1
     *
     * @param mixed $data
     * @param array $field
     *
     * @return string
     */
    public function render_field_data( $data, $field ) {
        $data       = implode( ',', $data );
        $hide_label = isset( $field['hide_field_label'] )
            ? wpuf_validate_boolean( $field['hide_field_label'] )
            : false;

        if ( empty( $data ) ) {
            return '';
        }

        $container_classnames = [ 'wpuf-field-data', 'wpuf-field-data-' . $this->input_type ];

        // Extract username for display
        $username = $this->extract_username_from_url( $data );
        $display_text = ! empty( $username ) ? $username : $data;

        ob_start();
        ?>
            <li class="<?php echo esc_attr( implode( ' ', $container_classnames ) ); ?>">
                <?php if ( ! $hide_label ) : ?>
                    <label><?php echo esc_html( $field['label'] ); ?>:</label>
                <?php endif; ?>
                <a href="<?php echo esc_url_raw( $data ); ?>"
                    <?php echo ! empty( $field['open_window'] ) && $field['open_window'] === 'new' ? 'target="_blank" rel="noreferrer noopener"' : ''; ?>
                    title="<?php echo esc_attr( $display_text ); ?>"
                    class="wpuf-<?php echo esc_attr( $this->platform ); ?>-link">
                    <?php echo esc_html( $display_text ); ?>
                    <?php if ( ! empty( $field['show_icon'] ) && $field['show_icon'] === 'yes' ) : ?>
                        <svg class="wpuf-twitter-svg" style="display: inline-block; vertical-align: middle; margin-left: 8px; width: 20px; height: 20px;" width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#16A34A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    <?php endif; ?>
                </a>
            </li>
        <?php
        return ob_get_clean();
    }
}
