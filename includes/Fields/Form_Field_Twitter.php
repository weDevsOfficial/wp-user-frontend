<?php declare(strict_types=1); 

namespace WeDevs\Wpuf\Fields;

/**
 * Twitter Field Class
 */
class Form_Field_Twitter extends Form_Field_Social {

    public function __construct() {
        // Set up platform-specific properties
        $this->platform = 'twitter';
        $this->platform_name = 'X (Twitter)';
        $this->icon_svg = '<svg class="wpuf-twitter-svg" width="20" height="20" viewBox="0 0 24 24" fill="#1da1f2" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
        </svg>';
        $this->base_url = 'https://twitter.com/';
        $this->username_pattern = '^@?[a-zA-Z0-9_]{1,15}$';
        $this->url_pattern = '/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]{1,15})/';
        $this->max_username_length = 15;
        $this->example_username = '@username';

        // Set standard field properties
        $this->name       = __( 'X (Twitter)', 'wp-user-frontend' );
        $this->input_type = 'twitter_url';
        $this->icon       = 'twitter';
    }

    /**
     * Override the get_field_props to set standard meta key format
     *
     * @return array
     */
    public function get_field_props() {
        $props = parent::get_field_props();
        
        // Set the standardized meta key format for social fields
        $props['name'] = 'wpuf_social_twitter';
        
        return $props;
    }

    /**
     * Override the get_options_settings to make meta key readonly
     *
     * @return array
     */
    public function get_options_settings() {
        $settings = parent::get_options_settings();
        
        // Find the meta key setting and make it readonly
        foreach ($settings as &$setting) {
            if (isset($setting['name']) && $setting['name'] === 'name') {
                $setting['readonly'] = true;
                $setting['help_text'] = __( 'This meta key is automatically set to follow the wpuf_social_{platform} format and cannot be changed', 'wp-user-frontend' );
                break;
            }
        }
        
        return $settings;
    }
}
