<?php
/**
 * Payments: gateways, transactions, currencies and prices
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Get all the payment gateways
 *
 * @return array
 */
function wpuf_get_gateways( $context = 'admin' ) {
    $gateways = Payment::get_payment_gateways();
    $return   = [];

    foreach ( $gateways as $id => $gate ) {
        if ( 'admin' === $context ) {
            $return[ $id ] = $gate['admin_label'];
        } elseif ( 'gateway_selector' === $context ) {
            $return[ $id ] = [
                'admin_label'           => $gate['admin_label'],
                'icon'                  => isset( $gate['icon'] ) ? $gate['icon'] : '',
                'supports_subscription' => ! empty( $gate['supports_subscription'] ),
                'is_pro_preview'        => ! empty( $gate['is_pro_preview'] ) ? $gate['is_pro_preview'] : false,
            ];
        } else {
            $return[ $id ] = [
                'label'          => $gate['checkout_label'],
                'icon'           => isset( $gate['icon'] ) ? $gate['icon'] : '',
                'is_pro_preview' => ! empty( $gate['is_pro_preview'] ) ? esc_attr( $gate['is_pro_preview'] ) : false,
            ];
        }
    }

    return $return;
}

/**
 * Get the display label for a stored payment method
 *
 * The stored value comes from the checkout request, so it is never trusted here:
 * it is sanitized, then matched against the registered gateways. Anything that is
 * not a known gateway is reported as unknown instead of being echoed back.
 *
 * @since 4.3.13
 *
 * @param string $payment_method Stored payment method.
 *
 * @return string
 */
function wpuf_get_payment_type_label( $payment_method ) {
    $payment_method = is_scalar( $payment_method ) ? sanitize_text_field( (string) $payment_method ) : '';

    if ( empty( $payment_method ) ) {
        return __( 'Unknown', 'wp-user-frontend' );
    }

    if ( 'bank' === $payment_method ) {
        return __( 'Bank/Manual', 'wp-user-frontend' );
    }

    $gateways = wpuf_get_gateways();

    if ( isset( $gateways[ $payment_method ] ) ) {
        return $gateways[ $payment_method ];
    }

    // A gateway that is switched off, or that belongs to a plugin that is not active
    // right now, is no longer registered: keep showing the stored name for those old
    // transactions. It is sanitized above, so it can never carry markup.
    return ucwords( $payment_method );
}

/**
 * Get the subscription page url
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_get_subscription_page_url() {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $page_id = wpuf_get_option( 'subscription_page', 'wpuf_payment' );

    return get_permalink( $page_id );
}

/**
 * Get all completed transactions
 *
 * @since 2.4.2
 *
 * @return array|string
 */
function wpuf_get_completed_transactions( $args = [] ) {

    $orderby = [ 'id', 'status', 'created' ];
    $order   = [ 'asc', 'desc' ];

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( ! in_array( $args['orderby'], $orderby, true ) ) {
        $args['orderby'] = 'id';
    }

    if ( ! in_array( $args['order'], $order, true ) ) {
        $args['order'] = 'DESC';
    }

    if ( $args['count'] ) {
        return Stores::transactions()->count_all();
    }

    return Stores::transactions()->all_rows( $args['orderby'], $args['order'], absint( $args['offset'] ), absint( $args['number'] ) );
}

/**
 * Get all pending transactions
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_pending_transactions( $args = [] ) {
    global $wpdb;

    $orderby = [ 'id', 'status', 'created' ];
    $order   = [ 'asc', 'desc' ];

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( ! in_array( $args['orderby'], $orderby, true ) ) {
        $args['orderby'] = 'id';
    }

    if ( ! in_array( $args['order'], $order, true ) ) {
        $args['order'] = 'DESC';
    }

    $pending_args = [
        'post_type'      => 'wpuf_order',
        'post_status'    => [ 'publish', 'pending' ],
        'posts_per_page' => $args['number'],
        'offset'         => $args['offset'],
        'orderby'        => $args['orderby'],
        'order'          => $args['order'],
    ];

    $wpuf_order_query = new WP_Query( $pending_args );

    if ( $args['count'] ) {
        return $wpuf_order_query->found_posts;
    }

    $transactions = $wpuf_order_query->get_posts();

    $items = [];

    foreach ( $transactions as $transaction ) {
        $info = Stores::transactions()->order_info( $transaction->ID );

        if ( ! $info ) {
            continue;
        }

        $tax      = ! empty( $info['tax'] ) ? $info['tax'] : 0;
        $subtotal = ! empty( $info['subtotal'] ) ? $info['subtotal'] : ( ! empty( $info['cost'] ) ? $info['cost'] : $info['price'] );

        $items[] = (object) [
            'id'               => $transaction->ID,
            'user_id'          => $info['user_info']['id'],
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'discount'         => ! empty( $info['discount'] ) ? $info['discount'] : 0,
            'coupon_id'        => ! empty( $info['post_data']['coupon_id'] ) ? absint( $info['post_data']['coupon_id'] ) : 0,
            'cost'             => ! empty( $info['cost'] ) ? floatval( $info['cost'] ) : ( ! empty( $info['price'] ) ? floatval( $info['price'] ) + floatval( $tax ) : $subtotal ),
            'tax'              => $tax,
            'post_id'          => ( $info['type'] === 'post' ) ? $info['item_number'] : 0,
            'pack_id'          => ( $info['type'] === 'pack' ) ? $info['item_number'] : 0,
            'payer_first_name' => $info['user_info']['first_name'],
            'payer_last_name'  => $info['user_info']['last_name'],
            'payer_email'      => $info['user_info']['email'],
            'payment_type'     => wpuf_get_payment_type_label( isset( $info['post_data']['wpuf_payment_method'] ) ? $info['post_data']['wpuf_payment_method'] : '' ),
            'transaction_id'   => 0,
            'created'          => $info['date'],
        ];
    }

    wp_reset_postdata();

    return $items;
}

/**
 * Get all pending and completed transactions
 *
 * @since 3.5.27
 *
 * @param $args
 *
 * @return array|int|void
 */
