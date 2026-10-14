<?php
/**
 * Subscription Preview (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

/**
 * Pro subscription features previewed in the free plugin: the payment gateways (the
 * pack tab and detail previews lived in the removed classic pack editor).
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Subscription_Preview extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    /**
     * Payment gateways for previewing in the Free version
     *
     * @since 3.6.0
     *
     * @param $gateways
     *
     * @return void
     */
    public function wpuf_payment_gateways( $gateways ) {
        // Keep the admin label as plain text. The PRO badge is rendered by the
        // gateway card UI based on the is_pro_preview flag, so embedding badge
        // markup here would only show up as escaped text in the card grid.
        $gateways['stripe'] = [
            'admin_label'    => __( 'Credit Card', 'wp-user-frontend' ),
            'checkout_label' => __( 'Credit Card', 'wp-user-frontend' ),
            'label_class'    => 'pro-preview',
            'is_pro_preview' => true,
        ];

        return $gateways;
    }
}
