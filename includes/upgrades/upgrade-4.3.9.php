<?php
/**
 * Upgrade routine for 4.3.9
 *
 * Adds the `discount` column to `wpuf_transaction` table so coupon
 * discount amounts are persisted per transaction.
 *
 * @since 4.3.9
 */

global $wpdb;

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$collate = '';
if ( $wpdb->has_cap( 'collation' ) ) {
    if ( ! empty( $wpdb->charset ) ) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
    }
    if ( ! empty( $wpdb->collate ) ) {
        $collate .= " COLLATE $wpdb->collate";
    }
}

$table_name = $wpdb->prefix . 'wpuf_transaction';

// No IF NOT EXISTS here: dbDelta parses the table name with
// `|CREATE TABLE ([^ ]*)|`, so it would read the name as "IF", find no such
// table, and run the raw statement as a no-op instead of emitting the
// ALTER TABLE ... ADD COLUMN for `discount` and `coupon_id`. dbDelta is
// already idempotent against an existing table.
$sql = "CREATE TABLE `{$table_name}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) DEFAULT NULL,
    `status` varchar(60) NOT NULL DEFAULT 'pending_payment',
    `subtotal` varchar(255) DEFAULT '',
    `discount` varchar(255) DEFAULT '0',
    `coupon_id` bigint(20) DEFAULT NULL,
    `tax` varchar(255) DEFAULT '',
    `cost` varchar(255) DEFAULT '',
    `post_id` varchar(20) DEFAULT NULL,
    `pack_id` bigint(20) DEFAULT NULL,
    `payer_first_name` varchar(60),
    `payer_last_name` varchar(60),
    `payer_email` varchar(100),
    `payment_type` varchar(20),
    `payer_address` longtext,
    `transaction_id` varchar(60),
    `created` datetime NOT NULL,
    PRIMARY KEY (`id`),
    key `user_id` (`user_id`),
    key `post_id` (`post_id`),
    key `pack_id` (`pack_id`),
    key `payer_email` (`payer_email`)
) $collate;";

dbDelta( $sql );
