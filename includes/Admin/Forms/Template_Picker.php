<?php

namespace WeDevs\Wpuf\Admin\Forms;

/**
 * Data of the form template picker (the React forms list screens).
 *
 * Replaces the markup of includes/Admin/template-parts/modal-v4.2.php: the
 * same templates, links, categories and counts, as data for the React picker.
 *
 * @since WPUF_SINCE
 */
class Template_Picker {

    /**
     * Picker data for a forms list screen.
     *
     * @since WPUF_SINCE
     *
     * @param array $args {
     *     @type string $form_type      `post` or `profile`.
     *     @type array  $registry       Template objects, keyed by template name.
     *     @type array  $pro_templates  Pro preview objects (shown without Pro).
     *     @type string $action_name    The `admin.php?action=` that creates a form.
     *     @type string $blank_form_url URL of a blank form.
     * }
     *
     * @return array
     */
    public static function data( $args ) {
        $args = wp_parse_args(
            $args,
            [
                'form_type'      => 'post',
                'registry'       => [],
                'pro_templates'  => [],
                'action_name'    => 'post_form_template',
                'blank_form_url' => '',
            ]
        );

        $is_profile = 'profile' === $args['form_type'];
        $categories = self::categories( $is_profile );
        $default    = $is_profile ? 'general' : 'post';
        $nonce      = wp_create_nonce( 'wpuf_create_from_template' );
        $templates  = [];

        foreach ( (array) $args['registry'] as $key => $template ) {
            if ( ! is_object( $template ) || ! method_exists( $template, 'get_title' ) ) {
                continue;
            }

            $title   = (string) $template->get_title();
            $enabled = method_exists( $template, 'is_enabled' ) ? (bool) $template->is_enabled() : true;

            $templates[] = [
                'key'         => (string) $key,
                'title'       => $title,
                'image'       => ! empty( $template->image ) ? esc_url_raw( $template->image ) : '',
                'description' => ! empty( $template->description ) ? wp_strip_all_tags( $template->description ) : '',
                'category'    => self::category_of( $title, $categories, $default ),
                'enabled'     => $enabled,
                // The plugin a disabled template waits for (name + install link), WPUF_SINCE.
                'requires'    => $enabled ? null : self::requirement( $template ),
                'is_pro'      => false,
                'url'         => $enabled
                    ? esc_url_raw(
                        add_query_arg(
                            [
                                'action'   => $args['action_name'],
                                'template' => $key,
                                '_wpnonce' => $nonce,
                            ],
                            admin_url( 'admin.php' )
                        )
                    )
                    : '',
            ];
        }

        $upgrade_url = class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' )
            ? \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url()
            : '';

        foreach ( (array) $args['pro_templates'] as $key => $template ) {
            if ( ! is_object( $template ) || ! method_exists( $template, 'get_title' ) ) {
                continue;
            }

            $title = (string) $template->get_title();

            $templates[] = [
                'key'         => 'pro_' . $key,
                'title'       => $title,
                'image'       => method_exists( $template, 'get_image' ) ? esc_url_raw( (string) $template->get_image() ) : '',
                'description' => '',
                'category'    => self::category_of( $title, $categories, $default ),
                'enabled'     => false,
                'is_pro'      => true,
                'url'         => esc_url_raw( $upgrade_url ),
            ];
        }

        $category_list = [];

        foreach ( $categories as $slug => $category ) {
            $category_list[] = [
                'slug'  => $slug,
                'label' => $category['label'],
            ];
        }

        return [
            'form_type'        => $is_profile ? 'profile' : 'post',
            'title'            => $is_profile
                ? __( 'Select a Registration Form Template', 'wp-user-frontend' )
                : __( 'Select a Post Form Template', 'wp-user-frontend' ),
            'blank_form_url'   => esc_url_raw( $args['blank_form_url'] ),
            'default_category' => $default,
            'categories'       => $category_list,
            'templates'        => $templates,
        ];
    }

