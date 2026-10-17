<?php
/**
 * Transactions service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Transactions;

use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WP_Error;

/**
 * User Frontend > Transactions for the admin app (`#/transactions`,
 * Platform\REST\Controllers\TransactionsController).
 *
 * Two kinds of rows, as on the classic page: completed payments are rows of
 * the `wpuf_transaction` table, pending bank payments are `wpuf_order`
 * posts. Their IDs overlap, so every row carries its kind (`transaction` or
 * `order`) and the actions check it: accept / reject take orders, delete
 * takes transactions. Accept and reject fire the same hooks as the classic
 * page (`wpuf_gateway_bank_order_complete`, `wpuf_{$gateway}_bank_order_reject`,
 * `wpuf_payment_received` through Payment::insert_payment()).
 *
 * @since WPUF_SINCE
 */
class TransactionService {

    /**
     * Kind of a completed payment row.
     */
    const KIND_TRANSACTION = TransactionStore::KIND_TRANSACTION;

    /**
     * Kind of a pending bank payment (wpuf_order post).
     */
    const KIND_ORDER = TransactionStore::KIND_ORDER;

    /**
     * Status tabs.
     */
    const STATUSES = TransactionStore::STATUSES;

    /**
     * Sortable columns.
     */
    const ORDERBY = TransactionStore::ORDERBY;

    /**
     * Gateway label of an accepted bank payment (classic page).
     */
    const BANK_LABEL = TransactionStore::BANK_LABEL;

    /**
     * The rows.
     *
     * @var TransactionStore
     */
    private $store;

    /**
     * @since WPUF_SINCE
     *
     * @param TransactionStore|null $store The rows (the container passes it).
     */
    public function __construct( $store = null ) {
        $this->store = $store instanceof TransactionStore ? $store : new TransactionStore();
    }

    /**
     * Rows of one page, with the totals.
     *
     * @since WPUF_SINCE
     *
     * @param array $args status, search, gateway, from, to, orderby, order, page, per_page
     *
     * @return array items, total, counts
     */
    public function query( $args ) {
        $args = (array) $args;

        return [
            'items'  => $this->hydrate( $this->store->query( $args ) ),
            'total'  => $this->store->count( $args ),
            'counts' => $this->counts(),
        ];
    }

