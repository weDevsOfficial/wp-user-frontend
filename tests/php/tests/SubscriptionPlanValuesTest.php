<?php
/**
 * The plan save refuses what the editor refuses (direct REST calls too): an
 * empty name, a negative price, a recurring plan without a price or with a
 * billing cycle under 1.
 *
 * @package WP_User_Frontend
 */

class SubscriptionPlanValuesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        do_action( 'rest_api_init' );
    }

    private function save( $title, $meta ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
        $request->set_param( 'subscription', [ 'post_title' => $title, 'post_status' => 'publish', 'meta_value' => $meta ] );

        return rest_get_server()->dispatch( $request )->get_data();
    }

    public function test_bad_plan_values_are_refused() {
        $cases = [
            [ '', [ '_billing_amount' => '10' ], 'Plan name is required.' ],
            [ 'Neg', [ '_billing_amount' => '-5' ], 'Enter a price of 0 or more.' ],
            [ 'Rec free', [ '_billing_amount' => '0', '_recurring_pay' => 'on', '_billing_cycle_number' => '1' ], 'A recurring plan needs a price above 0.' ],
            [ 'Rec cycle', [ '_billing_amount' => '9', '_recurring_pay' => true, '_billing_cycle_number' => '-1' ], 'Enter a billing cycle of 1 or more.' ],
        ];

        foreach ( $cases as $case ) {
            $data = $this->save( $case[0], $case[1] );

            $this->assertFalse( $data['success'], $case[0] );
            $this->assertSame( $case[2], $data['message'] );
        }
    }

    public function test_valid_plans_still_save() {
        $this->assertNotSame( false, $this->save( 'One time', [ '_billing_amount' => '10', '_recurring_pay' => 'no', '_billing_cycle_number' => '-1' ] )['success'] ?? null );
        $this->assertNotSame( false, $this->save( 'Monthly', [ '_billing_amount' => '9', '_recurring_pay' => 'on', '_billing_cycle_number' => '1' ] )['success'] ?? null );
    }
}
