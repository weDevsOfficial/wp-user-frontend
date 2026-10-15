<?php
/**
 * Post form submissions as a service: create, update and draft without dying
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Forms;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Platform\Http\Submission_Context;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WP_Error;

/**
 * The REST controller and the admin-ajax action share this service, so every
 * validation, every `wpuf_add_post_*` / `wpuf_edit_post_*` hook and every
 * redirect rule runs once, in one place, for both transports.
 *
 * The legacy submit handler, the field classes and the submit hooks read the
 * posted values from `$_POST`: every call runs through Submission_Context,
 * which builds that request from the service input and restores it after.
 *
 * @since WPUF_SINCE
 */
class Submission_Service {

    use FieldableTrait;

    /**
     * Settings of the form being handled (the trait reads them).
     *
     * @var array
     */
    public $form_settings = [];

    /**
     * Fields of the form being handled (the trait reads them).
     *
     * @var array
     */
    private $form_fields = [];

    /**
     * The legacy submit handler.
     *
     * @var Post_Form_Handler
     */
    private $ajax;

    /**
     * @since WPUF_SINCE
     *
     * @param Post_Form_Handler $ajax The legacy submit handler
     */
    public function __construct( Post_Form_Handler $ajax ) {
        $this->ajax = $ajax;
    }

    /**
     * Create or update a post from a form submission.
     *
     * Input keys are the form's field names plus `form_id`, `page_id`,
     * `post_id` (update), `wpuf_form_status`, `delete_attachments`, and the
     * `wpuf_form_add` nonce as `_wpnonce` (a form post) or `wpuf_nonce` (REST).
     *
     * @since WPUF_SINCE
     *
     * @param array $input   Submission input, unslashed
     * @param bool  $slashed True when $input already carries PHP's slashes ($_POST itself)
     *
     * @return array|WP_Error The AJAX success payload (`redirect_to`, `show_message`, `message`, ...) or the error
     */
    public function submit( array $input, $slashed = false ) {
        // Listeners of the submit hooks check the action the classic form posted.
        $input['action'] = 'wpuf_submit_post';

        return Submission_Context::run(
            $input,
            function () {
                return $this->ajax->submit_post();
            },
            [ 'slashed' => $slashed ]
        );
    }

    /**
     * Save a draft (the "Save Draft" button) for a logged-in user.
     *
     * @since WPUF_SINCE
     *
     * @param array $input   Submission input, unslashed
     * @param bool  $slashed True when $input already carries PHP's slashes
     *
     * @return array|WP_Error `post_id`, `url`, `message`, ... or the error
     */
    public function draft( array $input, $slashed = false ) {
        $input['action'] = 'wpuf_draft_post';
        $values          = $slashed ? wp_unslash( $input ) : $input;

        return Submission_Context::run(
            $input,
            function () use ( $values ) {
                return $this->save_draft( $values );
            },
            [ 'slashed' => $slashed ]
        );
    }

    /**
     * The draft handler (the body of the old `Frontend_Form::draft_post()`,
     * returning instead of answering). Its own values come from $input; the
     * field values go through the field classes (get_input_fields()), which
     * read the request Submission_Context built.
     *
     * @since WPUF_SINCE
     *
     * @param array $input Submission input, unslashed
     *
     * @return array|WP_Error
     */
    private function save_draft( array $input ) {
        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );

        $form_id             = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
        $form                = new Form( $form_id );
        $this->form_settings = $form->get_settings();
        $this->form_fields   = $form->get_fields();
        $current_user_id     = get_current_user_id();

