<?php
/**
 * AI form builder REST routes
 *
 * @package WP_User_Frontend
 * @since 4.2.1
 */

namespace WeDevs\Wpuf\AI;

use WeDevs\Wpuf\AI\Services\Field_Options;
use WeDevs\Wpuf\AI\Services\Form_Writer;
use WeDevs\Wpuf\AI\Services\Generation;
use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Contracts\Hookable;
use WeDevs\Wpuf\Platform\REST\RestController as PlatformRestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The `wpuf/v1/ai-form-builder/*` routes and the builder's
 * `wpuf_ai_generate_field_options` AJAX action: argument schemas,
 * permissions and request reading. The work is done by the services
 * (`Generation`, `Provider_Settings`, `Field_Options`, `Form_Writer`); the
 * answers keep the shapes and statuses the React AI builder and the settings
 * screen read.
 *
 * Permissions: generation, previews, lists and form writing need
 * `can_use_ai()` (logged in, `edit_posts` or `wpuf_create_forms`, admins
 * always, otherwise a role from `wpuf_ai_allowed_roles`); reading and saving
 * the AI settings and refreshing the model lists need `Caps::MANAGE_SITE`.
 *
 * @since 4.2.1
 * @since WPUF_SINCE On the platform REST base; services injected.
 */
class RestController extends PlatformRestController implements Hookable {

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'ai-form-builder';

    /**
     * Resource name in error codes.
     *
     * @var string
     */
    protected $resource = 'ai_form_builder';

    /**
     * Provider ids a request may name.
     */
    const PROVIDERS = [ 'openai', 'anthropic', 'google' ];

    /**
     * Form types a request may name.
     */
    const FORM_TYPES = [ 'post', 'profile', 'registration' ];

    /**
     * Integration ids a request may name ('' for none).
     */
    const INTEGRATIONS = [ '', 'woocommerce', 'edd', 'events_calendar', 'dokan', 'wc_vendors', 'wcfm' ];

    /**
     * Largest accepted generate request body.
     */
    const MAX_REQUEST_BYTES = 1048576;

    /**
     * Generation service.
     *
     * @var Generation
     */
    private $generation;

    /**
     * Provider settings service.
     *
     * @var Provider_Settings
     */
    private $settings;

    /**
     * Field options service.
     *
     * @var Field_Options
     */
    private $field_options;

    /**
     * Form writer service.
     *
     * @var Form_Writer
     */
    private $writer;

    /**
     * Constructor.
     *
     * @since 4.2.1
     * @since WPUF_SINCE Takes the services (container); builds defaults otherwise.
     *
     * @param Generation|null        $generation    Generation service
     * @param Provider_Settings|null $settings      Provider settings service
     * @param Field_Options|null     $field_options Field options service
     * @param Form_Writer|null       $writer        Form writer service
     */
    public function __construct( $generation = null, $settings = null, $field_options = null, $writer = null ) {
        $generator = null;

        foreach ( [ $generation, $settings, $field_options, $writer ] as $service ) {
            if ( null === $service ) {
                $generator = $generator ? $generator : $this->default_generator();
            }
        }

        $this->generation    = $generation instanceof Generation ? $generation : new Generation( $generator );
        $this->settings      = $settings instanceof Provider_Settings ? $settings : new Provider_Settings( $generator );
        $this->field_options = $field_options instanceof Field_Options ? $field_options : new Field_Options( $generator );
        $this->writer        = $writer instanceof Form_Writer ? $writer : new Form_Writer( $generator );
    }

