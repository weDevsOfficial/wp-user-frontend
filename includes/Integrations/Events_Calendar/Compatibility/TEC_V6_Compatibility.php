<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC v6 Compatibility Handler
 *
 * Handles The Events Calendar v6.x API calls using the new ORM API
 * Documentation: https://docs.theeventscalendar.com/apis/orm/create/events/
 *
 * @since WPUF_SINCE
 */
class TEC_V6_Compatibility {
    /**
     * Handle event creation using TEC ORM
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $form_data
     * @param array $meta_vars
     *
     * @return bool
     */
    public function handle_event_creation( $post_id, $form_data, $meta_vars ) {
        try {
            // Convert form data directly to ORM format
            $orm_args = $this->convert_form_data_to_orm_format( $form_data, $meta_vars );
            if ( empty( $orm_args ) ) {
                return false;
            }
            // Validate ORM requirements before saving
            if ( ! $this->validate_orm_requirements( $orm_args ) ) {
                return false;
            }
            // Temporarily disable TEC's save_post hooks to prevent conflicts
            $this->temporarily_disable_tec_hooks();
            // Use direct WordPress update and TEC meta saving
            $post_data = [
                'ID'        => $post_id,
                'post_type' => 'tribe_events',
            ];
            // Add basic post fields
            if ( isset( $orm_args['title'] ) ) {
                $post_data['post_title'] = $orm_args['title'];
            }
            if ( isset( $orm_args['description'] ) ) {
                $post_data['post_content'] = $orm_args['description'];
            }
            if ( isset( $orm_args['excerpt'] ) ) {
                $post_data['post_excerpt'] = $orm_args['excerpt'];
            }
            // Update the post first
            $post_result = wp_update_post( $post_data, true );
            if ( is_wp_error( $post_result ) ) {
                return false;
            } else {
                // Now save the event meta using TEC's API
                $meta_result = $this->save_event_meta( $post_id, $orm_args );
                $result      = $meta_result;
            }
            if ( ! $result ) {
                return false;
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Convert form data directly to ORM format
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @param array $meta_vars
     *
     * @return array
     */
    private function convert_form_data_to_orm_format( $form_data, $meta_vars = [] ) {
        $orm_args = [];
        // Merge form data with meta vars for comprehensive data access
        $all_data = array_merge( $form_data, $meta_vars );

        /**
         * Opportunity to modify form data before converting to TEC ORM format
         *
         * This filter allows developers to modify the raw form data before it's processed
         * into TEC ORM format. Useful for data validation, transformation, or integration
         * with custom form fields.
         *
         * @since WPUF_SINCE
         *
         * @param array $all_data The merged form data and meta variables
         * @param array $form_data The original form data from WPUF
         * @param array $meta_vars The original meta variables from WPUF
         */
        $all_data = apply_filters( 'wpuf_tec_before_convert_form_data', $all_data, $form_data, $meta_vars );

        // Required fields
        if ( ! empty( $all_data['post_title'] ) ) {
            $orm_args['title'] = sanitize_text_field( $all_data['post_title'] );
        }
        // Map basic fields
        if ( ! empty( $all_data['post_content'] ) ) {
            $orm_args['description'] = wp_kses_post( $all_data['post_content'] );
        }
        if ( ! empty( $all_data['post_excerpt'] ) ) {
            $orm_args['excerpt'] = sanitize_textarea_field( $all_data['post_excerpt'] );
        }
        // Handle date fields - these are critical for TEC ORM
        // Use TEC ORM field aliases instead of meta field names
        if ( ! empty( $all_data['_EventStartDate'] ) ) {
            $start_date = $this->format_date_for_tec( $all_data['_EventStartDate'] );
            if ( $start_date ) {
                // Enforce Y-m-d H:i:s format
                $dt = \DateTime::createFromFormat( 'Y-m-d H:i:s', $start_date );
                if ( $dt ) {
                    $orm_args['start_date'] = $dt->format( 'Y-m-d H:i:s' );
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Fallback: create a default start date if none provided
            $default_start          = current_time( 'Y-m-d H:i:s' );
            $orm_args['start_date'] = $default_start;
        }
        if ( ! empty( $all_data['_EventEndDate'] ) ) {
            $end_date = $this->format_date_for_tec( $all_data['_EventEndDate'] );
            if ( $end_date ) {
                // Enforce Y-m-d H:i:s format
                $dt = \DateTime::createFromFormat( 'Y-m-d H:i:s', $end_date );
                if ( $dt ) {
                    $orm_args['end_date'] = $dt->format( 'Y-m-d H:i:s' );
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Fallback: create a default end date if none provided
            if ( ! empty( $orm_args['start_date'] ) ) {
                $default_end          = date(
                    'Y-m-d H:i:s', strtotime( $orm_args['start_date'] ) + 3600
                ); // 1 hour later
                $orm_args['end_date'] = $default_end;
            }
        }
        // Add UTC dates for TEC v6 compatibility using ORM field aliases
        if ( ! empty( $orm_args['start_date'] ) ) {
            $timezone = isset( $orm_args['timezone'] ) ? $orm_args['timezone'] : 'UTC';
            try {
                $start_datetime             = new \DateTime( $orm_args['start_date'], new \DateTimeZone( $timezone ) );
                $orm_args['start_date_utc'] = $start_datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format(
                    'Y-m-d H:i:s'
                );
            } catch ( \Exception $e ) {
                // Fallback: use the same time as UTC
                $orm_args['start_date_utc'] = $orm_args['start_date'];
            }
        }
        if ( ! empty( $orm_args['end_date'] ) ) {
            $timezone = isset( $orm_args['timezone'] ) ? $orm_args['timezone'] : 'UTC';
            try {
                $end_datetime             = new \DateTime( $orm_args['end_date'], new \DateTimeZone( $timezone ) );
                $orm_args['end_date_utc'] = $end_datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format(
                    'Y-m-d H:i:s'
                );
            } catch ( \Exception $e ) {
                // Fallback: use the same time as UTC
                $orm_args['end_date_utc'] = $orm_args['end_date'];
            }
        }
        // Handle all-day events using ORM field alias
        if ( ! empty( $all_data['_EventAllDay'] ) ) {
            $is_all_day          = is_array( $all_data['_EventAllDay'] ) && in_array( '1', $all_data['_EventAllDay'] );
            $orm_args['all_day'] = $is_all_day;
            // For all-day events, if no end date is provided, set it to the same day
            if ( $is_all_day && ! empty( $orm_args['start_date'] ) && empty( $orm_args['end_date'] ) ) {
                $start_date = $this->format_date_for_tec( $all_data['_EventStartDate'] );
                if ( $start_date ) {
                    // Set end date to same day at 23:59:59
                    $end_date             = date( 'Y-m-d 23:59:59', strtotime( $start_date ) );
                    $orm_args['end_date'] = $end_date;
                }
            }
        }
        if ( isset( $all_data['EventFeatured'] ) ) {
            $orm_args['featured'] = tribe_is_truthy( $all_data['EventFeatured'] );
        }
        if ( isset( $all_data['EventSticky'] ) ) {
            $orm_args['sticky'] = tribe_is_truthy( $all_data['EventSticky'] );
        }
        // Map cost field (int or float) - handle properly for ORM API
        if ( ! empty( $all_data['_EventCost'] ) ) {
            $cost = sanitize_text_field( $all_data['_EventCost'] );
            // Remove any currency symbols and clean the cost value
            $cost = preg_replace( '/[^0-9.]/', '', $cost );
            if ( is_numeric( $cost ) && floatval( $cost ) > 0 ) {
                $orm_args['cost'] = floatval( $cost );
            }
        }
        // Map currency fields using ORM field aliases
        if ( ! empty( $all_data['_EventCurrencySymbol'] ) ) {
            $orm_args['currency_symbol'] = sanitize_text_field( $all_data['_EventCurrencySymbol'] );
        }
        if ( ! empty( $all_data['_EventCurrencyPosition'] ) ) {
            $orm_args['currency_position'] = sanitize_text_field( $all_data['_EventCurrencyPosition'] );
        }
        // Map timezone
        if ( ! empty( $all_data['_EventTimezone'] ) ) {
            $timezone = sanitize_text_field( $all_data['_EventTimezone'] );
            // Validate timezone
            if ( in_array( $timezone, timezone_identifiers_list() ) ) {
                $orm_args['timezone'] = $timezone;
            } else {
                // Use default timezone as fallback
                $orm_args['timezone'] = wp_timezone_string();
            }
        } else {
            // Use default timezone if none provided
            $orm_args['timezone'] = wp_timezone_string();
        }
        // Ensure timezone is valid for TEC
        if ( ! in_array( $orm_args['timezone'], timezone_identifiers_list() ) ) {
            $orm_args['timezone'] = 'UTC';
        }
        // Map URL field using ORM field alias
        if ( ! empty( $all_data['_EventURL'] ) ) {
            $orm_args['url'] = esc_url_raw( $all_data['_EventURL'] );
        }
        // Map image field
        if ( ! empty( $all_data['featured_image'] ) ) {
            $orm_args['image'] = intval( $all_data['featured_image'] );
        }
        // Map presentation fields using ORM field aliases
        if ( ! empty( $all_data['_EventShowMap'] ) ) {
            $show_map             = is_array( $all_data['_EventShowMap'] ) && in_array(
                    '1', $all_data['_EventShowMap']
                );
            $orm_args['show_map'] = $show_map;
        }
        if ( ! empty( $all_data['_EventShowMapLink'] ) ) {
            $show_map_link             = is_array( $all_data['_EventShowMapLink'] ) && in_array(
                    '1', $all_data['_EventShowMapLink']
                );
            $orm_args['show_map_link'] = $show_map_link;
        }
        if ( ! empty( $all_data['_EventHideFromUpcoming'] ) ) {
            $hide_from_upcoming             = is_array( $all_data['_EventHideFromUpcoming'] ) && in_array(
                    '1', $all_data['_EventHideFromUpcoming']
                );
            $orm_args['hide_from_upcoming'] = $hide_from_upcoming;
        }
        // Map duration if available
        if ( ! empty( $all_data['_EventDuration'] ) ) {
            $orm_args['duration'] = intval( $all_data['_EventDuration'] );
        }

        /**
         * Opportunity to modify TEC ORM arguments after conversion from form data
         *
         * This filter allows developers to modify the ORM arguments before they're validated
         * and saved. Useful for custom field mapping, data transformation, or integration
         * with third-party services.
         *
         * @since WPUF_SINCE
         *
         * @param array $orm_args The ORM arguments ready for TEC API
         * @param array $all_data The original merged form data
         * @param array $form_data The original form data from WPUF
         * @param array $meta_vars The original meta variables from WPUF
         */
        $orm_args = apply_filters( 'wpuf_tec_after_convert_form_data', $orm_args, $all_data, $form_data, $meta_vars );

        return $orm_args;
    }

    /**
     * Validate ORM requirements before saving
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     *
     * @return bool
     */
    private function validate_orm_requirements( $orm_args ) {
        // Title is always required
        if ( empty( $orm_args['title'] ) ) {
            return false;
        }
        // Check date requirements
        $has_start_date = ! empty( $orm_args['start_date'] );
        $has_end_date   = ! empty( $orm_args['end_date'] );
        $has_duration   = ! empty( $orm_args['duration'] );
        $is_all_day     = ! empty( $orm_args['all_day'] ) && $orm_args['all_day'];
        // Must have start_date AND (end_date OR duration OR all_day)
        if ( ! $has_start_date ) {
            return false;
        }
        if ( ! $has_end_date && ! $has_duration && ! $is_all_day ) {
            return false;
        }
        // Validate date formats if present
        if ( $has_start_date && ! $this->is_valid_date_format( $orm_args['start_date'] ) ) {
            return false;
        }
        if ( $has_end_date && ! $this->is_valid_date_format( $orm_args['end_date'] ) ) {
            return false;
        }
        // Validate that end date is after start date
        if ( $has_start_date && $has_end_date ) {
            $start_timestamp = strtotime( $orm_args['start_date'] );
            $end_timestamp   = strtotime( $orm_args['end_date'] );
            if ( $start_timestamp === false || $end_timestamp === false ) {
                return false;
            }
            if ( $end_timestamp <= $start_timestamp ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Temporarily disable TEC's save_post hooks to prevent conflicts
     *
     * @since WPUF_SINCE
     */
    private function temporarily_disable_tec_hooks() {
        // Store the current state
        $this->tec_hooks_disabled = true;
        // Remove TEC's main event meta processing
        remove_action( 'save_post', [ 'Tribe__Events__Main', 'addEventMeta' ], 15 );
        // Remove TEC's custom tables update hook if it exists
        if ( class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
            try {
                $tec_updates = tribe( 'tec.events.custom-tables.v1.updates.events' );
                if ( $tec_updates ) {
                    remove_action( 'save_post', [ $tec_updates, 'update' ], 10 );
                }
            } catch ( \Exception $e ) {
                // Service not bound, skip this hook
            }
        }
        // Remove TEC's linked posts processing - use instance method
        $linked_posts = tribe( 'tec.linked-posts' );
        if ( $linked_posts ) {
            remove_action( 'save_post', [ $linked_posts, 'handle_submission' ], 10 );
        }
    }

    /**
     * Prevent TEC from processing event creation when we're handling it through WPUF
     *
     * @since WPUF_SINCE
     *
     * @param int      $post_id
     * @param \WP_Post $post
     */
    public function prevent_tec_event_processing( $post_id, $post ) {
        // Only handle tribe_events post type
        if ( $post->post_type !== 'tribe_events' ) {
            return;
        }
        // Check if this is a WPUF form submission
        if ( $this->is_wpuf_form_submission() ) {
            // Remove TEC's event meta processing to prevent conflicts
            remove_action( 'save_post', [ 'Tribe__Events__Main', 'addEventMeta' ], 15 );
        }
    }

    /**
     * Check if current request is a WPUF form submission
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function is_wpuf_form_submission() {
        // Check for WPUF form submission indicators
        $wpuf_indicators = [
            'wpuf_form_submit',
            'wpuf_post_new',
            'wpuf_edit_post',
            'wpuf_ajax_submit',
        ];
        foreach ( $wpuf_indicators as $indicator ) {
            if ( isset( $_POST[ $indicator ] ) || isset( $_REQUEST[ $indicator ] ) ) {
                return true;
            }
        }
        // Check for WPUF nonce
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_form_add' ) ) {
            return true;
        }
        // Check for WPUF AJAX action
        if ( isset( $_POST['action'] ) && strpos( $_POST['action'], 'wpuf' ) === 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Prepare event data before post creation
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id
     * @param array $form_settings
     * @param array $form_data
     *
     * @return bool
     */
    public function prepare_event_data( $form_id, $form_settings, $form_data ) {
        if ( $form_settings['post_type'] !== 'tribe_events' ) {
            return true;
        }
        // Convert form data to ORM format
        $orm_args = $this->convert_form_data_to_orm_format( $form_data );
        if ( empty( $orm_args ) ) {
            return false;
        }
        // Prepare data for TEC's save_post hook
        $this->prepare_data_for_tec_save_post_from_orm( $orm_args );

        return true;
    }

    /**
     * Prepare data for TEC's save_post hook from ORM format
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     */
    private function prepare_data_for_tec_save_post_from_orm( $orm_args ) {
        // Convert ORM field names to TEC's expected field names
        $field_mapping = [
            'all_day'            => '_EventAllDay',
            'timezone'           => '_EventTimezone',
            'cost'               => '_EventCost',
            'currency_symbol'    => '_EventCurrencySymbol',
            'url'                => '_EventURL',
            'show_map'           => '_EventShowMap',
            'show_map_link'      => '_EventShowMapLink',
            'hide_from_upcoming' => '_EventHideFromUpcoming',
        ];
        // Convert field names and add to $_POST for TEC's save_post hook
        foreach ( $field_mapping as $orm_field => $tec_field ) {
            if ( isset( $orm_args[ $orm_field ] ) ) {
                $_POST[ $tec_field ] = $orm_args[ $orm_field ];
            }
        }
    }

    /**
     * Save event using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @param array $postarr       The post array (title, content, etc.)
     * @param array $meta_vars     The meta fields from the form
     * @param int   $form_id       The WPUF form ID
     * @param array $form_settings The WPUF form settings
     *
     * @return int|false The created event post ID on success, 0 or false on failure
     */
    public function save_event( $postarr, $meta_vars, $form_id, $form_settings ) {
        // Convert WPUF data to TEC ORM format
        $args = $this->convert_form_data_to_orm_format( $postarr, $meta_vars );
        if ( empty( $args ) || ! is_array( $args ) ) {
            return 0;
        }
        // Remove ID if present (should not be set for new posts)
        if ( isset( $args['ID'] ) ) {
            unset( $args['ID'] );
        }

        /**
         * Opportunity to modify TEC ORM arguments before creating the event
         *
         * This filter allows developers to modify the final ORM arguments before the event
         * is created via TEC's API. Useful for last-minute data validation, transformation,
         * or integration with external services.
         *
         * @since WPUF_SINCE
         *
         * @param array $args         The ORM arguments ready for TEC API
         * @param array $postarr      The original WordPress post array
         * @param array $meta_vars    The original meta variables from WPUF
         * @param int   $form_id      The WPUF form ID
         * @param array $form_settings The WPUF form settings
         */
        $args = apply_filters( 'wpuf_tec_before_create_event', $args, $postarr, $meta_vars, $form_id, $form_settings );

        // Call the TEC ORM API
        try {
            if ( function_exists( 'tribe_events' ) ) {
                $event = tribe_events()->set_args( $args )->create();
                if ( $event instanceof \WP_Post ) {
                    $post_status = ! empty( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'draft';
                    wp_update_post(
                        [
                            'ID'          => $event->ID,
                            'post_status' => $post_status,
                        ]
                    );

                    /**
                     * Opportunity to perform actions after event creation
                     *
                     * This action allows developers to perform additional operations after
                     * an event has been successfully created. Useful for notifications,
                     * integrations, or custom post-processing.
                     *
                     * @since WPUF_SINCE
                     *
                     * @param int   $event_id      The created event post ID
                     * @param array $args          The ORM arguments used to create the event
                     * @param array $postarr       The original WordPress post array
                     * @param array $meta_vars     The original meta variables from WPUF
                     * @param int   $form_id      The WPUF form ID
                     * @param array $form_settings The WPUF form settings
                     */
                    do_action( 'wpuf_tec_after_create_event', $event->ID, $args, $postarr, $meta_vars, $form_id, $form_settings );

                    return $event->ID;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } catch ( \Exception $e ) {
            return 0;
        }
    }

    /**
     * Format date for TEC ORM
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     *
     * @return string|false
     */
    public function format_date_for_tec( $date_string ) {
        if ( empty( $date_string ) ) {
            return false;
        }
        try {
            // Try multiple date formats that WPUF might use
            $formats_to_try = [
                'Y-m-d H:i:s',           // Standard MySQL format
                'Y-m-d H:i',             // Without seconds
                'Y-m-d',                 // Date only
                'm/d/Y H:i:s',           // US format with time
                'm/d/Y H:i',             // US format without seconds
                'm/d/Y',                 // US date only
                'd/m/Y H:i:s',           // European format with time
                'd/m/Y H:i',             // European format without time
                'd/m/Y',                 // European date only
                'Y-m-d\TH:i:s',          // ISO format
                'Y-m-d\TH:i',            // ISO format without seconds
            ];
            // First try TEC's build_date_object function
            if ( class_exists( 'Tribe__Date_Utils' ) ) {
                $date_object = \Tribe__Date_Utils::build_date_object( $date_string );
                if ( $date_object ) {
                    $formatted = $date_object->format( \Tribe__Date_Utils::DBDATETIMEFORMAT );

                    return $formatted;
                }
            }
            // Try parsing with different formats
            foreach ( $formats_to_try as $format ) {
                $date_object = \DateTime::createFromFormat( $format, $date_string );
                if ( $date_object ) {
                    $formatted = $date_object->format( 'Y-m-d H:i:s' );

                    return $formatted;
                }
            }
            // Fallback to WordPress date parsing
            $timestamp = strtotime( $date_string );
            if ( $timestamp !== false ) {
                $formatted = date( 'Y-m-d H:i:s', $timestamp );

                return $formatted;
            }

            return false;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if date string is in valid format
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     *
     * @return bool
     */
    private function is_valid_date_format( $date_string ) {
        if ( empty( $date_string ) ) {
            return false;
        }
        // Check if it's in Y-m-d H:i:s format
        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if ( preg_match( $pattern, $date_string ) ) {
            return true;
        }
        // Check if it's a valid date string
        $timestamp = strtotime( $date_string );

        return $timestamp !== false;
    }

    /**
     * Save event meta using TEC's API
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $orm_args
     *
     * @return bool
     */
    private function save_event_meta( $post_id, $orm_args ) {
        try {
            // Convert ORM args to TEC meta format
            $meta_data = [];
            // Map event-specific fields to TEC meta format
            if ( isset( $orm_args['start_date'] ) ) {
                $meta_data['_EventStartDate'] = $orm_args['start_date'];
            }
            if ( isset( $orm_args['end_date'] ) ) {
                $meta_data['_EventEndDate'] = $orm_args['end_date'];
            }
            if ( isset( $orm_args['start_date_utc'] ) ) {
                $meta_data['_EventStartDateUTC'] = $orm_args['start_date_utc'];
            }
            if ( isset( $orm_args['end_date_utc'] ) ) {
                $meta_data['_EventEndDateUTC'] = $orm_args['end_date_utc'];
            }
            if ( isset( $orm_args['timezone'] ) ) {
                $meta_data['_EventTimezone'] = $orm_args['timezone'];
            }
            if ( isset( $orm_args['all_day'] ) ) {
                $meta_data['_EventAllDay'] = $orm_args['all_day'] ? 'yes' : 'no';
            }
            if ( isset( $orm_args['cost'] ) ) {
                $meta_data['_EventCost'] = $orm_args['cost'];
            }
            if ( isset( $orm_args['currency_symbol'] ) ) {
                $meta_data['_EventCurrencySymbol'] = $orm_args['currency_symbol'];
            }
            if ( isset( $orm_args['currency_position'] ) ) {
                $meta_data['_EventCurrencyPosition'] = $orm_args['currency_position'];
            }
            if ( isset( $orm_args['url'] ) ) {
                $meta_data['_EventURL'] = $orm_args['url'];
            }
            if ( isset( $orm_args['show_map'] ) ) {
                $meta_data['_EventShowMap'] = $orm_args['show_map'] ? '1' : '0';
            }
            if ( isset( $orm_args['show_map_link'] ) ) {
                $meta_data['_EventShowMapLink'] = $orm_args['show_map_link'] ? '1' : '0';
            }
            if ( isset( $orm_args['hide_from_upcoming'] ) ) {
                $meta_data['_EventHideFromUpcoming'] = $orm_args['hide_from_upcoming'] ? '1' : '0';
            }
            if ( isset( $orm_args['featured'] ) ) {
                $meta_data['_tribe_featured'] = $orm_args['featured'] ? '1' : '0';
            }
            if ( isset( $orm_args['image'] ) ) {
                $meta_data['_thumbnail_id'] = $orm_args['image'];
            }

            /**
             * This filter allows developers to add, modify, or remove event metadata before it's saved.
             * Useful for custom event fields, validation, or integration with other plugins.
             *
             * @since WPUF_SINCE
             *
             * @param array $meta_data The event metadata array with keys like '_EventStartDate', '_EventEndDate', etc.
             * @param int   $post_id   The event post ID
             * @param array $orm_args  The original ORM arguments used to generate the metadata
             */
            $meta_data = apply_filters( 'wpuf_tec_event_meta', $meta_data, $post_id, $orm_args );

            // Save each meta field
            foreach ( $meta_data as $meta_key => $meta_value ) {
                update_post_meta( $post_id, $meta_key, $meta_value );
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if TEC v6 is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_active() {
        return class_exists( 'Tribe__Events__Main' ) && function_exists( 'tribe_events' );
    }
}
