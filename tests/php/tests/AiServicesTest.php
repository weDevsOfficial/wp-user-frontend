<?php
/**
 * AI form builder on the platform: controller base, services, stores
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\AI\RestController;
use WeDevs\Wpuf\AI\Services\Field_Options;
use WeDevs\Wpuf\AI\Services\Form_Writer;
use WeDevs\Wpuf\AI\Services\Generation;
use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Platform\REST\RestController as PlatformRestController;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;

/**
 * Provider client double: answers without a provider request.
 */
class WPUF_Test_AI_Generator extends FormGenerator {

    /**
     * The answer generate_form() gives.
     *
     * @var array
     */
    public $answer = [];

    /**
     * Prompts received.
     *
     * @var array
     */
    public $prompts = [];

    /**
     * Answer with the canned response.
     *
     * @param string $prompt  Prompt
     * @param array  $options Options
     *
     * @return array
     */
    public function generate_form( $prompt, $options = [] ) {
        $this->prompts[] = $prompt;

        return $this->answer;
    }
}

/**
 * AI controller on the platform base, services with plain arguments, persistence through the stores.
 */
class AiServicesTest extends WP_UnitTestCase {

    /**
     * No provider request leaves the test.
     *
     * @var callable
     */
    private $block_http;

    public function set_up() {
        parent::set_up();
        $this->block_http = function () {
            return new WP_Error( 'blocked', 'no HTTP in tests' );
        };
        add_filter( 'pre_http_request', $this->block_http );
    }

    public function tear_down() {
        remove_filter( 'pre_http_request', $this->block_http );
        delete_option( 'wpuf_ai' );
        delete_transient( 'wpuf_ai_models_cache' );
        parent::tear_down();
    }

    /**
     * Number of post forms (any status).
     *
     * @return int
     */
    private function form_count() {
        return count( get_posts( [ 'post_type' => 'wpuf_forms', 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => -1 ] ) );
    }

    /**
     * A text field definition the registry accepts.
     *
     * @param string $name Field name
     *
     * @return array
     */
    private function text_field( $name ) {
        return [
            'template'   => 'text_field',
            'input_type' => 'text',
            'name'       => $name,
            'label'      => ucfirst( $name ),
            'required'   => 'no',
        ];
    }

    /**
     * The controller comes from the provider on the platform base with the shared services (no AJAX hook).
     */
    public function test_controller_is_built_by_the_provider_with_the_shared_services() {
        $platform   = wpuf()->platform();
        $controller = $platform->get( RestController::class );

        $this->assertInstanceOf( PlatformRestController::class, $controller );
        $this->assertSame( $controller, wpuf()->ai_manager->get_rest_controller() );

        foreach ( [ Generation::class, Provider_Settings::class, Field_Options::class, Form_Writer::class ] as $service ) {
            $this->assertSame( $platform->get( $service ), $platform->get( $service ), $service );
        }

        // The builder calls REST `ai-form-builder/generate-options`: no admin-ajax action.
        $this->assertFalse( has_action( 'wp_ajax_wpuf_ai_generate_field_options' ) );
    }

