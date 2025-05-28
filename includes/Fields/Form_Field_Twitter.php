<?php declare(strict_types=1); 

namespace WeDevs\Wpuf\Fields;

/**
 * Twitter Field Class
 */
class Form_Field_Twitter extends Form_Field_URL {

    public function __construct() {
        $this->name       = __( 'Social Field – X (formerly Twitter)', 'wp-user-frontend' );
        $this->input_type = 'twitter_url';
        $this->icon       = 'twitter';
    }

    /**
     * Render the Twitter field
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
                $value = $this->extract_twitter_username( $value );
            }
        } else {
            $value = $field_settings['default'];
        }

        $this->field_print_label( $field_settings, $form_id ); ?>
            <div class="wpuf-fields">
                <?php if ( ! empty( $field_settings['show_icon'] ) && $field_settings['show_icon'] === 'yes' ) : ?>
                    <div class="wpuf-twitter-field-wrapper">
                        <span class="wpuf-twitter-icon">
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                        </span>
                <?php endif; ?>
                
                <input
                    id="<?php echo esc_attr( $field_settings['name'] . '_' . $form_id ); ?>"
                    type="text" 
                    pattern="^@?[a-zA-Z0-9_]{1,15}$"
                    class="textfield wpuf-twitter-field <?php echo esc_attr( ' wpuf_' . $field_settings['name'] . '_' . $form_id ); ?>"
                    data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                    data-type="twitter_url"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ); ?>" 
                    size="<?php echo esc_attr( $field_settings['size'] ); ?>"
                    autocomplete="url"
                />
                
                <?php if ( ! empty( $field_settings['show_icon'] ) && $field_settings['show_icon'] === 'yes' ) : ?>
                    </div>
                <?php endif; ?>
                
                <?php $this->help_text( $field_settings ); ?>
            </div>

        <?php
        $this->after_field_print_label();
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();
        $settings        = $this->get_default_text_option_settings( false ); // word_restriction = false

        $settings[] = [
            'name'      => 'show_icon',
            'title'     => __( 'Show Twitter Icon', 'wp-user-frontend' ),
            'type'      => 'radio',
            'options'   => [
                'yes'   => __( 'Yes', 'wp-user-frontend' ),
                'no'    => __( 'No', 'wp-user-frontend' ),
            ],
            'section'   => 'basic',
            'default'   => 'no',
            'inline'    => true,
            'priority'  => 30,
            'help_text' => __( 'Show Twitter/X icon before the input field', 'wp-user-frontend' ),
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
                'yes'   => __( 'Strict (Twitter username format)', 'wp-user-frontend' ),
                'no'    => __( 'Allow full URLs', 'wp-user-frontend' ),
            ],
            'section'   => 'basic',
            'default'   => 'yes',
            'inline'    => true,
            'priority'  => 34,
            'help_text' => __( 'Enforce Twitter username format (@username) or allow full URLs', 'wp-user-frontend' ),
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
            'input_type'           => 'twitter',
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
            'placeholder'          => __( '@username', 'wp-user-frontend' ),
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

        $value = isset( $_POST[ $field['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field['name'] ] ) ) : '';
        
        if ( empty( $value ) ) {
            return '';
        }

        // Convert username to full URL
        $twitter_url = $this->convert_to_twitter_url( $value );
        
        return esc_url( trim( $twitter_url ) );
    }

    /**
     * Convert username or partial URL to full Twitter URL
     *
     * @param string $input
     * @return string
     */
    private function convert_to_twitter_url( $input ) {
        $input = trim( $input );
        
        if ( empty( $input ) ) {
            return '';
        }

        // If it's already a full URL, return as is
        if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
            return $input;
        }

        // Remove @ if present
        $username = ltrim( $input, '@' );
        
        // Validate username format (1-15 characters, alphanumeric and underscore only)
        if ( ! preg_match( '/^[a-zA-Z0-9_]{1,15}$/', $username ) ) {
            return $input; // Return original if invalid
        }

        return 'https://twitter.com/' . $username;
    }

    /**
     * Extract Twitter username from full URL
     *
     * @param string $url
     * @return string
     */
    private function extract_twitter_username( $url ) {
        if ( empty( $url ) ) {
            return '';
        }

        // If it's not a URL, return as is
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return $url;
        }

        // Extract username from Twitter URL
        if ( preg_match( '/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
            return '@' . $matches[1];
        }

        return $url;
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
        $username = $this->extract_twitter_username( $data );
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
                    class="wpuf-twitter-link">
                    <?php if ( ! empty( $field['show_icon'] ) && $field['show_icon'] === 'yes' ) : ?>
                        <i class="fab fa-twitter" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?php echo esc_html( $display_text ); ?>
                </a>
            </li>
        <?php
        return ob_get_clean();
    }
}
