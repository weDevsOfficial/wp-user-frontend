<?php
// DESCRIPTION: Registers the wpuf/post-form Gutenberg block for wp-user-frontend.
// Handles asset registration, editor data localisation, and the server-side render callback.

namespace WeDevs\Wpuf\Blocks;

/**
 * Post Form Gutenberg block
 *
 * @since WPUF_SINCE
 */
class PostForm {

    /**
     * Initialize the block
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
        add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
    }

    /**
     * Register the block category for WPUF blocks (no-op if already registered)
     *
     * @since WPUF_SINCE
     *
     * @param array $categories Existing block categories
     *
     * @return array
     */
    public function register_block_category( $categories ) {
        foreach ( $categories as $category ) {
            if ( 'wpuf' === $category['slug'] ) {
                return $categories;
            }
        }

        return array_merge( $categories, [
            [
                'slug'  => 'wpuf',
                'title' => __( 'User Frontend', 'wp-user-frontend' ),
            ],
        ] );
    }

    /**
     * Register the Gutenberg block and localise editor data
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_block() {
        $asset_file = WPUF_ROOT . '/assets/js/blocks/post-form.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = require $asset_file;

        wp_register_script(
            'wpuf-post-form-editor',
            WPUF_ROOT_URI . '/assets/js/blocks/post-form.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            'wpuf-post-form-editor-style',
            WPUF_ROOT_URI . '/assets/js/blocks/post-form.css',
            [ 'wpuf-frontend-forms', 'wpuf-layout1' ],
            $asset['version']
        );

        wp_localize_script(
            'wpuf-post-form-editor',
            'wpufPostForm',
            $this->get_editor_data()
        );

        $block_json_path = WPUF_ROOT . '/src/js/blocks/post-form';

        register_block_type( $block_json_path, [
            'render_callback' => [ $this, 'render' ],
        ] );
    }

    /**
     * Get data to pass to the block editor via wp_localize_script
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_editor_data() {
        $post_forms = get_posts( [
            'post_type'   => 'wpuf_forms',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ] );

        $forms_list = [];

        foreach ( $post_forms as $form ) {
            $settings     = get_post_meta( $form->ID, 'wpuf_form_settings', true );
            $is_multistep = ! empty( $settings['enable_multistep'] ) && in_array( $settings['enable_multistep'], [ 'on', 'yes' ], true );

            $forms_list[] = [
                'id'           => $form->ID,
                'title'        => $form->post_title,
                'is_multistep' => $is_multistep,
            ];
        }

        return [
            'forms' => $forms_list,
        ];
    }

    /**
     * Sanitize a border object from BorderBoxControl
     *
     * Handles both flat (linked) and per-side (split) formats.
     * Returns a normalized array with top/right/bottom/left keys.
     *
     * @since WPUF_SINCE
     *
     * @param array|mixed $border Raw border attribute value
     *
     * @return array Normalized border array with top/right/bottom/left keys
     */
    private function sanitize_border( $border ) {
        if ( ! is_array( $border ) ) {
            return [
                'top'    => [ 'color' => '', 'style' => 'none', 'width' => '0px' ],
                'right'  => [ 'color' => '', 'style' => 'none', 'width' => '0px' ],
                'bottom' => [ 'color' => '', 'style' => 'none', 'width' => '0px' ],
                'left'   => [ 'color' => '', 'style' => 'none', 'width' => '0px' ],
            ];
        }

        $sides          = [ 'top', 'right', 'bottom', 'left' ];
        $allowed_styles = [ 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'none' ];

        // Check if this is a flat (linked) value or per-side (split) value
        $is_split = isset( $border['top'] ) || isset( $border['right'] ) || isset( $border['bottom'] ) || isset( $border['left'] );

        if ( ! $is_split ) {
            // Flat format — apply same values to all sides
            $sanitized_side = [
                'color' => sanitize_hex_color( $border['color'] ?? '' ) ?? '',
                'style' => in_array( $border['style'] ?? 'solid', $allowed_styles, true ) ? ( $border['style'] ?? 'solid' ) : 'solid',
                'width' => $this->sanitize_css_length( $border['width'] ?? '0px' ),
            ];

            return array_fill_keys( $sides, $sanitized_side );
        }

        // Per-side format
        $result = [];
        foreach ( $sides as $side ) {
            $side_val        = $border[ $side ] ?? [];
            $result[ $side ] = [
                'color' => sanitize_hex_color( $side_val['color'] ?? '' ) ?? '',
                'style' => in_array( $side_val['style'] ?? 'solid', $allowed_styles, true ) ? ( $side_val['style'] ?? 'solid' ) : 'solid',
                'width' => $this->sanitize_css_length( $side_val['width'] ?? '0px' ),
            ];
        }

        return $result;
    }

