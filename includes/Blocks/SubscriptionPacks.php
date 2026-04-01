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
        add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
    }

    /**
     * Register the block category for WPUF blocks
     *
     * @since WPUF_SINCE
     *
     * @param array $categories Existing block categories
     *
     * @return array
     */
    public function register_block_category( $categories ) {
        // Avoid duplicate registration
        foreach ( $categories as $category ) {
            if ( $category['slug'] === 'wpuf' ) {
                return $categories;
            }
        }

        return array_merge( $categories, [
            [
                'slug'  => 'wpuf',
                'title' => __( 'WP User Frontend', 'wp-user-frontend' ),
            ],
        ] );
    }

    /**
     * Register the Gutenberg block and its assets
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_block() {
        $asset_file = WPUF_ROOT . '/assets/js/subscription-packs.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = require $asset_file;

        wp_register_script(
            'wpuf-subscription-packs-editor',
            WPUF_ASSET_URI . '/js/subscription-packs.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            'wpuf-subscription-packs-editor-style',
            WPUF_ASSET_URI . '/js/subscription-packs.css',
            [],
            $asset['version']
        );

        wp_localize_script(
            'wpuf-subscription-packs-editor',
            'wpufSubscriptionPacks',
            $this->get_editor_data()
        );

        $block_json_path = WPUF_ROOT . '/src/js/blocks/subscription-packs';

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
        $subscription = \WPUF_Subscription::init();
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
            'include'         => [],
            'exclude'         => [],
            'columns'         => 3,
            'order'           => '',
            'orderby'         => '',
            'showPrice'       => true,
            'showFeatures'    => true,
            'showDescription' => true,
            'buttonColor'     => '',
            'buttonText'      => '',
        ];

        $attributes = wp_parse_args( $attributes, $defaults );

        // Build query args for get_subscriptions()
        $subscription_args = [];

        if ( ! empty( $attributes['include'] ) ) {
            $subscription_args['include'] = implode( ',', array_map( 'intval', $attributes['include'] ) );
        }

        if ( ! empty( $attributes['exclude'] ) ) {
            $subscription_args['exclude'] = implode( ',', array_map( 'intval', $attributes['exclude'] ) );
        }

        if ( ! empty( $attributes['order'] ) ) {
            $subscription_args['order'] = $attributes['order'];
        }

        if ( ! empty( $attributes['orderby'] ) ) {
            $subscription_args['orderby'] = $attributes['orderby'];
        }

        $subscription = \WPUF_Subscription::init();
        $packs        = $subscription->get_subscriptions( $subscription_args );
        $details_meta = $subscription->get_details_meta_value();
        $current_pack = \WPUF_Subscription::get_user_pack( get_current_user_id() );

        // Build block_config for templates
        $block_config = [
            'columns'          => absint( $attributes['columns'] ),
            'show_price'       => (bool) $attributes['showPrice'],
            'show_features'    => (bool) $attributes['showFeatures'],
            'show_description' => (bool) $attributes['showDescription'],
            'button_color'     => sanitize_hex_color( $attributes['buttonColor'] ),
            'button_text'      => sanitize_text_field( $attributes['buttonText'] ),
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
        wp_enqueue_style( 'wpuf-frontend-subscription' );

        // Determine pack order for include
        $pack_order = [];
        if ( ! empty( $attributes['include'] ) ) {
            $pack_order = array_map( 'intval', $attributes['include'] );
        }

        ob_start();

        wpuf_load_template(
            'subscriptions/listing.php',
            apply_filters(
                'wpuf_subscription_listing_args', [
                    'subscription' => $subscription,
                    'args'         => [
                        'include' => ! empty( $attributes['include'] ) ? implode( ',', $attributes['include'] ) : '',
                        'exclude' => ! empty( $attributes['exclude'] ) ? implode( ',', $attributes['exclude'] ) : '',
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
