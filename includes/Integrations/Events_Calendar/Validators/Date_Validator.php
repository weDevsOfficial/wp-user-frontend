<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Validators;

/**
 * Date Validator Class
 * 
 * Handles comprehensive date/time validation for Events Calendar
 * 
 * @since 3.6.0
 */
class Date_Validator {

    /**
     * Supported date formats
     *
     * @var array
     */
    private $supported_formats = [
        'Y-m-d',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'd/m/Y',
        'm/d/Y',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP'
    ];

    /**
     * Validate complete date/time data
     *
     * @param array $date_data Date/time data to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_date_data( $date_data ) {
        $errors = [];

        // Validate date format
        $format_errors = $this->validate_date_format( $date_data );
        if ( !empty( $format_errors ) ) {
            $errors = array_merge( $errors, $format_errors );
        }

        // Validate timezone
        $timezone_errors = $this->validate_timezone( $date_data );
        if ( !empty( $timezone_errors ) ) {
            $errors = array_merge( $errors, $timezone_errors );
        }

        // Validate date range
        $range_errors = $this->validate_date_range( $date_data );
        if ( !empty( $range_errors ) ) {
            $errors = array_merge( $errors, $range_errors );
        }

        // Validate all-day event
        $allday_errors = $this->validate_all_day_event( $date_data );
        if ( !empty( $allday_errors ) ) {
            $errors = array_merge( $errors, $allday_errors );
        }

        if ( !empty( $errors ) ) {
            return $errors;
        }

        return true;
    }

    /**
     * Validate date format
     *
     * @param array $date_data Date data
     * @return array Array of errors
     */
    private function validate_date_format( $date_data ) {
        $errors = [];

        // Validate start date
        if ( isset( $date_data['EventStartDate'] ) ) {
            $start_date = $date_data['EventStartDate'];
            if ( !$this->is_valid_date_format( $start_date ) ) {
                $errors[] = __( 'Event start date format is not valid.', 'wp-user-frontend' );
            }
        }

        // Validate end date
        if ( isset( $date_data['EventEndDate'] ) ) {
            $end_date = $date_data['EventEndDate'];
            if ( !$this->is_valid_date_format( $end_date ) ) {
                $errors[] = __( 'Event end date format is not valid.', 'wp-user-frontend' );
            }
        }

        // Validate time values
        $time_fields = [
            'EventStartHour', 'EventStartMinute',
            'EventEndHour', 'EventEndMinute'
        ];

        foreach ( $time_fields as $field ) {
            if ( isset( $date_data[ $field ] ) ) {
                $value = $date_data[ $field ];
                if ( !$this->is_valid_time_value( $value, $field ) ) {
                    $errors[] = sprintf(
                        // translators: %s is the field name (e.g., StartTime, EndTime)
                        __( 'Time field "%s" is not valid.', 'wp-user-frontend' ),
                        $field
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Validate timezone
     *
     * @param array $date_data Date data
     * @return array Array of errors
     */
    private function validate_timezone( $date_data ) {
        $errors = [];

        // Check if timezone is provided
        if ( isset( $date_data['EventTimezone'] ) && !empty( $date_data['EventTimezone'] ) ) {
            $timezone = $date_data['EventTimezone'];
            
            if ( !$this->is_valid_timezone( $timezone ) ) {
                $errors[] = __( 'Event timezone is not valid.', 'wp-user-frontend' );
            }
        }

        return $errors;
    }

    /**
     * Validate date range
     *
     * @param array $date_data Date data
     * @return array Array of errors
     */
    private function validate_date_range( $date_data ) {
        $errors = [];

        // Check if we have both start and end dates
        if ( !isset( $date_data['EventStartDate'] ) || !isset( $date_data['EventEndDate'] ) ) {
            return $errors;
        }

        $start_date = $date_data['EventStartDate'];
        $end_date = $date_data['EventEndDate'];

        // Parse dates
        $start_datetime = $this->parse_date( $start_date );
        $end_datetime = $this->parse_date( $end_date );

        if ( !$start_datetime || !$end_datetime ) {
            $errors[] = __( 'Unable to parse event dates.', 'wp-user-frontend' );
            return $errors;
        }

        // Check if end date is after start date
        if ( $end_datetime <= $start_datetime ) {
            $errors[] = __( 'Event end date must be after start date.', 'wp-user-frontend' );
        }

        // Check if event is in the past (optional validation)
        if ( $this->should_validate_past_dates() ) {
            $now = new \DateTime();
            if ( $start_datetime < $now ) {
                $errors[] = __( 'Event start date cannot be in the past.', 'wp-user-frontend' );
            }
        }

        // Check if event duration is reasonable (optional validation)
        $duration = $end_datetime->diff( $start_datetime );
        if ( $duration->days > 365 ) {
            $errors[] = __( 'Event duration cannot exceed one year.', 'wp-user-frontend' );
        }

        return $errors;
    }

    /**
     * Validate all-day event
     *
     * @param array $date_data Date data
     * @return array Array of errors
     */
    private function validate_all_day_event( $date_data ) {
        $errors = [];

        // Check if this is an all-day event
        if ( isset( $date_data['EventAllDay'] ) && $date_data['EventAllDay'] === 'yes' ) {
            // For all-day events, time should be 00:00 or not specified
            $time_fields = [
                'EventStartHour', 'EventStartMinute',
                'EventEndHour', 'EventEndMinute'
            ];

            foreach ( $time_fields as $field ) {
                if ( isset( $date_data[ $field ] ) && $date_data[ $field ] !== '0' && $date_data[ $field ] !== 0 ) {
                    $errors[] = __( 'All-day events should not have specific time values.', 'wp-user-frontend' );
                    break;
                }
            }

            // For all-day events, start and end dates should be the same or consecutive
            if ( isset( $date_data['EventStartDate'] ) && isset( $date_data['EventEndDate'] ) ) {
                $start_date = $this->parse_date( $date_data['EventStartDate'] );
                $end_date = $this->parse_date( $date_data['EventEndDate'] );

                if ( $start_date && $end_date ) {
                    $diff = $start_date->diff( $end_date );
                    if ( $diff->days > 1 ) {
                        $errors[] = __( 'All-day events should not span more than one day.', 'wp-user-frontend' );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Check if a date string has valid format
     *
     * @param string $date Date string
     * @return bool True if valid
     */
    private function is_valid_date_format( $date ) {
        if ( empty( $date ) ) {
            return false;
        }

        // Try to parse with supported formats
        foreach ( $this->supported_formats as $format ) {
            $parsed = \DateTime::createFromFormat( $format, $date );
            if ( $parsed !== false ) {
                return true;
            }
        }

        // Try standard strtotime as fallback
        $timestamp = strtotime( $date );
        return $timestamp !== false;
    }

    /**
     * Check if a time value is valid
     *
     * @param mixed $value Time value
     * @param string $field Field name
     * @return bool True if valid
     */
    private function is_valid_time_value( $value, $field ) {
        if ( !is_numeric( $value ) ) {
            return false;
        }

        $value = (int) $value;

        // Validate hour fields
        if ( strpos( $field, 'Hour' ) !== false ) {
            return $value >= 0 && $value <= 23;
        }

        // Validate minute fields
        if ( strpos( $field, 'Minute' ) !== false ) {
            return $value >= 0 && $value <= 59;
        }

        return false;
    }

    /**
     * Check if a timezone is valid
     *
     * @param string $timezone Timezone string
     * @return bool True if valid
     */
    private function is_valid_timezone( $timezone ) {
        return in_array( $timezone, \DateTimeZone::listIdentifiers() );
    }

    /**
     * Parse a date string into DateTime object
     *
     * @param string $date Date string
     * @return \DateTime|false DateTime object or false on failure
     */
    private function parse_date( $date ) {
        if ( empty( $date ) ) {
            return false;
        }

        // Try supported formats first
        foreach ( $this->supported_formats as $format ) {
            $parsed = \DateTime::createFromFormat( $format, $date );
            if ( $parsed !== false ) {
                return $parsed;
            }
        }

        // Try standard strtotime as fallback
        $timestamp = strtotime( $date );
        if ( $timestamp !== false ) {
            return new \DateTime( '@' . $timestamp );
        }

        return false;
    }

    /**
     * Check if we should validate past dates
     *
     * @return bool True if past dates should be validated
     */
    private function should_validate_past_dates() {
        // This can be made configurable via settings
        return apply_filters( 'wpuf_tec_validate_past_dates', false );
    }

    /**
     * Validate a specific date field
     *
     * @param array $date_data Date data
     * @param string $field Field name
     * @return array Array of errors
     */
    public function validate_field( $date_data, $field ) {
        $errors = [];

        switch ( $field ) {
            case 'EventStartDate':
            case 'EventEndDate':
                if ( !isset( $date_data[ $field ] ) || !$this->is_valid_date_format( $date_data[ $field ] ) ) {
                    $errors[] = sprintf( 
                        // translators: %s is the field name (e.g., StartDate, EndDate)
                        __( 'Event %s format is not valid.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', str_replace( 'Date', '', $field ) )
                    );
                }
                break;

            case 'EventStartHour':
            case 'EventEndHour':
                if ( isset( $date_data[ $field ] ) && !$this->is_valid_time_value( $date_data[ $field ], $field ) ) {                  
                    $errors[] = sprintf( 
                        // translators: %s is the field name (e.g., StartHour, EndHour)
                        __( 'Event %s must be between 0 and 23.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                }
                break;

            case 'EventStartMinute':
            case 'EventEndMinute':
                if ( isset( $date_data[ $field ] ) && !$this->is_valid_time_value( $date_data[ $field ], $field ) ) {
                    $errors[] = sprintf(
                        // translators: %s is the field name (e.g., StartMinute, EndMinute)
                        __( 'Event %s must be between 0 and 59.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                }
                break;

            case 'EventTimezone':
                if ( isset( $date_data[ $field ] ) && !empty( $date_data[ $field ] ) && !$this->is_valid_timezone( $date_data[ $field ] ) ) {
                    $errors[] = __( 'Event timezone is not valid.', 'wp-user-frontend' );
                }
                break;

            case 'EventAllDay':
                if ( isset( $date_data[ $field ] ) && !in_array( $date_data[ $field ], [ 'yes', 'no' ] ) ) {
                    $errors[] = __( 'Event all-day field must be "yes" or "no".', 'wp-user-frontend' );
                }
                break;
        }

        return $errors;
    }

    /**
     * Get supported date formats
     *
     * @return array Array of supported formats
     */
    public function get_supported_formats() {
        return $this->supported_formats;
    }

    /**
     * Add a custom date format
     *
     * @param string $format Date format
     * @return void
     */
    public function add_supported_format( $format ) {
        if ( !in_array( $format, $this->supported_formats ) ) {
            $this->supported_formats[] = $format;
        }
    }

    /**
     * Convert date to WordPress timezone
     *
     * @param string $date Date string
     * @param string $timezone Source timezone
     * @return \DateTime|false DateTime object or false on failure
     */
    public function convert_to_wp_timezone( $date, $timezone = null ) {
        $datetime = $this->parse_date( $date );
        
        if ( !$datetime ) {
            return false;
        }

        // If no timezone specified, use WordPress timezone
        if ( !$timezone ) {
            $timezone = wp_timezone_string();
        }

        // Set the timezone
        $wp_timezone = new \DateTimeZone( $timezone );
        $datetime->setTimezone( $wp_timezone );

        return $datetime;
    }

    /**
     * Format date for display
     *
     * @param string $date Date string
     * @param string $format Display format
     * @param string $timezone Timezone
     * @return string|false Formatted date or false on failure
     */
    public function format_date_for_display( $date, $format = null, $timezone = null ) {
        $datetime = $this->convert_to_wp_timezone( $date, $timezone );
        
        if ( !$datetime ) {
            return false;
        }

        // Use WordPress date format if none specified
        if ( !$format ) {
            $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
        }

        return $datetime->format( $format );
    }
} 