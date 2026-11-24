<?php
// DESCRIPTION: Registers the wpuf/subscription-packs Gutenberg block.
// Handles script/style registration, localized editor data, and the server-side render callback.

namespace WeDevs\Wpuf\Blocks;

/**
 * Subscription Packs Gutenberg block
 *
 * @since WPUF_SINCE
 */
class SubscriptionPacks {

    /**
     * Initialize the block
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
    }

    /**
     * Register the Gutenberg block and localise editor data
     *
     * The "wpuf" block category is registered by the PostForm block.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_block() {
        $asset_file = WPUF_ROOT . '/assets/js/blocks/subscription-packs.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = require $asset_file;

        wp_register_script(
            'wpuf-subscription-packs-editor',
            WPUF_ROOT_URI . '/assets/js/blocks/subscription-packs.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            'wpuf-subscription-packs-editor-style',
            WPUF_ROOT_URI . '/assets/js/blocks/subscription-packs.css',
            [ 'wpuf-frontend-subscriptions' ],
            $asset['version']
        );

        // Editor data is only needed in the block editor; avoid the pack query on every front-end request.
        if ( is_admin() ) {
            wp_localize_script(
                'wpuf-subscription-packs-editor',
                'wpufSubscriptionPacks',
                $this->get_editor_data()
            );
        }

        // Read the metadata from the built output: .distignore strips `src` from the release package.
        register_block_type(
            WPUF_ROOT . '/assets/js/blocks/subscription-packs',
            [
                'render_callback' => [ $this, 'render' ],
            ]
        );
    }

    /**
     * Get data to pass to the block editor via wp_localize_script
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_editor_data() {
        $subscription = wpuf()->subscription;
        $packs        = $subscription->get_subscriptions();
        $packs_list   = [];

        if ( $packs ) {
            foreach ( $packs as $pack ) {
                $packs_list[] = [
                    'id'    => $pack->ID,
                    'title' => $pack->post_title,
                ];
            }
        }

        return [
            'packs'          => $packs_list,
            'orderByOptions' => [
                [ 'value' => '',               'label' => __( 'Default', 'wp-user-frontend' ) ],
                [ 'value' => 'title',          'label' => __( 'Title', 'wp-user-frontend' ) ],
                [ 'value' => 'date',           'label' => __( 'Date', 'wp-user-frontend' ) ],
                [ 'value' => 'meta_value_num', 'label' => __( 'Sort Order', 'wp-user-frontend' ) ],
            ],
        ];
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
        $defaults = [
            'include'              => [],
            'exclude'              => [],
            'columns'              => 3,
            'order'                => '',
            'orderby'              => '',
            'showPrice'            => true,
            'showFeatures'         => true,
            'showDescription'      => true,
            'buttonColor'          => '',
            'buttonText'           => '',
            'packBackgroundColor'  => '#ffffff',
            'packBorderColor'      => '#e5e7eb',
            'packBorderRadius'     => 12,
            'packPadding'          => 24,
            'packShadow'           => 'md',
            'titleFontSize'        => 18,
            'priceFontSize'        => 30,
            'cardGap'              => 16,
            'recurringFontSize'    => 14,
        ];

        $attributes = wp_parse_args( $attributes, $defaults );

        // Build query args for get_subscriptions()
        $subscription_args = [];

        if ( ! empty( $attributes['include'] ) ) {
            $subscription_args['include'] = implode( ',', array_map( 'intval', (array) $attributes['include'] ) );
        }

        if ( ! empty( $attributes['exclude'] ) ) {
            $subscription_args['exclude'] = implode( ',', array_map( 'intval', (array) $attributes['exclude'] ) );
        }

        if ( in_array( strtoupper( (string) $attributes['order'] ), [ 'ASC', 'DESC' ], true ) ) {
            $subscription_args['order'] = strtoupper( $attributes['order'] );
        }

        if ( in_array( $attributes['orderby'], [ 'title', 'date', 'meta_value_num' ], true ) ) {
            $subscription_args['orderby'] = $attributes['orderby'];
        }

        $subscription = wpuf()->subscription;
        $packs        = $subscription->get_subscriptions( $subscription_args );
        $details_meta = $subscription->get_details_meta_value();
        $current_pack = $subscription->get_user_pack( get_current_user_id() );

        // Build block_config for templates
        $block_config = [
            'columns'               => absint( $attributes['columns'] ),
            'show_price'            => (bool) $attributes['showPrice'],
            'show_features'         => (bool) $attributes['showFeatures'],
            'show_description'      => (bool) $attributes['showDescription'],
            'button_color'          => sanitize_hex_color( $attributes['buttonColor'] ),
            'button_text'           => sanitize_text_field( $attributes['buttonText'] ),
            'pack_background_color' => sanitize_hex_color( $attributes['packBackgroundColor'] ),
            'pack_border_color'     => sanitize_hex_color( $attributes['packBorderColor'] ),
            'pack_border_radius'    => absint( $attributes['packBorderRadius'] ),
            'pack_padding'          => absint( $attributes['packPadding'] ),
            'pack_shadow'           => sanitize_key( $attributes['packShadow'] ),
            'title_font_size'       => absint( $attributes['titleFontSize'] ),
            'price_font_size'       => absint( $attributes['priceFontSize'] ),
            'card_gap'              => absint( $attributes['cardGap'] ),
            'recurring_font_size'   => absint( $attributes['recurringFontSize'] ),
        ];

        /**
         * Filter the block configuration before rendering
         *
         * @since WPUF_SINCE
         *
         * @param array $block_config Block configuration array
         * @param array $attributes   Raw block attributes
         */
        $block_config = apply_filters( 'wpuf_subscription_block_config', $block_config, $attributes );

        // Enqueue frontend styles
        wp_enqueue_style( 'wpuf-frontend-subscriptions' );

        // Determine pack order for include
        $pack_order = ! empty( $attributes['include'] ) ? array_map( 'intval', (array) $attributes['include'] ) : [];
        $exclude    = ! empty( $attributes['exclude'] ) ? array_map( 'intval', (array) $attributes['exclude'] ) : [];

        ob_start();

        wpuf_load_template(
            'subscriptions/listing.php',
            apply_filters(
                'wpuf_subscription_listing_args', [
                    'subscription' => $subscription,
                    'args'         => [
                        'include' => implode( ',', $pack_order ),
                        'exclude' => implode( ',', $exclude ),
                    ],
                    'packs'        => $packs,
                    'pack_order'   => $pack_order,
                    'details_meta' => $details_meta,
                    'current_pack' => $current_pack,
                    'block_config' => $block_config,
                ]
            )
        );

        $content = ob_get_clean();

        $wrapper_attributes = get_block_wrapper_attributes();

        return sprintf( '<div %s>%s</div>', $wrapper_attributes, $content );
    }
}
