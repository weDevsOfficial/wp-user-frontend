<?php
/**
 * Plugin options and the classic settings field renderers
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @return string HTML content, if not displaying
 */
function wpuf_get_pages( $post_type = 'page' ) {
    $array = [ '' => __( '-- Select --', 'wp-user-frontend' ) ];
    $pages = get_posts(
        [
            'post_type'              => $post_type,
            'numberposts'            => - 1,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]
    );

    if ( $pages ) {
        foreach ( $pages as $page ) {
            $array[ $page->ID ] = esc_attr( $page->post_title );
        }
    }

    return $array;
}

/**
 * Get the value of a settings field
 *
 * @param string $option  settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function wpuf_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[ $option ] ) ) {
        return $options[ $option ];
    }

    return $default;
}

/**
 * Renders an HTML Dropdown
 *
 * @param array $args
 *
 * @return string
 */
function wpuf_select( $args = [] ) {
    $defaults = [
        'options'          => [],
        'name'             => null,
        'class'            => '',
        'id'               => '',
        'selected'         => [],
        'chosen'           => false,
        'placeholder'      => null,
        'multiple'         => false,
        'show_option_all'  => __( 'All', 'wp-user-frontend' ),
        'show_option_none' => __( 'None', 'wp-user-frontend' ),
        'data'             => [],
        'readonly'         => false,
        'disabled'         => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    $data_elements = '';
    $selected      = '';

    foreach ( $args['data'] as $key => $value ) {
        $data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }

    if ( $args['multiple'] ) {
        $multiple = ' MULTIPLE';
    } else {
        $multiple = '';
    }

    if ( $args['chosen'] ) {
        $args['class'] .= ' wpuf-select-chosen';

        if ( is_rtl() ) {
            $args['class'] .= ' chosen-rtl';
        }
    }

    if ( $args['placeholder'] ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }

    if ( isset( $args['readonly'] ) && $args['readonly'] ) {
        $readonly = ' readonly="readonly"';
    } else {
        $readonly = '';
    }

    if ( isset( $args['disabled'] ) && $args['disabled'] ) {
        $disabled = ' disabled="disabled"';
    } else {
        $disabled = '';
    }

    $class  = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
    $output = '<select' . $disabled . $readonly . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( str_replace( '-', '_', $args['id'] ) ) . '" class="wpuf-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"' . $data_elements . '>';

    if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
        $selected = '';
    }

    if ( $args['show_option_all'] ) {
        if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
            $selected = selected( true, in_array( 0, $args['selected'], true ), false );
        } else {
            $selected = selected( $args['selected'], 0, false );
        }
        $output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
    }

    if ( ! empty( $args['options'] ) ) {
        if ( $args['show_option_none'] ) {
            if ( $args['multiple'] ) {
                $selected = selected( true, in_array( -1, $args['selected'], true ), false );
            } elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
                $selected = selected( $args['selected'], -1, false );
            }
            $output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
        }

        foreach ( $args['options'] as $key => $option ) {
            if ( $args['multiple'] && is_array( $args['selected'] ) ) {
                $selected = selected( true, in_array( (string) $key, $args['selected'], true ), false );
            } elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
                $selected = selected( $args['selected'], $key, false );
            }

            $output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
        }
    }

    $output .= '</select>';

    return $output;
}

/**
 * Renders a Text field in settings field
 *
 * @param array $args Arguments for the text field
 *
 * @return string Text field
 */
function wpuf_text( $args = [] ) {
    $defaults = [
        'id'           => '',
        'name'         => isset( $name ) ? $name : 'text',
        'value'        => isset( $value ) ? $value : null,
        'label'        => isset( $label ) ? $label : null,
        'desc'         => isset( $desc ) ? $desc : null,
        'placeholder'  => '',
        'class'        => 'regular-text',
        'disabled'     => false,
        'autocomplete' => '',
        'data'         => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    $class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
    $disabled = '';

    if ( $args['disabled'] ) {
        $disabled = ' disabled="disabled"';
    }

    $data = '';

    if ( ! empty( $args['data'] ) ) {
        foreach ( $args['data'] as $key => $value ) {
            $data .= 'data-' . $key . '="' . esc_attr( $value ) . '" ';
        }
    }

    $output = '<span id="wpuf-' . $args['name'] . '-wrap">';

    if ( ! empty( $args['label'] ) ) {
        $output .= '<label class="wpuf-label" for="' . $args['id'] . '">' . esc_html( $args['label'] ) . '</label>';
    }

    if ( ! empty( $args['desc'] ) ) {
        $output .= '<span class="wpuf-description">' . wp_kses_post( $args['desc'] ) . '</span>';
    }

    $output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

    $output .= '</span>';

    return $output;
}

/**
 * Descriptive text callback
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function wpuf_descriptive_text( $args ) {
    echo wp_kses_post( $args['desc'] );
}

/**
 * Update the value of a settings field
 *
 * @param string $option  settings field name
 * @param string $section the section name this field belongs to
 * @param string $value   the value to be set
 *
 * @return mixed
 */
function wpuf_update_option( $option, $section, $value ) {
    // Forwards to the settings store (task 2.4d); same write, then
    // wpuf_settings_saved.
    \WeDevs\Wpuf\Platform\Stores\Stores::settings()->set_value( $section, $option, $value );
}

/**
 * Displays a multi select dropdown for a settings field
 *
 * @param array $args settings field args
 */
function wpuf_settings_multiselect( $args ) {
    $settings = wpuf()->platform()->get( WeDevs_Settings_API::class );
    $value    = $settings->get_option( $args['id'], $args['section'], $args['std'] );
    $value    = is_array( $value ) ? (array) $value : [];
    $size     = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
    $html     = sprintf( '<select multiple="multiple" class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

    foreach ( $args['options'] as $key => $label ) {
        $checked = in_array( $key, $value, true ) ? $key : '0';
        $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $checked, $key, false ), $label );
    }

    $html .= sprintf( '</select>' );
    $html .= $settings->get_field_description( $args );

    echo wp_kses(
        $html, [
            'p' => [],
            'select' => [
                'multiple' => [],
                'class'    => [],
                'name'     => [],
                'id'       => [],
            ],
            'option' => [
                'value' => [],
                'selected' => [],
            ],
        ]
    );
}

