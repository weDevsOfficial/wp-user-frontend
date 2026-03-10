<?php

namespace WeDevs\Wpuf\Widgets;

class Manager {

    private $widgets_list = [];
    /**
     * Class constructor
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function __construct() {
        $wpuf_widgets = apply_filters(
            'wpuf_widgets', [
                'login_widget' => 'WeDevs\Wpuf\Widgets\Login_Widget',
            ]
        );

        foreach ( $wpuf_widgets as $widget_id => $widget_class ) {
            register_widget( $widget_class );
        }

        $this->widgets_list = $wpuf_widgets;
    }

    /**
     * Check if widget class exists
     *
     * @since 4.0.0
     *
     * @param string $widget_id
     *
     * @return bool
     */
    public function is_exists( $widget_id ) {
        return isset( $this->widgets_list[ $widget_id ] ) && class_exists( $this->widgets_list[ $widget_id ] );
    }

    /**
     * Get widget id from widget class
     *
     * @since 4.0.0
     *
     * @param string $widget_class
     *
     * @return bool|string Returns widget id if found, otherwise returns false
     */
    public function get_id( $widget_class ) {
        return array_search( $widget_class, $this->widgets_list, true );
    }
}