    /**
     * Routes: logged out 401, no builder capability 403, the AI settings need the site capability.
     */
    public function test_routes_need_login_the_builder_capability_and_the_site_capability_for_settings() {
        $get = function ( $route ) {
            return rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/ai-form-builder/' . $route ) )->get_status();
        };

        wp_set_current_user( 0 );
        $this->assertSame( 401, $get( 'providers' ) );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $get( 'providers' ) );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 200, $get( 'providers' ) );
        $this->assertSame( 200, $get( 'integrations' ) );
        $this->assertSame( 403, $get( 'settings' ) );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $this->assertSame( 200, $get( 'settings' ) );
        $this->assertSame( 200, $get( 'models' ) );
    }

    /**
     * Form_Writer::create() stores the form, its fields, settings, version and AI meta through the stores.
     */
    public function test_form_writer_creates_the_form_through_the_stores() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $writer = wpuf()->platform()->get( Form_Writer::class );
        $fields = [ $this->text_field( 'first_name' ), $this->text_field( 'last_name' ) ];

        $result = $writer->create(
            [
                'form_title'       => 'AI created <b>form</b>',
                'form_description' => 'From a prompt',
                'wpuf_fields'      => $fields,
                'form_settings'    => [ 'post_type' => 'product' ],
            ],
            'post'
        );

        $this->assertIsArray( $result );
        $this->assertTrue( $result['success'] );
        $form_id = $result['form_id'];
        $form    = get_post( $form_id );

        $this->assertSame( 'wpuf_forms', $form->post_type );
        $this->assertSame( 'AI created form', $form->post_title );
        $this->assertSame( get_current_user_id(), (int) $form->post_author );
        $this->assertSame( WPUF_VERSION, get_post_meta( $form_id, 'wpuf_form_version', true ) );
        $this->assertSame( 'post_form_template_woocommerce', get_post_meta( $form_id, 'wpuf_form_settings', true )['form_template'] );
        $this->assertSame( 'product', get_post_meta( $form_id, 'wpuf_form_settings', true )['post_type'] );
        $this->assertTrue( (bool) get_post_meta( $form_id, 'wpuf_ai_generated', true ) );
        $this->assertSame( get_current_user_id(), (int) get_post_meta( $form_id, 'wpuf_ai_created_by', true ) );
        $this->assertCount( 2, get_post_meta( $form_id, 'wpuf_form_fields', true ) );

        $stored = wpuf()->platform()->get( FieldStore::class )->read( $form_id );
        $this->assertSame( [ 'first_name', 'last_name' ], wp_list_pluck( $stored, 'name' ) );
        $this->assertStringContainsString( "page=wpuf-post-forms&action=edit&id={$form_id}", $result['edit_url'] );
    }

    /**
     * A field whose template and input type do not match is refused before any post exists.
     */
    public function test_form_writer_refuses_a_mismatched_field_without_creating_anything() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $writer = wpuf()->platform()->get( Form_Writer::class );
        $before = $this->form_count();
        $bad    = array_merge( $this->text_field( 'x' ), [ 'input_type' => 'textarea' ] );

        $result = $writer->create( [ 'form_title' => 'Bad', 'wpuf_fields' => [ $bad ] ] );

        $this->assertWPError( $result );
        $this->assertSame( 'invalid_field_definition', $result->get_error_code() );
        $this->assertSame( $before, $this->form_count() );
    }

    /**
     * Form_Writer::modify(): an add_field answer rewrites the fields through the stores, an update_settings answer the title.
     */
    public function test_form_writer_modify_writes_fields_and_settings_through_the_stores() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $generator = new WPUF_Test_AI_Generator();
        $writer    = new Form_Writer( $generator );
        $forms     = wpuf()->platform()->get( FormStore::class );
        $fields    = wpuf()->platform()->get( FieldStore::class );
        $form_id   = $forms->create(
            [
                'post_title'     => 'To modify',
                'fields'         => [ $this->text_field( 'first_name' ) ],
                'unslash_fields' => false,
                'meta'           => [ 'wpuf_form_fields' => [ $this->text_field( 'first_name' ) ] ],
            ]
        );
        $first_id  = $fields->read( $form_id )[0]['id'];

        $generator->answer = [
            'success'           => true,
            'action'            => 'modify',
            'modification_type' => 'add_field',
            'changes'           => [ 'field' => $this->text_field( 'email_text' ) ],
            'message'           => 'Added',
        ];
        $result = $writer->modify( $form_id, [ 'prompt' => 'add an email text field', 'current_form' => [], 'session_id' => 'abc' ] );

        $this->assertIsArray( $result );
        $this->assertSame( [ 'add an email text field' ], $generator->prompts );
        $stored = $fields->read( $form_id );
        $this->assertSame( [ 'first_name', 'email_text' ], wp_list_pluck( $stored, 'name' ) );
        $this->assertSame( $first_id, $stored[0]['id'], 'the first field post is updated in place' );
        $this->assertCount( 2, get_post_meta( $form_id, 'wpuf_form_fields', true ) );

        $generator->answer = [
            'success'           => true,
            'action'            => 'modify',
            'modification_type' => 'update_settings',
            'target'            => 'form_title',
            'changes'           => [ 'form_title' => 'Renamed by AI', 'submit_text' => 'Send' ],
        ];
        $result = $writer->modify( $form_id, [ 'prompt' => 'rename it' ] );

        $this->assertIsArray( $result );
        $this->assertSame( 'Renamed by AI', get_post( $form_id )->post_title );
        $this->assertSame( 'Send', $forms->read_settings( $form_id )['submit_text'] );

        $this->assertWPError( $writer->modify( $form_id + 1000, [ 'prompt' => 'x' ] ) );
    }

    /**
     * Provider settings save and read through the settings store; an unchanged save is not a failure.
     */
    public function test_provider_settings_save_and_read_through_the_settings_store() {
        $settings = wpuf()->platform()->get( Provider_Settings::class );
        $input    = [ 'provider' => 'openai', 'model' => 'gpt-4o-mini', 'api_key' => 'sk-1234567890abc', 'temperature' => 0.5, 'max_tokens' => 1500 ];

        $this->assertFalse( $settings->save( [ 'provider' => 'openai', 'api_key' => 'short' ] )['success'] );

        $result = $settings->save( $input );
        $this->assertTrue( $result['success'], $result['message'] );
        $this->assertSame( 'gpt-4o-mini', get_option( 'wpuf_ai' )['ai_model'] );
        $this->assertSame( 'sk-1234567890abc', get_option( 'wpuf_ai' )['ai_api_key'] );
        $this->assertSame( 1500, get_option( 'wpuf_ai' )['max_tokens'] );

        $this->assertTrue( $settings->save( $input )['success'], 'saving the same values again succeeds' );
        $this->assertTrue( $settings->save( [ 'provider' => 'google' ] )['success'] );
        $this->assertSame( 'google', get_option( 'wpuf_ai' )['ai_provider'] );
        $this->assertSame( 'sk-1234567890abc', get_option( 'wpuf_ai' )['ai_api_key'], 'an empty key keeps the stored one' );

        $read = $settings->read();
        $this->assertTrue( $read['settings']['has_api_key'] );
        $this->assertArrayNotHasKey( 'api_key', $read['settings'] );
        $this->assertFalse( $settings->refresh_google_models()['success'], 'no Google key stored' );
    }

    /**
     * Field options: bounds checked before the provider, options sanitized to label/value pairs.
     */
    public function test_field_options_checks_bounds_and_sanitizes() {
        $service = wpuf()->platform()->get( Field_Options::class );

        $result = $service->generate( 'countries', [ 'field_type' => 'dropdown_field', 'max_options' => 0 ] );
        $this->assertWPError( $result );
        $this->assertSame( 'invalid_max_options', $result->get_error_code() );

        $this->assertSame(
            [
                [ 'label' => 'Red', 'value' => 'red' ],
                [ 'label' => 'Dark blue', 'value' => 'dark_blue' ],
            ],
            $service->sanitize( [ [ 'label' => '<b>Red</b>', 'value' => 'RED' ], 'Dark blue', 5 ] )
        );
    }

    /**
     * Generation without a stored key answers without a provider request; the attempt is logged otherwise.
     */
    public function test_generation_needs_a_stored_key() {
        $generation = wpuf()->platform()->get( Generation::class );

        $result = $generation->generate( [ 'prompt' => 'A contact form', 'provider' => 'openai' ] );
        $this->assertWPError( $result );
        $this->assertSame( 'generation_failed', $result->get_error_code() );

        $result = $generation->generate( [ 'prompt' => 'x', 'session_id' => 'bad session!' ] );
        $this->assertSame( 'invalid_session', $result->get_error_code() );
    }

    /**
     * FormStore::create() stores extra meta and rolls the form back when a field cannot be stored.
     */
    public function test_form_store_create_stores_meta_and_rolls_back_a_failed_field() {
        $forms   = wpuf()->platform()->get( FormStore::class );
        $form_id = $forms->create( [ 'post_title' => 'Meta', 'fields' => [ $this->text_field( 'a' ) ], 'meta' => [ 'wpuf_test_meta' => 'kept' ] ] );

        $this->assertIsInt( $form_id );
        $this->assertSame( 'kept', $forms->read_meta( $form_id, 'wpuf_test_meta' ) );
        $this->assertSame( WPUF_VERSION, get_post_meta( $form_id, 'wpuf_form_version', true ) );

        $fail = function ( $maybe_empty, $postarr ) {
            return 'wpuf_input' === $postarr['post_type'] ? true : $maybe_empty;
        };
        add_filter( 'wp_insert_post_empty_content', $fail, 10, 2 );
        $before = $this->form_count();
        $result = $forms->create( [ 'post_title' => 'Half', 'fields' => [ $this->text_field( 'a' ) ] ] );
        remove_filter( 'wp_insert_post_empty_content', $fail, 10 );

        $this->assertWPError( $result );
        $this->assertSame( 'wpuf_form_field_not_created', $result->get_error_code() );
        $this->assertSame( $before, $this->form_count(), 'no half-created form' );
    }

    /**
     * FormStore::update() / settings / meta accessors and FieldStore::replace() by position.
     */
    public function test_form_store_update_settings_meta_and_field_store_replace() {
        $forms   = wpuf()->platform()->get( FormStore::class );
        $fields  = wpuf()->platform()->get( FieldStore::class );
        $form_id = $forms->create(
            [
                'post_title'     => 'Replace',
                'fields'         => [ $this->text_field( 'a' ), $this->text_field( 'b' ), $this->text_field( 'c' ) ],
                'unslash_fields' => false,
            ]
        );
        $ids     = wp_list_pluck( $fields->read( $form_id ), 'id' );

        $this->assertSame( $form_id, $forms->update( $form_id, [ 'post_title' => 'Replaced', 'ID' => 999999 ] ) );
        $this->assertSame( 'Replaced', get_post( $form_id )->post_title );

        $forms->write_settings( $form_id, [ 'post_type' => 'page' ] );
        $this->assertSame( [ 'post_type' => 'page' ], $forms->read_settings( $form_id ) );
        $this->assertSame( [], $forms->read_settings( $form_id + 1000 ) );

        $fields->replace( $form_id, [ $this->text_field( 'a2' ), $this->text_field( 'b2' ) ] );
        $after = $fields->read( $form_id );
        $this->assertSame( [ 'a2', 'b2' ], wp_list_pluck( $after, 'name' ) );
        $this->assertSame( [ $ids[0], $ids[1] ], wp_list_pluck( $after, 'id' ), 'the first two posts are updated in place' );
        $this->assertNull( get_post( $ids[2] ), 'the surplus post is deleted' );

        $fields->replace( $form_id, [ $this->text_field( 'a3' ), $this->text_field( 'b3' ), $this->text_field( 'c3' ) ] );
        $this->assertSame( [ 'a3', 'b3', 'c3' ], wp_list_pluck( $fields->read( $form_id ), 'name' ) );
    }
}
