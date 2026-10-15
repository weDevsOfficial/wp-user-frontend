<?php
/**
 * Forms REST controller (admin)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Stores\Stores;

use WeDevs\Wpuf\Builder\BuilderBoot;
use WeDevs\Wpuf\Builder\FormSave;
use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\Normalizers;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/forms/{id}` for post and registration forms:
 *
 * - GET reads a form for the builder, POST saves it (Builder\FormSave; body
 *   `{ success, data: { form_fields, form_settings } }`), DELETE deletes it
 *   permanently with its fields;
 * - POST `/duplicate`, `/trash`, `/restore` do what the forms list row actions do
 *   (same store calls), for React screens that work without a page reload.
 *
 * Every route needs the manage-forms capability and an id that is a builder form.
 *
 * @since WPUF_SINCE
 */
class FormsController extends RestController {

    /**
     * Resource name used in error codes.
     *
     * @var string
     */
    protected $resource = 'form';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'admin/forms';

    /**
     * Form store.
     *
     * @var FormStore
     */
    private $forms;

    /**
     * Builder save.
     *
     * @var FormSave
     */
    private $saver;

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     *
     * @param FormStore $forms Form store
     * @param FormSave  $saver Builder save
     */
    public function __construct( FormStore $forms, FormSave $saver ) {
        $this->forms = $forms;
        $this->saver = $saver;
    }

