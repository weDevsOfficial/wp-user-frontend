<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Validators;

/**
 * Event Validator Class
 * 
 * Handles comprehensive validation for Events Calendar event data
 * 
 * @since 3.6.0
 */
class Event_Validator {

    /**
     * Required fields for event creation
     *
     * @var array
     */
    private $required_fields = [
        'post_title',
        'EventStartDate',
        'EventEndDate',
        'EventStartHour',
        'EventStartMinute',
        'EventEndHour',
        'EventEndMinute'
    ];

    /**
     * Validate complete event data
     *
     * @param array $event_data Event data to validate
     * @return bool|array True if valid, array of errors if invalid
     */
    public function validate_event_data( $event_data ) {
        $errors = [];

        // Validate required fields
        $required_errors = $this->validate_required_fields( $event_data );
        if ( !empty( $required_errors ) ) {
            $errors = array_merge( $errors, $required_errors );
        }

        // Validate date ranges
        $date_errors = $this->validate_date_ranges( $event_data );
        if ( !empty( $date_errors ) ) {
            $errors = array_merge( $errors, $date_errors );
        }

        // Validate event details
        $detail_errors = $this->validate_event_details( $event_data );
        if ( !empty( $detail_errors ) ) {
            $errors = array_merge( $errors, $detail_errors );
        }

        if ( !empty( $errors ) ) {
            return $errors;
        }

        return true;
    }

    /**
     * Validate required fields
     *
     * @param array $event_data Event data
     * @return array Array of errors
     */
    private function validate_required_fields( $event_data ) {
        $errors = [];

        foreach ( $this->required_fields as $field ) {
            if ( !isset( $event_data[ $field ] ) || empty( $event_data[ $field ] ) ) {
                $errors[] = sprintf(
                    // translators: %s is the field name
                    __( 'Required field "%s" is missing or empty.', 'wp-user-frontend' ),
                    $field
                );
            }
        }

        return $errors;
    }

    /**
     * Validate date ranges
     *
     * @param array $event_data Event data
     * @return array Array of errors
     */
    private function validate_date_ranges( $event_data ) {
        $errors = [];

        // Check if we have the required date fields
        $date_fields = [
            'EventStartDate', 'EventEndDate',
            'EventStartHour', 'EventStartMinute',
            'EventEndHour', 'EventEndMinute'
        ];

        foreach ( $date_fields as $field ) {
            if ( !isset( $event_data[ $field ] ) ) {
                $errors[] = sprintf(
                    // translators: %s is the field name
                    __( 'Date field "%s" is missing.', 'wp-user-frontend' ),
                    $field
                );
            }
        }

        if ( !empty( $errors ) ) {
            return $errors;
        }

        // Validate start date
        $start_date = $event_data['EventStartDate'];
        if ( !$this->is_valid_date( $start_date ) ) {
            $errors[] = __( 'Event start date is not valid.', 'wp-user-frontend' );
        }

        // Validate end date
        $end_date = $event_data['EventEndDate'];
        if ( !$this->is_valid_date( $end_date ) ) {
            $errors[] = __( 'Event end date is not valid.', 'wp-user-frontend' );
        }

        // Validate time values
        $time_fields = [
            'EventStartHour', 'EventStartMinute',
            'EventEndHour', 'EventEndMinute'
        ];

        foreach ( $time_fields as $field ) {
            $value = $event_data[ $field ];
            
            // Determine if this is an hour or minute field
            $is_hour_field = strpos( $field, 'Hour' ) !== false;
            $is_minute_field = strpos( $field, 'Minute' ) !== false;
            
            if ( !is_numeric( $value ) || $value < 0 ) {
                $errors[] = sprintf(
                    // translators: %s is the field name
                    __( 'Time field "%s" must be a valid number.', 'wp-user-frontend' ),
                    $field
                );
            } elseif ( $is_hour_field && $value > 23 ) {
                $errors[] = sprintf(
                    // translators: %s is the field name
                    __( 'Hour field "%s" must be between 0 and 23.', 'wp-user-frontend' ),
                    $field
                );
            } elseif ( $is_minute_field && $value > 59 ) {
                $errors[] = sprintf(
                    // translators: %s is the field name
                    __( 'Minute field "%s" must be between 0 and 59.', 'wp-user-frontend' ),
                    $field
                );
            }
        }

        // Validate that end date/time is after start date/time
        if ( empty( $errors ) ) {
            $start_datetime = $this->build_datetime( $event_data, 'start' );
            $end_datetime = $this->build_datetime( $event_data, 'end' );

            if ( $end_datetime <= $start_datetime ) {
                $errors[] = __( 'Event end date/time must be after start date/time.', 'wp-user-frontend' );
            }
        }

        return $errors;
    }

