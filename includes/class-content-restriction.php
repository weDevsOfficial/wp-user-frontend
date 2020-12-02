<?php if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class partial content
 */
class WPUF_Partial_Content {

    /**
     * Constructor for partial content class
     */
    public function __construct() {
        add_shortcode( 'wpuf_content_restrict', [$this, 'add_shortcode'] );
    }

    public function add_shortcode($atts, $content) {

        $defaults = [
            'roles'         => [],
            'subscriptions' => []
        ];

        $atts = shortcode_atts( $defaults, $atts, 'wpuf_content_restrict' );

        $roles         = isset( $atts['roles'] ) ? explode(',', $atts['roles']) : [];
        $subscriptions = isset( $atts['subscriptions'] ) ? explode(',', $atts['subscriptions']) : [];

        unset($roles[0]);
        unset($subscriptions[0]);

        ob_start();

        wpuf_content_restrict( do_shortcode( $content ), $roles, $subscriptions );

        return ob_get_clean();
    }

}