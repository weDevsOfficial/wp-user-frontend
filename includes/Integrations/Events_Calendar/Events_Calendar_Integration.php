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

        // The user's own event lists (Account page, [wpuf_dashboard]) keep past drafts/pending events.
        add_filter( 'wpuf_dashboard_query', [ $this, 'list_past_unfinished_events' ], 5 );
        add_filter( 'posts_where', [ $this, 'hide_past_published_events' ], 10, 2 );

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
    }

    /**
     * Keep a member's past draft and pending events in their own dashboard lists
     *
     * TEC limits every front-end tribe_events query to upcoming events, which hid
     * past drafts and pending events, so the member could not open and finish them.
     * TEC's filter is switched off for these lists and replaced by
     * hide_past_published_events(), which still hides past published events.
     *
     * @since WPUF_SINCE
     *
     * @param array $args Dashboard query args.
     *
     * @return array
     */
    public function list_past_unfinished_events( $args ) {
        if ( in_array( 'tribe_events', (array) ( $args['post_type'] ?? [] ), true ) ) {
            $args['tribe_suppress_query_filters']  = true;
            $args['wpuf_hide_past_published_events'] = true;
        }

        return $args;
    }

    /**
     * Hide finished events unless they are a draft or pending
     *
     * @since WPUF_SINCE
     *
     * @param string    $where WHERE clause.
     * @param \WP_Query $query The query.
     *
     * @return string
     */
    public function hide_past_published_events( $where, $query ) {
        global $wpdb;

        if ( ! $query->get( 'wpuf_hide_past_published_events' ) ) {
            return $where;
        }

        // Same rule as TEC's upcoming filter (end date not passed), on its UTC meta so it works on TEC 5 and 6.
        $where .= $wpdb->prepare(
            " AND ( {$wpdb->posts}.post_type <> %s OR {$wpdb->posts}.post_status IN ( 'draft', 'pending' ) OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} AS wpuf_end WHERE wpuf_end.post_id = {$wpdb->posts}.ID AND wpuf_end.meta_key = '_EventEndDateUTC' AND wpuf_end.meta_value >= %s
            ) )",
            'tribe_events',
            current_time( 'mysql', true )
        );

        return $where;
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
