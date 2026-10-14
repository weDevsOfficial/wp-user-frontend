<?php
/**
 * AI form builder on the platform (task 5c): screen, localized data contract,
 * assets, frozen REST routes registered once, shared services.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Forms\AI_Form_Handler;
use WeDevs\Wpuf\Admin\Screens\AiFormBuilder;
use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\AI\RestController;
use WeDevs\Wpuf\Platform\REST\Manager;

/**
 * AI form builder tests.
 */
class AiFormBuilderTest extends WP_UnitTestCase {

    /**
     * The `wpufAIFormBuilder` keys develop's Vue app got (Admin::enqueue_ai_form_builder_scripts()).
     */
    const DEVELOP_KEYS = [ 'version', 'assetUrl', 'siteUrl', 'nonce', 'rest_url', 'endpoints', 'formType', 'provider', 'model', 'hasApiKey', 'isProActive', 'temperature', 'maxTokens', 'promptTemplates', 'promptAIInstructions', 'i18n' ];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function tear_down() {
        unset( $_GET['action'], $_GET['description'], $_GET['prompt'], $_GET['form_id'], $_GET['form_title'] );
        remove_all_filters( 'wpuf_ai_form_builder_localize_data' );
        wp_dequeue_script( AiFormBuilder::HANDLE );
        wp_dequeue_style( AiFormBuilder::HANDLE );
        parent::tear_down();
    }

    public function test_services_are_shared_from_the_ai_manager() {
        $platform = wpuf()->platform();

        $this->assertSame( wpuf()->ai_manager->get_rest_controller(), $platform->get( RestController::class ) );
        $this->assertSame( wpuf()->ai_manager->get_form_generator(), $platform->get( FormGenerator::class ) );
        $this->assertInstanceOf( AI_Form_Handler::class, $platform->get( AI_Form_Handler::class ) );
        $this->assertSame( $platform->get( AiFormBuilder::class ), $platform->get( AiFormBuilder::class ) );
    }

    public function test_localized_data_keeps_develops_keys_and_adds_the_stage_keys() {
        $data = wpuf()->platform()->get( AiFormBuilder::class )->localize_data( 'profile' );

        foreach ( self::DEVELOP_KEYS as $key ) {
            $this->assertArrayHasKey( $key, $data, $key );
        }

        $this->assertSame( 'profile', $data['formType'] );
        $this->assertSame( [ 'generate', 'createForm', 'integrations' ], array_keys( $data['endpoints'] ) );
        $this->assertStringEndsWith( 'wpuf/v1/ai-form-builder/generate', $data['endpoints']['generate'] );
        $this->assertSame( 'input', $data['stage'] );
        $this->assertStringEndsWith( '/images/confetti_transparent.gif', $data['confettiUrl'] );
    }

    public function test_stage_keys_come_from_the_request() {
        $_GET['action']      = 'wpuf_ai_form_success';
        $_GET['description'] = 'A <b>contact</b> form';
        $_GET['form_title']  = 'Contact';

        $data = wpuf()->platform()->get( AiFormBuilder::class )->stage_data();

        $this->assertSame( 'success', $data['stage'] );
        $this->assertSame( 'A contact form', $data['description'] );
        $this->assertSame( 'Contact', $data['formTitle'] );
    }

    public function test_enqueue_prints_the_react_bundle_and_applies_the_filter_once() {
        $calls = 0;
        add_filter(
            'wpuf_ai_form_builder_localize_data',
            function ( $data ) use ( &$calls ) {
                ++$calls;
                $data['provider'] = 'anthropic';

                return $data;
            }
        );

        wpuf()->assets->register_all_scripts();
        wpuf()->platform()->get( AiFormBuilder::class )->enqueue( 'post' );

        $registry = wpuf()->platform()->get( WeDevs\Wpuf\Admin\React_Assets::class );
        $script   = wp_scripts()->registered[ AiFormBuilder::HANDLE ];
        $this->assertSame( 1, $calls );
        $this->assertStringEndsWith( '/assets/js/react/ai-form-builder.js', $registry->scripts()['ai-form-builder']['src'] );
        $this->assertStringEndsWith( '/assets/css/ai-form-builder-react.css', $registry->styles()['ai-form-builder']['src'] );
        $this->assertContains( 'wpuf-admin-ui', $script->deps );
        $this->assertSame( 'wp-user-frontend', $script->textdomain );
        $this->assertStringContainsString( '"provider":"anthropic"', $script->extra['data'] );
        set_current_screen( 'dashboard' ); // Core's site health body-class callback needs a screen.
        $this->assertStringContainsString( 'wpuf-admin-react', apply_filters( 'admin_body_class', '' ) );
    }

