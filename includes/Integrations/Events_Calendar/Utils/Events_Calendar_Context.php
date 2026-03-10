<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Utils;

/**
 * Events Calendar Context Helper
 *
 * Centralizes all Events Calendar context detection logic
 *
 * @since 4.1.9
 */
class Events_Calendar_Context {

    /**
     * Check if we're currently in an Events Calendar context
     *
     * @param array $params Optional parameters to override $_GET values for testing
     * @return bool True if Events Calendar context detected
     */
    public static function is_current_context( $params = [] ) {
        // Only run this logic in admin area for security
        if ( ! is_admin() ) {
            return false;
        }

        // Use provided params or sanitize $_GET inputs
        $post_type = isset( $params['post_type'] )
            ? $params['post_type']
            : ( isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '' );

        $page = isset( $params['page'] )
            ? $params['page']
            : ( isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '' );

        $action = isset( $params['action'] )
            ? $params['action']
            : ( isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '' );

        $form_id = isset( $params['id'] )
            ? (int) $params['id']
            : ( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 );

        $template = isset( $params['template'] )
            ? $params['template']
            : ( isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : '' );

        // Check if post_type parameter is tribe_events
        if ( in_array( $post_type, TEC_Constants::TEC_POST_TYPES, true ) ) {
            return true;
        }

        // Check for "add new form" case with Events Calendar template
        if ( 'wpuf-post-forms' === $page && 'add-new' === $action ) {
            if ( TEC_Constants::FORM_TEMPLATE_ID === $template || 'events_calendar' === $template ) {
                return true;
            }
        }

        // Check if we're editing an existing form
        if ( 'wpuf-post-forms' === $page && 'edit' === $action && $form_id > 0 ) {
            return self::is_events_calendar_form( $form_id );
        }

        return false;
    }

    /**
     * Check if a specific form is configured for Events Calendar
     *
     * @param int $form_id Form ID to check
     * @return bool True if form is Events Calendar form
     */
    public static function is_events_calendar_form( $form_id ) {
        if ( ! $form_id ) {
            return false;
        }

        $form_settings = get_post_meta( $form_id, 'wpuf_form_settings', true );

        // Verify form_settings is an array to avoid warnings
        if ( ! is_array( $form_settings ) ) {
            return false;
        }

        // Check if form is configured for tribe_events post type
        if ( ! empty( $form_settings['post_type'] ) && in_array( $form_settings['post_type'], TEC_Constants::TEC_POST_TYPES, true ) ) {
            return true;
        }

        // Also check if form template is Events Calendar
        if ( ! empty( $form_settings['form_template'] ) && TEC_Constants::FORM_TEMPLATE_ID === $form_settings['form_template'] ) {
            return true;
        }

        return false;
    }

    /**
     * Get the primary TEC post type
     *
     * @return string
     */
    public static function get_primary_post_type() {
        return TEC_Constants::TEC_POST_TYPES[0] ?? 'tribe_events';
    }

    /**
     * Get all supported TEC post types
     *
     * @return array
     */
    public static function get_supported_post_types() {
        return TEC_Constants::TEC_POST_TYPES;
    }

    /**
     * Get the form template ID
     *
     * @return string
     */
    public static function get_form_template_id() {
        return TEC_Constants::FORM_TEMPLATE_ID;
    }
}
