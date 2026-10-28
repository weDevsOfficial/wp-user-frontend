<?php
/**
 * The React account's logout link is a plain URL (no &amp;), so the browser
 * sends the nonce and the member is logged out.
 *
 * @package WP_User_Frontend
 */

class AccountLogoutUrlTest extends WP_UnitTestCase {

    public function test_logout_url_is_not_html_escaped() {
        wp_set_current_user( self::factory()->user->create() );

        $url = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Account\Account_Service::class )->profile()['logout_url'];

        $this->assertStringNotContainsString( '&amp;', $url );

        parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );

        $this->assertSame( 'logout', $query['action'] );
        $this->assertSame( 1, wp_verify_nonce( $query['_wpnonce'], 'log-out' ) );
    }
}
