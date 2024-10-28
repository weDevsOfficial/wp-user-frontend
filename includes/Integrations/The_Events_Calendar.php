<?php

namespace WeDevs\Wpuf\Integrations;

if ( ! class_exists( 'WeDevs\Wpuf\Integrations\The_Events_Calendar' ) ) {
    class The_Events_Calendar {

        public function __construct() {
            add_action( 'wpuf_before_updating_post_meta_fields', [ $this, 'add_event' ], 10, 2 );
        }

        public function add_event( $post_id, $meta_key_value ) {
            if ( 'tribe_events' !== get_post_type( $post_id ) ) {
                return;
            }

            $args = [];

            if ( ! empty( $meta_key_value['_EventAllDay'] ) ) {
                $args['all_day'] = 'yes' === $meta_key_value['_EventAllDay'];
            }

            $default = [
                'title'   => '',
                'all_day' => true,
            ];

            $args = wp_parse_args( $args, $default );

            if ( function_exists( 'tribe_update_event' ) ) {
                tribe_update_event( $post_id, $args );
            }
        }
    }
}
