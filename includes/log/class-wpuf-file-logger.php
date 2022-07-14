<?php
/**
 * Provides logging capabilities for debugging purposes.
 *
 * @since wpuf
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPUF_File_Logger class.
 *
 * This logger writes to a PHP stream resource.
 */
class WPUF_File_Logger {

    /**
     * Default log format
     */
    const DEFAULT_LOG_FORMAT = "[%datetime%] %level%: %message%\n";

    /**
     * Default date format
     *
     * Example: 16/Nov/2014:03:26:16 -0500
     */
    const DEFAULT_DATE_FORMAT = 'd/M/Y:H:i:s O';

    /**
     * System is unusable
     */
    const EMERGENCY = 'emergency';

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger SMS/E-mail alerts and wake you up.
     */
    const ALERT = 'alert';

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 'critical';

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    const ERROR = 'error';

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    const WARNING = 'warning';

    /**
     * Normal but significant events.
     */
    const NOTICE = 'notice';

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    const INFO = 'info';

    /**
     * Detailed debug information.
     */
    const DEBUG = 'debug';

    /**
     * @var string|resource $file Where logs are written
     */
    private $file;

    /**
     * @var string $level The log level
     */
    private $level = self::INFO;

    /**
     * @var string $log_directory Where logs are stored
     */
    private $log_directory;

    /**
     * @var integer $mode The mode to use if the log file needs to be created
     */
    private $mode = 0640;

    /**
     * Cache logs that could not be written yet.
     *
     * If a log is written too early in the request, pluggable functions may be unavailable. These
     * logs will be cached and written on 'plugins_loaded' action.
     *
     * @var array
     */
    protected $cached_logs = [];

    /**
     * The logger constructor. Initialize the default file and log_directory
     *
     * @since WPUF
     *
     * @return void
     */
    public function __construct( $level = null, $file = null ) {
        if ( null !== $level ) {
            $this->level = $level;
        } else {
            $this->level = self::INFO;
        }

        if ( null !== $file ) {
            $this->file = $file;
        } else {
            $this->file = $this->get_log_file_name();
        }

        $upload_dir = wp_get_upload_dir();

        $this->log_directory = apply_filters( 'wpuf_log_directory', $upload_dir['basedir'] . '/wpuf-logs' );

        add_action( 'plugins_loaded', [ $this, 'write_cached_logs' ] );
    }

    /**
     * Get a log file name.
     * Log file name, followed by the date, followed by a hash, .log.
     *
     * @since WPUF
     *
     * @return bool|string The log file name or false if cannot be determined.
     */
    public function get_log_file_name() {
        if ( function_exists( 'wp_hash' ) ) {
            $date_suffix = date( 'Y-M-d', time() );
            $hash_suffix = wp_hash( $this->level );

            $log_file_name = apply_filters( 'wpuf_log_file_name', sanitize_file_name( implode( '-', [ $this->level, $date_suffix, $hash_suffix ] ) . '.log' ) );
            return $log_file_name;
        } else {
            _doing_it_wrong( __METHOD__, __( 'This method should not be called before plugins_loaded.', 'wp-user-frontend' ), WPUF_VERSION );
            return false;
        }
    }

    /**
     * Process the message and logs it int the logfile.
     *
     * @since WPUF
     *
     * @param $message string               The message to log
     * @param $level   string               The log level. Default INFO
     * @param $log_file string|resource     Optional parameter to pass the log file.
     *                                      Log will be written in this file instead of the default log file.
     *
     * @return bool
     */
    public function log( $message, $level = self::INFO, $log_file = null ) {
        if ( null !== $log_file ) {
            $this->file = $log_file;
            $this->open();
        }

        $message = $this->format_message(
            [
                'message'  => $message,
                'level'    => strtoupper( $level ),
                'datetime' => new DateTime(),
            ]
        );

        return $this->write( $message );
    }

    /**
     * Transform log variables into the defined log format.
     *
     * @since WPUF
     *
     * @param  array $variables The log variables.
     *
     * @return string
     */
    protected function format_message( $variables ) {
        $template = self::DEFAULT_LOG_FORMAT;

        foreach ( $variables as $key => $value ) {
            if ( strpos( $template, '%' . $key . '%' ) !== false ) {
                $template = str_replace(
                    '%' . $key . '%',
                    $this->export( $value ),
                    $template
                );
            }
        }

        return apply_filters( 'wpuf_formatted_log_message', $template );
    }

