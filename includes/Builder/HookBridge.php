<?php
/**
 * Form builder hook bridge
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Builder;

/**
 * Fires the plain-PHP hooks the Vue form builder views fired, so extensions that
 * listen to them keep running on the React builder.
 *
 * - Settings rows: `wpuf_before|after_{post,registration}_form_settings_field[_{key}]`
 *   with develop's arguments and order, one pair per settings row.
 * - Tabs: `wpuf-form-builder-tabs-{type}`, `wpuf_form_builder_settings_tabs_{type}`,
 *   `wpuf_{post,profile}_form_tab`, `wpuf-form-builder-tab-contents-{type}`.
 *
 * These view hooks are retired as well: when an outside callback listens they
 * fire through `do_action_deprecated()` (HookDeprecations::BRIDGED names the
 * replacement) and the admin notice lists them; the output still renders.
 *
 * The Vue-only template hooks (`wpuf_builder_field_options`,
 * `wpuf_field_option_data_actions|_after`, `..._builder_stage_submit_area|_bottom_area`)
 * are retired: HookDeprecations fires them (deprecated, output discarded) and
 * the React builder has slots in their place.
 *
 * WPUF's own listeners (free and pro) are React parts now, so they are left out
 * while a hook fires. Output from other listeners is captured, sanitized with a
 * form-element allowlist and returned per slot for the builder to render; inputs
 * named `wpuf_settings[...]` inside a slot are saved by `merge_posted_settings()`.
 *
 * @since WPUF_SINCE
 */
class HookBridge {

    /**
     * Builder type: 'post' or 'profile'.
     *
     * @var string
     */
    private $form_type;

    /**
     * Stored form settings.
     *
     * @var array
     */
    private $form_settings;

    /**
     * Callbacks removed while a hook fires, to be added back.
     *
     * @var array
     */
    private $removed = [];

    /**
     * Whether any captured output had markup the builder cannot run.
     *
     * @var bool
     */
    private $has_unsupported = false;

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type     'post' or 'profile'
     * @param array  $form_settings Stored form settings
     */
    public function __construct( $form_type, $form_settings ) {
        $this->form_type     = 'profile' === $form_type ? 'profile' : 'post';
        $this->form_settings = is_array( $form_settings ) ? $form_settings : [];
    }

    /**
     * Fire every bridged hook in develop's order and collect the output per slot.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings_items Settings sections the builder shows (`settings_items`)
     *
     * @return array {
     *     @type array  $settings    Per settings key: `before` / `after` HTML
     *     @type array  $tabs        `nav`, `settings`, `form_tab`, `contents` HTML
     *     @type bool   $unsupported Whether some output needed scripts or Vue
     * }
     */
    public function collect( $settings_items ) {
        $type = $this->form_type;

        $tabs = [
            'nav'      => $this->capture( "wpuf-form-builder-tabs-{$type}" ),
            'settings' => $this->capture( "wpuf_form_builder_settings_tabs_{$type}" ),
        ];

        $settings = $this->collect_settings_rows( is_array( $settings_items ) ? $settings_items : [] );

        $tabs['form_tab'] = $this->capture( 'post' === $type ? 'wpuf_post_form_tab' : 'wpuf_profile_form_tab' );
        $tabs['contents'] = $this->capture( "wpuf-form-builder-tab-contents-{$type}" );

        return [
            'settings'    => array_filter( $settings ),
            'tabs'        => array_filter( $tabs ),
            'unsupported' => $this->has_unsupported,
        ];
    }

