<?php
/**
 * The Events Calendar venue / organizer dropdowns in the React form schema:
 * the published venues / organizers, as Form_Field_Dropdown::render() lists them.
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Frontend\Forms\Form_Schema
 */
class FrontendEventListsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        foreach ( [ 'tribe_venue', 'tribe_organizer' ] as $post_type ) {
            if ( ! post_type_exists( $post_type ) ) {
                register_post_type( $post_type, [ 'public' => true ] );
            }
        }

        do_action( 'rest_api_init' );
    }

    private function fields( $form_id ) {
        $out = [];

        foreach ( rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' ) )->get_data()['fields'] as $field ) {
            $out[ $field['name'] ] = $field;
        }

        return $out;
    }

    public function test_venue_and_organizer_dropdowns_list_the_published_posts() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        $venue     = self::factory()->post->create( [ 'post_type' => 'tribe_venue', 'post_title' => 'Hall' ] );
        $organizer = self::factory()->post->create( [ 'post_type' => 'tribe_organizer', 'post_title' => 'Org' ] );
        self::factory()->post->create( [ 'post_type' => 'tribe_venue', 'post_title' => 'Draft hall', 'post_status' => 'draft' ] );

        $form_id  = wpuf_create_sample_form( 'Event Form', 'wpuf_forms' );
        $dropdown = [ 'template' => 'dropdown_field', 'input_type' => 'select', 'is_meta' => 'yes', 'first' => '- Select -', 'options' => [] ];

        wpuf_legacy_insert_form_field( $form_id, array_merge( $dropdown, [ 'name' => '_EventVenueID', 'label' => 'Venue' ] ), null, 10 );
        wpuf_legacy_insert_form_field( $form_id, array_merge( $dropdown, [ 'name' => '_EventOrganizerID', 'label' => 'Organizer' ] ), null, 11 );
        wpuf_legacy_insert_form_field( $form_id, array_merge( $dropdown, [ 'name' => 'color', 'label' => 'Color', 'options' => [ 'red' => 'Red' ] ] ), null, 12 );

        $fields = $this->fields( $form_id );

        $this->assertSame( [ $venue => 'Hall' ], $fields['_EventVenueID']['options'] );
        $this->assertSame( [ $organizer => 'Org' ], $fields['_EventOrganizerID']['options'] );
        $this->assertSame( [ 'red' => 'Red' ], $fields['color']['options'], 'other dropdowns keep their options' );
    }
}