    public function test_admin_listener_forwards_to_the_screen() {
        wpuf()->assets->register_all_scripts();
        // Admin (the `wpuf_load_ai_form_builder_page` listener) only loads in wp-admin.
        ( new ReflectionClass( WeDevs\Wpuf\Admin::class ) )->newInstanceWithoutConstructor()->enqueue_ai_form_builder_scripts( 'post' );

        $this->assertTrue( wp_script_is( AiFormBuilder::HANDLE, 'enqueued' ) );
        $this->assertTrue( wp_style_is( AiFormBuilder::HANDLE, 'enqueued' ) );
    }

    public function test_routes_register_once_through_the_manager() {
        global $wp_rest_server;

        $wp_rest_server = new WP_REST_Server(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        do_action( 'rest_api_init', $wp_rest_server );
        $routes = $wp_rest_server->get_routes();

        foreach ( [ 'generate', 'create-form', 'integrations', 'models', 'test', 'settings' ] as $route ) {
            $methods = [];

            foreach ( $routes[ '/wpuf/v1/ai-form-builder/' . $route ] as $endpoint ) {
                $methods = array_merge( $methods, array_keys( $endpoint['methods'] ) );
            }

            $this->assertSame( array_unique( $methods ), $methods, $route . ' registered once' );
        }

        $this->assertContains( wpuf()->ai_manager->get_rest_controller(), wpuf()->platform()->get( Manager::class )->controllers() );

        $wp_rest_server = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
    }

    public function test_read_routes_answer_through_the_services() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        foreach ( [ 'providers', 'settings', 'models', 'integrations' ] as $route ) {
            $response = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/ai-form-builder/' . $route ) );
            $this->assertSame( 200, $response->get_status(), $route );
            $this->assertIsArray( $response->get_data(), $route );
        }
    }

    public function test_generate_without_a_key_fails_without_a_provider_request() {
        update_option( 'wpuf_ai', [ 'ai_provider' => 'openai', 'ai_model' => 'gpt-4o-mini', 'openai_api_key' => '' ] );

        $requests = 0;
        $count    = function ( $pre ) use ( &$requests ) {
            ++$requests;
            return $pre;
        };
        add_filter( 'pre_http_request', $count );

        $request = new WP_REST_Request( 'POST', '/wpuf/v1/ai-form-builder/generate' );
        $request->set_param( 'prompt', 'Create a contact form' );
        $result = wpuf()->platform()->get( RestController::class )->generate_form( $request );

        remove_filter( 'pre_http_request', $count );
        delete_option( 'wpuf_ai' );

        $this->assertWPError( $result );
        $this->assertSame( 'generation_failed', $result->get_error_code() );
        $this->assertStringContainsString( 'No API key', $result->get_error_message() );
        $this->assertSame( 0, $requests );
    }

    public function test_modify_form_needs_the_form_and_edit_rights_on_it() {
        $form_id = wpuf_create_sample_form( 'AI target', 'wpuf_forms' );
        $post_id = self::factory()->post->create();
        $modify  = function ( $id ) {
            $request = new WP_REST_Request( 'POST', '/wpuf/v1/ai-form-builder/modify-form' );
            $request->set_param( 'form_id', $id );
            $request->set_param( 'modification_data', [ 'action' => 'noop' ] );

            return rest_do_request( $request )->get_status();
        };

        $this->assertSame( 404, $modify( $post_id ), 'not a form' );

        // An editor may use the AI builder but not edit an admin-only form.
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 403, $modify( $form_id ) );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $modify( $form_id ) );

        wp_set_current_user( 0 );
        $this->assertSame( 401, $modify( $form_id ) );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $this->assertNotContains( $modify( $form_id ), [ 401, 403, 404 ], 'an admin reaches the handler' );
    }
}
