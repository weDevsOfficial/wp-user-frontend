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