    /**
     * Validate event details
     *
     * @param array $event_data Event data
     * @return array Array of errors
     */
    private function validate_event_details( $event_data ) {
        $errors = [];

        // Validate event title length
        if ( isset( $event_data['post_title'] ) ) {
            $title = $event_data['post_title'];
            if ( strlen( $title ) > 255 ) {
                $errors[] = __( 'Event title cannot exceed 255 characters.', 'wp-user-frontend' );
            }
        }

        // Validate event description length
        if ( isset( $event_data['post_content'] ) ) {
            $content = $event_data['post_content'];
            if ( strlen( $content ) > 65535 ) {
                $errors[] = __( 'Event description is too long.', 'wp-user-frontend' );
            }
        }

        // Validate cost field
        if ( isset( $event_data['EventCost'] ) && !empty( $event_data['EventCost'] ) ) {
            if ( !is_numeric( $event_data['EventCost'] ) && $event_data['EventCost'] !== 'Free' ) {
                $errors[] = __( 'Event cost must be a number or "Free".', 'wp-user-frontend' );
            }
        }

        // Validate URL field
        if ( isset( $event_data['EventURL'] ) && !empty( $event_data['EventURL'] ) ) {
            if ( !$this->is_valid_url( $event_data['EventURL'] ) ) {
                $errors[] = __( 'Event URL is not valid.', 'wp-user-frontend' );
            }
        }

        return $errors;
    }

    /**
     * Check if a date string is valid
     *
     * @param string $date Date string
     * @return bool True if valid
     */
    private function is_valid_date( $date ) {
        if ( empty( $date ) ) {
            return false;
        }

        $timestamp = strtotime( $date );
        return $timestamp !== false;
    }

    /**
     * Build datetime object from event data
     *
     * @param array $event_data Event data
     * @param string $type 'start' or 'end'
     * @return \DateTime|false DateTime object or false on failure
     */
    private function build_datetime( $event_data, $type ) {
        $prefix = $type === 'start' ? 'EventStart' : 'EventEnd';
        
        $date = $event_data[ $prefix . 'Date' ];
        $hour = $event_data[ $prefix . 'Hour' ];
        $minute = $event_data[ $prefix . 'Minute' ];

        $datetime_string = sprintf( '%s %02d:%02d:00', $date, $hour, $minute );
        
        return \DateTime::createFromFormat( 'Y-m-d H:i:s', $datetime_string );
    }

    /**
     * Check if a phone number is valid
     *
     * @param string $phone Phone number
     * @return bool True if valid
     */
    private function is_valid_phone( $phone ) {
        // Basic phone validation - can be enhanced based on requirements
        $phone = preg_replace( '/[^0-9+\-\(\)\s]/', '', $phone );
        return strlen( $phone ) >= 10;
    }

    /**
     * Check if a URL is valid
     *
     * @param string $url URL to validate
     * @return bool True if valid
     */
    private function is_valid_url( $url ) {
        return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
    }

    /**
     * Get validation errors for a specific field
     *
     * @param array $event_data Event data
     * @param string $field Field name
     * @return array Array of errors for the field
     */
    public function validate_field( $event_data, $field ) {
        $errors = [];

        switch ( $field ) {
            case 'post_title':
                if ( !isset( $event_data[ $field ] ) || empty( $event_data[ $field ] ) ) {
                    $errors[] = __( 'Event title is required.', 'wp-user-frontend' );
                } elseif ( strlen( $event_data[ $field ] ) > 255 ) {
                    $errors[] = __( 'Event title cannot exceed 255 characters.', 'wp-user-frontend' );
                }
                break;

            case 'EventStartDate':
            case 'EventEndDate':
                if ( !isset( $event_data[ $field ] ) || !$this->is_valid_date( $event_data[ $field ] ) ) {
                    $errors[] = sprintf(
                        // translators: %s is the event field type (e.g., 'Start', 'End')
                        __( 'Event %s is not valid.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', str_replace( 'Date', '', $field ) )
                    );
                }
                break;

            case 'EventStartHour':
            case 'EventEndHour':
                if ( !isset( $event_data[ $field ] ) || !is_numeric( $event_data[ $field ] ) ) {
                    $errors[] = sprintf(
                        // translators: %s is the event field type (e.g., 'StartHour', 'EndHour')
                        __( 'Event %s must be a valid number.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                } elseif ( $event_data[ $field ] < 0 || $event_data[ $field ] > 23 ) {
                    $errors[] = sprintf(
                        // translators: %s is the event field type (e.g., 'StartHour', 'EndHour')
                        __( 'Event %s must be between 0 and 23.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                }
                break;

            case 'EventStartMinute':
            case 'EventEndMinute':
                if ( !isset( $event_data[ $field ] ) || !is_numeric( $event_data[ $field ] ) ) {
                    $errors[] = sprintf(
                        // translators: %s is the event field type (e.g., 'StartMinute', 'EndMinute')
                        __( 'Event %s must be a valid number.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                } elseif ( $event_data[ $field ] < 0 || $event_data[ $field ] > 59 ) {
                    $errors[] = sprintf(
                        // translators: %s is the event field type (e.g., 'StartMinute', 'EndMinute')
                        __( 'Event %s must be between 0 and 59.', 'wp-user-frontend' ),
                        str_replace( 'Event', '', $field )
                    );
                }
                break;
        }

        return $errors;
    }
} 