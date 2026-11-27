<?php
/**
 * Subscription pack model
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Models;

/**
 * A subscription pack (`wpuf_subscription`) with its meta in the shape
 * `Admin\Subscription::get_subscription_meta()` returns.
 *
 * @since WPUF_SINCE
 */
class SubscriptionPack extends Model {

    /**
     * A model from SubscriptionStore::read() data.
     *
     * @since WPUF_SINCE
     *
     * @param array $read `post`, `meta`
     *
     * @return static
     */
    public static function from_read( array $read ) {
        $post = $read['post'];

        return new static(
            $post->ID,
            [
                'post_title'   => $post->post_title,
                'post_content' => $post->post_content,
                'post_status'  => $post->post_status,
                'meta'         => isset( $read['meta'] ) ? (array) $read['meta'] : [],
            ]
        );
    }

    /**
     * Title.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_title() {
        return (string) $this->get( 'post_title', '' );
    }

    /**
     * Post status.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_status() {
        return (string) $this->get( 'post_status', '' );
    }

    /**
     * One meta value (keys as get_subscription_meta() returns them).
     *
     * @since WPUF_SINCE
     *
     * @param string $key     Meta key, e.g. 'billing_amount'
     * @param mixed  $default Value when missing
     *
     * @return mixed
     */
    public function get_meta( $key, $default = null ) {
        $meta = (array) $this->get( 'meta', [] );

        return array_key_exists( $key, $meta ) ? $meta[ $key ] : $default;
    }

    /**
     * Price (`billing_amount`).
     *
     * @since WPUF_SINCE
     *
     * @return float
     */
    public function get_price() {
        return (float) $this->get_meta( 'billing_amount', 0 );
    }
}
