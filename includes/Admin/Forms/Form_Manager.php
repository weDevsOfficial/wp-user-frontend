<?php

namespace WeDevs\Wpuf\Admin\Forms;

use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;
use WP_Post;
use WP_Query;

/**
 * The Form Manager Class
 *
 * @since 2.8.7
 */
class Form_Manager {

    /**
     * Get all the forms
     *
     * @return array
     */
    public function all() {
        return $this->get_forms();
    }

    /**
     * Get forms
     *
     * @param array $args
     *
     * @return array
     */
    public function get_forms( $args = [] ) {
        $forms_array = [
            'forms' => [],
            'meta'  => [
                'total' => 0,
                'pages' => 0,
            ],
        ];
        $defaults    = [
            'post_type'      => 'wpuf_forms',
            'post_status'    => [ 'publish', 'draft', 'pending' ],
            'posts_per_page' => - 1,
        ];
        $args = wp_parse_args( $args, $defaults );
        $query = new WP_Query( $args );
        $forms = $query->get_posts();
        if ( $forms ) {
            foreach ( $forms as $form ) {
                $forms_array['forms'][] = new Form( $form );
            }
        }
        $forms_array['meta']['total'] = (int) $query->found_posts;
        $forms_array['meta']['pages'] = (int) $query->max_num_pages;
        wp_reset_postdata();

        return $forms_array;
    }

    /**
     * Get a single form
     *
     * @param int|WP_Post $form
     *
     * @return Form
     */
    public function get( $form ) {
        return new Form( $form );
    }

    /**
     * Create a form
     *
     * @param string $form_name
     * @param array  $fields
     *
     * @return int|WP_Error
     */
    public function create( $form_name, $fields = [] ) {
        // Forwards to the form store (task 2.4a): post, then fields (unslashed);
        // no settings or version meta, as before.
        return Stores::forms()->create(
            [
                'post_title' => $form_name,
                'post_type'  => 'wpuf_forms',
                'fields'     => $fields ? $fields : [],
                'version'    => false,
            ]
        );
    }

    /**
     * Delete a form with it's input fields
     *
     * @param int  $form_id
     * @param bool $force
     *
     * @return void
     */
    public function delete( $form_id, $force = true ) {
        Stores::forms()->delete( $form_id, $force );
    }

    /**
     * API to duplicate a form
     *
     * @param int $_form_id
     *
     * @return int New duplicated form id
     */
    public function duplicate( $_form_id ) {
        return Stores::forms()->duplicate( $_form_id );
    }
}
