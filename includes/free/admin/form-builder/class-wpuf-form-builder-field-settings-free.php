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

}
