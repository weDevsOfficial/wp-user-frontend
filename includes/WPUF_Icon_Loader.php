<?php

namespace WeDevs\Wpuf;

/**
 * Icon Loader - Dynamically loads Font Awesome SVG icons
 *
 * @since 4.2.0
 */
class WPUF_Icon_Loader {

    /**
     * Instance
     *
     * @var WPUF_Icon_Loader|null
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return WPUF_Icon_Loader
     */
    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_wpuf_get_font_awesome_icons', [ $this, 'ajax_get_icons' ] );
    }

    /**
     * AJAX handler to get all Font Awesome icons
     *
     * @return void
     */
    public function ajax_get_icons() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'wpuf_form_builder_wpuf_forms' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        $icons = $this->load_all_icons();

        wp_send_json_success([
            'icons' => $icons,
            'total' => count( $icons )
        ]);
    }

    /**
     * Load all Font Awesome icons from predefined list
     *
     * @return array
     */
    public function load_all_icons() {
        // Return basic icon list since we're using CDN
        $icons = $this->get_common_icons();

        // Sort icons alphabetically by name
        usort( $icons, function( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        });

        return $icons;
    }

    /**
     * Get commonly used Font Awesome icons
     *
     * @return array
     */
    private function get_common_icons() {
        return [
            ['class' => 'fas fa-home', 'name' => 'Home', 'filename' => 'home', 'style' => 'solid'],
            ['class' => 'fas fa-user', 'name' => 'User', 'filename' => 'user', 'style' => 'solid'],
            ['class' => 'fas fa-envelope', 'name' => 'Envelope', 'filename' => 'envelope', 'style' => 'solid'],
            ['class' => 'fas fa-phone', 'name' => 'Phone', 'filename' => 'phone', 'style' => 'solid'],
            ['class' => 'fas fa-calendar', 'name' => 'Calendar', 'filename' => 'calendar', 'style' => 'solid'],
            ['class' => 'fas fa-image', 'name' => 'Image', 'filename' => 'image', 'style' => 'solid'],
            ['class' => 'fas fa-file', 'name' => 'File', 'filename' => 'file', 'style' => 'solid'],
            ['class' => 'fas fa-star', 'name' => 'Star', 'filename' => 'star', 'style' => 'solid'],
            ['class' => 'fas fa-heart', 'name' => 'Heart', 'filename' => 'heart', 'style' => 'solid'],
            ['class' => 'fas fa-search', 'name' => 'Search', 'filename' => 'search', 'style' => 'solid'],
            ['class' => 'fas fa-plus', 'name' => 'Plus', 'filename' => 'plus', 'style' => 'solid'],
            ['class' => 'fas fa-minus', 'name' => 'Minus', 'filename' => 'minus', 'style' => 'solid'],
            ['class' => 'fas fa-edit', 'name' => 'Edit', 'filename' => 'edit', 'style' => 'solid'],
            ['class' => 'fas fa-trash', 'name' => 'Trash', 'filename' => 'trash', 'style' => 'solid'],
            ['class' => 'fas fa-download', 'name' => 'Download', 'filename' => 'download', 'style' => 'solid'],
            ['class' => 'fas fa-upload', 'name' => 'Upload', 'filename' => 'upload', 'style' => 'solid'],
            ['class' => 'fas fa-link', 'name' => 'Link', 'filename' => 'link', 'style' => 'solid'],
            ['class' => 'fas fa-lock', 'name' => 'Lock', 'filename' => 'lock', 'style' => 'solid'],
            ['class' => 'fas fa-unlock', 'name' => 'Unlock', 'filename' => 'unlock', 'style' => 'solid'],
            ['class' => 'fas fa-cog', 'name' => 'Settings', 'filename' => 'cog', 'style' => 'solid'],
            ['class' => 'fas fa-check', 'name' => 'Check', 'filename' => 'check', 'style' => 'solid'],
            ['class' => 'fas fa-times', 'name' => 'Times', 'filename' => 'times', 'style' => 'solid'],
            ['class' => 'fas fa-arrow-up', 'name' => 'Arrow Up', 'filename' => 'arrow-up', 'style' => 'solid'],
            ['class' => 'fas fa-arrow-down', 'name' => 'Arrow Down', 'filename' => 'arrow-down', 'style' => 'solid'],
            ['class' => 'fas fa-arrow-left', 'name' => 'Arrow Left', 'filename' => 'arrow-left', 'style' => 'solid'],
            ['class' => 'fas fa-arrow-right', 'name' => 'Arrow Right', 'filename' => 'arrow-right', 'style' => 'solid'],
            ['class' => 'fab fa-facebook', 'name' => 'Facebook', 'filename' => 'facebook', 'style' => 'brands'],
            ['class' => 'fab fa-twitter', 'name' => 'Twitter', 'filename' => 'twitter', 'style' => 'brands'],
            ['class' => 'fab fa-instagram', 'name' => 'Instagram', 'filename' => 'instagram', 'style' => 'brands'],
            ['class' => 'fab fa-linkedin', 'name' => 'LinkedIn', 'filename' => 'linkedin', 'style' => 'brands'],
        ];
    }

    /**
     * Format icon filename to readable name
     *
     * @param string $filename
     * @return string
     */
    private function format_icon_name( $filename ) {
        // Convert kebab-case to Title Case
        $name = str_replace( '-', ' ', $filename );
        $name = ucwords( $name );

        // Handle common abbreviations
        $replacements = [
            'Css' => 'CSS',
            'Html' => 'HTML',
            'Js' => 'JS',
            'Php' => 'PHP',
            'Sql' => 'SQL',
            'Url' => 'URL',
            'Wifi' => 'WiFi',
            'Api' => 'API',
            'Ui' => 'UI',
            'Ux' => 'UX',
            'Db' => 'DB',
            'Id' => 'ID',
            'Tv' => 'TV',
            'Dvd' => 'DVD',
            'Cd' => 'CD',
            'Usb' => 'USB',
            'Gps' => 'GPS',
            'Fax' => 'FAX',
            'Pdf' => 'PDF',
            'Gif' => 'GIF',
            'Jpg' => 'JPG',
            'Png' => 'PNG',
            'Mp3' => 'MP3',
            'Mp4' => 'MP4',
            'Avi' => 'AVI'
        ];

        foreach ( $replacements as $search => $replace ) {
            $name = str_replace( $search, $replace, $name );
        }

        return $name;
    }

    /**
     * Initialize the Icon Loader
     *
     * @return void
     */
    public static function init() {
        // Only initialize on admin pages or AJAX requests
        if ( is_admin() || wp_doing_ajax() ) {
            self::get_instance();
        }
    }
}