        if ( ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) ) {
            return new WP_Error( 'wpuf_draft_invalid_form', __( 'Invalid form.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( $current_user_id <= 0 ) {
            return new WP_Error( 'wpuf_draft_login', __( 'You must be logged in to save drafts.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! isset( $input['post_id'] ) ) {
            [ $user_can_post, $submission_info ] = $form->is_submission_open( $form, $this->form_settings );
            $user_can_post                       = apply_filters( 'wpuf_can_post', $user_can_post, $form_id, $this->form_settings );
            $submission_info                     = apply_filters( 'wpuf_addpost_notice', $submission_info, $form_id, $this->form_settings );

            if ( ! wpuf_is_option_on( $user_can_post ) ) {
                return new WP_Error(
                    'wpuf_draft_closed',
                    ! empty( $submission_info ) ? $submission_info : __( 'You are not allowed to submit to this form.', 'wp-user-frontend' ),
                    [ 'status' => 403 ]
                );
            }
        }

        [ $post_vars, $taxonomy_vars, $meta_vars ] = $this->get_input_fields( $this->form_fields );

        $allowed_tags = wp_kses_allowed_html( 'post' );
        $post_content = isset( $input['post_content'] ) ? wp_kses( $input['post_content'], $allowed_tags ) : '';

        $postarr = [
            'post_type'    => $this->form_settings['post_type'],
            'post_status'  => wpuf_get_draft_post_status( $this->form_settings ),
            'post_author'  => $current_user_id,
            'post_title'   => isset( $input['post_title'] ) ? sanitize_text_field( $input['post_title'] ) : '',
            'post_content' => $post_content,
            'post_excerpt' => isset( $input['post_excerpt'] ) ? wp_kses( $input['post_excerpt'], $allowed_tags ) : '',
        ];

        if ( ! empty( $this->form_fields ) ) {
            foreach ( $this->form_fields as $field ) {
                if ( 'taxonomy' !== $field['template'] ) {
                    continue;
                }

                $category_name = $field['name'];

                if ( isset( $input[ $category_name ] ) && is_array( $input[ $category_name ] ) ) {
                    $category = array_map( 'sanitize_text_field', $input[ $category_name ] );
                } else {
                    $category = isset( $input[ $category_name ] ) ? sanitize_text_field( $input[ $category_name ] ) : '';
                }

                if ( '' !== $category && '0' !== $category && '-1' !== $category[0] ) {
                    if ( ! is_array( $category ) && is_string( $category ) ) {
                        $cat_ids = [];

                        foreach ( explode( ',', $category ) as $each_cat_string ) {
                            $cat_ids[] = get_cat_ID( trim( $each_cat_string ) );
                        }

                        $postarr['post_category'] = $cat_ids;
                    } else {
                        $postarr['post_category'] = $category;
                    }
                }
            }
        }

        if ( ! isset( $postarr['post_category'] ) && isset( $this->form_settings['default_cat'] ) && is_object_in_taxonomy( $this->form_settings['post_type'], 'category' ) ) {
            $postarr['post_category'] = is_array( $this->form_settings['default_cat'] ) ? $this->form_settings['default_cat'] : [ $this->form_settings['default_cat'] ];
        }

        if ( isset( $input['tags'] ) ) {
            $postarr['tags_input'] = explode( ',', sanitize_text_field( $input['tags'] ) );
        }

        if ( isset( $input['post_id'] ) ) {
            $update_post_id = intval( $input['post_id'] );
            $can_edit       = wpuf_user_can_edit_post( $update_post_id );

            if ( is_wp_error( $can_edit ) ) {
                $can_edit->add_data( [ 'status' => 403 ] );

                return $can_edit;
            }

            $existing_post             = get_post( $update_post_id );
            $postarr['ID']             = $update_post_id;
            $postarr['post_author']    = (int) $existing_post->post_author;
            $postarr['comment_status'] = 'open';
        }

        $postarr = $this->adjust_thumbnail_id( $postarr );
        $post_id = wp_insert_post( $postarr );

        wpuf_frontend_post_revision( $post_id, $this->form_settings );

        if ( $post_id ) {
            self::update_post_meta( $meta_vars, $post_id );
            Stores::submissions()->set_form_id( $post_id, $form_id );
            Stores::submissions()->set_draft_pending( $post_id );

            if ( isset( $this->form_settings['post_format'] ) && '0' !== $this->form_settings['post_format'] && post_type_supports( $this->form_settings['post_type'], 'post-formats' ) ) {
                set_post_format( $post_id, $this->form_settings['post_format'] );
            }

            if ( ! empty( $taxonomy_vars ) ) {
                $this->set_custom_taxonomy( $post_id, $taxonomy_vars );
            } else {
                $this->set_default_taxonomy( $post_id );
            }
        }

        do_action( 'wpuf_draft_post_after_insert', $post_id, $form_id, $this->form_settings, $this->form_fields );

        $action = isset( $input['action'] ) ? sanitize_text_field( $input['action'] ) : '';

        return [
            'post_id'        => $post_id,
            'action'         => $action,
            'date'           => current_time( 'mysql' ),
            'post_author'    => $current_user_id,
            'comment_status' => Stores::settings()->wp_option( 'default_comment_status' ),
            'url'            => add_query_arg( 'preview', 'true', get_permalink( $post_id ) ),
            'message'        => __( 'Post Saved', 'wp-user-frontend' ),
        ];
    }
}