    /**
     * Full rows for the `kind` + `id` pairs of a page, in that order.
     *
     * @param object[] $rows kind, id
     *
     * @return array
     */
    private function hydrate( $rows ) {
        $tx_ids = [];

        foreach ( $rows as $row ) {
            if ( self::KIND_TRANSACTION === $row->kind ) {
                $tx_ids[] = (int) $row->id;
            }
        }

        $transactions = $tx_ids ? $this->store->read_many( $tx_ids ) : [];
        $items        = [];

        foreach ( $rows as $row ) {
            $id = (int) $row->id;

            if ( self::KIND_TRANSACTION === $row->kind ) {
                if ( isset( $transactions[ $id ] ) ) {
                    $items[] = $this->format_transaction( $transactions[ $id ] );
                }
            } else {
                $item = $this->format_order( $id );

                if ( $item ) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * A completed payment row for the app.
     *
     * @param object $row Table row
     *
     * @return array
     */
    private function format_transaction( $row ) {
        return $this->format(
            [
                'kind'             => self::KIND_TRANSACTION,
                'id'               => (int) $row->id,
                'status'           => (string) $row->status,
                'user_id'          => (int) $row->user_id,
                'subtotal'         => '' !== (string) $row->subtotal ? $row->subtotal : $row->cost,
                'discount'         => $row->discount,
                'coupon_id'        => (int) $row->coupon_id,
                'tax'              => $row->tax,
                'cost'             => $row->cost,
                'post_id'          => (int) $row->post_id,
                'pack_id'          => (int) $row->pack_id,
                'payer_first_name' => $row->payer_first_name,
                'payer_last_name'  => $row->payer_last_name,
                'payer_email'      => $row->payer_email,
                'payer_address'    => $this->address( $row->payer_address ),
                'gateway'          => $row->payment_type,
                'transaction_id'   => (string) $row->transaction_id,
                'created'          => $row->created,
            ]
        );
    }

    /**
     * A pending bank payment (wpuf_order post) for the app, read as the
     * classic page reads it (wpuf_get_pending_transactions()).
     *
     * @param int $order_id Order post ID
     *
     * @return array|null
     */
    private function format_order( $order_id ) {
        $info = $this->store->order_info( $order_id );

        if ( ! is_array( $info ) ) {
            return null;
        }

        $type     = isset( $info['type'] ) ? $info['type'] : '';
        $item     = isset( $info['item_number'] ) ? (int) $info['item_number'] : 0;
        $tax      = ! empty( $info['tax'] ) ? $info['tax'] : 0;
        $price    = isset( $info['price'] ) ? $info['price'] : 0;
        $subtotal = ! empty( $info['subtotal'] ) ? $info['subtotal'] : ( ! empty( $info['cost'] ) ? $info['cost'] : $price );
        $method   = isset( $info['post_data']['wpuf_payment_method'] ) ? $info['post_data']['wpuf_payment_method'] : '';

        return $this->format(
            [
                'kind'             => self::KIND_ORDER,
                'id'               => (int) $order_id,
                'status'           => 'pending',
                'user_id'          => isset( $info['user_info']['id'] ) ? (int) $info['user_info']['id'] : 0,
                'subtotal'         => $subtotal,
                'discount'         => ! empty( $info['discount'] ) ? $info['discount'] : 0,
                'coupon_id'        => ! empty( $info['post_data']['coupon_id'] ) ? absint( $info['post_data']['coupon_id'] ) : 0,
                'tax'              => $tax,
                'cost'             => ! empty( $info['cost'] ) ? floatval( $info['cost'] ) : ( $price ? floatval( $price ) + floatval( $tax ) : $subtotal ),
                'post_id'          => 'post' === $type ? $item : 0,
                'pack_id'          => 'pack' === $type ? $item : 0,
                'payer_first_name' => isset( $info['user_info']['first_name'] ) ? $info['user_info']['first_name'] : '',
                'payer_last_name'  => isset( $info['user_info']['last_name'] ) ? $info['user_info']['last_name'] : '',
                'payer_email'      => isset( $info['user_info']['email'] ) ? $info['user_info']['email'] : '',
                'payer_address'    => [],
                'gateway'          => function_exists( 'wpuf_get_payment_type_label' ) ? wpuf_get_payment_type_label( $method ) : $method,
                'transaction_id'   => '',
                'created'          => isset( $info['date'] ) ? $info['date'] : get_post_field( 'post_date', $order_id ),
            ]
        );
    }

    /**
     * Add what the app shows: formatted money, names, links.
     *
     * @param array $row Raw row
     *
     * @return array
     */
    private function format( $row ) {
        $user = $row['user_id'] ? get_userdata( $row['user_id'] ) : false;

        $row['key']      = $row['kind'] . ':' . $row['id'];
        $row['user']     = $user ? [
            'id'   => (int) $user->ID,
            'name' => $user->display_name,
            'url'  => current_user_can( 'edit_user', $user->ID ) ? admin_url( 'user-edit.php?user_id=' . $user->ID ) : '',
        ] : null;
        $row['payer']    = trim( $row['payer_first_name'] . ' ' . $row['payer_last_name'] );
        $row['coupon']   = $row['coupon_id'] ? [
            'id'   => $row['coupon_id'],
            'code' => get_the_title( $row['coupon_id'] ),
            // The coupons screen of the admin app (Pro), else the coupon's post screen.
            'url'  => current_user_can( 'edit_post', $row['coupon_id'] ) ? $this->coupon_url( (int) $row['coupon_id'] ) : '',
        ] : null;
        $row['post']     = $this->linked_post( $row['post_id'] );
        $row['pack']     = $this->linked_post( $row['pack_id'] );
        $row['amounts']  = [
            'subtotal' => $this->money( $row['subtotal'] ),
            'discount' => (float) $row['discount'] ? $this->money( $row['discount'] ) : '',
            'tax'      => $this->money( $row['tax'] ),
            'cost'     => $this->money( $row['cost'] ),
        ];
        $row['date']     = $row['created'] ? mysql2date( Stores::settings()->wp_option( 'date_format' ), $row['created'] ) : '';
        $row['gateway_logo'] = $this->gateway_logo( (string) $row['gateway'] );

        return $row;
    }

    /**
     * The plugin's mark for a gateway label (PayPal, bank, else a card). Also
     * used by the pack Subscribers list.
     *
     * @since WPUF_SINCE
     *
     * @param string $gateway Gateway label
     *
     * @return string Image URL
     */
    public function gateway_logo( $gateway ) {
        $gateway = strtolower( $gateway );
        $file    = 'credit-card.svg';

        if ( false !== strpos( $gateway, 'paypal' ) ) {
            $file = 'paypal-mark.svg';
        } elseif ( false !== strpos( $gateway, 'bank' ) || false !== strpos( $gateway, 'manual' ) ) {
            $file = 'bank.svg';
        }

        return WPUF_ASSET_URI . '/images/' . $file;
    }

    /**
     * Edit link of a coupon: the admin app's coupons route when Pro serves it.
     *
     * @param int $coupon_id Coupon id
     *
     * @return string
     */
    private function coupon_url( $coupon_id ) {
        if ( class_exists( 'WP_User_Frontend_Pro' ) && function_exists( 'wpuf_admin_app_url' ) && wpuf_admin_app_enabled() ) {
            return wpuf_admin_app_url( '/coupons', [ 'action' => 'edit', 'id' => $coupon_id ] );
        }

        return admin_url( 'post.php?post=' . $coupon_id . '&action=edit' );
    }

    /**
     * Title and edit link of a post or pack.
     *
     * @param int $post_id Post ID
     *
     * @return array|null
     */
    private function linked_post( $post_id ) {
        if ( ! $post_id ) {
            return null;
        }

        $title = get_the_title( $post_id );

        // Packs are edited on the subscriptions screen, not post.php.
        if ( 'wpuf_subscription' === get_post_type( $post_id ) ) {
            // The admin app route (no page reload); the old screen URL redirects there anyway.
            $url = current_user_can( wpuf_admin_role() )
                ? ( function_exists( 'wpuf_admin_app_url' ) && wpuf_admin_app_enabled() ? wpuf_admin_app_url( '/subscriptions', [ 'action' => 'edit', 'id' => (int) $post_id ] ) : \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::screen_url( $post_id ) )
                : '';
        } else {
            $url = current_user_can( 'edit_post', $post_id ) ? admin_url( 'post.php?post=' . $post_id . '&action=edit' ) : '';
        }

        return [
            'id'    => (int) $post_id,
            'title' => '' !== $title ? html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) : '#' . $post_id,
            'url'   => $url,
        ];
    }

    /**
     * A price as the site shows it, as plain text.
     *
     * @param mixed $amount Amount
     *
     * @return string
     */
    private function money( $amount ) {
        return html_entity_decode( wp_strip_all_tags( wpuf_format_price( (float) $amount ) ), ENT_QUOTES, 'UTF-8' );
    }

    /**
     * The payer address stored with a payment (serialized array; classes
     * are never restored).
     *
     * @param mixed $value Stored value
     *
     * @return array
     */
    private function address( $value ) {
        if ( ! is_string( $value ) || '' === $value || ! is_serialized( $value ) || PHP_VERSION_ID < 70000 ) {
            return [];
        }

        $address = @unserialize( trim( $value ), [ 'allowed_classes' => false ] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

        if ( ! is_array( $address ) ) {
            return [];
        }

        return array_filter( array_map( 'strval', array_filter( $address, 'is_scalar' ) ) );
    }

    /**
     * Totals of the cards and the status tabs.
     *
     * @since WPUF_SINCE
     *
     * @return array all, completed, pending, income, tax
     */
    public function counts() {
        $counts = $this->store->counts();

        $counts['income'] = $this->money( $counts['income'] );
        $counts['tax']    = $this->money( $counts['tax'] );

        return $counts;
    }

    /**
     * Gateway filter options: every gateway with a row, plus bank orders.
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    public function gateways() {
        $gateways = $this->store->gateways();

        if ( ! in_array( self::BANK_LABEL, $gateways, true ) ) {
            $gateways[] = self::BANK_LABEL;
        }

        return array_values( $gateways );
    }

    /**
     * Run an action on rows.
     *
     * @since WPUF_SINCE
     *
     * @param string  $action accept, reject or delete
     * @param array[] $rows   kind, id
     *
     * @return array done, skipped
     */
    public function run( $action, $rows ) {
        $done    = 0;
        $skipped = 0;

        foreach ( (array) $rows as $row ) {
            $kind = isset( $row['kind'] ) ? (string) $row['kind'] : '';
            $id   = isset( $row['id'] ) ? absint( $row['id'] ) : 0;

            $result = $id ? $this->run_one( $action, $kind, $id ) : false;

            if ( true === $result ) {
                ++$done;
            } else {
                ++$skipped;
            }
        }

        return [
            'done'    => $done,
            'skipped' => $skipped,
        ];
    }

    /**
     * One action on one row.
     *
     * @param string $action accept, reject or delete
     * @param string $kind   Row kind
     * @param int    $id     Row ID
     *
     * @return bool
     */
    private function run_one( $action, $kind, $id ) {
        if ( 'delete' === $action ) {
            return self::KIND_TRANSACTION === $kind && $this->delete( $id );
        }

        if ( self::KIND_ORDER !== $kind || 'wpuf_order' !== get_post_type( $id ) ) {
            return false;
        }

        return 'accept' === $action ? $this->accept( $id ) : ( 'reject' === $action ? $this->reject( $id ) : false );
    }

    /**
     * Delete a completed payment row.
     *
     * @param int $id Row ID
     *
     * @return bool
     */
    private function delete( $id ) {
        return $this->store->delete( $id );
    }

    /**
     * Reject a pending bank payment: as the classic page.
     *
     * @param int $order_id Order post ID
     *
     * @return bool
     */
    private function reject( $order_id ) {
        $info    = $this->store->order_info( $order_id );
        $gateway = isset( $info['post_data']['wpuf_payment_method'] ) ? sanitize_key( $info['post_data']['wpuf_payment_method'] ) : '';

        // Same hook as the classic page's reject.
        do_action( "wpuf_{$gateway}_bank_order_reject", $order_id );

        return (bool) wp_delete_post( $order_id, true );
    }

    /**
     * Accept a pending bank payment: as the classic single accept (payer
     * address and coupon usage included, which the classic bulk accept
     * skipped). The order ID is the new row's transaction ID, which also
     * keeps Payment::insert_payment() from updating another row.
     *
     * @param int $order_id Order post ID
     *
     * @return bool
     */
    private function accept( $order_id ) {
        $info = $this->store->order_info( $order_id );

        if ( ! is_array( $info ) || empty( $info['type'] ) ) {
            return false;
        }

        $item = isset( $info['item_number'] ) ? $info['item_number'] : 0;

        $transaction = [
            'user_id'          => isset( $info['user_info']['id'] ) ? $info['user_info']['id'] : 0,
            'status'           => 'completed',
            'subtotal'         => isset( $info['subtotal'] ) ? $info['subtotal'] : 0,
            'discount'         => ! empty( $info['discount'] ) ? $info['discount'] : 0,
            'coupon_id'        => ! empty( $info['post_data']['coupon_id'] ) ? absint( $info['post_data']['coupon_id'] ) : 0,
            'tax'              => isset( $info['tax'] ) ? $info['tax'] : 0,
            'cost'             => isset( $info['price'] ) ? $info['price'] : 0,
            'post_id'          => 'post' === $info['type'] ? $item : 0,
            'pack_id'          => 'pack' === $info['type'] ? $item : 0,
            'payer_first_name' => isset( $info['user_info']['first_name'] ) ? $info['user_info']['first_name'] : '',
            'payer_last_name'  => isset( $info['user_info']['last_name'] ) ? $info['user_info']['last_name'] : '',
            'payer_address'    => Stores::settings()->get( 'wpuf_address_options', 'show_address', false ) ? wpuf_get_user_address() : '',
            'payer_email'      => isset( $info['user_info']['email'] ) ? $info['user_info']['email'] : '',
            'payment_type'     => self::BANK_LABEL,
            'transaction_id'   => $order_id,
            'created'          => current_time( 'mysql' ),
        ];

        // Same hook as the classic page's accept.
        do_action( 'wpuf_gateway_bank_order_complete', $transaction, $order_id );

        Payment::insert_payment( $transaction, $order_id );

        if ( $transaction['coupon_id'] ) {
            $this->store->record_coupon_use( $transaction['coupon_id'] );
        }

        wp_delete_post( $order_id, true );

        return true;
    }

    /**
     * Rows per page saved for the user (the classic page's screen option).
     *
     * @since WPUF_SINCE
     *
     * @return int
     */
    public function per_page() {
        $per_page = (int) get_user_option( 'transactions_per_page' );

        return $per_page > 0 ? $per_page : 20;
    }

    /**
     * Save rows per page for the user.
     *
     * @since WPUF_SINCE
     *
     * @param int $per_page Rows per page
     *
     * @return void
     */
    public function save_per_page( $per_page ) {
        $per_page = max( 1, min( 100, absint( $per_page ) ) );

        if ( $per_page !== $this->per_page() ) {
            // Where WordPress keeps the classic page's screen option.
            Stores::users()->set_meta( get_current_user_id(), 'transactions_per_page', $per_page );
        }
    }
}
