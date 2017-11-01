<?php

/**
 * Move 'Custom fields in post' option from global to individual field settings
 *
 * @return void
 */
function wpuf_upgrade_2_6_field_options() {

    $show_custom  = wpuf_get_option( 'cf_show_front', 'wpuf_general', 'no' );

    $input_fields = get_posts( array(
        'post_type'   => array( 'wpuf_input' ),
        'numberposts' => '-1'
    ) );

    if ( !$input_fields ) {
        return;
    }

    foreach ($input_fields as $key => $field) {
        $settings = maybe_unserialize($field->post_content);

        if ( isset($settings['is_meta'] ) && $settings['is_meta'] == 'yes' ) {
            $settings['show_in_post'] = $show_custom;
        }

        wp_update_post( array(
            'ID' => $field->ID,
            'post_content' => maybe_serialize( $settings )
        ) );
    }

}

/**
 * create table
 * @return void
 */
function create_subscribers_table() {
    global $wpdb;
    $sql = "CREATE TABLE {$wpdb->prefix}wpuf_subscribers (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `name` varchar(191) NOT NULL,
      `subscribtion_id` varchar(191) NOT NULL,
      `subscribtion_status` varchar(191) NOT NULL,
      `gateway` varchar(191) NOT NULL,
      `transaction_id` varchar(191) NOT NULL,
      `starts_from` varchar(191) NOT NULL,
      `expire` varchar(191) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}

/**
 * insert table data
 * @return void
 */
function insert_subscribers() {
    global $wpdb;
    $users = WPUF_Subscription::init()->subscription_pack_users();
    foreach ($users as $user) {
        $sub_data               = get_user_meta( $user->data->ID, '_wpuf_subscription_pack', true );
        $sql = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "wpuf_transaction
        WHERE user_id = %d AND pack_id = %d LIMIT 1", $user->data->ID, $sub_data['pack_id'] );
        $result = $wpdb->get_row( $sql );
        if ( $result ) {
            $table_data = array(
                'user_id'               => $user->data->ID,
                'name'                  => $user->data->display_name,
                'subscribtion_id'       => $sub_data['pack_id'],
                'subscribtion_status'   => is_null( $sub_data['status'] ) ? 'pending' : $sub_data['status'],
                'gateway'               => is_null( $result->payment_type ) ? 'bank' : $result->payment_type,
                'transaction_id'        => is_null( $result->transaction_id ) ? 'NA' : $result->transaction_id,
                'starts_from'           => is_null( $result->created ) ? 'NA' : $result->created,
                'expire'                => $sub_data['expire']
            );
            $wpdb->insert( $wpdb->prefix . 'wpuf_subscribers', $table_data );
        }
    }
}

wpuf_upgrade_2_6_field_options();
create_subscribers_table();
insert_subscribers();