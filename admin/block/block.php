<?php

class Block_Integration {
    private $container = [];

    public function __construct() {
        $this->define_constant();
        $this->include_files();
        $this->instantiate();
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ], 10 );
    }

    private function define_constant() {
        define( 'WPUF_ELEMENTOR_ASSETS', __DIR__ . '/assets' );
    }

    private function include_files() {
        require_once __DIR__ . '/includes/shortcode.php';
    }

    public function enqueue_assets() {
        $dependencies = include_once plugin_dir_path( __FILE__ ) . 'dist/blocks.asset.php';
        wp_register_script( 'wpuf-blocks', plugin_dir_url( __FILE__ ) . 'dist/blocks.js', $dependencies['dependencies'], $dependencies['version'], true );
        $this->shortcode_block->localize_object();
        wp_enqueue_script( 'wpuf-blocks' );
    }

    private function instantiate() {
        $this->container['shortcode_block'] = new Shortcode_Block();
    }

    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }
}

