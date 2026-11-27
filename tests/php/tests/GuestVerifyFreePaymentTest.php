<?php
/**
 * A guest post on a form that charges nothing is not left "unpaid": the
 * verification link publishes it and drops a pending payment status left by
 * older versions, so the account shows no Pay Now and an edit does not put
 * the post back to pending.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Frontend_Form;
use WeDevs\Wpuf\Platform\Stores\Stores;

class GuestVerifyFreePaymentTest extends WP_UnitTestCase {

    public function tear_down() {
        unset( $_GET['post_msg'], $_GET['p_id'], $_GET['f_id'] );

        parent::tear_down();
    }

    public function test_verified_free_guest_post_has_no_payment_status() {
        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        update_post_meta(
            $form, 'wpuf_form_settings', [
                'post_type'          => 'post',
                'post_status'        => 'publish',
                'post_permission'    => 'guest_post',
                'guest_email_verify' => 'true',
            ]
        );

        $post = self::factory()->post->create( [ 'post_status' => 'draft' ] );

        update_post_meta( $post, '_wpuf_form_id', $form );
        update_post_meta( $post, Frontend_Form::$guest_verify_id, 'yes' );
        Stores::submissions()->set_payment_status( $post, 'pending' );

        $_GET['post_msg'] = 'verified';
        $_GET['p_id']     = wpuf_encryption( $post );
        $_GET['f_id']     = wpuf_encryption( $form );

        ob_start();
        ( new Frontend_Form() )->publish_guest_post();
        ob_end_clean();

        $this->assertSame( 'publish', get_post_status( $post ) );
        $this->assertSame( '', (string) Stores::submissions()->payment_status( $post ), 'a free post is not waiting for payment' );
        $this->assertSame( 'no', get_post_meta( $post, Frontend_Form::$guest_verify_id, true ) );
    }
}