    /**
     * Sanitize a CSS length value (e.g. '2px', '0.5em')
     *
     * @since WPUF_SINCE
     *
     * @param string $value CSS length value
     *
     * @return string Sanitized CSS length or '0px'
     */
    private function sanitize_css_length( $value ) {
        $value = trim( (string) $value );

        if ( preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $value ) ) {
            return $value;
        }

        return '0px';
    }

    /**
     * Sanitize a CSS font-family value
     *
     * Strips characters that are not valid in CSS font-family declarations
     * to prevent CSS injection while allowing any valid font-family value.
     *
     * @since WPUF_SINCE
     *
     * @param string $value Raw font-family value
     *
     * @return string Sanitized font-family value
     */
    private function sanitize_font_family( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        // Allow only alphanumeric, spaces, commas, quotes, hyphens, periods
        return preg_replace( '/[^a-zA-Z0-9\s,\'".\-]/', '', $value );
    }

    /**
     * Sanitize a CSS font-style value
     *
     * @since WPUF_SINCE
     *
     * @param string $value Raw font-style value
     *
     * @return string Sanitized font-style value or empty string
     */
    private function sanitize_font_style( $value ) {
        $allowed = [ 'normal', 'italic', 'oblique' ];

        return in_array( (string) $value, $allowed, true ) ? $value : '';
    }

    /**
     * Sanitize a CSS font-weight value
     *
     * @since WPUF_SINCE
     *
     * @param string $value Raw font-weight value
     *
     * @return string Sanitized font-weight value or empty string
     */
    private function sanitize_font_weight( $value ) {
        $value   = (string) $value;
        $allowed = [ 'normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900' ];

        return in_array( $value, $allowed, true ) ? $value : '';
    }

    /**
     * Server-side render callback for the block
     *
     * @since WPUF_SINCE
     *
     * @param array $attributes Block attributes
     *
     * @return string
     */
    public function render( $attributes ) {
        if ( ! empty( $attributes['formId'] ) ) {
            $form_settings_dbg = get_post_meta( (int) $attributes['formId'], 'wpuf_form_settings', true );
        }

        $defaults = [
            'blockId'                     => '',
            'formId'                      => 0,
            'activeTemplate'              => 'default',
            'labelPosition'               => 'above',
            'labelFontSize'               => '14px',
            'labelColor'                  => '',
            'labelFontWeight'             => 'normal',
            'labelFontFamily'             => '',
            'labelFontStyle'              => '',
            'helpTextColor'               => '',
            'helpTextFontFamily'          => '',
            'helpTextFontSize'            => '12px',
            'helpTextFontStyle'           => '',
            'helpTextFontWeight'          => '',
            'fieldBorder'                 => [
                'color' => '#dddddd',
                'style' => 'solid',
                'width' => '1px',
            ],
            'fieldBorderRadius'           => '4px',
            'fieldPaddingV'               => '8px',
            'fieldPaddingH'               => '12px',
            'fieldBackgroundColor'        => '',
            'fieldTextColor'              => '',
            'fieldPlaceholderColor'       => '',
            'fieldFontFamily'             => '',
            'fieldFontSize'               => '14px',
            'fieldFontStyle'              => '',
            'fieldFontWeight'             => '',
            'fieldMarginBottom'           => '16px',
            'fieldFocusBorderColor'       => '',
            'buttonBackgroundColor'       => '',
            'buttonTextColor'             => '',
            'buttonBorderColor'           => '',
            'buttonBorderWidth'           => '0px',
            'buttonBorderRadius'          => '4px',
            'buttonFontSize'              => '14px',
            'buttonFontFamily'            => '',
            'buttonFontStyle'             => '',
            'buttonFontWeight'            => '',
            'buttonPaddingV'              => '10px',
            'buttonPaddingH'              => '20px',
            'uploadButtonBackgroundColor' => '',
            'uploadButtonTextColor'       => '',
            'uploadButtonBorderColor'     => '',
            'uploadButtonBorderWidth'     => '0px',
            'uploadButtonBorderRadius'    => '4px',
            'uploadButtonFontSize'        => '14px',
            'uploadButtonPaddingV'        => '10px',
            'uploadButtonPaddingH'        => '20px',
            'uploadButtonFontFamily'      => '',
            'uploadButtonFontStyle'       => '',
            'uploadButtonFontWeight'      => '',
            'errorMessageColor'           => '',
            'successMessageColor'         => '',
            'msButtonBackgroundColor'     => '#1e1e1e',
            'msButtonTextColor'           => '#ffffff',
            'msButtonFontFamily'          => '',
            'msButtonFontStyle'           => '',
            'msButtonFontWeight'          => '',
            'msButtonBorderColor'         => '',
            'msButtonBorderWidth'         => '0px',
            'msButtonBorderRadius'        => '4px',
            'msButtonFontSize'            => '14px',
            'msButtonPaddingV'            => '10px',
            'msButtonPaddingH'            => '20px',
            'msActiveBgColor'             => '',
            'msActiveTextColor'           => '',
            'msInactiveBgColor'           => '',
        ];

        $attributes = wp_parse_args( $attributes, $defaults );

        $block_id = sanitize_html_class( $attributes['blockId'] );
        if ( empty( $block_id ) ) {
            $block_id = 'wpuf-pf-fallback';
        }

        $block_config = [
            'label_font_size'             => $this->sanitize_css_length( $attributes['labelFontSize'] ),
            'label_color'                 => sanitize_hex_color( $attributes['labelColor'] ) ?? '',
            'label_font_weight'           => sanitize_text_field( $attributes['labelFontWeight'] ),
            'label_font_family'           => $this->sanitize_font_family( $attributes['labelFontFamily'] ),
            'label_font_style'            => $this->sanitize_font_style( $attributes['labelFontStyle'] ),
            'help_text_color'             => sanitize_hex_color( $attributes['helpTextColor'] ) ?? '',
            'help_text_font_family'       => $this->sanitize_font_family( $attributes['helpTextFontFamily'] ),
            'help_text_font_size'         => $this->sanitize_css_length( $attributes['helpTextFontSize'] ),
            'help_text_font_style'        => $this->sanitize_font_style( $attributes['helpTextFontStyle'] ),
            'help_text_font_weight'       => $this->sanitize_font_weight( $attributes['helpTextFontWeight'] ),
            'field_border'                => $this->sanitize_border( $attributes['fieldBorder'] ),
            'field_border_radius'         => $this->sanitize_css_length( $attributes['fieldBorderRadius'] ),
            'field_padding_v'             => $this->sanitize_css_length( $attributes['fieldPaddingV'] ),
            'field_padding_h'             => $this->sanitize_css_length( $attributes['fieldPaddingH'] ),
            'field_background_color'      => sanitize_hex_color( $attributes['fieldBackgroundColor'] ) ?? '',
            'field_text_color'            => sanitize_hex_color( $attributes['fieldTextColor'] ) ?? '',
            'field_placeholder_color'     => sanitize_hex_color( $attributes['fieldPlaceholderColor'] ) ?? '',
            'field_font_family'           => $this->sanitize_font_family( $attributes['fieldFontFamily'] ),
            'field_font_size'             => $this->sanitize_css_length( $attributes['fieldFontSize'] ),
            'field_font_style'            => $this->sanitize_font_style( $attributes['fieldFontStyle'] ),
            'field_font_weight'           => $this->sanitize_font_weight( $attributes['fieldFontWeight'] ),
            'field_margin_bottom'         => $this->sanitize_css_length( $attributes['fieldMarginBottom'] ),
            'field_focus_border_color'    => sanitize_hex_color( $attributes['fieldFocusBorderColor'] ) ?? '',
            'button_background_color'     => sanitize_hex_color( $attributes['buttonBackgroundColor'] ) ?? '',
            'button_text_color'           => sanitize_hex_color( $attributes['buttonTextColor'] ) ?? '',
            'button_border_color'         => sanitize_hex_color( $attributes['buttonBorderColor'] ) ?? '',
            'button_border_width'         => $this->sanitize_css_length( $attributes['buttonBorderWidth'] ),
            'button_border_radius'        => $this->sanitize_css_length( $attributes['buttonBorderRadius'] ),
            'button_font_size'            => $this->sanitize_css_length( $attributes['buttonFontSize'] ),
            'button_font_family'          => $this->sanitize_font_family( $attributes['buttonFontFamily'] ),
            'button_font_style'           => $this->sanitize_font_style( $attributes['buttonFontStyle'] ),
            'button_font_weight'          => $this->sanitize_font_weight( $attributes['buttonFontWeight'] ),
            'button_padding_v'            => $this->sanitize_css_length( $attributes['buttonPaddingV'] ),
            'button_padding_h'            => $this->sanitize_css_length( $attributes['buttonPaddingH'] ),
            'upload_btn_background_color' => sanitize_hex_color( $attributes['uploadButtonBackgroundColor'] ) ?? '',
            'upload_btn_text_color'       => sanitize_hex_color( $attributes['uploadButtonTextColor'] ) ?? '',
            'upload_btn_border_color'     => sanitize_hex_color( $attributes['uploadButtonBorderColor'] ) ?? '',
            'upload_btn_border_width'     => $this->sanitize_css_length( $attributes['uploadButtonBorderWidth'] ),
            'upload_btn_border_radius'    => $this->sanitize_css_length( $attributes['uploadButtonBorderRadius'] ),
            'upload_btn_font_size'        => $this->sanitize_css_length( $attributes['uploadButtonFontSize'] ),
            'upload_btn_font_family'      => $this->sanitize_font_family( $attributes['uploadButtonFontFamily'] ),
            'upload_btn_font_style'       => $this->sanitize_font_style( $attributes['uploadButtonFontStyle'] ),
            'upload_btn_font_weight'      => $this->sanitize_font_weight( $attributes['uploadButtonFontWeight'] ),
            'upload_btn_padding_v'        => $this->sanitize_css_length( $attributes['uploadButtonPaddingV'] ),
            'upload_btn_padding_h'        => $this->sanitize_css_length( $attributes['uploadButtonPaddingH'] ),
            'error_message_color'         => sanitize_hex_color( $attributes['errorMessageColor'] ) ?? '',
            'success_message_color'       => sanitize_hex_color( $attributes['successMessageColor'] ) ?? '',
            'ms_btn_background_color'     => sanitize_hex_color( $attributes['msButtonBackgroundColor'] ) ?? '',
            'ms_btn_text_color'           => sanitize_hex_color( $attributes['msButtonTextColor'] ) ?? '',
            'ms_btn_font_family'          => $this->sanitize_font_family( $attributes['msButtonFontFamily'] ),
            'ms_btn_font_style'           => $this->sanitize_font_style( $attributes['msButtonFontStyle'] ),
            'ms_btn_font_weight'          => $this->sanitize_font_weight( $attributes['msButtonFontWeight'] ),
            'ms_btn_border_color'         => sanitize_hex_color( $attributes['msButtonBorderColor'] ) ?? '',
            'ms_btn_border_width'         => $this->sanitize_css_length( $attributes['msButtonBorderWidth'] ),
            'ms_btn_border_radius'        => $this->sanitize_css_length( $attributes['msButtonBorderRadius'] ),
            'ms_btn_font_size'            => $this->sanitize_css_length( $attributes['msButtonFontSize'] ),
            'ms_btn_padding_v'            => $this->sanitize_css_length( $attributes['msButtonPaddingV'] ),
            'ms_btn_padding_h'            => $this->sanitize_css_length( $attributes['msButtonPaddingH'] ),
            'ms_active_bg_color'          => sanitize_hex_color( $attributes['msActiveBgColor'] ) ?? '',
            'ms_active_text_color'        => sanitize_hex_color( $attributes['msActiveTextColor'] ) ?? '',
            'ms_inactive_bg_color'        => sanitize_hex_color( $attributes['msInactiveBgColor'] ) ?? '',
        ];

        /**
         * Filter the block configuration before rendering
         *
         * @since WPUF_SINCE
         *
         * @param array $block_config Block configuration array
         * @param array $attributes   Raw block attributes
         */
        $block_config = apply_filters( 'wpuf_post_form_block_config', $block_config, $attributes );

        // get_block_wrapper_attributes() outputs id="..." automatically.
        // We output our own id for CSS scoping, so strip it from the wrapper attrs.
        $wrapper_attributes = preg_replace(
            '/\s*id="[^"]*"/',
            '',
            get_block_wrapper_attributes( [ 'class' => 'wpuf-post-form-block' ] )
        );

        ob_start();

        $template_file = WPUF_ROOT . '/includes/templates/blocks/post-form.php';

        if ( file_exists( $template_file ) ) {
            $args = [
                'block_id'       => $block_id,
                'block_config'   => $block_config,
                'form_id'        => absint( $attributes['formId'] ),
                'label_position' => sanitize_key( $attributes['labelPosition'] ),
                'wrapper_attrs'  => $wrapper_attributes,
            ];

            extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
            include $template_file;
        }

        return ob_get_clean();
    }
}
