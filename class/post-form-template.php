<?php

/**
 * Post form template
 *
 * @since 2.4
 */
abstract class WPUF_Post_Form_Template {

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
            'cond_option'      => [ '- select -' ],
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
        // we can return form here if it is not a 'The Event Calender' event
        if ( empty( $form_settings['post_type'] ) || 'tribe_events' !== $form_settings['post_type'] ) {
            return;
        }

        $tribe_api = WP_PLUGIN_DIR . '/the-events-calendar/src/Tribe/API.php';

        if ( ! file_exists( $tribe_api ) ) {
            return;
        }

        $event_data = [
            'EventAllDay'    => ! empty( $post_data['_EventAllDay'] ) ? $post_data['_EventAllDay'] : 'yes',
            'EventStartDate' => ! empty( $post_data['_EventStartDate'] ) ? $post_data['_EventStartDate'] : wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT ),
            'EventEndDate'   => ! empty( $post_data['_EventEndDate'] ) ? $post_data['_EventEndDate'] : wpuf_current_datetime()->format( self::TIB_DATETIME_FORMAT ),
        ];

        require_once $tribe_api;

        /**
         * Opportunity to change 'The Event Calendar' metadata just before WPUF is saving it to DB
         *
         * @since WPUF_SINCE
         *
         * @param array $event_data The event metadata
         * @param int $post_id The post id, in other words, The Event
         *
         */
        $event_data = apply_filters( 'wpuf_tib_event_meta', $event_data, $post_id );
        Tribe__Events__API::saveEventMeta( $post_id, $event_data );

        /**
         * Hook fired just after WPUF is saved 'The Event Calender' metadata to the DB
         *
         * @since WPUF_SINCE
         *
         * @param int $post_id The post_id, in other words, the event_id
         *
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
