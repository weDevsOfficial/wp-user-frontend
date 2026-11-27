<?php
/**
 * Onboarding Login & Registration step: only existing pages become the
 * login, registration and account pages.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Onboarding\Wizard;

/**
 * @covers \WeDevs\Wpuf\Platform\Onboarding\Step_Handlers::save_registration
 */
class OnboardingPagesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function test_only_existing_pages_are_stored() {
        $login   = self::factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Login' ] );
        $reg     = self::factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Register' ] );
        $account = self::factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Account' ] );
        $post    = self::factory()->post->create( [ 'post_type' => 'post', 'post_title' => 'Not a page' ] );
        $trashed = self::factory()->post->create( [ 'post_type' => 'page', 'post_status' => 'trash' ] );

        update_option( 'wpuf_profile', [ 'login_page' => $login, 'reg_override_page' => $reg ] );
        update_option( 'wpuf_my_account', [ 'account_page' => $account ] );

        $wizard = wpuf()->platform()->get( Wizard::class );

        foreach ( [ $post, $trashed, 999999 ] as $bad ) {
            $wizard->run_step( 'registration', [ 'login_page' => (string) $bad, 'reg_page' => (string) $bad, 'account_page' => (string) $bad ] );

            $profile = get_option( 'wpuf_profile' );
            $this->assertSame( $login, (int) $profile['login_page'], "login page kept for $bad" );
            $this->assertSame( $reg, (int) $profile['reg_override_page'], "registration page kept for $bad" );
            $this->assertSame( $account, (int) get_option( 'wpuf_my_account' )['account_page'], "account page kept for $bad" );
        }

        $this->assertStringNotContainsString( 'wpuf', get_post_field( 'post_content', $post ), 'a non page gets no shortcode' );

        // A real page is taken (and gets its shortcode).
        $other = self::factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Other login' ] );
        $wizard->run_step( 'registration', [ 'login_page' => (string) $other, 'reg_page' => (string) $reg, 'account_page' => (string) $account ] );
        $this->assertSame( $other, (int) get_option( 'wpuf_profile' )['login_page'] );
        $this->assertStringContainsString( '[wpuf-login]', get_post_field( 'post_content', $other ) );
    }
}
