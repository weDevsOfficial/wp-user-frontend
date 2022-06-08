<?php

class Shortcode_Block {
    public function __construct() {
        add_action( 'init', [ $this, 'create_block_shortcode_block_init' ] );
    }

    public function create_block_shortcode_block_init() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type(
            WPUF_ELEMENTOR_ASSETS . '/js/shortcode', [
				'render_callback' => [ $this, 'wpuf_render_selected_shortcode' ],
			]
        );

    }

    public function localize_object() {
        wp_localize_script( 'wpuf-blocks', 'wpuf_shortcods', $this->wpuf_shortcodes() );
        wp_localize_script( 'wpuf-blocks', 'wpuf_shortcode_atts', $this->wpuf_shortcode_atts_data() );
    }

    public function wpuf_render_selected_shortcode( $attributes, $content ) {
        $selected_shortcode = $attributes['selectedShortCode'];

        if ( empty( $selected_shortcode ) ) {
            return '<h3>Please select a shortcode</h3>';
        }

        $attributes = array_filter(
            $attributes, function ( $val, $key ) use ( $attributes, $selected_shortcode ) {
				foreach ( $this->wpuf_shortcodes() as $wpuf_shortcode ) {
					if ( $selected_shortcode === $wpuf_shortcode['code'] ) {
						return array_key_exists( $key, $wpuf_shortcode['atts'] ) && ( $val !== 0 || $val !== '' );
					}
				}
			}, ARRAY_FILTER_USE_BOTH
        );

        unset( $attributes['selectedShortCode'] );

        $atts = $this->wpuf_get_formatted_atts( $attributes );

        return do_shortcode( "[$selected_shortcode $atts]" );
    }

    private function wpuf_shortcodes() {
        return [
            [
                'code' => 'wpuf_dashboard',
                'atts' => [
                    'post_type'      => 'SELECT2',
                    'form_id'        => 'SELECT',
                    'meta'           => 'SELECT2',
                    'featured_image' => 'SWITCHER',
                    'category'       => 'SWITCHER',
                    'excerpt'        => 'SWITCHER',
                    'payment_column' => 'SWITCHER',
                ],
            ],
            [
                'code' => 'wpuf_form',
                'atts' => [
                    'id' => 'SELECT',
                ],
            ],
            [
                'code' => 'wpuf_user_listing',
                'atts' => [
                    'per_page'      => 'NUMBER',
                    'roles_exclude' => 'SELECT2',
                    'roles_include' => 'SELECT2',
                ],
            ],
        ];
    }

    private function wpuf_get_formatted_atts( $attributes ) {
        $atts = '';

        foreach ( $attributes as $key => $val ) {
            if ( empty( $val ) && ! is_bool( $val ) ) {
                continue;
            }

            if ( is_array( $val ) ) {
                $atts_nest = $key . "='" . implode(
                    ', ', array_map(
						function ( $type ) {
							return $type['value'];
						}, $val
                    )
                ) . "' ";
                $atts      .= $atts_nest;

                continue;
            }

            if ( is_bool( $val ) && $val ) {
                $val = 'on';
            }

            if ( is_bool( $val ) && ! $val ) {
                $val = 'off';
            }

            $atts .= $key . "='" . $val . "' ";
        }

        return $atts;
    }

    private function wpuf_shortcode_atts_data() {
        $form_id   = $this->get_wpuf_form_id();
        $post_meta = $this->get_wpuf_meta();

        $atts = [
            'post_type'     => wpuf_get_post_types(),
            'form_id'       => $form_id,
            'meta'          => $post_meta,
            'id'            => $form_id,
            'roles_exclude' => wp_roles()->role_names,
            'roles_include' => wp_roles()->role_names,
        ];

        return array_map(
            function ( $atts ) {
                return array_map(
                    function ( $att, $key ) {
                        return [
							'label' => $this->capitalize_label( $att ),
							'value' => $key,
                        ];
                    }, $atts, array_keys( $atts )
                );
            }, $atts
        );
    }

    private function capitalize_label( $str ) {
        return preg_replace_callback(
            '/\b\w/', function ( $s ) {
				return strtoupper( $s[0] );
			}, str_replace( '_', ' ', $str )
        );
    }

    private function get_wpuf_form_id() {
        $form_list = [];

        $forms = get_posts(
            [
                'post_type'      => [ 'wpuf_forms', 'wpuf_profile' ],
                'post_status'    => 'publish',
                'posts_per_page' => - 1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        foreach ( $forms as $form ) {
            $form_list[ $form->ID ] = $form->post_title;
        }

        return $form_list;
    }

    private function get_wpuf_meta() {
        global $wpdb;

        $all_post_meta = $wpdb->get_results( 'SELECT DISTINCT meta_key from ' . $wpdb->prefix . 'postmeta', ARRAY_N );

        return array_reduce(
            $all_post_meta, function ( $carry, $meta_key ) {
				$flat = array_merge( $meta_key, $carry );

				return array_combine( $flat, $flat );
			}, []
        );
    }
}
