<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC v5 Compatibility Handler
 *
 * Handles The Events Calendar v5.x API calls and functionality
 *
 * @since 4.1.9
 */
class TEC_V5_Compatibility {

    /**
     * Save event using TEC v5 API
     *
     * @since 4.1.9
     *
     * @param array $postarr      The post array (title, content, etc.)
     * @param array $meta_vars    The meta fields from the form
     * @param int   $form_id      The WPUF form ID
     * @param array $form_settings The WPUF form settings
     * @return int|false The created event post ID on success, 0 or false on failure
     */
    public function save_event( $postarr, $meta_vars, $form_id, $form_settings ) {
        try {
            /**
             * Opportunity to modify post data before creating the event in TEC v5
             *
             * This filter allows developers to modify the WordPress post array before
             * creating the event post. Useful for custom post fields, validation,
             * or integration with other plugins.
             *
             * @since 4.1.9
             *
             * @param array $postarr      The WordPress post array (title, content, etc.)
             * @param array $meta_vars    The meta fields from the form
             * @param int   $form_id      The WPUF form ID
             * @param array $form_settings The WPUF form settings
             */
            $postarr = apply_filters( 'wpuf_tec_v5_before_create_post', $postarr, $meta_vars, $form_id, $form_settings );

            // First create the WordPress post
            $post_id = wp_insert_post( $postarr );

            if ( is_wp_error( $post_id ) || ! $post_id ) {
                return false;
            }

            /**
             * Opportunity to modify meta variables before saving to TEC v5
             *
             * This filter allows developers to modify the meta variables before they're
             * saved using TEC's v5 API. Useful for custom event fields, validation,
             * or data transformation.
             *
             * @since 4.1.9
             *
             * @param array $meta_vars    The meta fields from the form
             * @param int   $post_id      The created event post ID
             * @param array $postarr      The original WordPress post array
             * @param int   $form_id      The WPUF form ID
             * @param array $form_settings The WPUF form settings
             */
            $meta_vars = apply_filters( 'wpuf_tec_v5_before_save_meta', $meta_vars, $post_id, $postarr, $form_id, $form_settings );

            // Use TEC's v5 API method to save event meta
            $result = \Tribe__Events__API::saveEventMeta( $post_id, $meta_vars, get_post( $post_id ) );

            if ( false === $result ) {
                return false;
            }

            /**
             * Opportunity to perform actions after event creation in TEC v5
             *
             * This action allows developers to perform additional operations after
             * an event has been successfully created using TEC v5 API. Useful for
             * notifications, integrations, or custom post-processing.
             *
             * @since 4.1.9
             *
             * @param int   $post_id      The created event post ID
             * @param array $meta_vars    The meta fields that were saved
             * @param array $postarr      The original WordPress post array
             * @param int   $form_id      The WPUF form ID
             * @param array $form_settings The WPUF form settings
             */
            do_action( 'wpuf_tec_v5_after_create_event', $post_id, $meta_vars, $postarr, $form_id, $form_settings );

            return $post_id;

        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if TEC v5 is active
     *
     * @since 4.1.9
     *
     * @return bool
     */
    public function is_active() {
        return class_exists( 'Tribe__Events__API' ) && class_exists( 'Tribe__Events__Main' );
    }
}
