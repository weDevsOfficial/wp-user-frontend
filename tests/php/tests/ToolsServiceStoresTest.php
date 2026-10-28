<?php
/**
 * ToolsService writes through the stores
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Admin_Tools;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\SubscriptionStore;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WeDevs\Wpuf\Platform\Tools\ToolsService;

/**
 * Tools: reset, delete, export, import, listing through FormStore, SubscriptionStore, TransactionStore and SettingsStore.
 */
class ToolsServiceStoresTest extends WP_UnitTestCase {

    /**
     * The one post with this title and type (any status).
     *
     * @param string $title     Title
     * @param string $post_type Post type
     *
     * @return WP_Post|null
     */
    private function post_titled( $title, $post_type ) {
        $posts = get_posts( [ 'title' => $title, 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => 1 ] );

        return $posts ? $posts[0] : null;
    }

    /**
     * A field definition.
     *
     * @param string $name Field name
     *
     * @return array
     */
    private function field( $name ) {
        return [ 'template' => 'text_field', 'input_type' => 'text', 'name' => $name, 'label' => ucfirst( $name ) ];
    }

    /**
     * The shared service is built with the shared stores.
     */
    public function test_service_is_shared_with_the_stores() {
        $platform = wpuf()->platform();
        $tools    = $platform->get( ToolsService::class );

        $this->assertSame( $tools, $platform->get( ToolsService::class ) );
        $this->assertInstanceOf( TransactionStore::class, \WeDevs\Wpuf\Platform\Stores\Stores::transactions() );
    }

    /**
     * import_forms() creates each form through the store with its fields, settings and notifications; type and status allowlisted.
     */
    public function test_import_forms_creates_through_the_form_store() {
        $tools  = wpuf()->platform()->get( ToolsService::class );
        $export = [
            [
                'post_data' => [ 'post_title' => 'Imported', 'post_type' => 'wpuf_profile', 'post_status' => 'draft', 'comment_status' => 'closed' ],
                'meta_data' => [
                    'fields'        => [ $this->field( 'a' ), $this->field( 'b' ) ],
                    'settings'      => [ 'post_type' => 'post', 'role' => 'subscriber' ],
                    'notifications' => [ [ 'name' => 'Admin', 'to' => 'a@b.c' ] ],
                ],
            ],
            [
                'post_data' => [ 'post_title' => 'Sneaky', 'post_type' => 'post', 'post_status' => 'private' ],
                'meta_data' => [ 'fields' => [], 'settings' => [], 'notifications' => [] ],
            ],
        ];

        $this->assertTrue( $tools->import_forms( $export ) );

        $imported = $this->post_titled( 'Imported', 'wpuf_profile' );
        $this->assertSame( 'draft', $imported->post_status );
        $this->assertSame( 'closed', $imported->comment_status );
        $this->assertSame( [ 'a', 'b' ], wp_list_pluck( wpuf()->platform()->get( FieldStore::class )->read( $imported->ID ), 'name' ) );
        $this->assertSame( [ 'post_type' => 'post', 'role' => 'subscriber' ], get_post_meta( $imported->ID, 'wpuf_form_settings', true ) );
        $this->assertSame( [ [ 'name' => 'Admin', 'to' => 'a@b.c' ] ], get_post_meta( $imported->ID, 'notifications', true ) );
        $this->assertSame( '', get_post_meta( $imported->ID, 'wpuf_form_version', true ), 'an import stores no version, as before' );

        $sneaky = $this->post_titled( 'Sneaky', 'wpuf_forms' );
        $this->assertSame( 'wpuf_forms', $sneaky->post_type, 'an unknown type becomes a post form' );
        $this->assertSame( 'publish', $sneaky->post_status, 'an unknown status becomes publish' );
        $this->assertSame( [], get_post_meta( $sneaky->ID, 'wpuf_form_settings', true ), 'empty settings are stored as exported' );
    }

