<?php

namespace WeDevs\Wpuf\Lib\Appsero;

use WP_Error;

/**
 * Appsero Client
 *
 * This class is necessary to set project data
 */
class Client {

    /**
     * The client version
     *
     * @var string
     */
    public $version = '1.3.0';

    /**
     * Hash identifier of the plugin
     *
     * @var string
     */
    public $hash;

    /**
     * Name of the plugin
     *
     * @var string
     */
    public $name;

    /**
     * The plugin/theme file path
     *
     * @example .../wp-content/plugins/test-slug/test-slug.php
     *
     * @var string
     */
    public $file;

    /**
     * Main plugin file
     *
     * @example test-slug/test-slug.php
     *
     * @var string
     */
    public $basename;

    /**
     * Slug of the plugin
     *
     * @example test-slug
     *
     * @var string
     */
    public $slug;

    /**
     * The project version
     *
     * @var string
     */
    public $project_version;

    /**
     * The project type
     *
     * @var string
     */
    public $type;

    /**
     * Textdomain
     *
     * @var string
     */
    public $textdomain;

    /**
     * The Object of Insights Class
     *
     * @var object
     */
    public $insights;

    /**
     * The Object of License Class
     *
     * @var object
     */
    private $license;

    /**
     * Initialize the class
     *
     * @param string $hash hash of the plugin
     * @param string $name readable name of the plugin
     * @param string $file main plugin file path
     */
    public function __construct( $hash, $name, $file ) {
        $this->hash = $hash;
        $this->name = $name;
        $this->file = $file;

        $this->set_basename_and_slug();
    }

    /**
     * Initialize insights class
     *
     * @return object
     */
    public function insights() {
        // if already instantiated, return the cached one
        if ( $this->insights ) {
            return $this->insights;
        }

        $this->insights = new Insights( $this );

        return $this->insights;
    }

    /**
     * Initialize license checker
     *
     * @return object
     */
    public function license() {
        // if already instantiated, return the cached one
        if ( $this->license ) {
            return $this->license;
        }

        $this->license = new License( $this );

        return $this->license;
    }

    /**
     * API Endpoint
     *
     * @return string
     */
    public function endpoint() {
        $endpoint = apply_filters( 'appsero_endpoint', 'https://api.appsero.com' );

        return trailingslashit( $endpoint );
    }

    /**
     * Set project basename, slug and version
     *
     * @return void
     */
    protected function set_basename_and_slug() {
        if ( strpos( $this->file, WP_CONTENT_DIR . '/themes/' ) === false ) {
            $this->basename = plugin_basename( $this->file );

            list( $this->slug, $mainfile ) = explode( '/', $this->basename );

            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $plugin_data = get_plugin_data( $this->file );

            $this->project_version = $plugin_data['Version'];
            $this->type            = 'plugin';
        } else {
            $this->basename = str_replace( WP_CONTENT_DIR . '/themes/', '', $this->file );

            list( $this->slug, $mainfile ) = explode( '/', $this->basename );

            $theme = wp_get_theme( $this->slug );

            $this->project_version = $theme->version;
            $this->type            = 'theme';
        }

        $this->textdomain = $this->slug;
    }

    /**
     * Send request to remote endpoint
     *
     * @param array  $params
     * @param string $route
     *
     * @return array|WP_Error array of results including HTTP headers or WP_Error if the request failed
     */
    public function send_request( $params, $route, $blocking = false ) {
        $url = $this->endpoint() . $route;

        $headers = [
            'user-agent' => 'Appsero/' . md5( esc_url( home_url() ) ) . ';',
            'Accept'     => 'application/json',
        ];

        $response = wp_remote_post(
            $url,
            [
                'method'      => 'POST',
                'timeout'     => 30,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => $blocking,
                'headers'     => $headers,
                'body'        => array_merge( $params, [ 'client' => $this->version ] ),
                'cookies'     => [],
            ]
        );

        return $response;
    }

    /**
     * Check if the current server is localhost
     *
     * @return bool
     */
    public function is_local_server() {
        $is_local = isset( $_SERVER['REMOTE_ADDR'] ) && in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ], true );

        return apply_filters( 'appsero_is_local', $is_local );
    }

    /**
     * Translate function _e()
     */
    // phpcs:ignore
    public function _etrans( $text ) {
        call_user_func( '_e', $text, $this->textdomain );
    }

    /**
     * Translate function __()
     */
    // phpcs:ignore
    public function __trans( $text ) {
        return call_user_func( '__', $text, $this->textdomain );
    }

    /**
     * Set project textdomain
     */
    public function set_textdomain( $textdomain ) {
        $this->textdomain = $textdomain;
    }
}
