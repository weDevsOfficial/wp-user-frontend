<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler;

/**
 * Main Events Calendar Integration Class
 *
 * @since 4.1.9
 */
class Events_Calendar_Integration {

    /**
     * Event handler instance
     *
     * @var Event_Handler
     */
    public $event_handler;

    /**
     * Compatibility manager instance
     *
     * @var TEC_Compatibility_Manager
     */
    private $compatibility_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_handlers();
    }

    /**
     * Initialize all handlers
     *
     * @since 4.1.9
     */
    private function init_handlers() {
        // Only initialize if TEC is active
        if ( ! $this->is_tec_active() ) {
            return;
        }

        $this->compatibility_manager = new TEC_Compatibility_Manager();
        $this->event_handler = new Event_Handler( $this->compatibility_manager );

        /**
         * Opportunity to perform actions after Events Calendar integration initialization
         *
         * This action allows developers to perform additional setup after the TEC integration
         * has been initialized. Useful for custom handlers, compatibility layers, or
         * integration with other plugins.
         *
         * @since 4.1.9
         *
         * @param Events_Calendar_Integration $this The integration instance
         * @param TEC_Compatibility_Manager $compatibility_manager The compatibility manager instance
         * @param Event_Handler $event_handler The event handler instance
         */
        do_action( 'wpuf_tec_integration_ready', $this, $this->compatibility_manager, $this->event_handler );

        add_filter( 'wpuf_single_form_field', [ $this, 'single_builder_fields' ] );
        add_filter( 'wpuf_form_builder_localize_script', [ $this, 'add_builder_fields' ] );
    }

    /**
     * Event fields of the "Events Calendar" group in the post form builder
     *
     * Each one is added with the meta key The Events Calendar reads, so a field
     * that was deleted from an event form can be added back and still saves into
     * the event.
     *
     * @since WPUF_SINCE
     *
     * @return array Meta key => field props (template + overrides of its defaults).
     */
    public function get_builder_fields() {
        $date = [
            'template' => 'date_field',
            'required' => 'yes',
            'format'   => 'yy-mm-dd',
            'time'     => 'yes',
        ];

        $fields = [
            '_EventStartDate'      => array_merge( $date, [ 'label' => __( 'Event Start', 'wp-user-frontend' ) ] ),
            '_EventEndDate'        => array_merge( $date, [ 'label' => __( 'Event End', 'wp-user-frontend' ) ] ),
            '_EventAllDay'         => [
                'template' => 'checkbox_field',
                'label'    => __( 'All Day Event', 'wp-user-frontend' ),
                'options'  => [ '1' => __( 'All Day Event', 'wp-user-frontend' ) ],
            ],
            '_EventURL'            => [
                'template' => 'website_url',
                'label'    => __( 'Event Website', 'wp-user-frontend' ),
            ],
            '_EventCurrencySymbol' => [
                'template' => 'text_field',
                'label'    => __( 'Currency Symbol', 'wp-user-frontend' ),
            ],
            '_EventCost'           => [
                'template' => 'text_field',
                'label'    => __( 'Cost', 'wp-user-frontend' ),
                'help'     => __( 'Enter a 0 for events that are free or leave blank to hide the field.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filters the fields of the "Events Calendar" group in the post form builder
         *
         * @since WPUF_SINCE
         *
         * @param array $fields Meta key => field props. `template` is the field type, the rest overrides its defaults.
         */
        return apply_filters( 'wpuf_tec_form_builder_fields', $fields );
    }

    /**
     * Each event field can be added to a form only once
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Single instance field keys.
     *
     * @return array
     */
    public function single_builder_fields( $fields ) {
        return array_merge( $fields, array_keys( $this->get_builder_fields() ) );
    }

    /**
     * Add the "Events Calendar" group to the post form builder palette
     *
     * The group starts empty and the builder fills it only while the form's
     * post type is tribe_events (wpuf-form-builder-wpuf-forms.js).
     *
     * @since WPUF_SINCE
     *
     * @param array $data Form builder script data.
     *
     * @return array
     */
    public function add_builder_fields( $data ) {
        if ( empty( $data['form_type'] ) || 'wpuf_forms' !== $data['form_type'] || empty( $data['panel_sections'] ) ) {
            return $data;
        }

        $keys = [];

        foreach ( $this->get_builder_fields() as $meta_key => $props ) {
            $template = isset( $props['template'] ) ? $props['template'] : '';

            if ( ! isset( $data['field_settings'][ $template ] ) ) {
                continue;
            }

            // Same field type, so the stage and the field options work as for that type.
            // Without Pro a Pro field type is an upgrade prompt, so its button shows locked.
            $settings                = $data['field_settings'][ $template ];
            $settings['title']       = $props['label'];
            $settings['field_props'] = array_merge(
                (array) $settings['field_props'],
                $props,
                [
                    'name'         => $meta_key,
                    'is_meta'      => 'yes',
                    'show_in_post' => 'no',
                ]
            );

            $data['field_settings'][ $meta_key ] = $settings;
            $keys[]                              = $meta_key;
        }

        if ( ! $keys ) {
            return $data;
        }

        $section = [
            'title'  => __( 'Events Calendar', 'wp-user-frontend' ),
            'id'     => 'events-calendar',
            'fields' => [],
        ];

        $position = count( $data['panel_sections'] );

        foreach ( $data['panel_sections'] as $index => $panel_section ) {
            if ( isset( $panel_section['id'] ) && 'taxonomies' === $panel_section['id'] ) {
                $position = $index + 1;
                break;
            }
        }

        array_splice( $data['panel_sections'], $position, 0, [ $section ] );

        $data['tec_fields'] = $keys;

        return $data;
    }

    /**
     * Check if The Events Calendar is active
     *
     * @since 4.1.9
     *
     * @return bool
     */
    private function is_tec_active() {
        return class_exists( 'Tribe__Events__Main' );
    }
}
