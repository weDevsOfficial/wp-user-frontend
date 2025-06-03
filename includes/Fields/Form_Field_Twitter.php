<?php
declare( strict_types=1 );

namespace WeDevs\Wpuf\Fields;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Twitter Field Class
 *
 * Handles Twitter/X URL field functionality including validation,
 * icon display, and URL formatting for social media integration.
 *
 * @since WPUF_SINCE
 * @package WeDevs\Wpuf\Fields
 */
class Form_Field_Twitter extends Form_Field_Social {

    /**
     * Constructor - initializes Twitter field properties
     *
     * Sets up platform-specific properties including validation patterns,
     * SVG icon, base URL, and field configuration for Twitter/X integration.
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        // Set up platform-specific properties.
        $this->platform             = 'twitter';
        $this->platform_name        = 'X (Twitter)';
        $this->icon_svg             = '<svg class="wpuf-twitter-svg" style="display: inline-block; vertical-align: middle; margin-left: 8px; width: 20px; height: 20px;" width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';
        $this->base_url             = 'https://twitter.com/';
        $this->username_pattern     = '^@?[a-zA-Z0-9_]{1,15}$';
        $this->url_pattern          = '/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]{1,15})/';
        $this->max_username_length  = 15;
        $this->example_username     = '@username';

        // Set standard field properties.
        $this->name       = __( 'X (Twitter)', 'wp-user-frontend' );
        $this->input_type = 'twitter_url';
        $this->icon       = 'twitter';
    }

    /**
     * Override the get_field_props to set standard meta key format
     *
     * Ensures consistent meta key naming across all social fields
     * using the wpuf_social_{platform} format.
     *
     * @since WPUF_SINCE
     *
     * @return array Field properties with standardized meta key.
     */
    public function get_field_props() {
        $props = parent::get_field_props();
        
        // Set the standardized meta key format for social fields.
        $props['name'] = 'wpuf_social_twitter';
        
        return $props;
    }

    /**
     * Override the get_options_settings to make meta key readonly in backend
     *
     * Makes the Meta Key field visible but readonly in the admin/backend settings to prevent
     * administrators from changing the standardized meta key format while still showing the value.
     * Also removes the size option from advanced settings.
     *
     * @since WPUF_SINCE
     *
     * @return array Updated settings with readonly meta key field and without size option.
     */
    public function get_options_settings() {
        $settings = parent::get_options_settings();
        
        $settings = array_filter( $settings, function( $setting ) {
            return ! ( isset( $setting['name'] ) && 'size' === $setting['name'] );
        });
        
        // Find the meta key setting and make it readonly while keeping it visible
        foreach ( $settings as &$setting ) {
            if ( isset( $setting['name'] ) && 'name' === $setting['name'] ) {
                $setting['disabled']  = true;
                $setting['css_class'] = 'wpuf-readonly-field';
                $setting['help_text'] = __( 'This meta key is automatically set to follow the wpuf_social_twitter format and cannot be changed', 'wp-user-frontend' );
                break;
            }
        }

        return $settings;
    }

    /**
     * Get platform-specific icon
     *
     * Returns the Twitter-specific SVG icon set in the constructor
     * instead of the parent's default icon.
     *
     * @since WPUF_SINCE
     *
     * @return string Twitter SVG icon markup.
     */
    protected function get_platform_icon() {
        return $this->icon_svg;
    }
}