    /**
     * The builder's AJAX action for field options.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        add_action( 'wp_ajax_wpuf_ai_generate_field_options', [ $this, 'ajax_generate_field_options' ] );
    }

    /**
     * Register the routes.
     *
     * @since 4.2.1
     *
     * @return void
     */
    public function register_routes() {
        $base   = '/' . $this->rest_base;
        $use    = [ $this, 'can_use_ai' ];
        $site   = $this->permission( Caps::MANAGE_SITE );
        $prompt = [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'validate_callback' => [ $this, 'validate_prompt' ],
        ];
        $form_type = [
            'required'          => false,
            'type'              => 'string',
            'default'           => 'post',
            'enum'              => self::FORM_TYPES,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $api_key = [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ];

        $this->route(
            $base . '/generate',
            WP_REST_Server::CREATABLE,
            'generate_form',
            $use,
            [
                'prompt'               => $prompt,
                'session_id'           => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'conversation_context' => [
                    'required' => false,
                    'type'     => 'object',
                    'default'  => [],
                ],
                'form_type'            => $form_type,
                'integration'          => [
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => '',
                    'enum'              => self::INTEGRATIONS,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'provider'             => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => 'openai',
                    'enum'     => self::PROVIDERS,
                ],
                'temperature'          => [
                    'required' => false,
                    'type'     => 'number',
                    'minimum'  => 0,
                    'maximum'  => 1,
                    'default'  => 0.7,
                ],
                'max_tokens'           => [
                    'required' => false,
                    'type'     => 'integer',
                    'minimum'  => 100,
                    'maximum'  => 4000,
                    'default'  => 2000,
                ],
            ]
        );

        $this->route(
            $base . '/test',
            WP_REST_Server::CREATABLE,
            'test_connection',
            $use,
            [
                'api_key'  => $api_key,
                'provider' => [
                    'required' => false,
                    'type'     => 'string',
                    'enum'     => self::PROVIDERS,
                ],
                'model'    => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ]
        );

        $this->route( $base . '/providers', WP_REST_Server::READABLE, 'get_providers', $use );
        $this->route( $base . '/integrations', WP_REST_Server::READABLE, 'get_integrations', $use, [ 'form_type' => $form_type ] );

        $this->route(
            $base . '/settings',
            WP_REST_Server::CREATABLE,
            'save_settings',
            $site,
            [
                'provider'    => [
                    'required' => true,
                    'type'     => 'string',
                    'enum'     => self::PROVIDERS,
                ],
                'model'       => [
                    'required' => false,
                    'type'     => 'string',
                ],
                'api_key'     => $api_key,
                'temperature' => [
                    'required' => false,
                    'type'     => 'number',
                    'minimum'  => 0,
                    'maximum'  => 1,
                ],
                'max_tokens'  => [
                    'required' => false,
                    'type'     => 'integer',
                    'minimum'  => 100,
                    'maximum'  => 4000,
                ],
            ]
        );

        $this->route(
            $base . '/create-form',
            WP_REST_Server::CREATABLE,
            'create_form_from_ai',
            $use,
            [
                'form_data' => [
                    'required'   => true,
                    'type'       => 'object',
                    'properties' => [
                        'form_title'       => [ 'type' => 'string' ],
                        'form_description' => [ 'type' => 'string' ],
                        'wpuf_fields'      => [ 'type' => 'array' ],
                        'form_settings'    => [ 'type' => 'object' ],
                    ],
                ],
                'form_type' => [
                    'required' => false,
                    'type'     => 'string',
                    'default'  => 'post',
                    'enum'     => self::FORM_TYPES,
                ],
            ]
        );

        $this->route(
            $base . '/modify-form',
            WP_REST_Server::CREATABLE,
            'modify_form_from_ai',
            [ $this, 'can_modify_form' ],
            [
                'form_id'           => [
                    'required' => true,
                    'type'     => 'integer',
                ],
                'modification_data' => [
                    'required'   => true,
                    'type'       => 'object',
                    'properties' => [
                        'action'            => [ 'type' => 'string' ],
                        'modification_type' => [ 'type' => 'string' ],
                        'target'            => [ 'type' => 'string' ],
                        'changes'           => [ 'type' => 'object' ],
                    ],
                ],
            ]
        );

        $this->route( $base . '/settings', WP_REST_Server::READABLE, 'get_settings', $site );
        $this->route( $base . '/refresh-google-models', WP_REST_Server::CREATABLE, 'refresh_google_models', $site );
        $this->route( $base . '/models', WP_REST_Server::READABLE, 'get_models', $use );

        $this->route(
            $base . '/generate-options',
            WP_REST_Server::CREATABLE,
            'generate_field_options',
            $use,
            [
                'prompt'        => $prompt,
                'field_type'    => [
                    'required'          => true,
                    'type'              => 'string',
                    'enum'              => [ 'dropdown_field', 'radio_field', 'checkbox_field', 'multiple_select' ],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'output_format' => [
                    'required'          => false,
                    'type'              => 'string',
                    'enum'              => [ 'one_per_line', 'value_label' ],
                    'default'           => 'one_per_line',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'tone'          => [
                    'required'          => false,
                    'type'              => 'string',
                    'enum'              => [ 'casual', 'formal', 'professional', 'friendly' ],
                    'default'           => 'casual',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'max_options'   => [
                    'required' => false,
                    'type'     => 'integer',
                    'minimum'  => 1,
                    'maximum'  => 100,
                    'default'  => 20,
                ],
            ]
        );
    }

    /**
     * Who may use the AI form builder: logged out 401, otherwise
     * check_permission() or 403.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_use_ai() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! $this->check_permission() ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Who may let the AI change a form: an AI builder user who may edit that
     * form (the form post types map edit_post to wpuf_admin_role()), and only
     * a WPUF form: 404 otherwise. Editors may use the AI builder for new forms
     * but no longer rewrite an existing form they cannot edit.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_modify_form( $request ) {
        $allowed = $this->can_use_ai();

        if ( true !== $allowed ) {
            return $allowed;
        }

        $form_id = absint( $request['form_id'] );

        if ( ! $form_id || ! in_array( get_post_type( $form_id ), [ 'wpuf_forms', 'wpuf_profile' ], true ) ) {
            return new WP_Error( 'wpuf_rest_not_found', __( 'Invalid form id', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        // The form post types are registered with the admin layer, which a REST
        // request may not load: then check the cap their edit_post maps to.
        $can_edit = post_type_exists( get_post_type( $form_id ) ) ? current_user_can( 'edit_post', $form_id ) : current_user_can( wpuf_admin_role() );

        if ( ! $can_edit ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to edit this form.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Whether the current user may use the AI form builder.
     *
     * @since 4.2.1
     *
     * @return bool
     */
    public function check_permission() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'wpuf_create_forms' ) ) {
            return false;
        }

        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        /**
         * Roles that may use the AI form builder.
         *
         * @since 4.2.1
         *
         * @param string[] $roles Default administrator and editor.
         */
        $allowed_roles = apply_filters( 'wpuf_ai_allowed_roles', [ 'administrator', 'editor' ] );
        $user          = wp_get_current_user();

        return ! empty( array_intersect( (array) $allowed_roles, (array) $user->roles ) );
    }

    /**
     * Validate a prompt argument: not empty, at most 1000 characters.
     *
     * @since 4.2.1
     *
     * @param string          $value   Prompt
     * @param WP_REST_Request $request Request
     * @param string          $param   Parameter name
     *
     * @return true|WP_Error
     */
    public function validate_prompt( $value, $request, $param ) {
        if ( empty( trim( (string) $value ) ) ) {
            return new WP_Error( 'invalid_prompt', __( 'Prompt cannot be empty', 'wp-user-frontend' ) );
        }

        if ( strlen( $value ) > 1000 ) {
            return new WP_Error( 'prompt_too_long', __( 'Prompt is too long. Maximum 1000 characters allowed.', 'wp-user-frontend' ) );
        }

        return true;
    }

    /**
     * POST generate: a form from a prompt.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function generate_form( WP_REST_Request $request ) {
        $content_length = (int) $request->get_header( 'content-length' );

        if ( $content_length > self::MAX_REQUEST_BYTES ) {
            return new WP_Error( 'request_too_large', __( 'Request payload too large', 'wp-user-frontend' ), [ 'status' => 413 ] );
        }

        $result = $this->generation->generate(
            [
                'prompt'               => $request->get_param( 'prompt' ),
                'session_id'           => $request->get_param( 'session_id' ),
                'conversation_context' => $request->get_param( 'conversation_context' ),
                'form_type'            => $request->get_param( 'form_type' ),
                'integration'          => $request->get_param( 'integration' ),
                'provider'             => $request->get_param( 'provider' ),
                'temperature'          => $request->get_param( 'temperature' ),
                'max_tokens'           => $request->get_param( 'max_tokens' ),
                'language'             => $request->get_param( 'language' ),
            ]
        );

        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 200 );
    }

    /**
     * POST test: the connection to a provider.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function test_connection( WP_REST_Request $request ) {
        $result = $this->settings->test_connection(
            $request->get_param( 'api_key' ),
            $request->get_param( 'provider' ),
            $request->get_param( 'model' )
        );

        $status = ! empty( $result['success'] ) ? 200 : ( ! empty( $result['exception'] ) ? 500 : 400 );
        unset( $result['exception'] );

        return new WP_REST_Response( $result, $status );
    }

    /**
     * GET providers.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function get_providers( WP_REST_Request $request ) {
        return new WP_REST_Response( $this->settings->providers(), 200 );
    }

    /**
     * GET integrations: those available for a form type.
     *
     * @since 4.2.9
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function get_integrations( WP_REST_Request $request ) {
        return new WP_REST_Response( $this->settings->integrations( $request->get_param( 'form_type' ) ), 200 );
    }

    /**
     * POST settings: save the AI settings.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function save_settings( WP_REST_Request $request ) {
        $result = $this->settings->save(
            [
                'provider'    => $request->get_param( 'provider' ),
                'model'       => $request->get_param( 'model' ),
                'api_key'     => $request->get_param( 'api_key' ),
                'temperature' => $request->get_param( 'temperature' ),
                'max_tokens'  => $request->get_param( 'max_tokens' ),
            ]
        );

        return new WP_REST_Response( $result, ! empty( $result['success'] ) ? 200 : 400 );
    }

    /**
     * GET settings.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function get_settings( WP_REST_Request $request ) {
        return new WP_REST_Response( $this->settings->read(), 200 );
    }

    /**
     * POST refresh-google-models.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function refresh_google_models( WP_REST_Request $request ) {
        $result = $this->settings->refresh_google_models();

        return new WP_REST_Response( $result, ! empty( $result['success'] ) ? 200 : 400 );
    }

    /**
     * GET models.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response
     */
    public function get_models( WP_REST_Request $request ) {
        return new WP_REST_Response( $this->settings->models(), 200 );
    }

    /**
     * POST generate-options: options of a choice field.
     *
     * @since 4.2.2
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function generate_field_options( WP_REST_Request $request ) {
        $options = $this->field_options->generate(
            $request->get_param( 'prompt' ),
            [
                'field_type'    => $request->get_param( 'field_type' ),
                'output_format' => $request->get_param( 'output_format' ),
                'tone'          => $request->get_param( 'tone' ),
                'max_options'   => $request->get_param( 'max_options' ),
            ]
        );

        if ( is_wp_error( $options ) ) {
            return $options;
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'options' => $options,
                'message' => __( 'Options generated successfully', 'wp-user-frontend' ),
            ],
            200
        );
    }

    /**
     * POST create-form: the form from the generated data.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_form_from_ai( WP_REST_Request $request ) {
        $form_data = $request->get_param( 'form_data' );
        $result    = $this->writer->create( is_array( $form_data ) ? $form_data : [], $request->get_param( 'form_type' ) );

        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 201 );
    }

    /**
     * POST modify-form: an AI modification of a form.
     *
     * @since 4.2.1
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function modify_form_from_ai( WP_REST_Request $request ) {
        $data   = $request->get_param( 'modification_data' );
        $result = $this->writer->modify( $request->get_param( 'form_id' ), is_array( $data ) ? $data : [] );

        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 200 );
    }

    /**
     * AJAX `wpuf_ai_generate_field_options` (the builder's field settings).
     *
     * @since 4.2.2
     *
     * @return void
     */
    public function ajax_generate_field_options() {
        check_ajax_referer( 'form-builder-setting-nonce', 'nonce' );

        if ( ! $this->check_permission() ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-user-frontend' ) ] );
        }

        $prompt     = sanitize_textarea_field( wp_unslash( isset( $_POST['prompt'] ) ? $_POST['prompt'] : '' ) );
        $field_type = sanitize_text_field( wp_unslash( isset( $_POST['field_type'] ) ? $_POST['field_type'] : 'dropdown_field' ) );

        if ( empty( $prompt ) ) {
            wp_send_json_error( [ 'message' => __( 'Prompt is required', 'wp-user-frontend' ) ] );
        }

        $options = $this->field_options->generate( $prompt, [ 'field_type' => $field_type ] );

        if ( is_wp_error( $options ) ) {
            wp_send_json_error( [ 'message' => $options->get_error_message() ] );
        }

        wp_send_json_success( [ 'options' => $options ] );
    }

    /**
     * The provider client when no service was given: the shared one, or a new one.
     *
     * @since WPUF_SINCE
     *
     * @return FormGenerator
     */
    private function default_generator() {
        $platform = function_exists( 'wpuf' ) ? wpuf()->platform() : null;

        if ( $platform && $platform->has( FormGenerator::class ) ) {
            return $platform->get( FormGenerator::class );
        }

        return new FormGenerator();
    }
}
