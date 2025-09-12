<?php
/**
 * WPUF Icon Loader - Dynamically loads Font Awesome SVG icons
 *
 * @since 4.2.0
 */
class WPUF_Icon_Loader {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Get instance
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
     * Load all Font Awesome SVG icons
     */
    public function load_all_icons() {
        $icons = [];
        $svg_base_path = WPUF_ASSET_URI . '/vendor/font-awesome-7/svgs/';
        $svg_file_path = WPUF_ROOT . '/assets/vendor/font-awesome-7/svgs/';
        
        // Define icon styles to scan
        $styles = [
            'solid' => 'fas',
            'regular' => 'far', 
            'brands' => 'fab'
        ];
        
        foreach ( $styles as $style_dir => $style_prefix ) {
            $style_path = $svg_file_path . $style_dir . '/';
            
            if ( ! is_dir( $style_path ) ) {
                continue;
            }
            
            $svg_files = glob( $style_path . '*.svg' );
            
            foreach ( $svg_files as $svg_file ) {
                $filename = basename( $svg_file, '.svg' );
                $icon_name = $this->format_icon_name( $filename );
                $class_name = $style_prefix . ' fa-' . $filename;
                
                // Read SVG content
                $svg_content = file_get_contents( $svg_file );
                
                if ( $svg_content ) {
                    $icons[] = [
                        'class' => $class_name,
                        'name' => $icon_name,
                        'filename' => $filename,
                        'style' => $style_dir,
                        'svg' => $svg_content,
                        'url' => $svg_base_path . $style_dir . '/' . $filename . '.svg'
                    ];
                }
            }
        }
        
        // Sort icons alphabetically by name
        usort( $icons, function( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        });
        
        return $icons;
    }
    
    /**
     * Format icon filename to readable name
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
}

// Initialize
WPUF_Icon_Loader::get_instance();