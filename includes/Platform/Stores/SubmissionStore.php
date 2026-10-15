<?php
/**
 * Submission store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Contracts\DataStore;

/**
 * The plugin's own meta on posts submitted through its forms: the form
 * (`_wpuf_form_id`), the order and payment state of a paid post
 * (`_wpuf_order_id`, `_wpuf_payment_status`) and the quota flag
 * (`wpuf_post_status`: new_draft / published).
 *
 * @since WPUF_SINCE
 */
class SubmissionStore implements DataStore {

    /**
     * Meta keys.
     */
    const FORM           = '_wpuf_form_id';
    const ORDER          = '_wpuf_order_id';
    const PAYMENT_STATUS = '_wpuf_payment_status';
    const QUOTA_FLAG     = 'wpuf_post_status';

    /**
     * Meta key: the admin "lock frontend editing" flag of a post.
     *
     * @since WPUF_SINCE
     */
    const LOCK = '_wpuf_lock_editing_post';

    /**
     * Meta key: when the lock was set (frontend edit screen).
     *
     * @since WPUF_SINCE
     */
    const LOCK_TIME = '_wpuf_lock_user_editing_post_time';

    /**
     * Whether a post was submitted through a form.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Post id
     *
     * @return bool
     */
    public function exists( $id ) {
        return '' !== get_post_meta( $id, self::FORM, true );
    }

    /**
     * The submission facts of a post.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Post id
     *
     * @return array|null { post, form_id, order_id, payment_status, quota_flag }, null for a missing post
     */
    public function read( $id ) {
        $post = get_post( $id );

        if ( ! $post ) {
            return null;
        }

        return [
            'post'           => $post,
            'form_id'        => (int) get_post_meta( $post->ID, self::FORM, true ),
            'order_id'       => $this->order_id( $post->ID ),
            'payment_status' => $this->payment_status( $post->ID ),
            'quota_flag'     => $this->quota_flag( $post->ID ),
        ];
    }

    /**
     * The submission facts of a post (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Post id
     *
     * @return array|null
     */
    public function find( $id ) {
        return $this->read( $id );
    }

    /**
     * Submissions by order id (`order_id`) or form (`form_id`), as posts.
     *
     * @since WPUF_SINCE
     *
     * @param array $args { @type string $order_id @type int $form_id @type string|array $status Post status (default any) }
     *
     * @return \WP_Post[]
     */
    public function query( array $args = [] ) {
        $query = [
            'post_type'      => 'any',
            'post_status'    => isset( $args['status'] ) ? $args['status'] : 'any',
            'posts_per_page' => isset( $args['per_page'] ) ? (int) $args['per_page'] : -1,
            'no_found_rows'  => true,
            'meta_query'     => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- the plugin's own meta.
        ];

        if ( ! empty( $args['order_id'] ) ) {
            $query['meta_query'][] = [
				'key' => self::ORDER,
				'value' => (string) $args['order_id'],
			];
        }

        if ( ! empty( $args['form_id'] ) ) {
            $query['meta_query'][] = [
				'key' => self::FORM,
				'value' => (int) $args['form_id'],
			];
        }

        return get_posts( $query );
    }

    /**
     * Submissions matching the args.
     *
     * @since WPUF_SINCE
     *
     * @param array $args As query()
     *
     * @return int
     */
    public function count( array $args = [] ) {
        return count( $this->query( $args ) );
    }

