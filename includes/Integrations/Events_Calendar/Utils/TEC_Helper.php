<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Utils;

/**
 * TEC Helper Utility
 *
 * Provides common helper functions for TEC operations
 *
 * @since WPUF_SINCE
 */
class TEC_Helper {

    /**
     * Check if The Events Calendar is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function is_tec_active() {
        return class_exists( 'Tribe__Events__Main' );
    }

    /**
     * Get TEC version
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public static function get_tec_version() {
        if ( class_exists( 'Tribe__Events__Main' ) ) {
            return \Tribe__Events__Main::VERSION;
        }
        return '0.0.0';
    }

    /**
     * Check if using TEC v6 or higher
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function is_tec_v6() {
        return version_compare( self::get_tec_version(), TEC_Constants::TEC_V6_MIN_VERSION, '>=' );
    }

    /**
     * Check if using TEC v5
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function is_tec_v5() {
        $version = self::get_tec_version();
        return version_compare( $version, TEC_Constants::TEC_V5_MIN_VERSION, '>=' ) && 
               version_compare( $version, TEC_Constants::TEC_V6_MIN_VERSION, '<' );
    }

    /**
     * Get site timezone
     *
     * @since WPUF_SINCE
     *
     * @return \DateTimeZone
     */
    public static function get_site_timezone() {
        return wp_timezone();
    }

    /**
     * Get site timezone string
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public static function get_site_timezone_string() {
        return wp_timezone_string();
    }

    /**
     * Format date for TEC
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     * @param bool   $is_utc
     * @return string
     */
    public static function format_tec_date( $date_string, $is_utc = false ) {
        try {
            $timezone = $is_utc ? new \DateTimeZone( 'UTC' ) : self::get_site_timezone();
            $date = new \DateTimeImmutable( $date_string, $timezone );
            
            if ( $is_utc ) {
                $date = $date->setTimezone( new \DateTimeZone( 'UTC' ) );
            }
            
            return $date->format( TEC_Constants::TEC_DATETIME_FORMAT );
        } catch ( \Exception $e ) {
            return $date_string;
        }
    }

    /**
     * Check if date is in valid TEC format
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     * @return bool
     */
    public static function is_valid_tec_date( $date_string ) {
        if ( ! is_string( $date_string ) ) {
            return false;
        }

        $parsed = \DateTime::createFromFormat( TEC_Constants::TEC_DATETIME_FORMAT, $date_string );
        return $parsed && $parsed->format( TEC_Constants::TEC_DATETIME_FORMAT ) === $date_string;
    }

    /**
     * Check if field is a TEC date field
     *
     * @since WPUF_SINCE
     *
     * @param string $field_name
     * @return bool
     */
    public static function is_tec_date_field( $field_name ) {
        return in_array( $field_name, TEC_Constants::EVENT_DATE_FIELDS, true );
    }

    /**
     * Check if post type is a TEC post type
     *
     * @since WPUF_SINCE
     *
     * @param string $post_type
     * @return bool
     */
    public static function is_tec_post_type( $post_type ) {
        return in_array( $post_type, TEC_Constants::TEC_POST_TYPES, true );
    }



    /**
     * Get all organizers
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public static function get_all_organizers() {
        if ( ! self::is_tec_active() ) {
            return [];
        }

        try {
            // Use the correct TEC function to get organizers
            return tribe_get_organizers( false, -1, true );
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Check if timezone is valid
     *
     * @since WPUF_SINCE
     *
     * @param string $timezone_string
     * @return bool
     */
    public static function is_valid_timezone( $timezone_string ) {
        if ( empty( $timezone_string ) ) {
            return false;
        }

        try {
            $timezone = new \DateTimeZone( $timezone_string );
            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }



    /**
     * Sanitize organizer data
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return array
     */
    public static function sanitize_organizer_data( $organizer_data ) {
        $sanitized = [];

        if ( ! empty( $organizer_data['Organizer'] ) ) {
            $sanitized['Organizer'] = sanitize_text_field( $organizer_data['Organizer'] );
        }

        if ( ! empty( $organizer_data['Email'] ) ) {
            $sanitized['Email'] = sanitize_email( $organizer_data['Email'] );
        }

        if ( ! empty( $organizer_data['Phone'] ) ) {
            $sanitized['Phone'] = sanitize_text_field( $organizer_data['Phone'] );
        }

        if ( ! empty( $organizer_data['Website'] ) ) {
            $sanitized['Website'] = esc_url_raw( $organizer_data['Website'] );
        }

        return $sanitized;
    }
} 