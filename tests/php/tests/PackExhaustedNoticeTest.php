<?php
/**
 * The post form's subscription notice (Pack_Display::add_post_info()) when
 * the forced pack is used up: no "$0.00 charge" sentence for a charge of 0,
 * the pack-exhausted note instead; develop's text when there is a charge.
 *
 * @package WP_User_Frontend
 */

class PackExhaustedNoticeTest extends WP_UnitTestCase {

    private $form;

    public function set_up() {
        parent::set_up();

        $pack = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish' ] );
        $user = self::factory()->user->create( [ 'role' => 'subscriber' ] );

        wp_set_current_user( $user );
        update_user_meta(
            $user, '_wpuf_subscription_pack', [
                'pack_id' => $pack,
                'posts'   => [ 'post' => 0 ],
                'status'  => 'completed',
                'expire'  => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
            ]
        );

        $this->form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
    }

    private function notice( array $settings ) {
        $settings = array_merge(
            [
                'post_type'             => 'post',
                'payment_options'       => 'on',
                'choose_payment_option' => 'force_pack_purchase',
            ], $settings
        );
        update_post_meta( $this->form, 'wpuf_form_settings', $settings );

        ob_start();
        wpuf()->subscription->add_post_info( $this->form, $settings );

        return wp_strip_all_tags( ob_get_clean() );
    }

    public function test_used_up_pack_without_a_charge_says_exhausted_without_zero_charge() {
        $text = $this->notice( [ 'fallback_ppp_enable' => 'false' ] );

        $this->assertStringContainsString( 'Your Subscription pack is exhausted.', $text );
        $this->assertStringNotContainsString( 'charge', $text );

        $text = $this->notice( [ 'fallback_ppp_enable' => 'true', 'fallback_ppp_cost' => '0' ] );

        $this->assertStringContainsString( 'Your Subscription pack is exhausted.', $text );
        $this->assertStringNotContainsString( 'charge', $text );
    }

    public function test_used_up_pack_with_a_fallback_charge_keeps_the_develop_text() {
        $text = $this->notice( [ 'fallback_ppp_enable' => 'true', 'fallback_ppp_cost' => '5' ] );

        $this->assertStringContainsString( 'Your Subscription pack is exhausted. There is a', $text );
        $this->assertStringContainsString( '5', $text );
    }
}