/**
 * Password preview field callback
 *
 * @param array $args Field arguments
 * @since WPUF_PRO_SINCE
 */
function wpuf_settings_password_preview( $args ) {
    $settings = wpuf()->platform()->get( WeDevs_Settings_API::class );
    $value    = $settings->get_option( $args['id'], $args['section'], $args['std'] );
    $disabled = ! empty( $args['is_pro_preview'] ) && $args['is_pro_preview'] ? 'disabled' : '';
    $size     = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

    // Create masked preview of the password
    $preview_value = '';
    if ( ! empty( $value ) ) {
        $length = strlen( $value );
        if ( $length >= 4 ) {
            $preview_value = substr( $value, 0, 2 ) . str_repeat( '*', $length - 4 ) . substr( $value, -2 );
        } else {
            $preview_value = str_repeat( '*', $length );
        }
    }

    $depends_on = ! empty( $args['depends_on'] ) ? $args['depends_on'] : '';
    $depends_on_value = ! empty( $args['depends_on_value'] ) ? $args['depends_on_value'] : '';

    // Handle array dependencies
    if ( is_array( $depends_on ) ) {
        $depends_on_json = esc_attr( json_encode( $depends_on ) );
        $depends_on_value = ''; // Not used for array format
    } else {
        $depends_on_json = esc_attr( $depends_on );
    }

    $html  = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" %5$s data-depends-on=\'%6$s\' data-depends-on-value="%7$s"/>', $size, $args['section'], $args['id'], $preview_value, $disabled, $depends_on_json, esc_attr( $depends_on_value ) );
    $html  .= $settings->get_field_description( $args );

    if ( ! empty( $args['is_pro_preview'] ) && $args['is_pro_preview'] ) {
        $html .= wpuf_get_pro_preview_html();
    }

    echo wp_kses(
        $html, array(
			'input' => array(
				'type' => array(),
				'class' => array(),
				'id' => array(),
				'name' => array(),
				'value' => array(),
				'readonly' => array(),
				'style' => array(),
				'disabled' => array(),
				'data-depends-on' => array(),
				'data-depends-on-value' => array(),
			),
			'p' => array( 'class' => array() ),
			'div' => array( 'class' => array() ),
			'a' => array(
				'href' => array(),
				'target' => array(),
				'class' => array(),
			),
			'span' => array( 'class' => array() ),
			'svg' => array(
				'width' => array(),
				'height' => array(),
				'viewBox' => array(),
				'fill' => array(),
				'xmlns' => array(),
			),
			'path' => array(
				'd' => array(),
				'fill' => array(),
			),
        )
    );
}

/**
 * Check if the option is on
 *
 * @since 4.0.11
 *
 * @param $option
 *
 * @return bool
 */
function wpuf_is_option_on( $option ) {
    return 'on' === $option || 'yes' === $option;
}

/**
 * Check if a checkbox or toggle is on
 *
 * @since 4.1.0
 *
 * @param string $value
 *
 * @return bool
 */
function wpuf_is_checkbox_or_toggle_on( $value ) {
    return 'on' === $value || 'yes' === $value || 'true' === $value || '1' === $value;
}

/**
 * Form style class for a form's "Use Theme CSS" setting: `wpuf-theme-style`
 * when it is on (the React builder stores the toggle as `true`, the classic
 * one as `on`; very old forms stored the class itself), `wpuf-style`
 * otherwise. The layout sheets skip `.wpuf-theme-style` forms.
 *
 * @since WPUF_SINCE
 *
 * @param mixed $value The stored `use_theme_css` value
 *
 * @return string
 */
function wpuf_form_style_class( $value ) {
    if ( true === $value || 'wpuf-theme-style' === $value || ( is_string( $value ) && wpuf_is_checkbox_or_toggle_on( $value ) ) ) {
        return 'wpuf-theme-style';
    }

    return 'wpuf-style';
}
