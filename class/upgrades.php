<?php

/**
 * Runs upgrade routines
 */
class WPUF_Upgrades {

    private $version;

    function __construct( $version ) {
        $this->version = $version;

        $this->after_update_2_1_9();
    }

    function after_update_2_1_9() {

        $version = get_option( 'wpuf_version', '2.1.9' );

        if ( version_compare( $this->version, $version, '<=' ) ) {
            return;
        }

        $this->update_form_field();
        $this->update_subscription();
        $this->update_registration();

        update_option( 'wpuf_version', $this->version );
    }

    function update_registration() {

    }

    function update_form_field() {
        $posts = get_posts( array(
            'post_type'   => array( 'wpuf_forms', 'wpuf_profile' ),
            'numberposts' => '-1'
        ) );

        if ( $posts ) {
            foreach ($posts as $key => $post) {
                $posts_meta = get_post_meta( $post->ID, 'wpuf_form', true );
                $posts_meta = is_array( $posts_meta ) ? $posts_meta : array();
                foreach ($posts_meta as $key => $post_meta) {
                    $post_meta['wpuf_cond'] = array();

                    // if key empty then replace by its value
                    if ( array_key_exists('options', $post_meta ) ) {
                        foreach ($post_meta['options'] as $key => $value) {
                            $post_meta['options'][$value] = $value;
                            unset( $post_meta['options'][$key] );
                        }
                    }

                    WPUF_Admin_Form::insert_form_field( $post->ID, $post_meta, null, $key );
                    delete_post_meta( $post->ID, 'wpuf_form' );
                }
            }
        }
    }

    function update_subscription() {
        global $wpdb;

        $table = $wpdb->prefix . 'wpuf_subscription';
        $results = $wpdb->get_results( "SELECT name, description, count, duration, cost FROM $table" );

        if ( !$results ) {
            return;
        }

        $post_type = WPUF_Subscription::init()->get_all_post_type();

        foreach ( $results as $key => $result ) {
            $args = array(
                'post_title'   => $result->name,
                'post_content' => $result->description,
                'post_status'  => 'publish',
                'post_type'    => 'wpuf_subscription'
            );

            $post_ID = wp_insert_post( $args );

            if ( $post_ID ) {
                foreach ( $post_type as $key => $name ) {
                    $post_type[$key] = $result->count;
                }

                $post = array(
                    'cost'           => $result->cost,
                    'duration'       => $result->duration,
                    'recurring_pay'  => 'no',
                    'trial_period'   => '',
                    'post_type_name' =>  $post_type
                );

                WPUF_Subscription::init()->update_user_subscription_meta( $post_ID, $post );
            }
        }

        $sql = "DROP TABLE IF_EXISTS $table";
        $wpdb->query($sql);
    }
}