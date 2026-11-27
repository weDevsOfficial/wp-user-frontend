<?php
/**
 * Form schema provider contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

/**
 * Builds the schema a React form renders from: fields, values, settings,
 * the submit target. Resolved from the container by this interface.
 *
 * @since WPUF_SINCE
 */
interface Form_Schema_Provider {

    /**
     * The schema of a form (create, or edit when a post id is given).
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param int   $post_id Post being edited, 0 for a new one
     * @param array $atts    Shortcode attributes / mode (post, registration, profile)
     *
     * @return array|\WP_Error
     */
    public function build( $form_id, $post_id = 0, array $atts = [] );
}