    /**
     * Exports a PHP value for logging to a string.
     *
     * @since WPUF
     *
     * @param mixed $value The value to
     *
     * @return bool
     */
    protected function export( $value ) {
        if ( $value instanceof DateTime ) {
            return $value->format( self::DEFAULT_DATE_FORMAT );
        }

        if ( is_string( $value ) ) {
            return preg_replace( '/[\r\n]+/', ' ', $value );
        }

        return false;
    }

    /**
     * Write the log message to the log file
     *
     * @since WPUF
     *
     * @param $message
     *
     * @return bool
     */
    protected function write( $message, $level = self::INFO ) {
        if ( is_string( $this->file ) ) {
            $this->open();
        }

        // file is not yet ready to write, cached for later
        if ( ! is_resource( $this->file ) ) {
            $this->cache_log( $message, $level );

            return true;
        }

        fwrite( $this->file, (string) $message ); // @codingStandardsIgnoreLine.

        return true;
    }

    /**
     * Opens the log for writing.
     *
     * @since WPUF
     *
     * @return false|resource|string
     */
    private function open() {
        $needs_chmod = ! file_exists( $this->file );

        // create the log directory if not exists
        if ( ! is_dir( $this->log_directory ) ) {
            wp_mkdir_p( $this->log_directory );
            $index_file = $this->log_directory . '/index.php';
            // create a blank index file
            $index_file = @fopen( $index_file, 'a' ); // @codingStandardsIgnoreLine.
            fclose( $index_file ); // @codingStandardsIgnoreLine.
        }

        // open or create the logfile
        $fh = @fopen( $this->log_directory . '/' . $this->file, 'a' ); // @codingStandardsIgnoreLine.

        if ( $needs_chmod ) {
            @chmod( $this->file, $this->mode & ~umask() ); // @codingStandardsIgnoreLine.
        }

        $this->file = $fh;

        return $this->file;
    }

    /**
     * Closes the log stream resource.
     *
     * @since WPUF
     *
     * @return void
     */
    private function close() {
        if ( is_resource( $this->file ) ) {
            fclose( $this->file ); // @codingStandardsIgnoreLine.
        }
    }

    /**
     * Cache log to write later.
     *
     * @since WPUF
     *
     * @param string $message Log entry text.
     * @param string $level   Log entry level.
     *
     * @return void
     */
    protected function cache_log( $message, $level ) {
        $this->cached_logs[] = [
            'message' => $message,
            'level'   => $level,
        ];
    }

    /**
     * Write cached logs. If a log is written too early in the request, pluggable functions may be unavailable.
     * These logs will be cached and written on 'plugins_loaded' action.
     *
     * @since WPUF
     *
     * @return void
     */
    public function write_cached_logs() {
        foreach ( $this->cached_logs as $log ) {
            $this->log( $log['message'], $log['level'] );
        }
    }

    /**
     * Delete all logs older than a defined timestamp.
     *
     * @since WPUF
     *
     * @param integer $timestamp Timestamp to delete logs before.
     *
     * @return void
     */
    public function delete_logs_before_timestamp( $timestamp = 0 ) {
        if ( ! $timestamp ) {
            return;
        }

        $log_files = $this->get_log_files();

        foreach ( $log_files as $log_file ) {
            $last_modified = filemtime( trailingslashit( $this->log_directory ) . $log_file );

            if ( $last_modified < $timestamp ) {
                @unlink( trailingslashit( $this->log_directory ) . $log_file ); // @codingStandardsIgnoreLine.
            }
        }
    }

    /**
     * Clear all logs older than a defined number of days. Defaults to 60 days.
     *
     * @since WPUF
     *
     * @return null
     */
    public function clear_expired_logs() {
        $days      = absint( apply_filters( 'wpuf_logger_expiration_days', 60 ) );
        $timestamp = strtotime( "-{$days} days" );

        $this->delete_logs_before_timestamp( $timestamp );
    }

    /**
     * Get all log files in the log directory.
     *
     * @since wpuf
     *
     * @return array
     */
    public function get_log_files() {
        $files  = @scandir( $this->log_directory ); // @codingStandardsIgnoreLine.
        $result = array();

        if ( ! empty( $files ) ) {
            foreach ( $files as $key => $value ) {
                if ( ! in_array( $value, array( '.', '..' ), true ) ) {
                    if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
                        $result[ sanitize_title( $value ) ] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Close the file when the object is destructed or the script is stopped or exited.
     *
     * @since WPUF
     *
     * @return void
     */
    public function __destruct() {
        $this->close();
    }
}
