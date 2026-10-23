<?php
/**
 * Admin forms REST routes and the builder save service (task 5b.4)
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Builder\FormSave;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\REST\Controllers\FormsController;
use WeDevs\Wpuf\Platform\REST\RestController;

/**
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\FormsController
 * @covers \WeDevs\Wpuf\Builder\FormSave
 * @covers \WeDevs\Wpuf\Platform\Providers\RestServiceProvider
 */
class FormsRestTest extends WP_UnitTestCase {

    /**
     * REST server for the test.
     *
     * @var WP_REST_Server
     */
    private $server;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;

        do_action( 'rest_api_init', $this->server );
    }

    public function tear_down() {
        global $wp_rest_server;
        $wp_rest_server = null;

        parent::tear_down();
    }

    /**
     * A post form with a title field.
     *
     * @return int
     */
    private function make_form() {
        $form_id = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'REST form' ] );

        update_post_meta( $form_id, 'wpuf_form_settings', [ 'post_type' => 'post', 'submit_text' => 'Submit' ] );

        return $form_id;
    }

    /**
     * The builder payload for a form.
     *
     * @param int   $form_id Form id
     * @param array $settings Settings to send
     *
     * @return array
     */
    private function payload( $form_id, $settings = [ 'post_type' => 'post', 'submit_text' => 'Send' ] ) {
        return [
            'form_data'            => http_build_query(
                [
                    'wpuf_form_id'      => $form_id,
                    'form_settings_key' => 'wpuf_form_settings',
                    'post_title'        => 'REST form saved',
                ]
            ),
            'form_fields'          => wp_json_encode(
                [
                    [
                        'input_type' => 'text',
                        'template'   => 'post_title',
                        'name'       => 'post_title',
                        'label'      => 'Title',
                        'is_new'     => true,
                    ],
                ]
            ),
            'notifications'        => '[]',
            'settings'             => wp_json_encode( $settings ),
            'legacy_settings_keys' => '[]',
        ];
    }

    /**
     * POST the payload to the route.
     *
     * @param int   $route_id Form id in the route
     * @param array $body     Body params
     *
     * @return WP_REST_Response
     */
    private function post( $route_id, $body ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $route_id );
        $request->set_body_params( $body );

        return $this->server->dispatch( $request );
    }

    public function test_routes_registered_once_and_controllers_share_the_base() {
        $routes = $this->server->get_routes();

        $this->assertCount( 3, $routes['/wpuf/v1/admin/forms/(?P<id>[\d]+)'] );
        foreach ( [ 'duplicate', 'trash', 'restore' ] as $action ) {
            $this->assertCount( 1, $routes[ '/wpuf/v1/admin/forms/(?P<id>[\d]+)/' . $action ] );
        }
        $this->assertCount( 1, $routes['/wpuf/v1/wpuf_form'] );
        $this->assertCount( 2, $routes['/wpuf/v1/settings'] );

        foreach ( [ \WeDevs\Wpuf\Platform\REST\Controllers\FormListController::class, \WeDevs\Wpuf\Platform\REST\Controllers\SubscriptionController::class, \WeDevs\Wpuf\Platform\REST\Controllers\SettingsController::class, FormsController::class ] as $class ) {
            $this->assertTrue( is_subclass_of( $class, RestController::class ), $class );
        }

        $this->assertSame( wpuf()->api->form_list, wpuf()->platform()->get( \WeDevs\Wpuf\Platform\REST\Controllers\FormListController::class ) );
    }

    public function test_permissions_401_403_404() {
        $form_id = $this->make_form();

        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $form_id ) )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $form_id ) )->get_status() );
        $this->assertSame( 403, $this->post( $form_id, $this->payload( $form_id ) )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        $this->assertSame( 404, $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $page ) )->get_status() );

        $read = $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $form_id ) );
        $this->assertSame( 200, $read->get_status() );
        $this->assertSame( 'wpuf_forms', $read->get_data()['data']['post_type'] );
    }

    public function test_route_id_must_match_the_payload_form() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $form_a = $this->make_form();
        $form_b = $this->make_form();

        $response = $this->post( $form_a, $this->payload( $form_b ) );

        $this->assertSame( 400, $response->get_status() );
        $this->assertSame( 'wpuf_form_invalid_id', $response->get_data()['code'] );
    }

    public function test_rest_save_stores_what_the_save_service_stores() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $rest_form    = $this->make_form();
        $service_form = $this->make_form();

        $response = $this->post( $rest_form, $this->payload( $rest_form ) );
        $this->assertSame( 200, $response->get_status() );
        $this->assertTrue( $response->get_data()['success'] );

        $payload = $this->payload( $service_form );
        parse_str( $payload['form_data'], $form_data );
        ( new FormSave() )->save( $payload, $form_data );

        $this->assertSame( get_post_meta( $service_form, 'wpuf_form_settings', true ), get_post_meta( $rest_form, 'wpuf_form_settings', true ) );
        $this->assertSame( 'Send', get_post_meta( $rest_form, 'wpuf_form_settings', true )['submit_text'] );
        $this->assertSame( 'REST form saved', get_post( $rest_form )->post_title );
        $this->assertCount( 1, wpuf_get_form_fields( $rest_form ) );
        $this->assertSame( $response->get_data()['data']['form_settings'], get_post_meta( $rest_form, 'wpuf_form_settings', true ) );
    }

    public function test_shown_first_options_stored_on_create_and_edited_save_only() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        // Untouched save of an existing form: nothing added (G3).
        $form_id = $this->make_form();
        $this->assertSame( 200, $this->post( $form_id, $this->payload( $form_id ) )->get_status() );
        $this->assertArrayNotHasKey( 'label_position', get_post_meta( $form_id, 'wpuf_form_settings', true ) );

        // Edited save: the select's shown first option is stored.
        $this->assertSame( 200, $this->post( $form_id, array_merge( $this->payload( $form_id ), [ 'touched' => '1' ] ) )->get_status() );
        $settings = get_post_meta( $form_id, 'wpuf_form_settings', true );
        $this->assertSame( 'above', $settings['label_position'] );
        $this->assertSame( 'force_pack_purchase', $settings['choose_payment_option'] );

        // A new form has them from its creation.
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms' );
        $request->set_body_params( [ 'type' => 'wpuf_forms' ] );
        $created = $this->server->dispatch( $request );
        $this->assertSame( 201, $created->get_status() );
        $this->assertSame( 'above', get_post_meta( $created->get_data()['data']['id'], 'wpuf_form_settings', true )['label_position'] );
    }

    public function test_save_never_updates_a_post_that_is_not_a_field_of_the_form() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        $page_id = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'private', 'post_title' => 'Private page', 'post_content' => 'secret' ] );
        $form_id = $this->make_form();
        $body    = $this->payload( $form_id );
        $fields  = json_decode( $body['form_fields'], true );

        // A field carrying another post's id (no is_new) is inserted as a new field.
        $fields[0]['id']     = $page_id;
        $body['form_fields'] = wp_json_encode( $fields );

        $this->assertSame( 200, $this->post( $form_id, $body )->get_status() );

        $page = get_post( $page_id );
        $this->assertSame( 'page', $page->post_type );
        $this->assertSame( 'secret', $page->post_content );
        $this->assertSame( 0, (int) $page->post_parent );
        $this->assertCount( 1, wpuf_get_form_fields( $form_id ) );
    }

    public function test_create_from_template_matches_the_template_link() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms' );
        $request->set_body_params( [ 'type' => 'wpuf_forms', 'template' => 'post_form_template_post' ] );
        $created = $this->server->dispatch( $request );
        $this->assertSame( 201, $created->get_status() );
        $rest_form = $created->get_data()['data']['id'];

        // The template link's path stores the same form.
        $link_form = ( new \WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Templates() )->create_from_template( 'post_form_template_post' );
        $strip     = function ( $form_id ) {
            return array_map(
                function ( $field ) {
                    unset( $field['id'] );
                    return $field;
                },
                wpuf_get_form_fields( $form_id )
            );
        };

        $this->assertSame( get_post( $link_form )->post_title, get_post( $rest_form )->post_title );
        $this->assertSame( get_post_meta( $link_form, 'wpuf_form_settings', true ), get_post_meta( $rest_form, 'wpuf_form_settings', true ) );
        $this->assertSame( $strip( $link_form ), $strip( $rest_form ) );
        $this->assertNotEmpty( $strip( $rest_form ) );

        // Unknown templates are refused, nothing created.
        $count   = function () {
            return count( get_posts( [ 'post_type' => 'wpuf_forms', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] ) );
        };
        $before  = $count();
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms' );
        $request->set_body_params( [ 'type' => 'wpuf_forms', 'template' => 'no_such_template' ] );
        $this->assertSame( 400, $this->server->dispatch( $request )->get_status() );
        $this->assertSame( $before, $count() );

        // Subscribers cannot create.
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms' );
        $request->set_body_params( [ 'type' => 'wpuf_forms', 'template' => 'post_form_template_post' ] );
        $this->assertSame( 403, $this->server->dispatch( $request )->get_status() );
    }

    public function test_save_service_rejects_bad_payloads() {
        $form_id = $this->make_form();
        $saver   = new FormSave();

        $this->assertSame( 'wpuf_form_invalid_id', $saver->save( [], [] )->get_error_code() );

        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        $this->assertSame( 'wpuf_form_invalid_id', $saver->save( [], [ 'wpuf_form_id' => $page, 'form_settings_key' => 'wpuf_form_settings' ] )->get_error_code() );

        $this->assertSame( 'wpuf_form_invalid_settings', $saver->save( [], [ 'wpuf_form_id' => $form_id, 'form_settings_key' => '_edit_lock' ] )->get_error_code() );

        $payload = $this->payload( $form_id, [ 'payment_options' => 'on', 'choose_payment_option' => 'force_pack_purchase', 'fallback_ppp_enable' => 'on', 'fallback_ppp_cost' => '' ] );
        parse_str( $payload['form_data'], $form_data );
        $this->assertSame( 'wpuf_form_ppp_cost_required', $saver->save( $payload, $form_data )->get_error_code() );

        foreach ( [ '', '0', '0.00', '-1', 'abc' ] as $cost ) {
            $payload = $this->payload( $form_id, [ 'payment_options' => 'true', 'choose_payment_option' => 'enable_pay_per_post', 'pay_per_post_cost' => $cost ] );
            parse_str( $payload['form_data'], $form_data );
            $result = $saver->save( $payload, $form_data );
            $this->assertInstanceOf( WP_Error::class, $result, "pay per post cost '{$cost}'" );
            $this->assertSame( 'wpuf_form_ppp_cost_required', $result->get_error_code() );
        }

        // Missing fields or settings, or broken JSON, never wipe the stored form.
        $fields_before = Stores::forms()->read_meta( $form_id, 'wpuf_form_settings' );
        foreach ( [ 'form_fields', 'settings' ] as $key ) {
            $payload = $this->payload( $form_id );
            parse_str( $payload['form_data'], $form_data );
            unset( $payload[ $key ] );
            $this->assertSame( 'wpuf_form_invalid_payload', $saver->save( $payload, $form_data )->get_error_code(), "without {$key}" );

            $payload         = $this->payload( $form_id );
            $payload[ $key ] = '{broken';
            $this->assertSame( 'wpuf_form_invalid_payload', $saver->save( $payload, $form_data )->get_error_code(), "broken {$key}" );
        }
        $this->assertSame( $fields_before, Stores::forms()->read_meta( $form_id, 'wpuf_form_settings' ) );

        $payload = $this->payload( $form_id, [ 'payment_options' => 'true', 'choose_payment_option' => 'enable_pay_per_post', 'pay_per_post_cost' => '2.5' ] );
        parse_str( $payload['form_data'], $form_data );
        $this->assertNotInstanceOf( WP_Error::class, $saver->save( $payload, $form_data ) );
    }

    public function test_edited_save_refuses_empty_required_settings() {
        $form_id = $this->make_form();
        $saver   = new FormSave();
        $save    = function ( $settings, $touched = '1' ) use ( $form_id, $saver ) {
            $payload = array_merge( $this->payload( $form_id, $settings ), [ 'touched' => $touched ] );
            parse_str( $payload['form_data'], $form_data );

            return $saver->save( $payload, $form_data );
        };

        $cases = [
            [ [ 'submit_text' => ' ' ], 'submit_text' ],
            [ [ 'submit_text' => 'Go', 'redirect_to' => 'page', 'page_id' => '0' ], 'page_id' ],
            [ [ 'submit_text' => 'Go', 'redirect_to' => 'url', 'url' => 'example.com' ], 'url' ],
            [ [ 'submit_text' => 'Go', 'edit_redirect_to' => 'same', 'update_message' => '<p></p>' ], 'update_message' ],
        ];

        foreach ( $cases as $case ) {
            $result = $save( $case[0] );
            $this->assertInstanceOf( WP_Error::class, $result, $case[1] );
            $this->assertSame( 'wpuf_form_required_settings', $result->get_error_code() );
            $this->assertStringContainsString( $case[1], $result->get_error_message() );
        }

        // A hidden setting (other redirect choice) and an untouched save are not checked.
        $this->assertNotInstanceOf( WP_Error::class, $save( [ 'submit_text' => 'Go', 'redirect_to' => 'post', 'page_id' => '' ] ) );
        $this->assertNotInstanceOf( WP_Error::class, $save( [ 'submit_text' => '' ], '' ) );
        $this->assertNotInstanceOf( WP_Error::class, $save( [ 'submit_text' => 'Go', 'redirect_to' => 'url', 'url' => 'https://example.com/thanks' ] ) );
    }

    public function test_pay_per_post_with_no_cost_charges_nothing() {
        $form_id = $this->make_form();
        $charge  = function ( $settings ) use ( $form_id ) {
            update_post_meta( $form_id, 'wpuf_form_settings', $settings );

            return ( new \WeDevs\Wpuf\Admin\Forms\Form( $form_id ) )->is_charging_enabled();
        };

        foreach ( [ null, '', '0', '0.00', '-3' ] as $cost ) {
            $settings = [ 'payment_options' => 'true', 'choose_payment_option' => 'enable_pay_per_post' ];

            if ( null !== $cost ) {
                $settings['pay_per_post_cost'] = $cost;
            }

            $this->assertFalse( $charge( $settings ), 'pay per post cost ' . var_export( $cost, true ) );
        }

        $this->assertTrue( $charge( [ 'payment_options' => 'true', 'choose_payment_option' => 'enable_pay_per_post', 'pay_per_post_cost' => '5' ] ) );
        $this->assertTrue( $charge( [ 'payment_options' => 'true', 'choose_payment_option' => 'force_pack_purchase' ] ) );
        $this->assertFalse( $charge( [ 'payment_options' => 'false', 'choose_payment_option' => 'enable_pay_per_post', 'pay_per_post_cost' => '5' ] ) );

        // A guest can still post on a pay per post form that costs nothing.
        wp_set_current_user( 0 );
        update_post_meta( $form_id, 'wpuf_form_settings', [ 'payment_options' => 'true', 'choose_payment_option' => 'enable_pay_per_post', 'pay_per_post_cost' => '0', 'post_permission' => 'guest_post' ] );
        $form = new \WeDevs\Wpuf\Admin\Forms\Form( $form_id );
        $this->assertSame( 'yes', $form->is_submission_open( $form, $form->get_settings() )[0] );
    }

    public function test_list_actions_duplicate_trash_restore_delete() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $form_id = $this->make_form();
        $this->post( $form_id, $this->payload( $form_id ) );

        $dup = $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/duplicate' ) );
        $this->assertSame( 201, $dup->get_status() );
        $copy = $dup->get_data()['data']['id'];
        $this->assertSame( 'draft', get_post_status( $copy ) );
        $this->assertSame( get_post_meta( $form_id, 'wpuf_form_settings', true ), get_post_meta( $copy, 'wpuf_form_settings', true ) );
        $this->assertCount( 1, wpuf_get_form_fields( $copy ) );

        $not_trashed = $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/restore' ) );
        $this->assertSame( 400, $not_trashed->get_status() );

        $trash = $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/trash' ) );
        $this->assertSame( 200, $trash->get_status() );
        $this->assertSame( 'trash', get_post_status( $form_id ) );

        $restore = $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/restore' ) );
        $this->assertSame( 200, $restore->get_status() );
        $this->assertSame( 'publish', get_post_status( $form_id ) );

        $delete = $this->server->dispatch( new WP_REST_Request( 'DELETE', '/wpuf/v1/admin/forms/' . $copy ) );
        $this->assertSame( 200, $delete->get_status() );
        $this->assertNull( get_post( $copy ) );
        $this->assertCount( 0, wpuf_get_form_fields( $copy ) );

        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        foreach ( [ 'duplicate', 'trash', 'restore' ] as $action ) {
            $this->assertSame( 404, $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $page . '/' . $action ) )->get_status() );
        }
        $this->assertSame( 404, $this->server->dispatch( new WP_REST_Request( 'DELETE', '/wpuf/v1/admin/forms/' . $page ) )->get_status() );
        $this->assertSame( 'publish', get_post_status( $page ) );
    }

    public function test_trash_restore_delete_need_the_delete_cap_of_the_form() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $form_id = $this->make_form();

        // A site that splits the form caps: deleting a form needs a cap the admin lacks.
        $split = function ( $args, $post_type ) {
            if ( 'wpuf_forms' === $post_type ) {
                $args['capabilities']['delete_post'] = 'wpuf_test_delete_forms';
            }

            return $args;
        };
        add_filter( 'register_post_type_args', $split, 10, 2 );
        // The admin layer registers the form post types (and their caps); it is not loaded here.
        ( new \WeDevs\Wpuf\Admin\Forms\Admin_Form() )->register_post_type();
        remove_filter( 'register_post_type_args', $split, 10 );

        foreach ( [ 'trash', 'restore' ] as $action ) {
            $this->assertSame( 403, $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/' . $action ) )->get_status() );
        }
        $this->assertSame( 403, $this->server->dispatch( new WP_REST_Request( 'DELETE', '/wpuf/v1/admin/forms/' . $form_id ) )->get_status() );
        $this->assertSame( 'publish', get_post_status( $form_id ) );

        // Editing and copying stay allowed.
        $this->assertSame( 200, $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $form_id ) )->get_status() );
        $this->assertSame( 201, $this->server->dispatch( new WP_REST_Request( 'POST', '/wpuf/v1/admin/forms/' . $form_id . '/duplicate' ) )->get_status() );

        unregister_post_type( 'wpuf_forms' );
        unregister_post_type( 'wpuf_profile' );
    }

    public function test_save_gives_listeners_the_develop_request_and_restores_it() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $form_id = $this->make_form();
        $seen    = null;

        // Like the Pro BuddyPress module: reads wpuf_settings from $_REQUEST['form_data'] on save_post.
        $listener = function ( $post_id ) use ( $form_id, &$seen ) {
            if ( $post_id !== $form_id || ! isset( $_REQUEST['form_data'] ) ) {
                return;
            }
            parse_str( wp_unslash( $_REQUEST['form_data'] ), $data );
            $seen = $data;
        };
        add_action( 'save_post', $listener, 1 );

        $payload = $this->payload( $form_id, [ 'post_type' => 'post', '_wpuf_bp_mapping' => [ 'first_name' => '7' ] ] );
        $this->assertSame( 200, $this->post( $form_id, $payload )->get_status() );
        remove_action( 'save_post', $listener, 1 );

        $this->assertIsArray( $seen );
        $this->assertSame( (string) $form_id, $seen['wpuf_form_id'] );
        $this->assertSame( [ 'first_name' => '7' ], $seen['wpuf_settings']['_wpuf_bp_mapping'] );
        $this->assertArrayNotHasKey( 'form_data', $_REQUEST );
        $this->assertArrayNotHasKey( 'form_data', $_POST );
    }

    public function test_free_only_rest_requests_attach_the_pro_settings_cleanup() {
        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Free-only behaviour.' );
        }

        $loader = wpuf()->free_loader;
        $this->assertNotNull( $loader );

        remove_all_actions( 'wpuf_form_builder_save_form' );
        // The cleanup is a shared service now: drop the instance an earlier test built so its hooks are added afresh.
        wpuf()->platform()->forget( \WeDevs\Wpuf\Hooks\Form_Settings_Cleanup::class );
        $loader->boot_rest_cleanup();
        $loader->boot_rest_cleanup();

        $this->assertNotFalse( has_action( 'wpuf_form_builder_save_form' ) );
        $this->assertCount( 1, $GLOBALS['wp_filter']['wpuf_form_builder_save_form']->callbacks[10], 'attached once' );
    }
}
