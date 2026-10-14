<?php
/**
 * Pro's date, time and date of birth fields in the React form schema
 * (Field_Facts): what the classic fields show, without jQuery UI.
 *
 * @package WP_User_Frontend
 */

class FrontendDateFactsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        do_action( 'rest_api_init' );

    }

    private function lift_feature_lock() {
        global $wp_filter;

        if ( isset( $wp_filter['wpuf_form_fields'] ) ) {
            foreach ( $wp_filter['wpuf_form_fields']->callbacks as $priority => $callbacks ) {
                foreach ( $callbacks as $callback ) {
                    if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) && false !== strpos( get_class( $callback['function'][0] ), 'Feature_Lock' ) ) {
                        remove_filter( 'wpuf_form_fields', $callback['function'], $priority );
                    }
                }
            }
        }

        $property = new ReflectionProperty( get_class( wpuf()->fields ), 'fields' );
        if ( PHP_VERSION_ID < 80100 ) {
            $property->setAccessible( true );
        }

        $property->setValue( wpuf()->fields, [] );
    }

    private function form() {
        $form_id = wpuf_create_sample_form( 'Date Form', 'wpuf_forms' );
        $time    = [ 'template' => 'time_field', 'input_type' => 'time_field', 'is_meta' => 'yes', 'required' => 'no', 'time_format' => 'g:i a', 'time_intervals' => '60', 'start_time' => '9:00 AM', 'end_time' => '1:00 PM' ];

        wpuf_legacy_insert_form_field( $form_id, array_merge( $time, [ 'name' => 'meet', 'label' => 'Meet', 'first_option' => '' ] ), null, 10 );
        wpuf_legacy_insert_form_field( $form_id, array_merge( $time, [ 'name' => 'meet_first', 'label' => 'Meet first', 'first_option' => '- Pick -' ] ), null, 11 );
        wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'date_field', 'input_type' => 'date', 'name' => 'when', 'label' => 'When', 'is_meta' => 'yes', 'format' => 'dd/mm/yy', 'time' => 'yes' ], null, 12 );
        wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'date_of_birth_field', 'input_type' => 'date_of_birth', 'name' => 'wpuf_dob', 'label' => 'Birth', 'is_meta' => 'yes', 'format' => 'mm-dd-yy', 'show_death_date' => 'yes' ], null, 13 );

        return $form_id;
    }

    private function fields( $form_id, $post_id = 0 ) {
        $request = new WP_REST_Request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' );

        if ( $post_id ) {
            $request->set_param( 'post_id', $post_id );
        }

        // Without a license Pro's Feature_Lock (hooked on the first request) drops the date / time fields.
        rest_do_request( $request );
        $this->lift_feature_lock();

        $out = [];

        foreach ( rest_do_request( $request )->get_data()['fields'] as $field ) {
            $out[ $field['name'] ] = $field;
        }

        return $out;
    }

    public function test_new_entry_time_defaults_like_the_classic_select() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $form_id = $this->form();

        // The React forms are on; only what building the schema enqueues counts.
        $general                   = (array) get_option( 'wpuf_general', [] );
        $general['frontend_react'] = 'on';
        update_option( 'wpuf_general', $general );
        wp_dequeue_script( 'jquery-ui-timepicker' );
        wp_dequeue_script( 'jquery-ui-datepicker' );

        $fields = $this->fields( $form_id );

        $this->assertSame( [ '9:00 am', '10:00 am', '11:00 am', '12:00 pm' ], $fields['meet']['time_options'] );
        $this->assertSame( '9:00 am', $fields['meet']['value'], 'no first option: the select shows (and posts) the first time' );
        $this->assertArrayNotHasKey( 'value', $fields['meet_first'], 'with a first option nothing is picked' );
        $this->assertSame( (int) get_option( 'start_of_week', 0 ), $fields['when']['week_start'] );
        $this->assertNotContains( 'jquery-ui-timepicker', wp_scripts()->queue, 'the React calendar needs no jQuery UI' );
        $this->assertNotContains( 'jquery-ui-datepicker', wp_scripts()->queue );
    }

    public function test_stored_values_show_as_the_classic_fields_print_them() {
        $user = self::factory()->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $user );
        $form_id = $this->form();
        $post_id = self::factory()->post->create( [ 'post_author' => $user ] );

        update_post_meta( $post_id, '_wpuf_form_id', $form_id );
        update_post_meta( $post_id, 'meet', '10:00' );
        update_post_meta( $post_id, 'meet_first', '7:00 am' );
        update_post_meta( $post_id, 'wpuf_dob', '1990-01-15' );
        update_post_meta( $post_id, 'wpuf_death_date', '2020-02-20' );

        $fields = $this->fields( $form_id, $post_id );

        $this->assertSame( '10:00 am', $fields['meet']['value'], 'reformatted in the field format (Field_Time::render())' );
        $this->assertSame( '', $fields['meet_first']['value'], 'a time the list does not have: the first option shows' );
        $this->assertSame( '01-15-1990', $fields['wpuf_dob']['value'], 'Y-m-d shown in the field format' );
        $this->assertSame( '02-20-2020', $fields['wpuf_dob']['death_date'] );
    }
}
