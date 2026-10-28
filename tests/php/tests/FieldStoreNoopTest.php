<?php
/**
 * An untouched builder save keeps stored fields byte for byte (task 2.8, G3 on
 * develop-made data).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Stores\FieldStore;

/**
 * Field store no-op tests.
 */
class FieldStoreNoopTest extends WP_UnitTestCase {

    private $store;

    public function set_up() {
        parent::set_up();
        $this->store = new FieldStore();
    }

    private function form() {
        return self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
    }

    /**
     * Store a field exactly as given (raw serialized content).
     */
    private function stored_field( $form_id, $content, $order ) {
        return wp_insert_post(
            [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $form_id,
                'post_status'  => 'publish',
                'post_content' => wp_slash( maybe_serialize( $content ) ),
                'menu_order'   => $order,
            ]
        );
    }

    private function raw( $field_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT post_content, menu_order, post_modified_gmt FROM {$wpdb->posts} WHERE ID = %d", $field_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    public function test_unchanged_field_sent_with_tracking_keys_and_empty_defaults_is_not_rewritten() {
        $form   = $this->form();
        $stored = [ 'template' => 'dropdown_field', 'label' => 'Pick \\ one', 'name' => 'pick', 'options' => [ 'a' => 'A' ] ];
        $id     = $this->stored_field( $form, $stored, 0 );
        $before = $this->raw( $id );

        // The builder sends the field back with its id, is_new false and empty defaults it adds.
        $saved = $this->store->save( $form, [ wp_slash( array_merge( $stored, [ 'id' => $id, 'is_new' => false, 'selected' => '', 'multiple' => [] ] ) ) ] );

        $this->assertSame( $before, $this->raw( $id ), 'content, order and modified date untouched' );
        $this->assertSame( $id, $saved[0]['id'] );
        $this->assertFalse( $saved[0]['is_new'] );
    }

    public function test_sample_form_fields_with_stored_is_new_keep_their_posts_without_the_markers() {
        $form    = $this->form();
        $stored  = [ 'template' => 'user_email', 'label' => 'Email', 'name' => 'user_email', 'id' => 0, 'is_new' => true ];
        $id      = $this->stored_field( $form, $stored, 0 );

        $this->store->save( $form, [ array_merge( $stored, [ 'id' => $id ] ) ] );

        $this->assertNotNull( get_post( $id ), 'not re-inserted' );
        // Develop also drops the markers (it re-inserts the field); the post id stays here.
        $this->assertSame( [ 'template' => 'user_email', 'label' => 'Email', 'name' => 'user_email' ], maybe_unserialize( get_post_field( 'post_content', $id ) ) );
        $this->assertCount( 1, get_children( [ 'post_parent' => $form, 'post_type' => 'wpuf_input' ] ) );
    }

    public function test_edited_sample_field_keeps_its_post_and_drops_the_tracking_keys() {
        $form = $this->form();
        $id   = $this->stored_field( $form, [ 'template' => 'user_email', 'label' => 'Email', 'id' => 0, 'is_new' => true ], 0 );

        $this->store->save( $form, [ [ 'template' => 'user_email', 'label' => 'E-mail address', 'id' => $id, 'is_new' => true ] ] );

        $this->assertSame( [ 'template' => 'user_email', 'label' => 'E-mail address' ], maybe_unserialize( get_post_field( 'post_content', $id ) ) );
    }

    public function test_edited_field_is_written_as_sent() {
        $form = $this->form();
        $id   = $this->stored_field( $form, [ 'template' => 'text_field', 'label' => 'Old' ], 0 );

        $this->store->save( $form, [ [ 'template' => 'text_field', 'label' => 'New', 'id' => $id ] ] );

        $this->assertSame( [ 'template' => 'text_field', 'label' => 'New', 'id' => $id ], maybe_unserialize( get_post_field( 'post_content', $id ) ) );
    }

    public function test_reordered_unchanged_fields_only_change_position() {
        $form   = $this->form();
        $first  = $this->stored_field( $form, [ 'template' => 'text_field', 'label' => 'First' ], 0 );
        $second = $this->stored_field( $form, [ 'template' => 'text_field', 'label' => 'Second' ], 1 );
        $a      = $this->raw( $first );

        $this->store->save(
            $form,
            [
                [ 'template' => 'text_field', 'label' => 'Second', 'id' => $second ],
                [ 'template' => 'text_field', 'label' => 'First', 'id' => $first ],
            ]
        );

        $this->assertSame( $a['post_content'], $this->raw( $first )['post_content'] );
        $this->assertSame( '1', (string) $this->raw( $first )['menu_order'] );
        $this->assertSame( '0', (string) $this->raw( $second )['menu_order'] );
    }

    public function test_new_and_removed_fields_still_insert_and_delete() {
        $form    = $this->form();
        $removed = $this->stored_field( $form, [ 'template' => 'text_field', 'label' => 'Gone' ], 0 );

        $saved = $this->store->save( $form, [ [ 'template' => 'textarea_field', 'label' => 'Brand new', 'id' => 9876543210, 'is_new' => true ] ] );

        $this->assertNull( get_post( $removed ) );
        $this->assertNotSame( 9876543210, $saved[0]['id'] );
        $this->assertSame( [ 'template' => 'textarea_field', 'label' => 'Brand new' ], maybe_unserialize( get_post_field( 'post_content', $saved[0]['id'] ) ) );
    }
}