    /**
     * Admin_Tools::import_json_file() (the old AJAX import) runs through the service.
     */
    public function test_legacy_import_json_file_runs_through_the_service() {
        $file = wp_tempnam( 'wpuf-import' );
        file_put_contents( $file, wp_json_encode( [ [ 'post_data' => [ 'post_title' => 'Via file' ], 'meta_data' => [ 'fields' => [ $this->field( 'x' ) ], 'settings' => [ 'post_type' => 'post' ], 'notifications' => [] ] ] ] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

        $this->assertTrue( Admin_Tools::import_json_file( $file ) );
        $form = $this->post_titled( 'Via file', 'wpuf_forms' );
        $this->assertCount( 1, wpuf()->platform()->get( FieldStore::class )->read( $form->ID ) );
        unlink( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
    }

    /**
     * delete_post_type(): forms go with their field posts (FormStore), packs through SubscriptionStore, trash included.
     */
    public function test_delete_post_type_goes_through_the_stores() {
        $tools   = wpuf()->platform()->get( ToolsService::class );
        $forms   = wpuf()->platform()->get( FormStore::class );
        $form_id = $forms->create( [ 'post_title' => 'Doomed', 'fields' => [ $this->field( 'a' ) ] ] );
        $trashed = $forms->create( [ 'post_title' => 'Trashed', 'post_status' => 'trash', 'fields' => [ $this->field( 'b' ) ] ] );
        $field   = wpuf()->platform()->get( FieldStore::class )->read( $form_id )[0]['id'];
        $pack    = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Pack' ] );
        $other   = self::factory()->post->create( [ 'post_type' => 'post', 'post_title' => 'Stays' ] );

        $this->assertSame( 2, $tools->delete_post_type( 'wpuf_forms' ) );
        $this->assertNull( get_post( $form_id ) );
        $this->assertNull( get_post( $trashed ) );
        $this->assertNull( get_post( $field ), 'field posts go with the form' );

        $this->assertSame( 1, $tools->delete_post_type( 'wpuf_subscription' ) );
        $this->assertNull( get_post( $pack ) );
        $this->assertNotNull( get_post( $other ) );
        $this->assertWPError( $tools->delete_post_type( 'post' ) );
    }

    /**
     * reset_settings() deletes the sections through the settings store; forms() and export() read through the form store.
     */
    public function test_reset_listing_and_export_through_the_stores() {
        $tools = wpuf()->platform()->get( ToolsService::class );
        $forms = wpuf()->platform()->get( FormStore::class );

        update_option( 'wpuf_general', [ 'x' => 1 ] );
        update_option( 'wpuf_payment', [ 'y' => 2 ] );
        $tools->reset_settings();
        $this->assertFalse( get_option( 'wpuf_general' ) );
        $this->assertFalse( get_option( 'wpuf_payment' ) );

        $b = $forms->create( [ 'post_title' => 'B form', 'fields' => [ $this->field( 'b' ) ], 'settings' => [ 'post_type' => 'post' ] ] );
        $a = $forms->create( [ 'post_title' => 'A form', 'fields' => [ $this->field( 'a' ) ], 'settings' => [ 'post_type' => 'page' ] ] );
        $forms->create( [ 'post_title' => 'Draft', 'post_status' => 'draft' ] );

        $this->assertSame( [ 'A form', 'B form' ], wp_list_pluck( $tools->forms( 'wpuf_forms' ), 'title' ), 'published, by title' );

        $export = $tools->export( 'wpuf_forms', [ $a ] );
        $this->assertCount( 1, $export['forms'] );
        $this->assertSame( 'A form', $export['forms'][0]['post_data']['post_title'] );
        $this->assertArrayNotHasKey( 'ID', $export['forms'][0]['post_data'] );
        $this->assertSame( 'a', $export['forms'][0]['meta_data']['fields'][0]['name'] );
        $this->assertSame( 'page', $export['forms'][0]['meta_data']['settings']['post_type'] );
        $this->assertCount( 2, $tools->export( 'wpuf_forms' )['forms'], 'every published form without ids' );
        $this->assertWPError( $tools->export( 'post' ) );
    }

    /**
     * clear_transactions() truncates through the transaction store.
     */
    public function test_clear_transactions_truncates_through_the_store() {
        global $wpdb;

        if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wpuf_transaction' ) ) ) {
            $this->markTestSkipped( 'no transactions table in this test database' );
        }

        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 1, 'status' => 'completed', 'cost' => '1.00', 'payment_type' => 'bank' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        wpuf()->platform()->get( ToolsService::class )->clear_transactions();
        $this->assertSame( 0, wpuf()->platform()->get( TransactionStore::class )->count() );
    }
}
