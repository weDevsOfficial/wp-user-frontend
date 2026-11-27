<?php
/**
 * The old form writers forward to the stores and still store the same bytes
 * (task 2.4a-3): each entry point is called for real and compared with the
 * pre-store copy in tests/php/src/LegacyFormWriters.php.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder;
use WeDevs\Wpuf\Admin\Forms\Form_Manager;
use WeDevs\Wpuf\Platform\Onboarding\Wizard;
use WeDevs\Wpuf\Hooks\Form_Settings_Cleanup;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Form writer shim tests.
 */
class FormWriterShimTest extends WP_UnitTestCase {

    use WPUF_Form_Snapshot;

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    private function new_form( $post_type = 'wpuf_forms' ) {
        return self::factory()->post->create( [ 'post_type' => $post_type, 'post_status' => 'draft', 'post_title' => 'Old title' ] );
    }

    private function seed_fields( $form_id ) {
        return [
            wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'textarea_field', 'label' => 'Kept' ], null, 0 ),
            wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'email_address', 'label' => 'Gone' ], null, 1 ),
        ];
    }

    private function payload( $form_id, $kept_id ) {
        return [
            'form_id'           => $form_id,
            'post_title'        => 'Form "A" \\ ü',
            'form_fields'       => [
                [ 'id' => 123, 'is_new' => true, 'template' => 'text_field', 'label' => 'It\\\'s "new" ü', 'name' => 'text' ],
                [ 'id' => $kept_id, 'template' => 'textarea_field', 'label' => 'Kept \\d+', 'name' => 'kept' ],
            ],
            'form_settings'     => [ 'submit_text' => 'Go', 'notification' => [ 'new' => 'on', 'edit' => 'on' ], 'notification_edit' => 'on' ],
            'form_settings_key' => 'wpuf_form_settings',
            'notifications'     => [ [ 'name' => 'Admin' ] ],
            'integrations'      => null,
        ];
    }

    /**
     * Snapshot with the new form id taken out of the title ("Title (#id)").
     */
    private function snapshot_without_id( $form_id ) {
        $snapshot            = $this->snapshot( $form_id );
        $snapshot['post'][2] = str_replace( (string) $form_id, 'ID', $snapshot['post'][2] );

        return $snapshot;
    }

    public function test_stores_resolve_from_the_container_and_are_shared() {
        $this->assertInstanceOf( FormStore::class, Stores::forms() );
        $this->assertInstanceOf( FieldStore::class, Stores::fields() );
        $this->assertSame( Stores::forms(), wpuf()->platform()->get( FormStore::class ) );
    }

    public function test_builder_save_shim_stores_the_same_bytes_and_fires_the_save_hook() {
        $legacy = $this->new_form();
        $shim   = $this->new_form();
        list( $legacy_kept ) = $this->seed_fields( $legacy );
        list( $shim_kept )   = $this->seed_fields( $shim );
        $fired = 0;
        add_action(
            'wpuf_form_builder_save_form',
            function () use ( &$fired ) {
                $fired++;
            }
        );

        $legacy_saved = WPUF_Legacy_Form_Builder::save_form( $this->payload( $legacy, $legacy_kept ) );
        $shim_saved   = Admin_Form_Builder::save_form( $this->payload( $shim, $shim_kept ) );

        $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ) );
        $this->assertSame( array_keys( $legacy_saved[0] ), array_keys( $shim_saved[0] ) );
        $this->assertSame( 1, $fired );
    }

    public function test_builder_save_shim_writes_nothing_for_a_post_that_is_not_a_form() {
        $page   = self::factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Page' ] );
        $before = $this->snapshot( $page );

        $this->assertSame( [], Admin_Form_Builder::save_form( $this->payload( $page, 0 ) ) );
        $this->assertEquals( $before, $this->snapshot( $page ) );
    }

    public function test_insert_form_field_matches_the_legacy_function() {
        $legacy = $this->new_form();
        $shim   = $this->new_form();
        $field  = [ 'template' => 'text_field', 'label' => 'A \\"q\\" ü', 'name' => 'a' ];

        $legacy_id = wpuf_legacy_insert_form_field( $legacy, $field, null, 3 );
        $shim_id   = wpuf_insert_form_field( $shim, $field, null, 3 );
        wpuf_legacy_insert_form_field( $legacy, [ 'label' => 'Updated' ], $legacy_id, 1 );
        wpuf_insert_form_field( $shim, [ 'label' => 'Updated' ], $shim_id, 1 );

        $this->assertIsInt( $shim_id );
        $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ) );
    }

    public function test_duplicate_shims_match_the_legacy_duplicate() {
        $source = $this->new_form();
        $this->seed_fields( $source );
        update_post_meta( $source, 'wpuf_form_settings', [ 'a' => 'b\\c' ] );
        update_post_meta( $source, 'notifications', [ [ 'name' => 'n' ] ] );
        update_post_meta( $source, 'integrations', [ 'x' => 1 ] );

        $legacy  = $this->snapshot_without_id( wpuf_legacy_duplicate_form( $source ) );
        $shim    = $this->snapshot_without_id( wpuf_duplicate_form( $source ) );
        $manager = $this->snapshot_without_id( ( new Form_Manager() )->duplicate( $source ) );

        $this->assertEquals( $legacy, $shim );
        $this->assertEquals( $legacy, $manager );
        $this->assertNull( wpuf_duplicate_form( 999999 ) );
    }

    public function test_sample_form_matches_the_legacy_function() {
        foreach ( [ [ 'Sample', 'wpuf_forms', false ], [ 'Registration', 'wpuf_profile', false ], [ 'Blank', 'wpuf_profile', true ] ] as $args ) {
            $legacy = call_user_func_array( 'wpuf_legacy_create_sample_form', $args );
            $shim   = call_user_func_array( 'wpuf_create_sample_form', $args );

            $this->assertIsInt( $shim );
            $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ), implode( ' / ', array_map( 'strval', $args ) ) );
        }
    }

    public function test_delete_shims_match_the_legacy_delete() {
        foreach ( [ true, false ] as $force ) {
            $legacy  = $this->new_form();
            $shim    = $this->new_form();
            $manager = $this->new_form();
            $this->seed_fields( $legacy );
            $this->seed_fields( $shim );
            $this->seed_fields( $manager );

            wpuf_legacy_delete_form( $legacy, $force );
            wpuf_delete_form( $shim, $force );
            ( new Form_Manager() )->delete( $manager, $force );

            $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ), $force ? 'forced' : 'trashed' );
            $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $manager ), $force ? 'forced' : 'trashed' );
        }
    }

    public function test_form_manager_create_matches_the_legacy_method() {
        $fields = [ [ 'template' => 'post_title', 'label' => 'T \\d' ], [ 'template' => 'post_content' ] ];

        $legacy = wpuf_legacy_form_manager_create( 'Managed', $fields );
        $shim   = ( new Form_Manager() )->create( 'Managed', $fields );

        $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ), 'no settings or version meta, fields unslashed' );
    }

    public function test_list_trash_and_restore_match_the_forms_list() {
        foreach ( [ 'legacy', 'store' ] as $side ) {
            $forms[ $side ] = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'Listed' ] );
            $this->seed_fields( $forms[ $side ] );
            update_post_meta( $forms[ $side ], '_wp_desired_post_slug', 'stale' );
        }

        wpuf_legacy_list_trash( $forms['legacy'] );
        Stores::forms()->trash( $forms['store'] );
        $this->assertSame( get_post_status( $forms['legacy'] ), get_post_status( $forms['store'] ) );
        $this->assertSame( get_post_meta( $forms['legacy'], '_wp_trash_meta_status', true ), get_post_meta( $forms['store'], '_wp_trash_meta_status', true ) );

        wpuf_legacy_list_restore( $forms['legacy'] );
        Stores::forms()->restore( $forms['store'] );
        $this->assertSame( 'publish', get_post_status( $forms['store'] ) );

        // WordPress makes the second form's slug unique ("listed-2").
        $unique = function ( $snapshot ) {
            return json_decode( preg_replace( '/listed-\d+/', 'listed', wp_json_encode( $snapshot ) ), true );
        };
        $this->assertEquals( $unique( $this->snapshot( $forms['legacy'] ) ), $unique( $this->snapshot( $forms['store'] ) ) );
    }

    public function test_onboarding_template_form_matches_the_legacy_writer() {
        $templates = [
            'tpl_full'  => new class() {
                public function get_title() { return 'Tpl'; }
                public function get_form_settings() { return [ 'redirect_to' => 'post' ]; }
                public function get_form_fields() { return [ [ 'template' => 'post_title', 'pattern' => '\\d+' ], [ 'template' => 'post_content' ] ]; }
            },
            'tpl_empty' => new class() {
                public function get_title() { return 'Empty'; }
                public function get_form_settings() { return []; }
                public function get_form_fields() { return []; }
            },
        ];
        add_filter(
            'wpuf_get_post_form_templates',
            function ( $registry ) use ( $templates ) {
                return array_merge( $registry, $templates );
            }
        );
        $handlers = wpuf()->platform()->get( Wizard::class )->handlers();

        foreach ( $templates as $name => $template ) {
            $legacy = wpuf_legacy_create_form_from_template( $template );
            $shim   = $handlers->create_form_from_template( $name );

            $this->assertEquals( $this->snapshot( $legacy ), $this->snapshot( $shim ), $name . ' (empty settings still stored, fields not unslashed)' );
        }

        $this->assertFalse( $handlers->create_form_from_template( 'no_such_template' ) );
    }

    public function test_settings_cleanup_strip_matches_the_legacy_strip() {
        $strip = new ReflectionMethod( Form_Settings_Cleanup::class, 'remove_pro_notification_settings' );
        $strip->setAccessible( true );
        $cleanup = ( new ReflectionClass( Form_Settings_Cleanup::class ) )->newInstanceWithoutConstructor();

        $inputs = [
            [ 'notification_edit' => 'on', 'notification_edit_body' => 'b', 'submit_text' => 'Go', 'notification' => [ 'new' => 'on', 'edit' => 'on', 'edit_subject' => 's' ] ],
            [ 'notification' => 'not an array' ],
            [],
            'raw',
        ];

        foreach ( $inputs as $input ) {
            $this->assertSame( wpuf_legacy_remove_pro_notification_settings( $input ), $strip->invoke( $cleanup, $input ) );
        }
    }
}
