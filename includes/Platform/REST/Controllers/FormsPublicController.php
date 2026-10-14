<?php
/**
 * REST: the post form on the front end (schema, submit, draft, helpers)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Frontend\Forms\Form_Schema;
use WeDevs\Wpuf\Frontend\Forms\Submission_Service;
use WeDevs\Wpuf\Frontend\React_Renderer;
use WeDevs\Wpuf\Platform\Http\Admin_Ajax_Context;
use WeDevs\Wpuf\Platform\REST\Rate_Limit;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/forms/{id}/...` for the React post form. Public only where the
 * form itself is public (guest posting); every write carries the form nonce
 * the schema handed out, and the submit runs the
 * same service (and therefore the same validation, hooks and redirects) as
 * the admin-ajax action.
 *
 * @since WPUF_SINCE
 */
class FormsPublicController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'form';

    /**
     * Schema builder.
     *
     * @var Form_Schema
     */
    private $form_schema;

    /**
     * Submissions.
     *
     * @var Submission_Service
     */
    private $submissions;

    /**
     * Rate limiter (not applied yet: the per-IP limits come back with a later enhancement).
     *
     * @var Rate_Limit
     */
    private $limiter;

    /**
     * The React renderer (its build() keeps the classic asset listeners quiet).
     *
     * @var React_Renderer|null
     */
    private $renderer;

    /**
     * @since WPUF_SINCE
     *
     * @param Form_Schema         $schema      Schema builder
     * @param Submission_Service  $submissions Submissions
     * @param Rate_Limit          $limiter     Rate limiter
     * @param React_Renderer|null $renderer    React renderer
     */
    public function __construct( Form_Schema $schema, Submission_Service $submissions, Rate_Limit $limiter, $renderer = null ) {
        $this->form_schema = $schema;
        $this->submissions = $submissions;
        $this->limiter     = $limiter;
        $this->renderer    = $renderer instanceof React_Renderer ? $renderer : null;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $id = [
            'id' => [
                'type'     => 'integer',
                'required' => true,
                'minimum'  => 1,
            ],
        ];

        $this->route(
            '/forms/(?P<id>\d+)/schema', WP_REST_Server::READABLE, 'get_schema', [ $this, 'can_read' ], $id + [
				'post_id' => [
					'type'    => 'integer',
					'default' => 0,
				],
				'page_id' => [
					'type'    => 'integer',
					'default' => 0,
				],
			]
        );
        $this->route( '/forms/(?P<id>\d+)/submissions', WP_REST_Server::CREATABLE, 'create_submission', [ $this, 'can_submit' ], $id );
        $this->route( '/forms/(?P<id>\d+)/submissions/(?P<post_id>\d+)', WP_REST_Server::EDITABLE, 'update_submission', [ $this, 'can_edit' ], $id );
        $this->route( '/forms/(?P<id>\d+)/drafts', WP_REST_Server::CREATABLE, 'create_draft', [ $this, 'can_edit' ], $id );
        $this->route(
            '/forms/(?P<id>\d+)/terms', WP_REST_Server::READABLE, 'get_terms', [ $this, 'can_read' ], $id + [
				'taxonomy' => [
					'type'    => 'string',
					'default' => 'category',
				],
				'parent'   => [
					'type'    => 'integer',
					'default' => 0,
				],
				'search'   => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			]
        );
        $this->route( '/forms/(?P<id>\d+)/embed', WP_REST_Server::CREATABLE, 'get_embed', [ $this, 'can_embed' ], $id );
        $this->route(
            '/forms/(?P<id>\d+)/states', WP_REST_Server::READABLE, 'get_states', [ $this, 'can_read_states' ], $id + [
				'country' => [
					'type'     => 'string',
					'required' => true,
				],
			]
        );
    }

    /**
     * The states list of the address field: public data (the country / state
     * names). A post form follows `can_read`; a published profile form
     * (Pro registration / profile) is open to the visitor filling it.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_read_states( WP_REST_Request $request ) {
        $form_id = (int) $request['id'];

        if ( 'wpuf_profile' === get_post_type( $form_id ) && 'publish' === get_post_status( $form_id ) ) {
            return true;
        }

        return $this->can_read( $request );
    }

    /**
     * Reading the form: logged in, or a form that accepts guests.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_read( WP_REST_Request $request ) {
        $form_id = (int) $request['id'];

        if ( 'wpuf_forms' !== get_post_type( $form_id ) ) {
            return $this->error( 'not_found', __( 'Your selected form is no longer available.', 'wp-user-frontend' ), 404 );
        }

        if ( is_user_logged_in() || $this->guest_form( $form_id ) ) {
            return true;
        }

        return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
    }

    /**
     * Submitting: `can_read` (no rate limit, as the classic path; planned later). The form nonce
     * is checked by the service, the captcha by the legacy validator.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_submit( WP_REST_Request $request ) {
        $read = $this->can_read( $request );

        if ( true !== $read ) {
            return $read;
        }

        return true;
    }

    /**
     * Editing and drafts: a logged-in user.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_edit( WP_REST_Request $request ) {
        if ( 'wpuf_forms' !== get_post_type( (int) $request['id'] ) ) {
            return $this->error( 'not_found', __( 'Your selected form is no longer available.', 'wp-user-frontend' ), 404 );
        }

        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        return true;
    }

    /**
     * Embeds: the server fetches the URL (wp_oembed_get()), so only users who
     * may write posts, as core's own oEmbed proxy (`edit_posts`).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_embed( WP_REST_Request $request ) {
        $allowed = $this->can_edit( $request );

        if ( true !== $allowed ) {
            return $allowed;
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to embed URLs.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Whether a form accepts guests.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return bool
     */
    private function guest_form( $form_id ) {
        $settings = wpuf_get_form_settings( $form_id );

        return isset( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'];
    }

    /**
     * GET /forms/{id}/schema
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_schema( WP_REST_Request $request ) {
        $build = function () use ( $request ) {
            return $this->form_schema->build( (int) $request['id'], (int) $request['post_id'], [ 'page_id' => (int) $request['page_id'] ] );
        };

        // As on the page: the hooks the schema runs (`wpuf_before_form_render` ...)
        // must not enqueue the classic form bundle for a React form.
        $schema = $this->renderer ? $this->renderer->build( (int) $request['post_id'] ? 'edit_form' : 'post_form', $build ) : $build();

        return is_wp_error( $schema ) ? $schema : rest_ensure_response( $schema );
    }

    /**
     * POST /forms/{id}/submissions
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function create_submission( WP_REST_Request $request ) {
        $input = $this->input( $request );
        unset( $input['post_id'] );

        return $this->answer( Admin_Ajax_Context::run( fn() => $this->submissions->submit( $input ) ) );
    }

    /**
     * PUT /forms/{id}/submissions/{post_id}
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function update_submission( WP_REST_Request $request ) {
        $input            = $this->input( $request );
        $input['post_id'] = (int) $request['post_id'];

        return $this->answer( Admin_Ajax_Context::run( fn() => $this->submissions->submit( $input ) ) );
    }

    /**
     * POST /forms/{id}/drafts
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function create_draft( WP_REST_Request $request ) {
        $input = $this->input( $request );

        return $this->answer( Admin_Ajax_Context::run( fn() => $this->submissions->draft( $input ) ) );
    }

    /**
     * GET /forms/{id}/terms: the child terms of a taxonomy field, or with
     * `search` the terms whose name matches (the suggestions of the text /
     * ajax taxonomy input, as `wpuf_ajax_tag_search` gives them; 20 at most).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_terms( WP_REST_Request $request ) {
        $taxonomy = sanitize_key( $request['taxonomy'] );

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return $this->error( 'bad_taxonomy', __( 'Unknown taxonomy.', 'wp-user-frontend' ), 400 );
        }

        if ( ! is_taxonomy_viewable( $taxonomy ) && ! current_user_can( get_taxonomy( $taxonomy )->cap->assign_terms ) ) {
            return $this->error( 'bad_taxonomy', __( 'Unknown taxonomy.', 'wp-user-frontend' ), 400 );
        }

        $search = trim( (string) $request['search'] );
        $args   = [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ];

        if ( '' !== $search ) {
            $args['search'] = $search;
            $args['number'] = 20;
        } else {
            $args['parent'] = (int) $request['parent'];
        }

        $terms = get_terms( $args );

        if ( is_wp_error( $terms ) ) {
            return $terms;
        }

        $items = [];

        foreach ( $terms as $term ) {
            $children = get_term_children( $term->term_id, $taxonomy );

            $items[] = [
                'id'       => (int) $term->term_id,
                'name'     => (string) $term->name,
                'slug'     => (string) $term->slug,
                'parent'   => (int) $term->parent,
                'children' => is_array( $children ) ? count( $children ) : 0,
            ];
        }

        return rest_ensure_response( $items );
    }

    /**
     * POST /forms/{id}/embed: the oEmbed markup of a URL (the editor's "insert embed").
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_embed( WP_REST_Request $request ) {
        $url = esc_url_raw( (string) $request->get_param( 'url' ) );

        if ( '' === $url ) {
            return $this->error( 'bad_url', __( 'A URL is required.', 'wp-user-frontend' ), 400 );
        }

        $html = wp_oembed_get( $url );

        if ( ! $html ) {
            return $this->error( 'no_embed', __( 'No embed found for this URL.', 'wp-user-frontend' ), 404 );
        }

        return rest_ensure_response( [ 'html' => $html ] );
    }

    /**
     * GET /forms/{id}/states?country=: the states of a country (the address field).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function get_states( WP_REST_Request $request ) {
        // The same lookup as wpuf_ajax_get_states_field(), as data instead of a <select>.
        $country   = sanitize_text_field( (string) $request['country'] );
        $cs        = new \WeDevs\Wpuf\Data\Country_State();
        $countries = $cs->countries();
        $states    = isset( $countries[ $country ] ) ? (array) $cs->getStates( $countries[ $country ] ) : [];

        return rest_ensure_response(
            [
                'country' => $country,
                'states'  => $states,
            ]
        );
    }

    /**
     * The body parameters as submit input (route parameters removed).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return array
     */
    private function input( WP_REST_Request $request ) {
        $input = (array) $request->get_body_params();

        if ( ! $input ) {
            $input = (array) $request->get_json_params();
        }

        $input['form_id'] = (int) $request['id'];
        unset( $input['_wpnonce'] );

        return $input;
    }

    /**
     * A service result as a response.
     *
     * @since WPUF_SINCE
     *
     * @param array|WP_Error $result Result
     *
     * @return \WP_REST_Response|WP_Error
     */
    private function answer( $result ) {
        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }
}
