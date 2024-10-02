<?php

namespace WeDevs\Wpuf\Admin;

/**
 * Manage Subscription packs
 */
class Transaction {
    /**
     * The constructor
     */
    public function __construct() {
        add_action( 'wpuf_load_transactions_page', [ $this, 'remove_notices' ] );
        add_action( 'wpuf_load_transactions_page', [ $this, 'enqueue_admin_scripts' ] );
        add_filter( 'script_loader_tag', [ $this, 'add_type_attribute' ], 10, 3 );
    }

    /**
     * Add type="module" to the script tag
     *
     * @param $tag
     * @param $handle
     * @param $src
     *
     * @since WPUF_SINCE
     *
     * @return mixed|string
     */
    public function add_type_attribute( $tag, $handle, $src ) {
        // Check if this is the script you want to modify
        if ( 'wpuf-admin-transactions' === $handle ) {
            // phpcs:ignore
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        }

        return $tag;
    }

    /**
     * Enqueue scripts for subscription page
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'wpuf-admin-transactions' );
        wp_enqueue_style( 'wpuf-admin-transactions' );
        wp_script_add_data( 'wpuf-admin-transactions', 'type', 'module' );

        wp_localize_script(
            'wpuf-admin-transactions', 'wpufTransactions',
            [
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'transactionSummary' => $this->get_transaction_summary(),
                'perPage'            => apply_filters( 'wpuf_transactions_per_page', 10 ),
            ]
        );
    }

    /**
     * Remove admin notices from this page
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function remove_notices() {
        add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
    }

    /**
     * Get transaction summary
     *
     * @since WPUF_SINCE
     *
     * @param string $args
     * The arguments. Default is empty array.
     * - time: string
     *      The time. Default is 'all'.
     * - per_page: int
     *      The number of items per page. Default is 10.
     *
     *
     * @return array
     */
    public function get_transaction_summary( $args = [] ) {
        $default = [
            'time' => 'all',
        ];

        $args = wp_parse_args( $args, $default );

        $income = $this->get_total_income( $args['time'] );

        $income_direction = $this->get_income_direction(
            [
                'time'   => $args['time'],
                'income' => $income,
                'type'   => 'total',
            ]
        );

        $change_direction  = 'all' === $args['time'] ? '' : $income_direction['profit_trend'];
        $change_percentage = 'all' === $args['time'] ? '' : $income_direction['percentage_change'];

//        switch ( $args['time'] ) {
//            case 'this_month':
//                $total_income      = $this->get_total_income( $args['time'] );
//                $total_change_type = 'positive';
//                $change_percentage = 2.5;
//                break;
//            case 'last_month':
//                $total_income = 2000;
//                break;
//            case 'last_6_months':
//                $total_income = 3000;
//                break;
//            default:
//                // get total cost from wpuf_transaction table without the refunded amount
//                $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction" );
//        }

        $total = apply_filters(
            'wpuf_transaction_summary_total', [
                'amount'      => $income,
                'change_type' => $change_direction,
                'percentage'  => $change_percentage,
                'label'       => __( 'Total Transactions', 'wp-user-frontend' ),
            ]
        );

        $approved = apply_filters(
            'wpuf_transaction_summary_approved', [
                'amount'         => 4000,
                'change_type'    => 'negative',
                'percentage'     => 4.5,
                'label'          => __( 'Approved', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $pending = apply_filters(
            'wpuf_transaction_summary_pending', [
                'amount'         => 4000,
                'change_type'    => 'positive',
                'percentage'     => 4.5,
                'label'          => __( 'Pending', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $refunded = apply_filters(
            'wpuf_transaction_summary_refunded', [
                'amount'         => 1340,
                'change_type'    => 'negative',
                'percentage'     => 5,
                'label'          => __( 'Refunded', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $subscriptions = apply_filters(
            'wpuf_transaction_summary_subscriptions', [
                'amount'         => 7634,
                'change_type'    => 'positive',
                'percentage'     => 7.9,
                'label'          => __( 'Subscriptions', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $summary = apply_filters(
            'wpuf_transaction_summary', [
                'total'         => $total,
                'approved'      => $approved,
                'pending'       => $pending,
                'refunded'      => $refunded,
                'subscriptions' => $subscriptions,
            ]
        );

        return $summary;
    }

    /**
     * Get income direction. Based on the time and type, it will return if the income increases or decreases.
     * Also, it will return the percentage of change.
     *
     * @since WPUF_SINCE
     *
     * @param array $args
     * The arguments. Default is empty array.
     * - time: string
     *      The time. Default is 'all'.
     * - income: int
     *      The income. Default is 0.
     * - type: string
     *      The type. Default is 'total'.
     *
     * @return string
     */
    public function get_income_direction( $args = [] ) {
        global $wpdb;

        $default = [
            'time'   => 'this_month',
            'income' => 0,
            'type'   => 'total',
        ];

        $args = wp_parse_args( $args, $default );

        $result = [
            'current_month'     => '',
            'current_profit'    => '',
            'previous_profit'   => '',
            'profit_trend'      => '',
            'percentage_change' => '',
        ];

        if ( 'total' === $args['type'] && 'this_month' === $args['time'] ) {
            $result = $wpdb->get_results(
                "WITH MonthlyTotals AS (
    SELECT
        DATE_FORMAT(created, '%Y-%m') AS month,
        SUM(cost) AS monthly_cost
    FROM {$wpdb->prefix}wpuf_transaction
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created, '%Y-%m')
)

SELECT
    current.month AS current_month,
    current.monthly_cost AS current_profit,
    previous.monthly_cost AS previous_profit,
    CASE
        WHEN current.monthly_cost > previous.monthly_cost THEN '+'
        WHEN current.monthly_cost < previous.monthly_cost THEN '-'
        ELSE '0'
    END AS profit_trend,
    ROUND(((current.monthly_cost - previous.monthly_cost) / previous.monthly_cost) * 100, 2) AS percentage_change
FROM MonthlyTotals AS current
JOIN MonthlyTotals AS previous
    ON current.month = DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(previous.month, '-01'), '%Y-%m-%d'), INTERVAL 1 MONTH), '%Y-%m')
ORDER BY current.month DESC
LIMIT 1;",
                ARRAY_A
            );
        }

        return isset( $result[0] ) ? $result[0] : $result;

        // 6 months
//        $result = $wpdb->get_results(
//            "WITH MonthlyTotals AS (
//    SELECT
//        DATE_FORMAT(created, '%Y-%m') as month,
//        SUM(cost) as monthly_cost
//    FROM {$wpdb->prefix}wpuf_transaction
//    WHERE status = 'completed'
//    GROUP BY DATE_FORMAT(created, '%Y-%m')
//)
//SELECT
//    current.month as current_month,
//    current.monthly_cost as current_profit,
//    previous.month as six_months_ago,
//    previous.monthly_cost as previous_profit,
//    CASE
//        WHEN current.monthly_cost > previous.monthly_cost THEN 'Increase'
//        WHEN current.monthly_cost < previous.monthly_cost THEN 'Decrease'
//        ELSE 'No Change'
//    END AS profit_trend,
//    ROUND(((current.monthly_cost - previous.monthly_cost) / previous.monthly_cost) * 100, 2) AS percentage_change
//FROM MonthlyTotals as current
//JOIN MonthlyTotals as previous
//    ON current.month = DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(previous.month, '-01'), '%Y-%m-%d'), INTERVAL 6 MONTH), '%Y-%m')
//ORDER BY current.month DESC
//LIMIT 1"
//        );
    }

    /**
     * Get total income
     *
     * @since WPUF_SINCE
     *
     * @param string $time
     * The time. Default is 'all'.
     *
     * @return int
     */
    public function get_total_income( $time = 'all' ) {
        global $wpdb;

        switch ( $time ) {
            case 'this_month':
                $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE MONTH(created) = MONTH(CURRENT_DATE())" );
                break;
            case 'last_month':
                $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE MONTH(created) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)" );
                break;
            case 'last_6_months':
                $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE created >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)" );
                break;
            default:
                $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction" );
        }

        return ! empty( $total_income ) ? $total_income : 0;
    }
}
