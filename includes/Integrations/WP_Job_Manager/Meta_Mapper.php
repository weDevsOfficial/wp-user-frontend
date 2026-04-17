<?php
// DESCRIPTION: Maps WPUF-submitted form data to WP Job Manager `job_listing`
// meta keys, applying WPJM's own sanitizer callbacks for safe persistence.

namespace WeDevs\Wpuf\Integrations\WP_Job_Manager;

use WP_Job_Manager_Post_Types;

/**
 * Meta mapper for WP Job Manager integration
 *
 * @since WPUF_SINCE
 */
class Meta_Mapper {

    /**
     * WPJM job listing custom post type slug.
     */
    const POST_TYPE = 'job_listing';

    /**
     * Register submission hooks.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        add_action( 'wpuf_add_post_after_insert', [ $this, 'handle_after_insert' ], 10, 4 );
        add_action( 'wpuf_edit_post_after_update', [ $this, 'handle_after_update' ], 10, 4 );
    }

    /**
     * Handle new-post submission.
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id       Newly created post ID.
     * @param int   $form_id       WPUF form ID.
     * @param array $form_settings WPUF form settings.
     * @param array $meta_vars     Raw meta fields from the submission.
     *
     * @return void
     */
    public function handle_after_insert( $post_id, $form_id, $form_settings, $meta_vars = [] ) {
        if ( ! $this->is_job_listing_form( $form_settings ) ) {
            return;
        }

        $this->apply_approval_override( $post_id, $form_settings );

        // Signal to WPJM that this is a frontend submission — WPJM's own
        // shutdown handler will fire `job_manager_job_submitted` on the next
        // publish transition. See ensure_job_submission_action_triggered().
        update_post_meta( $post_id, '_public_submission', 1 );

        $this->persist_meta( $post_id );
    }

    /**
     * Handle post-edit submission.
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id       Updated post ID.
     * @param int   $form_id       WPUF form ID.
     * @param array $form_settings WPUF form settings.
     * @param array $meta_vars     Raw meta fields from the submission.
     *
     * @return void
     */
    public function handle_after_update( $post_id, $form_id, $form_settings, $meta_vars = [] ) {
        if ( ! $this->is_job_listing_form( $form_settings ) ) {
            return;
        }

        $this->persist_meta( $post_id );
    }

    /**
     * Is this submission targeting `job_listing`?
     *
     * @since WPUF_SINCE
     *
     * @param array $form_settings WPUF form settings.
     *
     * @return bool
     */
    private function is_job_listing_form( $form_settings ) {
        return isset( $form_settings['post_type'] )
            && self::POST_TYPE === $form_settings['post_type'];
    }

    /**
     * Override post_status to `pending` when WPJM requires approval.
     *
     * WPUF has already inserted the post by this hook, so we flip the status
     * before WPJM's own `transition_post_status` listener fires.
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id       Post ID.
     * @param array $form_settings WPUF form settings.
     *
     * @return void
     */
    private function apply_approval_override( $post_id, $form_settings ) {
        $requires_approval = (bool) get_option( 'job_manager_submission_requires_approval' );

        /**
         * Filter the approval decision for a WPUF-submitted job listing.
         *
         * @since WPUF_SINCE
         *
         * @param bool  $requires_approval Whether the submission should land as pending.
         * @param int   $post_id           Post ID.
         * @param array $form_settings     WPUF form settings.
         */
        $requires_approval = (bool) apply_filters(
            'wpuf_wpjm_should_approve',
            $requires_approval,
            $post_id,
            $form_settings
        );

        if ( ! $requires_approval ) {
            return;
        }

        $post = get_post( $post_id );

        if ( ! $post || 'pending' === $post->post_status ) {
            return;
        }

        wp_update_post(
            [
                'ID'          => $post_id,
                'post_status' => 'pending',
            ]
        );
    }

    /**
     * Persist posted meta to WPJM-known keys with WPJM's sanitizers applied.
     *
     * We read from `$_POST` directly because WPUF's `$meta_vars` arrives
     * already split into typed arrays; the submitted field name is the
     * canonical WPJM meta key in our templates, so pulling from `$_POST`
     * keeps the mapping one-to-one and lets us skip fields the form omits.
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post ID.
     *
     * @return void
     */
    private function persist_meta( $post_id ) {
        $mapping = $this->get_meta_mapping();

        foreach ( $mapping as $field_name => $meta_key ) {
            if ( ! isset( $_POST[ $field_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                continue;
            }

            $raw_value = wp_unslash( $_POST[ $field_name ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            $sanitized = $this->sanitize_for_meta( $raw_value, $meta_key );

            update_post_meta( $post_id, $meta_key, $sanitized );
        }
    }

    /**
     * Default WPUF-field → WPJM-meta mapping.
     *
     * Both taxonomies (`job_listing_category`, `job_listing_type`) are handled
     * by WPUF's taxonomy field directly and are intentionally absent here.
     *
     * @since WPUF_SINCE
     *
     * @return array<string,string>
     */
    private function get_meta_mapping() {
        $mapping = [
            '_job_location'    => '_job_location',
            '_remote_position' => '_remote_position',
            '_application'     => '_application',
            '_company_name'    => '_company_name',
            '_company_website' => '_company_website',
            '_company_tagline' => '_company_tagline',
            '_company_twitter' => '_company_twitter',
            '_company_video'   => '_company_video',
        ];

        /**
         * Filter the WPUF-field → WPJM-meta mapping.
         *
         * @since WPUF_SINCE
         *
         * @param array<string,string> $mapping Default mapping.
         */
        return apply_filters( 'wpuf_wpjm_meta_mapping', $mapping );
    }

    /**
     * Sanitize a value using WPJM's own callbacks where applicable.
     *
     * @since WPUF_SINCE
     *
     * @param mixed  $value    Raw value.
     * @param string $meta_key WPJM meta key.
     *
     * @return mixed
     */
    private function sanitize_for_meta( $value, $meta_key ) {
        if ( ! class_exists( WP_Job_Manager_Post_Types::class ) ) {
            return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
        }

        switch ( $meta_key ) {
            case '_company_website':
            case '_company_video':
                return WP_Job_Manager_Post_Types::sanitize_meta_field_url( $value );

            case '_application':
                return WP_Job_Manager_Post_Types::sanitize_meta_field_application( $value );

            case '_remote_position':
                return (int) (bool) $value;

            default:
                return WP_Job_Manager_Post_Types::sanitize_meta_field_based_on_input_type( $value, $meta_key );
        }
    }
}