    /**
     * Categories of the picker, with the title keywords that sort a template
     * into them (as modal-v4.2.php).
     *
     * @since WPUF_SINCE
     *
     * @param bool $is_profile Registration forms screen.
     *
     * @return array
     */
    private static function categories( $is_profile ) {
        if ( $is_profile ) {
            return [
                'general'    => [
                    'label'    => __( 'General', 'wp-user-frontend' ),
                    'keywords' => [ 'simple', 'signup', 'blog author' ],
                ],
                'ecommerce'  => [
                    'label'    => __( 'E-commerce', 'wp-user-frontend' ),
                    'keywords' => [ 'vendor', 'marketplace', 'product' ],
                ],
                'membership' => [
                    'label'    => __( 'Membership', 'wp-user-frontend' ),
                    'keywords' => [ 'membership' ],
                ],
                'community'  => [
                    'label'    => __( 'Community', 'wp-user-frontend' ),
                    'keywords' => [ 'community member' ],
                ],
            ];
        }

        return [
            'ecommerce' => [
                'label'    => __( 'E-commerce', 'wp-user-frontend' ),
                'keywords' => [ 'vendor', 'marketplace', 'product', 'woocommerce', 'edd' ],
            ],
            'post'      => [
                'label'    => __( 'Post Form', 'wp-user-frontend' ),
                'keywords' => [ 'post', 'article', 'blog' ],
            ],
        ];
    }

    /**
     * Category of a template, from its title.
     *
     * @since WPUF_SINCE
     *
     * @param string $title      Template title.
     * @param array  $categories Categories with keywords.
     * @param string $fallback   Category when no keyword matches.
     *
     * @return string
     */
    private static function category_of( $title, $categories, $fallback ) {
        $title = strtolower( $title );

        foreach ( $categories as $slug => $category ) {
            foreach ( $category['keywords'] as $keyword ) {
                if ( false !== strpos( $title, $keyword ) ) {
                    return $slug;
                }
            }
        }

        return $fallback;
    }

    /**
     * The plugin a template needs (by its class), with an install link for
     * who may install plugins. Filter `wpuf_form_template_requirement`.
     *
     * @since WPUF_SINCE
     *
     * @param object $template Template object
     *
     * @return array|null { name, url }
     */
    private static function requirement( $template ) {
        $class = get_class( $template );
        $known = [
            'WooCommerce' => [ 'WooCommerce', 'woocommerce' ],
            'EDD'         => [ 'Easy Digital Downloads', 'easy-digital-downloads' ],
            'Dokan'       => [ 'Dokan', 'dokan-lite' ],
            'WC_Vendors'  => [ 'WC Vendors', 'wc-vendors' ],
            'WCMp'        => [ 'MultiVendorX', 'dc-woocommerce-multi-vendor' ],
            'Events'      => [ 'The Events Calendar', 'the-events-calendar' ],
        ];
        $found = null;

        foreach ( $known as $needle => $plugin ) {
            if ( false !== stripos( $class, $needle ) ) {
                $found = [
                    'name' => $plugin[0],
                    'url'  => current_user_can( 'install_plugins' ) ? admin_url( 'plugin-install.php?s=' . rawurlencode( $plugin[1] ) . '&tab=search&type=term' ) : '',
                ];
                break;
            }
        }

        /**
         * Filters the plugin a disabled form template needs.
         *
         * @since WPUF_SINCE
         *
         * @param array|null $found    { name, url } or null when unknown
         * @param object     $template Template object
         */
        $found = apply_filters( 'wpuf_form_template_requirement', $found, $template );

        return is_array( $found ) && ! empty( $found['name'] ) ? [
            'name' => wp_strip_all_tags( (string) $found['name'] ),
            'url'  => ! empty( $found['url'] ) ? esc_url_raw( $found['url'] ) : '',
        ] : null;
    }
}
