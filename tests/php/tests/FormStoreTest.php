<?php
/**
 * Form and field stores (task 2.4a): the stores store the same bytes the
 * pre-store writers stored, and keep their hooks.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\Normalizers;

/**
 * @covers \WeDevs\Wpuf\Platform\Stores\FormStore
 * @covers \WeDevs\Wpuf\Platform\Stores\FieldStore
 * @covers \WeDevs\Wpuf\Platform\Stores\Normalizers
 */
class FormStoreTest extends WP_UnitTestCase {

    use WPUF_Form_Snapshot;

    /**
     * Form store under test.
     *
     * @var FormStore
     */
    private $store;

    public function set_up() {
        parent::set_up();
        $this->store = new FormStore( new FieldStore() );
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    private function new_form( $post_type = 'wpuf_forms' ) {
        return self::factory()->post->create( [ 'post_type' => $post_type, 'post_status' => 'draft', 'post_title' => 'Old title' ] );
    }

    /**
     * A builder payload with a new field, an edited stored field, a removed
     * field, slashes, quotes and unicode.
     */
    private function payload( $form_id, $kept_id ) {
        return [
            'form_id'           => $form_id,
            'post_title'        => 'Form "A" \\ ü',
            'form_fields'       => [
                [ 'id' => 123, 'is_new' => true, 'template' => 'text_field', 'label' => 'It\\\'s "new" ü', 'name' => 'text', 'read_only' => false ],
                [ 'id' => $kept_id, 'template' => 'textarea_field', 'label' => 'Kept \\d+', 'name' => 'kept', 'default' => '<b>x</b>' ],
            ],
            'form_settings'     => [ 'submit_text' => 'Go', 'notification' => [ 'new' => 'on', 'edit' => 'on' ], 'notification_edit' => 'on' ],
            'form_settings_key' => 'wpuf_form_settings',
            'notifications'     => [],
            'integrations'      => null,
        ];
    }

    private function seed_fields( $form_id ) {
        $kept    = wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'textarea_field', 'label' => 'Kept' ], null, 0 );
        $removed = wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'email_address', 'label' => 'Gone' ], null, 1 );

        return [ $kept, $removed ];
    }

    public function test_save_stores_the_same_bytes_as_the_legacy_builder_save() {
        $legacy_form = $this->new_form();
        $store_form  = $this->new_form();
        list( $legacy_kept ) = $this->seed_fields( $legacy_form );
        list( $store_kept )  = $this->seed_fields( $store_form );

        $legacy_saved = WPUF_Legacy_Form_Builder::save_form( $this->payload( $legacy_form, $legacy_kept ) );
        $store_saved  = $this->store->save( $store_form, $this->payload( $store_form, $store_kept ) );

        $this->assertEquals( $this->snapshot( $legacy_form ), $this->snapshot( $store_form ), 'post, field rows and meta identical' );
        $this->assertCount( 2, $store_saved );
        $this->assertFalse( $store_saved[0]['is_new'] );
        $this->assertSame( array_keys( $legacy_saved[0] ), array_keys( $store_saved[0] ), 'returned fields have the same shape' );
    }

    public function test_save_keeps_integrations_when_not_sent_and_writes_them_when_sent() {
        $form = $this->new_form();
        update_post_meta( $form, 'integrations', [ 'mailchimp' => [ 'enabled' => 'yes' ] ] );

        $this->store->save( $form, [ 'post_title' => 'T', 'integrations' => null ] );
        $this->assertSame( [ 'mailchimp' => [ 'enabled' => 'yes' ] ], get_post_meta( $form, 'integrations', true ) );

        $this->store->save( $form, [ 'post_title' => 'T', 'integrations' => [ 'x' => 1 ] ] );
        $this->assertSame( [ 'x' => 1 ], get_post_meta( $form, 'integrations', true ) );
    }

    public function test_save_fires_the_hooks_in_order_once() {
        $form  = $this->new_form();
        $calls = [];

        foreach ( [ 'wpuf_before_form_store_save', 'wpuf_form_builder_save_form', 'wpuf_after_form_store_save' ] as $hook ) {
            add_action(
                $hook,
                function ( $id ) use ( &$calls, $hook ) {
                    $calls[] = [ $hook, $id ];
                }
            );
        }

        $this->store->save( $form, [ 'post_title' => 'T' ] );

        $this->assertSame(
            [ [ 'wpuf_before_form_store_save', $form ], [ 'wpuf_form_builder_save_form', $form ], [ 'wpuf_after_form_store_save', $form ] ],
            $calls
        );
    }

    public function test_save_refuses_other_post_types_and_settings_keys() {
        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );

        $this->assertSame( 'wpuf_form_invalid_form', $this->store->save( $page, [ 'post_title' => 'x' ] )->get_error_code() );
        $this->assertSame( 'Old title', get_post( $this->new_form() )->post_title );
        $this->assertSame( 'wpuf_form_invalid_settings', $this->store->save( $this->new_form(), [ 'form_settings_key' => '_edit_lock' ] )->get_error_code() );
        $this->assertEmpty( get_post_meta( $page, 'wpuf_form_settings', true ), 'nothing written for a refused save' );
    }

    public function test_save_keeps_hidden_pro_taxonomy_fields_without_pro() {
        if ( wpuf_is_pro_active() ) {
            $this->markTestSkipped( 'Runs without Pro.' );
        }

        $form   = $this->new_form();
        $hidden = wpuf_legacy_insert_form_field( $form, [ 'input_type' => 'taxonomy', 'name' => 'product_cat', 'template' => 'taxonomy' ], null, 0 );

        $this->store->save( $form, [ 'post_title' => 'T', 'form_fields' => [] ] );

        $this->assertNotNull( get_post( $hidden ), 'a custom taxonomy field the builder does not show is kept' );
    }

    public function test_duplicate_matches_the_legacy_duplicate() {
        $source = $this->new_form();
        $this->seed_fields( $source );
        update_post_meta( $source, 'wpuf_form_settings', [ 'a' => 'b\\c' ] );
        update_post_meta( $source, 'notifications', [ [ 'name' => 'n' ] ] );
        update_post_meta( $source, 'integrations', [ 'x' => 1 ] );
        update_post_meta( $source, 'wpuf_form_version', '4.3.13' );

        $legacy = wpuf_legacy_duplicate_form( $source );
        $copy   = $this->store->duplicate( $source );

        $legacy_snapshot = $this->snapshot( $legacy );
        $store_snapshot  = $this->snapshot( $copy );
        $legacy_snapshot['post'][2] = str_replace( (string) $legacy, 'ID', $legacy_snapshot['post'][2] );
        $store_snapshot['post'][2]  = str_replace( (string) $copy, 'ID', $store_snapshot['post'][2] );

        $this->assertEquals( $legacy_snapshot, $store_snapshot );
        $this->assertNull( $this->store->duplicate( 999999 ) );
    }

    public function test_create_matches_the_sample_form_and_template_writers() {
        $legacy = wpuf_legacy_create_sample_form( 'Sample', 'wpuf_forms' );
        $sample = get_post( $legacy );
        $store  = $this->store->create(
            [
                'post_title'     => 'Sample',
                'post_type'      => 'wpuf_forms',
                'comment_status' => 'closed',
                'post_content'   => '',
                'fields'         => wp_list_pluck( array_map( function ( $row ) { return [ 'c' => maybe_unserialize( $row['post_content'] ) ]; }, $this->snapshot( $legacy )['fields'] ), 'c' ),
                'settings'       => get_post_meta( $legacy, 'wpuf_form_settings', true ),
            ]
        );
        $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $store ), 'sample form' );
        $this->assertSame( 'closed', $sample->comment_status );

        $template = new class() {
            public function get_title() { return 'Tpl'; }
            public function get_form_settings() { return [ 'redirect_to' => 'post' ]; }
            public function get_form_fields() { return [ [ 'template' => 'post_title', 'pattern' => '\\d+' ], [ 'template' => 'post_content' ] ]; }
        };
        $legacy_template = wpuf_legacy_create_form_from_template( $template );
        $store_template  = $this->store->create(
            [
                'post_title'     => $template->get_title(),
                'post_author'    => get_current_user_id(),
                'fields'         => $template->get_form_fields(),
                'unslash_fields' => false,
                'settings'       => $template->get_form_settings(),
                'settings_first' => true,
            ]
        );
        $this->assertEquals( $this->snapshot( $legacy_template ), $this->snapshot( $store_template ), 'template form (fields stored without unslash)' );
    }

    public function test_trash_keeps_fields_and_restore_brings_the_status_back() {
        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        list( $kept ) = $this->seed_fields( $form );

        $this->store->trash( $form );
        $this->assertSame( 'trash', get_post_status( $form ) );
        $this->assertNotNull( get_post( $kept ) );

        $this->store->restore( $form );
        $this->assertSame( 'publish', get_post_status( $form ) );
    }

    public function test_delete_permanently_removes_fields_only_when_the_form_is_gone() {
        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        list( $kept ) = $this->seed_fields( $form );

        $this->assertFalse( $this->store->delete_permanently( self::factory()->post->create( [ 'post_type' => 'page' ] ) ), 'other post types refused' );
        $this->assertTrue( $this->store->delete_permanently( $form ) );
        $this->assertNull( get_post( $form ) );
        $this->assertNull( get_post( $kept ) );
    }

    public function test_legacy_delete_matches_wpuf_delete_form() {
        $legacy = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        $store  = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        $this->seed_fields( $legacy );
        $this->seed_fields( $store );

        wpuf_legacy_delete_form( $legacy );
        $this->store->delete( $store );

        $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $store ) );
    }

    public function test_normalizer_drops_pro_notification_settings_only_without_pro() {
        $settings = [ 'notification_edit' => 'on', 'submit_text' => 'Go', 'notification' => [ 'new' => 'on', 'edit_to' => 'x' ] ];

        $this->assertSame( [ 'submit_text' => 'Go', 'notification' => [ 'new' => 'on' ] ], Normalizers::form_settings( $settings, false ) );
        $this->assertSame( $settings, Normalizers::form_settings( $settings, true ) );
        $this->assertSame( 'raw', Normalizers::form_settings( 'raw', false ) );
    }

    public function test_registration_user_status_is_filled_like_develops_hidden_input() {
        $this->assertSame( [ 'role' => 'subscriber', 'wpuf_user_status' => 'approved' ], Normalizers::registration_user_status( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( [ 'user_status' => 'on', 'wpuf_user_status' => 'pending' ], Normalizers::registration_user_status( [ 'user_status' => 'on' ] ) );
        $this->assertSame( [ 'user_status' => 'off', 'wpuf_user_status' => 'approved' ], Normalizers::registration_user_status( [ 'user_status' => 'off' ] ) );
        // A stored value stays as it is (templates store 'pending').
        $this->assertSame( [ 'wpuf_user_status' => 'pending' ], Normalizers::registration_user_status( [ 'wpuf_user_status' => 'pending' ] ) );
    }

    public function test_multistep_progressbar_type_is_filled_like_develops_select() {
        $this->assertSame( [ 'enable_multistep' => 'on', 'multistep_progressbar_type' => 'progressive' ], Normalizers::multistep_progressbar_type( [ 'enable_multistep' => 'on' ] ) );
        // A picked value stays; multi-step off adds nothing.
        $this->assertSame( [ 'enable_multistep' => 'on', 'multistep_progressbar_type' => 'step_by_step' ], Normalizers::multistep_progressbar_type( [ 'enable_multistep' => 'on', 'multistep_progressbar_type' => 'step_by_step' ] ) );
        $this->assertSame( [ 'enable_multistep' => 'off' ], Normalizers::multistep_progressbar_type( [ 'enable_multistep' => 'off' ] ) );
        $this->assertSame( [], Normalizers::multistep_progressbar_type( [] ) );
    }

    public function test_post_form_selects_store_develops_first_option() {
        $this->assertSame( [ 'label_position' => 'above', 'choose_payment_option' => 'force_pack_purchase' ], Normalizers::post_form_selects( [] ) );
        // Picked values stay.
        $this->assertSame(
            [ 'label_position' => 'left', 'choose_payment_option' => 'enable_pay_per_post' ],
            Normalizers::post_form_selects( [ 'label_position' => 'left', 'choose_payment_option' => 'enable_pay_per_post' ] )
        );
        $this->assertNull( Normalizers::post_form_selects( null ) );
    }

    public function test_registration_form_selects_store_develops_first_option() {
        $this->assertSame(
            [ 'label_position' => 'above', 'reg_redirect_to' => 'same', 'profile_redirect_to' => 'same' ],
            Normalizers::registration_form_selects( [] )
        );
        $result = Normalizers::registration_form_selects( [ 'reg_redirect_to' => 'url', 'label_position' => 'left' ] );
        $this->assertSame( 'url', $result['reg_redirect_to'] );
        $this->assertSame( 'left', $result['label_position'] );
        $this->assertSame( 'same', $result['profile_redirect_to'] );
        $this->assertNull( Normalizers::registration_form_selects( null ) );
    }

    public function test_post_expiration_stores_the_values_the_builder_shows() {
        $shown = [
            'enable_post_expiration'  => 'on',
            'expiration_time_value'   => '7',
            'expiration_time_type'    => 'day',
            'expired_post_status'     => 'draft',
            'post_expiration_message' => '',
        ];

        $this->assertSame( [ 'expiration_settings' => $shown ], Normalizers::post_expiration( [] ) );

        // Edited and stored values stay, only missing keys are filled.
        $result = Normalizers::post_expiration(
            [
                'expiration_settings' => [
                    'enable_post_expiration'    => 'off',
                    'expiration_time_value'     => '3',
                    'enable_mail_after_expired' => 'on',
                ],
            ]
        );
        $this->assertSame( 'off', $result['expiration_settings']['enable_post_expiration'] );
        $this->assertSame( '3', $result['expiration_settings']['expiration_time_value'] );
        $this->assertSame( 'on', $result['expiration_settings']['enable_mail_after_expired'] );
        $this->assertSame( 'day', $result['expiration_settings']['expiration_time_type'] );
        $this->assertSame( 'draft', $result['expiration_settings']['expired_post_status'] );
        $this->assertNull( Normalizers::post_expiration( null ) );
    }

    public function test_form_post_newlines_match_a_browser_form_post() {
        $value = [
            'new_body'     => "Hi\n\nThere\r\nEnd\rX",
            'single'       => 'no breaks',
            'on'           => true,
            'nested'       => [ 'body' => "a\nb" ],
            'already_crlf' => "a\r\nb",
        ];

        $this->assertSame(
            [
                'new_body'     => "Hi\r\n\r\nThere\r\nEnd\r\nX",
                'single'       => 'no breaks',
                'on'           => true,
                'nested'       => [ 'body' => "a\r\nb" ],
                'already_crlf' => "a\r\nb",
            ],
            Normalizers::form_post_newlines( $value )
        );
        $this->assertNull( Normalizers::form_post_newlines( null ) );

        // Untouched text keeps the stored bytes (untouched save changes nothing).
        $stored = [ 'body' => "a\nb", 'nested' => [ 'x' => "c\nd" ] ];
        $this->assertSame( $stored, Normalizers::form_post_newlines( [ 'body' => "a\nb", 'nested' => [ 'x' => "c\nd" ] ], $stored ) );
        $this->assertSame( [ 'body' => "a\r\nb\r\nz" ], Normalizers::form_post_newlines( [ 'body' => "a\nb\nz" ], $stored ) );
    }
}