    /**
     * Fire the before/after hooks of every settings row, walking the sections as
     * develop's post-form-settings.php / profile-form-settings.php did.
     *
     * @param array $settings_items Settings sections
     *
     * @return array Per settings key: `before` / `after` HTML (empty rows left out)
     */
    private function collect_settings_rows( $settings_items ) {
        $rows = [];

        foreach ( $settings_items as $section ) {
            if ( ! is_array( $section ) ) {
                continue;
            }

            foreach ( $section as $settings_item ) {
                if ( ! is_array( $settings_item ) ) {
                    continue;
                }

                $groups = [];

                if ( ! empty( $settings_item['section'] ) && is_array( $settings_item['section'] ) ) {
                    foreach ( $settings_item['section'] as $sub_section ) {
                        $groups[] = isset( $sub_section['fields'] ) ? $sub_section['fields'] : [];
                        $groups[] = isset( $sub_section['pro_preview']['fields'] ) ? $sub_section['pro_preview']['fields'] : [];
                    }
                } else {
                    $fields = $settings_item;
                    unset( $fields['pro_preview'] );
                    $groups[] = $fields;
                    $groups[] = isset( $settings_item['pro_preview']['fields'] ) ? $settings_item['pro_preview']['fields'] : [];
                }

                foreach ( $groups as $fields ) {
                    foreach ( (array) $fields as $field_key => $field ) {
                        if ( is_array( $field ) ) {
                            $this->collect_settings_row( $rows, (string) $field_key, $field );
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Fire one settings row's hooks with develop's arguments and order.
     *
     * @param array  $rows      Collected rows (by reference)
     * @param string $field_key Settings key
     * @param array  $field     Settings field definition
     *
     * @return void
     */
    private function collect_settings_row( &$rows, $field_key, $field ) {
        // Rows are delivered under the key the builder renders; the hook name may
        // use develop's renamed key (default_{post_type}_cat).
        $row_key = $field_key;

        if ( 'post' === $this->form_type ) {
            $post_type = ! empty( $this->form_settings['post_type'] ) ? $this->form_settings['post_type'] : 'post';

            if ( 'default_category' === $field_key && 'post' !== $post_type ) {
                $field_key = 'default_' . $post_type . '_cat';
            }

            $value  = $this->post_row_value( $field_key, $field );
            $before = [
                [ 'wpuf_before_post_form_settings_field', [ $field, $value ] ],
                [ 'wpuf_before_post_form_settings_field_' . $field_key, [ $field, $value ] ],
            ];
            $after  = [
                [ 'wpuf_after_post_form_settings_field_' . $field_key, [ $field, $value, $this->form_settings ] ],
                [ 'wpuf_after_post_form_settings_field', [ $field, $value, $this->form_settings ] ],
            ];
        } else {
            $value  = $this->registration_row_value( $field_key, $field );
            $before = [
                [ 'wpuf_before_registration_form_settings_field_' . $field_key, [ $field, $value, $this->form_settings ] ],
                [ 'wpuf_before_registration_form_settings_field', [ $field, $value, $this->form_settings ] ],
            ];
            $after  = [
                [ 'wpuf_after_registration_form_settings_field', [ $field, $value, $this->form_settings ] ],
                [ 'wpuf_after_registration_form_settings_field_' . $field_key, [ $field, $value, $this->form_settings ] ],
            ];
        }

        $html = [
            'before' => '',
            'after'  => '',
        ];

        foreach ( [
			'before' => $before,
			'after' => $after,
		] as $position => $hooks ) {
            foreach ( $hooks as $hook ) {
                $html[ $position ] .= $this->capture( $hook[0], $hook[1] );
            }
        }

        if ( '' !== $html['before'] || '' !== $html['after'] ) {
            $rows[ $row_key ] = array_filter( $html );
        }
    }

    /**
     * A post form settings row's value, resolved as develop's
     * wpuf_render_settings_field() (free) did.
     *
     * @param string $field_key Settings key
     * @param array  $field     Settings field definition
     *
     * @return mixed
     */
    private function post_row_value( $field_key, $field ) {
        $value = ! empty( $field['default'] ) ? $field['default'] : '';
        $value = ! empty( $field['value'] ) ? $field['value'] : $value;

        $is_pro_preview = ! empty( $field['pro_preview'] )
            || ( ! wpuf_is_pro_active() && in_array( $field_key, [ 'notification_edit', 'notification_edit_to', 'notification_edit_subject', 'notification_edit_body' ], true ) );

        if ( ! empty( $field['name'] ) && preg_match( '/wpuf_settings\[(.*?)\]\[(.*?)\]/', $field['name'], $matches ) ) {
            return isset( $this->form_settings[ $matches[1] ][ $matches[2] ] ) ? $this->form_settings[ $matches[1] ][ $matches[2] ] : $value;
        }

        if ( $is_pro_preview ) {
            return $value;
        }

        return isset( $this->form_settings[ $field_key ] ) ? $this->form_settings[ $field_key ] : $value;
    }

    /**
     * A registration form settings row's value, resolved as develop's
     * wpuf_render_settings_field() (pro) did.
     *
     * @param string $field_key Settings key
     * @param array  $field     Settings field definition
     *
     * @return mixed
     */
    private function registration_row_value( $field_key, $field ) {
        $value = ! empty( $field['default'] ) ? $field['default'] : '';
        $value = ! empty( $field['value'] ) ? $field['value'] : $value;

        if ( ! empty( $field['name'] ) ) {
            if ( preg_match( '/wpuf_settings\[(.*?)\]\[(.*?)\]/', $field['name'], $matches ) ) {
                return isset( $this->form_settings[ $matches[1] ][ $matches[2] ] ) ? $this->form_settings[ $matches[1] ][ $matches[2] ] : $value;
            }

            return $value;
        }

        return isset( $this->form_settings[ $field_key ] ) ? $this->form_settings[ $field_key ] : $value;
    }

    /**
     * Fire a hook without WPUF's own listeners and return the sanitized output of
     * the others.
     *
     * @param string      $hook        Hook name
     * @param array       $args        Hook arguments
     *
     * @return string Sanitized HTML ('' when nothing was printed)
     */
    private function capture( $hook, $args = [] ) {
        $this->detach_own_listeners( $hook );

        ob_start();

        // Only outside callbacks are left: the hook is a retired Vue builder view
        // hook, so it fires the WordPress deprecated way (notice under WP_DEBUG,
        // admin notice through HookDeprecations) and keeps its output.
        $retired     = wpuf()->platform()->get( HookDeprecations::class );
        $replacement = $retired->replacement_for( $hook );

        if ( '' !== $replacement && $retired->note( $hook ) ) {
            do_action_deprecated( $hook, $args, HookDeprecations::SINCE, $replacement ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        } else {
            // Spread, as develop's views called do_action(): `all` listeners see the same arguments.
            do_action( $hook, ...$args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }

        $raw = (string) ob_get_clean();

        $this->reattach_own_listeners( $hook );

        return $this->sanitize_output( $raw );
    }

    /**
     * Remove callbacks defined in WPUF free or pro from a hook (they are React
     * parts on this builder) and remember them.
     *
     * @param string $hook Hook name
     *
     * @return void
     */
    private function detach_own_listeners( $hook ) {
        global $wp_filter;

        $this->removed[ $hook ] = [];

        if ( empty( $wp_filter[ $hook ] ) || ! is_object( $wp_filter[ $hook ] ) ) {
            return;
        }

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $callback ) {
                if ( $this->is_own_callback( $callback['function'] ) ) {
                    $this->removed[ $hook ][] = [ $callback['function'], $priority, $callback['accepted_args'] ];
                }
            }
        }

        foreach ( $this->removed[ $hook ] as $callback ) {
            remove_action( $hook, $callback[0], $callback[1] );
        }
    }

    /**
     * Add back the callbacks removed by detach_own_listeners().
     *
     * @param string $hook Hook name
     *
     * @return void
     */
    private function reattach_own_listeners( $hook ) {
        foreach ( $this->removed[ $hook ] as $callback ) {
            add_action( $hook, $callback[0], $callback[1], $callback[2] );
        }

        unset( $this->removed[ $hook ] );
    }

    /**
     * Whether a callback is defined in WPUF free or pro (modules included).
     *
     * @param callable $callback Callback
     *
     * @return bool
     */
    private function is_own_callback( $callback ) {
        try {
            if ( is_array( $callback ) && 2 === count( $callback ) ) {
                $reflection = new \ReflectionMethod( $callback[0], $callback[1] );
            } elseif ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
                // Class and method passed apart: the one-string form is deprecated in PHP 8.4.
                $parts      = explode( '::', $callback, 2 );
                $reflection = new \ReflectionMethod( $parts[0], $parts[1] );
            } elseif ( $callback instanceof \Closure || is_string( $callback ) ) {
                $reflection = new \ReflectionFunction( $callback );
            } elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
                $reflection = new \ReflectionMethod( $callback, '__invoke' );
            } else {
                return false;
            }
        } catch ( \ReflectionException $e ) {
            return false;
        }

        $file = $reflection->getFileName();

        if ( ! $file ) {
            return false;
        }

        $file  = wp_normalize_path( $file );
        $roots = [ wp_normalize_path( WPUF_ROOT ) ];

        if ( defined( 'WPUF_PRO_ROOT' ) ) {
            $roots[] = wp_normalize_path( WPUF_PRO_ROOT );
        }

        foreach ( $roots as $root ) {
            if ( 0 === strpos( $file, trailingslashit( $root ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize captured output: form elements and basic markup only, no scripts,
     * styles or event attributes. Notes markup the builder cannot run (scripts,
     * Vue bindings).
     *
     * @param string $raw Captured output
     *
     * @return string
     */
    private function sanitize_output( $raw ) {
        if ( '' === trim( $raw ) ) {
            return '';
        }

        if ( preg_match( '/<script\b|\sv-[a-z]+=|\s[:@][a-z-]+=/i', $raw ) ) {
            $this->has_unsupported = true;
        }

        // Script and style blocks go whole (wp_kses would keep their text).
        $raw = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', $raw );

        return trim( wp_kses( $raw, self::allowed_html() ) );
    }

    /**
     * Allowed markup for captured output: post content plus form controls.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public static function allowed_html() {
        $common = [
            'id'          => true,
            'class'       => true,
            'name'        => true,
            'value'       => true,
            'title'       => true,
            'disabled'    => true,
            'readonly'    => true,
            'required'    => true,
            'placeholder' => true,
            'data-*'      => true,
            'aria-*'      => true,
        ];

        $allowed = wp_kses_allowed_html( 'post' );

        $allowed['form']     = [];
        $allowed['input']    = array_merge(
            $common, [
				'type'    => true,
				'checked' => true,
				'min'     => true,
				'max'     => true,
				'step'    => true,
				'size'    => true,
				'pattern' => true,
            ]
        );
        $allowed['select']   = array_merge(
            $common, [
				'multiple' => true,
				'size' => true,
			]
        );
        $allowed['option']   = [
            'value'    => true,
            'selected' => true,
            'disabled' => true,
            'label'    => true,
        ];
        $allowed['optgroup'] = [
			'label' => true,
			'disabled' => true,
		];
        $allowed['textarea'] = array_merge(
            $common, [
				'rows' => true,
				'cols' => true,
			]
        );
        $allowed['label']    = [
			'for' => true,
			'class' => true,
			'id' => true,
		];
        $allowed['fieldset'] = [
			'class' => true,
			'id' => true,
			'disabled' => true,
		];
        $allowed['legend']   = [ 'class' => true ];
        $allowed['button']   = array_merge( $common, [ 'type' => true ] );

        // A captured <form> would nest inside the builder form.
        unset( $allowed['form'] );

        return $allowed;
    }

    /**
     * Merge settings posted from legacy slots into the settings the React builder
     * sent: for each slot-owned key, the posted value wins and a key the slot did
     * not post (an unticked checkbox) is removed, as develop's form post did.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings     Settings from the React builder
     * @param array $form_data    Parsed `form_data` (the builder form, slots included)
     * @param array $slot_keys    Top-level `wpuf_settings` keys found in legacy slots
     *
     * @return array
     */
    public static function merge_posted_settings( $settings, $form_data, $slot_keys ) {
        $settings = is_array( $settings ) ? $settings : [];
        $posted   = isset( $form_data['wpuf_settings'] ) && is_array( $form_data['wpuf_settings'] ) ? $form_data['wpuf_settings'] : [];

        foreach ( (array) $slot_keys as $key ) {
            if ( ! is_string( $key ) || ! preg_match( '/^[A-Za-z0-9_\-]+$/', $key ) ) {
                continue;
            }

            if ( array_key_exists( $key, $posted ) ) {
                $settings[ $key ] = $posted[ $key ];
            } else {
                unset( $settings[ $key ] );
            }
        }

        return $settings;
    }
}
