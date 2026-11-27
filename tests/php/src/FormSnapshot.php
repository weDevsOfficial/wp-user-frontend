<?php
/**
 * Snapshot of everything a form stores, for byte comparisons in tests.
 *
 * @package WP_User_Frontend
 */

/**
 * Form snapshot helper.
 */
trait WPUF_Form_Snapshot {

    /**
     * Everything a form stores: post row, field rows (raw post_content bytes in
     * order) and form meta (raw values).
     */
    protected function snapshot( $form_id ) {
        global $wpdb;

        $post   = get_post( $form_id );
        $fields = $wpdb->get_results( $wpdb->prepare( "SELECT post_content, menu_order, post_status FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'wpuf_input' ORDER BY menu_order, ID", $form_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $meta   = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key NOT LIKE %s ORDER BY meta_key, meta_id", $form_id, '\_edit%' ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        // A field's own post id is stored in its content; it differs between two forms.
        foreach ( $fields as &$row ) {
            $row['post_content'] = preg_replace( '/s:2:"id";i:\d+;/', 's:2:"id";i:ID;', $row['post_content'] );
        }
        unset( $row );

        return [
            'post'   => $post ? [ $post->post_type, $post->post_status, $post->post_title, $post->comment_status ] : null,
            'fields' => $fields,
            'meta'   => $meta,
        ];
    }
}
