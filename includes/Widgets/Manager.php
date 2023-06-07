<?php

namespace Wp\User\Frontend\Widgets;

class Manager {

    private $widgets_list = [];
    /**
     * Class constructor
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function __construct() {
        $wpuf_widgets = apply_filters(
            'wpuf_widgets', [
                'login_widget' => 'Wp\User\Frontend\Login_Widget',
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
     *
     * @param string $widget_class
     *
     * @return bool|string Returns widget id if found, otherwise returns false
     */
    public function get_id( $widget_class ) {
        return array_search( $widget_class, $this->widgets_list, true );
    }
}