function wpuf_get_all_transactions( $args = [] ) {
    global $wpdb;
    $transaction_table = $wpdb->prefix . 'wpuf_transaction';

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];

    $orderby_keys = [ 'id', 'status', 'created' ];
    $order_keys = [ 'asc', 'desc' ];

    $args = wp_parse_args( $args, $defaults );

    if ( $args['count'] ) {
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
        return (int) $wpdb->get_var(
            "SELECT SUM(AllCount)
            FROM ((SELECT COUNT(*) AS AllCount FROM {$transaction_table})
            UNION ALL
                (SELECT COUNT(*) AS AllCount FROM {$wpdb->posts}
                    WHERE post_type = 'wpuf_order'
                    AND post_status IN('pending', 'publish'))) AS post_table"
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    $orderby       = in_array( $args['orderby'], $orderby_keys, true ) ? $args['orderby'] : 'id';
    $sorting_order = in_array( $args['order'], $order_keys, true ) ? $args['order'] : 'DESC';
    $offset        = (int) sanitize_key( $args['offset'] );
    $number        = (int) sanitize_key( $args['number'] );

    // get all the completed transaction from transaction table
    // and pending transaction from post table
    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix; orderby and order are allowlisted above.
    $transactions = $wpdb->get_results(
        $wpdb->prepare(
            "(SELECT id, user_id, status, subtotal, discount, coupon_id, tax, cost, post_id, pack_id, payer_first_name, payer_last_name, payer_email, payment_type, transaction_id, created FROM {$transaction_table})
            UNION ALL
            (SELECT ID AS id, post_author AS user_id, null AS status, null AS subtotal, null AS discount, null AS coupon_id, null AS tax, null AS cost, ID as post_id, null AS pack_id, null AS payer_first_name, null AS payer_last_name, null AS payer_email, null AS payment_type, 0 AS transaction_id, post_date AS created FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status IN ('pending', 'publish'))
            ORDER BY {$orderby} {$sorting_order}
            LIMIT %d, %d",
            'wpuf_order', $offset, $number
        )
    );
    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

    if ( ! $transactions ) {
        return;
    }

    foreach ( $transactions as $transaction ) {
        if ( $transaction->status ) {
            continue;
        }

        // get metadata for pending transactions
        $info = Stores::transactions()->order_info( $transaction->id );
        $payment_method = isset( $info['post_data']['wpuf_payment_method'] ) ? $info['post_data']['wpuf_payment_method'] : '';

        $type = isset( $info['type'] ) ? $info['type'] : '';
        $item_number = isset( $info['item_number'] ) ? $info['item_number'] : 0;

        // attach data to pending transactions
        $transaction->user_id          = isset( $info['user_info']['id'] ) ? $info['user_info']['id'] : 0;
        $transaction->status           = 'pending';
        $tax                           = isset( $info['tax'] ) ? $info['tax'] : 0;
        $subtotal                      = ! empty( $info['subtotal'] ) ? $info['subtotal'] : ( ! empty( $info['cost'] ) ? $info['cost'] : ( $info['price'] ?? 0 ) );
        $transaction->subtotal         = $subtotal;
        $transaction->discount         = ! empty( $info['discount'] ) ? $info['discount'] : 0;
        $transaction->coupon_id        = ! empty( $info['post_data']['coupon_id'] ) ? absint( $info['post_data']['coupon_id'] ) : 0;
        $transaction->cost             = ! empty( $info['cost'] ) ? floatval( $info['cost'] ) : ( ! empty( $info['price'] ) ? floatval( $info['price'] ) + floatval( $tax ) : $subtotal );
        $transaction->tax              = $tax;
        $transaction->post_id          = ( 'post' === $type ) ? $item_number : 0;
        $transaction->pack_id          = ( 'pack' === $type ) ? $item_number : 0;
        $transaction->payer_first_name = isset( $info['user_info']['first_name'] ) ? $info['user_info']['first_name'] : '';
        $transaction->payer_last_name  = isset( $info['user_info']['last_name'] ) ? $info['user_info']['last_name'] : '';
        $transaction->payer_email      = isset( $info['user_info']['email'] ) ? $info['user_info']['email'] : '';
        $transaction->payment_type     = ( 'bank' === $payment_method ) ? 'Bank/Manual' : ucwords( $payment_method );
        $transaction->transaction_id   = 0;
        $transaction->created          = isset( $info['date'] ) ? $info['date'] : '';
    }

    return $transactions;
}

/**
 * Get full list of currency codes.
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_currencies() {
    $currencies = [
        [
            'currency' => 'AED',
            'label'    => __( 'United Arab Emirates Dirham', 'wp-user-frontend' ),
            'symbol'   => 'د.إ',
        ],
        [
            'currency' => 'AFN',
            'label'    => __( 'Afghan Afghani', 'wp-user-frontend' ),
            'symbol'   => '؋',
        ],
        [
            'currency' => 'ALL',
            'label'    => __( 'Albanian Lek', 'wp-user-frontend' ),
            'symbol'   => 'L',
        ],
        [
            'currency' => 'AMD',
            'label'    => __( 'Armenian Dram', 'wp-user-frontend' ),
            'symbol'   => '&#1423;',
        ],
        [
            'currency' => 'ANG',
            'label'    => __( 'Netherlands Antillean Guilder', 'wp-user-frontend' ),
            'symbol'   => 'ƒ',
        ],
        [
            'currency' => 'AOA',
            'label'    => __( 'Angolan Kwanza', 'wp-user-frontend' ),
            'symbol'   => 'Kz',
        ],
        [
            'currency' => 'ARS',
            'label'    => __( 'Argentine Peso', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'AUD',
            'label'    => __( 'Australian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'AWG',
            'label'    => __( 'Aruban Florin', 'wp-user-frontend' ),
            'symbol'   => 'ƒ',
        ],
        [
            'currency' => 'AZN',
            'label'    => __( 'Azerbaijani Manat', 'wp-user-frontend' ),
            'symbol'   => '&#8380;',
        ],
        [
            'currency' => 'BAM',
            'label'    => __( 'Bosnia and Herzegovina Convertible Mark', 'wp-user-frontend' ),
            'symbol'   => 'KM',
        ],
        [
            'currency' => 'BBD',
            'label'    => __( 'Barbadian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'BDT',
            'label'    => __( 'Bangladeshi Taka', 'wp-user-frontend' ),
            'symbol'   => '&#2547;',
        ],
        [
            'currency' => 'BGN',
            'label'    => __( 'Bulgarian Lev', 'wp-user-frontend' ),
            'symbol'   => '&#1083;&#1074;.',
        ],
        [
            'currency' => 'BHD',
            'label'    => __( 'Bahraini Dinar', 'wp-user-frontend' ),
            'symbol'   => '.د.ب',
        ],
        [
            'currency' => 'BIF',
            'label'    => __( 'Burundian Franc', 'wp-user-frontend' ),
            'symbol'   => 'FBu',
        ],
        [
            'currency' => 'BMD',
            'label'    => __( 'Bermudian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'BND',
            'label'    => __( 'Brunei Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'BOB',
            'label'    => __( 'Bolivian Boliviano', 'wp-user-frontend' ),
            'symbol'   => 'Bs.',
        ],
        [
            'currency' => 'BRL',
            'label'    => __( 'Brazilian Real', 'wp-user-frontend' ),
            'symbol'   => '&#82;&#36;',
        ],
        [
            'currency' => 'BSD',
            'label'    => __( 'Bahamian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'BTN',
            'label'    => __( 'Bhutanese Ngultrum', 'wp-user-frontend' ),
            'symbol'   => 'Nu.',
        ],
        [
            'currency' => 'BWP',
            'label'    => __( 'Botswana Pula', 'wp-user-frontend' ),
            'symbol'   => 'P',
        ],
        [
            'currency' => 'BYN',
            'label'    => __( 'Belarusian Ruble', 'wp-user-frontend' ),
            'symbol'   => 'Br',
        ],
        [
            'currency' => 'BZD',
            'label'    => __( 'Belize Dollar', 'wp-user-frontend' ),
            'symbol'   => 'BZ&#36;',
        ],
        [
            'currency' => 'CAD',
            'label'    => __( 'Canadian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'CDF',
            'label'    => __( 'Congolese Franc', 'wp-user-frontend' ),
            'symbol'   => 'FC',
        ],
        [
            'currency' => 'CHF',
            'label'    => __( 'Swiss Franc', 'wp-user-frontend' ),
            'symbol'   => '&#67;&#72;&#70;',
        ],
        [
            'currency' => 'CLP',
            'label'    => __( 'Chilean Peso', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'CNY',
            'label'    => __( 'Chinese Yuan', 'wp-user-frontend' ),
            'symbol'   => '&yen;',
        ],
        [
            'currency' => 'COP',
            'label'    => __( 'Colombian Peso', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'CRC',
            'label'    => __( 'Costa Rican Colón', 'wp-user-frontend' ),
            'symbol'   => '&#8353;',
        ],
        [
            'currency' => 'CUP',
            'label'    => __( 'Cuban Peso', 'wp-user-frontend' ),
            'symbol'   => '&#8369;',
        ],
        [
            'currency' => 'CVE',
            'label'    => __( 'Cape Verdean Escudo', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'CZK',
            'label'    => __( 'Czech Koruna', 'wp-user-frontend' ),
            'symbol'   => '&#75;&#269;',
        ],
        [
            'currency' => 'DJF',
            'label'    => __( 'Djiboutian Franc', 'wp-user-frontend' ),
            'symbol'   => 'Fdj',
        ],
        [
            'currency' => 'DKK',
            'label'    => __( 'Danish Krone', 'wp-user-frontend' ),
            'symbol'   => 'kr.',
        ],
        [
            'currency' => 'DOP',
            'label'    => __( 'Dominican Peso', 'wp-user-frontend' ),
            'symbol'   => 'RD&#36;',
        ],
        [
            'currency' => 'DZD',
            'label'    => __( 'Algerian Dinar', 'wp-user-frontend' ),
            'symbol'   => 'DA',
        ],
        [
            'currency' => 'EGP',
            'label'    => __( 'Egyptian Pound', 'wp-user-frontend' ),
            'symbol'   => 'E&pound;',
        ],
        [
            'currency' => 'ERN',
            'label'    => __( 'Eritrean Nakfa', 'wp-user-frontend' ),
            'symbol'   => 'Nfk',
        ],
        [
            'currency' => 'ETB',
            'label'    => __( 'Ethiopian Birr', 'wp-user-frontend' ),
            'symbol'   => 'Br',
        ],
        [
            'currency' => 'EUR',
            'label'    => __( 'Euro', 'wp-user-frontend' ),
            'symbol'   => '&euro;',
        ],
        [
            'currency' => 'FJD',
            'label'    => __( 'Fijian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'FKP',
            'label'    => __( 'Falkland Islands Pound', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'GBP',
            'label'    => __( 'British Pound Sterling', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'GEL',
            'label'    => __( 'Georgian Lari', 'wp-user-frontend' ),
            'symbol'   => '&#8382;',
        ],
        [
            'currency' => 'GHS',
            'label'    => __( 'Ghanaian Cedi', 'wp-user-frontend' ),
            'symbol'   => '&#8373;',
        ],
        [
            'currency' => 'GIP',
            'label'    => __( 'Gibraltar Pound', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'GMD',
            'label'    => __( 'Gambian Dalasi', 'wp-user-frontend' ),
            'symbol'   => 'D',
        ],
        [
            'currency' => 'GNF',
            'label'    => __( 'Guinean Franc', 'wp-user-frontend' ),
            'symbol'   => 'FG',
        ],
        [
            'currency' => 'GTQ',
            'label'    => __( 'Guatemalan Quetzal', 'wp-user-frontend' ),
            'symbol'   => 'Q',
        ],
        [
            'currency' => 'GYD',
            'label'    => __( 'Guyanese Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'HKD',
            'label'    => __( 'Hong Kong Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'HNL',
            'label'    => __( 'Honduran Lempira', 'wp-user-frontend' ),
            'symbol'   => 'L',
        ],
        [
            'currency' => 'HRK',
            'label'    => __( 'Croatian Kuna', 'wp-user-frontend' ),
            'symbol'   => 'Kn',
        ],
        [
            'currency' => 'HTG',
            'label'    => __( 'Haitian Gourde', 'wp-user-frontend' ),
            'symbol'   => 'G',
        ],
        [
            'currency' => 'HUF',
            'label'    => __( 'Hungarian Forint', 'wp-user-frontend' ),
            'symbol'   => '&#70;&#116;',
        ],
        [
            'currency' => 'IDR',
            'label'    => __( 'Indonesian Rupiah', 'wp-user-frontend' ),
            'symbol'   => 'Rp',
        ],
        [
            'currency' => 'ILS',
            'label'    => __( 'Israeli New Shekel', 'wp-user-frontend' ),
            'symbol'   => '&#8362;',
        ],
        [
            'currency' => 'INR',
            'label'    => __( 'Indian Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8377;',
        ],
        [
            'currency' => 'IQD',
            'label'    => __( 'Iraqi Dinar', 'wp-user-frontend' ),
            'symbol'   => 'ع.د',
        ],
        [
            'currency' => 'IRR',
            'label'    => __( 'Iranian Rial', 'wp-user-frontend' ),
            'symbol'   => '&#65020;',
        ],
        [
            'currency' => 'ISK',
            'label'    => __( 'Icelandic Króna', 'wp-user-frontend' ),
            'symbol'   => 'kr',
        ],
        [
            'currency' => 'JMD',
            'label'    => __( 'Jamaican Dollar', 'wp-user-frontend' ),
            'symbol'   => 'J&#36;',
        ],
        [
            'currency' => 'JOD',
            'label'    => __( 'Jordanian Dinar', 'wp-user-frontend' ),
            'symbol'   => 'د.أ',
        ],
        [
            'currency' => 'JPY',
            'label'    => __( 'Japanese Yen', 'wp-user-frontend' ),
            'symbol'   => '&yen;',
        ],
        [
            'currency' => 'KES',
            'label'    => __( 'Kenyan Shilling', 'wp-user-frontend' ),
            'symbol'   => 'KSh',
        ],
        [
            'currency' => 'KGS',
            'label'    => __( 'Kyrgyzstani Som', 'wp-user-frontend' ),
            'symbol'   => 'сом',
        ],
        [
            'currency' => 'KHR',
            'label'    => __( 'Cambodian Riel', 'wp-user-frontend' ),
            'symbol'   => '&#6107;',
        ],
        [
            'currency' => 'KMF',
            'label'    => __( 'Comorian Franc', 'wp-user-frontend' ),
            'symbol'   => 'CF',
        ],
        [
            'currency' => 'KPW',
            'label'    => __( 'North Korean Won', 'wp-user-frontend' ),
            'symbol'   => '&#8361;',
        ],
        [
            'currency' => 'KRW',
            'label'    => __( 'South Korean Won', 'wp-user-frontend' ),
            'symbol'   => '&#8361;',
        ],
        [
            'currency' => 'KWD',
            'label'    => __( 'Kuwaiti Dinar', 'wp-user-frontend' ),
            'symbol'   => 'د.ك',
        ],
        [
            'currency' => 'KYD',
            'label'    => __( 'Cayman Islands Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'KZT',
            'label'    => __( 'Kazakhstani Tenge', 'wp-user-frontend' ),
            'symbol'   => '&#8376;',
        ],
        [
            'currency' => 'LAK',
            'label'    => __( 'Lao Kip', 'wp-user-frontend' ),
            'symbol'   => '&#8365;',
        ],
        [
            'currency' => 'LBP',
            'label'    => __( 'Lebanese Pound', 'wp-user-frontend' ),
            'symbol'   => 'ل.ل',
        ],
        [
            'currency' => 'LKR',
            'label'    => __( 'Sri Lankan Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8360;',
        ],
        [
            'currency' => 'LRD',
            'label'    => __( 'Liberian Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'LSL',
            'label'    => __( 'Lesotho Loti', 'wp-user-frontend' ),
            'symbol'   => 'L',
        ],
        [
            'currency' => 'LYD',
            'label'    => __( 'Libyan Dinar', 'wp-user-frontend' ),
            'symbol'   => 'ل.د',
        ],
        [
            'currency' => 'MAD',
            'label'    => __( 'Moroccan Dirham', 'wp-user-frontend' ),
            'symbol'   => 'د.م.',
        ],
        [
            'currency' => 'MDL',
            'label'    => __( 'Moldovan Leu', 'wp-user-frontend' ),
            'symbol'   => 'L',
        ],
        [
            'currency' => 'MGA',
            'label'    => __( 'Malagasy Ariary', 'wp-user-frontend' ),
            'symbol'   => 'Ar',
        ],
        [
            'currency' => 'MKD',
            'label'    => __( 'Macedonian Denar', 'wp-user-frontend' ),
            'symbol'   => 'ден',
        ],
        [
            'currency' => 'MMK',
            'label'    => __( 'Myanmar Kyat', 'wp-user-frontend' ),
            'symbol'   => 'K',
        ],
        [
            'currency' => 'MNT',
            'label'    => __( 'Mongolian Tögrög', 'wp-user-frontend' ),
            'symbol'   => '&#8366;',
        ],
        [
            'currency' => 'MOP',
            'label'    => __( 'Macanese Pataca', 'wp-user-frontend' ),
            'symbol'   => 'MOP&#36;',
        ],
        [
            'currency' => 'MRU',
            'label'    => __( 'Mauritanian Ouguiya', 'wp-user-frontend' ),
            'symbol'   => 'UM',
        ],
        [
            'currency' => 'MUR',
            'label'    => __( 'Mauritian Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8360;',
        ],
        [
            'currency' => 'MVR',
            'label'    => __( 'Maldivian Rufiyaa', 'wp-user-frontend' ),
            'symbol'   => 'Rf',
        ],
        [
            'currency' => 'MWK',
            'label'    => __( 'Malawian Kwacha', 'wp-user-frontend' ),
            'symbol'   => 'MK',
        ],
        [
            'currency' => 'MXN',
            'label'    => __( 'Mexican Peso', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'MYR',
            'label'    => __( 'Malaysian Ringgit', 'wp-user-frontend' ),
            'symbol'   => '&#82;&#77;',
        ],
        [
            'currency' => 'MZN',
            'label'    => __( 'Mozambican Metical', 'wp-user-frontend' ),
            'symbol'   => 'MT',
        ],
        [
            'currency' => 'NAD',
            'label'    => __( 'Namibian Dollar', 'wp-user-frontend' ),
            'symbol'   => 'N&#36;',
        ],
        [
            'currency' => 'NGN',
            'label'    => __( 'Nigerian Naira', 'wp-user-frontend' ),
            'symbol'   => '&#8358;',
        ],
        [
            'currency' => 'NIO',
            'label'    => __( 'Nicaraguan Córdoba', 'wp-user-frontend' ),
            'symbol'   => 'C&#36;',
        ],
        [
            'currency' => 'NOK',
            'label'    => __( 'Norwegian Krone', 'wp-user-frontend' ),
            'symbol'   => '&#107;&#114;',
        ],
        [
            'currency' => 'NPR',
            'label'    => __( 'Nepalese Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8360;',
        ],
        [
            'currency' => 'NZD',
            'label'    => __( 'New Zealand Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'OMR',
            'label'    => __( 'Omani Rial', 'wp-user-frontend' ),
            'symbol'   => 'ر.ع.',
        ],
        [
            'currency' => 'PAB',
            'label'    => __( 'Panamanian Balboa', 'wp-user-frontend' ),
            'symbol'   => 'B/.',
        ],
        [
            'currency' => 'PEN',
            'label'    => __( 'Peruvian Sol', 'wp-user-frontend' ),
            'symbol'   => 'S/',
        ],
        [
            'currency' => 'PGK',
            'label'    => __( 'Papua New Guinean Kina', 'wp-user-frontend' ),
            'symbol'   => 'K',
        ],
        [
            'currency' => 'PHP',
            'label'    => __( 'Philippine Peso', 'wp-user-frontend' ),
            'symbol'   => '&#8369;',
        ],
        [
            'currency' => 'PKR',
            'label'    => __( 'Pakistani Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8360;',
        ],
        [
            'currency' => 'PLN',
            'label'    => __( 'Polish Zloty', 'wp-user-frontend' ),
            'symbol'   => '&#122;&#322;',
        ],
        [
            'currency' => 'PYG',
            'label'    => __( 'Paraguayan Guaraní', 'wp-user-frontend' ),
            'symbol'   => '&#8370;',
        ],
        [
            'currency' => 'QAR',
            'label'    => __( 'Qatari Riyal', 'wp-user-frontend' ),
            'symbol'   => 'ر.ق',
        ],
        [
            'currency' => 'RON',
            'label'    => __( 'Romanian Leu', 'wp-user-frontend' ),
            'symbol'   => 'lei',
        ],
        [
            'currency' => 'RSD',
            'label'    => __( 'Serbian Dinar', 'wp-user-frontend' ),
            'symbol'   => 'дин.',
        ],
        [
            'currency' => 'RUB',
            'label'    => __( 'Russian Ruble', 'wp-user-frontend' ),
            'symbol'   => '&#8381;',
        ],
        [
            'currency' => 'RWF',
            'label'    => __( 'Rwandan Franc', 'wp-user-frontend' ),
            'symbol'   => 'FRw',
        ],
        [
            'currency' => 'SAR',
            'label'    => __( 'Saudi Riyal', 'wp-user-frontend' ),
            'symbol'   => 'ر.س',
        ],
        [
            'currency' => 'SBD',
            'label'    => __( 'Solomon Islands Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'SCR',
            'label'    => __( 'Seychellois Rupee', 'wp-user-frontend' ),
            'symbol'   => '&#8360;',
        ],
        [
            'currency' => 'SDG',
            'label'    => __( 'Sudanese Pound', 'wp-user-frontend' ),
            'symbol'   => 'ج.س.',
        ],
        [
            'currency' => 'SEK',
            'label'    => __( 'Swedish Krona', 'wp-user-frontend' ),
            'symbol'   => '&#107;&#114;',
        ],
        [
            'currency' => 'SGD',
            'label'    => __( 'Singapore Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'SHP',
            'label'    => __( 'Saint Helena Pound', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'SLE',
            'label'    => __( 'Sierra Leonean Leone', 'wp-user-frontend' ),
            'symbol'   => 'Le',
        ],
        [
            'currency' => 'SOS',
            'label'    => __( 'Somali Shilling', 'wp-user-frontend' ),
            'symbol'   => 'S',
        ],
        [
            'currency' => 'SRD',
            'label'    => __( 'Surinamese Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'SSP',
            'label'    => __( 'South Sudanese Pound', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'STN',
            'label'    => __( 'São Tomé and Príncipe Dobra', 'wp-user-frontend' ),
            'symbol'   => 'Db',
        ],
        [
            'currency' => 'SYP',
            'label'    => __( 'Syrian Pound', 'wp-user-frontend' ),
            'symbol'   => '&pound;',
        ],
        [
            'currency' => 'SZL',
            'label'    => __( 'Eswatini Lilangeni', 'wp-user-frontend' ),
            'symbol'   => 'L',
        ],
        [
            'currency' => 'THB',
            'label'    => __( 'Thai Baht', 'wp-user-frontend' ),
            'symbol'   => '&#3647;',
        ],
        [
            'currency' => 'TJS',
            'label'    => __( 'Tajikistani Somoni', 'wp-user-frontend' ),
            'symbol'   => 'SM',
        ],
        [
            'currency' => 'TMT',
            'label'    => __( 'Turkmenistani Manat', 'wp-user-frontend' ),
            'symbol'   => 'T',
        ],
        [
            'currency' => 'TND',
            'label'    => __( 'Tunisian Dinar', 'wp-user-frontend' ),
            'symbol'   => 'د.ت',
        ],
        [
            'currency' => 'TOP',
            'label'    => __( 'Tongan Paʻanga', 'wp-user-frontend' ),
            'symbol'   => 'T&#36;',
        ],
        [
            'currency' => 'TRY',
            'label'    => __( 'Turkish Lira', 'wp-user-frontend' ),
            'symbol'   => '&#8378;',
        ],
        [
            'currency' => 'TTD',
            'label'    => __( 'Trinidad and Tobago Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#84;&#84;&#36;',
        ],
        [
            'currency' => 'TWD',
            'label'    => __( 'New Taiwan Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#78;&#84;&#36;',
        ],
        [
            'currency' => 'TZS',
            'label'    => __( 'Tanzanian Shilling', 'wp-user-frontend' ),
            'symbol'   => 'TSh',
        ],
        [
            'currency' => 'UAH',
            'label'    => __( 'Ukrainian Hryvnia', 'wp-user-frontend' ),
            'symbol'   => '&#8372;',
        ],
        [
            'currency' => 'UGX',
            'label'    => __( 'Ugandan Shilling', 'wp-user-frontend' ),
            'symbol'   => 'USh',
        ],
        [
            'currency' => 'USD',
            'label'    => __( 'US Dollar', 'wp-user-frontend' ),
            'symbol'   => '&#36;',
        ],
        [
            'currency' => 'UYU',
            'label'    => __( 'Uruguayan Peso', 'wp-user-frontend' ),
            'symbol'   => '&#36;U',
        ],
        [
            'currency' => 'UZS',
            'label'    => __( 'Uzbekistani Som', 'wp-user-frontend' ),
            'symbol'   => 'сўм',
        ],
        [
            'currency' => 'VES',
            'label'    => __( 'Venezuelan Bolívar', 'wp-user-frontend' ),
            'symbol'   => 'Bs.',
        ],
        [
            'currency' => 'VND',
            'label'    => __( 'Vietnamese Đồng', 'wp-user-frontend' ),
            'symbol'   => '&#8363;',
        ],
        [
            'currency' => 'VUV',
            'label'    => __( 'Vanuatu Vatu', 'wp-user-frontend' ),
            'symbol'   => 'VT',
        ],
        [
            'currency' => 'WST',
            'label'    => __( 'Samoan Tālā', 'wp-user-frontend' ),
            'symbol'   => 'WS&#36;',
        ],
        [
            'currency' => 'XAF',
            'label'    => __( 'Central African CFA Franc', 'wp-user-frontend' ),
            'symbol'   => 'FCFA',
        ],
        [
            'currency' => 'XCD',
            'label'    => __( 'East Caribbean Dollar', 'wp-user-frontend' ),
            'symbol'   => 'EC&#36;',
        ],
        [
            'currency' => 'XOF',
            'label'    => __( 'West African CFA Franc', 'wp-user-frontend' ),
            'symbol'   => 'CFA',
        ],
        [
            'currency' => 'XPF',
            'label'    => __( 'CFP Franc', 'wp-user-frontend' ),
            'symbol'   => '&#8355;',
        ],
        [
            'currency' => 'YER',
            'label'    => __( 'Yemeni Rial', 'wp-user-frontend' ),
            'symbol'   => '&#65020;',
        ],
        [
            'currency' => 'ZAR',
            'label'    => __( 'South African Rand', 'wp-user-frontend' ),
            'symbol'   => '&#82;',
        ],
        [
            'currency' => 'ZMW',
            'label'    => __( 'Zambian Kwacha', 'wp-user-frontend' ),
            'symbol'   => 'ZK',
        ],
        [
            'currency' => 'ZWL',
            'label'    => __( 'Zimbabwean Dollar', 'wp-user-frontend' ),
            'symbol'   => 'Z&#36;',
        ],
    ];

    return apply_filters( 'wpuf_currencies', $currencies );
}

/**
 * Get global currency
 *
 * @since 2.4.2
 *
 * @param string $type
 *
 * @return mixed
 */
function wpuf_get_currency( $type = '' ) {
    $currency_code = wpuf_get_option( 'currency', 'wpuf_payment', 'USD' );

    if ( 'code' === $type ) {
        return $currency_code;
    }

    $currencies = wpuf_get_currencies();
    $index      = array_search( $currency_code, array_column( $currencies, 'currency' ), true );
    $currency   = $currencies[ $index ];

    if ( 'symbol' === $type ) {
        return $currency['symbol'];
    }

    return $currency;
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function get_wpuf_price_format() {
    $currency_pos = wpuf_get_option( 'currency_position', 'wpuf_payment', 'left' );
    $format       = '%1$s%2$s';

    switch ( $currency_pos ) {
        case 'left':
            $format = '%1$s%2$s';
            break;

        case 'right':
            $format = '%2$s%1$s';
            break;

        case 'left_space':
            $format = '%1$s&nbsp;%2$s';
            break;

        case 'right_space':
            $format = '%2$s&nbsp;%1$s';
            break;
    }

    return apply_filters( 'wpuf_price_format', $format, $currency_pos );
}

/**
 * Return the thousand separator for prices.
 *
 * @since  2.4.4
 *
 * @return string
 */
function wpuf_get_price_thousand_separator() {
    $separator = stripslashes( wpuf_get_option( 'wpuf_price_thousand_sep', 'wpuf_payment', ',' ) );

    return $separator;
}

/**
 * Return the decimal separator for prices.
 *
 * @since  2.4.4
 *
 * @return string
 */
function wpuf_get_price_decimal_separator() {
    $separator = stripslashes( wpuf_get_option( 'wpuf_price_decimal_sep', 'wpuf_payment', '.' ) );

    return $separator;
}

/**
 * Return the number of decimals after the decimal point.
 *
 * @since  2.4.4
 *
 * @return int
 */
function wpuf_get_price_decimals() {
    return absint( wpuf_get_option( 'wpuf_price_num_decimals', 'wpuf_payment', 2 ) );
}

/**
 * Trim trailing zeros off prices.
 *
 * @param mixed $price
 *
 * @return string
 */
function wpuf_trim_zeros( $price ) {
    return preg_replace( '/' . preg_quote( wc_get_price_decimal_separator(), '/' ) . '0++$/', '', $price );
}

/**
 * Format the pricing number
 *
 * @since 2.4.2
 *
 * @param number $number
 * @param  array
 *
 * @return mixed
 */
function wpuf_format_price( $price, $formated = true, $args = [] ) {

    $price_args = apply_filters(
        'wpuf_price_args', wp_parse_args(
            $args, [
                'currency'           => $formated ? wpuf_get_currency( 'symbol' ) : '',
                'decimal_separator'  => wpuf_get_price_decimal_separator(),
                'thousand_separator' => $formated ? wpuf_get_price_thousand_separator() : '',
                'decimals'           => wpuf_get_price_decimals(),
                'price_format'       => get_wpuf_price_format(),
            ]
        )
    );

    $currency = $price_args['currency'];
    $decimal_separator = $price_args['decimal_separator'];
    $thousand_separator = $price_args['thousand_separator'];
    $decimals = $price_args['decimals'];
    $price_format = $price_args['price_format'];
    $negative        = $price < 0;
    $price           = apply_filters( 'wpuf_raw_price', floatval( $negative ? $price * -1 : $price ) );
    $price           = apply_filters( 'wpuf_formatted_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

    if ( apply_filters( 'wpuf_price_trim_zeros', false ) && $decimals > 0 ) {
        $price = wpuf_trim_zeros( $price );
    }

    $formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, $currency, $price );

    return apply_filters( 'wpuf_format_price', $formatted_price, $price, $args );
}

/**
 * Determine page after payment success
 *
 * @since 3.5.27_PRO
 *
 * @param $data
 *
 * @return bool|false|string|WP_Error
 */
function wpuf_payment_success_page( $data ) {
    $gateway          = ! empty( $data['wpuf_payment_method'] ) ? $data['wpuf_payment_method'] : '';
    $success_query    = 'wpuf_' . $gateway . '_success';
    $redirect_page    = '';
    $redirect_page_id = 0;
    $payment_method   = ! empty( $data['post_data']['wpuf_payment_method'] ) ? $data['post_data']['wpuf_payment_method'] : '';

    if ( 'bank' === $payment_method ) {
        $redirect_page_id = wpuf_get_option( 'bank_success', 'wpuf_payment' );
    } else {
        $redirect_page_id = wpuf_get_option( 'payment_success', 'wpuf_payment' );
    }

    if ( 'post' === $data['type'] ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only id on the payment success redirect.
        $post_id           = array_key_exists( 'item_number', $data ) && ! empty( $data['item_number'] ) ? $data['item_number'] : ( isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0 );
        $form_id           = Stores::submissions()->form_id( $post_id );
        $form_settings     = wpuf_get_form_settings( $form_id );
        $ppp_success_page  = ! empty( $form_settings['ppp_payment_success_page'] ) ? $form_settings['ppp_payment_success_page'] : '';
        $redirect_page_id  = $ppp_success_page ? $ppp_success_page : $redirect_page_id;
    }

    $redirect_page = $redirect_page_id ? add_query_arg( 'action', $success_query, untrailingslashit( get_permalink( $redirect_page_id ) ) ) : add_query_arg(
        'action', $success_query, untrailingslashit(
            get_permalink(
                wpuf_get_option(
                    'subscription_page',
                    'wpuf_payment'
                )
            )
        )
    );
    //for bank
    $redirect_page = ! empty( $data['wpuf_payment_method'] ) && 'bank' === $data['wpuf_payment_method'] ? get_permalink(
        wpuf_get_option(
            'bank_success',
            'wpuf_payment'
        )
    ) : $redirect_page;

    return $redirect_page;
}

/**
 * The feature lines of a subscription pack card: the pack's own list when
 * set, otherwise one line per limit of its advanced configuration (the lines
 * the pack card always printed).
 *
 * @since WPUF_SINCE
 *
 * @param WP_Post|object $pack Pack with `meta_value`
 *
 * @return string[] Plain text lines
 */
function wpuf_subscription_pack_features( $pack ) {
    $meta     = isset( $pack->meta_value ) && is_array( $pack->meta_value ) ? $pack->meta_value : [];
    $features = [];

    if ( ! empty( $meta['features'] ) && is_array( $meta['features'] ) ) {
        foreach ( $meta['features'] as $feature ) {
            $features[] = wp_strip_all_tags( (string) $feature );
        }

        /** This filter is documented below. */
        return (array) apply_filters( 'wpuf_subscription_pack_features', $features, $pack );
    }

    $post_type_limits = isset( $meta['_post_type_name'] ) ? maybe_unserialize( $meta['_post_type_name'] ) : [];
    $additional_cpt   = isset( $meta['additional_cpt_options'] ) ? maybe_unserialize( $meta['additional_cpt_options'] ) : [];
    $limits           = array_merge( (array) $post_type_limits, (array) $additional_cpt );

    // type => [ unlimited line, "%d allowed" line ]
    $lines = [
        'post'              => [ __( 'Unlimited posts', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d posts allowed', 'wp-user-frontend' ) ],
        'page'              => [ __( 'Unlimited pages', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d pages allowed', 'wp-user-frontend' ) ],
        'user_request'      => [ __( 'Unlimited user requests', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d user requests allowed', 'wp-user-frontend' ) ],
        'product'           => [ __( 'Unlimited products', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d products allowed', 'wp-user-frontend' ) ],
        'wp_block'          => [ __( 'Unlimited reusable blocks', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d reusable blocks allowed', 'wp-user-frontend' ) ],
        'wp_template'       => [ __( 'Unlimited templates', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d templates allowed', 'wp-user-frontend' ) ],
        'wp_template_part'  => [ __( 'Unlimited template parts', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d template parts allowed', 'wp-user-frontend' ) ],
        'wp_navigation'     => [ __( 'Unlimited navigation menus', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d navigation menus allowed', 'wp-user-frontend' ) ],
        'wp_global_styles'  => [ __( 'Unlimited global styles', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d global styles allowed', 'wp-user-frontend' ) ],
        'wp_font_family'    => [ __( 'Unlimited font families', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d font families allowed', 'wp-user-frontend' ) ],
        'wp_font_face'      => [ __( 'Unlimited font faces', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d font faces allowed', 'wp-user-frontend' ) ],
        'product_variation' => [ __( 'Unlimited product variations', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d product variations allowed', 'wp-user-frontend' ) ],
        'shop_order'        => [ __( 'Unlimited shop orders', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d shop orders allowed', 'wp-user-frontend' ) ],
        'shop_order_refund' => [ __( 'Unlimited shop refunds', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d shop refunds allowed', 'wp-user-frontend' ) ],
        'shop_coupon'       => [ __( 'Unlimited shop coupons', 'wp-user-frontend' ), /* translators: %d: limit */ __( '%d shop coupons allowed', 'wp-user-frontend' ) ],
    ];

    foreach ( $lines as $type => $texts ) {
        if ( ! isset( $limits[ $type ] ) || '0' === (string) $limits[ $type ] ) {
            continue;
        }

        $features[] = '-1' === (string) $limits[ $type ] ? $texts[0] : sprintf( $texts[1], intval( $limits[ $type ] ) );
    }

    if ( isset( $meta['_total_feature_item'] ) && '0' !== (string) $meta['_total_feature_item'] ) {
        /* translators: %d: number of featured items */
        $features[] = '-1' === (string) $meta['_total_feature_item'] ? __( 'Unlimited featured items', 'wp-user-frontend' ) : sprintf( /* translators: %d: number of featured items */ __( '%d featured items', 'wp-user-frontend' ), intval( $meta['_total_feature_item'] ) );
    }

    if ( isset( $meta['_enable_post_expiration'] ) && 'yes' === $meta['_enable_post_expiration'] ) {
        $expiry_period = isset( $meta['_post_expiration_number'] ) ? intval( $meta['_post_expiration_number'] ) : 0;
        $expiry_type   = isset( $meta['_post_expiration_period'] ) ? $meta['_post_expiration_period'] : 'day';

        /* translators: %d: number, %s: period */
        $features[] = $expiry_period > 0 ? sprintf( /* translators: %d: number, %s: period */ __( 'Posts expire after %d %s', 'wp-user-frontend' ), $expiry_period, $expiry_type . ( $expiry_period > 1 ? 's' : '' ) ) : __( 'Post expiration enabled', 'wp-user-frontend' ); // phpcs:ignore WordPress.WP.I18n.UnorderedPlaceholdersText -- the pack card's existing msgid, kept for its translations.
    }

    if ( isset( $meta['_recurring_pay'] ) && 'yes' === $meta['_recurring_pay'] ) {
        $features[] = __( 'Recurring subscription', 'wp-user-frontend' );
    }

    if ( isset( $meta['_trial_status'] ) && 'yes' === $meta['_trial_status'] ) {
        $trial_duration = isset( $meta['_trial_duration'] ) ? intval( $meta['_trial_duration'] ) : 0;
        $trial_type     = isset( $meta['_trial_duration_type'] ) ? $meta['_trial_duration_type'] : 'day';

        /* translators: %d: number, %s: period */
        $features[] = $trial_duration > 0 ? sprintf( /* translators: %d: number, %s: period */ __( '%d %s free trial', 'wp-user-frontend' ), $trial_duration, $trial_type . ( $trial_duration > 1 ? 's' : '' ) ) : __( 'Free trial available', 'wp-user-frontend' ); // phpcs:ignore WordPress.WP.I18n.UnorderedPlaceholdersText -- the pack card's existing msgid, kept for its translations.
    }

    if ( isset( $meta['_enable_mail_after_expired'] ) && 'yes' === $meta['_enable_mail_after_expired'] ) {
        $features[] = __( 'Email notifications on post expiry', 'wp-user-frontend' );
    }

    if ( empty( $features ) ) {
        $features[] = __( 'Full website access', 'wp-user-frontend' );
    }

    /**
     * Filters the feature lines of a subscription pack card.
     *
     * @since WPUF_SINCE
     *
     * @param string[] $features Plain text lines
     * @param object   $pack     Pack
     */
    return (array) apply_filters( 'wpuf_subscription_pack_features', $features, $pack );
}
