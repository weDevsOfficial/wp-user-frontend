<?php

namespace WeDevs\Wpuf\Admin\Forms;

/**
 * Post form template
 *
 * @since 2.4
 */
abstract class Form_Template {

    /**
     * If the form is enabled
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * Template title
     *
     * @var string
     */
    public $title;

    /**
     * Template description
     *
     * @var string
     */
    public $description;

    /**
     * Form Template Image
     *
     * @var string
     */
    public $image;

    /**
     * Conditional logic
     *
     * @var array
     */
    protected $conditionals;

    /**
     * Form fields
     *
     * @var array
     */
    protected $form_fields;

    /**
     * Form settings
     *
     * @var array
     */
    protected $form_settings;

    /**
     * Form notifications
     *
     * @since 2.5.2
     *
     * @var array
     */
    protected $form_notifications;

    /**
     * The datetime format for The Event Calender
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    const TIB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct() {
        $this->conditionals = [
            'condition_status' => 'no',
            'cond_field'       => [],
            'cond_operator'    => [ '=' ],
            'cond_option'      => [ '- Select -' ],
            'cond_logic'       => 'all',
        ];
    }

    /**
     * Get the template title
     *
     * @return string
     */
    public function get_title() {
        return apply_filters( 'wpuf_post_form_template_title', $this->title, $this );
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function get_description() {
        return apply_filters( 'wpuf_post_form_template_description', $this->description, $this );
    }

    /**
     * Get the form fields
     *
     * @return array
     */
    public function get_form_fields() {
        return apply_filters( 'wpuf_post_form_template_form_fields', $this->form_fields, $this );
    }

    /**
     * Get the form settings
     *
     * @return array
     */
    public function get_form_settings() {
        return apply_filters( 'wpuf_post_form_template_form_settings', $this->form_settings, $this );
    }

    /**
     * Get form notifications
     *
     * @since 2.5.2
     *
     * @return array
     */
    public function get_form_notifications() {
        return apply_filters( 'wpuf_post_form_template_form_notifications', $this->form_notifications, $this );
    }

    /**
     * Check if the template is enabled
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->enabled;
    }

    /**
     * Run necessary processing after new post insert
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     *
     * @return void
     */
    public function after_insert( $post_id, $form_id, $form_settings ) {
        // handle The Events Calendar data and for event calendar version below 6
        if ( class_exists( 'Tribe__Events__Main' ) && version_compare( \Tribe__Events__Main::VERSION, 6, '<' ) ) {
            $timezone       = get_option( 'timezone_string', 'UTC+0' );
            $start_date     = wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT );
            $end_date       = wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT );
            $start_date_utc = wpuf_current_datetime()->setTimezone( $timezone )->format( self::TIB_DATETIME_FORMAT );
            $end_date_utc   = wpuf_current_datetime()->setTimezone( $timezone )->format( self::TIB_DATETIME_FORMAT );

            $meta_to_update = [];
            $meta_to_delete = [];

            if ( 'yes' === $post_data['_EventAllDay'] ) {
                $p1d            = new \DateInterval( 'PT23H59M59S' );
                $new_start_date = $start_date;
                $new_end_date   = $end_date;

                $meta_to_update['_EventAllDay']       = $post_data['_EventAllDay'];
                $meta_to_update['_EventStartDate']    = $new_start_date;
                $meta_to_update['_EventEndDate']      = $new_end_date;
                $meta_to_update['_EventStartDateUTC'] = $start_date_utc;
                $meta_to_update['_EventEndDateUTC']   = $end_date_utc;
            } else {
                $meta_to_delete[]                     = '_EventAllDay';
                $meta_to_update['_EventStartDate']    = $start_date;
                $meta_to_update['_EventEndDate']      = $end_date;
                $meta_to_update['_EventStartDateUTC'] = $start_date_utc;
                $meta_to_update['_EventEndDateUTC']   = $end_date_utc;
                $meta_to_update['_EventDuration']     = 32400;
            }

            foreach ( $meta_to_update as $meta_key => $meta_value ) {
                update_post_meta( $post_id, $meta_key, $meta_value );
            }

            foreach ( $meta_to_delete as $meta_key ) {
                delete_post_meta( $post_id, $meta_key );
            }
        }

        $event_data = [
            'EventAllDay'    => ! empty( $post_data['_EventAllDay'] ) ? $post_data['_EventAllDay'] : 'yes',
            'EventStartDate' => ! empty( $post_data['_EventStartDate'] ) ? $post_data['_EventStartDate'] : wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT ),
            'EventEndDate'   => ! empty( $post_data['_EventEndDate'] ) ? $post_data['_EventEndDate'] : wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT ),
            'EventTimezone'  => ! empty( $post_data['_EventTimeZone'] ) ? $post_data['_EventTimeZone'] : get_option( 'timezone_string', 'UTC+0' ),
        ];

        if ( 'no' === $event_data['EventAllDay'] ) {
            $event_data = [
                'EventStartTime' => wpuf_current_datetime()->modify( $event_data['EventStartDate'] )->format( 'h:ia' ),
                'EventEndTime'   => wpuf_current_datetime()->modify( $event_data['EventEndDate'] )->format( 'h:ia' ),
            ];
        }

        $tribe_api = WP_PLUGIN_DIR . '/the-events-calendar/src/Tribe/API.php';

        require_once $tribe_api;

        /**
         * Opportunity to change 'The Event Calendar' metadata just before WPUF is saving it to DB
         *
         * @since WPUFPRO_SINCE
         *
         * @param array $event_data The event metadata
         * @param int $post_id The post id, in other words, The Event
         */
        $event_data = apply_filters( 'wpuf_tib_event_meta', $event_data, $post_id );
        \Tribe__Events__API::saveEventMeta( $post_id, $event_data );

        /**
         * Hook fired just after WPUF is saved 'The Event Calendar' metadata to the DB
         *
         * @since WPUFPRO_SINCE
         *
         * @param int $post_id The post_id, in other words, the event_id
         */
        do_action( 'wpuf_tib_after_saving_event_meta', $post_id );
    }

    /**
     * Run necessary processing after editing a post
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     *
     * @return void
     */
    public function after_update( $post_id, $form_id, $form_settings ) {
    }

    /**
     * wpuf_visibility property for all fields
     *
     * @since 2.6
     *
     * @return array
     */
    public function get_default_visibility_prop( $default = 'everyone' ) {
        return [
            'selected' => $default,
            'choices'  => [],
        ];
    }
}
