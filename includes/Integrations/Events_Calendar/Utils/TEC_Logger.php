<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Utils;

/**
 * TEC Logger Utility
 *
 * Provides consistent error logging for Events Calendar integration
 *
 * @since WPUF_SINCE
 */
class TEC_Logger {

    /**
     * Log a message
     *
     * @since WPUF_SINCE
     *
     * @param string $message
     * @param string $level
     */
    public function log( $message, $level = 'info' ) {
        $log_message = sprintf( '[WPUF TEC Integration] %s: %s', strtoupper( $level ), $message );

        switch ( $level ) {
            case 'error':
                error_log( $log_message );
                break;
            case 'warning':
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( $log_message );
                }
                break;
            case 'debug':
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( $log_message );
                }
                break;
            case 'info':
            default:
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( $log_message );
                }
                break;
        }
    }

    /**
     * Log an error message
     *
     * @since WPUF_SINCE
     *
     * @param string $message
     */
    public function error( $message ) {
        $this->log( $message, 'error' );
    }

    /**
     * Log a warning message
     *
     * @since WPUF_SINCE
     *
     * @param string $message
     */
    public function warning( $message ) {
        $this->log( $message, 'warning' );
    }

    /**
     * Log a debug message
     *
     * @since WPUF_SINCE
     *
     * @param string $message
     */
    public function debug( $message ) {
        $this->log( $message, 'debug' );
    }

    /**
     * Log an info message
     *
     * @since WPUF_SINCE
     *
     * @param string $message
     */
    public function info( $message ) {
        $this->log( $message, 'info' );
    }
} 