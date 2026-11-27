<?php
/**
 * Commerce quick actions: the user / pack / coupon filters of the payments
 * list and the subscription setup checks.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Setup\Prerequisites;

/**
 * @covers \WeDevs\Wpuf\Platform\Stores\TransactionStore
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\TransactionsController
 * @covers \WeDevs\Wpuf\Platform\Setup\Prerequisites
 */
class CommerceQuickActionsTest extends WP_UnitTestCase {

    /**
     * Administrator.
     *
     * @var int
     */
    private $admin = 0;

    public function set_up() {
        parent::set_up();

        global $wpdb;

        $this->admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        do_action( 'rest_api_init' );
    }

    /**
     * A completed payment row.
     *
     * @param array $data Columns
     *
     * @return int Row ID
     */
    private function transaction( $data ) {
        global $wpdb;

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . 'wpuf_transaction',
            array_merge(
                [
                    'user_id'        => $this->admin,
                    'status'         => 'completed',
                    'cost'           => '10',
                    'tax'            => '0',
                    'post_id'        => 0,
                    'pack_id'        => 0,
                    'coupon_id'      => 0,
                    'payer_email'    => 'payer@example.com',
                    'payment_type'   => 'Paypal',
                    'transaction_id' => 'TX',
                    'created'        => current_time( 'mysql' ),
                ],
                $data
            )
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Ids of the list rows for some filters.
     *
     * @param array $params Query params
     *
     * @return int[]
     */
    private function listed( $params ) {
        wp_set_current_user( $this->admin );

        $request = new WP_REST_Request( 'GET', '/wpuf/v1/admin/transactions' );
        $request->set_query_params( $params );
        $data = rest_do_request( $request )->get_data();

        return array_map( 'intval', wp_list_pluck( $data['items'], 'id' ) );
    }

    public function test_payments_filter_by_user_pack_and_coupon() {
        $other = self::factory()->user->create();
        $a     = $this->transaction( [ 'pack_id' => 11 ] );
        $b     = $this->transaction( [ 'user_id' => $other, 'pack_id' => 12, 'coupon_id' => 7 ] );

        $this->assertSame( [ $b ], $this->listed( [ 'user_id' => $other ] ) );
        $this->assertSame( [ $a ], $this->listed( [ 'pack_id' => 11 ] ) );
        $this->assertSame( [ $b ], $this->listed( [ 'coupon_id' => 7 ] ) );
        $this->assertCount( 2, $this->listed( [] ) );
    }

    public function test_registration_check_follows_anyone_can_register() {
        $prerequisites = new Prerequisites();

        update_option( 'users_can_register', 0 );
        $this->assertFalse( $prerequisites->registration_open() );

        update_option( 'users_can_register', 1 );
        $this->assertTrue( $prerequisites->registration_open() );
    }

    public function test_form_requiring_a_pack_no_pack_allows() {
        $form = self::factory()->post->create(
            [
                'post_type'   => 'wpuf_forms',
                'post_status' => 'publish',
                'post_title'  => 'Products form',
            ]
        );

        update_post_meta(
            $form,
            'wpuf_form_settings',
            [
                'post_type'             => 'post',
                'payment_options'       => 'true',
                'choose_payment_option' => 'force_pack_purchase',
            ]
        );

        $pack = self::factory()->post->create(
            [
                'post_type'   => 'wpuf_subscription',
                'post_status' => 'publish',
            ]
        );

        update_post_meta( $pack, '_post_type_name', [ 'post' => '0' ] );

        $found = ( new Prerequisites() )->forms_without_pack();
        $this->assertSame( [ $form ], wp_list_pluck( $found, 'id' ) );

        // A pack that allows posts covers the form.
        update_post_meta( $pack, '_post_type_name', [ 'post' => '-1' ] );
        $this->assertSame( [], ( new Prerequisites() )->forms_without_pack() );
    }
}
