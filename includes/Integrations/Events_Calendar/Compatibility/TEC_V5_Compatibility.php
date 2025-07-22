<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC v5 Compatibility Handler
 *
 * Handles The Events Calendar v5.x API calls and functionality
 *
 * @since WPUF_SINCE
 */
class TEC_V5_Compatibility {

    /**
     * Save event using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param array $postarr      The post array (title, content, etc.)
     * @param array $meta_vars    The meta fields from the form
     * @param int   $form_id      The WPUF form ID
     * @param array $form_settings The WPUF form settings
     * @return int|false The created event post ID on success, 0 or false on failure
     */
    public function save_event( $postarr, $meta_vars, $form_id, $form_settings ) {
        try {
            // First create the WordPress post
            $post_id = wp_insert_post( $postarr );

            if ( is_wp_error( $post_id ) || ! $post_id ) {
                return false;
            }

            // Use TEC's v5 API method to save event meta
            $result = \Tribe__Events__API::saveEventMeta( $post_id, $meta_vars, get_post( $post_id ) );

            if ( false === $result ) {
                return false;
            }

            return $post_id;

        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if TEC v5 is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_active() {
        return class_exists( 'Tribe__Events__API' ) && class_exists( 'Tribe__Events__Main' );
    }
}