    /**
     * Register the routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        $string = [
            'type'     => 'string',
            'required' => false,
        ];

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'type'              => 'integer',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'update_item_permissions_check' ],
                    'args'                => [
                        'form_data'            => [
                            'type'     => 'string',
                            'required' => true,
                        ],
                        'form_fields'          => $string,
                        'notifications'        => $string,
                        'settings'             => $string,
                        'integrations'         => $string,
                        'legacy_settings'      => $string,
                        'legacy_settings_keys' => $string,
                        'touched'              => $string,
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
            ]
        );

        // A new form for the builder's "new form" route (the builder page's
        // `action=add-new` did the same before opening the form).
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => $this->permission( Caps::MANAGE_FORMS ),
                    'args'                => [
                        'type'     => [
                            'type'     => 'string',
                            'required' => true,
                            'enum'     => array_keys( BuilderBoot::SCREENS ),
                        ],
                        'template' => [
                            'type'              => 'string',
                            'required'          => false,
                            'sanitize_callback' => 'sanitize_key',
                        ],
                    ],
                ],
            ]
        );

        // The builder's data for one form (the React admin app opens builders
        // without a page load). Same permission as reading the form.
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)/builder',
            [
                'args' => [
                    'id' => [
                        'type'              => 'integer',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_builder' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                ],
            ]
        );

        foreach ( [ 'duplicate', 'trash', 'restore' ] as $action ) {
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/' . $action,
                [
                    'args' => [
                        'id' => [
                            'type'              => 'integer',
                            'required'          => true,
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => [ $this, $action . '_item' ],
                        // Trash and restore need the delete cap, like WordPress's own trash.
                        'permission_callback' => [ $this, 'duplicate' === $action ? 'update_item_permissions_check' : 'delete_item_permissions_check' ],
                    ],
                ]
            );
        }
    }

    /**
     * The builder's data for a form: `wpuf_form_builder`, `wpuf_single_objects`
     * and `wpuf_mixins`, built as on the builder screen (Builder\BuilderBoot).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_builder( $request ) {
        $data = ( new BuilderBoot() )->boot( (int) $request['id'] );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        return rest_ensure_response(
            [
                'success' => true,
                'data'    => $data,
            ]
        );
    }

    /**
     * Create a sample form of a type, as the builder page's `action=add-new`
     * did (registration forms need Pro).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_item( $request ) {
        $type = (string) $request['type'];

        if ( 'wpuf_profile' === $type && ! wpuf_is_pro_active() ) {
            return $this->error( 'invalid_type', __( 'Registration forms need WP User Frontend Pro.', 'wp-user-frontend' ), 400 );
        }

        $template = (string) $request['template'];

        if ( '' !== $template ) {
            $form_id = $this->create_from_template( $template, $type );

            if ( is_wp_error( $form_id ) ) {
                return $form_id;
            }
        } else {
            $title   = 'wpuf_profile' === $type ? 'Sample Registration Form' : 'Sample Form';
            $form_id = wpuf_create_sample_form( $title, $type, true );

            if ( ! $form_id ) {
                return $this->error( 'not_created', __( 'The form could not be created.', 'wp-user-frontend' ), 500 );
            }

            $this->store_shown_settings( $form_id, $type );
        }

        $response = rest_ensure_response(
            [
                'success' => true,
                'data'    => [ 'id' => (int) $form_id ],
            ]
        );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * A form from a template picker card, stored as the card's link did
     * (`admin_action_post_form_template`, Pro's `wpuf_profile_form_template`).
     * Post forms use the free template registry; other form types are created
     * through the `wpuf_admin_form_from_template` filter (Pro hooks it for
     * registration templates).
     *
     * @param string $template Template key
     * @param string $type     Form post type
     *
     * @return int|\WP_Error Form id
     */
    private function create_from_template( $template, $type ) {
        $form_id = null;

        if ( 'wpuf_forms' === $type ) {
            // The admin container only exists on wp-admin requests, not on REST ones.
            $templates = wpuf()->admin ? wpuf()->admin->form_template : null;
            $templates = $templates ? $templates : new \WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Templates();
            $object    = $templates->get_template_object( $template );

            // A card the picker shows as not installed (its integration is off) is refused too.
            if ( $object && method_exists( $object, 'is_enabled' ) && ! $object->is_enabled() ) {
                return $this->error( 'invalid_template', __( 'This template is not available.', 'wp-user-frontend' ), 400 );
            }

            $form_id = false === $object ? null : $templates->create_from_template( $template );
        }

        /**
         * Create a form of this type from a template picker card.
         *
         * @since WPUF_SINCE
         *
         * @param int|false|\WP_Error|null $form_id  Form id; null when nothing created it yet
         * @param string                   $template Template key
         * @param string                   $type     Form post type
         */
        $form_id = apply_filters( 'wpuf_admin_form_from_template', $form_id, $template, $type );

        if ( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        if ( null === $form_id ) {
            return $this->error( 'invalid_template', __( 'This template is not available.', 'wp-user-frontend' ), 400 );
        }

        if ( ! $form_id || get_post_type( (int) $form_id ) !== $type ) {
            return $this->error( 'not_created', __( 'The form could not be created.', 'wp-user-frontend' ), 500 );
        }

        return (int) $form_id;
    }

    /**
     * A new form stores the values its builder shows while nothing is stored
     * (the selects' first options, Pro's post expiration), as develop's first
     * builder save did. Later saves store them only when the builder is edited
     * (FormSave), so an untouched save changes nothing stored.
     *
     * @param int    $form_id Form id
     * @param string $type    Form post type
     *
     * @return void
     */
    private function store_shown_settings( $form_id, $type ) {
        $settings = Stores::forms()->read_settings( $form_id );

        if ( 'wpuf_profile' === $type ) {
            $settings = Normalizers::registration_form_selects( $settings );

            if ( wpuf_is_pro_active() ) {
                $settings = Normalizers::registration_user_status( $settings );
            }
        } else {
            $settings = Normalizers::post_form_selects( $settings );

            if ( wpuf_is_pro_active() ) {
                $settings = Normalizers::post_expiration( $settings );
            }
        }

        Stores::forms()->write_settings( $form_id, $settings );
    }

    /**
     * Read permission: manage forms, and edit this form.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|\WP_Error
     */
    public function get_item_permissions_check( $request ) {
        return $this->form_permission( $request );
    }

    /**
     * Save permission: manage forms, and edit this form.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|\WP_Error
     */
    public function update_item_permissions_check( $request ) {
        return $this->form_permission( $request );
    }

    /**
     * Delete permission (also trash and restore): manage forms, and delete this form.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|\WP_Error
     */
    public function delete_item_permissions_check( $request ) {
        return $this->form_permission( $request, 'delete_post' );
    }

    /**
     * A builder form for the builder.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_item( $request ) {
        $form = $this->forms->find( (int) $request['id'] );

        if ( null === $form ) {
            return $this->error( 'not_found', __( 'Invalid form id', 'wp-user-frontend' ), 404 );
        }

        return rest_ensure_response(
            [
                'success' => true,
                'data'    => [
                    'id'            => $form->get_id(),
                    'post_type'     => $form->get_type(),
                    'post_title'    => $form->get_title(),
                    'post_status'   => $form->get_status(),
                    'form_fields'   => $form->get_fields(),
                    'form_settings' => $form->get_settings(),
                    'notifications' => $form->get_notifications(),
                    'integrations'  => $form->get_integrations(),
                ],
            ]
        );
    }

    /**
     * Save a form from the builder payload.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_item( $request ) {
        $form_data = [];
        parse_str( (string) $request['form_data'], $form_data );

        // The form id in the builder's own fields must be the route's form.
        if ( empty( $form_data['wpuf_form_id'] ) || absint( $form_data['wpuf_form_id'] ) !== (int) $request['id'] ) {
            return $this->error( 'invalid_id', __( 'Invalid form id', 'wp-user-frontend' ), 400 );
        }

        $post_data = [];

        foreach ( [ 'form_fields', 'notifications', 'settings', 'integrations', 'legacy_settings', 'legacy_settings_keys', 'touched' ] as $key ) {
            if ( null !== $request[ $key ] ) {
                $post_data[ $key ] = (string) $request[ $key ];
            }
        }

        $result = $this->saver->save( $post_data, $form_data );

        if ( is_wp_error( $result ) ) {
            $result->add_data( [ 'status' => 400 ] );

            return $result;
        }

        return rest_ensure_response(
            [
                'success' => true,
                'data'    => $result,
            ]
        );
    }

    /**
     * Delete a form permanently with its fields (the list's Delete Permanently).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item( $request ) {
        $form_id = (int) $request['id'];

        if ( ! $this->forms->delete_permanently( $form_id ) ) {
            return $this->error( 'not_deleted', __( 'The form could not be deleted.', 'wp-user-frontend' ), 500 );
        }

        return rest_ensure_response(
            [
                'success' => true,
                'data'    => [
                    'id'      => $form_id,
                    'deleted' => true,
                ],
            ]
        );
    }

    /**
     * Duplicate a form as a draft (the list's Duplicate).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function duplicate_item( $request ) {
        $new_id = $this->forms->duplicate( (int) $request['id'] );

        if ( ! $new_id ) {
            return $this->error( 'not_duplicated', __( 'The form could not be duplicated.', 'wp-user-frontend' ), 500 );
        }

        $response = rest_ensure_response(
            [
                'success' => true,
                'data'    => [ 'id' => (int) $new_id ],
            ]
        );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Move a form to the trash (the list's Trash).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function trash_item( $request ) {
        $form_id = (int) $request['id'];

        if ( ! $this->forms->trash( $form_id ) ) {
            return $this->error( 'not_trashed', __( 'The form could not be moved to the trash.', 'wp-user-frontend' ), 500 );
        }

        return $this->status_response( $form_id );
    }

    /**
     * Restore a form from the trash (the list's Restore).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function restore_item( $request ) {
        $form_id = (int) $request['id'];

        if ( 'trash' !== get_post_status( $form_id ) ) {
            return $this->error( 'not_in_trash', __( 'The form is not in the trash.', 'wp-user-frontend' ), 400 );
        }

        if ( ! $this->forms->restore( $form_id ) ) {
            return $this->error( 'not_restored', __( 'The form could not be restored.', 'wp-user-frontend' ), 500 );
        }

        return $this->status_response( $form_id );
    }

    /**
     * `{ success, data: { id, status } }` for a form.
     *
     * @param int $form_id Form id
     *
     * @return \WP_REST_Response
     */
    private function status_response( $form_id ) {
        return rest_ensure_response(
            [
                'success' => true,
                'data'    => [
                    'id'     => $form_id,
                    'status' => get_post_status( $form_id ),
                ],
            ]
        );
    }

    /**
     * Logged in (401), may manage forms (403), the id is a builder form (404)
     * and the user may edit (or delete) it (403).
     *
     * @param WP_REST_Request $request  Request
     * @param string          $meta_cap `edit_post` or `delete_post`
     *
     * @return true|\WP_Error
     */
    private function form_permission( $request, $meta_cap = 'edit_post' ) {
        $allowed = call_user_func( $this->permission( Caps::MANAGE_FORMS ) );

        if ( true !== $allowed ) {
            return $allowed;
        }

        $form_id = (int) $request['id'];

        if ( ! $this->forms->is_form( $form_id ) ) {
            return $this->error( 'not_found', __( 'Invalid form id', 'wp-user-frontend' ), 404 );
        }

        // Both form post types map edit_post / delete_post to wpuf_admin_role() (the
        // cap checked above). They are registered with the admin layer, which a REST
        // request without Pro does not load: check the object cap only when registered.
        if ( post_type_exists( get_post_type( $form_id ) ) && ! current_user_can( $meta_cap, $form_id ) ) {
            $message = 'delete_post' === $meta_cap
                ? __( 'Sorry, you are not allowed to delete this form.', 'wp-user-frontend' )
                : __( 'Sorry, you are not allowed to edit this form.', 'wp-user-frontend' );

            return $this->error( 'forbidden', $message, 403 );
        }

        return true;
    }
}
