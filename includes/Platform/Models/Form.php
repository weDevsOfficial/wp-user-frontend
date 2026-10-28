<?php
/**
 * Form model
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Models;

/**
 * A post form (`wpuf_forms`) or registration form (`wpuf_profile`) with its
 * fields, settings, notifications and integrations, in their stored shapes.
 *
 * @since WPUF_SINCE
 */
class Form extends Model {

    /**
     * A model from FormStore::read() data.
     *
     * @since WPUF_SINCE
     *
     * @param array $read `post`, `fields`, `settings`, `notifications`, `integrations`
     *
     * @return static
     */
    public static function from_read( array $read ) {
        $post = $read['post'];

        return new static(
            $post->ID,
            [
                'post_type'     => $post->post_type,
                'post_title'    => $post->post_title,
                'post_status'   => $post->post_status,
                'fields'        => isset( $read['fields'] ) ? $read['fields'] : [],
                'settings'      => isset( $read['settings'] ) ? $read['settings'] : [],
                'notifications' => isset( $read['notifications'] ) ? $read['notifications'] : [],
                'integrations'  => isset( $read['integrations'] ) ? $read['integrations'] : '',
            ]
        );
    }

    /**
     * Post type (`wpuf_forms` or `wpuf_profile`).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_type() {
        return (string) $this->get( 'post_type', '' );
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
     * Fields as stored.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_fields() {
        return (array) $this->get( 'fields', [] );
    }

    /**
     * Form settings as stored.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_settings() {
        $settings = $this->get( 'settings', [] );

        return is_array( $settings ) ? $settings : [];
    }

    /**
     * Notifications as stored.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_notifications() {
        $notifications = $this->get( 'notifications', [] );

        return is_array( $notifications ) ? $notifications : [];
    }

    /**
     * Integrations as stored ('' when none).
     *
     * @since WPUF_SINCE
     *
     * @return mixed
     */
    public function get_integrations() {
        return $this->get( 'integrations', '' );
    }
}