    /**
     * The unpublished post of an order (id and status), as the payment
     * handlers look it up.
     *
     * @since WPUF_SINCE
     *
     * @param string $order_id Order id
     *
     * @return object|null `{ ID, post_status }`
     */
    public function find_by_order( $order_id ) {
        global $wpdb;

        $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT p.ID, p.post_status FROM {$wpdb->posts} p, {$wpdb->postmeta} m WHERE p.ID = m.post_id AND p.post_status <> 'publish' AND m.meta_key = %s AND m.meta_value = %s",
                self::ORDER,
                $order_id
            )
        );

        return is_object( $row ) ? $row : null;
    }

    /**
     * The form id of a submission.
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return int
     */
    public function form_id( $post_id ) {
        return (int) get_post_meta( $post_id, self::FORM, true );
    }

    /**
     * The order id of a paid submission ('' when none).
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return string
     */
    public function order_id( $post_id ) {
        return (string) get_post_meta( $post_id, self::ORDER, true );
    }

    /**
     * Give a submission its order id, once: a stored order id is kept (the
     * handlers call this with a fresh id on every pass).
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id  Post id
     * @param string $order_id Order id
     *
     * @return void
     */
    public function set_order_once( $post_id, $order_id ) {
        if ( '' === $this->order_id( $post_id ) ) {
            update_post_meta( $post_id, self::ORDER, $order_id );
        }
    }

    /**
     * The payment status of a submission ('' when none).
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return string
     */
    public function payment_status( $post_id ) {
        return (string) get_post_meta( $post_id, self::PAYMENT_STATUS, true );
    }

    /**
     * Store the payment status of a submission (pending, completed, ...).
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id Post id
     * @param string $status  Status
     *
     * @return void
     */
    public function set_payment_status( $post_id, $status ) {
        update_post_meta( $post_id, self::PAYMENT_STATUS, $status );
    }

    /**
     * The quota flag of a submission ('', new_draft or published).
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return string
     */
    public function quota_flag( $post_id ) {
        return (string) get_post_meta( $post_id, self::QUOTA_FLAG, true );
    }

    /**
     * Store the quota flag of a submission.
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id Post id
     * @param string $flag    new_draft or published
     *
     * @return void
     */
    public function set_quota_flag( $post_id, $flag ) {
        update_post_meta( $post_id, self::QUOTA_FLAG, $flag );
    }

    /**
     * Change the status of a submitted post.
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id Post id
     * @param string $status  Post status
     *
     * @return int|\WP_Error
     */
    public function set_post_status( $post_id, $status ) {
        return wp_update_post(
            [
				'ID' => $post_id,
				'post_status' => $status,
			], true
        );
    }

    /**
     * Set the form a post was submitted with (the admin "form selection" metabox).
     *
     * @since WPUF_SINCE
     *
     * @param int        $post_id Post id
     * @param int|string $form_id Form id, or '' to clear the selection
     *
     * @return void
     */
    public function set_form_id( $post_id, $form_id ) {
        update_post_meta( $post_id, self::FORM, $form_id );
    }

    /**
     * The "lock frontend editing" flag of a post ('yes' / 'no' / '').
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return string
     */
    public function lock( $post_id ) {
        return (string) get_post_meta( $post_id, self::LOCK, true );
    }

    /**
     * When the lock was set, as stored (empty when never locked from the frontend).
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return string
     */
    public function lock_time( $post_id ) {
        return (string) get_post_meta( $post_id, self::LOCK_TIME, true );
    }

    /**
     * Set the "lock frontend editing" flag of a post.
     *
     * @since WPUF_SINCE
     *
     * @param int    $post_id Post id
     * @param string $value   'yes', 'no' or ''
     *
     * @return void
     */
    public function set_lock( $post_id, $value ) {
        update_post_meta( $post_id, self::LOCK, $value );
    }

    /**
     * Detach posts from their author (privacy eraser): post_author becomes 0.
     *
     * @since WPUF_SINCE
     *
     * @param int[] $post_ids Post ids
     *
     * @return int Rows changed
     */
    public function detach_author( array $post_ids ) {
        global $wpdb;

        $post_ids = array_values( array_filter( array_map( 'absint', $post_ids ) ) );

        if ( empty( $post_ids ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders only
        return (int) $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author = 0 WHERE ID IN ({$placeholders})", $post_ids ) );
    }

    /**
     * Set when the lock was set ('' clears it).
     *
     * @since WPUF_SINCE
     *
     * @param int        $post_id Post id
     * @param int|string $value   Timestamp or ''
     *
     * @return void
     */
    public function set_lock_time( $post_id, $value ) {
        update_post_meta( $post_id, self::LOCK_TIME, $value );
    }

    /**
     * How many posts of a type a user has in each status the account's
     * posts table lists (publish, pending, draft, future, private; 0 when
     * none).
     *
     * @since WPUF_SINCE
     *
     * @param int    $user_id   User id
     * @param string $post_type Post type
     *
     * @return int[] status => count
     */
    public function status_counts( $user_id, $post_type ) {
        global $wpdb;

        $counts = array_fill_keys( [ 'publish', 'pending', 'draft', 'future', 'private' ], 0 );
        $rows   = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT post_status, COUNT(*) AS total FROM {$wpdb->posts} WHERE post_author = %d AND post_type = %s GROUP BY post_status",
                $user_id,
                $post_type
            )
        );

        foreach ( (array) $rows as $row ) {
            if ( isset( $counts[ $row->post_status ] ) ) {
                $counts[ $row->post_status ] = (int) $row->total;
            }
        }

        return $counts;
    }
}
