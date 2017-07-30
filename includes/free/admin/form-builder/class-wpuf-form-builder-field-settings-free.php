<?php
/**
 * Field Settings
 *
 * @since 2.5
 */
class WPUF_Form_Builder_Field_Settings_Free extends WPUF_Form_Builder_Field_Settings {

    /**
     * Pro field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_field_settings() {
        return array(
            'repeat_field'          => self::repeat_field(),
            'date_field'            => self::date_field(),
            'file_upload'           => self::file_upload(),
            'country_list_field'    => self::country_list_field(),
            'numeric_text_field'    => self::numeric_text_field(),
            'address_field'         => self::address_field(),
            'step_start'            => self::step_start(),
            'google_map'            => self::google_map(),
            'shortcode'             => self::shortcode(),
            'really_simple_captcha' => self::really_simple_captcha(),
            'action_hook'           => self::action_hook(),
            'toc'                   => self::toc(),
            'ratings'               => self::ratings(),
        );
    }

    /**
     * wpuf_cond option field settings
     *
     * This is for sidebar panel, not in builder stage
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_field_wpuf_cond() {
        return array(
            'name'           => 'wpuf_cond',
            'title'          => __( 'Conditional Logic', 'wpuf' ),
            'type'           => 'option-pro-feature-alert',
            'section'        => 'advanced',
            'priority'       => 30,
            'help_text'      => '',
            'is_pro_feature' => true
        );
    }

    /**
     * Repeatable field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function repeat_field() {
        return array(
            'template'      => 'repeat_field',
            'title'         => __( 'Repeat Field', 'wpuf' ),
            'icon'          => 'clone',
            'pro_feature'   => true,
        );
    }

    /**
     * Date Field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function date_field() {
        return array(
            'template'      => 'date_field',
            'title'         => __( 'Date / Time', 'wpuf' ),
            'icon'          => 'calendar-o',
            'pro_feature'   => true
        );
    }

    /**
     * File upload field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function file_upload() {
        return array(
            'template'      => 'file_upload',
            'title'         => __( 'File Upload', 'wpuf' ),
            'icon'          => 'upload',
            'pro_feature'   => true
        );
    }

    /**
     * Country list field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function country_list_field() {
        return array(
            'template'      => 'country_list_field',
            'title'         => __( 'Country List', 'wpuf' ),
            'icon'          => 'globe',
            'pro_feature'   => true
        );
    }

    /**
     * Numeric text field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function numeric_text_field() {
        return array(
            'template'      => 'numeric_text_field',
            'title'         => __( 'Numeric Field', 'wpuf' ),
            'icon'          => 'hashtag',
            'pro_feature'   => true
        );
    }

    /**
     * Address field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function address_field() {
        return array(
            'template'      => 'address_field',
            'title'         => __( 'Address Field', 'wpuf' ),
            'icon'          => 'address-card-o',
            'pro_feature'   => true,
        );
    }

    /**
     * Step Start field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function step_start() {
        return array(
            'template'      => 'step_start',
            'title'         => __( 'Step Start', 'wpuf' ),
            'icon'          => 'step-forward',
            'pro_feature'   => true,
            'is_full_width' => true,
        );
    }


    /**
     * Google Map
     *
     * @since 2.5
     *
     * @return array
     */
    public static function google_map() {
        return array(
            'template'      => 'google_map',
            'title'         => __( 'Google Map', 'wpuf' ),
            'icon'          => 'map-marker',
            'pro_feature'   => true,
        );
    }

    /**
     * Shortcode
     *
     * @since 2.5.4
     *
     * @return array
     */
    public static function shortcode() {
        return array(
            'template'      => 'shortcode',
            'title'         => __( 'Shortcode', 'wpuf' ),
            'pro_feature'   => true,
        );
    }

    /**
     * Really Simple Captcha
     *
     * @since 2.5
     *
     * @return array
     */
    public static function really_simple_captcha() {
        return array(
            'template'      => 'really_simple_captcha',
            'title'         => __( 'Really Simple Captcha', 'wpuf' ),
            'pro_feature'   => true,
        );
    }

    /**
     * Action Hook
     *
     * @since 2.5
     *
     * @return array
     */
    public static function action_hook() {
        return array(
            'template'      => 'action_hook',
            'title'         => __( 'Action Hook', 'wpuf' ),
            'pro_feature'   => true,
        );
    }

    /**
     * Term & Conditions
     *
     * @since 2.5
     *
     * @return array
     */
    public static function toc() {
        return array(
            'template'      => 'toc',
            'title'         => __( 'Term & Conditions', 'wpuf' ),
            'pro_feature'   => true,
        );
    }

    /**
     * Ratings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function ratings() {
        return array(
            'template'      => 'ratings',
            'title'         => __( 'Ratings', 'wpuf' ),
            'pro_feature'   => true,
        );
    }

}
