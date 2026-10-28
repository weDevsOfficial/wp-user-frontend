<?php
/**
 * Post queries for stores
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WP_Query;

/**
 * Turns `DataStore::query()` / `count()` args into read-only WP_Query args for
 * the post-based stores. Never writes (no meta defaults, unlike
 * `Admin\Subscription::get_subscriptions()`).
 *
 * @since WPUF_SINCE
 */
trait QueriesPosts {

    /**
     * Post ids matching the args.
     *
     * @param string|string[] $post_types Post type(s)
     * @param array           $args       `status`, `search`, `per_page`, `page`, `orderby`, `order`, `post_type`
     *
     * @return int[]
     */
    protected function query_ids( $post_types, array $args ) {
        return array_map( 'intval', get_posts( $this->post_query_args( $post_types, $args ) ) );
    }

    /**
     * Number of posts matching the args (paging ignored).
     *
     * @param string|string[] $post_types Post type(s)
     * @param array           $args       Query args
     *
     * @return int
     */
    protected function count_posts( $post_types, array $args ) {
        $query_args                   = $this->post_query_args( $post_types, $args );
        $query_args['posts_per_page'] = 1;
        $query_args['paged']          = 1;
        $query_args['no_found_rows']  = false;

        $query = new WP_Query( $query_args );

        return (int) $query->found_posts;
    }

    /**
     * WP_Query args.
     *
     * @param string|string[] $post_types Allowed post type(s)
     * @param array           $args       Query args
     *
     * @return array
     */
    private function post_query_args( $post_types, array $args ) {
        $allowed = (array) $post_types;
        $type    = isset( $args['post_type'] ) && in_array( $args['post_type'], $allowed, true ) ? $args['post_type'] : $allowed;
        $orderby = isset( $args['orderby'] ) && in_array( $args['orderby'], [ 'date', 'title', 'ID', 'modified', 'menu_order' ], true ) ? $args['orderby'] : 'date';
        $order   = isset( $args['order'] ) && 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

        $query_args = [
            'post_type'        => $type,
            'post_status'      => isset( $args['status'] ) ? $args['status'] : 'any',
            'posts_per_page'   => isset( $args['per_page'] ) ? (int) $args['per_page'] : -1,
            'paged'            => isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1,
            'orderby'          => $orderby,
            'order'            => $order,
            'fields'           => 'ids',
            'suppress_filters' => false,
            'no_found_rows'    => true,
        ];

        if ( ! empty( $args['search'] ) ) {
            $query_args['s'] = sanitize_text_field( (string) $args['search'] );
        }

        // Only these ids (export of picked forms).
        if ( ! empty( $args['include'] ) ) {
            $query_args['post__in'] = array_values( array_filter( array_map( 'absint', (array) $args['include'] ) ) );
        }

        return $query_args;
    }
}
